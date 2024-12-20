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
    $camposRequeridos = ['tipo'];
    foreach ($camposRequeridos as $campo) {
        if (empty($json[$campo])) {
            echo json_encode(["error" => "El campo $campo es obligatorio"]);
            exit;
        }
    }

    //verificamos que el entrenador exista
    $sentencia = $db->prepare("SELECT ent_cedula FROM Entrenador WHERE ent_cedula = ?");
    $sentencia->execute([$json['ent_cedula']]);
    $entrenador= $sentencia->fetch(PDO::FETCH_ASSOC);
    

    if($json['identrenamiento']==null || $json['identrenamiento']== 0){
        if($entrenador==null || $entrenador == 0){
            echo json_encode(["error"=> "El entrenador asociado no existe"]);
        }else{
            // Se guarda un entrenamiento nuevo
            $sentencia = $db->prepare("
            INSERT INTO Entrenamiento (tipo, jornada, ent_cedula, estado) 
            VALUES (?, ?, ?, ?)
            ");
            $resultado = $sentencia->execute([
                $json['tipo'],
                $json['jornada'],
                $json['ent_cedula'],
                $json['estado']
            ]);
             // Enviar respuesta al cliente
            if ($resultado) {
                echo json_encode(["exito" => "Entrenamiento registrado correctamente"]);
            } else {
                echo json_encode(["error" => "Error al registrar el entrenamiento"]);
            }   
        }
    }else{
        //se actualiza los entrenamientos correspondientes
        $sentencia = $db->prepare("
            UPDATE Entrenamiento 
            SET tipo = ?, jornada = ?, ent_cedula = ?, estado = ? 
            WHERE identrenamiento = ?
            ");
        $resultado = $sentencia->execute([
            $json['tipo'],
            $json['jornada'],
            $json['ent_cedula'],
            $json['estado'],
            $json['identrenamiento']
        ]);
        // Enviar respuesta al cliente
        if ($resultado) {
            echo json_encode(["exito" => "Entrenamiento editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar el entrenamiento"]);
        }    
    }
} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

