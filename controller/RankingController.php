<?php

class RankingController
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

    public function rankingPosition()
    {
        $rankingList = $this->model->getRanking();
        $data = isset($_GET['id']);
        if($rankingList){
            $this->presenter->show('ranking', ['rankingList' => $rankingList, 'id' => $data]);
        }else{
            $this->presenter->show('notFound');
        }
    }

    public function profile()
    {
        $userId = $_GET['id'] ?? $this->authHelper->getUser()["user_id"];
        $data = $this->model->getProfile($userId);

        $this->presenter->show($data ? 'profile' : 'notFound', $data);
    }

}