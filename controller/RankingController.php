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
        $tokenInfo =  $this->authHelper->getUser();
        $userId = $_GET['id'] ?? $tokenInfo["user_id"];
        $data = $this->model->getProfile($userId);

        $data['dataProfile']['picture'] = $tokenInfo['profile_picture'];

        $this->presenter->show($data ? 'profile' : 'notFound', $data);
    }

}