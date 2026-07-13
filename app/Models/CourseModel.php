<?php
class CourseModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getPublishedCourses(int $limit = 6): array {
        $courses = [];

        foreach ($this->fetchCoursesFromSource(true) as $course) {
            $courses[] = $this->normalizeCourse($course, $course['__source'] ?? 'modern');
        }

        return array_slice($courses, 0, $limit);
    }

    public function getAllCourses(): array {
        $courses = [];

        foreach ($this->fetchCoursesFromSource(false) as $course) {
            $courses[] = $this->normalizeCourse($course, $course['__source'] ?? 'modern');
        }

        return $courses;
    }

    public function getCourseById(int $id): ?array {
        $source = $this->detectCourseSource(false);

        if ($source === 'legacy') {
            $stmt = $this->db->prepare("SELECT id, titulo, descripcion, imagen, categoria, estado, creado_en, materiales FROM cursos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($course) {
                $course['__source'] = 'legacy';
                return $this->normalizeCourse($course, 'legacy');
            }
            return null;
        }

        $stmt = $this->db->prepare("SELECT c.*, cat.nombre AS categoria_nombre
            FROM lc_cursos c
            LEFT JOIN lc_categorias cat ON cat.id = c.categoria_id
            WHERE c.id = :id");
        $stmt->execute([':id' => $id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($course) {
            $course['__source'] = 'modern';
            return $this->normalizeCourse($course, 'modern');
        }

        return null;
    }

    public function getCategories(): array {
        $categories = [];
        if (!$this->tableExists('lc_categorias')) {
            $stmt = $this->db->query("SELECT DISTINCT categoria AS nombre FROM cursos WHERE categoria IS NOT NULL AND TRIM(categoria) <> '' ORDER BY categoria ASC");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $category) {
                $categories[] = ['id' => null, 'nombre' => $category['nombre']];
            }
            return $categories;
        }

        $stmt = $this->db->query("SELECT * FROM lc_categorias WHERE estado = 1 ORDER BY nombre ASC");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $category) {
            $categories[] = $category;
        }

        return $categories;
    }

    private function fetchCoursesFromSource(bool $publishedOnly): array {
        $source = $this->detectCourseSource($publishedOnly);

        if ($source === 'legacy') {
            $sql = "SELECT id, titulo, descripcion, imagen, categoria, estado, creado_en, materiales FROM cursos";
            if ($publishedOnly) {
                $sql .= " WHERE estado = 1";
            }
            $sql .= " ORDER BY creado_en DESC";

            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $row['__source'] = 'legacy';
            }
            return $rows;
        }

        $sql = "SELECT c.*, cat.nombre AS categoria_nombre
            FROM lc_cursos c
            LEFT JOIN lc_categorias cat ON cat.id = c.categoria_id";
        if ($publishedOnly) {
            $sql .= " WHERE c.estado = 'publicado'";
        }
        $sql .= " ORDER BY c.publicado_en DESC, c.creado_en DESC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['__source'] = 'modern';
        }
        return $rows;
    }

    private function detectCourseSource(bool $publishedOnly): string {
        if (!$this->tableExists('lc_cursos') && !$this->tableExists('cursos')) {
            return 'modern';
        }

        if (!$this->tableExists('lc_cursos')) {
            return 'legacy';
        }

        if (!$this->tableExists('cursos')) {
            return 'modern';
        }

        $count = (int) $this->db->query("SELECT COUNT(*) FROM lc_cursos")->fetchColumn();
        if ($count > 0) {
            return 'modern';
        }

        return 'legacy';
    }

    private function tableExists(string $table): bool {
        $stmt = $this->db->query("SHOW TABLES LIKE '{$table}'");
        return $stmt->fetchColumn() !== false;
    }

    public function getDashboardStats(): array {
        $stats = [];
        $stats['cursos_activos'] = (int) $this->db->query("SELECT COUNT(*) FROM lc_cursos WHERE estado = 'publicado'")->fetchColumn();
        $stats['cursos_publicados'] = $stats['cursos_activos'];
        $stats['cursos_borrador'] = (int) $this->db->query("SELECT COUNT(*) FROM lc_cursos WHERE estado = 'borrador'")->fetchColumn();
        $stats['estudiantes_inscritos'] = (int) $this->db->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn();
        $stats['horas_impartidas'] = (int) $this->db->query("SELECT COALESCE(SUM(horas), 0) FROM lc_cursos")->fetchColumn();
        $stats['certificados_emitidos'] = 0;
        return $stats;
    }

    private function normalizeCourse(array $course, string $source): array {
        if ($source === 'legacy') {
            $description = trim((string) ($course['descripcion'] ?? ''));
            return [
                'id' => (int) ($course['id'] ?? 0),
                'nombre' => trim((string) ($course['titulo'] ?? 'Curso sin título')),
                'codigo' => '',
                'imagen' => trim((string) ($course['imagen'] ?? '')),
                'descripcion_corta' => mb_substr($description, 0, 140, 'UTF-8'),
                'descripcion_larga' => $description,
                'categoria_id' => null,
                'categoria_nombre' => trim((string) ($course['categoria'] ?? 'Sin categoría')) ?: 'Sin categoría',
                'instructor' => 'Instructor disponible',
                'duracion' => 'Duración flexible',
                'horas' => 0,
                'nivel' => 'Básico',
                'idioma' => 'Español',
                'precio' => 0.00,
                'gratuito' => 1,
                'certificado' => 1,
                'estado' => (string) ($course['estado'] ?? '1'),
                'publicado_en' => null,
                'creado_en' => $course['creado_en'] ?? null,
                'materiales' => trim((string) ($course['materiales'] ?? '')),
            ];
        }

        $descriptionShort = trim((string) ($course['descripcion_corta'] ?? ''));
        $descriptionLong = trim((string) ($course['descripcion_larga'] ?? ''));
        $description = $descriptionShort !== '' ? $descriptionShort : $descriptionLong;

        return [
            'id' => (int) ($course['id'] ?? 0),
            'nombre' => trim((string) ($course['nombre'] ?? 'Curso sin título')),
            'codigo' => trim((string) ($course['codigo'] ?? '')),
            'imagen' => trim((string) ($course['imagen'] ?? '')),
            'descripcion_corta' => mb_substr($description, 0, 140, 'UTF-8'),
            'descripcion_larga' => $descriptionLong,
            'categoria_id' => $course['categoria_id'] ?? null,
            'categoria_nombre' => trim((string) ($course['categoria_nombre'] ?? 'Sin categoría')) ?: 'Sin categoría',
            'instructor' => trim((string) ($course['instructor'] ?? 'Instructor disponible')) ?: 'Instructor disponible',
            'duracion' => trim((string) ($course['duracion'] ?? 'Duración flexible')) ?: 'Duración flexible',
            'horas' => (int) ($course['horas'] ?? 0),
            'nivel' => trim((string) ($course['nivel'] ?? 'Básico')) ?: 'Básico',
            'idioma' => trim((string) ($course['idioma'] ?? 'Español')) ?: 'Español',
            'precio' => (float) ($course['precio'] ?? 0.00),
            'gratuito' => (int) ($course['gratuito'] ?? 1),
            'certificado' => (int) ($course['certificado'] ?? 1),
            'estado' => (string) ($course['estado'] ?? 'borrador'),
            'publicado_en' => $course['publicado_en'] ?? null,
            'creado_en' => $course['creado_en'] ?? null,
            'materiales' => trim((string) ($course['materiales'] ?? '')),
        ];
    }

    public function createCourse(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO lc_cursos (
            nombre, codigo, imagen, descripcion_corta, descripcion_larga, categoria_id, instructor, duracion, horas, nivel, idioma, precio, gratuito, certificado, objetivos, competencias, requisitos, bibliografia, etiquetas, estado
        ) VALUES (
            :nombre, :codigo, :imagen, :descripcion_corta, :descripcion_larga, :categoria_id, :instructor, :duracion, :horas, :nivel, :idioma, :precio, :gratuito, :certificado, :objetivos, :competencias, :requisitos, :bibliografia, :etiquetas, :estado)");

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':codigo' => $data['codigo'],
            ':imagen' => $data['imagen'] ?? '',
            ':descripcion_corta' => $data['descripcion_corta'] ?? '',
            ':descripcion_larga' => $data['descripcion_larga'] ?? '',
            ':categoria_id' => !empty($data['categoria_id']) ? (int) $data['categoria_id'] : null,
            ':instructor' => $data['instructor'] ?? 'Por definir',
            ':duracion' => $data['duracion'] ?? '4 semanas',
            ':horas' => !empty($data['horas']) ? (int) $data['horas'] : 0,
            ':nivel' => $data['nivel'] ?? 'Básico',
            ':idioma' => $data['idioma'] ?? 'Español',
            ':precio' => !empty($data['precio']) ? (float) $data['precio'] : 0.00,
            ':gratuito' => !empty($data['gratuito']) ? 1 : 0,
            ':certificado' => !empty($data['certificado']) ? 1 : 0,
            ':objetivos' => $data['objetivos'] ?? '',
            ':competencias' => $data['competencias'] ?? '',
            ':requisitos' => $data['requisitos'] ?? '',
            ':bibliografia' => $data['bibliografia'] ?? '',
            ':etiquetas' => $data['etiquetas'] ?? '',
            ':estado' => $data['estado'] ?? 'borrador'
        ]);
    }
}
