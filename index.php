<?php
// index.php - Página principal CON TESTIMONIOS EN EL SLIDER
require_once 'includes/db.php';
require_once 'includes/header.php';

// Obtener datos de la base de datos
$stmt = $pdo->query("SELECT * FROM compromisos WHERE activo = 1 ORDER BY orden");
$compromisos = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY orden LIMIT 4");
$servicios_destacados = $stmt->fetchAll();

// ============================================
// SLIDER: Mostrar testimonios si existen
// ============================================

// Obtener testimonios activos
$stmt = $pdo->query("SELECT * FROM testimonios WHERE activo = 1 ORDER BY orden");
$testimonios = $stmt->fetchAll();

// Obtener slider genérico (fallback)
$stmt = $pdo->query("SELECT * FROM slider WHERE activo = 1 ORDER BY orden");
$slider_items = $stmt->fetchAll();

// Decidir qué mostrar en el slider
if (count($testimonios) > 0) {
    // Usar testimonios reales
    $slider_data = $testimonios;
    $slider_titulo = 'Trabajadores que confiaron en <span class="highlight">nosotros</span>';
    $slider_subtitulo = 'Experiencias reales de personas que defendieron sus derechos laborales.';
    $slider_tipo = 'testimonios';
} else {
    // Usar mensajes genéricos
    $slider_data = $slider_items;
    $slider_titulo = 'Nuestro <span class="highlight">proceso de trabajo</span>';
    $slider_subtitulo = 'Te acompañamos en cada etapa para defender tus derechos laborales.';
    $slider_tipo = 'slider';
}
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
</head>
<body>
    <?php require_once 'includes/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <img src="img/hero-bg.jpg" alt="Defensa laboral" class="hero-bg">
            <div class="container hero-content">
                <h1>La empresa tiene abogados.<br><span class="highlight">Tú también.</span></h1>
                <p>No enfrentarás a la empresa solo. En Defensa Obrera te orientamos, preparamos y representamos para defender tus derechos laborales, acompañándote durante todo el proceso.</p>
                <div class="hero-buttons">
                    <a href="contacto.php" class="btn-primary">Revisar mi caso</a>
                    <a href="https://wa.me/56973847746" class="btn-secondary">Hablar por WhatsApp</a>
                </div>
            </div>
        </section>

        <!-- Compromisos -->
        <section class="compromisos">
            <div class="container">
                <div class="compromisos-grid">
                    <?php foreach($compromisos as $compromiso): ?>
                    <div class="compromiso-item">
                        <i class="fas <?php echo $compromiso['icono']; ?>"></i>
                        <h3><?php echo htmlspecialchars($compromiso['titulo']); ?></h3>
                        <p><?php echo htmlspecialchars($compromiso['descripcion']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Slider (con testimonios o mensajes genéricos) -->
        <section class="slider-section">
            <div class="container">
                <div class="section-header">
                    <h2><?php echo $slider_titulo; ?></h2>
                    <p><?php echo $slider_subtitulo; ?></p>
                </div>
                <div class="slider-container">
                    <div class="slider-track">
                        <?php foreach($slider_data as $item): ?>
                        <div class="slider-slide">
                            <?php if ($slider_tipo === 'testimonios'): ?>
                                <!-- Mostrar testimonio con formato -->
                                <div class="testimonio-slide">
                                    <i class="fas fa-quote-left" style="font-size:32px;color:var(--amarillo-calido);opacity:0.5;margin-bottom:15px;display:block;"></i>
                                    <p style="font-size:20px;font-style:italic;max-width:700px;margin:0 auto 20px;">
                                        "<?php echo htmlspecialchars($item['testimonio']); ?>"
                                    </p>
                                    <div style="display:flex;align-items:center;justify-content:center;gap:15px;">
                                        <?php if (!empty($item['foto']) && file_exists($item['foto'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['nombre']); ?>" 
                                                 style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--amarillo-calido);">
                                        <?php else: ?>
                                            <div style="width:60px;height:60px;border-radius:50%;background:var(--gris-claro);display:flex;align-items:center;justify-content:center;font-size:30px;color:#ccc;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div style="text-align:left;">
                                            <strong style="color:var(--azul-marino);font-size:18px;"><?php echo htmlspecialchars($item['nombre']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Mostrar mensaje genérico -->
                                <h3><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                <p><?php echo htmlspecialchars($item['descripcion']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="slider-btn prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="slider-btn next"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="slider-dots">
                    <?php for($i = 0; $i < count($slider_data); $i++): ?>
                    <button class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></button>
                    <?php endfor; ?>
                </div>
            </div>
        </section>

        <!-- Servicios Destacados -->
        <section class="servicios">
            <div class="container">
                <div class="section-header">
                    <h2>Servicios legales para defender tus <span class="highlight">derechos laborales</span></h2>
                    <p>Si estás enfrentando un problema en tu trabajo o después de una desvinculación, revisamos tus antecedentes y te explicamos cómo podemos ayudarte.</p>
                </div>
                <div class="servicios-grid">
                    <?php foreach($servicios_destacados as $servicio): ?>
                    <div class="servicio-card">
                        <i class="fas <?php echo $servicio['icono'] ?: 'fa-gavel'; ?>"></i>
                        <h3><?php echo htmlspecialchars($servicio['titulo']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 100)) . '...'; ?></p>
                        <a href="servicios.php#servicio-<?php echo $servicio['id']; ?>" class="btn-servicio">Consultar por mi caso</a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align:center;margin-top:30px;">
                    <a href="servicios.php" class="btn-primary">Ver todos los servicios</a>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>