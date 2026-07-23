<?php
/**
 * =============================================================
 * SEED DE USUARIOS INICIALES - Sistema Gestión IE
 * =============================================================
 * 
 * Este script crea dos usuarios de prueba:
 *   1. Director  -> username: director  | password: director123
 *   2. Docente   -> username: docente   | password: docente123
 *
 * Ejecutar UNA SOLA VEZ después de importar corazonJesus.sql
 * Desde CLI:  php seed_usuarios.php
 * Desde web:  http://localhost:8080/Sistema_Gestion_IE/seed_usuarios.php
 * =============================================================
 */

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/security.php';

try {
    $conn = Conexion::connection();
    $conn->beginTransaction();

    // =========================================================
    // CREDENCIALES DE LOS USUARIOS
    // =========================================================
    $usuarios = [
        [
            'rol'            => 'Director',
            'username'       => 'director',
            'password'       => 'director123',
            'dni'            => '70000001',
            'nombre'         => 'Carlos',
            'ap_paterno'     => 'Ramirez',
            'ap_materno'     => 'Torres',
            'fechaNa'        => '1980-05-15',
            'direccion'      => 'Av. Principal 123, Lima',
            'telefono'       => '987654321',
            'correo'         => 'director@corazonjesus.edu.pe',
            'tipo'           => 'administrativo',
            'grado_academico'=> 'Magister en Educacion',
            'especialidad'   => 'Gestion Educativa',
        ],
        [
            'rol'            => 'Docente',
            'username'       => 'docente',
            'password'       => 'docente123',
            'dni'            => '70000002',
            'nombre'         => 'Maria',
            'ap_paterno'     => 'Lopez',
            'ap_materno'     => 'Gutierrez',
            'fechaNa'        => '1990-08-22',
            'direccion'      => 'Jr. Los Olivos 456, Lima',
            'telefono'       => '912345678',
            'correo'         => 'docente@corazonjesus.edu.pe',
            'tipo'           => 'docente',
            'cod_docente'    => 'DOC-0001',
            'tipo_contrato'  => 'Tiempo Completo',
            'grado_academico'=> 'Licenciada en Matematicas',
            'especialidad'   => 'Matematicas',
        ],
    ];

    foreach ($usuarios as $u) {

        // 1. Insertar en PERSONAS
        $stmt = $conn->prepare("
            INSERT INTO PERSONAS (dni, nombre, ap_paterno, ap_materno, fechaNa, direccion)
            VALUES (:dni, :nombre, :ap_paterno, :ap_materno, :fechaNa, :direccion)
        ");
        $stmt->execute([
            ':dni'        => $u['dni'],
            ':nombre'     => $u['nombre'],
            ':ap_paterno' => $u['ap_paterno'],
            ':ap_materno' => $u['ap_materno'],
            ':fechaNa'    => $u['fechaNa'],
            ':direccion'  => $u['direccion'],
        ]);
        $idPersona = $conn->lastInsertId();

        // 2. Insertar en EXTRA_PERSONA
        $stmt = $conn->prepare("
            INSERT INTO EXTRA_PERSONA (id_persona, telefono, correo)
            VALUES (:id_persona, :telefono, :correo)
        ");
        $stmt->execute([
            ':id_persona' => $idPersona,
            ':telefono'   => $u['telefono'],
            ':correo'     => $u['correo'],
        ]);

        // 3. Crear BUZON para el usuario
        $stmt = $conn->prepare("INSERT INTO BUZON (no_leidos) VALUES (0)");
        $stmt->execute();
        $idBuzon = $conn->lastInsertId();

        // 4. Insertar en tabla de rol específico (ADMINISTRATIVO o DOCENTES)
        if ($u['tipo'] === 'administrativo') {
            $stmt = $conn->prepare("
                INSERT INTO ADMINISTRATIVO (id_persona, es_activo, grado_academico, especialidad, id_buzon)
                VALUES (:id_persona, 1, :grado_academico, :especialidad, :id_buzon)
            ");
            $stmt->execute([
                ':id_persona'      => $idPersona,
                ':grado_academico' => $u['grado_academico'],
                ':especialidad'    => $u['especialidad'],
                ':id_buzon'        => $idBuzon,
            ]);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO DOCENTES (id_persona, cod_docente, tipo_contrato, es_activo, grado_academico, especialidad, id_buzon)
                VALUES (:id_persona, :cod_docente, :tipo_contrato, 1, :grado_academico, :especialidad, :id_buzon)
            ");
            $stmt->execute([
                ':id_persona'      => $idPersona,
                ':cod_docente'     => $u['cod_docente'],
                ':tipo_contrato'   => $u['tipo_contrato'],
                ':grado_academico' => $u['grado_academico'],
                ':especialidad'    => $u['especialidad'],
                ':id_buzon'        => $idBuzon,
            ]);
        }

        // 5. Insertar en CREDENCIALES (password hasheado con bcrypt)
        $passwordHash = Security::encriptarPassword($u['password']);
        $stmt = $conn->prepare("
            INSERT INTO CREDENCIALES (username, password_hash, id_persona)
            VALUES (:username, :password_hash, :id_persona)
        ");
        $stmt->execute([
            ':username'      => $u['username'],
            ':password_hash' => $passwordHash,
            ':id_persona'    => $idPersona,
        ]);
        $idCredenciales = $conn->lastInsertId();

        // 6. Obtener el id_rol correspondiente
        $stmt = $conn->prepare("SELECT id_rol FROM ROL WHERE nombre = :nombre");
        $stmt->execute([':nombre' => $u['rol']]);
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rol) {
            throw new Exception("Rol '{$u['rol']}' no encontrado. Asegurese de haber ejecutado corazonJesus.sql primero.");
        }

        // 7. Asignar rol en USUARIO_ROL
        $stmt = $conn->prepare("
            INSERT INTO USUARIO_ROL (id_credenciales, id_rol)
            VALUES (:id_credenciales, :id_rol)
        ");
        $stmt->execute([
            ':id_credenciales' => $idCredenciales,
            ':id_rol'          => $rol['id_rol'],
        ]);

        echo "✅ Usuario '{$u['username']}' creado exitosamente con rol '{$u['rol']}'.\n";
    }

    $conn->commit();

    echo "\n";
    echo "========================================\n";
    echo "  SEED COMPLETADO EXITOSAMENTE\n";
    echo "========================================\n";
    echo "\n";
    echo "Usuarios creados:\n";
    echo "  📌 Director -> username: director  | password: director123\n";
    echo "  📌 Docente  -> username: docente   | password: docente123\n";
    echo "\n";

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
