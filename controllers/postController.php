<?php
// include_once "./includes/php-jwt/BeforeValidException.php";
// include_once "./includes/php-jwt/ExpiredException.php";
// include_once "./includes/php-jwt/SignatureInvalidException.php";
// include_once "./includes/php-jwt/JWT.php";
// include_once "./includes/php-jwt/Key.php";
class PostController
{

    // use \Firebase\JWT\JWT;
    // use \Firebase\JWT\Key;

    private $dbh;
    function __construct()
    {
        include("./classes/databaseClass.php");
        $database = new Database();
        $this->dbh = $database->conncect();
    }

    public function auth($data)
    {

        $login = $data['login'];
        $password = $data['password'];
        $auth = $this->dbh->prepare("SELECT * FROM `users` where `login` = :login");
        $auth->bindValue(':login', $login);
        $auth->execute();
        $data_user = $auth->fetchAll();
        // return $password;
        // return $data_user[0]["password"];
        if (password_verify($password, $data_user[0]["password"])) {
            return 'Пароль верен';
        } else {
            return 'Пароль неверен';
        }
    }
    public function createHashPassword($data)
    {
        $password = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        return $password;
    }

    public function create($data)
    {
        $maxNumberQuery = $this->dbh->query("SELECT COALESCE(MAX(number), 0) + 1 as maxNumber FROM projects where floor = '" . $data['floor'] . "'");
        $maxNumber = $maxNumberQuery->fetchColumn();

        $sql = "INSERT INTO `projects` (`number`,";
        $values = array();
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values[] = $value;
        }
        $sql = rtrim($sql, ', ') . ") VALUES (";
        array_unshift($values, $maxNumber); // Вставляем $maxNumber в начало массива значений
        foreach ($values as $value) {
            $sql .= "?, ";
        }
        $sql = rtrim($sql, ', ') . ")";
        $projects = $this->dbh->prepare($sql);

        // return $sql;
        if ($projects->execute($values)) {
            $lastInsertId = $this->dbh->lastInsertId();
            // Код, который будет выполнен при успешном выполнении запроса
            return "Запись успешно добавлена с ID: {$lastInsertId}";
        } else {
            // Код, который будет выполнен в случае ошибки выполнения запроса
            return "Ошибка при выполнении запроса." . $sql;
        }
    }

    public function update($data)
    {
        // $data = json_decode($data, true);
        $sql = "UPDATE `projects` SET";
        foreach ($data as $key => $value) {
            $sql .= ($key == "id") ? "" : " `$key` = '$value',";
        }
        $sql = substr($sql, 0, -1);
        $sql .= " WHERE `id` = {$data['id']} ";
        // return $sql;
        $projects = $this->dbh->prepare($sql);
        if ($projects->execute()) {
            // Код, который будет выполнен при успешном выполнении запроса
            return "Запрос успешно выполнен.";
        } else {
            // Код, который будет выполнен в случае ошибки выполнения запроса
            return "Ошибка при выполнении запроса.";
        }
    }

    public function addPhoto($data)
    {
        $datas = $data;
        include './classes/photoManagerClass.php';
        $photoManager = new PhotoManager();
        $result = $photoManager->addPhotoInDbAndFolders($datas);
        return $result;
    }

}
?>