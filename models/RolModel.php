<?php

class RolModel
{

    private $conn;

    public function __construct()
    {
        $this->conn = Conexion::connection();
    }

    public function getRoles()
    {
        $query = "SELECT id, rol_nombre FROM roles ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRolById($id)
    {
        $query = "SELECT id, rol_nombre FROM roles WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
