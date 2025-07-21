<?php
namespace App\Core;

class DatabaseSessionHandler implements \SessionHandlerInterface
{
    private static \PDO $staticDbConnection;

    public function __construct(\PDO $dbConnection)
    {
        self::$staticDbConnection = $dbConnection;
    }

    public function open(string $save_path, string $session_name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $session_id): string
    {
        try {

            $stmt = self::$staticDbConnection->prepare("SELECT [data] FROM [sessions] WHERE [id] = :id");
            $stmt->bindParam(':id', $session_id);
            $stmt->execute();
            $data = $stmt->fetchColumn();
            return (string) $data;
        } catch (\PDOException $e) {
            error_log("Session read PDOException: " . $e->getMessage() . " Code: " . $e->getCode());
            return '';
        } catch (\Throwable $e) {
            error_log("Session read general error: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
            return '';
        }
    }

    public function write(string $session_id, string $session_data): bool
    {


        if (!isset(self::$staticDbConnection) || !self::$staticDbConnection instanceof \PDO) {
            error_log(">>> DEBUG SESSION WRITE <<< ERRO CRÍTICO: Conex\xc3\xa3o DB est\xc3\xa1tica \xc3\xa9 inv\xc3\xa1lida ou n\xc3\xa3o definida no write().");
            return false;
        }

        try {
            self::$staticDbConnection->query("SELECT 1");

        } catch (\PDOException $e) {
            error_log(">>> DEBUG SESSION WRITE <<< ERRO: Ping DB falhou. Conex\xc3\xa3o perdida. Mensagem: " . $e->getMessage() . " Code: " . $e->getCode());
            return false;
        } catch (\Throwable $e) {
            error_log(">>> DEBUG SESSION WRITE <<< ERRO: Ping DB falhou (geral). Mensagem: " . $e->getMessage() . " File: " . $e->getLine());
            return false;
        }

        try {
            $stmt = self::$staticDbConnection->prepare("
                MERGE [sessions] AS T
                USING (SELECT :id AS id, :data AS data, :timestamp AS timestamp) AS S
                ON T.id = S.id
                WHEN MATCHED THEN
                    UPDATE SET T.data = S.data, T.timestamp = S.timestamp
                WHEN NOT MATCHED THEN
                    INSERT (id, data, timestamp) VALUES (S.id, S.data, S.timestamp);
            ");

            $stmt->bindParam(':id', $session_id);
            $stmt->bindParam(':data', $session_data);
            $stmt->bindValue(':timestamp', time(), \PDO::PARAM_INT);
            $stmt->execute();
            error_log(">>> DEBUG SESSION WRITE <<< Escrita de sess\xc3\xa3o ID " . $session_id . " BEM-SUCEDIDA.");
            return true;
        } catch (\PDOException $e) {
            error_log(">>> DEBUG SESSION WRITE <<< ERRO PDO ao escrever sess\xc3\xa3o. Mensagem: " . $e->getMessage() . " Code: " . $e->getCode());
            return false;
        } catch (\Throwable $e) {
            error_log(">>> DEBUG SESSION WRITE <<< ERRO GERAL ao escrever sess\xc3\xa3o. Mensagem: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
            return false;
        }
    }

    public function destroy(string $session_id): bool
    {
        try {
            $stmt = self::$staticDbConnection->prepare("DELETE FROM [sessions] WHERE [id] = :id");
            $stmt->bindParam(':id', $session_id);
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            error_log("Session destroy PDOException: " . $e->getMessage() . " Code: " . $e->getCode());
            return false;
        } catch (\Throwable $e) {
            error_log("Session destroy general error: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $oldest_timestamp = time() - $max_lifetime;
            $stmt = self::$staticDbConnection->prepare("DELETE FROM [sessions] WHERE [timestamp] < :oldest_timestamp");
            $stmt->bindParam(':oldest_timestamp', $oldest_timestamp, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Session GC PDOException: " . $e->getMessage() . " Code: " . $e->getCode());
            return false;
        } catch (\Throwable $e) {
            error_log("Session GC general error: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
            return false;
        }
    }
}