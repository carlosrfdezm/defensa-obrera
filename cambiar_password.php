<?php
// cambiar_password.php - Script para cambiar contraseña de admin
require_once 'includes/db.php';

// Nueva contraseña
$nueva_password = 'admin123';
$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

echo "<h1>Cambio de Contraseña de Administrador</h1>";

try {
    // Verificar si existe el usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        // Actualizar contraseña
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE usuario = 'admin'");
        $stmt->execute([$password_hash]);
        echo "✅ Contraseña actualizada correctamente para usuario: admin<br>";
        echo "🔑 Nueva contraseña: admin123<br>";
        echo "<a href='admin/login.php'>Ir al panel de administración</a>";
    } else {
        // Crear usuario admin
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, email) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $password_hash, 'admin@defensaobrera.cl']);
        echo "✅ Usuario admin creado correctamente<br>";
        echo "🔑 Usuario: admin | Contraseña: admin123<br>";
        echo "<a href='admin/login.php'>Ir al panel de administración</a>";
    }
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>