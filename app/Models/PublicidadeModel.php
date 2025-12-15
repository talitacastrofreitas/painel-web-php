<?php

namespace App\Models;
use PDO;
use PDOException;

class PublicidadeModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

   public function getPublicidadesAtivas()
{
    // Adicionado o campo 'duracao'
    $sql = "SELECT caminho_imagem, media_type, duracao FROM publicidades WHERE ativo = 1 ORDER BY ordem_exibicao ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}