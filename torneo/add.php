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
    $camposRequeridos = ['fecha', 'idclub'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            exit;
        }
    }

    if($json['idtorneo']==null || $json['idtorneo']== 0){
        // Se guarda un torneo nueva
        $sentencia = $db->prepare("
        INSERT INTO Torneo (nombre, modalidad, fecha, estado, idclub) 
        VALUES (?, ?, ?, ?, ?)
        ");
        $resultado = $sentencia->execute([
            $json['nombre'],
            $json['modalidad'],
            $json['fecha'],
            $json['estado'],
            $json['idclub'],
        ]);
         // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Torneo registrado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al registrar el torneo"]);
        }   
    
    }else{
        //se actualiza los entrenamientos correspondientes
        $sentencia = $db->prepare("
            UPDATE Torneo 
            SET nombre = ?, modalidad = ?, fecha = ?, estado = ?
            WHERE idtorneo = ?
            ");
        $resultado = $sentencia->execute([
            $json['nombre'],
            $json['modalidad'],
            $json['fecha'],
            $json['estado'],
            $json['idtorneo']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Torneo editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar el torneo"]);
        }    
    }
} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

