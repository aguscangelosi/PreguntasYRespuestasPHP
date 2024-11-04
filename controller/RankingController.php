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
        if($rankingList){
            $this->presenter->show('ranking', ['rankingList' => $rankingList]);
        }else{
            $this->presenter->show('notFound');
        }
    }

    public function profile()
    {
        $user = $this->authHelper->getUser();
        $userId = $user["user_id"];

        $data = $this->model->getProfile($userId);

        if($data){
            $this->presenter->show('profile', $data);
        }else{
            $this->presenter->show('notFound');
        }
    }

}