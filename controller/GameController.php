<?php

class GameController
{

    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function play()
    {
        $this->presenter->show('roulette');
    }

    public function spin()
    {
        $this->model->dropCategory();
    }

    public function findQuestions()
    {
        $category = isset($_GET['category']);
        if (!$category) {
            $this->presenter->show('notFound');
        }
            $questions = $this->model->findQuestions($category);
            $this->presenter->show('question', $questions);
    }


    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/index.php');
        exit();
    }
}