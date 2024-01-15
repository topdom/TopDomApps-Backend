<?php
class GetController
{
    private $dbh;
    function __construct()
    {
        include("./db.php");
        $this->dbh = conncect();
    }

    //Все данные на главную страницу 
    public function getAllOnMain()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`is_cian`,`is_avito`,`is_derect`,`is_domclick`,`floor`,`number`,`sq`,`K`,`S`,`G`,`B`,`T`,`link`,`price`,`seo` FROM `projects`");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        return $houses;
    }

    //Все данные на страницу Циан
    public function getAllCian()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`floor`,`number`,`sq`,`category_obj`,`address`,`phone`,`cadastr_number`,`K`,`S`,`G`,`area_land`,`price`,`photo`,`photo_planirovka`,`description`  FROM `projects` WHERE `is_cian` = 1;");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        return $houses;
    }
    //Все данные на страницу Авито
    public function getAllAvito()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`is_cian`,`is_avito`,`is_derect`,`is_domclick`,`floor`,`number`,`sq`,`K`,`S`,`G`,`B`,`T`,`link`,`price`,`seo` FROM `projects` where is_avito = 1");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        return $houses;
    }
    //Все данные на страницу Домклик
    public function getAllDomclick()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`is_cian`,`is_avito`,`is_derect`,`is_domclick`,`floor`,`number`,`sq`,`K`,`S`,`G`,`B`,`T`,`link`,`price`,`seo` FROM `projects` where is_domclick = 1");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        return $houses;
    }
    //Все данные на страницу ЯндексДирект
    public function getAllDirect()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`is_cian`,`is_avito`,`is_derect`,`is_domclick`,`floor`,`number`,`sq`,`K`,`S`,`G`,`B`,`T`,`link`,`price`,`seo` FROM `projects` where is_derect = 1");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);
        return $houses;
    }

}
?>