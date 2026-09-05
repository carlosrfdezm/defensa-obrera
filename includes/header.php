<?php
// includes/header.php - VERSIÓN DEFINITIVA
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defensa Obrera - Abogados Laborales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ============================================
           ESTILOS EXCLUSIVOS DEL HEADER
           ============================================ */
        
        /* --- TOP BAR --- */
        .top-bar {
            background: #1a1a1a !important;
            color: #FFFFFF !important;
            padding: 5px 0 !important;
            font-size: 13px !important;
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
        }
        
        .top-bar-content {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
        }
        
        .top-bar a {
            color: #FFFFFF !important;
            text-decoration: none !important;
            transition: color 0.3s !important;
        }
        
        .top-bar a:hover {
            color: #FFB900 !important;
        }
        
        .top-bar-left a,
        .top-bar-left span {
            color: #FFFFFF !important;
            text-decoration: none !important;
            margin-right: 20px !important;
            font-size: 13px !important;
        }
        
        .top-bar-left i {
            margin-right: 6px !important;
        }
        
        .top-bar-right a {
            color: #FFFFFF !important;
            margin-left: 15px !important;
            text-decoration: none !important;
            font-size: 15px !important;
        }
        
        .top-bar-right a:hover {
            color: #FFB900 !important;
        }
        
        /* --- HEADER PRINCIPAL (NEGRO) --- */
        .main-header {
            background: #000000 !important;
            padding: 8px 0 !important;
            box-shadow: 0 2px 20px rgba(0,0,0,0.4) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
            border-bottom: 3px solid #E5161D !important;
        }
        
        .header-content {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }
        
        /* --- LOGO MÁS GRANDE --- */
        .logo img {
            max-height: 80px !important;
            width: auto !important;
            height: auto !important;
            display: block !important;
            transition: transform 0.3s ease !important;
        }
        
        .logo img:hover {
            transform: scale(1.03) !important;
        }
        
        /* --- MENÚ CON TEXTO BLANCO --- */
        .main-nav {
            display: flex !important;
            align-items: center !important;
        }
        
        .nav-menu {
            display: flex !important;
            list-style: none !important;
            gap: 25px !important;
            margin: 0 !important;
            padding: 0 !important;
            align-items: center !important;
        }
        
        .nav-menu a {
            color: #FFFFFF !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            transition: color 0.3s !important;
            padding: 8px 0 !important;
        }
        
        .nav-menu a:hover {
            color: #FFB900 !important;
        }
        
        .nav-menu a.active {
            color: #FFB900 !important;
        }
        
        /* --- BOTÓN WHATSAPP --- */
        .header-actions {
            display: flex !important;
            align-items: center !important;
        }
        
        .btn-whatsapp {
            background: #25D366 !important;
            color: #FFFFFF !important;
            padding: 10px 22px !important;
            border-radius: 30px !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            transition: all 0.3s !important;
            font-size: 14px !important;
            white-space: nowrap !important;
        }
        
        .btn-whatsapp:hover {
            background: #1DA851 !important;
            transform: scale(1.03) !important;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.35) !important;
        }
        
        /* --- BOTÓN MENÚ MÓVIL --- */
        .mobile-menu-toggle {
            display: none !important;
            background: none !important;
            border: none !important;
            font-size: 28px !important;
            color: #FFFFFF !important;
            cursor: pointer !important;
            padding: 5px !important;
        }
        
        .mobile-menu-toggle:hover {
            color: #FFB900 !important;
        }
        
        /* ============================================
           RESPONSIVE SOLO DEL HEADER
           ============================================ */
        
        @media (max-width: 992px) {
            .logo img {
                max-height: 65px !important;
            }
            
            .nav-menu {
                gap: 15px !important;
            }
            
            .nav-menu a {
                font-size: 13px !important;
            }
            
            .btn-whatsapp {
                padding: 8px 16px !important;
                font-size: 13px !important;
            }
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block !important;
            }
            
            .nav-menu {
                display: none !important;
                flex-direction: column !important;
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                width: 100% !important;
                background: #000000 !important;
                padding: 20px !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
                gap: 10px !important;
                border-top: 2px solid #E5161D !important;
            }
            
            .nav-menu.open {
                display: flex !important;
            }
            
            .nav-menu a {
                color: #FFFFFF !important;
                font-size: 16px !important;
                padding: 12px 15px !important;
                border-radius: 8px !important;
                text-align: center !important;
                border-bottom: 1px solid rgba(255,255,255,0.05) !important;
                width: 100% !important;
            }
            
            .nav-menu a:hover {
                background: rgba(255, 255, 255, 0.05) !important;
                color: #FFB900 !important;
            }
            
            .logo img {
                max-height: 50px !important;
            }
            
            .btn-whatsapp span {
                display: none !important;
            }
            
            .btn-whatsapp {
                padding: 8px 14px !important;
            }
            
            .top-bar-left a,
            .top-bar-left span {
                font-size: 11px !important;
                margin-right: 10px !important;
            }
        }
        
        @media (max-width: 480px) {
            .logo img {
                max-height: 42px !important;
            }
            
            .btn-whatsapp {
                padding: 6px 12px !important;
            }
            
            .top-bar-left a,
            .top-bar-left span {
                font-size: 10px !important;
                margin-right: 8px !important;
            }
            
            .top-bar-right a {
                font-size: 12px !important;
                margin-left: 8px !important;
            }
            
            .nav-menu a {
                font-size: 14px !important;
                padding: 10px 15px !important;
            }
        }
    </style>
</head>
<body>
    <!-- Barra superior -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <a href="tel:+56973847746"><i class="fas fa-phone"></i> +56 9 7384 7746</a>
                    <a href="mailto:abogados@defensaobrera.cl"><i class="fas fa-envelope"></i> abogados@defensaobrera.cl</a>
                    <span><i class="fas fa-clock"></i> 08:00 - 16:00</span>
                </div>
                <div class="top-bar-right">
                    <a href="https://www.facebook.com/defensaobrera" class="social-link" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/defensa_obrera" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://wa.me/56973847746" class="social-link whatsapp-link" target="_blank"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Encabezado principal -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- LOGO -->
                <div class="logo">
                    <a href="index.php">
                        <img src="img/logo-defensa-obrera.png" alt="Defensa Obrera">
                    </a>
                </div>
                
                <!-- NAVEGACIÓN -->
                <nav class="main-nav">
                    <button class="mobile-menu-toggle" aria-label="Menú">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="nav-menu">
                        <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Inicio</a></li>
                        <li><a href="servicios.php" <?php echo basename($_SERVER['PHP_SELF']) == 'servicios.php' ? 'class="active"' : ''; ?>>Servicios legales</a></li>
                        <li><a href="como-trabajamos.php" <?php echo basename($_SERVER['PHP_SELF']) == 'como-trabajamos.php' ? 'class="active"' : ''; ?>>Cómo trabajamos</a></li>
                        <li><a href="equipo.php" <?php echo basename($_SERVER['PHP_SELF']) == 'equipo.php' ? 'class="active"' : ''; ?>>Equipo</a></li>
                        <li><a href="resultados.php" <?php echo basename($_SERVER['PHP_SELF']) == 'resultados.php' ? 'class="active"' : ''; ?>>Resultados</a></li>
                        <li><a href="recursos.php" <?php echo basename($_SERVER['PHP_SELF']) == 'recursos.php' ? 'class="active"' : ''; ?>>Recursos</a></li>
                        <li><a href="contacto.php" <?php echo basename($_SERVER['PHP_SELF']) == 'contacto.php' ? 'class="active"' : ''; ?>>Contacto</a></li>
                    </ul>
                </nav>
                
                <!-- WHATSAPP -->
                <div class="header-actions">
                    <a href="https://wa.me/56973847746" class="btn-whatsapp" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </header>