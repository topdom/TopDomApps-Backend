<?php
class DeleteController
{
    private $dbh;
    function __construct()
    {
        include("./classes/databaseClass.php");
        $database = new Database();
        $this->dbh = $database->conncect();
    }

    //Все данные на главную страницу 
    public function deleteOnId($data)
    {
        $sql = "DELETE FROM projects WHERE id = " . $data["id"];
        $projects = $this->dbh->prepare($sql);
        if ($projects->execute()) {
            // Код, который будет выполнен при успешном выполнении запроса
            return "Запрос успешно выполнен. Удалена запись с ID: {$data["id"]}";
        } else {
            // Код, который будет выполнен в случае ошибки выполнения запроса
            return "Ошибка при выполнении запроса.";
        }

    }

    public function deletePhoto($data)
    {
        include './classes/photoManagerClass.php';
        $photoManager = new PhotoManager();
        $result = $photoManager->deletePhotoInDbAndFolders($data);
        return $result;

    }


}
?>