<?php
// admin/login.php - Página de login con diseño mejorado
session_start();
require_once '../includes/db.php';

// Verificar si ya está logueado
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_usuario'] = $user['usuario'];
            $_SESSION['admin_nombre'] = $user['nombre'] ?? $user['usuario'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administración - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* ============================================
           RESET Y BASE
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #001B35;
            background-image: 
                radial-gradient(ellipse at 10% 20%, rgba(229, 22, 29, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 80%, rgba(255, 185, 0, 0.10) 0%, transparent 50%),
                linear-gradient(135deg, #001B35 0%, #002a4a 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* --- FONDO DECORATIVO --- */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 70% 30%, rgba(229, 22, 29, 0.03) 0%, transparent 30%);
            animation: rotarFondo 60s linear infinite;
            pointer-events: none;
        }
        
        @keyframes rotarFondo {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* --- PARTICULAS DECORATIVAS --- */
        .particula {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            pointer-events: none;
            animation: flotar 20s ease-in-out infinite;
        }
        
        .particula:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }
        
        .particula:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
            animation-delay: 5s;
        }
        
        .particula:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }
        
        @keyframes flotar {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.3; }
            25% { transform: translate(30px, -20px) scale(1.1); opacity: 0.5; }
            50% { transform: translate(-20px, 30px) scale(0.9); opacity: 0.3; }
            75% { transform: translate(20px, 10px) scale(1.05); opacity: 0.4; }
        }
        
        /* ============================================
           CONTENEDOR PRINCIPAL
           ============================================ */
        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }
        
        /* ============================================
           TARJETA DE LOGIN
           ============================================ */
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 45px 40px 40px;
            box-shadow: 
                0 25px 60px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 35px 80px rgba(0, 0, 0, 0.6);
        }
        
        /* --- BORDE DECORATIVO SUPERIOR --- */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #E5161D, #FFB900, #E5161D);
            background-size: 200% 100%;
            animation: moverBorde 3s ease-in-out infinite;
        }
        
        @keyframes moverBorde {
            0%, 100% { background-position: 0% 0%; }
            50% { background-position: 100% 0%; }
        }
        
        /* ============================================
           LOGO
           ============================================ */
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo img {
            max-width: 180px;
            height: auto;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }
        
        .login-logo img:hover {
            transform: scale(1.02);
        }
        
        .login-logo .logo-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #001B35;
            margin-top: 10px;
        }
        
        .login-logo .logo-text span {
            color: #E5161D;
        }
        
        .login-logo .logo-sub {
            font-size: 13px;
            color: #888;
            font-weight: 400;
            margin-top: 2px;
        }
        
        /* ============================================
           TÍTULO
           ============================================ */
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #001B35;
            margin-bottom: 5px;
        }
        
        .login-title p {
            font-size: 14px;
            color: #888;
        }
        
        /* ============================================
           ALERTAS
           ============================================ */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.4s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-danger i {
            font-size: 18px;
            color: #dc2626;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .alert-success i {
            font-size: 18px;
            color: #16a34a;
        }
        
        /* ============================================
           FORMULARIO
           ============================================ */
        .login-form .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .login-form .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #001B35;
            margin-bottom: 6px;
        }
        
        .login-form .form-group label .required {
            color: #E5161D;
        }
        
        .login-form .form-group .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .login-form .form-group .input-wrapper i {
            position: absolute;
            left: 16px;
            color: #aaa;
            font-size: 16px;
            transition: color 0.3s;
            z-index: 2;
            pointer-events: none;
        }
        
        .login-form .form-group .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Open Sans', sans-serif;
            transition: all 0.3s;
            background: #f9fafb;
            color: #1a1a2e;
        }
        
        .login-form .form-group .input-wrapper input:focus {
            outline: none;
            border-color: #E5161D;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(229, 22, 29, 0.10);
        }
        
        .login-form .form-group .input-wrapper input:focus + i,
        .login-form .form-group .input-wrapper input:focus ~ i {
            color: #E5161D;
        }
        
        .login-form .form-group .input-wrapper input::placeholder {
            color: #bbb;
            font-size: 14px;
        }
        
        /* --- INPUT DE CONTRASEÑA CON BOTÓN MOSTRAR --- */
        .login-form .form-group .input-wrapper .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #aaa;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            transition: color 0.3s;
            z-index: 2;
        }
        
        .login-form .form-group .input-wrapper .toggle-password:hover {
            color: #001B35;
        }
        
        /* ============================================
           OPCIONES ADICIONALES
           ============================================ */
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .login-options .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }
        
        .login-options .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #E5161D;
            cursor: pointer;
        }
        
        .login-options .forgot-link {
            font-size: 14px;
            color: #E5161D;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .login-options .forgot-link:hover {
            color: #c40d14;
            text-decoration: underline;
        }
        
        /* ============================================
           BOTÓN DE ENVÍO
           ============================================ */
        .login-form .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #E5161D, #c40d14);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .login-form .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.6s ease;
        }
        
        .login-form .btn-submit:hover::before {
            left: 100%;
        }
        
        .login-form .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 22, 29, 0.35);
        }
        
        .login-form .btn-submit:active {
            transform: translateY(0px);
            box-shadow: 0 4px 15px rgba(229, 22, 29, 0.25);
        }
        
        .login-form .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-form .btn-submit i {
            font-size: 18px;
        }
        
        /* ============================================
           PIE DE PÁGINA
           ============================================ */
        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .login-footer p {
            font-size: 13px;
            color: #999;
        }
        
        .login-footer p i {
            color: #E5161D;
            margin: 0 3px;
        }
        
        .login-footer .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 10px;
        }
        
        .login-footer .footer-links a {
            font-size: 13px;
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .login-footer .footer-links a:hover {
            color: #E5161D;
        }
        
        /* ============================================
           CREDENCIALES DE PRUEBA (solo en desarrollo)
           ============================================ */
        .dev-credentials {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 20px;
            border: 1px dashed #d1d5db;
            text-align: center;
        }
        
        .dev-credentials p {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .dev-credentials .cred {
            display: inline-flex;
            gap: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #001B35;
        }
        
        .dev-credentials .cred span {
            background: #e5e7eb;
            padding: 2px 12px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px 30px;
                border-radius: 20px;
            }
            
            .login-logo img {
                max-width: 140px;
            }
            
            .login-title h2 {
                font-size: 20px;
            }
            
            .login-form .form-group .input-wrapper input {
                padding: 12px 14px 12px 42px;
                font-size: 14px;
            }
            
            .login-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .login-form .btn-submit {
                font-size: 15px;
                padding: 14px;
            }
            
            .dev-credentials .cred {
                flex-direction: column;
                gap: 5px;
            }
        }
        
        @media (max-width: 380px) {
            .login-card {
                padding: 20px 15px 25px;
            }
            
            .login-logo img {
                max-width: 120px;
            }
        }
    </style>
</head>
<body>
    <!-- Decoración de fondo -->
    <div class="particula"></div>
    <div class="particula"></div>
    <div class="particula"></div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="login-logo">
                <img src="../img/logo-defensa-obrera.png" alt="Defensa Obrera">
                <div class="logo-text">DEFENSA <span>OBRERA</span></div>
                <div class="logo-sub">Panel de Administración</div>
            </div>
            
            <!-- Título -->
            <div class="login-title">
                <h2>👋 ¡Bienvenido!</h2>
                <p>Ingresa tus credenciales para acceder</p>
            </div>
            
            <!-- Alertas -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Sesión cerrada correctamente.
                </div>
            <?php endif; ?>
            
            <!-- Formulario -->
            <form method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="usuario">
                        <i class="fas fa-user"></i> Usuario <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user-circle"></i>
                        <input 
                            type="text" 
                            id="usuario" 
                            name="usuario" 
                            placeholder="Ingresa tu usuario"
                            value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                            required
                            autofocus
                            autocomplete="username"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Contraseña <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-key"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Ingresa tu contraseña"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="login-options">
                    <label class="remember-me">
                        <input type="checkbox" name="recordar" id="recordar">
                        Recordarme
                    </label>
                    <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Ingresar al panel
                </button>
            </form>
            
            <!-- Credenciales de desarrollo (solo visibles localmente) 
            <?php if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1'): ?>
            <div class="dev-credentials">
                <p>🔐 <strong>Credenciales de desarrollo</strong></p>
                <div class="cred">
                    <span>👤 Usuario: admin</span>
                    <span>🔑 Contraseña: admin123</span>
                </div>
            </div> -->
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="login-footer">
                <p>
                    <i class="fas fa-shield-alt"></i>
                    Acceso seguro · Defensa Obrera
                </p>
                <div class="footer-links">
                    <a href="../index.php"><i class="fas fa-home"></i> Volver al sitio</a>
                    <a href="#"><i class="fas fa-lock"></i> Política de privacidad</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // ============================================
        // MOSTRAR/OCULTAR CONTRASEÑA
        // ============================================
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        // ============================================
        // PREVENIR DOBLE ENVÍO
        // ============================================
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            
            // Si hay error, se habilita nuevamente (el PHP redirige o muestra error)
            // Re-habilitamos después de un tiempo por si el servidor tarda
            setTimeout(() => {
                if (!submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Ingresar al panel';
                }
            }, 5000);
        });
        
        // ============================================
        // RECORDAR USUARIO (localStorage)
        // ============================================
        const rememberCheckbox = document.getElementById('recordar');
        const usuarioInput = document.getElementById('usuario');
        
        // Cargar usuario guardado
        if (localStorage.getItem('recordar_usuario')) {
            usuarioInput.value = localStorage.getItem('recordar_usuario');
            rememberCheckbox.checked = true;
        }
        
        // Guardar usuario al enviar
        form.addEventListener('submit', function() {
            if (rememberCheckbox.checked) {
                localStorage.setItem('recordar_usuario', usuarioInput.value);
            } else {
                localStorage.removeItem('recordar_usuario');
            }
        });
        
        // ============================================
        // ENTER PARA ENVIAR
        // ============================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement && (activeElement.id === 'usuario' || activeElement.id === 'password')) {
                    form.submit();
                }
            }
        });
    </script>
</body>
</html>