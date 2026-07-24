<?php
// Modelo de asistencia docente - tabla ASISTENCIA_DOCENTE
class AsistenciaDocenteModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Asegurar que la tabla exista (auto-migración ligera)
    public function ensureTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `ASISTENCIA_DOCENTE` (
                `id_asistencia_docente` INT NOT NULL AUTO_INCREMENT,
                `id_docente` INT NOT NULL,
                `fecha` DATE NOT NULL,
                `hora_marcacion` TIME NOT NULL,
                `ip_address` VARCHAR(45) NOT NULL,
                `estado` ENUM('Presente', 'Tardanza', 'Falta') NOT NULL DEFAULT 'Presente',
                `observaciones` TEXT,
                PRIMARY KEY (`id_asistencia_docente`),
                UNIQUE KEY `uq_asistencia_docente_dia` (`id_docente`, `fecha`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Intentar agregar FK si no existe (silencioso si ya está)
        try {
            $this->pdo->exec("
                ALTER TABLE `ASISTENCIA_DOCENTE`
                    ADD CONSTRAINT `fk_asistdoc_docente`
                    FOREIGN KEY (`id_docente`) REFERENCES `DOCENTES`(`id_docente`)
                    ON UPDATE CASCADE ON DELETE CASCADE
            ");
        } catch (\Throwable $e) {
            // FK ya existe, ignorar
        }
    }

    // Obtener id_docente a partir del id_credenciales de sesión
    public function obtenerIdDocentePorCredencial(int $idCredenciales): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT d.id_docente
            FROM DOCENTES d
            INNER JOIN CREDENCIALES c ON c.id_persona = d.id_persona
            WHERE c.id_credenciales = ?
            LIMIT 1
        ");
        $stmt->execute([$idCredenciales]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_docente'] : null;
    }

    // Verificar si ya marcó asistencia hoy
    public function yaRegistroHoy(int $idDocente, string $fecha): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id_asistencia_docente, fecha, hora_marcacion, estado, ip_address
            FROM ASISTENCIA_DOCENTE
            WHERE id_docente = ? AND fecha = ?
            LIMIT 1
        ");
        $stmt->execute([$idDocente, $fecha]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Registrar marcación de asistencia
    public function registrar(int $idDocente, string $fecha, string $hora, string $ip, string $estado): array
    {
        // Si ya existe registro para ese día, actualiza
        $existente = $this->yaRegistroHoy($idDocente, $fecha);

        if ($existente) {
            $stmt = $this->pdo->prepare("
                UPDATE ASISTENCIA_DOCENTE
                SET hora_marcacion = ?, ip_address = ?, estado = ?
                WHERE id_asistencia_docente = ?
            ");
            $stmt->execute([$hora, $ip, $estado, $existente['id_asistencia_docente']]);
            $id = (int)$existente['id_asistencia_docente'];
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO ASISTENCIA_DOCENTE (id_docente, fecha, hora_marcacion, ip_address, estado)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$idDocente, $fecha, $hora, $ip, $estado]);
            $id = (int)$this->pdo->lastInsertId();
        }

        return [
            'id_asistencia_docente' => $id,
            'id_docente'            => $idDocente,
            'fecha'                 => $fecha,
            'hora_marcacion'        => $hora,
            'ip_address'            => $ip,
            'estado'                => $estado,
        ];
    }

    // Obtener historial de asistencia de un docente específico
    public function getPorDocente(int $idDocente, int $limite = 30): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ad.id_asistencia_docente, ad.fecha, ad.hora_marcacion,
                   ad.ip_address, ad.estado, ad.observaciones,
                   CONCAT(p.nombre, ' ', p.ap_paterno, ' ', p.ap_materno) AS nombre_completo,
                   d.cod_docente
            FROM ASISTENCIA_DOCENTE ad
            INNER JOIN DOCENTES d ON d.id_docente = ad.id_docente
            INNER JOIN PERSONAS p ON p.id_persona = d.id_persona
            WHERE ad.id_docente = ?
            ORDER BY ad.fecha DESC, ad.hora_marcacion DESC
            LIMIT ?
        ");
        $stmt->execute([$idDocente, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener asistencia de todos los docentes para una fecha (vista director)
    public function getPorFecha(string $fecha): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ad.id_asistencia_docente, ad.fecha, ad.hora_marcacion,
                   ad.ip_address, ad.estado, ad.observaciones,
                   d.id_docente, d.cod_docente, d.es_activo,
                   CONCAT(p.nombre, ' ', p.ap_paterno, ' ', p.ap_materno) AS nombre_completo,
                   d.especialidad
            FROM DOCENTES d
            INNER JOIN PERSONAS p ON p.id_persona = d.id_persona
            LEFT JOIN ASISTENCIA_DOCENTE ad ON ad.id_docente = d.id_docente AND ad.fecha = ?
            WHERE d.es_activo = 1
            ORDER BY nombre_completo ASC
        ");
        $stmt->execute([$fecha]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener resumen general de asistencia docente (vista director, con rango de fechas)
    public function getResumen(array $filtros = []): array
    {
        $where  = ['d.es_activo = 1'];
        $params = [];

        $fechaInicio = trim((string)($filtros['fecha_inicio'] ?? ''));
        $fechaFin    = trim((string)($filtros['fecha_fin'] ?? ''));
        $idDocente   = (int)($filtros['id_docente'] ?? 0);

        if ($fechaInicio !== '') {
            $where[]  = 'ad.fecha >= ?';
            $params[] = $fechaInicio;
        }
        if ($fechaFin !== '') {
            $where[]  = 'ad.fecha <= ?';
            $params[] = $fechaFin;
        }
        if ($idDocente > 0) {
            $where[]  = 'd.id_docente = ?';
            $params[] = $idDocente;
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $this->pdo->prepare("
            SELECT d.id_docente, d.cod_docente,
                   CONCAT(p.nombre, ' ', p.ap_paterno, ' ', p.ap_materno) AS nombre_completo,
                   d.especialidad,
                   COUNT(ad.id_asistencia_docente) AS total_marcaciones,
                   SUM(CASE WHEN ad.estado = 'Presente' THEN 1 ELSE 0 END) AS presentes,
                   SUM(CASE WHEN ad.estado = 'Tardanza' THEN 1 ELSE 0 END) AS tardanzas,
                   SUM(CASE WHEN ad.estado = 'Falta' THEN 1 ELSE 0 END) AS faltas
            FROM DOCENTES d
            INNER JOIN PERSONAS p ON p.id_persona = d.id_persona
            LEFT JOIN ASISTENCIA_DOCENTE ad ON ad.id_docente = d.id_docente
            WHERE {$whereStr}
            GROUP BY d.id_docente
            ORDER BY nombre_completo ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas del día para el dashboard del director
    public function getEstadisticasDelDia(string $fecha): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(DISTINCT d.id_docente) AS total_docentes,
                COUNT(ad.id_asistencia_docente) AS marcados,
                SUM(CASE WHEN ad.estado = 'Presente' THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN ad.estado = 'Tardanza' THEN 1 ELSE 0 END) AS tardanzas,
                SUM(CASE WHEN ad.estado = 'Falta' OR ad.id_asistencia_docente IS NULL THEN 1 ELSE 0 END) AS faltas
            FROM DOCENTES d
            LEFT JOIN ASISTENCIA_DOCENTE ad ON ad.id_docente = d.id_docente AND ad.fecha = ?
            WHERE d.es_activo = 1
        ");
        $stmt->execute([$fecha]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [
            'total_docentes' => 0,
            'marcados'       => 0,
            'presentes'      => 0,
            'tardanzas'      => 0,
            'faltas'         => 0,
        ];
    }
}
