<?php
// resultados.php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Obtener resultados
$resultados = $pdo->query("SELECT * FROM resultados WHERE activo = 1 ORDER BY fecha DESC")->fetchAll();
$testimonios = $pdo->query("SELECT * FROM testimonios WHERE activo = 1 ORDER BY orden")->fetchAll();

$mostrar_resultados = count($resultados) > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Interno -->
        <section class="page-hero" style="background:var(--azul-marino);color:var(--blanco);padding:60px 0;text-align:center;">
            <div class="container">
                <h1 style="font-family:'Montserrat',sans-serif;font-size:42px;font-weight:900;margin-bottom:15px;">
                    Defensa firme. <span style="color:var(--amarillo-calido);">Resultados que podemos respaldar.</span>
                </h1>
                <p style="font-size:18px;max-width:700px;margin:0 auto;">
                    Compartimos casos y soluciones obtenidas, resguardando la identidad y la información de las personas involucradas.
                </p>
            </div>
        </section>
        
        <?php if ($mostrar_resultados): ?>
        <!-- Resultados -->
        <section style="padding:80px 0;background:var(--blanco);">
            <div class="container">
                <div class="section-header">
                    <h2>Casos <span class="highlight">resueltos</span></h2>
                    <p>Conoce algunos de los casos en los que hemos trabajado.</p>
                </div>
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(350px,1fr));gap:30px;">
                    <?php foreach($resultados as $resultado): ?>
                    <div style="background:var(--gris-claro);border-radius:10px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,0.05);transition:transform 0.3s;">
                        <?php if (!empty($resultado['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($resultado['imagen']); ?>" alt="<?php echo htmlspecialchars($resultado['titulo']); ?>" style="width:100%;height:200px;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:200px;background:var(--azul-marino);display:flex;align-items:center;justify-content:center;color:var(--blanco);font-size:48px;">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                        <?php endif; ?>
                        <div style="padding:25px;">
                            <h3 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);font-size:18px;margin-bottom:10px;">
                                <?php echo htmlspecialchars($resultado['titulo']); ?>
                            </h3>
                            <div style="margin-bottom:15px;">
                                <div style="background:var(--rojo-intenso);color:var(--blanco);padding:2px 12px;border-radius:20px;display:inline-block;font-size:12px;font-weight:600;">
                                    <?php echo date('d/m/Y', strtotime($resultado['fecha'])); ?>
                                </div>
                            </div>
                            <p style="color:var(--gris-texto);font-size:14px;line-height:1.6;">
                                <strong>Situación:</strong> <?php echo htmlspecialchars($resultado['situacion']); ?>
                            </p>
                            <p style="color:var(--gris-texto);font-size:14px;line-height:1.6;margin-top:5px;">
                                <strong>Solución:</strong> <?php echo htmlspecialchars($resultado['solucion']); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Aclaración legal -->
                <div style="text-align:center;margin-top:40px;padding:20px;background:var(--gris-claro);border-radius:10px;border-left:4px solid var(--amarillo-calido);">
                    <p style="font-size:14px;color:var(--gris-texto);max-width:600px;margin:0 auto;">
                        <i class="fas fa-info-circle" style="color:var(--amarillo-calido);"></i>
                        Cada caso es diferente. Los resultados anteriores no garantizan resultados futuros.
                    </p>
                </div>
            </div>
        </section>
        
        <!-- Testimonios -->
        <?php if (count($testimonios) > 0): ?>
        <section style="padding:80px 0;background:var(--gris-claro);">
            <div class="container">
                <div class="section-header">
                    <h2>Lo que dicen <span class="highlight">nuestros clientes</span></h2>
                </div>
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;">
                    <?php foreach($testimonios as $testimonio): ?>
                    <div style="background:var(--blanco);padding:30px;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,0.05);text-align:center;">
                        <i class="fas fa-quote-left" style="font-size:32px;color:var(--amarillo-calido);margin-bottom:15px;"></i>
                        <p style="font-style:italic;color:var(--gris-texto);margin-bottom:20px;">
                            "<?php echo htmlspecialchars($testimonio['testimonio']); ?>"
                        </p>
                        <div style="display:flex;align-items:center;justify-content:center;gap:15px;">
                            <?php if (!empty($testimonio['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($testimonio['foto']); ?>" alt="<?php echo htmlspecialchars($testimonio['nombre']); ?>" style="width:50px;height:50px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:50px;height:50px;border-radius:50%;background:var(--gris-claro);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user" style="font-size:24px;color:#999;"></i>
                                </div>
                            <?php endif; ?>
                            <div style="text-align:left;">
                                <strong style="color:var(--azul-marino);"><?php echo htmlspecialchars($testimonio['nombre']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Sección Oculto (sin resultados) -->
        <section style="padding:80px 0;background:var(--blanco);">
            <div class="container">
                <div style="text-align:center;padding:60px 20px;background:var(--gris-claro);border-radius:10px;">
                    <i class="fas fa-chart-bar" style="font-size:60px;color:var(--azul-marino);margin-bottom:20px;"></i>
                    <h2 style="font-family:'Montserrat',sans-serif;color:var(--azul-marino);font-size:28px;margin-bottom:15px;">
                        Próximamente compartiremos <span style="color:var(--rojo-intenso);">nuestros resultados</span>
                    </h2>
                    <p style="font-size:18px;color:var(--gris-texto);max-width:600px;margin:0 auto 25px;">
                        Estamos preparando los casos que podremos compartir, respetando siempre la confidencialidad de nuestros clientes.
                    </p>
                    <a href="contacto.php" class="btn-primary">Consultar por mi caso</a>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>