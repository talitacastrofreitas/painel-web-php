<?php

namespace App\Models;
use PDO;
use PDOException;

class ReservaModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProgramacaoDoDiaPorCampus($unidadeId)
    {
        try {
            $sql = "SELECT 
                        res_hora_inicio, res_hora_fim, curs_curso, compc_componente,
                        res_componente_atividade, res_componente_atividade_nome,
                        res_nome_atividade, res_modulo, res_professor, esp_nome_local_resumido
                    FROM reservas
                    LEFT JOIN cursos ON cursos.curs_id = reservas.res_curso
                    LEFT JOIN componente_curricular ON componente_curricular.compc_id = reservas.res_componente_atividade
                    INNER JOIN espaco ON espaco.esp_id = reservas.res_espaco_id
                    WHERE 
                        res_data = :res_data AND
                        esp_unidade = :unidade_id
                    ORDER BY res_hora_inicio ASC";

            $params = [
                ':res_data' => date('Y-m-d'),
                ':unidade_id' => $unidadeId
            ];

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {

            error_log($e->getMessage());
            return [];
        }

    }
}