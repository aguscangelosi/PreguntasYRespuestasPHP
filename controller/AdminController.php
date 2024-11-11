<?php

class AdminController
{
    private $model;
    private $presenter;
    private $authHelper;

    public function __construct($model, $presenter, $authHelper)
    {
        $this->model = $model;
        $this->presenter = $presenter;
        $this->authHelper = $authHelper;
    }

    public function home(){
        $this->presenter->show('homeAdmin');
    }

    public function homeEdit(){
        $reportedQuestions = $this->model->obtainReportedQuestions();
        $this->presenter->show('homeEdit', $reportedQuestions);
    }

    public function createQuestion(){
        $categories = $this->model->findCategories();
        $this->presenter->show('createQuestion', ['categories' => $categories]);
    }

    public function createQuestionPost()
    {

        $question = isset($_POST['question-text']) ? $_POST['question-text'] : '';

        $correctAnswer = isset($_POST['answer1']);
        $answer2 = isset($_POST['answer2']);
        $answer3 = isset($_POST['answer3']);
        $answer4 = isset($_POST['answer4']);

        $category = isset($_POST['category']) ? $_POST['category'] : '';

        $this->model->insertNewQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category);
        $this->redirectHome();
        $this->presenter->show('createQuestion', ['success' => "Pregunta agregada exitosamente"]);
        exit();

    }

    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/homeEdit');
    }




}