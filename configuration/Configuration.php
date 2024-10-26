<?php
include_once("helper/MysqlObjectDatabase.php");
include_once("helper/IncludeFilePresenter.php");
include_once("helper/Router.php");
include_once("helper/MustachePresenter.php");


include_once("model/AuthModel.php");
include_once("model/GameModel.php");
include_once("controller/AuthController.php");
include_once("controller/GameController.php");


include_once('vendor/mustache/src/Mustache/Autoloader.php');

class Configuration
{
    public function __construct()
    {
    }

//    public function getLoginController(){
//        return new PreguntaleController($this->getPokedexModel(), $this->getPresenter());
//    }

    public function getAuthController(){
        return new AuthController($this->getAuthModel(), $this->getPresenter());
    }

    public function getGameController()
    {
        return new GameController($this->getGameModel(), $this->getPresenter());
    }


    private function getAuthModel(){
        return new AuthModel($this->getDatabase());
    }

    private function getGameModel(){
        return new GameModel($this->getDatabase());
    }

    private function getPresenter()
    {
        return new MustachePresenter("./view");
    }


    private function getDatabase()
    {
        $config = parse_ini_file('configuration/config.ini');
        return new MysqlObjectDatabase(
            $config['host'],
            $config['username'],
            $config['password'],
            $config["database"]
        );
    }

    public function getRouter()
    {
        return new Router($this, "getAuthController", "init");
    }

}