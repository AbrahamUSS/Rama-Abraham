<?php
/**
 * Controlador de Reportes Generales
 *
 * Maneja las peticiones de datos de la BD para el módulo de Reportes:
 *   - Reporte de Notas
 *   - Reporte de Asistencias
 *   - Alumnos con Filtros Combinados
 *   - Opciones dinámicas de filtros (Niveles, Grados)
 */

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__ . '/../models/AsistenciaModel.php';

class ReporteController
{
    private $reporteModel;
    private $asistenciaModel;

    public function __construct()
    {
        $pdo = Conexion::connection();
        $this->reporteModel   = new ReporteModel($pdo);
        $this->asistenciaModel = new AsistenciaModel($pdo);
    }

    public function handleRequest(string $method, array $payload = [], array $query = []): array
    {
        if ($method !== 'GET' && $method !== 'POST') {
            return ['success' => false, 'message' => 'Método HTTP no soportado.', 'data' => null];
        }

        $tipo = trim((string)($query['tipo'] ?? $payload['tipo'] ?? 'notas'));
        $params = array_merge($query, $payload);

        switch ($tipo) {
            case 'filtros':
                $data = $this->reporteModel->getFiltrosOpciones();
                return [
                    'success' => true,
                    'message' => 'Opciones de filtros cargadas desde la BD.',
                    'data'    => $data
                ];

            case 'notas':
                $data = $this->reporteModel->getReporteNotas($params);
                return [
                    'success' => true,
                    'message' => 'Reporte de notas cargado desde la BD.',
                    'data'    => $data
                ];

            case 'asistencia':
            case 'resumen_asistencia':
                $data = $this->asistenciaModel->getResumenPorAlumno($params);
                return [
                    'success' => true,
                    'message' => 'Reporte de asistencias cargado desde la BD.',
                    'data'    => $data
                ];

            case 'consolidado':
                $data = $this->reporteModel->getReporteConsolidado($params);
                return [
                    'success' => true,
                    'message' => 'Reporte consolidado cargado desde la BD.',
                    'data'    => $data
                ];

            default:
                return [
                    'success' => false,
                    'message' => "Tipo de reporte '{$tipo}' no reconocido. Opciones: filtros, notas, asistencia, consolidado.",
                    'data'    => null
                ];
        }
    }
}
