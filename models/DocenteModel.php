<?php
class DocenteModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ensureTable(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS docentes (" .
            "id_docente INT AUTO_INCREMENT PRIMARY KEY, " .
            "id_persona INT NOT NULL, " .
            "cod_docente VARCHAR(20) NOT NULL UNIQUE, " .
            "tipo_contrato VARCHAR(50) NOT NULL, " .
            "es_activo TINYINT(1) NOT NULL DEFAULT 1, " .
            "grado_academico VARCHAR(100) NOT NULL, " .
            "especialidad VARCHAR(150) NOT NULL, " .
            "id_buzon INT NOT NULL, " .
            "nombre_completo VARCHAR(150) NOT NULL, " .
            "fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->pdo->exec(
            "ALTER TABLE docentes ADD COLUMN IF NOT EXISTS nombre_completo VARCHAR(150) NOT NULL DEFAULT ''"
        );
        $this->pdo->exec(
            "ALTER TABLE docentes ADD COLUMN IF NOT EXISTS fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        );
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id_docente, id_persona, cod_docente, tipo_contrato, es_activo, grado_academico, especialidad, id_buzon, nombre_completo, fecha_registro FROM docentes ORDER BY id_docente DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): array
    {
        $nombreCompleto = trim((string)($data['nombre_completo'] ?? ''));
        $tipoContrato = trim((string)($data['tipo_contrato'] ?? ''));
        $gradoAcademico = trim((string)($data['grado_academico'] ?? ''));
        $especialidad = trim((string)($data['especialidad'] ?? ''));
        $esActivo = !empty($data['es_activo']);

        if ($nombreCompleto === '' || $tipoContrato === '' || $gradoAcademico === '' || $especialidad === '') {
            throw new InvalidArgumentException('Complete los campos obligatorios.');
        }

        $this->pdo->beginTransaction();

        try {
            $codeStmt = $this->pdo->query(
                "SELECT cod_docente FROM docentes WHERE cod_docente LIKE 'DOC%' ORDER BY CAST(SUBSTRING(cod_docente, 4) AS UNSIGNED) DESC LIMIT 1"
            );
            $lastCodeRow = $codeStmt->fetch(PDO::FETCH_ASSOC);
            $nextNumber = 1;

            if ($lastCodeRow && !empty($lastCodeRow['cod_docente'])) {
                $lastNumber = (int)substr($lastCodeRow['cod_docente'], 3);
                $nextNumber = $lastNumber + 1;
            }

            $codDocente = 'DOC' . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
            $personaDni = str_pad((string)$nextNumber, 8, '0', STR_PAD_LEFT);

            $nameParts = preg_split('/\s+/', $nombreCompleto) ?: [];
            $nombre = $nameParts[0] ?? 'Docente';
            $apPaterno = $nameParts[1] ?? 'Sin';
            $apMaterno = isset($nameParts[2]) ? implode(' ', array_slice($nameParts, 2)) : 'Apellido';

            $personaStmt = $this->pdo->prepare(
                "INSERT INTO personas (dni, nombre, ap_paterno, ap_materno, fechaNa, direccion) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $personaStmt->execute([
                $personaDni,
                $nombre,
                $apPaterno,
                $apMaterno,
                '2000-01-01',
                'Sin registro'
            ]);
            $idPersona = (int)$this->pdo->lastInsertId();

            $buzonStmt = $this->pdo->prepare("INSERT INTO buzon (no_leidos) VALUES (?)");
            $buzonStmt->execute([0]);
            $idBuzon = (int)$this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare(
                "INSERT INTO docentes (id_persona, cod_docente, tipo_contrato, es_activo, grado_academico, especialidad, id_buzon, nombre_completo) " .
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $idPersona,
                $codDocente,
                $tipoContrato,
                $esActivo ? 1 : 0,
                $gradoAcademico,
                $especialidad,
                $idBuzon,
                $nombreCompleto
            ]);

            $insertedId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();

            $recordStmt = $this->pdo->prepare(
                "SELECT id_docente, id_persona, cod_docente, tipo_contrato, es_activo, grado_academico, especialidad, id_buzon, nombre_completo, fecha_registro FROM docentes WHERE id_docente = ?"
            );
            $recordStmt->execute([$insertedId]);

            $record = $recordStmt->fetch(PDO::FETCH_ASSOC);
            return $record ?: [];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $idDocente, bool $esActivo): array
    {
        $stmt = $this->pdo->prepare("UPDATE docentes SET es_activo = ? WHERE id_docente = ?");
        $stmt->execute([$esActivo ? 1 : 0, $idDocente]);

        return [
            'id_docente' => $idDocente,
            'es_activo' => $esActivo
        ];
    }
}
