<?php
// admin/recursos.php - Gestión de Recursos, Artículos, Guías y FAQs
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

$mensaje = '';
$error = '';

// ============================================
// VERIFICAR Y CREAR CAMPOS SI NO EXISTEN
// ============================================

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM recursos LIKE 'resumen'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE recursos ADD COLUMN resumen TEXT AFTER contenido");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM recursos LIKE 'contenido_completo'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE recursos ADD COLUMN contenido_completo LONGTEXT AFTER resumen");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM recursos LIKE 'autor'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE recursos ADD COLUMN autor VARCHAR(100) DEFAULT 'Defensa Obrera' AFTER contenido_completo");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM recursos LIKE 'archivo_pdf'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE recursos ADD COLUMN archivo_pdf VARCHAR(255) AFTER imagen");
    }
} catch(PDOException $e) {
    $error = 'Error al verificar campos: ' . $e->getMessage();
}

// ============================================
// RUTAS PARA SUBIDA DE IMÁGENES Y PDF
// ============================================

$upload_dir = __DIR__ . '/../uploads/recursos/';
$upload_url = 'uploads/recursos/';

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

function subirPDF($file, $upload_dir, $upload_url) {
    if ($file['type'] != 'application/pdf') {
        return false;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    $nombre_archivo = uniqid() . '_' . time() . '.pdf';
    $ruta_completa = $upload_dir . $nombre_archivo;
    if (move_uploaded_file($file['tmp_name'], $ruta_completa)) {
        return $upload_url . $nombre_archivo;
    }
    return false;
}

// ============================================
// PROCESAR FORMULARIO
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            // ============================================
            // AGREGAR RECURSO (Artículo o Guía)
            // ============================================
            case 'add_recurso':
                $imagen_path = '';
                $pdf_path = '';
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imagen_path = subirImagen($_FILES['imagen'], $upload_dir, $upload_url);
                    if (!$imagen_path) {
                        $error = 'Error al subir la imagen.';
                        break;
                    }
                }
                
                if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
                    $pdf_path = subirPDF($_FILES['archivo_pdf'], $upload_dir, $upload_url);
                    if (!$pdf_path) {
                        $error = 'Error al subir el PDF. Asegúrate de que sea un archivo PDF válido (máx 5MB).';
                        break;
                    }
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("INSERT INTO recursos (titulo, resumen, contenido, contenido_completo, autor, tipo, imagen, archivo_pdf, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['titulo'],
                        $_POST['resumen'] ?? '',
                        $_POST['contenido'] ?? '',
                        $_POST['contenido_completo'] ?? $_POST['contenido'] ?? '',
                        $_POST['autor'] ?? 'Defensa Obrera',
                        $_POST['tipo'],
                        $imagen_path,
                        $pdf_path,
                        $_POST['activo'] ?? 1
                    ]);
                    $mensaje = '✅ Recurso agregado correctamente';
                }
                break;
                
            // ============================================
            // EDITAR RECURSO
            // ============================================
            case 'edit_recurso':
                $stmt = $pdo->prepare("SELECT imagen, archivo_pdf FROM recursos WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $recurso_actual = $stmt->fetch();
                $imagen_path = $recurso_actual['imagen'] ?? '';
                $pdf_path = $recurso_actual['archivo_pdf'] ?? '';
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    if ($imagen_path && file_exists(__DIR__ . '/../' . $imagen_path)) {
                        unlink(__DIR__ . '/../' . $imagen_path);
                    }
                    $imagen_path = subirImagen($_FILES['imagen'], $upload_dir, $upload_url);
                    if (!$imagen_path) {
                        $error = 'Error al subir la imagen.';
                        break;
                    }
                }
                
                if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
                    if ($pdf_path && file_exists(__DIR__ . '/../' . $pdf_path)) {
                        unlink(__DIR__ . '/../' . $pdf_path);
                    }
                    $pdf_path = subirPDF($_FILES['archivo_pdf'], $upload_dir, $upload_url);
                    if (!$pdf_path) {
                        $error = 'Error al subir el PDF.';
                        break;
                    }
                }
                
                if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
                    if ($imagen_path && file_exists(__DIR__ . '/../' . $imagen_path)) {
                        unlink(__DIR__ . '/../' . $imagen_path);
                    }
                    $imagen_path = '';
                }
                
                if (isset($_POST['eliminar_pdf']) && $_POST['eliminar_pdf'] == '1') {
                    if ($pdf_path && file_exists(__DIR__ . '/../' . $pdf_path)) {
                        unlink(__DIR__ . '/../' . $pdf_path);
                    }
                    $pdf_path = '';
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("UPDATE recursos SET titulo = ?, resumen = ?, contenido = ?, contenido_completo = ?, autor = ?, tipo = ?, imagen = ?, archivo_pdf = ?, activo = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['titulo'],
                        $_POST['resumen'] ?? '',
                        $_POST['contenido'] ?? '',
                        $_POST['contenido_completo'] ?? $_POST['contenido'] ?? '',
                        $_POST['autor'] ?? 'Defensa Obrera',
                        $_POST['tipo'],
                        $imagen_path,
                        $pdf_path,
                        $_POST['activo'] ?? 1,
                        $_POST['id']
                    ]);
                    $mensaje = '✅ Recurso actualizado correctamente';
                }
                break;
                
            // ============================================
            // ELIMINAR RECURSO
            // ============================================
            case 'delete_recurso':
                $stmt = $pdo->prepare("SELECT imagen, archivo_pdf FROM recursos WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $recurso = $stmt->fetch();
                if ($recurso) {
                    if ($recurso['imagen'] && file_exists(__DIR__ . '/../' . $recurso['imagen'])) {
                        unlink(__DIR__ . '/../' . $recurso['imagen']);
                    }
                    if ($recurso['archivo_pdf'] && file_exists(__DIR__ . '/../' . $recurso['archivo_pdf'])) {
                        unlink(__DIR__ . '/../' . $recurso['archivo_pdf']);
                    }
                }
                $stmt = $pdo->prepare("DELETE FROM recursos WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $mensaje = '✅ Recurso eliminado correctamente';
                break;
                
            // ============================================
            // AGREGAR FAQ
            // ============================================
            case 'add_faq':
                $stmt = $pdo->prepare("INSERT INTO faqs (pregunta, respuesta, orden, activo) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['pregunta'],
                    $_POST['respuesta'],
                    $_POST['orden'] ?? 0,
                    $_POST['activo'] ?? 1
                ]);
                $mensaje = '✅ FAQ agregada correctamente';
                break;
                
            // ============================================
            // EDITAR FAQ
            // ============================================
            case 'edit_faq':
                $stmt = $pdo->prepare("UPDATE faqs SET pregunta = ?, respuesta = ?, orden = ?, activo = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['pregunta'],
                    $_POST['respuesta'],
                    $_POST['orden'] ?? 0,
                    $_POST['activo'] ?? 1,
                    $_POST['id']
                ]);
                $mensaje = '✅ FAQ actualizada correctamente';
                break;
                
            // ============================================
            // ELIMINAR FAQ
            // ============================================
            case 'delete_faq':
                $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $mensaje = '✅ FAQ eliminada correctamente';
                break;
        }
    }
}

// ============================================
// OBTENER DATOS PARA MOSTRAR
// ============================================

$recursos = $pdo->query("SELECT * FROM recursos ORDER BY created_at DESC")->fetchAll();
$faqs = $pdo->query("SELECT * FROM faqs ORDER BY orden")->fetchAll();

$modo_edicion = false;
$recurso_editar = null;
if (isset($_GET['editar_recurso'])) {
    $stmt = $pdo->prepare("SELECT * FROM recursos WHERE id = ?");
    $stmt->execute([$_GET['editar_recurso']]);
    $recurso_editar = $stmt->fetch();
    if ($recurso_editar) {
        $modo_edicion = true;
    }
}

$modo_edicion_faq = false;
$faq_editar = null;
if (isset($_GET['editar_faq'])) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['editar_faq']]);
    $faq_editar = $stmt->fetch();
    if ($faq_editar) {
        $modo_edicion_faq = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- CKEditor 5 (SIN API Key) -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
    <style>
        /* --- Estilos del panel admin --- */
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
        .recurso-img-mini {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
            border: 2px solid var(--azul-marino);
        }
        .ck-editor__editable {
            min-height: 300px !important;
        }
        
        /* --- PDF Badge --- */
        .pdf-badge {
            display: inline-block;
            background: #dc3545;
            color: #fff;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .pdf-badge i {
            margin-right: 4px;
        }
        
        /* --- PDF Upload Area --- */
        .pdf-upload-area {
            border: 2px dashed #ddd;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
            position: relative;
        }
        .pdf-upload-area:hover {
            border-color: #dc3545;
            background: #fff5f5;
        }
        .pdf-upload-area input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .pdf-icon {
            font-size: 32px;
            color: #dc3545;
            margin-bottom: 5px;
        }
        .pdf-preview {
            display: inline-block;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #dee2e6;
        }
        .pdf-preview i {
            font-size: 24px;
            color: #dc3545;
            margin-right: 10px;
        }
        .pdf-preview .remove-pdf {
            margin-left: 15px;
            color: #dc3545;
            cursor: pointer;
            font-weight: 600;
        }
        .pdf-preview .remove-pdf:hover {
            color: #c82333;
        }
        
        /* --- Badge tipo --- */
        .badge-tipo {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-tipo.articulo {
            background: #17a2b8;
            color: #fff;
        }
        .badge-tipo.guia {
            background: #28a745;
            color: #fff;
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
                <a href="equipo.php"><i class="fas fa-users"></i> Equipo</a>
                <a href="resultados.php"><i class="fas fa-chart-bar"></i> Resultados</a>
                <a href="recursos.php" class="active"><i class="fas fa-book"></i> Recursos</a>
                <a href="mensajes.php"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>Gestión de Recursos, Artículos, Guías y FAQs</h1>
            </header>
            <div class="admin-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- ============================================
                FORMULARIO: Agregar/Editar Recurso
                ============================================ -->
                <div class="admin-card">
                    <h2><?php echo $modo_edicion ? '✏️ Editar Recurso' : '➕ Publicar Artículo o Guía'; ?></h2>
                    <form method="POST" class="admin-form" enctype="multipart/form-data" id="recursoForm">
                        <input type="hidden" name="action" value="<?php echo $modo_edicion ? 'edit_recurso' : 'add_recurso'; ?>">
                        <?php if ($modo_edicion): ?>
                            <input type="hidden" name="id" value="<?php echo $recurso_editar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Título *</label>
                            <input type="text" name="titulo" required value="<?php echo $modo_edicion ? htmlspecialchars($recurso_editar['titulo'] ?? '') : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Autor</label>
                            <input type="text" name="autor" value="<?php echo $modo_edicion ? htmlspecialchars($recurso_editar['autor'] ?? 'Defensa Obrera') : 'Defensa Obrera'; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Tipo *</label>
                            <select name="tipo" required>
                                <option value="articulo" <?php echo ($modo_edicion && ($recurso_editar['tipo'] ?? '') == 'articulo') ? 'selected' : ''; ?>>📄 Artículo</option>
                                <option value="guia" <?php echo ($modo_edicion && ($recurso_editar['tipo'] ?? '') == 'guia') ? 'selected' : ''; ?>>📚 Guía</option>
                            </select>
                            <small style="color:#999;">Los artículos aparecen en la sección de artículos, las guías en la sección de guías prácticas.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Resumen (aparece en la lista) *</label>
                            <textarea name="resumen" required rows="2"><?php echo $modo_edicion ? htmlspecialchars($recurso_editar['resumen'] ?? $recurso_editar['contenido'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Contenido completo *</label>
                            <textarea name="contenido_completo" id="editor" rows="15"><?php echo $modo_edicion ? htmlspecialchars($recurso_editar['contenido_completo'] ?? $recurso_editar['contenido'] ?? '') : ''; ?></textarea>
                            <small style="color:#999;">Usa el editor para dar formato a tu contenido (negritas, listas, etc.)</small>
                        </div>
                        
                        <!-- Área de subida de imagen -->
                        <div class="form-group upload-area">
                            <input type="file" name="imagen" accept="image/*">
                            <div class="upload-icon"><i class="fas fa-image"></i></div>
                            <div class="upload-text"><strong>Haz clic o arrastra</strong> una imagen para subir</div>
                            <div class="upload-formats">Formatos: JPG, PNG, GIF, WebP | Máx: 2MB</div>
                            
                            <?php if ($modo_edicion && !empty($recurso_editar['imagen'])): ?>
                                <div class="upload-preview" id="previewExistente">
                                    <img src="../<?php echo htmlspecialchars($recurso_editar['imagen']); ?>" alt="Imagen actual">
                                    <button type="button" class="remove-img" onclick="eliminarImagenExistente()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="eliminar_imagen" id="eliminar_imagen" value="0">
                            <?php endif; ?>
                            
                            <div id="previewNueva" style="display:none;" class="upload-preview">
                                <img id="previewImg" src="" alt="Vista previa">
                                <button type="button" class="remove-img" onclick="removerNuevaPreview()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Área de subida de PDF -->
                        <div class="form-group">
                            <label>Documento PDF (opcional)</label>
                            <div class="pdf-upload-area">
                                <input type="file" name="archivo_pdf" accept=".pdf">
                                <div class="pdf-icon"><i class="fas fa-file-pdf"></i></div>
                                <div class="upload-text"><strong>Haz clic o arrastra</strong> un archivo PDF</div>
                                <div class="upload-formats">Formato: PDF | Máx: 5MB</div>
                                
                                <?php if ($modo_edicion && !empty($recurso_editar['archivo_pdf'])): ?>
                                    <div class="pdf-preview" id="pdfExistente">
                                        <i class="fas fa-file-pdf"></i>
                                        <?php echo basename($recurso_editar['archivo_pdf']); ?>
                                        <span class="remove-pdf" onclick="eliminarPDFExistente()">✕ Eliminar</span>
                                    </div>
                                    <input type="hidden" name="eliminar_pdf" id="eliminar_pdf" value="0">
                                <?php endif; ?>
                                
                                <div id="pdfPreviewNueva" style="display:none;" class="pdf-preview">
                                    <i class="fas fa-file-pdf"></i>
                                    <span id="pdfNombre">documento.pdf</span>
                                    <span class="remove-pdf" onclick="removerPDFPreview()">✕ Eliminar</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="activo" value="1" <?php echo ($modo_edicion && !empty($recurso_editar['activo'])) || !$modo_edicion ? 'checked' : ''; ?>> 
                                Activo (visible en la página)
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-primary" onclick="return guardarContenido()">
                            <?php echo $modo_edicion ? 'Actualizar Recurso' : 'Publicar Recurso'; ?>
                        </button>
                        
                        <?php if ($modo_edicion): ?>
                            <a href="recursos.php" class="btn-secondary" style="display:inline-block;padding:12px 30px;background:var(--gris-claro);color:var(--azul-marino);border-radius:5px;text-decoration:none;font-weight:600;margin-left:10px;">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- ============================================
                FORMULARIO: Agregar/Editar FAQ
                ============================================ -->
                <div class="admin-card">
                    <h2><?php echo $modo_edicion_faq ? '✏️ Editar FAQ' : '➕ Agregar Pregunta Frecuente'; ?></h2>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="action" value="<?php echo $modo_edicion_faq ? 'edit_faq' : 'add_faq'; ?>">
                        <?php if ($modo_edicion_faq): ?>
                            <input type="hidden" name="id" value="<?php echo $faq_editar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Pregunta *</label>
                            <input type="text" name="pregunta" required value="<?php echo $modo_edicion_faq ? htmlspecialchars($faq_editar['pregunta'] ?? '') : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Respuesta *</label>
                            <textarea name="respuesta" required rows="3"><?php echo $modo_edicion_faq ? htmlspecialchars($faq_editar['respuesta'] ?? '') : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Orden</label>
                            <input type="number" name="orden" value="<?php echo $modo_edicion_faq ? ($faq_editar['orden'] ?? 0) : '0'; ?>">
                            <small style="color:#999;">Número más bajo = aparece primero</small>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="activo" value="1" <?php echo ($modo_edicion_faq && !empty($faq_editar['activo'])) || !$modo_edicion_faq ? 'checked' : ''; ?>> 
                                Activo (visible en la página)
                            </label>
                        </div>
                        <button type="submit" class="btn-primary">
                            <?php echo $modo_edicion_faq ? 'Actualizar FAQ' : 'Agregar FAQ'; ?>
                        </button>
                        <?php if ($modo_edicion_faq): ?>
                            <a href="recursos.php" class="btn-secondary" style="display:inline-block;padding:12px 30px;background:var(--gris-claro);color:var(--azul-marino);border-radius:5px;text-decoration:none;font-weight:600;margin-left:10px;">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- ============================================
                LISTA DE RECURSOS (Artículos y Guías)
                ============================================ -->
                <div class="admin-card">
                    <h2>Recursos Publicados (<?php echo count($recursos); ?>)</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Imagen</th>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Tipo</th>
                                <th>PDF</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recursos as $recurso): ?>
                            <tr>
                                <td><?php echo $recurso['id']; ?></td>
                                <td>
                                    <?php if (!empty($recurso['imagen'])): ?>
                                        <img src="../<?php echo htmlspecialchars($recurso['imagen']); ?>" class="recurso-img-mini">
                                    <?php else: ?>
                                        <i class="fas fa-file" style="font-size:30px;color:#ccc;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($recurso['titulo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($recurso['autor'] ?? 'Defensa Obrera'); ?></td>
                                <td>
                                    <?php if (($recurso['tipo'] ?? '') == 'articulo'): ?>
                                        <span class="badge-tipo articulo">📄 Artículo</span>
                                    <?php elseif (($recurso['tipo'] ?? '') == 'guia'): ?>
                                        <span class="badge-tipo guia">📚 Guía</span>
                                    <?php else: ?>
                                        <span class="badge-tipo" style="background:#6c757d;color:#fff;"><?php echo htmlspecialchars($recurso['tipo'] ?? ''); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($recurso['archivo_pdf'])): ?>
                                        <span class="pdf-badge"><i class="fas fa-file-pdf"></i> PDF</span>
                                    <?php else: ?>
                                        <span style="color:#999;font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($recurso['activo']) ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                <td class="actions">
                                    <a href="?editar_recurso=<?php echo $recurso['id']; ?>" class="btn-edit">Editar</a>
                                    <a href="../recurso.php?id=<?php echo $recurso['id']; ?>" target="_blank" class="btn-edit" style="background:var(--azul-marino);color:var(--blanco);">Ver</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_recurso">
                                        <input type="hidden" name="id" value="<?php echo $recurso['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Eliminar este recurso? Se eliminarán también la imagen y el PDF asociados.')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ============================================
                LISTA DE FAQs
                ============================================ -->
                <div class="admin-card">
                    <h2>Preguntas Frecuentes (<?php echo count($faqs); ?>)</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pregunta</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($faqs as $faq): ?>
                            <tr>
                                <td><?php echo $faq['id']; ?></td>
                                <td><?php echo htmlspecialchars(substr($faq['pregunta'] ?? '', 0, 60)) . '...'; ?></td>
                                <td><?php echo $faq['orden'] ?? 0; ?></td>
                                <td><?php echo !empty($faq['activo']) ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                <td class="actions">
                                    <a href="?editar_faq=<?php echo $faq['id']; ?>" class="btn-edit">Editar</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_faq">
                                        <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Eliminar esta FAQ?')">Eliminar</button>
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
        // ============================================
        // INICIALIZAR CKEDITOR
        // ============================================
        let editorInstance = null;
        
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ],
                language: 'es',
                height: 400,
                removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges', 'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments', 'TrackChanges', 'RevisionHistory', 'Pagination', 'WProofreader', 'MathType']
            })
            .then(editor => {
                editorInstance = editor;
                console.log('CKEditor inicializado correctamente');
            })
            .catch(error => {
                console.error('Error al inicializar CKEditor:', error);
            });
        
        function guardarContenido() {
            if (editorInstance) {
                const contenido = editorInstance.getData();
                document.querySelector('textarea[name="contenido_completo"]').value = contenido;
            }
            return true;
        }
        
        // ============================================
        // PREVIEW DE IMAGEN
        // ============================================
        const fileInput = document.querySelector('input[name="imagen"]');
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
                document.getElementById('eliminar_imagen').value = '1';
            }
        }
        
        // ============================================
        // PREVIEW DE PDF
        // ============================================
        const pdfInput = document.querySelector('input[name="archivo_pdf"]');
        const pdfPreviewNueva = document.getElementById('pdfPreviewNueva');
        const pdfNombre = document.getElementById('pdfNombre');
        
        if (pdfInput) {
            pdfInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    if (file.type === 'application/pdf') {
                        pdfNombre.textContent = file.name;
                        pdfPreviewNueva.style.display = 'inline-block';
                        if (document.getElementById('pdfExistente')) {
                            document.getElementById('pdfExistente').style.display = 'none';
                        }
                    } else {
                        alert('Por favor, selecciona un archivo PDF válido.');
                        this.value = '';
                    }
                }
            });
        }
        
        function removerPDFPreview() {
            pdfPreviewNueva.style.display = 'none';
            pdfInput.value = '';
            if (document.getElementById('pdfExistente')) {
                document.getElementById('pdfExistente').style.display = 'inline-block';
            }
        }
        
        function eliminarPDFExistente() {
            if (confirm('¿Eliminar el archivo PDF actual?')) {
                document.getElementById('pdfExistente').style.display = 'none';
                document.getElementById('eliminar_pdf').value = '1';
            }
        }
    </script>
</body>
</html>