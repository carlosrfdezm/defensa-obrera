<?php
// index.php - Página principal
require_once 'includes/db.php';
require_once 'includes/header.php';

// Obtener datos
$compromisos = $pdo->query("SELECT * FROM compromisos WHERE activo = 1 ORDER BY orden")->fetchAll();
$slider_items = $pdo->query("SELECT * FROM slider WHERE activo = 1 ORDER BY orden")->fetchAll();
$servicios_destacados = $pdo->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY orden LIMIT 4")->fetchAll();
?>

<main>
    <!-- Hero -->
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

    <!-- Slider -->
    <section class="slider-section">
        <div class="container">
            <div class="section-header">
                <h2>Trabajadores que confiaron en <span class="highlight">nosotros</span></h2>
            </div>
            <div class="slider-container">
                <div class="slider-track">
                    <?php foreach($slider_items as $item): ?>
                    <div class="slider-slide">
                        <h3><?php echo htmlspecialchars($item['titulo']); ?></h3>
                        <p><?php echo htmlspecialchars($item['descripcion']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-btn prev"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="slider-dots">
                <?php for($i = 0; $i < count($slider_items); $i++): ?>
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