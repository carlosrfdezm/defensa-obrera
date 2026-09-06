<?php
// admin/index.php - Dashboard
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administración</title>
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
                <a href="index.php" class="active"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="servicios.php"><i class="fas fa-gavel"></i> Servicios</a>
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
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span><?php echo $_SESSION['admin_usuario']; ?></span>
                </div>
            </header>
            
            <div class="admin-content">
                <div class="stats-grid">
                    <?php
                    require_once '../includes/db.php';
                    
                    $servicios = $pdo->query("SELECT COUNT(*) FROM servicios WHERE activo = 1")->fetchColumn();
                    $equipo = $pdo->query("SELECT COUNT(*) FROM equipo WHERE activo = 1")->fetchColumn();
                    $mensajes = $pdo->query("SELECT COUNT(*) FROM mensajes WHERE leido = 0")->fetchColumn();
                    $recursos = $pdo->query("SELECT COUNT(*) FROM recursos WHERE activo = 1")->fetchColumn();
                    ?>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-gavel"></i></div>
                        <div class="stat-info">
                            <h3>Servicios</h3>
                            <p><?php echo $servicios; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3>Equipo</h3>
                            <p><?php echo $equipo; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                        <div class="stat-info">
                            <h3>Mensajes</h3>
                            <p><?php echo $mensajes; ?> no leídos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-book"></i></div>
                        <div class="stat-info">
                            <h3>Recursos</h3>
                            <p><?php echo $recursos; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="admin-actions">
                    <h2>Acciones rápidas</h2>
                    <div class="action-buttons">
                        <a href="servicios.php" class="btn-primary"><i class="fas fa-plus"></i> Agregar Servicio</a>
                        <a href="equipo.php" class="btn-primary"><i class="fas fa-user-plus"></i> Agregar Miembro</a>
                        <a href="resultados.php" class="btn-primary"><i class="fas fa-plus"></i> Agregar Resultado</a>
                        <a href="recursos.php" class="btn-primary"><i class="fas fa-plus"></i> Agregar Recurso</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>