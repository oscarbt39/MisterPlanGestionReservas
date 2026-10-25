<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

use App\Database\Database;
use App\Service\ReservaService;

echo "--- EJECUTANDO TEST DE INTEGRACIÓN ---\n";

$db = Database::getConnection();

// 1. Inicia una transacción (modo borrador)
$db->beginTransaction();

try {
    $service = new ReservaService();

    // Fecha de hoy para la entrada
    $fecEntrada = date('Y-m-d');

    // Fecha 7 días después para la salida
    $fecSalida = date('Y-m-d', strtotime('+7 days'));

    $datosValidos = [
        'nombre_huesped' => 'Test Rollback',
        'email' => 'rollback@test.com',
        'alojamiento' => 'Suite Rollback',
        'fec_entrada' => $fecEntrada,
        'fec_salida' => $fecSalida,
        'importe' => 150,
        'notas' => 'Prueba de integración dinámica con rollback'
    ];

    // 2. Ejecuta la inserción a través del servicio
    $service->crearReserva($datosValidos);
    echo "✅ INSERCIÓN: Reserva creada dentro de la transacción.\n";

    // 3. Verifica que existe (dentro de la transacción sí se ve)
    $stmt = $db->prepare("SELECT COUNT(*) FROM RESERVA WHERE EMAIL_HUESPED = :email");
    $stmt->execute([':email' => 'rollback@test.com']);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        echo "✅ VERIFICACIÓN: El registro existe temporalmente en la BBDD.\n";
    } else {
        echo "❌ ERROR: No se encontró el registro.\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR EN EL TEST: " . $e->getMessage() . "\n";
} finally {
    // 4. Rollback para descartar los cambios
    $db->rollBack();
    echo "🔄 ROLLBACK EJECUTADO: Base de datos restaurada, sin rastro de basura.\n";
}

echo "--- FIN DEL TEST DE INTEGRACIÓN ---";
