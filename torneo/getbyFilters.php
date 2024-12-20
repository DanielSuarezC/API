<?php

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

try {

    $json= json_decode(file_get_contents("php://input"), true);
    if (!$json) {
        echo json_encode(["error" => "No se recibieron datos válidos"]);
        exit;
    }

    // Incluir la conexión a la base de datos
    $db = include_once "../db.php"; // Ajusta la ruta según la ubicación
    if (!$db) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }

    //  Para filtrar por id, nombre y ciudad
        $sentencia = $db->prepare("
            SELECT *
            FROM Torneo 
            WHERE (idtorneo = ?) OR (nombre =?) OR (modalidad=?) OR (estado = ?) OR (fecha = ?) OR (idclub = ?)
            ");
        $resultado = $sentencia->execute([
            $json['idtorneo'], 
            $json['nombre'], 
            $json['modalidad'],
            $json['estado'],
            $json['fecha'],
            $json['idclub'],
        ]);
        // Enviar respuesta al cliente
        if (!$sentencia) {
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