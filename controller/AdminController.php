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




}