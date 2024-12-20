<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

try {

    $json = json_decode(file_get_contents("php://input"), true);
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

    
        $sentencia = $db->prepare("
            SELECT 
                D.dep_cedula,
                D.nombre AS nombre_deportista,
                D.email,
                D.telefono,
                D.categoria,
                D.elo,
                D.idclub,
                D.estado
            FROM 
                Deportista D
            WHERE 
                D.dep_cedula NOT IN (
                    SELECT TD.dep_cedula
                    FROM Torneo_Deportista TD
                    WHERE TD.idtorneo = ? 
                )
            AND NOT EXISTS (
                SELECT 1
                FROM Torneo_Deportista TD
                JOIN Torneo T ON TD.idtorneo = T.idtorneo
                WHERE TD.dep_cedula = D.dep_cedula
                  AND T.fecha = (
                      SELECT fecha 
                      FROM Torneo 
                      WHERE idtorneo = ? 
                  )
            );

            ");
        $resultado = $sentencia->execute([
            $json['idtorneo'],
            $json['idtorneo'],
        ]);
        // Enviar respuesta al cliente
        if (!$sentencia) {
            throw new Exception("Error al ejecutar la consulta");
        }
        
         // Obtener los resultados
         $deportista = $sentencia->fetchAll(PDO::FETCH_OBJ);

         // Devolver la respuesta en formato JSON                                                                                                                                                
         echo json_encode(['value' => $deportista], JSON_UNESCAPED_UNICODE);   

    
} catch (Exception $e) {
    // Manejar errores y devolver una respuesta JSON de error
    http_response_code(500); // Establecer código de error HTTP
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} 
