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
            FROM Entrenamiento 
            WHERE (identrenamiento = ?) OR (tipo =?) OR (jornada=?) OR (ent_cedula = ?) OR (estado = ?)
            ");
        $resultado = $sentencia->execute([
            $json['identrenamiento'], 
            $json['tipo'], 
            $json['jornada'],
            $json['ent_cedula'],
            $json['estado'],
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
// header("Access-Control-Allow-Origin: http://localhost:4200");
// header("Access-Control-Allow-Methods: GET, POST");
// header("Access-Control-Allow-Headers: *");
// header("Content-Type: application/json; charset=UTF-8");

// try {
//     $json = json_decode(file_get_contents("php://input"), true);

//     // Incluir la conexión a la base de datos
//     $db = include_once "../db.php"; // Ajusta la ruta según la ubicación
//     if (!$db) {
//         echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
//         exit;
//     }

//     $query = "SELECT * FROM Entrenamiento WHERE 1=1";
//     $params = [];

//         // Verificar y agregar filtros dinámicamente
//         if (!empty($json['identrenamiento'])) {
//             $query .= " AND identrenamiento = ?";
//             $params[] = $json['identrenamiento'];
//         }
//         if (!empty($json['tipo'])) {
//             $query .= " AND tipo = ?";
//             $params[] = '%' . $json['tipo'] . '%';
//         }
//         if (!empty($json['jornada'])) {
//             $query .= " AND jornada = ?";
//             $params[] = '%' . $json['jornada'] . '%';
//         }
//         if (!empty($json['estado'])) {
//             $query .= " AND estado = ?";
//             $params[] = '%' . $json['estado'] . '%';
//         }
        
//         if (!empty($json['ent_cedula'])) {
//             $query .= " AND ent_cedula = ?";
//             $params[] = '%' . $json['ent_cedula'] . '%';
//         }
//     // // Verificar y agregar filtros dinámicamente
//     // if (!empty($json['identrenamiento'])) {
//     //     $query .= " AND identrenamiento = ?";
//     //     $params[] = $json['identrenamiento'];
//     // }
//     // if (!empty($json['tipo'])) {
//     //     $query .= " AND tipo = ?";
//     //     $params[] = '%' . $json['tipo'] . '%';
//     // }
//     // if (!empty($json['jornada'])) {
//     //     $query .= " AND jornada = ?";
//     //     $params[] = '%' . $json['jornada'] . '%';
//     // }
//     // if (!empty($json['estado'])) {
//     //     $query .= " AND estado = ?";
//     //     $params[] = '%' . $json['estado'] . '%';
//     // }
    
//     // if (!empty($json['ent_cedula'])) {
//     //     $query .= " AND ent_cedula = ?";
//     //     $params[] = '%' . $json['ent_cedula'] . '%';
//     // }

//     // Preparar y ejecutar la consulta
//     $sentencia = $bd->prepare($query);
//     $resultado = $sentencia->execute($params);

//     if (!$resultado) {
//         throw new Exception("Error al ejecutar la consulta");
//     }

//     // Obtener los resultados
//     $dato = $sentencia->fetchAll(PDO::FETCH_OBJ);

//     // Devolver la respuesta en formato JSON
//     echo json_encode(['value' => $dato], JSON_UNESCAPED_UNICODE);

// } catch (Exception $e) {
//     // Manejar errores y devolver una respuesta JSON de error
//     http_response_code(500); // Establecer código de error HTTP
//     echo json_encode([
//         'error' => true,
//         'message' => $e->getMessage()
//     ], JSON_UNESCAPED_UNICODE);
// }