<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Si usas Composer para el autoloader PSR-4
require_once __DIR__ . '/../db.php'; // Tu archivo Singleton de conexión en la raíz

use App\Service\ReservaService;
use App\Repository\ReservaRepository;
use App\Database\Database;
use App\View\View;

// Obtener el método HTTP y la ruta limpia
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$service = new ReservaService();


// ---------------------------------------------------------
// 1. RUTAS DE VISTAS (Twig) - Interfaz web para el usuario
// ---------------------------------------------------------
if ($requestUri === '/' && $method === 'GET') {
    // Recogemos posibles filtros opcionales de la URL para la tabla
    $filtros = [
        'estado' => $_GET['estado'] ?? null,
        'nombre' => $_GET['nombre'] ?? null,
        'desde'  => $_GET['desde'] ?? null,
        'hasta'  => $_GET['hasta'] ?? null,
    ];

    $reservas = $service->listarReservas($filtros);
    
    // Renderizamos la plantilla Twig pasándole los datos
    header('Content-Type: text/html; charset=UTF-8');
    View::render('reservas/index.twig', [
        'reservas' => $reservas,
        'filtros' => $filtros
    ]);
    exit;
} elseif ($requestUri === '/reservas/detalle' && $method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // 1. Obtiene los datos principales de la reserva
    $reserva = $service->obtenerReservaPorId($id); 
    
    // 2. Obtiene el timeline de eventos asociado a esta reserva
    $eventos = $service->obtenerEventosPorIdReserva($id);
    
    header('Content-Type: text/html; charset=UTF-8');
    View::render('reservas/detalle.twig', [
        'reserva' => $reserva,
        'eventos' => $eventos 
    ]);
    exit;
}



// ---------------------------------------------------------
// 2. RUTAS DE LA API (JSON) - Para JavaScript / Postman
// ---------------------------------------------------------

// Configurar cabeceras para responder siempre en JSON
header('Content-Type: application/json; charset=UTF-8');

// Capturar el cuerpo de la petición si viene en formato JSON (para POST o PUT)
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true) ?? [];

try {
    // Inicializar dependencias de forma limpia para los servicios que lo requieran
    $pdo = Database::getConnection(); 

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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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