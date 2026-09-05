<?php
// recursos.php - Página pública de Recursos (CON GUÍAS DINÁMICAS)
require_once 'includes/db.php';
require_once 'includes/header.php';

// Obtener recursos activos (artículos y guías)
$recursos = $pdo->query("SELECT * FROM recursos WHERE activo = 1 AND tipo != 'faq' ORDER BY created_at DESC")->fetchAll();

// Obtener FAQs activas
$faqs = $pdo->query("SELECT * FROM faqs WHERE activo = 1 ORDER BY orden ASC")->fetchAll();

// Separar artículos y guías
$articulos = [];
$guias = [];
foreach ($recursos as $recurso) {
    if ($recurso['tipo'] == 'articulo') {
        $articulos[] = $recurso;
    } elseif ($recurso['tipo'] == 'guia') {
        $guias[] = $recurso;
    }
}

// Temas predefinidos para guías (solo si no hay guías en BD)
$temas_predefinidos = [
    ['icono' => 'fa-file-signature', 'titulo' => 'Qué hacer si te despiden', 'descripcion' => 'Pasos a seguir después de un despido y cómo proteger tus derechos.'],
    ['icono' => 'fa-pen-fancy', 'titulo' => 'Qué revisar antes de firmar un finiquito', 'descripcion' => 'Aspectos clave que debes verificar antes de firmar tu finiquito.'],
    ['icono' => 'fa-exclamation-triangle', 'titulo' => 'Cómo reunir antecedentes sobre acoso laboral', 'descripcion' => 'Documentos y pruebas necesarios para denunciar acoso laboral.'],
    ['icono' => 'fa-folder-open', 'titulo' => 'Qué documentos y pruebas conservar', 'descripcion' => 'Lista de documentos importantes que debes guardar como trabajador.'],
    ['icono' => 'fa-briefcase-medical', 'titulo' => 'Qué hacer ante un accidente del trabajo', 'descripcion' => 'Pasos a seguir y prestaciones a las que tienes derecho.'],
    ['icono' => 'fa-clock', 'titulo' => 'Por qué es importante consultar oportunamente', 'descripcion' => 'Plazos legales y la importancia de actuar a tiempo.']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ============================================
           RECURSOS - ESTILOS PÚBLICOS
           ============================================ */
        
        .page-hero {
            background: var(--azul-marino);
            color: var(--blanco);
            padding: 60px 0;
            text-align: center;
        }
        
        .page-hero h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 15px;
        }
        
        .page-hero h1 .highlight {
            color: var(--amarillo-calido);
        }
        
        .page-hero p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        /* --- Guías Prácticas --- */
        .guias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .guia-card {
            background: var(--gris-claro);
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            cursor: default;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .guia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .guia-card .icono {
            width: 60px;
            height: 60px;
            background: var(--azul-marino);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: var(--blanco);
            flex-shrink: 0;
        }
        
        .guia-card h4 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .guia-card p {
            font-size: 14px;
            color: var(--gris-texto);
            line-height: 1.6;
            flex: 1;
        }
        
        .guia-card .btn-leer-guia {
            display: inline-block;
            margin-top: 12px;
            color: var(--rojo-intenso);
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .guia-card .btn-leer-guia:hover {
            color: #c40d14;
            gap: 8px;
        }
        
        .guia-card .btn-leer-guia i {
            transition: transform 0.3s;
        }
        
        .guia-card .btn-leer-guia:hover i {
            transform: translateX(5px);
        }
        
        /* --- Artículos --- */
        .articulos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        
        .articulo-card {
            background: var(--blanco);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }
        
        .articulo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }
        
        .articulo-card .imagen {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .articulo-card .imagen-placeholder {
            width: 100%;
            height: 200px;
            background: var(--azul-marino);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blanco);
            font-size: 48px;
        }
        
        .articulo-card .contenido {
            padding: 20px 25px 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .articulo-card .badge {
            display: inline-block;
            background: var(--amarillo-calido);
            color: var(--azul-marino);
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            align-self: flex-start;
        }
        
        .articulo-card .badge-pdf {
            display: inline-block;
            background: #dc3545;
            color: #fff;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 8px;
        }
        
        .articulo-card h3 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            font-size: 18px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .articulo-card .resumen {
            color: var(--gris-texto);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 15px;
            flex: 1;
        }
        
        .articulo-card .btn-leer-mas {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--rojo-intenso);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s;
            margin-top: 5px;
        }
        
        .articulo-card .btn-leer-mas:hover {
            color: #c40d14;
            gap: 12px;
        }
        
        .articulo-card .btn-leer-mas:hover i {
            transform: translateX(5px);
        }
        
        .articulo-card .btn-leer-mas i {
            transition: transform 0.3s;
        }
        
        .articulo-card .footer-articulo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .articulo-card .footer-articulo i {
            margin-right: 5px;
        }
        
        .articulo-card .footer-articulo .autor {
            color: var(--azul-marino);
            font-weight: 600;
        }
        
        /* --- Descarga PDF en artículo --- */
        .btn-pdf-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #dc3545;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s;
            margin-top: 8px;
            align-self: flex-start;
        }
        
        .btn-pdf-download:hover {
            background: #c82333;
            color: #fff;
            transform: scale(1.03);
        }
        
        .btn-pdf-download i {
            font-size: 14px;
        }
        
        /* --- FAQs --- */
        .faq-item {
            background: var(--blanco);
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .faq-question {
            padding: 18px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            color: var(--azul-marino);
            transition: background 0.3s;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
        }
        
        .faq-question:hover {
            background: var(--gris-claro);
        }
        
        .faq-question i {
            transition: transform 0.3s;
            color: var(--rojo-intenso);
            font-size: 18px;
        }
        
        .faq-question.active i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
            padding: 0 25px;
        }
        
        .faq-answer.open {
            max-height: 500px;
            padding: 0 25px 20px;
        }
        
        .faq-answer p {
            color: var(--gris-texto);
            line-height: 1.8;
            margin: 0;
        }
        
        /* --- CTA --- */
        .cta-section {
            padding: 60px 0;
            background: var(--azul-marino);
            text-align: center;
            color: var(--blanco);
        }
        
        .cta-section h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        .cta-section h2 .highlight {
            color: var(--amarillo-calido);
        }
        
        .cta-section p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .btn-primary {
            background: var(--rojo-intenso);
            color: var(--blanco);
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            display: inline-block;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: #c40d14;
        }
        
        .btn-primary i {
            margin-right: 8px;
        }
        
        /* --- Responsive --- */
        @media (max-width: 768px) {
            .page-hero h1 {
                font-size: 28px;
            }
            
            .articulos-grid {
                grid-template-columns: 1fr;
            }
            
            .guias-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .articulo-card .footer-articulo {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
        
        @media (max-width: 480px) {
            .guias-grid {
                grid-template-columns: 1fr;
            }
            
            .page-hero h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Interno -->
        <section class="page-hero">
            <div class="container">
                <h1>
                    Información para conocer y <span class="highlight">proteger tus derechos</span>
                </h1>
                <p>
                    Guías prácticas y respuestas claras para comprender situaciones laborales, organizar tus antecedentes y saber cuándo buscar orientación.
                </p>
            </div>
        </section>
        
        <!-- ============================================
        GUÍAS PRÁCTICAS (DESDE BASE DE DATOS)
        ============================================ -->
        <section style="padding: 80px 0; background: var(--blanco);">
            <div class="container">
                <div class="section-header">
                    <h2>Guías <span class="highlight">prácticas</span></h2>
                    <p>Información útil para entender y enfrentar situaciones laborales.</p>
                </div>
                
                <?php if (count($guias) > 0): ?>
                <div class="guias-grid">
                    <?php foreach($guias as $guia): ?>
                    <div class="guia-card">
                        <div class="icono">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($guia['titulo']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($guia['resumen'] ?? $guia['contenido'] ?? '', 0, 120)) . '...'; ?></p>
                        <a href="recurso.php?id=<?php echo $guia['id']; ?>" class="btn-leer-guia">
                            Leer guía completa <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <!-- Guías predefinidas (fallback) -->
                <div class="guias-grid">
                    <?php foreach($temas_predefinidos as $tema): ?>
                    <div class="guia-card">
                        <div class="icono">
                            <i class="fas <?php echo $tema['icono']; ?>"></i>
                        </div>
                        <h4><?php echo $tema['titulo']; ?></h4>
                        <p><?php echo $tema['descripcion']; ?></p>
                        <a href="contacto.php" class="btn-leer-guia">
                            Consultar <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- ============================================
        ARTÍCULOS (DESDE LA BASE DE DATOS)
        ============================================ -->
        <?php if (count($articulos) > 0): ?>
        <section style="padding: 80px 0; background: var(--gris-claro);">
            <div class="container">
                <div class="section-header">
                    <h2>Artículos <span class="highlight">recientes</span></h2>
                    <p>Información actualizada sobre derechos laborales.</p>
                </div>
                
                <div class="articulos-grid">
                    <?php foreach($articulos as $articulo): ?>
                    <div class="articulo-card">
                        <?php if (!empty($articulo['imagen']) && file_exists($articulo['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($articulo['imagen']); ?>" alt="<?php echo htmlspecialchars($articulo['titulo']); ?>" class="imagen" loading="lazy">
                        <?php else: ?>
                            <div class="imagen-placeholder">
                                <i class="fas fa-newspaper"></i>
                            </div>
                        <?php endif; ?>
                        <div class="contenido">
                            <div>
                                <span class="badge"><?php echo ucfirst(htmlspecialchars($articulo['tipo'])); ?></span>
                                <?php if (!empty($articulo['archivo_pdf']) && file_exists($articulo['archivo_pdf'])): ?>
                                    <span class="badge-pdf"><i class="fas fa-file-pdf"></i> PDF</span>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($articulo['titulo']); ?></h3>
                            <p class="resumen">
                                <?php 
                                $texto_resumen = $articulo['resumen'] ?? $articulo['contenido'] ?? '';
                                echo htmlspecialchars(substr($texto_resumen, 0, 150)) . '...'; 
                                ?>
                            </p>
                            
                            <?php if (!empty($articulo['archivo_pdf']) && file_exists($articulo['archivo_pdf'])): ?>
                                <a href="<?php echo htmlspecialchars($articulo['archivo_pdf']); ?>" target="_blank" class="btn-pdf-download">
                                    <i class="fas fa-file-pdf"></i> Descargar PDF
                                </a>
                            <?php endif; ?>
                            
                            <a href="recurso.php?id=<?php echo $articulo['id']; ?>" class="btn-leer-mas">
                                Leer más <i class="fas fa-arrow-right"></i>
                            </a>
                            
                            <div class="footer-articulo">
                                <span class="autor"><i class="fas fa-user"></i> <?php echo htmlspecialchars($articulo['autor'] ?? 'Defensa Obrera'); ?></span>
                                <span><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($articulo['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- ============================================
        PREGUNTAS FRECUENTES (FAQs)
        ============================================ -->
        <?php if (count($faqs) > 0): ?>
        <section style="padding: 80px 0; background: var(--blanco);">
            <div class="container">
                <div class="section-header">
                    <h2>Preguntas <span class="highlight">frecuentes</span></h2>
                    <p>Respuestas a las dudas más comunes sobre defensa laboral.</p>
                </div>
                
                <div style="max-width: 800px; margin: 0 auto;">
                    <?php foreach($faqs as $faq): ?>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <?php echo htmlspecialchars($faq['pregunta']); ?>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p><?php echo nl2br(htmlspecialchars($faq['respuesta'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- ============================================
        CTA FINAL
        ============================================ -->
        <section class="cta-section">
            <div class="container">
                <h2>
                    ¿Necesitas <span class="highlight">orientación personalizada</span>?
                </h2>
                <p>
                    Cada caso es diferente. Contáctanos y te ayudaremos a encontrar la mejor solución para tu situación.
                </p>
                <a href="contacto.php" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Quiero revisar mi caso
                </a>
            </div>
        </section>
    </main>
    
    <script>
        function toggleFaq(element) {
            const answer = element.nextElementSibling;
            const isOpen = answer.classList.contains('open');
            
            // Cerrar todas las FAQs
            document.querySelectorAll('.faq-answer').forEach(el => {
                el.classList.remove('open');
            });
            document.querySelectorAll('.faq-question').forEach(el => {
                el.classList.remove('active');
            });
            
            if (!isOpen) {
                answer.classList.add('open');
                element.classList.add('active');
            }
        }
    </script>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>