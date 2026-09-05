<?php
// includes/db.php - CONFIGURACIÓN PARA INFINITYFREE

// ============================================
// DATOS DE INFINITYFREE (¡YA CONFIGURADOS!)
// ============================================

define('DB_HOST', 'sql308.infinityfree.com');
define('DB_NAME', 'if0_42836381_defensaobrera');
define('DB_USER', 'if0_42836381');
define('DB_PASS', 'Myn3waccount26');

// ============================================
// CONEXIÓN A LA BASE DE DATOS
// ============================================

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "✅ Conexión exitosa a la base de datos"; // ← Puedes eliminar esta línea después de probar
} catch(PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos. Por favor, intenta más tarde.");
}
?>