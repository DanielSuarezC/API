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
    $camposRequeridos = ['ent_cedula','nombre'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            exit;
        }
    }
    
    //Verificar si existe entrenador con esa cedula
    $sentencia = $db->prepare("SELECT ent_cedula FROM Entrenador WHERE ent_cedula = ?");
    $sentencia->execute([$json['ent_cedula']]);
    $entrenador= $sentencia->fetch(PDO::FETCH_ASSOC);

    //Verificar que el club exista
    $sentenciaclub = $db->prepare("SELECT idclub FROM Club WHERE idclub = ?");
    $sentenciaclub->execute([$json['idclub']]);
    $club= $sentenciaclub->fetch(PDO::FETCH_ASSOC);


    if($entrenador==null || $entrenador== 0){
        if($club==null || $club==0){
            echo json_encode(["error"=> "El club asociado no existe"]);
        }else{
             // Se guarda un entrenador nuevo
             $sentencia = $db->prepare("
             INSERT INTO Entrenador (ent_cedula, nombre, email, telefono, elo, tituloFide, sueldo, idclub, estado) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ");
             $resultado = $sentencia->execute([
                 $json['ent_cedula'],
                 $json['nombre'],
                 $json['email'],
                 $json['telefono'],
                 $json['elo'],
                 $json['tituloFide'],
                 $json['sueldo'],
                 $json['idclub'],
                 $json['estado']
             ]);
              // Enviar respuesta al cliente
             if ($resultado) {
                 echo json_encode(["exito" => "Entrenador registrado correctamente"]);
             } else {
                 echo json_encode(["error" => "Error al registrar el entrenador"]);
             }     
        }
  
    }else{
        //se actualiza los entrenadores correspondientes
        $sentencia = $db->prepare("
            UPDATE Entrenador 
            SET nombre = ?, email = ?, telefono = ?, elo = ?, tituloFide = ?, sueldo = ?, idclub = ?, estado = ? 
            WHERE ent_cedula = ?
            ");
        $resultado = $sentencia->execute([
            $json['nombre'],
            $json['email'],
            $json['telefono'],
            $json['elo'],
            $json['tituloFide'],
            $json['sueldo'],
            $json['idclub'],
            $json['estado'],
            $json['ent_cedula']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Entrenador editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar el entrenador"]);
        }    
    }



} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

