<?php

namespace App\Repository;

use App\Database\Database;
use PDO;

class ReservaRepository
{

    /**
     * Obtiene todas las reservas ordenadas por fecha de creación descendente y aplicando los filtros
     * indicados.
     * @param array $filtros Array con los filtros
     * @return array $data Array con el resultado de la consulta
     */
    public function findFiltered(array $filtros): array
    {
        $db = Database::getConnection();
        $sql = "SELECT * FROM RESERVA WHERE 1=1";
        $params = [];

        if (!empty($filtros['estado'])) {
            $sql .= " AND ESTADO = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['nombre'])) {
            $sql .= " AND NOMBRE_HUESPED LIKE :nombre";
            $params[':nombre'] = "%" . $filtros['nombre'] . "%";
        }

        if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
            $sql .= " AND FEC_ENTRADA BETWEEN :desde AND :hasta";
            $params[':desde'] = $filtros['desde'];
            $params[':hasta'] = $filtros['hasta'];
        }

        $sql .= " ORDER BY FEC_CREACION DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca una reserva concreta por su ID de forma segura.
     * @param int $id Integer con el identificador a buscar 
     * @return array $result Array con el resultado de la consulta
     */
    public function findById(int $id): ?array
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM RESERVA WHERE ID_RESERVA = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : null;
    }

    /**
     * Inserta un nuevo evento de reserva, parametrizado para evitar SQL injection
     * @param int $idReserva Integer con el ID de la reserva
     * @param string $tipo String con el tipo de la reserva
     * @param string $desc String con la descripcion de la reserva
     * @return bool $result Booleano con el resultado de la insercion 
     */
    public function insertarEvento(int $idReserva, string $tipo, string $desc): bool
    {
        $db = Database::getConnection();

        $sqlQuery = "INSERT INTO RESERVA_EVENTO (ID_RESERVA, TIPO, DESCRIPCION) VALUES (:idReserva, :tipo, :descripcion)";
        $sqlStatement = $db->prepare($sqlQuery);
        $result = $sqlStatement->execute([':idReserva' => $idReserva, ':tipo' => $tipo, ':descripcion' => $desc]);

        return $result;
    }

    /**
     * Inserta una nueva reserva, recibe un array con los datos, lo escribe en la bbdd y devuelve el numero 
     * de identifiacion recien creado
     * @param array $datos Array con los datos de la reserva
     * @return int $id Integer con el valor del ultimo ID
     */
    public function insertarReserva(array $datos): int
    {
        $db = Database::getConnection();

        $sqlQuery = "INSERT INTO RESERVA (NOMBRE_HUESPED, EMAIL_HUESPED, NOM_ALOJAMIENTO, FEC_ENTRADA, FEC_SALIDA, ESTADO, IMPORTE, NOTAS) 
                 VALUES (:nombre, :email, :alojamiento, :entrada, :salida, :estado, :importe, :notas)";

        $sqlStatement = $db->prepare($sqlQuery);

        $sqlStatement->execute([
            ':nombre'      => $datos['nombre_huesped'],
            ':email'       => $datos['email'],
            ':alojamiento' => $datos['alojamiento'],
            ':entrada'     => $datos['fec_entrada'],
            ':salida'      => $datos['fec_salida'],
            ':estado'      => $datos['estado'],
            ':importe'     => (float)$datos['importe'],
            ':notas'       => $datos['notas']
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Recibe un id y un nuevo estado y actualiza esa reserva
     * @param int $idUpd Integer con el ID de la reserva
     * @param string $nuevoEstado String con el estado de la actualizacion 
     * @return bool Boolean confirmando la actualizacion
     */
    public function actualizarEstado(int $idUpd, string $nuevoEstado): bool
    {
        $db = Database::getConnection();

        $sqlQuery = "UPDATE RESERVA SET ESTADO = :nuevoEstado WHERE ID_RESERVA = :id";

        $sqlStatement = $db->prepare($sqlQuery);
        $sqlStatement->execute([':nuevoEstado' => $nuevoEstado, ":id" => $idUpd]);


        return true;
    }
}
