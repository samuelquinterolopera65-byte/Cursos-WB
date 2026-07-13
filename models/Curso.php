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
        if ($this->prefersLegacyTable()) {
            $query = "SELECT c.id, c.titulo, c.descripcion, c.imagen, c.materiales, c.categoria, c.cupo_limite, c.campos_requeridos, c.estado, c.creado_en,
                    (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS inscritos
                  FROM cursos c
                  ORDER BY c.creado_en DESC";
            return $this->db->query($query)->fetchAll();
        }

        $query = "SELECT c.*, 
                    c.nombre AS titulo, 
                    c.descripcion_larga AS descripcion,
                    CASE WHEN c.estado = 'publicado' THEN 1 ELSE 0 END AS estado,
                    (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS inscritos 
                  FROM lc_cursos c 
                  ORDER BY c.creado_en DESC";
         return $this->db->query($query)->fetchAll();
    }

    /**
     * Obtener todos los cursos habilitados/públicos con la cantidad total de inscritos
     */
    public function getEnabled() {
        if ($this->prefersLegacyTable()) {
            $query = "SELECT c.id, c.titulo, c.descripcion, c.imagen, c.materiales, c.categoria, c.cupo_limite, c.campos_requeridos, c.estado, c.creado_en,
                    (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS inscritos
                  FROM cursos c
                  WHERE c.estado = 1
                  ORDER BY c.creado_en DESC";
            return $this->db->query($query)->fetchAll();
        }

        $query = "SELECT c.*, 
                    c.nombre AS titulo, 
                    c.descripcion_larga AS descripcion,
                    1 AS estado,
                    (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) AS inscritos 
                  FROM lc_cursos c 
                  WHERE c.estado = 'publicado'
                  ORDER BY c.creado_en DESC";
        return $this->db->query($query)->fetchAll();
    }

    /**
     * Obtener curso por ID
     */
    public function getById($id) {
        if ($this->prefersLegacyTable()) {
            $stmt = $this->db->prepare("SELECT id, titulo, descripcion, imagen, materiales, categoria, cupo_limite, campos_requeridos, estado, creado_en FROM cursos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        }

        $stmt = $this->db->prepare("SELECT *, nombre AS titulo, descripcion_larga AS descripcion, CASE WHEN estado = 'publicado' THEN 1 ELSE 0 END AS estado FROM lc_cursos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo curso
     */
    public function create($titulo, $descripcion, $imagen, $materiales, $categoria, $cupo_limite, $campos_requeridos, $estado) {
        if ($this->prefersLegacyTable()) {
            $estadoValue = ($estado == 1) ? 1 : 0;
            $stmt = $this->db->prepare("INSERT INTO cursos (titulo, descripcion, imagen, materiales, categoria, cupo_limite, campos_requeridos, estado) VALUES (:titulo, :descripcion, :imagen, :materiales, :categoria, :cupo_limite, :campos_requeridos, :estado)");
            return $stmt->execute([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'imagen' => $imagen,
                'materiales' => $materiales,
                'categoria' => $categoria,
                'cupo_limite' => $cupo_limite,
                'campos_requeridos' => $campos_requeridos,
                'estado' => $estadoValue
            ]);
        }

        $estadoStr = ($estado == 1) ? 'publicado' : 'borrador';
        $stmt = $this->db->prepare("INSERT INTO lc_cursos (nombre, descripcion_larga, imagen, materiales, categoria, cupo_limite, campos_requeridos, estado) VALUES (:titulo, :descripcion, :imagen, :materiales, :categoria, :cupo_limite, :campos_requeridos, :estado)");
        return $stmt->execute([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'imagen' => $imagen,
            'materiales' => $materiales,
            'categoria' => $categoria,
            'cupo_limite' => $cupo_limite,
            'campos_requeridos' => $campos_requeridos,
            'estado' => $estadoStr
        ]);
    }

    /**
     * Actualizar un curso existente
     */
    public function update($id, $titulo, $descripcion, $imagen, $materiales, $categoria, $cupo_limite, $campos_requeridos, $estado) {
        if ($this->prefersLegacyTable()) {
            $estadoValue = ($estado == 1) ? 1 : 0;
            $stmt = $this->db->prepare("UPDATE cursos SET titulo = :titulo, descripcion = :descripcion, imagen = :imagen, materiales = :materiales, categoria = :categoria, cupo_limite = :cupo_limite, campos_requeridos = :campos_requeridos, estado = :estado WHERE id = :id");
            return $stmt->execute([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'imagen' => $imagen,
                'materiales' => $materiales,
                'categoria' => $categoria,
                'cupo_limite' => $cupo_limite,
                'campos_requeridos' => $campos_requeridos,
                'estado' => $estadoValue,
                'id' => $id
            ]);
        }

        $estadoStr = ($estado == 1) ? 'publicado' : 'borrador';
        $stmt = $this->db->prepare("UPDATE lc_cursos SET nombre = :titulo, descripcion_larga = :descripcion, imagen = :imagen, materiales = :materiales, categoria = :categoria, cupo_limite = :cupo_limite, campos_requeridos = :campos_requeridos, estado = :estado WHERE id = :id");
        return $stmt->execute([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'imagen' => $imagen,
            'materiales' => $materiales,
            'categoria' => $categoria,
            'cupo_limite' => $cupo_limite,
            'campos_requeridos' => $campos_requeridos,
            'estado' => $estadoStr,
            'id' => $id
        ]);
    }

    /**
     * Alternar el estado habilitado/deshabilitado de un curso
     */
    public function toggleStatus($id) {
        if ($this->prefersLegacyTable()) {
            $stmt = $this->db->prepare("UPDATE cursos SET estado = CASE WHEN estado = 1 THEN 0 ELSE 1 END WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }

        $stmt = $this->db->prepare("UPDATE lc_cursos SET estado = CASE WHEN estado = 'publicado' THEN 'borrador' ELSE 'publicado' END WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Eliminar un curso
     */
    public function delete($id) {
        if ($this->prefersLegacyTable()) {
            $stmt = $this->db->prepare("DELETE FROM cursos WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }

        $stmt = $this->db->prepare("DELETE FROM lc_cursos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Contar el total de cursos
     */
    public function countAll() {
        if ($this->prefersLegacyTable()) {
            return $this->db->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
        }
        return $this->db->query("SELECT COUNT(*) FROM lc_cursos")->fetchColumn();
    }

    /**
     * Contar los cursos activos/habilitados
     */
    public function countActive() {
        if ($this->prefersLegacyTable()) {
            return $this->db->query("SELECT COUNT(*) FROM cursos WHERE estado = 1")->fetchColumn();
        }
        return $this->db->query("SELECT COUNT(*) FROM lc_cursos WHERE estado = 'publicado'")->fetchColumn();
    }

    private function prefersLegacyTable() {
        if (!$this->tableExists('lc_cursos')) {
            return $this->tableExists('cursos');
        }

        if (!$this->tableExists('cursos')) {
            return false;
        }

        $modernCount = (int) $this->db->query("SELECT COUNT(*) FROM lc_cursos")->fetchColumn();
        return $modernCount === 0;
    }

    private function tableExists($table) {
        $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
        return $stmt->fetchColumn() !== false;
    }
}
?>
