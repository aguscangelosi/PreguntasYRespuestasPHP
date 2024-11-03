<?php

class MustachePresenter{
    private $mustache;
    private $partialsPathLoader;

    private $authHelper;

    public function __construct($partialsPathLoader, $authHelper){
        Mustache_Autoloader::register();
        $this->mustache = new Mustache_Engine(
            array(
            'partials_loader' => new Mustache_Loader_FilesystemLoader( $partialsPathLoader )
        ));
        $this->partialsPathLoader = $partialsPathLoader;
        $this->authHelper = $authHelper;
    }

    public function show($contentFile , $data = array() ){
        echo  $this->generateHtml(  $this->partialsPathLoader . '/' . $contentFile . "View.mustache" , $data);
    }

    public function generateHtml($contentFile, $data = array()) {
        $contentAsString = file_get_contents(  $this->partialsPathLoader .'/header.mustache');
        $contentAsString .= file_get_contents( $contentFile );
        $contentAsString .= file_get_contents($this->partialsPathLoader . '/footer.mustache');
        $data['token'] = $this->authHelper->getUser();
        var_dump($data);
        return $this->mustache->render($contentAsString, $data);
    }
}