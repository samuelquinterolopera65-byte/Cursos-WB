<?php
class Curso {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    /**
     * Obtener todos los cursos con la cantidad total de inscritos
     */
    public function getAll() {
        $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS inscritos 
                  FROM cursos c 
                  ORDER BY c.creado_en DESC";
         return $this->db->query($query)->fetchAll();
    }

    /**
     * Obtener todos los cursos habilitados/públicos con la cantidad total de inscritos
     */
    public function getEnabled() {
        $query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS inscritos 
                  FROM cursos c 
                  WHERE c.estado = 1
                  ORDER BY c.creado_en DESC";
        return $this->db->query($query)->fetchAll();
    }

    /**
     * Obtener curso por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM cursos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo curso
     */
    public function create($titulo, $descripcion, $imagen, $materiales, $cupo_limite, $campos_requeridos, $estado) {
        $stmt = $this->db->prepare("INSERT INTO cursos (titulo, descripcion, imagen, materiales, cupo_limite, campos_requeridos, estado) VALUES (:titulo, :descripcion, :imagen, :materiales, :cupo_limite, :campos_requeridos, :estado)");
        return $stmt->execute([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'imagen' => $imagen,
            'materiales' => $materiales,
            'cupo_limite' => $cupo_limite,
            'campos_requeridos' => $campos_requeridos,
            'estado' => $estado
        ]);
    }

    /**
     * Actualizar un curso existente
     */
    public function update($id, $titulo, $descripcion, $imagen, $materiales, $cupo_limite, $campos_requeridos, $estado) {
        $stmt = $this->db->prepare("UPDATE cursos SET titulo = :titulo, descripcion = :descripcion, imagen = :imagen, materiales = :materiales, cupo_limite = :cupo_limite, campos_requeridos = :campos_requeridos, estado = :estado WHERE id = :id");
        return $stmt->execute([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'imagen' => $imagen,
            'materiales' => $materiales,
            'cupo_limite' => $cupo_limite,
            'campos_requeridos' => $campos_requeridos,
            'estado' => $estado,
            'id' => $id
        ]);
    }

    /**
     * Alternar el estado habilitado/deshabilitado de un curso
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE cursos SET estado = 1 - estado WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Eliminar un curso
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM cursos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Contar el total de cursos
     */
    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
    }

    /**
     * Contar los cursos activos/habilitados
     */
    public function countActive() {
        return $this->db->query("SELECT COUNT(*) FROM cursos WHERE estado = 1")->fetchColumn();
    }
}
?>
