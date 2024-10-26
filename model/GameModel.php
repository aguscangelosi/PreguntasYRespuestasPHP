<?php

class GameModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function dropCategory(){
        $sql = "SELECT * FROM category ORDER BY RAND() LIMIT 1";

        $category = $this->database->query($sql);

        return $category;
    }
}