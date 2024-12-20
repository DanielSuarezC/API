<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    $jsonClub = json_decode(file_get_contents("php://input"), true);

    // Incluir la conexión a la base de datos
    $db = include_once "../db.php"; // Ajusta la ruta según la ubicación
    if (!$db) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }

    $query = "SELECT * FROM Club WHERE 1=1";
    $params = [];

    // Verificar y agregar filtros dinámicamente
    if (!empty($jsonClub['idclub'])) {
        $query .= " AND idclub = ?";
        $params[] = $jsonClub['idclub'];
    }
    if (!empty($jsonClub['nombre'])) {
        $query .= " AND nombre LIKE ?";
        $params[] = '%' . $jsonClub['nombre'] . '%';
    }
    if (!empty($jsonClub['ciudad'])) {
        $query .= " AND ciudad LIKE ?";
        $params[] = '%' . $jsonClub['ciudad'] . '%';
    }

    // Preparar y ejecutar la consulta
    $sentencia = $bd->prepare($query);
    $resultado = $sentencia->execute($params);

    if (!$resultado) {
        throw new Exception("Error al ejecutar la consulta");
    }

    // Obtener los resultados
    $club = $sentencia->fetchAll(PDO::FETCH_OBJ);

    // Devolver la respuesta en formato JSON
    echo json_encode(['value' => $club], JSON_UNESCAPED_UNICODE);

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

//     $jsonClub = json_decode(file_get_contents("php://input"), true);
//     if (!$jsonClub) {
//         echo json_encode(["error" => "No se recibieron datos válidos"]);
//         exit;
//     }

//     // Incluir la conexión a la base de datos
//     $db = include_once "../db.php"; // Ajusta la ruta según la ubicación
//     if (!$db) {
//         echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
//         exit;
//     }

//     //  Para filtrar por id, nombre y ciudad
//         $sentencia = $db->prepare("
//             SELECT *
//             FROM Club 
//             WHERE (idclub = ?) OR (nombre =?) OR (ciudad=?)
//             ");
//         $resultado = $sentencia->execute([
//             $jsonClub['idclub'], 
//             $jsonClub['nombre'], 
//             $jsonClub['ciudad']
//         ]);
//         // Enviar respuesta al cliente
//         if (!$sentencia) {
//             throw new Exception("Error al ejecutar la consulta");
//         }
        
//          // Obtener los resultados
//          $club = $sentencia->fetchAll(PDO::FETCH_OBJ);

//          // Devolver la respuesta en formato JSON                                                                                                                                                
//          echo json_encode(['value' => $club], JSON_UNESCAPED_UNICODE);   

    
// } catch (Exception $e) {
//     // Manejar errores y devolver una respuesta JSON de error
//     http_response_code(500); // Establecer código de error HTTP
//     echo json_encode([
//         'error' => true,
//         'message' => $e->getMessage()
//     ], JSON_UNESCAPED_UNICODE);
// } 
