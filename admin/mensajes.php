<?php
// admin/mensajes.php - Gestión de Mensajes (ACTUALIZADO)
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// Marcar como leído
if (isset($_GET['marcar_leido'])) {
    $stmt = $pdo->prepare("UPDATE mensajes SET leido = 1 WHERE id = ?");
    $stmt->execute([$_GET['marcar_leido']]);
    header('Location: mensajes.php');
    exit;
}

// Marcar como no leído
if (isset($_GET['marcar_no_leido'])) {
    $stmt = $pdo->prepare("UPDATE mensajes SET leido = 0 WHERE id = ?");
    $stmt->execute([$_GET['marcar_no_leido']]);
    header('Location: mensajes.php');
    exit;
}

// Eliminar mensaje
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM mensajes WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header('Location: mensajes.php');
    exit;
}

// Marcar todos como leídos
if (isset($_GET['marcar_todos_leidos'])) {
    $pdo->query("UPDATE mensajes SET leido = 1 WHERE leido = 0");
    header('Location: mensajes.php');
    exit;
}

// Obtener mensajes
$mensajes = $pdo->query("SELECT * FROM mensajes ORDER BY created_at DESC")->fetchAll();
$no_leidos = $pdo->query("SELECT COUNT(*) FROM mensajes WHERE leido = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .mensaje-item {
            background: var(--blanco);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #ccc;
            transition: all 0.3s;
        }
        
        .mensaje-item.no-leido {
            border-left-color: var(--rojo-intenso);
            background: #fff5f5;
        }
        
        .mensaje-item:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .mensaje-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        
        .mensaje-header h3 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            margin: 0;
            font-size: 18px;
        }
        
        .mensaje-fecha {
            font-size: 14px;
            color: #999;
        }
        
        .mensaje-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
            padding: 15px;
            background: var(--gris-claro);
            border-radius: 5px;
        }
        
        .mensaje-info-item {
            font-size: 14px;
        }
        
        .mensaje-info-item strong {
            color: var(--azul-marino);
        }
        
        .mensaje-relato {
            background: var(--gris-claro);
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-style: italic;
            line-height: 1.8;
        }
        
        .mensaje-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .mensaje-actions a {
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-leido {
            background: var(--amarillo-calido);
            color: var(--azul-marino);
        }
        
        .btn-leido:hover {
            background: #e6a700;
        }
        
        .btn-no-leido {
            background: #6c757d;
            color: var(--blanco);
        }
        
        .btn-no-leido:hover {
            background: #5a6268;
        }
        
        .btn-eliminar {
            background: var(--rojo-intenso);
            color: var(--blanco);
        }
        
        .btn-eliminar:hover {
            background: #c40d14;
        }
        
        .btn-responder {
            background: var(--azul-marino);
            color: var(--blanco);
        }
        
        .btn-responder:hover {
            background: #002a4a;
        }
        
        .badge-no-leido {
            background: var(--rojo-intenso);
            color: var(--blanco);
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .admin-actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .admin-actions-bar .btn-primary {
            background: var(--rojo-intenso);
            color: var(--blanco);
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-actions-bar .btn-primary:hover {
            background: #c40d14;
        }
        
        .mensaje-autorizacion {
            display: inline-block;
            background: #28a745;
            color: var(--blanco);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .mensaje-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .mensaje-info {
                grid-template-columns: 1fr;
            }
            
            .mensaje-actions {
                flex-direction: column;
            }
            
            .mensaje-actions a {
                width: 100%;
                justify-content: center;
            }
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
                <a href="recursos.php"><i class="fas fa-book"></i> Recursos</a>
                <a href="mensajes.php" class="active"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="usuarios.php"><i class="fas fa-user-cog"></i> Usuarios</a>
                <a href="cambiar_password.php"><i class="fas fa-key"></i> Cambiar contraseña</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>
                    Mensajes de Contacto 
                    <?php if ($no_leidos > 0): ?>
                        <span style="background:var(--rojo-intenso);color:var(--blanco);padding:2px 15px;border-radius:20px;font-size:14px;margin-left:10px;">
                            <?php echo $no_leidos; ?> no leídos
                        </span>
                    <?php endif; ?>
                </h1>
            </header>
            
            <div class="admin-content">
                <div class="admin-actions-bar">
                    <span style="color:var(--gris-texto);font-size:14px;">
                        <i class="fas fa-envelope"></i> Total: <?php echo count($mensajes); ?> mensajes
                    </span>
                    <?php if ($no_leidos > 0): ?>
                        <a href="?marcar_todos_leidos" class="btn-primary" onclick="return confirm('¿Marcar todos los mensajes como leídos?')">
                            <i class="fas fa-check-double"></i> Marcar todos como leídos
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if (count($mensajes) === 0): ?>
                    <div style="text-align:center;padding:80px 20px;background:var(--blanco);border-radius:10px;">
                        <i class="fas fa-inbox" style="font-size:80px;color:#ddd;margin-bottom:20px;"></i>
                        <h3 style="color:var(--gris-texto);font-family:'Montserrat',sans-serif;">No hay mensajes</h3>
                        <p style="color:#999;">Aún no se han recibido consultas desde el formulario de contacto.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($mensajes as $mensaje): ?>
                    <div class="mensaje-item <?php echo $mensaje['leido'] ? '' : 'no-leido'; ?>">
                        <div class="mensaje-header">
                            <h3>
                                <?php echo htmlspecialchars($mensaje['nombre']); ?>
                                <?php if (!$mensaje['leido']): ?>
                                    <span class="badge-no-leido">Nuevo</span>
                                <?php endif; ?>
                                <?php if ($mensaje['autorizacion_datos']): ?>
                                    <span class="mensaje-autorizacion"><i class="fas fa-check"></i> Datos autorizados</span>
                                <?php endif; ?>
                            </h3>
                            <span class="mensaje-fecha">
                                <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($mensaje['created_at'])); ?>
                            </span>
                        </div>
                        
                        <div class="mensaje-info">
                            <div class="mensaje-info-item">
                                <strong>📞 Teléfono:</strong> <?php echo htmlspecialchars($mensaje['telefono']); ?>
                            </div>
                            <div class="mensaje-info-item">
                                <strong>✉️ Email:</strong> <?php echo htmlspecialchars($mensaje['email']); ?>
                            </div>
                            <div class="mensaje-info-item">
                                <strong>📍 Comuna:</strong> <?php echo htmlspecialchars($mensaje['comuna'] ?: 'No especificada'); ?>
                            </div>
                            <div class="mensaje-info-item">
                                <strong>💼 Situación:</strong> <?php echo htmlspecialchars($mensaje['situacion_laboral'] ?: 'No especificada'); ?>
                            </div>
                            <div class="mensaje-info-item">
                                <strong>📅 Fecha hechos:</strong> <?php echo $mensaje['fecha_hechos'] ? date('d/m/Y', strtotime($mensaje['fecha_hechos'])) : 'No especificada'; ?>
                            </div>
                            <div class="mensaje-info-item">
                                <strong>📱 Medio preferido:</strong> <?php echo htmlspecialchars($mensaje['medio_contacto'] ?: 'No especificado'); ?>
                            </div>
                        </div>
                        
                        <div class="mensaje-relato">
                            <strong>📝 Relato:</strong><br>
                            <?php echo nl2br(htmlspecialchars($mensaje['relato'])); ?>
                        </div>
                        
                        <div class="mensaje-actions">
                            <?php if ($mensaje['leido']): ?>
                                <a href="?marcar_no_leido=<?php echo $mensaje['id']; ?>" class="btn-no-leido">
                                    <i class="fas fa-eye-slash"></i> Marcar como no leído
                                </a>
                            <?php else: ?>
                                <a href="?marcar_leido=<?php echo $mensaje['id']; ?>" class="btn-leido">
                                    <i class="fas fa-check"></i> Marcar como leído
                                </a>
                            <?php endif; ?>
                            
                            <a href="mailto:<?php echo htmlspecialchars($mensaje['email']); ?>?subject=Respuesta a tu consulta - Defensa Obrera&body=Hola <?php echo urlencode($mensaje['nombre']); ?>,%0D%0A%0D%0AGracias por contactarnos. Hemos recibido tu consulta y nos comunicaremos contigo a la brevedad.%0D%0A%0D%0ASaludos cordiales,%0D%0AEquipo Defensa Obrera" class="btn-responder" target="_blank">
                                <i class="fas fa-reply"></i> Responder por correo
                            </a>
                            
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $mensaje['telefono']); ?>?text=Hola%20<?php echo urlencode($mensaje['nombre']); ?>%2C%20hemos%20recibido%20tu%20consulta%20y%20nos%20comunicaremos%20contigo%20a%20la%20brevedad." class="btn-responder" target="_blank" style="background:#25D366;">
                                <i class="fab fa-whatsapp"></i> Responder por WhatsApp
                            </a>
                            
                            <a href="?eliminar=<?php echo $mensaje['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar este mensaje?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>