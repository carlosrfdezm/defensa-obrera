<?php
// admin/usuarios.php - Gestión de usuarios CON PREGUNTA + PIN (Menú incluido)
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

$mensaje = '';
$error = '';

// ============================================
// PROCESAR FORMULARIO - AGREGAR USUARIO
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = $_POST['rol'] ?? 'editor';
    $pregunta_seguridad = trim($_POST['pregunta_seguridad'] ?? '');
    $respuesta_seguridad = trim($_POST['respuesta_seguridad'] ?? '');
    $pin_seguridad = trim($_POST['pin_seguridad'] ?? '');
    
    if (empty($usuario) || empty($password) || empty($email)) {
        $error = 'Usuario, contraseña y email son obligatorios.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        if ($stmt->fetch()) {
            $error = 'El usuario ya existe.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (usuario, nombre, email, password, rol";
            $params = [$usuario, $nombre, $email, $hash, $rol];
            
            if (!empty($pregunta_seguridad) && !empty($respuesta_seguridad)) {
                $respuesta_hash = password_hash($respuesta_seguridad, PASSWORD_DEFAULT);
                $sql .= ", pregunta_seguridad, respuesta_seguridad";
                $params[] = $pregunta_seguridad;
                $params[] = $respuesta_hash;
            }
            
            if (!empty($pin_seguridad)) {
                $pin_hash = password_hash($pin_seguridad, PASSWORD_DEFAULT);
                $sql .= ", pin_seguridad";
                $params[] = $pin_hash;
            }
            
            $sql .= ") VALUES (" . implode(',', array_fill(0, count($params), '?')) . ")";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $mensaje = '✅ Usuario agregado correctamente.';
        }
    }
}

$usuarios = $pdo->query("SELECT id, usuario, nombre, email, rol, activo, created_at, pregunta_seguridad, pin_seguridad FROM usuarios ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .badge-rol {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-rol.admin {
            background: #dc3545;
            color: #fff;
        }
        .badge-rol.editor {
            background: #17a2b8;
            color: #fff;
        }
        .badge-estado {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-estado.activo {
            background: #28a745;
            color: #fff;
        }
        .badge-estado.inactivo {
            background: #6c757d;
            color: #fff;
        }
        .badge-seguridad {
            display: inline-block;
            background: #FFB900;
            color: var(--azul-marino);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-pin {
            display: inline-block;
            background: #17a2b8;
            color: #fff;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }
        .security-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #FFB900;
            margin: 20px 0;
        }
        .security-box h4 {
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
           MENÚ LATERAL COMPLETO (como en index.php)
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
        .admin-table {
            width: 100%;
            background: var(--blanco);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .admin-table th {
            background: var(--azul-marino);
            color: var(--blanco);
            padding: 15px;
            text-align: left;
        }
        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .admin-table .actions {
            display: flex;
            gap: 10px;
        }
        .admin-table .btn-edit {
            background: var(--amarillo-calido);
            color: var(--azul-marino);
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }
        .admin-table .btn-delete {
            background: var(--rojo-intenso);
            color: var(--blanco);
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
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
                <h1>👥 Gestión de Usuarios</h1>
            </header>
            <div class="admin-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- FORMULARIO PARA AGREGAR USUARIO -->
                <div class="admin-card">
                    <h2>➕ Agregar Nuevo Usuario</h2>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Usuario *</label>
                                <input type="text" name="usuario" required placeholder="ej: juan">
                            </div>
                            <div class="form-group">
                                <label>Contraseña *</label>
                                <input type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nombre completo</label>
                                <input type="text" name="nombre" placeholder="Juan Pérez">
                            </div>
                            <div class="form-group">
                                <label>Correo electrónico *</label>
                                <input type="email" name="email" required placeholder="juan@ejemplo.cl">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Rol</label>
                                <select name="rol">
                                    <option value="editor">📝 Editor</option>
                                    <option value="admin">🔐 Administrador</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div style="margin-top:8px;">
                                    <label style="display:inline-block;margin-right:20px;font-weight:400;">
                                        <input type="checkbox" name="activo" value="1" checked> 
                                        Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PREGUNTA DE SEGURIDAD + PIN -->
                        <div class="security-box">
                            <h4><i class="fas fa-shield-alt"></i> Seguridad adicional (opcional)</h4>
                            <p style="font-size:13px;color:#666;margin-bottom:15px;">
                                Configurar pregunta y PIN de seguridad permite al usuario recuperar su contraseña 
                                de forma segura.
                            </p>
                            
                            <div class="form-group">
                                <label>Pregunta de seguridad</label>
                                <input type="text" name="pregunta_seguridad" 
                                       placeholder="Ej: ¿Cuál es el nombre de tu primera mascota?">
                                <div class="help-text">
                                    <i class="fas fa-info-circle"></i> Si no quieres configurar, deja los campos vacíos.
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Respuesta de seguridad</label>
                                <input type="text" name="respuesta_seguridad" 
                                       placeholder="Escribe la respuesta (se guardará encriptada)">
                                <div class="help-text">
                                    <i class="fas fa-lock"></i> La respuesta se guardará encriptada.
                                </div>
                            </div>
                            
                            <div class="pin-box">
                                <h5><i class="fas fa-key"></i> PIN de seguridad (4 dígitos)</h5>
                                <p style="font-size:13px;color:#666;margin-bottom:10px;">
                                    El PIN es un código numérico de 4 dígitos que el usuario debe ingresar 
                                    además de la respuesta de seguridad.
                                </p>
                                
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>PIN de seguridad</label>
                                    <input type="password" name="pin_seguridad" 
                                           maxlength="4" placeholder="1234"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,4)">
                                    <div class="help-text">
                                        <i class="fas fa-info-circle"></i> Debe ser un número de 4 dígitos.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus"></i> Agregar Usuario
                        </button>
                    </form>
                </div>
                
                <!-- LISTA DE USUARIOS -->
                <div class="admin-card">
                    <h2>📋 Usuarios Registrados (<?php echo count($usuarios); ?>)</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Pregunta</th>
                                <th>PIN</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($usuario['usuario']); ?></strong></td>
                                <td><?php echo htmlspecialchars($usuario['nombre'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <span class="badge-rol <?php echo $usuario['rol']; ?>">
                                        <?php echo $usuario['rol'] === 'admin' ? '🔐 Admin' : '📝 Editor'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($usuario['pregunta_seguridad'])): ?>
                                        <span class="badge-seguridad"><i class="fas fa-check-circle"></i> Sí</span>
                                    <?php else: ?>
                                        <span style="color:#999;font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($usuario['pin_seguridad'])): ?>
                                        <span class="badge-pin"><i class="fas fa-key"></i> Configurado</span>
                                    <?php else: ?>
                                        <span style="color:#999;font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-estado <?php echo $usuario['activo'] ? 'activo' : 'inactivo'; ?>">
                                        <?php echo $usuario['activo'] ? '✅ Activo' : '❌ Inactivo'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></td>
                                <td class="actions">
                                    <?php if ($usuario['id'] != $_SESSION['admin_id']): ?>
                                        <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="eliminar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este usuario?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#999;font-size:12px;">(Tú)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>