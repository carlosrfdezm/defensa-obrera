<?php
// admin/eliminar_usuario.php - Eliminar usuario
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Verificar que el usuario actual sea admin
$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$usuario_actual = $stmt->fetch();

if ($usuario_actual['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: usuarios.php');
    exit;
}

// No permitir eliminarse a sí mismo
if ($id == $_SESSION['admin_id']) {
    header('Location: usuarios.php?error=no_puedes_eliminarte');
    exit;
}

// Eliminar usuario
$stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->execute([$id]);

header('Location: usuarios.php?mensaje=usuario_eliminado');
exit;
?>