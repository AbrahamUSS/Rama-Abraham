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
            "CREATE TABLE IF NOT EXISTS personas (" .
            "id_persona INT AUTO_INCREMENT PRIMARY KEY, " .
            "dni VARCHAR(20) NOT NULL, " .
            "nombre VARCHAR(100) NOT NULL, " .
            "ap_paterno VARCHAR(100) NOT NULL, " .
            "ap_materno VARCHAR(100) NOT NULL, " .
            "fechaNa DATE NOT NULL, " .
            "direccion VARCHAR(150) NOT NULL" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS buzon (" .
            "id_buzon INT AUTO_INCREMENT PRIMARY KEY, " .
            "no_leidos INT NOT NULL DEFAULT 0" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

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

        // Las credenciales se vinculan a la misma persona del docente.  Así se
        // evita duplicar personas y el inicio de sesión puede identificarlo.
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS CREDENCIALES (" .
            "id_credenciales INT AUTO_INCREMENT PRIMARY KEY, " .
            "username VARCHAR(255) NOT NULL UNIQUE, " .
            "password_hash VARCHAR(255) NOT NULL, " .
            "id_persona INT NOT NULL UNIQUE" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS ROL (" .
            "id_rol INT AUTO_INCREMENT PRIMARY KEY, " .
            "nombre VARCHAR(30) NOT NULL UNIQUE" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS USUARIO_ROL (" .
            "id_usuario_rol INT AUTO_INCREMENT PRIMARY KEY, " .
            "id_credenciales INT NOT NULL, " .
            "id_rol INT NOT NULL, " .
            "UNIQUE KEY uq_usuario_rol (id_credenciales, id_rol)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->pdo->exec(
            "ALTER TABLE docentes ADD COLUMN IF NOT EXISTS nombre_completo VARCHAR(150) NOT NULL DEFAULT ''"
        );
        $this->pdo->exec(
            "ALTER TABLE docentes ADD COLUMN IF NOT EXISTS fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        );
        $this->pdo->exec(
            "ALTER TABLE docentes ADD COLUMN IF NOT EXISTS calificacion DECIMAL(3,1) DEFAULT 5.0"
        );
        $this->pdo->exec(
            "ALTER TABLE docentes ADD COLUMN IF NOT EXISTS observaciones TEXT NULL"
        );
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            "SELECT d.id_docente, d.id_persona, d.cod_docente, d.tipo_contrato, d.es_activo, d.grado_academico, d.especialidad, d.id_buzon, " .
            "COALESCE(NULLIF(TRIM(d.nombre_completo), ''), TRIM(CONCAT(p.nombre, ' ', COALESCE(p.ap_paterno, ''), ' ', COALESCE(p.ap_materno, '')))) AS nombre_completo, " .
            "d.fecha_registro, d.calificacion, d.observaciones, " .
            "p.dni, p.nombre, p.ap_paterno, p.ap_materno, p.fechaNa, p.direccion AS email, " .
            "GROUP_CONCAT(DISTINCT c.nombre SEPARATOR ', ') AS cursos_a_cargo " .
            "FROM docentes d " .
            "LEFT JOIN personas p ON d.id_persona = p.id_persona " .
            "LEFT JOIN asignacion_curso ac ON ac.id_docente = d.id_docente " .
            "LEFT JOIN grado_curso gc ON gc.id_gradoCurso = ac.id_gradoCurso " .
            "LEFT JOIN curso c ON c.id_curso = gc.id_curso " .
            "GROUP BY d.id_docente " .
            "ORDER BY d.id_docente DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCredentials(): array
    {
        $stmt = $this->pdo->query(
            "SELECT d.id_docente, d.cod_docente, " .
            "TRIM(CONCAT(p.nombre, ' ', p.ap_paterno, ' ', p.ap_materno)) AS nombre_completo, " .
            "c.username, r.nombre AS rol " .
            "FROM docentes d " .
            "INNER JOIN personas p ON p.id_persona = d.id_persona " .
            "INNER JOIN CREDENCIALES c ON c.id_persona = p.id_persona " .
            "INNER JOIN USUARIO_ROL ur ON ur.id_credenciales = c.id_credenciales " .
            "INNER JOIN ROL r ON r.id_rol = ur.id_rol " .
            "WHERE LOWER(r.nombre) = 'docente' " .
            "ORDER BY d.id_docente DESC"
        );
        $credentials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($credentials as &$credential) {
            // La clave inicial es igual al usuario. El hash nunca se expone.
            $credential['password_temporal'] = $credential['username'];
        }
        unset($credential);
        return $credentials;
    }

    public function create(array $data): array
    {
        $dni = trim((string)($data['dni'] ?? ''));
        $nombre = trim((string)($data['nombre'] ?? ''));
        $apPaterno = trim((string)($data['ap_paterno'] ?? ''));
        $apMaterno = trim((string)($data['ap_materno'] ?? ''));
        $fechaNa = trim((string)($data['fechaNa'] ?? ''));
        $direccion = trim((string)($data['direccion'] ?? ''));

        $tipoContrato = trim((string)($data['tipo_contrato'] ?? ''));
        $gradoAcademico = trim((string)($data['grado_academico'] ?? ''));
        $especialidad = trim((string)($data['especialidad'] ?? ''));
        $esActivo = !empty($data['es_activo']);

        $nombreCompleto = trim((string)($data['nombre_completo'] ?? ''));
        if ($nombreCompleto === '' && ($nombre !== '' || $apPaterno !== '' || $apMaterno !== '')) {
            $nombreCompleto = trim("$nombre $apPaterno $apMaterno");
        }

        if ($dni === '' || $nombre === '' || $apPaterno === '' || $apMaterno === '' || $fechaNa === '' || $direccion === '' || $nombreCompleto === '' || $tipoContrato === '' || $gradoAcademico === '' || $especialidad === '') {
            throw new InvalidArgumentException('Complete todos los campos obligatorios de la Persona Natural y del Docente.');
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

            $personaStmt = $this->pdo->prepare(
                "INSERT INTO personas (dni, nombre, ap_paterno, ap_materno, fechaNa, direccion) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $personaStmt->execute([
                $dni,
                $nombre,
                $apPaterno,
                $apMaterno,
                $fechaNa,
                $direccion
            ]);
            $idPersona = (int)$this->pdo->lastInsertId();

            $buzonStmt = $this->pdo->prepare("INSERT INTO buzon (no_leidos) VALUES (?)");
            $buzonStmt->execute([0]);
            $idBuzon = (int)$this->pdo->lastInsertId();

            $username = $this->buildUsername($nombre, $apPaterno, $apMaterno, $dni);
            $roleStmt = $this->pdo->prepare("SELECT id_rol FROM ROL WHERE LOWER(nombre) = 'docente' LIMIT 1");
            $roleStmt->execute();
            $idRol = (int)$roleStmt->fetchColumn();
            if ($idRol <= 0) {
                $createRoleStmt = $this->pdo->prepare("INSERT INTO ROL (nombre) VALUES ('Docente')");
                $createRoleStmt->execute();
                $idRol = (int)$this->pdo->lastInsertId();
            }

            $credentialStmt = $this->pdo->prepare(
                "INSERT INTO CREDENCIALES (username, password_hash, id_persona) VALUES (?, ?, ?)"
            );
            $credentialStmt->execute([$username, password_hash($username, PASSWORD_DEFAULT), $idPersona]);
            $idCredenciales = (int)$this->pdo->lastInsertId();

            $userRoleStmt = $this->pdo->prepare(
                "INSERT INTO USUARIO_ROL (id_credenciales, id_rol) VALUES (?, ?)"
            );
            $userRoleStmt->execute([$idCredenciales, $idRol]);

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
                "SELECT d.id_docente, d.id_persona, d.cod_docente, d.tipo_contrato, d.es_activo, d.grado_academico, d.especialidad, d.id_buzon, d.nombre_completo, d.fecha_registro, " .
                "p.dni, p.nombre, p.ap_paterno, p.ap_materno, p.fechaNa, p.direccion AS email " .
                "FROM docentes d " .
                "LEFT JOIN personas p ON d.id_persona = p.id_persona " .
                "WHERE d.id_docente = ?"
            );
            $recordStmt->execute([$insertedId]);

            $record = $recordStmt->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $record['username'] = $username;
                $record['password_temporal'] = $username;
            }
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

    public function rateDocente(int $idDocente, float $calificacion, string $observaciones): array
    {
        $stmt = $this->pdo->prepare("UPDATE docentes SET calificacion = ?, observaciones = ? WHERE id_docente = ?");
        $stmt->execute([$calificacion, $observaciones, $idDocente]);

        return [
            'id_docente' => $idDocente,
            'calificacion' => $calificacion,
            'observaciones' => $observaciones
        ];
    }

    private function buildUsername(string $nombre, string $apPaterno, string $apMaterno, string $dni): string
    {
        $raw = strtolower(trim($nombre . '.' . $apPaterno . '.' . $apMaterno . '.' . $dni));
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $raw);
        $normalized = $normalized === false ? $raw : $normalized;
        $normalized = preg_replace('/[^a-z0-9]+/', '.', $normalized);
        return trim((string)$normalized, '.');
    }
}
