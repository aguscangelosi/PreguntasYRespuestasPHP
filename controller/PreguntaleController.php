<?php

class PreguntaleController
{
    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function init()
    {
        $this->presenter->show('login');
    }

    public function redirectHome()
    {
        header('location: /pokedex');
        exit();
    }
}