<?php
namespace App\Models;

class ColaboradorModel
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
    }

    public function checkEmailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM admin WHERE admin_email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

}