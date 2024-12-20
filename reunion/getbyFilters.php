<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    $json = json_decode(file_get_contents("php://input"), true);

    // Incluir la conexión a la base de datos
    $db = include_once "../db.php"; // Ajusta la ruta según la ubicación
    if (!$db) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }

    $query = "SELECT * FROM Reunion WHERE 1=1";
    $params = [];

    // Verificar y agregar filtros dinámicamente
    if (!empty($json['idreunion'])) {
        $query .= " AND idreunion = ?";
        $params[] = $json['idreunion'];
    }
    if (!empty($json['fecha'])) {
        $query .= " AND fecha = ?";
        $params[] = '%' . $json['fecha'] . '%';
    }
    if (!empty($json['estado'])) {
        $query .= " AND estado = ?";
        $params[] = '%' . $json['estado'] . '%';
    }

    // Preparar y ejecutar la consulta
    $sentencia = $bd->prepare($query);
    $resultado = $sentencia->execute($params);

    if (!$resultado) {
        throw new Exception("Error al ejecutar la consulta");
    }

    // Obtener los resultados
    $dato = $sentencia->fetchAll(PDO::FETCH_OBJ);

    // Devolver la respuesta en formato JSON
    echo json_encode(['value' => $dato], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Manejar errores y devolver una respuesta JSON de error
    http_response_code(500); // Establecer código de error HTTP
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}