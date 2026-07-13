<?php
require_once dirname(__DIR__) . '/Helpers/functions.php';
require_once dirname(__DIR__, 2) . '/models/Inscripcion.php';

class HomeController {
    private PDO $db;
    private CourseModel $courseModel;
    private Inscripcion $inscriptionModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->courseModel = new CourseModel($db);
        $this->inscriptionModel = new Inscripcion($db);
    }

    public function landing(): void {
        $courses = $this->courseModel->getPublishedCourses(3);
        $categories = $this->courseModel->getCategories();
        $this->render('landing', compact('courses', 'categories'));
    }

    public function dashboard(): void {
        $stats = $this->courseModel->getDashboardStats();
        $courses = $this->courseModel->getAllCourses();
        $enrolledCourses = [];
        if (!empty($_SESSION['user_id'])) {
            $enrolledCourses = $this->getEnrolledCoursesForUser((int) $_SESSION['user_id']);
        }
        $this->render('dashboard', compact('stats', 'courses', 'enrolledCourses'));
    }

    public function catalog(): void {
        $courses = $this->courseModel->getAllCourses();
        $categories = $this->courseModel->getCategories();
        $this->render('catalog', compact('courses', 'categories'));
    }

    public function courseDetail(): void {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $course = $id > 0 ? $this->courseModel->getCourseById($id) : null;
        $isEnrolled = false;
        if ($course && !empty($_SESSION['user_id'])) {
            $isEnrolled = $this->isUserEnrolled((int) $_SESSION['user_id'], (int) $course['id']);
        }
        $this->render('course_detail', compact('course', 'isEnrolled'));
    }

    public function enrollCourse(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $courseId = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
        if ($courseId <= 0) {
            header('Location: index.php?action=catalog');
            exit;
        }

        $this->inscriptionModel->register($courseId, (int) $_SESSION['user_id']);
        header('Location: index.php?action=course&id=' . $courseId . '&enrolled=1');
        exit;
    }

    public function learn(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        $enrolledCourses = $this->getEnrolledCoursesForUser((int) $_SESSION['user_id']);
        $this->render('learn', compact('enrolledCourses'));
    }

    public function courseProgress(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $course = $id > 0 ? $this->courseModel->getCourseById($id) : null;
        $isEnrolled = false;
        if ($course && !empty($_SESSION['user_id'])) {
            $isEnrolled = $this->isUserEnrolled((int) $_SESSION['user_id'], (int) $course['id']);
        }
        if (!$isEnrolled) {
            header('Location: index.php?action=learn');
            exit;
        }
        $this->render('course_progress', compact('course', 'isEnrolled'));
    }

    public function certificate(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $course = $id > 0 ? $this->courseModel->getCourseById($id) : null;
        $isEnrolled = $course && $this->isUserEnrolled((int) $_SESSION['user_id'], (int) $course['id']);
        if (!$isEnrolled) {
            header('Location: index.php?action=learn');
            exit;
        }
        $studentName = $_SESSION['user_name'] ?? 'Estudiante';
        $this->render('certificate', compact('course', 'studentName'));
    }

    public function adminCourses(): void {
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
            $payload = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'codigo' => trim($_POST['codigo'] ?? ''),
                'descripcion_corta' => trim($_POST['descripcion_corta'] ?? ''),
                'descripcion_larga' => trim($_POST['descripcion_larga'] ?? ''),
                'categoria_id' => $_POST['categoria_id'] ?? null,
                'instructor' => trim($_POST['instructor'] ?? ''),
                'duracion' => trim($_POST['duracion'] ?? ''),
                'horas' => trim($_POST['horas'] ?? 0),
                'nivel' => trim($_POST['nivel'] ?? 'Básico'),
                'idioma' => trim($_POST['idioma'] ?? 'Español'),
                'precio' => trim($_POST['precio'] ?? 0),
                'gratuito' => isset($_POST['gratuito']) ? 1 : 0,
                'certificado' => isset($_POST['certificado']) ? 1 : 0,
                'objetivos' => trim($_POST['objetivos'] ?? ''),
                'competencias' => trim($_POST['competencias'] ?? ''),
                'requisitos' => trim($_POST['requisitos'] ?? ''),
                'bibliografia' => trim($_POST['bibliografia'] ?? ''),
                'etiquetas' => trim($_POST['etiquetas'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'borrador')
            ];

            if (!empty($payload['nombre']) && !empty($payload['codigo'])) {
                $this->courseModel->createCourse($payload);
                $success = 'Curso creado correctamente.';
            }
        }

        $courses = $this->courseModel->getAllCourses();
        $categories = $this->courseModel->getCategories();
        $this->render('admin_courses', compact('courses', 'categories', 'success'));
    }

    private function isUserEnrolled(int $userId, int $courseId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM inscripciones WHERE usuario_id = :user_id AND curso_id = :course_id");
        $stmt->execute([':user_id' => $userId, ':course_id' => $courseId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function getEnrolledCoursesForUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT i.curso_id, c.nombre AS nombre, c.descripcion_larga AS descripcion, c.imagen, c.estado, i.fecha_inscripcion
            FROM inscripciones i
            LEFT JOIN lc_cursos c ON c.id = i.curso_id
            WHERE i.usuario_id = :user_id
            ORDER BY i.fecha_inscripcion DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function render(string $view, array $data = []): void {
        extract($data);
        ob_start();
        require VIEW_PATH . '/' . $view . '.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout.php';
    }
}
