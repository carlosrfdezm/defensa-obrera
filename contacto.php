<?php
// contacto.php - Página de Contacto
require_once 'includes/db.php';
require_once 'includes/header.php';

$enviado = false;
$error = false;
$mensaje_error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos requeridos
    $errores = [];
    
    if (empty($_POST['nombre'])) {
        $errores[] = 'El nombre es obligatorio';
    }
    if (empty($_POST['telefono'])) {
        $errores[] = 'El teléfono es obligatorio';
    }
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido';
    }
    if (empty($_POST['relato'])) {
        $errores[] = 'El relato de tu situación es obligatorio';
    }
    if (!isset($_POST['autorizacion']) || $_POST['autorizacion'] != '1') {
        $errores[] = 'Debes aceptar la política de privacidad';
    }
    
    if (empty($errores)) {
        try {
            // Guardar en la base de datos
            $stmt = $pdo->prepare("INSERT INTO mensajes 
                (nombre, telefono, email, comuna, situacion_laboral, fecha_hechos, relato, medio_contacto, autorizacion_datos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                trim($_POST['nombre']),
                trim($_POST['telefono']),
                trim($_POST['email']),
                trim($_POST['comuna']),
                trim($_POST['situacion_laboral']),
                !empty($_POST['fecha_hechos']) ? $_POST['fecha_hechos'] : null,
                trim($_POST['relato']),
                trim($_POST['medio_contacto']),
                isset($_POST['autorizacion']) ? 1 : 0
            ]);
            
            $enviado = true;
            
            // Enviar notificación por correo (opcional)
            // mail('abogados@defensaobrera.cl', 'Nueva consulta', 'Se ha recibido una nueva consulta');
            
        } catch(PDOException $e) {
            $error = true;
            $mensaje_error = 'Hubo un error al enviar tu mensaje. Por favor, intenta nuevamente.';
        }
    } else {
        $error = true;
        $mensaje_error = implode('<br>', $errores);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Defensa Obrera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .contacto-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .contacto-info-item {
            background: var(--blanco);
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .contacto-info-item:hover {
            transform: translateY(-5px);
        }
        
        .contacto-info-item .icono {
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
        }
        
        .contacto-info-item h4 {
            font-family: 'Montserrat', sans-serif;
            color: var(--azul-marino);
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .contacto-info-item p {
            color: var(--gris-texto);
            font-size: 14px;
            margin: 0;
        }
        
        .contacto-info-item a {
            color: var(--rojo-intenso);
            text-decoration: none;
            font-weight: 600;
        }
        
        .contacto-info-item a:hover {
            text-decoration: underline;
        }
        
        .contact-form {
            background: var(--blanco);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.08);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .contact-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .contact-form .form-group {
            margin-bottom: 20px;
        }
        
        .contact-form .form-group label {
            display: block;
            font-weight: 600;
            color: var(--azul-marino);
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .contact-form .form-group label .required {
            color: var(--rojo-intenso);
        }
        
        .contact-form .form-group input,
        .contact-form .form-group select,
        .contact-form .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s;
        }
        
        .contact-form .form-group input:focus,
        .contact-form .form-group select:focus,
        .contact-form .form-group textarea:focus {
            border-color: var(--rojo-intenso);
            outline: none;
        }
        
        .contact-form .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .contact-form .form-group .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .contact-form .form-group.checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .contact-form .form-group.checkbox input {
            width: auto;
            margin-top: 3px;
        }
        
        .contact-form .form-group.checkbox label {
            font-weight: 400;
            font-size: 14px;
        }
        
        .contact-form .btn-submit {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            font-weight: 700;
            background: var(--rojo-intenso);
            color: var(--blanco);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .contact-form .btn-submit:hover {
            background: #c40d14;
        }
        
        .contact-form .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .alert-success i {
            font-size: 60px;
            color: #28a745;
            display: block;
            margin-bottom: 15px;
        }
        
        .alert-success h3 {
            margin-bottom: 10px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .map-container {
            margin-top: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .contact-form .form-row {
                grid-template-columns: 1fr;
            }
            
            .contact-form {
                padding: 20px;
            }
            
            .contacto-info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .contacto-info-grid {
                grid-template-columns: 1fr;
            }
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
                    Cuéntanos tu caso. <span style="color:var(--amarillo-calido);">Estamos para ayudarte.</span>
                </h1>
                <p style="font-size:20px;max-width:700px;margin:0 auto;opacity:0.9;">
                    Explícanos brevemente lo ocurrido y déjanos un medio de contacto. 
                    Nos comunicaremos contigo para conocer tus antecedentes y explicarte los próximos pasos.
                </p>
            </div>
        </section>
        
        <!-- Información de Contacto -->
        <section style="padding:60px 0 0 0;background:var(--gris-claro);">
            <div class="container">
                <div class="contacto-info-grid">
                    <div class="contacto-info-item">
                        <div class="icono"><i class="fas fa-phone"></i></div>
                        <h4>Teléfono</h4>
                        <p><a href="tel:+56973847746">+56 9 7384 7746</a></p>
                    </div>
                    
                    <div class="contacto-info-item">
                        <div class="icono"><i class="fab fa-whatsapp" style="color:#25D366;"></i></div>
                        <h4>WhatsApp</h4>
                        <p><a href="https://wa.me/56973847746" target="_blank">+56 9 7384 7746</a></p>
                    </div>
                    
                    <div class="contacto-info-item">
                        <div class="icono"><i class="fas fa-envelope"></i></div>
                        <h4>Correo</h4>
                        <p><a href="mailto:abogados@defensaobrera.cl">abogados@defensaobrera.cl</a></p>
                    </div>
                    
                    <div class="contacto-info-item">
                        <div class="icono"><i class="fas fa-clock"></i></div>
                        <h4>Horario</h4>
                        <p>08:00 - 16:00</p>
                    </div>
                    
                    <div class="contacto-info-item">
                        <div class="icono"><i class="fas fa-map-marker-alt"></i></div>
                        <h4>Dirección</h4>
                        <p>Iquique</p>
                    </div>
                    
                    <div class="contacto-info-item">
                        <div class="icono"><i class="fas fa-globe"></i></div>
                        <h4>Sitio Web</h4>
                        <p><a href="https://defensaobrera.cl">defensaobrera.cl</a></p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Formulario -->
        <section style="padding:60px 0 80px 0;background:var(--gris-claro);">
            <div class="container">
                <?php if ($enviado): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i>
                        <h3>¡Mensaje enviado correctamente!</h3>
                        <p style="font-size:16px;">Nos comunicaremos contigo pronto a través de tu medio de contacto preferido.</p>
                        <p style="font-size:14px;margin-top:10px;opacity:0.8;">
                            <i class="fas fa-clock"></i> Tiempo de respuesta estimado: 24 horas hábiles
                        </p>
                        <a href="index.php" class="btn-primary" style="display:inline-block;margin-top:20px;padding:12px 30px;text-decoration:none;">
                            <i class="fas fa-home"></i> Volver al inicio
                        </a>
                    </div>
                <?php else: ?>
                    
                    <?php if ($error): ?>
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Por favor, corrige los siguientes errores:</strong>
                            <br>
                            <?php echo $mensaje_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nombre completo <span class="required">*</span></label>
                                <input type="text" name="nombre" required placeholder="Ej: Juan Pérez" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Teléfono o WhatsApp <span class="required">*</span></label>
                                <input type="text" name="telefono" required placeholder="+56 9 1234 5678" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Correo electrónico <span class="required">*</span></label>
                                <input type="email" name="email" required placeholder="tu@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Comuna</label>
                                <input type="text" name="comuna" placeholder="Ej: Iquique" value="<?php echo isset($_POST['comuna']) ? htmlspecialchars($_POST['comuna']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Situación laboral</label>
                                <select name="situacion_laboral">
                                    <option value="">Selecciona...</option>
                                    <option value="Trabajando" <?php echo (isset($_POST['situacion_laboral']) && $_POST['situacion_laboral'] == 'Trabajando') ? 'selected' : ''; ?>>Trabajando</option>
                                    <option value="Cesante" <?php echo (isset($_POST['situacion_laboral']) && $_POST['situacion_laboral'] == 'Cesante') ? 'selected' : ''; ?>>Cesante</option>
                                    <option value="Con finiquito pendiente" <?php echo (isset($_POST['situacion_laboral']) && $_POST['situacion_laboral'] == 'Con finiquito pendiente') ? 'selected' : ''; ?>>Con finiquito pendiente</option>
                                    <option value="En juicio" <?php echo (isset($_POST['situacion_laboral']) && $_POST['situacion_laboral'] == 'En juicio') ? 'selected' : ''; ?>>En juicio</option>
                                    <option value="Despedido" <?php echo (isset($_POST['situacion_laboral']) && $_POST['situacion_laboral'] == 'Despedido') ? 'selected' : ''; ?>>Despedido</option>
                                    <option value="Otro" <?php echo (isset($_POST['situacion_laboral']) && $_POST['situacion_laboral'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Fecha aproximada de los hechos</label>
                                <input type="date" name="fecha_hechos" value="<?php echo isset($_POST['fecha_hechos']) ? $_POST['fecha_hechos'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Relato breve de tu situación <span class="required">*</span></label>
                            <textarea name="relato" required placeholder="Describe brevemente lo que ha ocurrido..."><?php echo isset($_POST['relato']) ? htmlspecialchars($_POST['relato']) : ''; ?></textarea>
                            <div class="help-text">Mínimo 20 caracteres. Cuanto más detalle, mejor podremos ayudarte.</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Medio de contacto preferido</label>
                            <select name="medio_contacto">
                                <option value="whatsapp" <?php echo (isset($_POST['medio_contacto']) && $_POST['medio_contacto'] == 'whatsapp') ? 'selected' : ''; ?>>WhatsApp</option>
                                <option value="email" <?php echo (isset($_POST['medio_contacto']) && $_POST['medio_contacto'] == 'email') ? 'selected' : ''; ?>>Correo electrónico</option>
                                <option value="telefono" <?php echo (isset($_POST['medio_contacto']) && $_POST['medio_contacto'] == 'telefono') ? 'selected' : ''; ?>>Teléfono</option>
                            </select>
                        </div>
                        
                        <div class="form-group checkbox">
                            <input type="checkbox" name="autorizacion" value="1" id="autorizacion" <?php echo isset($_POST['autorizacion']) ? 'checked' : ''; ?>>
                            <label for="autorizacion">
                                Acepto la <a href="#" style="color:var(--rojo-intenso);text-decoration:underline;">política de privacidad</a> y autorizo el tratamiento de mis datos para fines de contacto y evaluación de mi caso.
                                <span class="required">*</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Quiero que revisen mi caso
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Mapa (Iquique) -->
        <section style="padding:0 0 60px 0;background:var(--gris-claro);">
            <div class="container">
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3718.456!2d-70.1314857!3d-20.2257412!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9153860f0e34b58f%3A0x2b3a7f3b8f3b8f3b!2sIquique%2C%20Regi%C3%B3n%20de%20Tarapac%C3%A1!5e0!3m2!1ses!2scl!4v1700000000000" 
                        width="100%" 
                        height="400" 
                        style="border:0;display:block;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </section>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Validación adicional en cliente
                    const nombre = document.querySelector('input[name="nombre"]').value.trim();
                    const telefono = document.querySelector('input[name="telefono"]').value.trim();
                    const email = document.querySelector('input[name="email"]').value.trim();
                    const relato = document.querySelector('textarea[name="relato"]').value.trim();
                    const autorizacion = document.querySelector('input[name="autorizacion"]');
                    
                    let errores = [];
                    
                    if (nombre.length < 3) {
                        errores.push('El nombre debe tener al menos 3 caracteres');
                    }
                    
                    if (telefono.length < 8) {
                        errores.push('El teléfono no es válido');
                    }
                    
                    if (!email || !email.includes('@')) {
                        errores.push('El correo electrónico no es válido');
                    }
                    
                    if (relato.length < 20) {
                        errores.push('El relato debe tener al menos 20 caracteres');
                    }
                    
                    if (!autorizacion.checked) {
                        errores.push('Debes aceptar la política de privacidad');
                    }
                    
                    if (errores.length > 0) {
                        e.preventDefault();
                        alert('Por favor, corrige los siguientes errores:\n\n- ' + errores.join('\n- '));
                        return false;
                    }
                    
                    // Deshabilitar botón para evitar doble envío
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                });
            }
        });
    </script>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>