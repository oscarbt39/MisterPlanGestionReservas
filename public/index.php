<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Si usas Composer para el autoloader PSR-4
require_once __DIR__ . '/../db.php'; // Tu archivo Singleton de conexión en la raíz

use App\Service\ReservaService;
use App\Repository\ReservaRepository;
use App\Database\Database;

// Configurar cabeceras para responder siempre en JSON
header('Content-Type: application/json; charset=UTF-8');

// Obtener el método HTTP y la ruta limpia
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Capturar el cuerpo de la petición si viene en formato JSON (para POST o PUT)
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true) ?? [];

try {
    // Inicializar dependencias de forma limpia para los servicios que lo requieran
    $pdo = Database::getConnection(); 
    $service = new ReservaService();

    // Enrutamiento básico según la URL y el método
    if ($requestUri === '/api/reservas' && $method === 'GET') {
        // 1. Si viene un ID específico, prioriza buscar esa única reserva
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $id = (int)$_GET['id'];
            $reserva = $service->obtenerReservaPorId($id);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $reserva
            ], JSON_UNESCAPED_UNICODE);
        } 
        else {
            // 2. Si no viene ID, recoge posibles filtros de la query string
            $filtros = [
                'estado' => $_GET['estado'] ?? null,
                'nombre' => $_GET['nombre'] ?? null,
                'desde'  => $_GET['desde'] ?? null,
                'hasta'  => $_GET['hasta'] ?? null,
            ];

            // Llama al servicio pasando los filtros (si están vacíos, listará todo)
            $reservas = $service->listarReservas($filtros);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $reservas
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    elseif ($requestUri === '/api/reservas' && $method === 'POST') {
        $service->crearReserva($data);
        http_response_code(201);
        echo json_encode([
            "status" => "success", 
            "message" => "Reserva creada correctamente."
        ], JSON_UNESCAPED_UNICODE);
    }
    elseif ($requestUri === '/api/reservas/estado' && $method === 'PUT') {
        $id = $data['id_reserva'] ?? 0;
        $estado = $data['estado'] ?? '';
        
        $service->actualizarEstadoReserva((int)$id, $estado);
        http_response_code(200);
        echo json_encode([
            "status" => "success", 
            "message" => "Estado actualizado correctamente."
        ], JSON_UNESCAPED_UNICODE);
    } 
    else {
        http_response_code(404);
        echo json_encode([
            "status" => "error", 
            "message" => "Ruta no encontrada."
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (\InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno del servidor: " . $e->getMessage()]);
}