<?php
class MysqlObjectDatabase
{
    private $conn;
    public function __construct($host, $username, $password, $database)
    {
        $this->conn = new mysqli($host, $username, $password, $database);
    }

    public function query($sql){
        $result = $this->conn->query($sql);
        return  $result->fetch_all( MYSQLI_ASSOC );
    }

    public function execute($sql){
        $this->conn->query($sql);
        return $this->conn->affected_rows;
    }

    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function insert_id()
    {
        return $this->conn->insert_id;
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
