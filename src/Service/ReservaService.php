<?php

namespace App\Service;

use App\Repository\ReservaRepository;

class ReservaService
{
    private ReservaRepository $repository;

    public function __construct()
    {
        $this->repository = new ReservaRepository();
    }


    /**
     * Obtiene todas las reservas
     * @param array $filtros Array con filtros
     * @return array Array con todas las reservas
     */
    public function listarReservas(array $filtros): array
    {
        return $this->repository->findFiltered($filtros);
    }

    /**
     * Obtiene una reserva por su ID o lanza una excepción si no existe
     * @param int $id ID de la reserva
     * @return array Array con los datos de la reserva
     */
    public function obtenerReservaPorId(int $id): array
    {
        $reserva = $this->repository->findById($id);

        if (!$reserva) {
            throw new \InvalidArgumentException("No se ha encontrado ninguna reserva con el ID proporcionado.");
        }

        return $reserva;
    }

    /**
     * Comprueba de forma estricta la coherencia, formato y vigencia de las fechas.
     * @param string $fecEntrada Fecha de entrada (Y-m-d)
     * @param string $fecSalida Fecha de salida (Y-m-d)
     * @return bool True si todo es correcto, o lanza excepción si falla algo
     */
    public function validarFechas(string $fecEntrada, string $fecSalida): bool
    {
        $hoy = new \DateTime('today');
        $entrada = \DateTime::createFromFormat('Y-m-d', $fecEntrada);
        $salida = \DateTime::createFromFormat('Y-m-d', $fecSalida);

        // 1. Validar que el formato y la fecha sean reales (evita 2026-02-30, letras, etc.)
        if (!$entrada || !$salida || $entrada->format('Y-m-d') !== $fecEntrada || $salida->format('Y-m-d') !== $fecSalida) {
            throw new \InvalidArgumentException("El formato de las fechas debe ser YYYY-MM-DD y corresponder a fechas reales.");
        }

        // 2. Validar que la fecha de entrada no sea en el pasado
        if ($entrada < $hoy) {
            throw new \InvalidArgumentException("La fecha de entrada no puede ser anterior al día de hoy.");
        }

        // 3. Validar que la fecha de salida sea estrictamente posterior a la de entrada
        if ($entrada >= $salida) {
            throw new \InvalidArgumentException("La fecha de salida debe ser estrictamente posterior a la de entrada.");
        }

        return true;
    }

    /**
     * Orquesta la creación de una reserva y su evento de historial.
     * @param array $datos Array con los datos de la reserva 
     * @return bool Booleano true verificando la ejecucion del metodo
     */
    public function crearReserva(array $datos): bool
    {
        // 1. Validar que existan las claves obligatorias en el array
        $camposRequeridos = ['nombre_huesped', 'email', 'alojamiento', 'fec_entrada', 'fec_salida', 'importe'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new \InvalidArgumentException("El campo obligatorio '{$campo}' no puede estar vacío.");
            }
        }

        // 2. Validar formato de email
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("El formato del correo electrónico no es válido.");
        }

        // 3. Validar importe (debe ser numérico y mayor que 0)
        if (!is_numeric($datos['importe']) || (float)$datos['importe'] <= 0) {
            throw new \InvalidArgumentException("El importe de la reserva debe ser un número mayor a 0.");
        }

        // 4. Validar fechas antes de hacer nada
        if (!$this->validarFechas($datos['fec_entrada'], $datos['fec_salida'])) {
            throw new \InvalidArgumentException("La fecha de salida debe ser posterior a la de entrada.");
        }

        // 5. Normalizar o asignar estado por defecto de forma segura
        $estado = isset($datos['estado']) ? strtolower($datos['estado']) : 'pendiente';
        if (!in_array($estado, ['confirmada', 'pendiente', 'cancelada'])) {
            throw new \InvalidArgumentException("El estado proporcionado no es válido.");
        }
        $datos['estado'] = $estado;

        // 6. Sanitizar notas opcionales (evitar XSS básico si se muestran en HTML)
        $datos['notas'] = isset($datos['notas']) ? htmlspecialchars(trim($datos['notas']), ENT_QUOTES, 'UTF-8') : '';

        // 7. Insertar en la base de datos a través del repositorio
        $idReserva = $this->repository->insertarReserva($datos);

        // 8. Registrar el evento en el historial
        $this->repository->insertarEvento($idReserva, 'CREACION', 'Reserva creada en el sistema.');

        return true;
    }

    /**
     * Orquesta la actualizacion del estado de una reserva, comprueba la validez del estado
     * @param int $id Integer con el ID de la reserva a modificar  
     * @param string $estado String con el nuevo estado de la reserva 
     * return bool Booleando resultante de la actualizacion 
     */
    public function actualizarEstadoReserva(int $id, string $estado): bool
    {
        if (!in_array(strtolower($estado), ['confirmada', 'pendiente', 'cancelada'])) {
            throw new \InvalidArgumentException("El nuevo estado debe ser uno de entre los tres disponibles");
        }
        return $this->repository->actualizarEstado($id, $estado);
    }
}
