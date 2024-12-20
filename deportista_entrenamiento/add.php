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
        // exit;
    }

    // Incluir la conexión a la base de datos
    $db = include_once "../db.php";
    if (!$db) {
        echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
        exit;
    }

    // Función para verificar existencia de registros
    function verificarRegistro($db, $tabla, $campo, $valor) {
        $sentencia = $db->prepare("SELECT 1 FROM $tabla WHERE $campo = ?");
        $sentencia->execute([$valor]);
        return $sentencia->fetch(PDO::FETCH_ASSOC);
    }

    // Validar existencia de entrenamiento y deportista
    $entrenamiento = verificarRegistro($db, "Entrenamiento", "identrenamiento", $json['identrenamiento']);
    $deportista = verificarRegistro($db, "Deportista", "dep_cedula", $json['dep_cedula']);

    // Verificar la clave de relación entre entrenamiento y deportista
    $clave = $db->prepare("SELECT 1 FROM deportista_entrenamiento WHERE identrenamiento = ? AND dep_cedula = ?");
    $clave->execute([$json['identrenamiento'], $json['dep_cedula']]);
    $clave = $clave->fetch(PDO::FETCH_ASSOC);

    // Construir dinámicamente la consulta para la actualización
    if ($entrenamiento != null && $deportista == null) {
        $campos = [];
        $valores = [];

        if (!empty($json['fecha'])) {
            $campos[] = "fecha = ?";
            $valores[] = $json['fecha'];
        }
        if (!empty($json['horainicio'])) {
            $campos[] = "horainicio = ?";
            $valores[] = $json['horainicio'];
        }
        if (!empty($json['horafin'])) {
            $campos[] = "horafin = ?";
            $valores[] = $json['horafin'];
        }

        if (!empty($campos)) {
            $valores[] = $json['identrenamiento'];

            $consulta = "
                UPDATE deportista_entrenamiento 
                SET " . implode(", ", $campos) . " 
                WHERE identrenamiento = ?
            ";
            $sentencia = $db->prepare($consulta);
            $resultado = $sentencia->execute($valores);

            echo json_encode($resultado ? ["exito" => "editado correctamente"] : ["error" => "Error al editar"]);
        } else {
            echo json_encode(["error" => "No se proporcionaron valores para actualizar"]);
        }

        exit;
    } else {
        // Si no existe la clave, intentar insertar el registro
        if (!$entrenamiento) {
            echo json_encode(['error' => 'El entrenamiento asociado no existe']);
        } elseif (!$deportista) {
            echo json_encode(['error' => 'El deportista asociado no existe']);
        } else {
            $sentencia = $db->prepare("
                INSERT INTO deportista_entrenamiento (identrenamiento, dep_cedula, fecha, horainicio, horafin) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $resultado = $sentencia->execute([
                $json['identrenamiento'], $json['dep_cedula'], $json['fecha'], $json['horainicio'], $json['horafin']
            ]);
            echo json_encode($resultado ? ["exito" => "registro correcto"] : ["error" => "Error al registrar"]);
        }
    }
} catch (Exception $e) {
    // Capturar errores inesperados
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}

// // Encabezados para CORS y tipo de contenido
// header("Access-Control-Allow-Origin: http://localhost:4200");
// header("Access-Control-Allow-Methods: GET, POST");
// header("Access-Control-Allow-Headers: *");
// header("Content-Type: application/json; charset=UTF-8");

// try {
//     // Obtener y decodificar los datos JSON enviados
//     $json = json_decode(file_get_contents("php://input"), true);
//     if (!$json) {
//         echo json_encode(["error" => "No se recibieron datos válidos"]);
//         exit;
//     }

//     // Incluir la conexión a la base de datos
//     $db = include_once "../db.php";
//     if (!$db) {
//         echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
//         exit;
//     }

//     // Función para validar existencia de registros
//     function verificarRegistro($db, $tabla, $campo, $valor) {
//         $sentencia = $db->prepare("SELECT 1 FROM $tabla WHERE $campo = ?");
//         $sentencia->execute([$valor]);
//         return $sentencia->fetch(PDO::FETCH_ASSOC);
//     }

//     // Validar existencia de entrenamiento y deportista
//     $entrenamiento = verificarRegistro($db, "Entrenamiento", "identrenamiento", $json['identrenamiento']);
//     $deportista = verificarRegistro($db, "Deportista", "dep_cedula", $json['dep_cedula']);
    
//     // Verificar la clave de relación entre entrenamiento y deportista
//     $clave = $db->prepare("SELECT 1 FROM deportista_entrenamiento WHERE identrenamiento = ? AND dep_cedula = ?");
//     $clave->execute([$json['identrenamiento'], $json['dep_cedula']]);
//     $clave = $clave->fetch(PDO::FETCH_ASSOC);

//     // Manejo de lógica para filtros
//     if ($entrenamiento && !$deportista) {
//         if (!empty($json['fecha']) || !empty($json['horainicio']) || !empty($json['horafin'])) {
//             $sentencia = $db->prepare("
//                 UPDATE deportista_entrenamiento 
//                 SET fecha = ?, horainicio = ?, horafin = ? 
//                 WHERE identrenamiento = ?
//             ");
//             $resultado = $sentencia->execute([
//                 $json['fecha'], $json['horainicio'], $json['horafin'], $json['identrenamiento']
//             ]);
//             echo json_encode($resultado ? ["exito" => "editado correctamente"] : ["error" => "Error al editar"]);
//         }
//         exit;
//     }

//     if (!$clave) {
//         if (!$entrenamiento) {
//             echo json_encode(['error' => 'El entrenamiento asociado no existe']);
//         } elseif (!$deportista) {
//             echo json_encode(['error' => 'El deportista asociado no existe']);
//         } else {
//             $sentencia = $db->prepare("
//                 INSERT INTO deportista_entrenamiento (identrenamiento, dep_cedula, fecha, horainicio, horafin) 
//                 VALUES (?, ?, ?, ?, ?)
//             ");
//             $resultado = $sentencia->execute([
//                 $json['identrenamiento'], $json['dep_cedula'], $json['fecha'], $json['horainicio'], $json['horafin']
//             ]);
//             echo json_encode($resultado ? ["exito" => "registro correcto"] : ["error" => "Error al registrar"]);
//         }
//         exit;
//     }

//     // Si la clave ya existe, se actualizan los datos
//     $sentencia = $db->prepare("
//         UPDATE deportista_entrenamiento 
//         SET fecha = ?, horainicio = ?, horafin = ? 
//         WHERE identrenamiento = ? AND dep_cedula = ?
//     ");
//     $resultado = $sentencia->execute([
//         $json['fecha'], $json['horainicio'], $json['horafin'], $json['identrenamiento'], $json['dep_cedula']
//     ]);
//     echo json_encode($resultado ? ["exito" => "editado correctamente"] : ["error" => "Error al editar"]);

// } catch (Exception $e) {
//     // Capturar errores inesperados
//     echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
// }

// // Encabezados para CORS y tipo de contenido
// header("Access-Control-Allow-Origin: http://localhost:4200");
// header("Access-Control-Allow-Methods: GET, POST");
// header("Access-Control-Allow-Headers: *");
// header("Content-Type: application/json; charset=UTF-8");

// try {
//     // Obtener y decodificar los datos JSON enviados
//     $json = json_decode(file_get_contents("php://input"), true);
//     if (!$json) {
//         echo json_encode(["error" => "No se recibieron datos válidos"]);
//         exit;
//     }

//     // Incluir la conexión a la base de datos
//     $db = include_once "../db.php";
//     if (!$db) {
//         echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
//         exit;
//     }

//     // Validar los datos recibidos
//     $camposRequeridos = ['identrenamiento'];
//     foreach ($camposRequeridos as $campo) {
//         if (empty($json[$campo])) {
//             echo json_encode(["error" => "El campo $campo es obligatorio"]);
//             // exit;
//         }
//     }

//     //Verificar que el entrenamiento
//     $sentenciaEntrenamiento = $db->prepare("SELECT identrenamiento FROM Entrenamiento WHERE identrenamiento = ?");
//     $sentenciaEntrenamiento->execute([$json['identrenamiento']]);
//     $entrenamiento = $sentenciaEntrenamiento->fetch(PDO::FETCH_ASSOC);

//     //Verificar que el Deportista exista
//     $sentenciaDeportista = $db->prepare("SELECT dep_cedula FROM Deportista WHERE dep_cedula = ?");
//     $sentenciaDeportista->execute([$json['dep_cedula']]);
//     $deportista = $sentenciaDeportista->fetch(PDO::FETCH_ASSOC);

//     //Verificar que el Deportista exista
//     $sentenciaClave = $db->prepare("SELECT 1 FROM deportista_entrenamiento WHERE (identrenamiento = ?) and (dep_cedula = ?)");
//     $sentenciaClave->execute([$json['identrenamiento'], $json['dep_cedula']]);
//     $clave = $sentenciaClave->fetch(PDO::FETCH_ASSOC);

//     if($entrenamiento != null && $deportista == null){
//        if($json['fecha']!=null || $json['horafin']!=null || $json['horainicio']!=null || $json['fecha']!="" || $json['horainicio']!="" || $json['horafin']!="")

//         $sentencia = $db->prepare("
//             UPDATE deportista_entrenamiento 
//             SET fecha = ?, horainicio = ?, horafin = ? 
//             WHERE (identrenamiento = ?)
//             ");
//             $resultado = $sentencia->execute([
//             $json['fecha'],
//             $json['horainicio'],
//             $json['horafin'],
//             $json['identrenamiento'],
//         ]);
//         // Enviar respuesta al cliente
//         if ($resultado) {
//             echo json_encode(["exito" => "editado correctamente"]);
//         } else {
//             echo json_encode(["error" => "Error al editar"]);
//         }
//         exit;    
//     }

    
//     if($clave == null || $clave == null){
//         if($entrenamiento == null || $entrenamiento== 0){
//             echo json_encode(['error'=> 'El entrenamiento asociado no existe']);
//             }else if($deportista == null || $deportista == 0){
//                 echo json_encode(['error'=> 'El deportista asociado no existe']);
//                 }else{
//                     $sentencia = $db->prepare("
//                     INSERT INTO deportista_entrenamiento (identrenamiento,dep_cedula, fecha, horainicio, horafin) 
//                     VALUES (?, ?, ?, ?, ?)
//                     ");
//                     $resultado = $sentencia->execute([
//                         $json['identrenamiento'],
//                         $json['dep_cedula'],
//                         $json['fecha'],
//                         $json['horainicio'],
//                         $json['horafin']
//                     ]);
//                      // Enviar respuesta al cliente
//                     if ($resultado) {
//                         echo json_encode(["exito" => "registro correcto"]);
//                     } else {
//                         echo json_encode(["error" => "Error al registrar"]);
//                     } 
//             }
//     }else{
//         //se actualiza los entrenamientos correspondientes
//         $sentencia = $db->prepare("
//             UPDATE deportista_entrenamiento 
//             SET fecha = ?, horainicio = ?, horafin = ? 
//             WHERE (identrenamiento = ?) and (dep_cedula = ?)
//             ");
//         $resultado = $sentencia->execute([
//             $json['fecha'],
//             $json['horaincio'],
//             $json['horafin'],
//             $json['identrenamiento'],
//             $json['dep_cedula']
//         ]);
//         // Enviar respuesta al cliente
//         if ($resultado) {
//             echo json_encode(["exito" => "editado correctamente"]);
//         } else {
//             echo json_encode(["error" => "Error al editar"]);
//         }    
//     }
// } catch (Exception $e) {
//     // Capturar errores inesperados
//     echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
// }

