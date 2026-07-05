<?php
// Inicializar configuraciones dentro del encabezado público
if (isset($conn)) {
    require_once __DIR__ . '/../../models/Ajustes.php';
    $ajustesModel = new Ajustes($conn);
    $logoPath = $ajustesModel->get('logo');
} else {
    $logoPath = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Catálogo de cursos y capacitación - Cursos-WB" />
    <title>Cursos-WB - Catálogo de Cursos</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Iconos de Bootstrap (v1.10.5) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- CSS del tema principal (incluye Bootstrap) -->
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <style>
        html, body {
            min-height: 100%;
            margin: 0 !important;
            background: #eef3fb !important;
        }
        body {
            background: #eef3fb !important;
        }
        .navbar,
        .public-navbar,
        .public-navbar-container,
        .public-navbar-brand,
        .public-navbar-brand * {
            background: transparent !important;
        }
        .public-navbar {
            position: relative;
            overflow: hidden;
            background: #eef3fb !important;
            border-bottom: none !important;
        }
            .public-navbar .public-navbar-brand {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.75rem;
            color: #1a1a2e !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .public-navbar .public-navbar-brand img {
            max-height: 42px;
            width: auto;
            display: block;
            filter: saturate(1.1) brightness(1.05);
        }
        .hero-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .hero-brand .hero-logo {
            max-height: 70px;
            width: auto;
            display: block;
            object-fit: contain;
            filter: saturate(1.05) brightness(1.05);
        }
        .hero-brand .hero-brand-text {
            display: inline-flex;
            flex-direction: column;
            text-align: left;
        }
        .hero-brand .hero-brand-text span:first-child {
            font-size: 0.95rem;
            font-weight: 700;
            color: rgba(255,255,255,0.88);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .hero-brand .hero-brand-text span:last-child {
            font-size: 1.05rem;
            color: rgba(255,255,255,0.78);
        }
        .public-navbar::before,
        .public-navbar::after {
            content: '';
            position: absolute;
            width: 220%;
            height: 220%;
            top: -55%;
            left: -40%;
            z-index: -1;
            filter: blur(60px);
            opacity: 0.75;
            pointer-events: none;
        }
        .public-navbar::before {
            background: radial-gradient(circle at 10% 20%, rgba(26, 115, 232, 0.24), transparent 32%);
            animation: moveGradient 14s linear infinite;
        }
        .public-navbar::after {
            background: radial-gradient(circle at 80% 35%, rgba(46, 125, 50, 0.18), transparent 28%);
            animation: moveGradientReverse 16s linear infinite;
        }
        .public-navbar-logo {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            max-height: 72px;
            width: auto;
            object-fit: contain;
            mix-blend-mode: multiply !important;
            opacity: 0.95 !important;
            filter: brightness(1.04) contrast(1.08) !important;
        }
        .hero-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .hero-title .public-navbar-logo {
            max-height: 72px;
            width: auto;
        }
        .hero-title .hero-logo-background {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            backdrop-filter: blur(8px);
        }
        .public-navbar-brand {
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
        }
        .public-navbar-container {
            display: block !important;
            background: #eef3fb !important;
        }
        @keyframes moveGradient {
            0% { transform: translate(0, 0); }
            50% { transform: translate(18%, -10%); }
            100% { transform: translate(0, 0); }
        }
        @keyframes moveGradientReverse {
            0% { transform: translate(0, 0); }
            50% { transform: translate(-12%, 8%); }
            100% { transform: translate(0, 0); }
        }
    </style>
</head>
<body>