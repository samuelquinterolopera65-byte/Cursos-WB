<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "cursos_wb";

// Conectar al servidor MySQL sin base de datos primero para asegurar que exista
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    die("Error al conectar con el servidor de base de datos o crear la BD: " . $e->getMessage());
}

// Reconectar a la base de datos específica
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos `$dbname`: " . $e->getMessage());
}

// Verificar e inicializar las tablas si no existen
try {
    // 1. Tabla de Roles
    $conn->exec("CREATE TABLE IF NOT EXISTS `roles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB;");

    // 2. Tabla de Usuarios
    $conn->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `rol_id` INT NOT NULL,
        `permisos` TEXT DEFAULT NULL,
        `asignatura` VARCHAR(100) DEFAULT NULL,
        `foto` VARCHAR(255) DEFAULT NULL,
        `ultimo_acceso` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB;");

    // 3. Tabla de Servicios
    $conn->exec("CREATE TABLE IF NOT EXISTS `servicios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `descripcion` TEXT,
        `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00
    ) ENGINE=InnoDB;");

    // 4. Tabla de Cursos
    $conn->exec("CREATE TABLE IF NOT EXISTS `cursos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `titulo` VARCHAR(150) NOT NULL,
        `descripcion` TEXT,
        `imagen` VARCHAR(255) DEFAULT NULL,
        `materiales` TEXT DEFAULT NULL,
        `categoria` VARCHAR(100) DEFAULT NULL,
        `cupo_limite` INT DEFAULT NULL, -- NULL significa ilimitado
        `campos_requeridos` VARCHAR(255) DEFAULT 'nombre,email',
        `estado` TINYINT(1) DEFAULT 1, -- 1 = habilitado, 0 = deshabilitado
        `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 5. Tabla de Inscripciones
    $conn->exec("CREATE TABLE IF NOT EXISTS `inscripciones` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `curso_id` INT NOT NULL,
        `usuario_id` INT NOT NULL,
        `telefono` VARCHAR(50) DEFAULT NULL,
        `edad` INT DEFAULT NULL,
        `empresa` VARCHAR(150) DEFAULT NULL,
        `fecha_inscripcion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_registro` (`curso_id`, `usuario_id`)
    ) ENGINE=InnoDB;");

    // 6. Tabla de Configuraciones
    $conn->exec("CREATE TABLE IF NOT EXISTS `configuraciones` (
        `clave` VARCHAR(50) PRIMARY KEY,
        `valor` TEXT
    ) ENGINE=InnoDB;");

    // Consultas de actualización de BD seguras para tablas existentes (en caso de que las tablas existieran antes de las actualizaciones estructurales)
    try {
        $conn->exec("ALTER TABLE `cursos` ADD COLUMN `campos_requeridos` VARCHAR(255) DEFAULT 'nombre,email'");
    } catch (PDOException $e) {}

    try {
        $conn->exec("ALTER TABLE `cursos` ADD COLUMN `categoria` VARCHAR(100) DEFAULT NULL");
    } catch (PDOException $e) {}

    try {
        $conn->exec("ALTER TABLE `inscripciones` ADD COLUMN `telefono` VARCHAR(50) DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $conn->exec("ALTER TABLE `inscripciones` ADD COLUMN `edad` INT DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $conn->exec("ALTER TABLE `inscripciones` ADD COLUMN `empresa` VARCHAR(150) DEFAULT NULL");
    } catch (PDOException $e) {}

    try {
        $conn->exec("ALTER TABLE `usuarios` ADD COLUMN `permisos` TEXT DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $conn->exec("ALTER TABLE `usuarios` ADD COLUMN `asignatura` VARCHAR(100) DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $conn->exec("ALTER TABLE `usuarios` ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $conn->exec("ALTER TABLE `usuarios` ADD COLUMN `ultimo_acceso` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    } catch (PDOException $e) {}

    // Sembrar roles del LMS con los perfiles de usuario solicitados
    $rolesSeed = [
        [1, 'Administrador'],
        [2, 'Creador de Cursos'],
        [3, 'Profesor'],
        [4, 'Profesor no editor'],
        [5, 'Estudiante']
    ];

    foreach ($rolesSeed as $roleData) {
        $stmt = $conn->prepare("INSERT INTO `roles` (`id`, `nombre`) VALUES (:id, :nombre) ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`)");
        $stmt->execute([':id' => $roleData[0], ':nombre' => $roleData[1]]);
    }

    // Sembrar usuario administrador por defecto si la tabla de usuarios está vacía
    $usersCount = $conn->query("SELECT COUNT(*) FROM `usuarios`")->fetchColumn();
    if ($usersCount == 0) {
        $adminPass = password_hash("admin123", PASSWORD_DEFAULT);
        $adminPerms = "crear_cursos,editar_cursos,eliminar_cursos,gestionar_usuarios,gestionar_servicios,descargar_excel,gestionar_ajustes,instalar_complementos,crear_cuentas,configurar_estetica,reportes_globales,ver_panel_tecnico,ver_administracion_sitio";
        $conn->exec("INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol_id`, `permisos`) VALUES 
            ('Administrador del Sistema', 'admin@cursoswb.com', '$adminPass', 1, '$adminPerms');");
    } else {
        // Asegurar que el administrador sembrado tenga permisos en caso de que la tabla haya sido creada previamente
        $conn->exec("UPDATE `usuarios` SET `permisos` = 'crear_cursos,editar_cursos,eliminar_cursos,gestionar_usuarios,gestionar_servicios,descargar_excel,gestionar_ajustes,instalar_complementos,crear_cuentas,configurar_estetica,reportes_globales,ver_panel_tecnico,ver_administracion_sitio' WHERE `rol_id` = 1 AND (`permisos` IS NULL OR `permisos` = '')");
    }

    // Sembrar servicios iniciales si está vacía
    $servicesCount = $conn->query("SELECT COUNT(*) FROM `servicios`")->fetchColumn();
    if ($servicesCount == 0) {
        $conn->exec("INSERT INTO `servicios` (`nombre`, `descripcion`, `precio`) VALUES 
            ('Mentorías 1-a-1', 'Sesiones individuales de mentoría de desarrollo de software con expertos de la industria.', 49.99),
            ('Soporte Premium 24/7', 'Acceso directo a chat y llamadas para resolver problemas de código y servidores.', 19.99),
            ('Acceso a Comunidad Privada', 'Invitación al canal premium de Discord con canales de networking, ofertas de empleo y tutoriales.', 9.99);");
    }

    // Sembrar cursos iniciales si está vacía
    $coursesCount = $conn->query("SELECT COUNT(*) FROM `cursos`")->fetchColumn();
    if ($coursesCount == 0) {
        $conn->exec("INSERT INTO `cursos` (`titulo`, `descripcion`, `imagen`, `materiales`, `categoria`, `cupo_limite`, `campos_requeridos`, `estado`) VALUES 
            ('Curso de PHP Moderno y PDO', 'Aprende PHP 8 desde las bases hasta la conexión segura a bases de datos con PDO, arquitectura MVC y buenas prácticas.', 'https://images.unsplash.com/photo-1599507593499-a3f7d7d97667?auto=format&fit=crop&w=450&h=300&q=80', 'Manual de PHP en PDF\r\nRepositorio de código en GitHub\r\nEjercicios prácticos de consultas SQL', 'Programación', NULL, 'nombre,email', 1),
            ('Bootstrap 5 Avanzado y Maquetación Web', 'Domina el diseño responsivo, personalización con Sass, animaciones de componentes y maquetación de proyectos reales.', 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=450&h=300&q=80', 'Plantilla HTML base de Bootstrap\r\nHojas de trucos CSS (Cheatsheets)\r\nAcceso a iconos premium', 'Diseño Web', 2, 'nombre,email,telefono', 1),
            ('Base de Datos MySQL y Optimización de Consultas', 'Diseño de bases de datos relacionales, normalización, índices, optimización de queries complejas y backups.', 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?auto=format&fit=crop&w=450&h=300&q=80', 'Diagrama Entidad-Relación de ejemplo\r\nScript SQL con base de datos de pruebas\r\nGuía de optimización en PDF', 'Bases de Datos', 10, 'nombre,email,telefono,edad,empresa', 1);");
    }

    // Sembrar configuraciones por defecto si está vacía
    $configCount = $conn->query("SELECT COUNT(*) FROM `configuraciones`")->fetchColumn();
    if ($configCount == 0) {
        $conn->exec("INSERT INTO `configuraciones` (`clave`, `valor`) VALUES ('logo', '');");
    }

} catch (PDOException $e) {
    die("Error al inicializar la base de datos: " . $e->getMessage());
}
?>
