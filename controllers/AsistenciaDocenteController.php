<?php
// Controlador de Asistencia Docente: auto-marcación y consulta
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/attendance_config.php';
require_once __DIR__ . '/../models/AsistenciaDocenteModel.php';

class AsistenciaDocenteController
{
    private AsistenciaDocenteModel $model;
    private array $config;

    public function __construct()
    {
        $this->model  = new AsistenciaDocenteModel(Conexion::connection());
        $this->model->ensureTable();
        $this->config = require __DIR__ . '/../core/attendance_config.php';
    }

    public function handleRequest(string $method, array $payload = [], array $query = []): array
    {
        $idCredencial = (int)($_SESSION['usuario_id'] ?? 0);
        $rol = strtolower(trim((string)($_SESSION['rol_nombre'] ?? '')));

        if ($idCredencial <= 0 || $rol === '') {
            return $this->respuesta(false, 'La sesión no está activa.', null, 401);
        }

        $esDocente = ($rol === 'docente');
        $esAdmin   = in_array($rol, ['director', 'administrador', 'admin'], true);

        if (!$esDocente && !$esAdmin) {
            return $this->respuesta(false, 'No tiene permisos para este módulo.', null, 403);
        }

        // ── POST: marcar asistencia (solo docente) ────────────────────
        if ($method === 'POST') {
            if (!$esDocente) {
                return $this->respuesta(false, 'Solo el docente puede marcar su asistencia.', null, 403);
            }

            return $this->marcarAsistencia($idCredencial);
        }

        // ── GET: consultas ────────────────────────────────────────────
        if ($method === 'GET') {
            $action = strtolower(trim((string)($query['action'] ?? 'status')));

            // Estado actual del docente hoy
            if ($action === 'status') {
                return $this->obtenerEstado($idCredencial);
            }

            // Verificar IP (el frontend llama esto antes de mostrar el botón)
            if ($action === 'check-ip') {
                return $this->verificarIP();
            }

            // Historial propio del docente
            if ($action === 'mi-historial') {
                return $this->miHistorial($idCredencial);
            }

            // Vista director: asistencia de todos los docentes en una fecha
            if ($action === 'all') {
                if (!$esAdmin) {
                    return $this->respuesta(false, 'Solo Dirección puede ver el reporte general.', null, 403);
                }
                $fecha = trim((string)($query['fecha'] ?? date('Y-m-d')));
                return $this->asistenciaDelDia($fecha);
            }

            // Vista director: resumen con filtros
            if ($action === 'resumen') {
                if (!$esAdmin) {
                    return $this->respuesta(false, 'Solo Dirección puede ver el resumen.', null, 403);
                }
                return $this->resumen($query);
            }

            // Vista director: estadísticas del día
            if ($action === 'stats') {
                if (!$esAdmin) {
                    return $this->respuesta(false, 'Solo Dirección puede ver estadísticas.', null, 403);
                }
                $fecha = trim((string)($query['fecha'] ?? date('Y-m-d')));
                return $this->estadisticasDelDia($fecha);
            }

            return $this->respuesta(false, "Acción '{$action}' no reconocida.", null, 404);
        }

        return $this->respuesta(false, 'Método no permitido.', null, 405);
    }

    // ── Lógica interna ───────────────────────────────────────────────

    private function marcarAsistencia(int $idCredencial): array
    {
        // 1. Validar IP
        $validacionIP = $this->verificarIP();
        if (!$validacionIP['success']) {
            return $validacionIP;
        }

        // 2. Obtener id_docente
        $idDocente = $this->model->obtenerIdDocentePorCredencial($idCredencial);
        if (!$idDocente) {
            return $this->respuesta(false, 'Su cuenta no está vinculada como docente.', null, 422);
        }

        // 3. Verificar horario
        $ahora   = new DateTime();
        $fecha   = $ahora->format('Y-m-d');
        $hora    = $ahora->format('H:i:s');
        $horaNum = (int)$ahora->format('Hi');

        $horaCierre = (int)str_replace(':', '', $this->config['hora_cierre']);
        if ($horaNum > $horaCierre) {
            return $this->respuesta(false, $this->config['mensaje_fuera_horario'], null, 403);
        }

        // 4. Determinar estado (Presente o Tardanza)
        $horaLimite = (int)str_replace(':', '', $this->config['hora_limite_tardanza']);
        $estado = ($horaNum > $horaLimite) ? 'Tardanza' : 'Presente';

        // 5. Verificar si ya marcó hoy
        $yaMarcado = $this->model->yaRegistroHoy($idDocente, $fecha);
        if ($yaMarcado) {
            // Actualizar registro existente
            $registro = $this->model->registrar($idDocente, $fecha, $hora, $this->getIP(), $estado);
            return $this->respuesta(true, 'Asistencia actualizada correctamente.', $registro);
        }

        // 6. Registrar nueva marcación
        $registro = $this->model->registrar($idDocente, $fecha, $hora, $this->getIP(), $estado);
        return $this->respuesta(true, 'Asistencia registrada correctamente.', $registro, 201);
    }

    private function obtenerEstado(int $idCredencial): array
    {
        $idDocente = $this->model->obtenerIdDocentePorCredencial($idCredencial);
        if (!$idDocente) {
            return $this->respuesta(false, 'Su cuenta no está vinculada como docente.', null, 422);
        }

        $fecha    = date('Y-m-d');
        $registro = $this->model->yaRegistroHoy($idDocente, $fecha);

        $horaActual  = new DateTime();
        $horaCierre  = new DateTime($this->config['hora_cierre']);
        $fueraHorario = $horaActual > $horaCierre;

        return $this->respuesta(true, 'Estado cargado.', [
            'marcado'       => $registro !== null,
            'registro'      => $registro,
            'fuera_horario' => $fueraHorario,
            'fecha'         => $fecha,
            'hora_actual'   => $horaActual->format('H:i:s'),
        ]);
    }

    private function verificarIP(): array
    {
        $ipCliente = $this->getIP();
        $permitida = false;

        foreach ($this->config['ip_permitidas'] as $patron) {
            if ($this->ipCoincide($ipCliente, $patron)) {
                $permitida = true;
                break;
            }
        }

        if (!$permitida) {
            return $this->respuesta(false, $this->config['mensaje_ip_invalida'], [
                'ip_detectada' => $ipCliente,
                'autorizada'   => false,
            ], 403);
        }

        return $this->respuesta(true, 'IP autorizada.', [
            'ip_detectada' => $ipCliente,
            'autorizada'   => true,
        ]);
    }

    private function miHistorial(int $idCredencial): array
    {
        $idDocente = $this->model->obtenerIdDocentePorCredencial($idCredencial);
        if (!$idDocente) {
            return $this->respuesta(false, 'Su cuenta no está vinculada como docente.', null, 422);
        }

        $historial = $this->model->getPorDocente($idDocente, 30);
        return $this->respuesta(true, 'Historial cargado.', $historial);
    }

    private function asistenciaDelDia(string $fecha): array
    {
        $datos = $this->model->getPorFecha($fecha);
        return $this->respuesta(true, 'Asistencia del día cargada.', $datos);
    }

    private function resumen(array $filtros): array
    {
        $datos = $this->model->getResumen($filtros);
        return $this->respuesta(true, 'Resumen cargado.', $datos);
    }

    private function estadisticasDelDia(string $fecha): array
    {
        $stats = $this->model->getEstadisticasDelDia($fecha);
        return $this->respuesta(true, 'Estadísticas del día.', $stats);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function getIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return trim($_SERVER['HTTP_CLIENT_IP']);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function ipCoincide(string $ip, string $patron): bool
    {
        // Convertir comodín * a regex: 192.168.1.* → 192\.168\.1\.\d+
        $regex = preg_replace('/\*/', '\\d+', preg_quote($patron, '/'));
        return (bool)preg_match('/^' . '$|^' . $regex . '$/', $ip);
    }

    private function respuesta(bool $success, string $message, mixed $data = null, int $status = 200): array
    {
        return compact('success', 'message', 'data', 'status');
    }
}
