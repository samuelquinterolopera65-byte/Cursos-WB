<?php
require_once __DIR__ . '/app/Config/app.php';
require_once __DIR__ . '/app/Config/database.php';
require_once __DIR__ . '/app/Models/CourseModel.php';
require_once __DIR__ . '/app/Controllers/HomeController.php';

session_start();

$controller = new HomeController($conn);
$action = $_GET['action'] ?? 'landing';

switch ($action) {
    case 'catalog':
        $controller->catalog();
        break;
    case 'dashboard':
        $controller->dashboard();
        break;
    case 'learn':
        $controller->learn();
        break;
    case 'course-progress':
        $controller->courseProgress();
        break;
    case 'course':
        $controller->courseDetail();
        break;
    case 'enroll':
        $controller->enrollCourse();
        break;
    case 'admin-courses':
        $controller->adminCourses();
        break;
    case 'certificate':
        $controller->certificate();
        break;
    default:
        $controller->landing();
        break;
}

