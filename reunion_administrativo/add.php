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
    $camposRequeridos = ['adm_cedula','idreunion'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            exit;
        }
    }

    //Verificar que el administrativo exista
    $sentenciaAdministrativo = $db->prepare("SELECT adm_cedula FROM Administrativo WHERE adm_cedula = ?");
    $sentenciaAdministrativo->execute([$json['adm_cedula']]);
    $administrativo = $sentenciaAdministrativo->fetch(PDO::FETCH_ASSOC);

    //Verificar que la reunion exista
    $sentenciaReunion = $db->prepare("SELECT idreunion FROM Reunion WHERE idreunion = ?");
    $sentenciaReunion->execute([$json['idreunion']]);
    $reunion = $sentenciaReunion->fetch(PDO::FETCH_ASSOC);

    //Verificar la clave primaria
    $sentenciaClave = $db->prepare("SELECT 1 FROM reunion_administrativo WHERE (adm_cedula = ?) and (idreunion = ?)");
    $sentenciaClave->execute([$json['adm_cedula'], $json['idreunion']]);
    $clave = $sentenciaClave->fetch(PDO::FETCH_ASSOC);

    
    if($clave == null || $clave == 0){
        if($reunion == null || $reunion== 0){
            echo json_encode(['error'=> 'La reunion asociada no existe']);
            }else if($administrativo == null || $administrativo == 0){
                echo json_encode(['error'=> 'El administrativo asociado no existe']);
                }else{
                    $sentencia = $db->prepare("
                    INSERT INTO reunion_administrativo (idreunion, adm_cedula, horainicio, horafin) 
                    VALUES ( ?, ?, ?, ?)
                    ");
                    $resultado = $sentencia->execute([
                        $json['idreunion'],
                        $json['adm_cedula'],
                        $json['horainicio'],
                        $json['horafin']
                    ]);
                     // Enviar respuesta al cliente
                    if ($resultado) {
                        echo json_encode(["exito" => "registro correcto"]);
                    } else {
                        echo json_encode(["error" => "Error al registrar"]);
                    } 
            }
    }else{
        //se actualiza los entrenamientos correspondientes
        $sentencia = $db->prepare("
            UPDATE reunion_administrativo 
            SET horainicio = ?, horafin = ? 
            WHERE (adm_cedula = ?) and (idreunion = ?)
            ");
        $resultado = $sentencia->execute([
            $json['horainicio'],
            $json['horafin'],
            $json['adm_cedula'],
            $json['idreunion']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar"]);
        }    
    }
} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

