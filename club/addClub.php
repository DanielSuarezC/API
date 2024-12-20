<?php
// Encabezados para CORS y tipo de contenido
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    // Obtener y decodificar los datos JSON enviados
    $jsonClub = json_decode(file_get_contents("php://input"), true);
    if (!$jsonClub) {
        echo json_encode(["error" => "No se recibieron datos válidos"]);
        exit;
    }

    // Incluir la conexión a la base de datos
    $bd = include_once "../db.php";
    if (!$bd) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }

    // Validar los datos recibidos
    $requiredFields = ['nombre', 'ciudad', 'direccion', 'telefono'];
    foreach ($requiredFields as $field) {
        if (empty($jsonClub[$field])) {
            echo json_encode(["error" => "El campo $field es obligatorio"]);
            exit;
        }
    }


    if($jsonClub['idclub']==null || $jsonClub['idclub']==0){
        // Se guarda un club nuevo
        $sentenciaGuardar = $bd->prepare("
            INSERT INTO club (nombre, ciudad, direccion, telefono) 
            VALUES (?, ?, ?, ?)
            ");
        $resultado = $sentenciaGuardar->execute([
            $jsonClub['nombre'],
            $jsonClub['ciudad'],
            $jsonClub['direccion'],
            $jsonClub['telefono']
        ]);
         // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Club registrado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al registrar el club"]);
        }    
    }else{
        //se actualiza los clubes correspondientes
        $sentenciaActualizar = $bd->prepare("
            UPDATE Club 
            SET nombre = ?, ciudad = ?, direccion = ?, telefono = ? 
            WHERE idclub = ?
            ");
        $resultado = $sentenciaActualizar->execute([
            $jsonClub['nombre'], 
            $jsonClub['ciudad'], 
            $jsonClub['direccion'], 
            $jsonClub['telefono'], 
            $jsonClub['idclub']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Club editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar el club"]);
        }    
    }



} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

