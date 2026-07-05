<?php
require_once '../config/db.php';
require_once '../models/Curso.php';
require_once '../models/Usuario.php';

session_start();

// Verificar que el usuario sea administrador
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

$cursoModel = new Curso($conn);

// Obtener ID de curso
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de curso no especificado.");
}

$id = intval($_GET['id']);
$course = $cursoModel->getById($id);

if (!$course) {
    die("Curso no encontrado.");
}

// Construir la URL pública del curso para el QR
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$dir = dirname($_SERVER['PHP_SELF']);
$rootDir = str_replace('/manage', '', $dir);
$rootDir = rtrim($rootDir, '/\\');
$course_url = $protocol . $domainName . $rootDir . '/index.php?curso_id=' . $course['id'];

// URL de la API de QR Code para generar el código QR
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=" . urlencode($course_url);

// Preparar el nombre seguro del archivo PDF
$safe_title = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $course['titulo']);
$pdf_filename = 'QR_Curso_' . $safe_title . '.pdf';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargar QR - <?php echo htmlspecialchars($course['titulo']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f5e9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        /* Barra superior de acciones (visible en pantalla, oculta al generar PDF) */
        .action-bar {
            width: 100%;
            max-width: 640px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .back-link {
            color: #1a73e8;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s;
        }
        .back-link:hover { opacity: 0.7; }

        .btn-download {
            background: linear-gradient(135deg, #1a73e8, #1558b0);
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            padding: 11px 26px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(26, 115, 232, 0.35);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(26, 115, 232, 0.45);
        }
        .btn-download:active { transform: translateY(0); }
        .btn-download.loading { opacity: 0.75; pointer-events: none; }

        /* Flyer — área que se convierte en PDF */
        #flyer {
            width: 600px;
            background: #ffffff;
            border: 3px solid #1a73e8;
            border-radius: 22px;
            padding: 44px 40px 36px;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        }

        .flyer-badge {
            background: rgba(26, 115, 232, 0.08);
            color: #1a73e8;
            padding: 7px 18px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: inline-block;
            margin-bottom: 14px;
        }

        .flyer-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1e1b4b;
            line-height: 1.2;
            margin-bottom: 12px;
        }

        .flyer-desc {
            font-size: 1rem;
            color: #64748b;
            max-width: 480px;
            margin: 0 auto 26px;
            line-height: 1.55;
        }

        .qr-wrapper {
            background: radial-gradient(circle, rgba(79,70,229,0.04) 0%, transparent 72%);
            padding: 18px;
            display: inline-block;
            border-radius: 18px;
            margin-bottom: 26px;
        }

        .qr-image {
            width: 220px;
            height: 220px;
            border: 6px solid #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.09);
            display: block;
        }

        .divider {
            border: none;
            border-top: 1px dashed #d1d5db;
            margin: 10px 0 20px;
        }

        .scan-text {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1a73e8;
            margin-bottom: 6px;
        }

        .instructions {
            font-size: 0.875rem;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Barra de acciones superior -->
    <div class="action-bar" id="action-bar">
        <a href="index.php?tab=cursos" class="back-link">
            <i class="bi bi-arrow-left-circle"></i> Volver al panel
        </a>
        <button class="btn-download" id="btn-pdf" onclick="descargarPDF()">
            <i class="bi bi-file-earmark-pdf-fill"></i>
            Descargar PDF
        </button>
    </div>

    <!-- Flyer que se convertirá en PDF -->
    <div id="flyer">
        <span class="flyer-badge"><i class="bi bi-journal-bookmark-fill"></i> Curso Especializado</span>
        <h1 class="flyer-title"><?php echo htmlspecialchars($course['titulo']); ?></h1>
        <p class="flyer-desc"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($course['descripcion']), 0, 170, "…")); ?></p>

        <div class="qr-wrapper">
            <!-- Pre-cargar la imagen del QR como base64 para que html2pdf la capture correctamente -->
            <img id="qr-img" class="qr-image"
                 src="<?php echo $qr_api_url; ?>&format=png"
                 crossorigin="anonymous"
                 alt="Código QR de Registro">
        </div>

        <hr class="divider">
        <p class="scan-text"><i class="bi bi-phone"></i> ¡Escanea para inscribirte!</p>
        <p class="instructions">Apunta la cámara de tu celular al código QR para abrir el formulario de inscripción del curso directamente.</p>
    </div>

    <!-- html2pdf.js — genera PDFs desde HTML directamente en el navegador -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        function descargarPDF() {
            const btn = document.getElementById('btn-pdf');
            const actionBar = document.getElementById('action-bar');
            const flyer = document.getElementById('flyer');

            // Indicar estado de carga
            btn.classList.add('loading');
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

            // Ocultar la barra de acciones para que no quede en el PDF
            actionBar.style.display = 'none';

            const opciones = {
                margin:       [10, 10, 10, 10],
                filename:     '<?php echo $pdf_filename; ?>',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  {
                    scale: 2,
                    useCORS: true,
                    logging: false
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opciones).from(flyer).save().then(function() {
                // Restaurar la barra y el botón tras la descarga
                actionBar.style.display = '';
                btn.classList.remove('loading');
                btn.innerHTML = '<i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF';
            });
        }
    </script>
</body>
</html>
