<?php
class GetController
{
    public $dbh;
    function __construct()
    {
        // include("./db.php");
        include("./classes/databaseClass.php");
        $database = new Database();
        $this->dbh = $database->conncect();
    }

    //Все данные на главную страницу 
    public function getAllOnMain()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`is_cian`,`is_avito`,`is_direct`,`is_domclick`,`floor`,`number`,`sq`,`name`,`category_obj`,`town` FROM `projects`");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        if (empty($houses)) {
            http_response_code(404);
            return "Нет проектов!";
        } else {
            return $houses;
        }
    }

    //Все данные на страницу Циан
    public function getAllCian()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`floor`,`sq`,`category_obj`,`address`,`phone`,`cadastr_number`,`K`,`S`,`G`,`area_land`,`price`,`photo`,`photo_planirovka`,`description`,`name`,`town`,`number`   FROM `projects` WHERE `is_cian` = 1;");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        if (empty($houses)) {
            http_response_code(404);
            return "Нет проектов для Циана";
        } else {
            return $houses;
        }
    }
    //Все данные на страницу Авито
    public function getAllAvito()
    {
        $projects = $this->dbh->prepare("SELECT `id`, `phone`,`description`, `image`,`address`,`category_obj`,`price`,`rooms`,`floor`,`wallsType`,`sq`,`area_land`,`renovation`,`town`,`name`,`number`  FROM `projects` WHERE `is_avito` = 1;");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        if (empty($houses)) {
            http_response_code(404);
            return "Нет проектов для Авито!";
        } else {
            return $houses;
        }
    }
    //Все данные на страницу Домклик
    public function getAllDomclick()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`floor`,`sq`,`category_obj`,`address`,`phone`,`cadastr_number`,`K`,`S`,`G`,`area_land`,`price`,`photo`,`photo_planirovka`,`description`,`name`,`town`,`number`   FROM `projects` WHERE `is_domclick` = 1;");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        if (empty($houses)) {
            http_response_code(404);
            return "Нет проектов для Домклик!";
        } else {
            return $houses;
        }
    }
    //Все данные на страницу ЯндексДирект
    public function getAllDirect()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`category_obj`,`address`,`floor`,`phone`,`cadastr_number`, `price`, `sq`,`area_land`, `renovation`,`description`,`name`,`town`,`number`  FROM `projects` where is_direct = 1");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        if (empty($houses)) {
            http_response_code(404);
            return "Нет проектов для Яндекс Недвижимости!";
        } else {
            return $houses;
        }
    }

    public function getPhoto($data)
    {
        include './classes/photoManagerClass.php';
        $photoManager = new PhotoManager();
        $result = $photoManager->checkingPhoto($data);

        return $result;
    }


}
?>