<?php
// Encabezados para CORS y tipo de contenido
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    // Obtener y decodificar los datos JSON enviados
    $json = json_decode(file_get_contents("php://input"), true);
    if (!$json) {
        echo json_encode(["error" => "No se recibieron datos válidos"]);
        exit;
    }

    // Incluir la conexión a la base de datos
    $db = include_once "../db.php";
    if (!$db) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }

    // Validar los datos recibidos
    $camposRequeridos = ['fecha'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            exit;
        }
    }

    if($json['idreunion']==null || $json['idreunion']== 0){
        // Se guarda una reunion nueva
        $sentencia = $db->prepare("
        INSERT INTO Reunion (nombre, fecha, horainicio, estado) 
        VALUES (?, ?, ?, ?)
        ");
        $resultado = $sentencia->execute([
            $json['nombre'],
            $json['fecha'],
            $json['horainicio'],
            $json['estado']
        ]);
         // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Reunion registrada correctamente"]);
        } else {
            echo json_encode(["error" => "Error al registrar la reunion"]);
        }   
    
    }else{
        //se actualiza los entrenamientos correspondientes
        $sentencia = $db->prepare("
            UPDATE Reunion 
            SET nombre = ?, fecha = ?, horainicio = ?, estado = ? 
            WHERE idreunion = ?
            ");
        $resultado = $sentencia->execute([
            $json['nombre'],
            $json['fecha'],
            $json['horainicio'],
            $json['estado'],
            $json['idreunion']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Reunion editada correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar la reunion"]);
        }    
    }
} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

