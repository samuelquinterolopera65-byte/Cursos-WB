<?php
class Inscripcion {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    /**
     * Registrar un usuario en un curso con campos dinámicos opcionales
     */
    public function register($curso_id, $usuario_id, $telefono = null, $edad = null, $empresa = null) {
        $stmt = $this->db->prepare("INSERT INTO inscripciones (curso_id, usuario_id, telefono, edad, empresa) VALUES (:curso_id, :usuario_id, :telefono, :edad, :empresa)");
        return $stmt->execute([
            'curso_id' => $curso_id,
            'usuario_id' => $usuario_id,
            'telefono' => $telefono,
            'edad' => !empty($edad) ? intval($edad) : null,
            'empresa' => $empresa
        ]);
    }

    /**
     * Contar inscripciones para un curso específico
     */
    public function countByCurso($curso_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM inscripciones WHERE curso_id = :curso_id");
        $stmt->execute(['curso_id' => $curso_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Listar todas las inscripciones, opcionalmente filtradas por curso
     */
    public function getAll($curso_id = null) {
        $query = "SELECT 
                    i.id AS inscripcion_id, 
                    COALESCE(NULLIF(u.nombre, ''), CONCAT('Participante #', i.id)) AS usuario_nombre, 
                    u.email AS usuario_email, 
                    c.nombre AS curso_titulo, 
                    i.telefono AS usuario_telefono,
                    i.edad AS usuario_edad,
                    i.empresa AS usuario_empresa,
                    i.fecha_inscripcion AS fecha_registro,
                    r.nombre AS rol_nombre
                  FROM inscripciones i
                  JOIN usuarios u ON i.usuario_id = u.id
                  JOIN lc_cursos c ON i.curso_id = c.id
                  JOIN roles r ON u.rol_id = r.id";
                  
        if (!is_null($curso_id) && $curso_id > 0) {
            $query .= " WHERE i.curso_id = :curso_id";
        }
        
        $query .= " ORDER BY i.fecha_inscripcion DESC";
        
        if (!is_null($curso_id) && $curso_id > 0) {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['curso_id' => $curso_id]);
            return $stmt->fetchAll();
        } else {
            return $this->db->query($query)->fetchAll();
        }
    }

    /**
     * Obtener los últimos 10 usuarios registrados en cursos, opcionalmente filtrados por curso
     */
    public function getLastTen($curso_id = null) {
        $query = "SELECT 
                    i.id,
                    COALESCE(NULLIF(u.nombre, ''), CONCAT('Participante #', i.id)) AS user_name,
                    u.email AS user_email,
                    COALESCE(c.nombre, 'Curso eliminado') AS course_title,
                    i.fecha_inscripcion
                  FROM inscripciones i
                  LEFT JOIN usuarios u ON i.usuario_id = u.id
                  LEFT JOIN lc_cursos c ON i.curso_id = c.id";
                  
        if (!is_null($curso_id) && $curso_id > 0) {
            $query .= " WHERE i.curso_id = :curso_id";
        }
        
        $query .= " ORDER BY i.fecha_inscripcion DESC LIMIT 10";
        
        if (!is_null($curso_id) && $curso_id > 0) {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['curso_id' => $curso_id]);
            return $stmt->fetchAll();
        } else {
            return $this->db->query($query)->fetchAll();
        }
    }

    /**
     * Contar el total de inscripciones
     */
    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn();
    }
}
?>
