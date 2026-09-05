<?php
// recurso.php - Página de artículo individual CON PDF CORREGIDO
require_once 'includes/db.php';
require_once 'includes/header.php';

// Obtener ID del artículo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: recursos.php');
    exit;
}

// Obtener el artículo
$stmt = $pdo->prepare("SELECT * FROM recursos WHERE id = ? AND activo = 1");
$stmt->execute([$id]);
$recurso = $stmt->fetch();

if (!$recurso) {
    header('Location: recursos.php');
    exit;
}

// Verificar si tiene contenido completo o usar el contenido normal
$contenido_completo = !empty($recurso['contenido_completo']) 
    ? $recurso['contenido_completo'] 
    : $recurso['contenido'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recurso['titulo']); ?> - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ============================================
           ARTÍCULO INDIVIDUAL - ESTILOS
           ============================================ */
        
        .articulo-hero {
            background: var(--azul-marino);
            color: var(--blanco);
            padding: 50px 0;
        }
        
        .articulo-hero h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 38px;
            font-weight: 900;
            margin-bottom: 15px;
        }
        
        .articulo-hero .meta {
            font-size: 14px;
            opacity: 0.8;
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .articulo-hero .meta i {
            margin-right: 6px;
        }
        
        .articulo-hero .meta .badge-tipo {
            background: var(--amarillo-calido);
            color: var(--azul-marino);
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .articulo-hero .meta .badge-pdf {
            background: #dc3545;
            color: #fff;
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .articulo-contenido {
            padding: 60px 0;
            background: var(--gris-claro);
        }
        
        .articulo-contenido .contenedor {
            max-width: 850px;
            margin: 0 auto;
            background: var(--blanco);
            padding: 50px 60px;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.08);
        }
        
        .articulo-contenido .contenedor .imagen-destacada {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .articulo-contenido .contenedor .resumen {
            font-size: 18px;
            color: var(--azul-marino);
            font-weight: 600;
            padding: 20px;
            background: var(--gris-claro);
            border-radius: 8px;
            border-left: 4px solid var(--rojo-intenso);
            margin-bottom: 30px;
            line-height: 1.7;
        }
        
        .articulo-contenido .contenedor .cuerpo {
            font-size: 16px;
            line-height: 1.9;
            color: var(--gris-texto);
        }
        
        .articulo-contenido .contenedor .cuerpo h2 {
            color: var(--azul-marino);
            font-family: 'Montserrat', sans-serif;
            font-size: 26px;
            margin-top: 35px;
            margin-bottom: 15px;
        }
        
        .articulo-contenido .contenedor .cuerpo h3 {
            color: var(--azul-marino);
            font-family: 'Montserrat', sans-serif;
            font-size: 22px;
            margin-top: 30px;
            margin-bottom: 12px;
        }
        
        .articulo-contenido .contenedor .cuerpo p {
            margin-bottom: 18px;
        }
        
        .articulo-contenido .contenedor .cuerpo ul,
        .articulo-contenido .contenedor .cuerpo ol {
            padding-left: 25px;
            margin-bottom: 18px;
        }
        
        .articulo-contenido .contenedor .cuerpo ul li,
        .articulo-contenido .contenedor .cuerpo ol li {
            margin-bottom: 8px;
        }
        
        .articulo-contenido .contenedor .cuerpo img {
            max-width: 100%;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .articulo-contenido .contenedor .cuerpo blockquote {
            padding: 15px 25px;
            background: var(--gris-claro);
            border-left: 4px solid var(--amarillo-calido);
            margin: 20px 0;
            font-style: italic;
        }
        
        .articulo-contenido .contenedor .cuerpo table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .articulo-contenido .contenedor .cuerpo table th,
        .articulo-contenido .contenedor .cuerpo table td {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .articulo-contenido .contenedor .cuerpo table th {
            background: var(--azul-marino);
            color: #fff;
        }
        
        /* --- Acciones del artículo --- */
        .articulo-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 2px solid var(--gris-claro);
            flex-wrap: wrap;
        }
        
        .btn-pdf {
            background: #dc3545;
            color: var(--blanco);
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-pdf:hover {
            background: #c82333;
            transform: scale(1.03);
        }
        
        .btn-pdf:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-volver {
            background: var(--azul-marino);
            color: var(--blanco);
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-volver:hover {
            background: #002a4a;
            transform: scale(1.03);
        }
        
        /* --- PDF Adjunto --- */
        .pdf-adjunto {
            margin-top: 25px;
            padding: 20px 25px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pdf-adjunto .info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .pdf-adjunto .info i {
            font-size: 32px;
            color: #dc3545;
        }
        
        .pdf-adjunto .info span {
            font-weight: 600;
            color: var(--azul-marino);
        }
        
        .pdf-adjunto .info small {
            color: #999;
            font-size: 12px;
            display: block;
        }
        
        .pdf-adjunto .btn-descargar {
            background: #dc3545;
            color: #fff;
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .pdf-adjunto .btn-descargar:hover {
            background: #c82333;
            transform: scale(1.03);
        }
        
        /* --- Compartir --- */
        .compartir {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .compartir span {
            font-weight: 600;
            color: var(--azul-marino);
            margin-right: 15px;
        }
        
        .compartir a {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gris-claro);
            color: var(--azul-marino);
            text-align: center;
            line-height: 40px;
            margin-right: 8px;
            transition: all 0.3s;
        }
        
        .compartir a:hover {
            background: var(--azul-marino);
            color: var(--blanco);
        }
        
        /* --- Responsive --- */
        @media (max-width: 768px) {
            .articulo-hero h1 {
                font-size: 28px;
            }
            
            .articulo-contenido .contenedor {
                padding: 25px 20px;
            }
            
            .articulo-actions {
                flex-direction: column;
            }
            
            .articulo-actions .btn-pdf,
            .articulo-actions .btn-volver {
                justify-content: center;
                text-align: center;
            }
            
            .pdf-adjunto {
                flex-direction: column;
                text-align: center;
            }
            
            .pdf-adjunto .info {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .articulo-hero h1 {
                font-size: 22px;
            }
            
            .articulo-hero .meta {
                font-size: 12px;
                gap: 10px;
            }
            
            .articulo-contenido .contenedor {
                padding: 20px 15px;
            }
            
            .articulo-contenido .contenedor .resumen {
                font-size: 15px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main>
        <!-- Hero -->
        <section class="articulo-hero">
            <div class="container">
                <h1><?php echo htmlspecialchars($recurso['titulo']); ?></h1>
                <div class="meta">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($recurso['autor'] ?? 'Defensa Obrera'); ?></span>
                    <span><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($recurso['created_at'])); ?></span>
                    <span class="badge-tipo"><?php echo ucfirst(htmlspecialchars($recurso['tipo'] ?? 'Artículo')); ?></span>
                    <?php if (!empty($recurso['archivo_pdf']) && file_exists($recurso['archivo_pdf'])): ?>
                        <span class="badge-pdf"><i class="fas fa-file-pdf"></i> PDF disponible</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- Contenido -->
        <section class="articulo-contenido">
            <div class="container">
                <div class="contenedor" id="contenido-articulo">
                    <!-- Imagen destacada -->
                    <?php if (!empty($recurso['imagen']) && file_exists($recurso['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($recurso['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($recurso['titulo']); ?>" 
                             class="imagen-destacada">
                    <?php endif; ?>
                    
                    <!-- Resumen -->
                    <?php if (!empty($recurso['resumen'])): ?>
                        <div class="resumen">
                            <?php echo htmlspecialchars($recurso['resumen']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Contenido completo -->
                    <div class="cuerpo">
                        <?php echo htmlspecialchars_decode($contenido_completo); ?>
                    </div>
                    
                    <!-- PDF Adjunto -->
                    <?php if (!empty($recurso['archivo_pdf']) && file_exists($recurso['archivo_pdf'])): ?>
                        <div class="pdf-adjunto">
                            <div class="info">
                                <i class="fas fa-file-pdf"></i>
                                <div>
                                    <span>Documento adjunto</span>
                                    <small><?php echo basename($recurso['archivo_pdf']); ?></small>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($recurso['archivo_pdf']); ?>" 
                               target="_blank" 
                               class="btn-descargar">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Acciones -->
                    <div class="articulo-actions">
                        <!-- Botón Guardar como PDF -->
                        <button onclick="generarPDF()" class="btn-pdf" id="btnPDF">
                            <i class="fas fa-file-pdf"></i> Guardar como PDF
                        </button>
                        
                        <a href="recursos.php" class="btn-volver">
                            <i class="fas fa-arrow-left"></i> Volver a Recursos
                        </a>
                    </div>
                    
                    <!-- Compartir -->
                    <div class="compartir">
                        <span>Compartir:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" title="Compartir en Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($recurso['titulo']); ?>&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" title="Compartir en Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($recurso['titulo'] . ' - ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" title="Compartir en WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="mailto:?subject=<?php echo urlencode($recurso['titulo']); ?>&body=<?php echo urlencode('Lee este artículo: ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" title="Compartir por Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                        <a href="javascript:void(0);" onclick="window.print();" title="Imprimir">
                            <i class="fas fa-print"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
    
    <!-- Librería para generar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function generarPDF() {
            const elemento = document.getElementById('contenido-articulo');
            const btn = document.getElementById('btnPDF');
            const textoOriginal = btn.innerHTML;
            
            // Deshabilitar botón y mostrar spinner
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando PDF...';
            
            // Opciones del PDF
            const opt = {
                margin:        [10, 10, 10, 10],
                filename:     '<?php echo htmlspecialchars($recurso['titulo']); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, letterRendering: true, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Generar PDF
            html2pdf()
                .set(opt)
                .from(elemento)
                .save()
                .then(function() {
                    // Restaurar botón
                    btn.innerHTML = textoOriginal;
                    btn.disabled = false;
                })
                .catch(function(error) {
                    console.error('Error al generar PDF:', error);
                    btn.innerHTML = textoOriginal;
                    btn.disabled = false;
                    alert('Error al generar el PDF. Por favor, intenta nuevamente.');
                });
        }
    </script>
</body>
</html>