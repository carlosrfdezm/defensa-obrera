<?php
// admin/recuperar_password.php - Recuperación con PREGUNTA + PIN
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../includes/db.php';

$mensaje = '';
$error = '';
$usuario_encontrado = null;
$paso = 1; // 1: correo, 2: pregunta, 3: PIN, 4: nueva contraseña

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // PASO 1: Verificar correo
    if (isset($_POST['email']) && !isset($_POST['respuesta']) && !isset($_POST['pin'])) {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Por favor, ingresa tu correo electrónico.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico no es válido.';
        } else {
            $stmt = $pdo->prepare("SELECT id, usuario, email, pregunta_seguridad, pin_seguridad FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario_encontrado = $stmt->fetch();
            
            if ($usuario_encontrado) {
                if (empty($usuario_encontrado['pregunta_seguridad']) && empty($usuario_encontrado['pin_seguridad'])) {
                    $error = 'Este usuario no tiene seguridad adicional configurada. Contacta al administrador.';
                    $usuario_encontrado = null;
                } else {
                    $paso = 2;
                }
            } else {
                $error = 'No existe un usuario con este correo electrónico.';
            }
        }
    }
    
    // PASO 2: Verificar respuesta
    if (isset($_POST['respuesta']) && isset($_POST['usuario_id']) && !isset($_POST['pin'])) {
        $respuesta = trim($_POST['respuesta'] ?? '');
        $usuario_id = intval($_POST['usuario_id']);
        
        if (empty($respuesta)) {
            $error = 'Por favor, responde la pregunta de seguridad.';
        } else {
            $stmt = $pdo->prepare("SELECT id, usuario, respuesta_seguridad, pin_seguridad FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($respuesta, $usuario['respuesta_seguridad'])) {
                if (!empty($usuario['pin_seguridad'])) {
                    $paso = 3;
                    $usuario_encontrado = $usuario;
                } else {
                    // Sin PIN, generar token directamente
                    $token = bin2hex(random_bytes(32));
                    $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expira = ? WHERE id = ?");
                    $stmt->execute([$token, $expira, $usuario_id]);
                    $mensaje = '✅ Respuesta correcta. Ahora puedes cambiar tu contraseña.';
                    $paso = 4;
                    $token_valido = $token;
                }
            } else {
                $error = '❌ Respuesta incorrecta. Intenta nuevamente.';
                $paso = 2;
                $stmt = $pdo->prepare("SELECT id, usuario, email, pregunta_seguridad FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario_encontrado = $stmt->fetch();
            }
        }
    }
    
    // PASO 3: Verificar PIN
    if (isset($_POST['pin']) && isset($_POST['usuario_id'])) {
        $pin = trim($_POST['pin'] ?? '');
        $usuario_id = intval($_POST['usuario_id']);
        
        if (empty($pin)) {
            $error = 'Por favor, ingresa tu PIN de seguridad.';
        } elseif (strlen($pin) != 4 || !ctype_digit($pin)) {
            $error = 'El PIN debe ser un número de 4 dígitos.';
        } else {
            $stmt = $pdo->prepare("SELECT id, pin_seguridad FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($pin, $usuario['pin_seguridad'])) {
                $token = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expira = ? WHERE id = ?");
                $stmt->execute([$token, $expira, $usuario_id]);
                $mensaje = '✅ PIN correcto. Ahora puedes cambiar tu contraseña.';
                $paso = 4;
                $token_valido = $token;
            } else {
                $error = '❌ PIN incorrecto. Intenta nuevamente.';
                $paso = 3;
                $stmt = $pdo->prepare("SELECT id, usuario, email, pregunta_seguridad FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario_encontrado = $stmt->fetch();
            }
        }
    }
    
    // PASO 4: Cambiar contraseña
    if (isset($_POST['password_nueva']) && isset($_POST['token'])) {
        $token = $_POST['token'];
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_confirmar = $_POST['password_confirmar'] ?? '';
        
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            $error = 'El enlace ha expirado. Vuelve a intentarlo.';
        } elseif (empty($password_nueva) || empty($password_confirmar)) {
            $error = 'Todos los campos son obligatorios.';
        } elseif ($password_nueva !== $password_confirmar) {
            $error = 'Las contraseñas no coinciden.';
        } elseif (strlen($password_nueva) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } else {
            $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_token_expira = NULL WHERE id = ?");
            $stmt->execute([$hash, $usuario['id']]);
            $mensaje = '✅ Contraseña actualizada correctamente.';
            $paso = 0;
        }
    }
}

$token_actual = $_GET['token'] ?? '';
if (!empty($token_actual)) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
    $stmt->execute([$token_actual]);
    if ($stmt->fetch()) {
        $paso = 4;
        $token_valido = $token_actual;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 450px;
            margin: 50px auto;
            background: var(--blanco);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        .form-container h1 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            text-align: center;
            margin-bottom: 10px;
        }
        .form-container .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--azul-marino);
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: var(--rojo-intenso);
            outline: none;
        }
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: var(--rojo-intenso);
            color: var(--blanco);
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background: #c40d14;
        }
        .btn-volver {
            display: inline-block;
            margin-top: 15px;
            color: var(--azul-marino);
            text-decoration: none;
            font-weight: 600;
        }
        .btn-volver:hover {
            color: var(--rojo-intenso);
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #cce5ff;
            color: #004085;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #b8daff;
        }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid var(--rojo-intenso);
        }
        .user-info p {
            margin: 0;
            color: var(--azul-marino);
        }
        .user-info strong {
            color: var(--rojo-intenso);
        }
        .divider {
            border: none;
            border-top: 2px dashed #e0e0e0;
            margin: 25px 0;
        }
        .pregunta-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FFB900;
            margin: 15px 0;
        }
        .pregunta-box p {
            font-size: 18px;
            font-weight: 600;
            color: var(--azul-marino);
            margin: 0;
        }
        .badge-paso {
            display: inline-block;
            background: var(--azul-marino);
            color: var(--blanco);
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .pin-box {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
            margin: 15px 0;
        }
        .pin-box p {
            font-size: 16px;
            font-weight: 600;
            color: #0c5460;
            margin: 0;
        }
        .pin-box small {
            display: block;
            color: #666;
            font-weight: 400;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div style="min-height:100vh;background:var(--gris-claro);padding:20px;">
        <div class="form-container">
            <h1>🔐 Recuperar Contraseña</h1>
            
            <?php if ($mensaje && $paso == 0): ?>
                <div class="alert-success">
                    <?php echo $mensaje; ?>
                    <br><br>
                    <a href="login.php" class="btn-primary" style="text-align:center;text-decoration:none;display:inline-block;">
                        <i class="fas fa-sign-in-alt"></i> Ir al inicio de sesión
                    </a>
                </div>
            <?php else: ?>
            
            <span class="badge-paso">Paso <?php echo $paso; ?> de 4</span>
            <p class="subtitle">
                <?php
                if ($paso == 1) echo 'Ingresa tu correo para verificar tu identidad.';
                elseif ($paso == 2) echo 'Responde la pregunta de seguridad.';
                elseif ($paso == 3) echo 'Ingresa tu PIN de seguridad.';
                elseif ($paso == 4) echo 'Ingresa tu nueva contraseña.';
                ?>
            </p>
            
            <?php if ($error): ?>
                <div class="alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($paso == 1): ?>
            <!-- PASO 1: Ingresar correo -->
            <form method="POST">
                <div class="form-group">
                    <label>Correo electrónico *</label>
                    <input type="email" name="email" required placeholder="admin@defensaobrera.cl" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i> Verificar identidad
                </button>
            </form>
            <?php endif; ?>
            
            <?php if ($paso == 2 && $usuario_encontrado): ?>
            <!-- PASO 2: Pregunta de seguridad -->
            <form method="POST">
                <input type="hidden" name="usuario_id" value="<?php echo $usuario_encontrado['id']; ?>">
                
                <div class="user-info">
                    <p><i class="fas fa-user"></i> Usuario: <strong><?php echo htmlspecialchars($usuario_encontrado['usuario']); ?></strong></p>
                </div>
                
                <div class="pregunta-box">
                    <p><i class="fas fa-question-circle"></i> <?php echo htmlspecialchars($usuario_encontrado['pregunta_seguridad']); ?></p>
                </div>
                
                <div class="form-group">
                    <label>Tu respuesta *</label>
                    <input type="text" name="respuesta" required placeholder="Escribe tu respuesta" autocomplete="off">
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Verificar respuesta
                </button>
                
                <div style="text-align:center;margin-top:10px;">
                    <a href="recuperar_password.php" class="btn-volver" style="font-size:13px;">
                        <i class="fas fa-arrow-left"></i> Volver a buscar otro correo
                    </a>
                </div>
            </form>
            <?php endif; ?>
            
            <?php if ($paso == 3 && $usuario_encontrado): ?>
            <!-- PASO 3: PIN de seguridad -->
            <form method="POST">
                <input type="hidden" name="usuario_id" value="<?php echo $usuario_encontrado['id']; ?>">
                
                <div class="user-info">
                    <p><i class="fas fa-user"></i> Usuario: <strong><?php echo htmlspecialchars($usuario_encontrado['usuario']); ?></strong></p>
                </div>
                
                <div class="pin-box">
                    <p><i class="fas fa-key"></i> Ingresa tu PIN de seguridad</p>
                    <small>El PIN es un número de 4 dígitos que configuraste al crear tu usuario.</small>
                </div>
                
                <div class="form-group">
                    <label>PIN de seguridad *</label>
                    <input type="password" name="pin" required maxlength="4" placeholder="1234" 
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,4)">
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Verificar PIN
                </button>
                
                <div style="text-align:center;margin-top:10px;">
                    <a href="recuperar_password.php" class="btn-volver" style="font-size:13px;">
                        <i class="fas fa-arrow-left"></i> Volver a buscar otro correo
                    </a>
                </div>
            </form>
            <?php endif; ?>
            
            <?php if ($paso == 4 && isset($token_valido)): ?>
            <!-- PASO 4: Nueva contraseña -->
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_valido); ?>">
                
                <div class="alert-info">
                    <i class="fas fa-info-circle"></i> 
                    El enlace es válido por <strong>30 minutos</strong>.
                </div>
                
                <div class="form-group">
                    <label>Nueva contraseña *</label>
                    <input type="password" name="password_nueva" required minlength="6" placeholder="Mínimo 6 caracteres">
                    <small style="color:#999;">Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label>Confirmar nueva contraseña *</label>
                    <input type="password" name="password_confirmar" required placeholder="Repite la nueva contraseña">
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Actualizar contraseña
                </button>
            </form>
            <?php endif; ?>
            
            <div style="text-align:center;margin-top:15px;">
                <a href="login.php" class="btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                </a>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</body>
</html>