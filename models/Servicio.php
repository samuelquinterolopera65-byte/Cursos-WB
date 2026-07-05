<?php
class Servicio {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    /**
     * Obtener todos los servicios
     */
    public function getAll() {
        return $this->db->query("SELECT * FROM servicios ORDER BY nombre ASC")->fetchAll();
    }

    /**
     * Obtener servicio por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM servicios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crear servicio
     */
    public function create($nombre, $descripcion, $precio) {
        $stmt = $this->db->prepare("INSERT INTO servicios (nombre, descripcion, precio) VALUES (:nombre, :descripcion, :precio)");
        return $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio
        ]);
    }

    /**
     * Actualizar servicio
     */
    public function update($id, $nombre, $descripcion, $precio) {
        $stmt = $this->db->prepare("UPDATE servicios SET nombre = :nombre, descripcion = :descripcion, precio = :precio WHERE id = :id");
        return $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'id' => $id
        ]);
    }

    /**
     * Eliminar servicio
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM servicios WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Contar servicios
     */
    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM servicios")->fetchColumn();
    }
}
?>
