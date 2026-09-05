<?php
// como-trabajamos.php - Página con imágenes en cada etapa
require_once 'includes/db.php';
require_once 'includes/header.php';

// Definir las etapas con imágenes
$etapas = [
    [
        'numero' => 1,
        'titulo' => 'Primer contacto',
        'descripcion' => 'Escuchamos tu caso, conocemos lo ocurrido y resolvemos tus primeras dudas. Te damos un espacio seguro para contar tu situación sin presión.',
        'icono' => 'fa-headset',
        'imagen' => 'img/etapas/etapa-1-contacto.jpg',
        'color' => '#E5161D',
        'tiempo' => '24-48 horas'
    ],
    [
        'numero' => 2,
        'titulo' => 'Revisión y pruebas',
        'descripcion' => 'Analizamos documentos y reunimos los antecedentes necesarios para preparar tu defensa. Identificamos las fortalezas y debilidades de tu caso.',
        'icono' => 'fa-search',
        'imagen' => 'img/etapas/etapa-2-revision.jpg',
        'color' => '#001B35',
        'tiempo' => '3-5 días'
    ],
    [
        'numero' => 3,
        'titulo' => 'Estrategia',
        'descripcion' => 'Te explicamos las alternativas, sus alcances y los próximos pasos. Definimos juntos la mejor ruta para defender tus derechos laborales.',
        'icono' => 'fa-chess',
        'imagen' => 'img/etapas/etapa-3-estrategia.jpg',
        'color' => '#FFB900',
        'tiempo' => '2-3 días'
    ],
    [
        'numero' => 4,
        'titulo' => 'Negociación con la empresa',
        'descripcion' => 'Defendemos tu posición en las gestiones y negociaciones que correspondan. Buscamos acuerdos justos sin perder de vista tus derechos.',
        'icono' => 'fa-handshake',
        'imagen' => 'img/etapas/etapa-4-negociacion.jpg',
        'color' => '#E5161D',
        'tiempo' => 'Variable'
    ],
    [
        'numero' => 5,
        'titulo' => 'Dirección del Trabajo',
        'descripcion' => 'Te representamos en las actuaciones administrativas que requiera tu caso. Presentamos descargos y gestionamos citaciones ante la DT.',
        'icono' => 'fa-building',
        'imagen' => 'img/etapas/etapa-5-direccion.jpg',
        'color' => '#001B35',
        'tiempo' => '15-30 días'
    ],
    [
        'numero' => 6,
        'titulo' => 'Demanda y audiencia',
        'descripcion' => 'Cuando corresponde, presentamos acciones judiciales y te representamos ante los tribunales. Te acompañamos en cada audiencia y trámite.',
        'icono' => 'fa-gavel',
        'imagen' => 'img/etapas/etapa-6-demanda.jpg',
        'color' => '#FFB900',
        'tiempo' => '60-120 días'
    ],
    [
        'numero' => 7,
        'titulo' => 'Resolución y seguimiento',
        'descripcion' => 'Te explicamos el resultado y las actuaciones posteriores que sean necesarias. Hacemos seguimiento hasta el cierre definitivo de tu caso.',
        'icono' => 'fa-flag-checkered',
        'imagen' => 'img/etapas/etapa-7-resolucion.jpg',
        'color' => '#E5161D',
        'tiempo' => 'Hasta el cierre'
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cómo trabajamos - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ============================================
           ESTILOS PARA CÓMO TRABAJAMOS
           ============================================ */
        
        /* --- HERO --- */
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
        
        /* --- ETAPAS --- */
        .etapa-item {
            display: flex;
            align-items: stretch;
            gap: 0;
            margin-bottom: 40px;
            background: var(--blanco);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .etapa-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.12);
        }
        
        /* --- LADO DE LA IMAGEN --- */
        .etapa-imagen {
            flex: 0 0 40%;
            min-height: 280px;
            overflow: hidden;
            position: relative;
        }
        
        .etapa-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }
        
        .etapa-item:hover .etapa-imagen img {
            transform: scale(1.05);
        }
        
        /* --- BADGE SOBRE LA IMAGEN --- */
        .etapa-imagen .etapa-badge-img {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--azul-marino);
            color: var(--blanco);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        
        .etapa-imagen .etapa-badge-img i {
            font-size: 16px;
        }
        
        /* --- LADO DEL CONTENIDO --- */
        .etapa-contenido {
            flex: 1;
            padding: 35px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .etapa-contenido .etapa-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        
        .etapa-contenido .etapa-numero {
            background: var(--rojo-intenso);
            color: var(--blanco);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .etapa-contenido .etapa-titulo {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        
        .etapa-contenido .etapa-icono {
            font-size: 28px;
            color: var(--rojo-intenso);
            margin-left: auto;
        }
        
        .etapa-contenido .etapa-descripcion {
            color: var(--gris-texto);
            font-size: 16px;
            line-height: 1.8;
            margin: 8px 0 15px 0;
        }
        
        .etapa-contenido .etapa-footer {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 5px;
        }
        
        .etapa-contenido .etapa-tiempo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--gris-texto);
        }
        
        .etapa-contenido .etapa-tiempo i {
            color: var(--amarillo-calido);
        }
        
        .etapa-contenido .etapa-tag {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* --- ETAPAS ALTERNANTES (imagen a la derecha) --- */
        .etapa-item.reverse {
            flex-direction: row-reverse;
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 992px) {
            .etapa-item {
                flex-direction: column !important;
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .etapa-imagen {
                flex: 0 0 220px;
                min-height: 220px;
            }
            
            .etapa-contenido {
                padding: 25px 30px 30px;
            }
            
            .etapa-contenido .etapa-titulo {
                font-size: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .etapa-imagen {
                flex: 0 0 180px;
                min-height: 180px;
            }
            
            .etapa-contenido {
                padding: 20px 20px 25px;
            }
            
            .etapa-contenido .etapa-titulo {
                font-size: 18px;
            }
            
            .etapa-contenido .etapa-header {
                gap: 10px;
            }
            
            .etapa-contenido .etapa-numero {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
            
            .etapa-contenido .etapa-descripcion {
                font-size: 15px;
            }
            
            .etapa-imagen .etapa-badge-img {
                font-size: 12px;
                padding: 5px 12px;
                top: 12px;
                left: 12px;
            }
        }

        /* --- CTA FINAL --- */
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
        
        .cta-section .btn-primary {
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
        
        .cta-section .btn-primary:hover {
            background: #c40d14;
        }
        
        .cta-section .btn-primary i {
            margin-right: 8px;
        }
        
        /* --- RESPONSIVE PARA CTA --- */
        @media (max-width: 576px) {
            .cta-section h2 {
                font-size: 26px;
            }
            
            .cta-section .btn-primary {
                padding: 12px 30px;
                font-size: 16px;
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
                    Te acompañamos en cada etapa.<br>
                    <span class="highlight">No soltamos tu caso.</span>
                </h1>
                <p>
                    Desde el primer contacto hasta el cierre, tendrás orientación, preparación y una explicación clara de los pasos que correspondan a tu situación.
                </p>
            </div>
        </section>
        
        <!-- Etapas -->
        <section style="padding: 70px 0; background: var(--gris-claro);">
            <div class="container">
                <div class="section-header">
                    <h2>Nuestro <span class="highlight">proceso de trabajo</span></h2>
                    <p>Cada caso es único, pero nuestro compromiso y metodología son siempre los mismos.</p>
                </div>
                
                <?php foreach($etapas as $index => $etapa): ?>
                <div class="etapa-item <?php echo ($index % 2 == 0) ? '' : 'reverse'; ?>">
                    <!-- Imagen -->
                    <div class="etapa-imagen">
                        <?php if (file_exists($etapa['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($etapa['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($etapa['titulo']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:var(--azul-marino);display:flex;align-items:center;justify-content:center;color:var(--blanco);font-size:48px;">
                                <i class="fas <?php echo $etapa['icono']; ?>"></i>
                            </div>
                        <?php endif; ?>
                        <div class="etapa-badge-img">
                            <i class="fas fa-flag"></i> Etapa <?php echo $etapa['numero']; ?>
                        </div>
                    </div>
                    
                    <!-- Contenido -->
                    <div class="etapa-contenido">
                        <div class="etapa-header">
                            <span class="etapa-numero"><?php echo $etapa['numero']; ?></span>
                            <h3 class="etapa-titulo"><?php echo htmlspecialchars($etapa['titulo']); ?></h3>
                            <span class="etapa-icono"><i class="fas <?php echo $etapa['icono']; ?>"></i></span>
                        </div>
                        
                        <p class="etapa-descripcion"><?php echo htmlspecialchars($etapa['descripcion']); ?></p>
                        
                        <div class="etapa-footer">
                            <span class="etapa-tiempo">
                                <i class="fas fa-clock"></i> Tiempo estimado: <?php echo $etapa['tiempo']; ?>
                            </span>
                            <span class="etapa-tag" style="background:<?php echo $etapa['color']; ?>;color:<?php echo in_array($etapa['color'], ['#001B35', '#E5161D']) ? '#fff' : '#001B35'; ?>;">
                                Paso <?php echo $etapa['numero']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- CTA Final -->
        <section class="cta-section">
            <div class="container">
                <h2>
                    ¿Listo para <span class="highlight">empezar</span>?
                </h2>
                <p>
                    Contáctanos y te guiaremos en cada paso del proceso. Tu caso es importante para nosotros.
                </p>
                <a href="contacto.php" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Quiero revisar mi caso
                </a>
            </div>
        </section>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>