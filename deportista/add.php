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
    $camposRequeridos = ['dep_cedula','nombre', 'categoria'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            exit;
        }
    }
    

    $sentencia = $db->prepare("SELECT dep_cedula FROM deportista WHERE dep_cedula = ?");
    $sentencia->execute([$json['dep_cedula']]);
    $deportista= $sentencia->fetch(PDO::FETCH_ASSOC);

    //Verificar que el club exista
    $sentenciaclub = $db->prepare("SELECT idclub FROM Club WHERE idclub = ?");
    $sentenciaclub->execute([$json['idclub']]);
    $club= $sentenciaclub->fetch(PDO::FETCH_ASSOC);


    if($deportista==null || $deportista	== 0){
        if($club==null || $club==0){
            echo json_encode(["error"=>"El club asociado no existe"]);
        }else{
            // Se guarda un deportista nuevo
            $sentencia = $db->prepare("
                INSERT INTO Deportista (dep_cedula, nombre, email, telefono, categoria, elo, idclub, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
            $resultado = $sentencia->execute([
                $json['dep_cedula'],
                $json['nombre'],
                $json['email'],
                $json['telefono'],
                $json['categoria'],
                $json['elo'],
                $json['idclub'],
                $json['estado']
            ]);
             // Enviar respuesta al cliente
            if ($resultado) {
                echo json_encode(["exito" => "Deportista registrado correctamente"]);
            } else {
                echo json_encode(["error" => "Error al registrar el deportista"]);
            }   
        }
 
    }else{
        //se actualiza los deportistas correspondientes
        $sentencia = $db->prepare("
            UPDATE Deportista 
            SET nombre = ?, email = ?, telefono = ?, categoria = ?, elo = ?, idclub = ?, estado = ? 
            WHERE dep_cedula = ?
            ");
        $resultado = $sentencia->execute([
            $json['nombre'],
            $json['email'],
            $json['telefono'],
            $json['categoria'],
            $json['elo'],
            $json['idclub'],
            $json['estado'],
            $json['dep_cedula']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Deportista editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar el Deportista"]);
        }    
    }



} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

