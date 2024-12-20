<?php
// filepath: /C:/wamp64/www/API/db.php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'sportsclubmanager';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

try {
    $bd = new PDO(
        "mysql:host=$db_host;dbname=$db_name",
        $db_user,
        $db_pass
    );
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $bd;
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}
// try {
//     $bd = new PDO('mysql:host=localhost;dbname=sportsclubmanager', 'root', '');
//     $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     return $bd;
// } catch (PDOException $e) {
//     echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
//     exit;
// }

