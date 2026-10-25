<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

use App\Service\ReservaService;

echo "--- EJECUTANDO TESTS NATIVOS DE RESERVAS ---\n";

$service = new ReservaService();

// TEST 1: Comprobar que la fecha de salida anterior a la entrada lanza excepción
try {
    $datosMal = [
        'nombre_huesped' => 'Test User',
        'email' => 'test@test.com',
        'alojamiento' => 'Suite Test',
        'fec_entrada' => '2026-06-10',
        'fec_salida' => '2026-06-05', 
        'importe' => 100,
        'notas' => 'Prueba de test'
    ];
    
    $service->crearReserva($datosMal);
    echo "❌ TEST 1 FALLÓ: Debería haber lanzado una excepción por fechas incorrectas.\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ TEST 1 PASÓ: Excepción capturada correctamente -> " . $e->getMessage() . "\n";
}

// TEST 2: Comprobar que el email inválido lanza excepción
try {
    $datosEmailMal = [
        'nombre_huesped' => 'Test User',
        'email' => 'correo-sin-arroba', 
        'alojamiento' => 'Suite Test',
        'fec_entrada' => '2026-06-10',
        'fec_salida' => '2026-06-15',
        'importe' => 100,
        'notas' => 'Prueba de test'
    ];
    
    $service->crearReserva($datosEmailMal);
    echo "❌ TEST 2 FALLÓ: Debería haber lanzado una excepción por email inválido.\n";
} catch (\InvalidArgumentException $e) {
    echo "✅ TEST 2 PASÓ: Excepción capturada correctamente -> " . $e->getMessage() . "\n";
}

echo "--- FIN DE LOS TESTS ---\n";