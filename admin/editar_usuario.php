<?php
// admin/editar_usuario.php - Editar usuario CON PREGUNTA + PIN (Menú incluido)
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Verificar que el usuario actual sea admin
$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$usuario_actual = $stmt->fetch();

if ($usuario_actual['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: usuarios.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = $_POST['rol'] ?? 'editor';
    $activo = isset($_POST['activo']) ? 1 : 0;
    $password = $_POST['password'] ?? '';
    
    $pregunta_seguridad = trim($_POST['pregunta_seguridad'] ?? '');
    $respuesta_seguridad = trim($_POST['respuesta_seguridad'] ?? '');
    $pin_seguridad = trim($_POST['pin_seguridad'] ?? '');
    
    if (empty($email)) {
        $error = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ?";
        $params = [$nombre, $email, $rol, $activo];
        
        if (!empty($pregunta_seguridad) && !empty($respuesta_seguridad)) {
            $respuesta_hash = password_hash($respuesta_seguridad, PASSWORD_DEFAULT);
            $sql .= ", pregunta_seguridad = ?, respuesta_seguridad = ?";
            $params[] = $pregunta_seguridad;
            $params[] = $respuesta_hash;
        } elseif (empty($pregunta_seguridad) && isset($_POST['eliminar_pregunta'])) {
            $sql .= ", pregunta_seguridad = NULL, respuesta_seguridad = NULL";
        }
        
        if (!empty($pin_seguridad)) {
            if (strlen($pin_seguridad) != 4 || !ctype_digit($pin_seguridad)) {
                $error = 'El PIN debe ser un número de 4 dígitos.';
            } else {
                $pin_hash = password_hash($pin_seguridad, PASSWORD_DEFAULT);
                $sql .= ", pin_seguridad = ?";
                $params[] = $pin_hash;
            }
        } elseif (isset($_POST['eliminar_pin'])) {
            $sql .= ", pin_seguridad = NULL";
        }
        
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = ?";
                $params[] = $hash;
            }
        }
        
        if (empty($error)) {
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $mensaje = '✅ Usuario actualizado correctamente.';
            
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .security-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FFB900;
            margin: 20px 0;
        }
        .security-box h3 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            margin-top: 0;
            margin-bottom: 15px;
        }
        .security-box .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .security-box .pin-box {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
            margin-top: 15px;
        }
        .security-box .pin-box h5 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #0c5460;
        }
        .btn-eliminar-pregunta {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-eliminar-pregunta:hover {
            background: #c82333;
        }
        .btn-eliminar-pin {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-eliminar-pin:hover {
            background: #c82333;
        }
        .pregunta-existente {
            background: #d4edda;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin-bottom: 15px;
        }
        .pregunta-existente p {
            margin: 0;
            color: #155724;
        }
        .pregunta-existente strong {
            color: #0b5e1a;
        }
        .pin-existente {
            background: #d1ecf1;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #bee5eb;
            margin-bottom: 15px;
        }
        .pin-existente p {
            margin: 0;
            color: #0c5460;
        }
        .badge-seguridad {
            display: inline-block;
            background: #FFB900;
            color: var(--azul-marino);
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 10px;
        }
        .badge-pin {
            display: inline-block;
            background: #17a2b8;
            color: #fff;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 10px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* ============================================
           MENÚ LATERAL COMPLETO
           ============================================ */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .admin-sidebar {
            width: 250px;
            background: var(--azul-marino);
            color: var(--blanco);
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .admin-logo img {
            max-width: 150px;
            margin-bottom: 30px;
        }
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            margin-bottom: 5px;
        }
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: var(--blanco);
        }
        .admin-nav a i {
            width: 20px;
        }
        .admin-main {
            margin-left: 250px;
            flex: 1;
            background: var(--gris-claro);
        }
        .admin-header {
            background: var(--blanco);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
        }
        .admin-header h1 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            margin: 0;
        }
        .admin-content {
            padding: 30px;
        }
        .admin-card {
            background: var(--blanco);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .admin-card h2 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-primary {
            background: var(--rojo-intenso);
            color: var(--blanco);
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background: #c40d14;
        }
        .btn-secondary {
            display: inline-block;
            padding: 12px 30px;
            background: var(--gris-claro);
            color: var(--azul-marino);
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
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
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--rojo-intenso);
            outline: none;
        }
        .form-group input[disabled] {
            background: #f0f0f0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- MENÚ LATERAL COMPLETO -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img src="../img/logo-defensa-obrera.png" alt="Defensa Obrera">
            </div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="servicios.php"><i class="fas fa-gavel"></i> Servicios</a>
                <a href="equipo.php"><i class="fas fa-users"></i> Equipo</a>
                <a href="resultados.php"><i class="fas fa-chart-bar"></i> Resultados</a>
                <a href="recursos.php"><i class="fas fa-book"></i> Recursos</a>
                <a href="mensajes.php"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="usuarios.php" class="active"><i class="fas fa-user-cog"></i> Usuarios</a>
                <a href="cambiar_password.php"><i class="fas fa-key"></i> Cambiar contraseña</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>✏️ Editar Usuario</h1>
            </header>
            <div class="admin-content">
                <div class="form-container">
                    <?php if ($mensaje): ?>
                        <div class="alert alert-success"><?php echo $mensaje; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="admin-card">
                        <form method="POST" class="admin-form">
                            <div class="form-group">
                                <label>Usuario</label>
                                <input type="text" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" disabled>
                                <small style="color:#999;">El nombre de usuario no se puede cambiar.</small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nombre completo</label>
                                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" placeholder="Juan Pérez">
                                </div>
                                
                                <div class="form-group">
                                    <label>Correo electrónico *</label>
                                    <input type="email" name="email" required value="<?php echo htmlspecialchars($usuario['email']); ?>" placeholder="usuario@ejemplo.cl">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Rol</label>
                                    <select name="rol">
                                        <option value="editor" <?php echo $usuario['rol'] === 'editor' ? 'selected' : ''; ?>>📝 Editor</option>
                                        <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>🔐 Administrador</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Estado</label>
                                    <div style="margin-top:8px;">
                                        <label style="display:inline-block;margin-right:20px;font-weight:400;">
                                            <input type="checkbox" name="activo" value="1" <?php echo $usuario['activo'] ? 'checked' : ''; ?>> 
                                            Activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Nueva contraseña (dejar en blanco para no cambiar)</label>
                                <input type="password" name="password" minlength="6" placeholder="Nueva contraseña (mínimo 6 caracteres)">
                            </div>
                            
                            <!-- PREGUNTA DE SEGURIDAD + PIN -->
                            <div class="security-box">
                                <h3>
                                    <i class="fas fa-shield-alt"></i> Seguridad adicional
                                    <span class="badge-seguridad">Pregunta</span>
                                    <span class="badge-pin">PIN</span>
                                </h3>
                                
                                <?php if (!empty($usuario['pregunta_seguridad'])): ?>
                                    <div class="pregunta-existente">
                                        <p>
                                            <i class="fas fa-check-circle" style="color:#28a745;"></i>
                                            <strong>Pregunta configurada:</strong> 
                                            <?php echo htmlspecialchars($usuario['pregunta_seguridad']); ?>
                                        </p>
                                        <div style="margin-top:8px;">
                                            <button type="button" class="btn-eliminar-pregunta" onclick="eliminarPregunta()">
                                                <i class="fas fa-trash"></i> Eliminar pregunta
                                            </button>
                                            <input type="hidden" name="eliminar_pregunta" id="eliminar_pregunta" value="0">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label>Pregunta de seguridad</label>
                                    <input type="text" name="pregunta_seguridad" 
                                           value="<?php echo !empty($usuario['pregunta_seguridad']) ? htmlspecialchars($usuario['pregunta_seguridad']) : ''; ?>" 
                                           placeholder="Ej: ¿Cuál es el nombre de tu primera mascota?">
                                    <div class="help-text">
                                        <i class="fas fa-info-circle"></i> Esta pregunta será usada para recuperar la contraseña.
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Respuesta de seguridad</label>
                                    <input type="text" name="respuesta_seguridad" 
                                           value="" 
                                           placeholder="Escribe la respuesta (se guardará encriptada)">
                                    <div class="help-text">
                                        <i class="fas fa-lock"></i> La respuesta se guardará encriptada.
                                        <?php if (!empty($usuario['respuesta_seguridad'])): ?>
                                            <span style="color:#28a745;">✓ Ya hay una respuesta configurada.</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="pin-box">
                                    <h5><i class="fas fa-key"></i> PIN de seguridad (4 dígitos)</h5>
                                    
                                    <?php if (!empty($usuario['pin_seguridad'])): ?>
                                        <div class="pin-existente">
                                            <p>
                                                <i class="fas fa-check-circle" style="color:#17a2b8;"></i>
                                                <strong>PIN configurado:</strong> 
                                                <span style="color:#17a2b8;">●●●●</span>
                                            </p>
                                            <div style="margin-top:8px;">
                                                <button type="button" class="btn-eliminar-pin" onclick="eliminarPIN()">
                                                    <i class="fas fa-trash"></i> Eliminar PIN
                                                </button>
                                                <input type="hidden" name="eliminar_pin" id="eliminar_pin" value="0">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label>Nuevo PIN (4 dígitos)</label>
                                        <input type="password" name="pin_seguridad" 
                                               maxlength="4" placeholder="1234"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,4)">
                                        <div class="help-text">
                                            <i class="fas fa-info-circle"></i> Debe ser un número de 4 dígitos. 
                                            <?php if (!empty($usuario['pin_seguridad'])): ?>
                                                <span style="color:#17a2b8;">✓ Ya hay un PIN configurado. Escribe uno nuevo para reemplazarlo.</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Actualizar Usuario
                            </button>
                            
                            <a href="usuarios.php" class="btn-secondary" style="display:inline-block;padding:12px 30px;background:var(--gris-claro);color:var(--azul-marino);border-radius:5px;text-decoration:none;font-weight:600;margin-left:10px;">
                                Cancelar
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function eliminarPregunta() {
            if (confirm('¿Eliminar la pregunta de seguridad?')) {
                document.getElementById('eliminar_pregunta').value = '1';
                var preguntaDiv = document.querySelector('.pregunta-existente');
                if (preguntaDiv) preguntaDiv.style.display = 'none';
                document.querySelector('input[name="pregunta_seguridad"]').value = '';
                document.querySelector('input[name="respuesta_seguridad"]').value = '';
            }
        }
        
        function eliminarPIN() {
            if (confirm('¿Eliminar el PIN de seguridad?')) {
                document.getElementById('eliminar_pin').value = '1';
                var pinDiv = document.querySelector('.pin-existente');
                if (pinDiv) pinDiv.style.display = 'none';
                document.querySelector('input[name="pin_seguridad"]').value = '';
            }
        }
    </script>
</body>
</html>