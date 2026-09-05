<?php
// equipo.php - Página del equipo CON IMÁGENES AJUSTADAS
require_once 'includes/db.php';
require_once 'includes/header.php';

// Obtener miembros del equipo activos
$equipo = $pdo->query("SELECT * FROM equipo WHERE activo = 1 ORDER BY orden ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipo - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ============================================
           ESTILOS PARA EQUIPO - FRONTEND
           ============================================ */
        
        /* --- CONTENEDOR DE LA IMAGEN --- */
        .equipo-img-container {
            width: 100%;
            height: 280px;
            overflow: hidden;
            background: var(--gris-claro);
            position: relative;
        }
        
        /* --- IMAGEN --- */
        .equipo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 20%;
            display: block;
            transition: transform 0.4s ease;
        }
        
        .equipo-card:hover .equipo-img {
            transform: scale(1.06);
        }
        
        /* --- PLACEHOLDER CUANDO NO HAY FOTO --- */
        .equipo-img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gris-claro);
            color: #d0d0d0;
        }
        
        .equipo-img-placeholder i {
            font-size: 80px;
        }
        
        /* --- TARJETA DE EQUIPO --- */
        .equipo-card {
            background: var(--blanco);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            max-width: 340px;
            margin: 0 auto;
            width: 100%;
        }
        
        .equipo-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        /* --- INFORMACIÓN --- */
        .equipo-card .info {
            padding: 20px 20px 25px;
            text-align: center;
        }
        
        .equipo-card h3 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            font-size: 20px;
            margin: 0 0 3px 0;
        }
        
        .equipo-card .cargo {
            color: var(--rojo-intenso);
            font-weight: 600;
            font-size: 14px;
            margin: 0 0 10px 0;
        }
        
        .equipo-card .presentacion {
            color: var(--gris-texto);
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }
        
        /* --- GRID RESPONSIVE --- */
        .equipo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 1024px) {
            .equipo-img-container {
                height: 250px;
            }
            
            .equipo-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 25px;
            }
        }
        
        @media (max-width: 768px) {
            .equipo-img-container {
                height: 220px;
            }
            
            .equipo-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 20px;
                padding: 0 15px;
            }
            
            .equipo-card {
                max-width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .equipo-img-container {
                height: 200px;
            }
            
            .equipo-grid {
                grid-template-columns: 1fr;
                max-width: 340px;
                padding: 0 10px;
            }
            
            .equipo-card .info {
                padding: 15px 15px 20px;
            }
            
            .equipo-card h3 {
                font-size: 18px;
            }
            
            .equipo-img-placeholder i {
                font-size: 60px;
            }
        }

        /* --- HERO INTERNO --- */
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
        
        /* --- CTA FINAL --- */
        .cta-section {
            padding: 60px 0;
            background: var(--blanco);
            text-align: center;
        }
        
        .cta-section h2 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        .cta-section h2 .highlight {
            color: var(--rojo-intenso);
        }
        
        .cta-section p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 30px;
            color: var(--gris-texto);
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
    </style>
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Interno -->
        <section class="page-hero">
            <div class="container">
                <h1>
                    Un equipo legal detrás de <span class="highlight">tu caso</span>
                </h1>
                <p>
                    En Defensa Obrera combinamos preparación jurídica, trato humano y defensa firme. 
                    Queremos que comprendas tu situación, conozcas tus alternativas y sepas quién está trabajando en tu caso.
                </p>
            </div>
        </section>
        
        <!-- Equipo -->
        <section style="padding: 80px 0; background: var(--gris-claro);">
            <div class="container">
                <?php if (count($equipo) > 0): ?>
                <div class="equipo-grid">
                    <?php foreach($equipo as $miembro): ?>
                    <div class="equipo-card">
                        <div class="equipo-img-container">
                            <?php if (!empty($miembro['foto']) && file_exists($miembro['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($miembro['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($miembro['nombre']); ?>"
                                     class="equipo-img"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="equipo-img-placeholder">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="info">
                            <h3><?php echo htmlspecialchars($miembro['nombre']); ?></h3>
                            <p class="cargo"><?php echo htmlspecialchars($miembro['cargo']); ?></p>
                            <p class="presentacion"><?php echo htmlspecialchars($miembro['presentacion']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:60px 20px;background:var(--blanco);border-radius:12px;max-width:600px;margin:0 auto;">
                    <i class="fas fa-users" style="font-size:60px;color:#ccc;margin-bottom:20px;display:block;"></i>
                    <h3 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);font-size:24px;margin-bottom:10px;">
                        Próximamente
                    </h3>
                    <p style="color:var(--gris-texto);font-size:16px;">
                        Estamos actualizando la información de nuestro equipo.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- CTA Final -->
        <section class="cta-section">
            <div class="container">
                <h2>
                    ¿Necesitas <span class="highlight">defensa laboral</span>?
                </h2>
                <p>
                    Estamos listos para ayudarte. Contáctanos y te orientaremos sin compromiso.
                </p>
                <a href="contacto.php" class="btn-primary">
                    <i class="fas fa-envelope"></i> Contáctanos ahora
                </a>
            </div>
        </section>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>