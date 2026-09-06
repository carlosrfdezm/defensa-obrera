<?php
// admin/restablecer_password.php - Restablecer contraseña con token
session_start();

// Si ya está logueado, redirigir al panel
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../includes/db.php';

$token = $_GET['token'] ?? '';
$mensaje = '';
$error = '';
$token_valido = false;
$usuario_id = null;

// Verificar token
if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id, email FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        $token_valido = true;
        $usuario_id = $usuario['id'];
    } else {
        $error = 'El enlace de recuperación no es válido o ha expirado.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    if (empty($password_nueva) || empty($password_confirmar)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password_nueva !== $password_confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password_nueva) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // Actualizar contraseña
        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_token_expira = NULL WHERE id = ?");
        $stmt->execute([$hash, $usuario_id]);
        
        $mensaje = '✅ Contraseña restablecida correctamente.';
        $token_valido = false; // Ocultar formulario
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Administración</title>
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
    </style>
</head>
<body>
    <div style="min-height:100vh;background:var(--gris-claro);padding:20px;">
        <div class="form-container">
            <h1>🔑 Restablecer Contraseña</h1>
            
            <?php if ($mensaje): ?>
                <div class="alert-success">
                    <?php echo $mensaje; ?>
                    <br><br>
                    <a href="login.php" class="btn-volver">
                        <i class="fas fa-arrow-left"></i> Ir al inicio de sesión
                    </a>
                </div>
            <?php elseif ($error): ?>
                <div class="alert-danger"><?php echo $error; ?></div>
                <div style="text-align:center;margin-top:15px;">
                    <a href="recuperar_password.php" class="btn-volver">
                        <i class="fas fa-arrow-left"></i> Solicitar nuevo enlace
                    </a>
                </div>
            <?php elseif ($token_valido): ?>
                <p class="subtitle">Ingresa tu nueva contraseña.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nueva contraseña *</label>
                        <input type="password" name="password_nueva" required minlength="6">
                        <small style="color:#999;">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar nueva contraseña *</label>
                        <input type="password" name="password_confirmar" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Restablecer contraseña
                    </button>
                </form>
            <?php else: ?>
                <div class="alert-danger">El enlace de recuperación no es válido o ha expirado.</div>
                <div style="text-align:center;margin-top:15px;">
                    <a href="recuperar_password.php" class="btn-volver">
                        <i class="fas fa-arrow-left"></i> Solicitar nuevo enlace
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>