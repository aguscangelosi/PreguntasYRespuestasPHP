<?php


include_once("helper/MysqlObjectDatabase.php");
include_once("helper/IncludeFilePresenter.php");
include_once("helper/Router.php");
include_once("helper/MustachePresenter.php");
include_once("helper/AuthHelper.php");
include_once("helper/QrHelper.php");


include_once("model/AuthModel.php");
include_once("model/GameModel.php");
include_once("model/RankingModel.php");
include_once("model/AdminModel.php");

include_once("controller/AuthController.php");
include_once("controller/GameController.php");
include_once("controller/RankingController.php");
include_once("controller/AdminController.php");

include_once("services/QuestionService.php");

include_once('vendor/mustache/src/Mustache/Autoloader.php');

class Configuration
{

    private static $authHelper;
    private static $QrHelper;
    public function __construct()
    {
        self::$authHelper = new AuthHelper();
        self::$QrHelper = new QRHelper();
    }

    public static function getAuthHelper()
    {
        return self::$authHelper;
    }

    public static function getQrHelper()
    {
        return self::$QrHelper;
    }

    public function getAuthController(){
        return new AuthController($this->getAuthModel(), $this->getPresenter(),$this->getAuthHelper(), $this->getQrHelper());
    }

    public function getGameController()
    {
        return new GameController($this->getGameModel(), $this->getPresenter(),$this->getAuthHelper());
    }

    public function getRankingController()
    {
        return new RankingController($this->getRankingModel(), $this->getPresenter(),$this->getAuthHelper());
    }

    public function getAdminController()
    {
        return new AdminController($this->getAdminModel(), $this->getPresenter(),$this->getAuthHelper());
    }

    public function getPdfController()
    {
        return new PdfController();
    }


    private function getAuthModel(){
        return new AuthModel($this->getDatabase());
    }

    private function getGameModel(){
        return new GameModel($this->getDatabase(), $this->getService());
    }

    public function getRankingModel()
    {
        return new RankingModel($this->getDatabase());
    }

    public function getAdminModel()
    {
        return new AdminModel($this->getDatabase(), $this->getService());
    }


    private function getPresenter()
    {
        return new MustachePresenter("./view", $this->getAuthHelper());
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
        return new Router($this, "getAuthController", "init", Configuration::$authHelper);
    }

    public function getService()
    {
        return new QuestionService($this->getDatabase(), $this->getAuthHelper());
    }


}