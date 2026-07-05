<?php
require_once 'config/db.php';
require_once 'models/Usuario.php';
require_once 'models/Ajustes.php';

session_start();

$usuarioModel = new Usuario($conn);
$ajustesModel = new Ajustes($conn);

$error = '';
$logoPath = $ajustesModel->get('logo');

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 1) {
        header("Location: manage/index.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        try {
            $user = $usuarioModel->authenticate($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol_id'];
                $_SESSION['user_role_name'] = $user['rol_nombre'];

                if ($user['rol_id'] == 1) {
                    header("Location: manage/index.php");
                    exit;
                } else {
                    header("Location: index.php");
                    exit;
                }
            } else {
                $error = 'Correo electrónico o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el sistema: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Iniciar Sesión - Cursos-WB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-4">
                <a href="index.php" class="text-decoration-none d-inline-block mb-3">
                    <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
                        <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Cursos-WB Logo" style="height: 48px; max-width: 180px; object-fit: contain;">
                    <?php else: ?>
                        <span class="fs-3 fw-bold text-primary"><i class="bi bi-journal-bookmark-fill me-2"></i>Cursos-WB</span>
                    <?php endif; ?>
                </a>
                <h4 class="fw-bold">¡Bienvenido de nuevo!</h4>
                <p class="text-muted small">Ingresa tus credenciales para acceder</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control form-control-premium" placeholder="ejemplo@correo.com" required autofocus>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label small fw-bold">Contraseña</label>
                    </div>
                    <input type="password" name="password" id="password" class="form-control form-control-premium" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary-premium w-100 py-2.5">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
