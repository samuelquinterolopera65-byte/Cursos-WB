<?php
class Ajustes {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    /**
     * Obtener una configuración por su clave
     */
    public function get($clave) {
        try {
            $stmt = $this->db->prepare("SELECT valor FROM configuraciones WHERE clave = :clave");
            $stmt->execute(['clave' => $clave]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Establecer una configuración por su clave (inserta o actualiza si está duplicada)
     */
    public function set($clave, $valor) {
        try {
            $stmt = $this->db->prepare("INSERT INTO configuraciones (clave, valor) VALUES (:clave, :valor) 
                ON DUPLICATE KEY UPDATE valor = :valor_update");
            return $stmt->execute([
                'clave' => $clave,
                'valor' => $valor,
                'valor_update' => $valor
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
