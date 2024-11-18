<?php

class RankingModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getRanking(){
        $sql = "SELECT DISTINCT u.id, u.username, MAX(ug.puntaje) AS puntaje
               FROM user_game ug JOIN user u ON ug.user_id = u.id
               GROUP BY u.id 
               ORDER BY ug.puntaje DESC";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $resultrows = $result->fetch_all(MYSQLI_ASSOC);

        return $resultrows;
    }

    public function getPosition($idUser)
    {
        $sql = "SELECT *
            FROM (SELECT
                u.id,
                MAX(g.puntaje) AS puntaje,
                RANK() OVER (ORDER BY MAX(g.puntaje) DESC) AS posicion
                  FROM user u JOIN user_game g ON u.id = g.user_id
                  GROUP BY u.id) AS ranking
            WHERE id = ?;
";


        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $idUser);
        $stmt->execute();
        $result = $stmt->get_result();
        $resultFinal = $result->fetch_assoc();

        return $resultFinal;
    }

    public function getDataProfile($idUser)
        //foto perfil - agregar bdd
        //Nombre de usuario
        //Mejor partidas (historico)
        //trampitas - agregar bdd
    {
        $sql = "SELECT DISTINCT u.id, u.username, MAX(ug.puntaje)
               FROM user_game ug JOIN user u ON ug.user_id = u.id
               WHERE u.id = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $idUser);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getQrCode($idUser){
        $basePath = 'localhost' . '/PreguntasYRespuestasPHP/img/profile/';
        $userImagePath = $basePath . 'usuario_' . $idUser . '.png';

        return $userImagePath;
    }

    public function getProfile($idUser){
        //foto perfil
        //Nombre de usuario
        //Mejor partidad (historico)
        //posicion Ranking
        //qr -- Se crea a parte
        //porcentaje de partidas (ganadas/perdidas) -- Ver si tiene sentido
        //trampitas -- agregar bdd

        $position = $this->getPosition($idUser);
        $dataProfile = $this->getDataProfile($idUser);
//        $dataProfile['img'] = $this->getQrCode($idUser);

        return ['position'=>$position, 'dataProfile'=>$dataProfile];
    }


}