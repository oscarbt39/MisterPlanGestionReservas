<?php

namespace App\Service;

use App\Repository\ReservaRepository;

class ReservaService {
    private ReservaRepository $repository;

    public function __construct() {
        $this->repository = new ReservaRepository();
    }

    
    /**
     * Obtiene todas las reservas
     * @param array $filtros Array con filtros
     * @return array Array con todas las reservas
     */
    public function listarReservas(array $filtros): array {
        return $this->repository->findFiltered($filtros);
    }

    /**
     * Obtiene una reserva por su ID o lanza una excepción si no existe
     * @param int $id ID de la reserva
     * @return array Array con los datos de la reserva
     */
    public function obtenerReservaPorId(int $id): array {
        $reserva = $this->repository->findById($id);
        
        if (!$reserva) {
            throw new \InvalidArgumentException("No se ha encontrado ninguna reserva con el ID proporcionado.");
        }
        
        return $reserva;
    }

    /**
     * Comprueba la coherencia entre fechas
     *  @param string $fecEntrada String con la fecha de entrada
     *  @param string $fecSalida String con la fecha de salida
     *  @return bool Boolean resultante de la comparacion de fechas
     */
    public function validarFechas(string $fecEntrada, string $fecSalida): bool {
        return strtotime($fecEntrada) < strtotime($fecSalida);
    }

    /**
     * Orquesta la creación de una reserva y su evento de historial.
     * @param array $datos Array con los datos de la reserva 
     * @return bool Booleano true verificando la ejecucion del metodo
     */
    public function crearReserva(array $datos): bool {
        // 1. Validamos fechas antes de hacer nada
        if (!$this->validarFechas($datos['fec_entrada'], $datos['fec_salida'])) {
            throw new \InvalidArgumentException("La fecha de salida debe ser posterior a la de entrada.");
        }

        $idReserva = $this->repository->insertarReserva($datos);

        $this->repository->insertarEvento($idReserva, 'CREACION', 'Reserva creada en el sistema.');

        return true;
    }

    /**
     * Orquesta la actualizacion del estado de una reserva, comprueba la validez del estado
     * @param int $id Integer con el ID de la reserva a modificar  
     * @param string $estado String con el nuevo estado de la reserva 
     * return bool Booleando resultante de la actualizacion 
     */
    public function actualizarEstadoReserva(int $id, string $estado): bool {
        if (!in_array(strtolower($estado), ['confirmada', 'pendiente', 'cancelada'])) {
            throw new \InvalidArgumentException("El nuevo estado debe ser uno de entre los tres disponibles");
        }
        return $this->repository->actualizarEstado($id, $estado);
    }
}