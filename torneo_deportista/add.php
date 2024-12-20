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
    $camposRequeridos = ['idtorneo'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            // exit;
        }
    }

    //Verificar que el torneo exista
    $sentenciaTorneo = $db->prepare("SELECT idtorneo FROM Torneo WHERE idtorneo = ?");
    $sentenciaTorneo->execute([$json['idtorneo']]);
    $torneo = $sentenciaTorneo->fetch(PDO::FETCH_ASSOC);

    //Verificar que el Deportista exista
    $sentenciaDeportista = $db->prepare("SELECT dep_cedula FROM Deportista WHERE dep_cedula = ?");
    $sentenciaDeportista->execute([$json['dep_cedula']]);
    $deportista = $sentenciaDeportista->fetch(PDO::FETCH_ASSOC);

    //Verificar que clave primaria
    $sentenciaClave = $db->prepare("SELECT 1 FROM torneo_deportista WHERE (idtorneo = ?) and (dep_cedula = ?)");
    $sentenciaClave->execute([$json['idtorneo'], $json['dep_cedula']]);
    $clave = $sentenciaClave->fetch(PDO::FETCH_ASSOC);

    if($torneo != null && $deportista == null){
        $sentencia = $db->prepare("
            UPDATE torneo_deportista 
            SET horainicio = ?, horafin = ? 
            WHERE (idtorneo = ?)
            ");
        $resultado = $sentencia->execute([
            $json['horainicio'],
            $json['horafin'],
            $json['idtorneo'],
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar"]);
        }
        exit;    
    }
    
    if($clave == null || $clave == 0){
        if($torneo == null || $torneo== 0){
            echo json_encode(['error'=> 'El torneo asociado no existe']); 
            }else if($deportista == null || $deportista == 0){
                echo json_encode(['error'=> 'El deportista asociado no existe']);
                }else{
                    $sentencia = $db->prepare("
                    INSERT INTO torneo_deportista (idtorneo, dep_cedula, horainicio, horafin) 
                    VALUES ( ?, ?, ?, ?)
                    ");
                    $resultado = $sentencia->execute([
                        $json['idtorneo'],
                        $json['dep_cedula'],
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
            UPDATE torneo_deportista 
            SET horainicio = ?, horafin = ? 
            WHERE (idtorneo = ?)
            ");
        $resultado = $sentencia->execute([
            $json['horainicio'],
            $json['horafin'],
            $json['idtorneo'],
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

