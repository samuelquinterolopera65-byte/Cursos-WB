<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($conn) || !$conn instanceof PDO) {
    throw new Exception('No se pudo inicializar la conexión a la base de datos.');
}

$conn->exec("CREATE TABLE IF NOT EXISTS lc_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$conn->exec("CREATE TABLE IF NOT EXISTS lc_cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    codigo VARCHAR(60) NOT NULL UNIQUE,
    imagen VARCHAR(255) NULL,
    descripcion_corta VARCHAR(255) NULL,
    descripcion_larga TEXT NULL,
    categoria_id INT NULL,
    instructor VARCHAR(120) NULL,
    duracion VARCHAR(60) NULL,
    horas INT DEFAULT 0,
    nivel VARCHAR(40) DEFAULT 'Básico',
    idioma VARCHAR(40) DEFAULT 'Español',
    precio DECIMAL(10,2) DEFAULT 0.00,
    gratuito TINYINT(1) DEFAULT 1,
    certificado TINYINT(1) DEFAULT 1,
    objetivos TEXT NULL,
    competencias TEXT NULL,
    requisitos TEXT NULL,
    bibliografia TEXT NULL,
    etiquetas TEXT NULL,
    estado VARCHAR(30) DEFAULT 'borrador',
    publicado_en TIMESTAMP NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES lc_categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB");

try {
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS materiales TEXT NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS cupo_limite INT NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS campos_requeridos VARCHAR(255) DEFAULT 'nombre,email'");
} catch (PDOException $e) {}

$conn->exec("CREATE TABLE IF NOT EXISTS lc_modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    orden_num INT DEFAULT 1,
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES lc_cursos(id) ON DELETE CASCADE
) ENGINE=InnoDB");

$conn->exec("CREATE TABLE IF NOT EXISTS lc_inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    estado VARCHAR(30) DEFAULT 'activa',
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES lc_cursos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registro (curso_id, usuario_id)
) ENGINE=InnoDB");

$conn->exec("CREATE TABLE IF NOT EXISTS lc_progreso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    porcentaje INT DEFAULT 0,
    tiempo_invertido INT DEFAULT 0,
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES lc_cursos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progreso (curso_id, usuario_id)
) ENGINE=InnoDB");

// 6. Tabla de Unidades (dentro de módulos)
$conn->exec("CREATE TABLE IF NOT EXISTS lc_unidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    orden_num INT DEFAULT 1,
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES lc_modulos(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 7. Tabla de Lecciones
$conn->exec("CREATE TABLE IF NOT EXISTS lc_lecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unidad_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    tipo VARCHAR(40) DEFAULT 'texto', -- video, audio, pdf, doc, texto, html, iframe, youtube, vimeo, codigo, scorm, zip, link
    contenido_texto TEXT NULL,
    contenido_url VARCHAR(255) NULL,
    duracion_segundos INT DEFAULT 0,
    orden_num INT DEFAULT 1,
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unidad_id) REFERENCES lc_unidades(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 8. Tabla de Recursos
$conn->exec("CREATE TABLE IF NOT EXISTS lc_recursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leccion_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    url VARCHAR(255) NOT NULL,
    tipo VARCHAR(40) NULL,
    tamano_bytes INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leccion_id) REFERENCES lc_lecciones(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 9. Tabla de Actividades
$conn->exec("CREATE TABLE IF NOT EXISTS lc_actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leccion_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    descripcion TEXT NULL,
    tipo VARCHAR(40) DEFAULT 'tarea', -- foro, tarea, proyecto, wiki, encuesta, chat, debate, archivo
    es_grupal TINYINT(1) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leccion_id) REFERENCES lc_lecciones(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 10. Tabla de Evaluaciones
$conn->exec("CREATE TABLE IF NOT EXISTS lc_evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leccion_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    descripcion TEXT NULL,
    tiempo_limite_minutos INT DEFAULT 0,
    intentos_permitidos INT DEFAULT 1,
    calificacion_minima DECIMAL(5,2) DEFAULT 60.00,
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leccion_id) REFERENCES lc_lecciones(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 11. Tabla de Preguntas
$conn->exec("CREATE TABLE IF NOT EXISTS lc_preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    enunciado TEXT NOT NULL,
    tipo VARCHAR(40) DEFAULT 'opcion_multiple', -- opcion_multiple, verdadera_falso, abierta
    orden_num INT DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluacion_id) REFERENCES lc_evaluaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 12. Tabla de Opciones
$conn->exec("CREATE TABLE IF NOT EXISTS lc_opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    texto TEXT NOT NULL,
    es_correcta TINYINT(1) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pregunta_id) REFERENCES lc_preguntas(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 13. Tabla de Intentos de Evaluación
$conn->exec("CREATE TABLE IF NOT EXISTS lc_intentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    calificacion DECIMAL(5,2) NULL,
    comenzado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completado_en TIMESTAMP NULL,
    estado VARCHAR(30) DEFAULT 'en_progreso',
    FOREIGN KEY (evaluacion_id) REFERENCES lc_evaluaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 14. Tabla de Certificados (Plantillas)
$conn->exec("CREATE TABLE IF NOT EXISTS lc_certificados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    plantilla_html TEXT NULL,
    firma_instructor VARCHAR(255) NULL,
    firma_lms VARCHAR(255) NULL,
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES lc_cursos(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 15. Tabla de Certificados Emitidos
$conn->exec("CREATE TABLE IF NOT EXISTS lc_certificados_emitidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificado_id INT NOT NULL,
    usuario_id INT NOT NULL,
    codigo_verificacion VARCHAR(80) NOT NULL UNIQUE,
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (certificado_id) REFERENCES lc_certificados(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 16. Progreso por Lección
$conn->exec("CREATE TABLE IF NOT EXISTS lc_progreso_leccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leccion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    completado TINYINT(1) DEFAULT 0,
    fecha_completado TIMESTAMP NULL,
    ultimo_tiempo_segundos INT DEFAULT 0,
    FOREIGN KEY (leccion_id) REFERENCES lc_lecciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progreso_leccion (leccion_id, usuario_id)
) ENGINE=InnoDB");

// 17. Favoritos
$conn->exec("CREATE TABLE IF NOT EXISTS lc_favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES lc_cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorito (curso_id, usuario_id)
) ENGINE=InnoDB");

// 18. Notificaciones
$conn->exec("CREATE TABLE IF NOT EXISTS lc_notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo VARCHAR(50) DEFAULT 'sistema',
    leido TINYINT(1) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 19. Comentarios e Valoraciones
$conn->exec("CREATE TABLE IF NOT EXISTS lc_comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    valoracion INT DEFAULT 5,
    estado VARCHAR(30) DEFAULT 'aprobado',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES lc_cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 20. Artículos del Blog / Noticias
$conn->exec("CREATE TABLE IF NOT EXISTS lc_blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) NULL,
    autor_id INT NOT NULL,
    estado VARCHAR(30) DEFAULT 'publicado',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB");

// 21. Eventos y Webinars
$conn->exec("CREATE TABLE IF NOT EXISTS lc_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    fecha_evento DATETIME NOT NULL,
    tipo VARCHAR(50) DEFAULT 'webinar',
    enlace VARCHAR(255) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// 22. Rutas de Aprendizaje
$conn->exec("CREATE TABLE IF NOT EXISTS lc_rutas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    imagen VARCHAR(255) NULL,
    estado TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// 23. Relación de Cursos en Rutas
$conn->exec("CREATE TABLE IF NOT EXISTS lc_rutas_cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT NOT NULL,
    curso_id INT NOT NULL,
    orden_num INT DEFAULT 1,
    FOREIGN KEY (ruta_id) REFERENCES lc_rutas(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES lc_cursos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_ruta_curso (ruta_id, curso_id)
) ENGINE=InnoDB");

// 24. Biblioteca Multimedia
$conn->exec("CREATE TABLE IF NOT EXISTS lc_multimedia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    tipo VARCHAR(80) NOT NULL,
    tamano INT DEFAULT 0,
    etiquetas VARCHAR(255) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// 25. Auditoría / Logs
$conn->exec("CREATE TABLE IF NOT EXISTS lc_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(100) NOT NULL,
    detalle TEXT NULL,
    ip VARCHAR(45) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// 26. Ajustes / Configuración Global
$conn->exec("CREATE TABLE IF NOT EXISTS lc_configuracion (
    clave VARCHAR(80) PRIMARY KEY,
    valor TEXT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$categoryCount = $conn->query("SELECT COUNT(*) FROM lc_categorias")->fetchColumn();
if ((int) $categoryCount === 0) {
    $conn->exec("INSERT INTO lc_categorias (nombre, descripcion, estado) VALUES
        ('Programación', 'Cursos de desarrollo web y backend.', 1),
        ('Diseño', 'Diseño visual y experiencia de usuario.', 1),
        ('Negocios', 'Formación ejecutiva y productividad.', 1)");
}
