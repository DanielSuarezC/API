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
    $camposRequeridos = ['adm_cedula','nombre', 'cargo'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            exit;
        }
    }
    

    $sentencia = $db->prepare("SELECT adm_cedula FROM administrativo WHERE adm_cedula = ?");
    $sentencia->execute([$json['adm_cedula']]);
    $administrativo= $sentencia->fetch(PDO::FETCH_ASSOC);


    //Verificar que el club exista
    $sentenciaclub = $db->prepare("SELECT idclub FROM Club WHERE idclub = ?");
    $sentenciaclub->execute([$json['idclub']]);
    $club= $sentenciaclub->fetch(PDO::FETCH_ASSOC);


    if($administrativo==null || $administrativo== 0){
        if($club == null || $club == 0){
            echo json_encode(['error'=> 'El club asociado no existe']);
        }else{
            // Se guarda un administrativo nuevo
            $sentencia = $db->prepare("
                INSERT INTO Administrativo (adm_cedula, nombre, email, telefono, cargo, sueldo, idclub, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
            $resultado = $sentencia->execute([
                $json['adm_cedula'],
                $json['nombre'],
                $json['email'],
                $json['telefono'],
                $json['cargo'],
                $json['sueldo'],
                $json['idclub'],
                $json['estado']
            ]);
             // Enviar respuesta al cliente
            if ($resultado) {
                echo json_encode(["exito" => "Administrativo registrado correctamente"]);
            } else {
                echo json_encode(["error" => "Error al registrar el Administrativo"]);
            }   
        }     
    }else{
        //se actualiza los administrativos correspondientes
        $sentencia = $db->prepare("
            UPDATE Administrativo 
            SET nombre = ?, email = ?, telefono = ?, cargo = ?, sueldo = ?, idclub = ?, estado = ? 
            WHERE adm_cedula = ?
            ");
        $resultado = $sentencia->execute([
            $json['nombre'],
            $json['email'],
            $json['telefono'],
            $json['cargo'],
            $json['sueldo'],
            $json['idclub'],
            $json['estado'],
            $json['adm_cedula']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Administrativo editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar el Administrativo"]);
        }    
    }



} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

