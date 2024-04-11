<?php

namespace Utils;

use Exception;
use mysqli;
use mysqli_sql_exception;

class Database
{

    private const HOST = 'mysql_server';
    private const USERNAME = 'root';
    private const PASSWORD = 'admin';
    private const DATABASE = 'mrango';

    private mysqli $conn;

    public function __construct()
    {
        $this->conn = new mysqli(self::HOST, self::USERNAME, self::PASSWORD, self::DATABASE);
    }

    public function close(): void
    {
        if ($this->conn->ping()) {
            $this->conn->close();
        }
    }

    public function __destructor(): void
    {
        $this->close();
    }

    private function execute_query(string $query, ...$params): array | bool
    {
        try {
            $stmt = $this->conn->prepare($query);

            if ($stmt === false) {
                throw new Exception("Errore nella preparazione della query");
            }

            if (!empty($params)) {
                $stmt->bind_param(str_repeat("s", count($params)), ...$params);
            }

            $success = $stmt->execute();
            $res_set = $stmt->get_result();

            if ($res_set !== false) {
                $ret_set = $res_set->fetch_all(MYSQLI_ASSOC);
            } else {
                $ret_set = $success;
            }

            $stmt->close();
        } catch (mysqli_sql_exception $ex) {
            // TODO: implementazione pagine di errore personalizzate
            http_response_code(500);
            echo $ex->getMessage();
            exit();
        } catch (Exception $ex) {
            // TODO: Gestione di altri tipi di eccezioni, se necessario
            http_response_code(500);
            echo $ex->getMessage();
            exit();
        }
        return $ret_set;
    }

    public function add_user(): void
    {
        $this->execute_query(
            "INSERT INTO Users(username, password, status) VALUES(?,?,'USER');",
            strval(random_int(0, 1000)),
            strval(random_int(0, 1000)));
    }
}