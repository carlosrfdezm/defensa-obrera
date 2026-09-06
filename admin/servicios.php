<?php
// admin/servicios.php - Gestión de Servicios
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// Procesar acciones
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO servicios (titulo, descripcion, icono, orden, activo) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['titulo'], $_POST['descripcion'], $_POST['icono'], $_POST['orden'], $_POST['activo'] ?? 1]);
                $mensaje = 'Servicio agregado correctamente';
                break;
            case 'edit':
                $stmt = $pdo->prepare("UPDATE servicios SET titulo = ?, descripcion = ?, icono = ?, orden = ?, activo = ? WHERE id = ?");
                $stmt->execute([$_POST['titulo'], $_POST['descripcion'], $_POST['icono'], $_POST['orden'], $_POST['activo'] ?? 1, $_POST['id']]);
                $mensaje = 'Servicio actualizado correctamente';
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM servicios WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $mensaje = 'Servicio eliminado correctamente';
                break;
        }
    }
}

// Obtener servicios
$servicios = $pdo->query("SELECT * FROM servicios ORDER BY orden")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img src="../img/logo-defensa-obrera.png" alt="Defensa Obrera">
            </div>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="servicios.php" class="active"><i class="fas fa-gavel"></i> Servicios</a>
                <a href="equipo.php"><i class="fas fa-users"></i> Equipo</a>
                <a href="resultados.php"><i class="fas fa-chart-bar"></i> Resultados</a>
                <a href="recursos.php"><i class="fas fa-book"></i> Recursos</a>
                <a href="mensajes.php"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="usuarios.php"><i class="fas fa-user-cog"></i> Usuarios</a>
                <a href="cambiar_password.php"><i class="fas fa-key"></i> Cambiar contraseña</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </aside>
        <main class="admin-main">
            <header class="admin-header">
                <h1>Gestión de Servicios Legales</h1>
            </header>
            
            <div class="admin-content">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <h2>Agregar Nuevo Servicio</h2>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Icono (FontAwesome, ej: fa-gavel)</label>
                            <input type="text" name="icono" value="fa-gavel">
                        </div>
                        <div class="form-group">
                            <label>Orden</label>
                            <input type="number" name="orden" value="0">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="activo" value="1" checked> Activo
                            </label>
                        </div>
                        <button type="submit" class="btn-primary">Agregar Servicio</button>
                    </form>
                </div>
                
                <div class="admin-card">
                    <h2>Servicios Existentes</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio): ?>
                            <tr>
                                <td><?php echo $servicio['id']; ?></td>
                                <td><?php echo htmlspecialchars($servicio['titulo']); ?></td>
                                <td><?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 50)) . '...'; ?></td>
                                <td><?php echo $servicio['activo'] ? '✅ Activo' : '❌ Inactivo'; ?></td>
                                <td class="actions">
                                    <button class="btn-edit" onclick="editar(<?php echo htmlspecialchars(json_encode($servicio)); ?>)">Editar</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $servicio['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Eliminar este servicio?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    function editar(servicio) {
        // Implementar edición con modal o redirección
        alert('Función de edición - ID: ' + servicio.id);
    }
    </script>
</body>
</html> 