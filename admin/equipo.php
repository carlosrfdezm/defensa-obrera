<?php
// admin/equipo.php - Gestión de Equipo CON SUBIDA DE IMÁGENES
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$mensaje = '';
$error = '';

// ============================================
// RUTAS PARA SUBIDA DE IMÁGENES
// ============================================

$upload_dir = __DIR__ . '/../uploads/equipo/';
$upload_url = 'uploads/equipo/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function subirImagen($file, $upload_dir, $upload_url) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $tipos_permitidos)) {
        return false;
    }
    
    if ($file['size'] > 2 * 1024 * 1024) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
    $ruta_completa = $upload_dir . $nombre_archivo;
    
    if (move_uploaded_file($file['tmp_name'], $ruta_completa)) {
        return $upload_url . $nombre_archivo;
    }
    
    return false;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $foto_path = '';
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $foto_path = subirImagen($_FILES['foto'], $upload_dir, $upload_url);
                    if (!$foto_path) {
                        $error = 'Error al subir la imagen. Asegúrate de que sea JPG, PNG o GIF (máx 2MB).';
                        break;
                    }
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("INSERT INTO equipo (nombre, cargo, presentacion, foto, orden, activo) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['nombre'],
                        $_POST['cargo'],
                        $_POST['presentacion'],
                        $foto_path,
                        $_POST['orden'],
                        $_POST['activo'] ?? 1
                    ]);
                    $mensaje = '✅ Miembro agregado correctamente';
                }
                break;
                
            case 'edit':
                $stmt = $pdo->prepare("SELECT foto FROM equipo WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $miembro_actual = $stmt->fetch();
                $foto_path = $miembro_actual['foto'];
                
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    if ($foto_path && file_exists(__DIR__ . '/../' . $foto_path)) {
                        unlink(__DIR__ . '/../' . $foto_path);
                    }
                    $foto_path = subirImagen($_FILES['foto'], $upload_dir, $upload_url);
                    if (!$foto_path) {
                        $error = 'Error al subir la imagen.';
                        break;
                    }
                }
                
                if (isset($_POST['eliminar_foto']) && $_POST['eliminar_foto'] == '1') {
                    if ($foto_path && file_exists(__DIR__ . '/../' . $foto_path)) {
                        unlink(__DIR__ . '/../' . $foto_path);
                    }
                    $foto_path = '';
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("UPDATE equipo SET nombre = ?, cargo = ?, presentacion = ?, foto = ?, orden = ?, activo = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['nombre'],
                        $_POST['cargo'],
                        $_POST['presentacion'],
                        $foto_path,
                        $_POST['orden'],
                        $_POST['activo'] ?? 1,
                        $_POST['id']
                    ]);
                    $mensaje = '✅ Miembro actualizado correctamente';
                }
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("SELECT foto FROM equipo WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $miembro = $stmt->fetch();
                if ($miembro && $miembro['foto'] && file_exists(__DIR__ . '/../' . $miembro['foto'])) {
                    unlink(__DIR__ . '/../' . $miembro['foto']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM equipo WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $mensaje = '✅ Miembro eliminado correctamente';
                break;
        }
    }
}

$equipo = $pdo->query("SELECT * FROM equipo ORDER BY orden")->fetchAll();

$modo_edicion = false;
$miembro_editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM equipo WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $miembro_editar = $stmt->fetch();
    if ($miembro_editar) {
        $modo_edicion = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipo - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .upload-preview img {
            max-width: 120px;
            max-height: 120px;
            border-radius: 10px;
            border: 3px solid var(--azul-marino);
            object-fit: cover;
        }
        .upload-preview {
            display: inline-block;
            position: relative;
            margin-top: 10px;
        }
        .upload-preview .remove-img {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--rojo-intenso);
            color: var(--blanco);
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .upload-preview .remove-img:hover {
            transform: scale(1.15);
        }
        .form-group.upload-area {
            border: 2px dashed #ddd;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
            position: relative;
            min-height: 120px;
        }
        .form-group.upload-area:hover {
            border-color: var(--rojo-intenso);
            background: #fff5f5;
        }
        .form-group.upload-area input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .upload-icon {
            font-size: 32px;
            color: #ccc;
            margin-bottom: 5px;
        }
        .upload-text {
            color: #999;
            font-size: 13px;
        }
        .upload-text strong {
            color: var(--azul-marino);
        }
        .upload-formats {
            font-size: 11px;
            color: #bbb;
            margin-top: 3px;
        }
        .equipo-foto-mini {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--azul-marino);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img src="../img/logo-defensa-obrera.png" alt="Defensa Obrera">
            </div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="servicios.php"><i class="fas fa-gavel"></i> Servicios</a>
                <a href="equipo.php" class="active"><i class="fas fa-users"></i> Equipo</a>
                <a href="resultados.php"><i class="fas fa-chart-bar"></i> Resultados</a>
                <a href="recursos.php"><i class="fas fa-book"></i> Recursos</a>
                <a href="mensajes.php"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="usuarios.php"><i class="fas fa-user-cog"></i> Usuarios</a>
                <a href="cambiar_password.php"><i class="fas fa-key"></i> Cambiar contraseña</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </aside>
        <main class="admin-main">
            <header class="admin-header">
                <h1>Gestión del Equipo</h1>
            </header>
            <div class="admin-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <h2><?php echo $modo_edicion ? '✏️ Editar Miembro' : '➕ Agregar Miembro del Equipo'; ?></h2>
                    <form method="POST" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $modo_edicion ? 'edit' : 'add'; ?>">
                        <?php if ($modo_edicion): ?>
                            <input type="hidden" name="id" value="<?php echo $miembro_editar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Nombre completo *</label>
                            <input type="text" name="nombre" required value="<?php echo $modo_edicion ? htmlspecialchars($miembro_editar['nombre']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Cargo *</label>
                            <input type="text" name="cargo" required value="<?php echo $modo_edicion ? htmlspecialchars($miembro_editar['cargo']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Presentación *</label>
                            <textarea name="presentacion" required rows="4"><?php echo $modo_edicion ? htmlspecialchars($miembro_editar['presentacion']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group upload-area">
                            <input type="file" name="foto" accept="image/*">
                            <div class="upload-icon"><i class="fas fa-camera"></i></div>
                            <div class="upload-text"><strong>Haz clic o arrastra</strong> una imagen para subir</div>
                            <div class="upload-formats">Formatos: JPG, PNG, GIF, WebP | Máx: 2MB</div>
                            
                            <?php if ($modo_edicion && $miembro_editar['foto']): ?>
                                <div class="upload-preview" id="previewExistente">
                                    <img src="../<?php echo htmlspecialchars($miembro_editar['foto']); ?>" alt="Foto actual">
                                    <button type="button" class="remove-img" onclick="eliminarImagenExistente()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="eliminar_foto" id="eliminar_foto" value="0">
                            <?php endif; ?>
                            
                            <div id="previewNueva" style="display:none;" class="upload-preview">
                                <img id="previewImg" src="" alt="Vista previa">
                                <button type="button" class="remove-img" onclick="removerNuevaPreview()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Orden</label>
                            <input type="number" name="orden" value="<?php echo $modo_edicion ? $miembro_editar['orden'] : '0'; ?>">
                            <small style="color:#999;">Número más bajo = aparece primero</small>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="activo" value="1" <?php echo ($modo_edicion && $miembro_editar['activo']) || !$modo_edicion ? 'checked' : ''; ?>> 
                                Activo (visible en la página)
                            </label>
                        </div>
                        <button type="submit" class="btn-primary">
                            <?php echo $modo_edicion ? 'Actualizar Miembro' : 'Agregar Miembro'; ?>
                        </button>
                        <?php if ($modo_edicion): ?>
                            <a href="equipo.php" class="btn-secondary" style="display:inline-block;padding:12px 30px;background:var(--gris-claro);color:var(--azul-marino);border-radius:5px;text-decoration:none;font-weight:600;margin-left:10px;">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="admin-card">
                    <h2>Miembros del Equipo (<?php echo count($equipo); ?>)</h2>
                    <table class="admin-table">
                        <thead>
                            <tr><th>#</th><th>Foto</th><th>Nombre</th><th>Cargo</th><th>Orden</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($equipo as $miembro): ?>
                            <tr>
                                <td><?php echo $miembro['id']; ?></td>
                                <td>
                                    <?php if (!empty($miembro['foto'])): ?>
                                        <img src="../<?php echo htmlspecialchars($miembro['foto']); ?>" class="equipo-foto-mini">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle" style="font-size:35px;color:#ccc;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($miembro['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($miembro['cargo']); ?></td>
                                <td style="text-align:center;"><?php echo $miembro['orden']; ?></td>
                                <td><?php echo $miembro['activo'] ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                <td class="actions">
                                    <a href="?editar=<?php echo $miembro['id']; ?>" class="btn-edit">Editar</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $miembro['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Eliminar este miembro?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        const fileInput = document.querySelector('input[name="foto"]');
        const previewNueva = document.getElementById('previewNueva');
        const previewImg = document.getElementById('previewImg');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewNueva.style.display = 'inline-block';
                        if (document.getElementById('previewExistente')) {
                            document.getElementById('previewExistente').style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
        function removerNuevaPreview() {
            previewNueva.style.display = 'none';
            previewImg.src = '';
            fileInput.value = '';
            if (document.getElementById('previewExistente')) {
                document.getElementById('previewExistente').style.display = 'inline-block';
            }
        }
        function eliminarImagenExistente() {
            if (confirm('¿Eliminar la imagen actual?')) {
                document.getElementById('previewExistente').style.display = 'none';
                document.getElementById('eliminar_foto').value = '1';
            }
        }
    </script>
</body>
</html>