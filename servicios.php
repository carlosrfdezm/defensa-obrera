<?php
// servicios.php - Página de Servicios Legales
require_once 'includes/db.php';
require_once 'includes/header.php';

// Obtener todos los servicios activos
$servicios = $pdo->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY orden")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios Legales - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .servicio-detalle {
            display: none;
            padding: 20px;
            background: var(--blanco);
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--rojo-intenso);
        }
        
        .servicio-detalle.open {
            display: block;
        }
        
        .servicio-card {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .servicio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .servicio-card .btn-toggle {
            background: transparent;
            border: 2px solid var(--rojo-intenso);
            color: var(--rojo-intenso);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .servicio-card .btn-toggle:hover {
            background: var(--rojo-intenso);
            color: var(--blanco);
        }
        
        .servicio-card .btn-toggle.active {
            background: var(--rojo-intenso);
            color: var(--blanco);
        }
        
        .servicio-detalle p {
            color: var(--gris-texto);
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        .servicio-detalle .btn-consultar {
            display: inline-block;
            background: var(--rojo-intenso);
            color: var(--blanco);
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            transition: background 0.3s;
        }
        
        .servicio-detalle .btn-consultar:hover {
            background: #c40d14;
        }
        
        .servicio-icono {
            width: 70px;
            height: 70px;
            background: var(--azul-marino);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 30px;
            color: var(--blanco);
            transition: all 0.3s;
        }
        
        .servicio-card:hover .servicio-icono {
            background: var(--rojo-intenso);
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Interno -->
        <section class="page-hero" style="background:var(--azul-marino);color:var(--blanco);padding:80px 0;text-align:center;">
            <div class="container">
                <h1 style="font-family:'Montserrat',sans-serif;font-size:42px;font-weight:900;margin-bottom:20px;">
                    Servicios legales para defender tus <span style="color:var(--amarillo-calido);">derechos laborales</span>
                </h1>
                <p style="font-size:20px;max-width:700px;margin:0 auto;opacity:0.9;">
                    Si estás enfrentando un problema en tu trabajo o después de una desvinculación, revisamos tus antecedentes y te explicamos cómo podemos ayudarte.
                </p>
                <div style="margin-top:30px;">
                    <a href="#servicios" class="btn-primary" style="font-size:18px;padding:15px 40px;">
                        <i class="fas fa-arrow-down"></i> Ver servicios
                    </a>
                </div>
            </div>
        </section>
        
        <!-- Lista de Servicios -->
        <section id="servicios" style="padding:80px 0;background:var(--gris-claro);">
            <div class="container">
                <div class="section-header">
                    <h2>Nuestros <span class="highlight">servicios legales</span></h2>
                    <p>Conoce todas las áreas en las que podemos ayudarte a defender tus derechos laborales.</p>
                </div>
                
                <?php if (count($servicios) > 0): ?>
                <div class="servicios-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:30px;">
                    <?php foreach($servicios as $index => $servicio): ?>
                    <div class="servicio-card" style="background:var(--blanco);padding:30px;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,0.08);text-align:center;transition:all 0.3s;">
                        <div class="servicio-icono">
                            <i class="fas <?php echo $servicio['icono'] ?: 'fa-gavel'; ?>"></i>
                        </div>
                        <h3 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);font-size:20px;margin-bottom:15px;">
                            <?php echo htmlspecialchars($servicio['titulo']); ?>
                        </h3>
                        <p style="color:var(--gris-texto);font-size:15px;line-height:1.6;margin-bottom:20px;">
                            <?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 120)) . '...'; ?>
                        </p>
                        <button class="btn-toggle" onclick="toggleServicio(<?php echo $servicio['id']; ?>)">
                            <i class="fas fa-plus-circle"></i> Ver más
                        </button>
                        
                        <div id="servicio-<?php echo $servicio['id']; ?>" class="servicio-detalle">
                            <p><?php echo nl2br(htmlspecialchars($servicio['descripcion'])); ?></p>
                            <a href="contacto.php" class="btn-consultar">
                                <i class="fas fa-whatsapp"></i> Consultar por mi caso
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:60px 20px;background:var(--blanco);border-radius:10px;">
                    <i class="fas fa-gavel" style="font-size:60px;color:var(--gris-texto);margin-bottom:20px;"></i>
                    <h3 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);">Próximamente</h3>
                    <p style="color:var(--gris-texto);">Estamos actualizando la información de nuestros servicios.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- CTA - Por qué elegirnos -->
        <section style="padding:80px 0;background:var(--blanco);">
            <div class="container">
                <div class="section-header">
                    <h2>¿Por qué <span class="highlight">Defensa Obrera</span>?</h2>
                    <p>Razones para confiar en nosotros tu defensa laboral.</p>
                </div>
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:30px;">
                    <div style="text-align:center;padding:30px 20px;background:var(--gris-claro);border-radius:10px;">
                        <div style="font-size:48px;color:var(--rojo-intenso);margin-bottom:15px;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);">Solo defendemos a trabajadores</h4>
                        <p style="font-size:14px;color:var(--gris-texto);">Nuestra especialidad es la defensa laboral, sin conflictos de intereses.</p>
                    </div>
                    
                    <div style="text-align:center;padding:30px 20px;background:var(--gris-claro);border-radius:10px;">
                        <div style="font-size:48px;color:var(--rojo-intenso);margin-bottom:15px;">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h4 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);">Especialistas en derecho laboral</h4>
                        <p style="font-size:14px;color:var(--gris-texto);">Equipo con amplia experiencia y conocimiento en materia laboral.</p>
                    </div>
                    
                    <div style="text-align:center;padding:30px 20px;background:var(--gris-claro);border-radius:10px;">
                        <div style="font-size:48px;color:var(--rojo-intenso);margin-bottom:15px;">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h4 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);">Acompañamiento de principio a fin</h4>
                        <p style="font-size:14px;color:var(--gris-texto);">Estamos contigo en cada etapa del proceso, sin soltar tu caso.</p>
                    </div>
                    
                    <div style="text-align:center;padding:30px 20px;background:var(--gris-claro);border-radius:10px;">
                        <div style="font-size:48px;color:var(--rojo-intenso);margin-bottom:15px;">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h4 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);">Comunicación clara y confidencial</h4>
                        <p style="font-size:14px;color:var(--gris-texto);">Transparencia y privacidad garantizadas en todo momento.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- CTA Final -->
        <section style="padding:60px 0;background:var(--azul-marino);color:var(--blanco);text-align:center;">
            <div class="container">
                <h2 style="font-family:'Montserrat',sans-serif;font-size:36px;margin-bottom:20px;">
                    ¿Necesitas <span style="color:var(--amarillo-calido);">defensa laboral</span>?
                </h2>
                <p style="font-size:18px;max-width:600px;margin:0 auto 30px;opacity:0.9;">
                    No enfrentes a la empresa solo. Contáctanos y te orientaremos sin compromiso.
                </p>
                <div style="display:flex;justify-content:center;gap:15px;flex-wrap:wrap;">
                    <a href="contacto.php" class="btn-primary" style="font-size:18px;padding:15px 40px;">
                        <i class="fas fa-envelope"></i> Revisar mi caso
                    </a>
                    <a href="https://wa.me/56973847746" style="background:#25D366;color:var(--blanco);padding:15px 40px;border-radius:30px;text-decoration:none;font-weight:700;font-size:18px;display:inline-flex;align-items:center;gap:10px;">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
        </section>
    </main>
    
    <script>
        function toggleServicio(id) {
            const detalle = document.getElementById('servicio-' + id);
            const btn = detalle.parentElement.querySelector('.btn-toggle');
            
            if (detalle.classList.contains('open')) {
                detalle.classList.remove('open');
                btn.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-plus-circle"></i> Ver más';
            } else {
                // Cerrar otros servicios abiertos
                document.querySelectorAll('.servicio-detalle.open').forEach(el => {
                    el.classList.remove('open');
                    const parentBtn = el.parentElement.querySelector('.btn-toggle');
                    if (parentBtn) {
                        parentBtn.classList.remove('active');
                        parentBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Ver más';
                    }
                });
                
                detalle.classList.add('open');
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-minus-circle"></i> Ver menos';
            }
        }
    </script>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>