<?php
// Устанавливаем заголовки CORS
// header("Access-Control-Allow-Origin: *");
// // header("Access-Control-Allow-Origin: https://phpmyadmin.topdom-erp.ru");
// header("Access-Control-Allow-Methods: GET, POST,PATCH");
// header("Access-Control-Allow-Headers: *");

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: *');

header("Access-Control-Allow-Headers: *");

$method = $_SERVER['REQUEST_METHOD'];
//Проверяем входящие значения для geta
if ($method === "GET") {
    $methodName = isset($_GET['method']) ? $_GET['method'] : "";
    $data = isset($_GET['data']) ? $_GET['data'] : "";
    $id = isset($_GET['id']) ? $_GET['id'] : "";
    $img_type = isset($_GET['img_type']) ? $_GET['img_type'] : "";
    if ((!empty($id) && !empty($img_type)) || (!empty($id) && !empty($img_type))) {
        $data = ["id" => $id, "img_type" => $img_type];
    }
} else {
    //Обработчик для получения файлов и дальнешего их добавения
    if (isset($_POST['method']) && !empty($_POST['method'])) {
        $methodName = isset($_POST['method']) ? $_POST['method'] : "";
        $data = [
            "type" => $_POST['type'],
            "id" => $_POST['id'],
            "files" => $_FILES
        ];
    } else {
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $methodName = isset($jsonData['method']) && !empty($jsonData['method']) ? $jsonData['method'] : "";
        $data = isset($jsonData['data']) && !empty($jsonData['data']) ? $jsonData['data'] : "";
    }

}
//Основная проверка метода данных
if ($method === 'POST') {
    require_once 'controllers/postController.php';
    $controller = new PostController();
} elseif ($method === 'GET') {
    require_once 'controllers/getController.php';
    $controller = new GetController();
} elseif ($method === 'PATCH') {
    require_once 'controllers/patchСontroller.php';
    $controller = new PatchController();
} elseif ($method === 'DELETE') {
    require_once 'controllers/deleteСontroller.php';
    $controller = new DeleteController();
} else {
    $controller = "";
}

try {
    if (empty($data)) {
        $response = $controller->$methodName();
        $response = json_encode($response, JSON_UNESCAPED_UNICODE);
        echo $response = str_replace('\\', '', $response);
    } else {
        $response = $controller->$methodName($data);
        $response = json_encode($response, JSON_UNESCAPED_UNICODE);
        echo $response = str_replace('\\', '', $response);
    }
} catch (Error $e) {
    if ($controller === "") {
        // http_response_code(405);
        $response = "Ошибка: Не поддерживаемый HTTP метод.";
        // var_dump($_POST);
    } elseif (empty($methodName)) {
        // http_response_code(405);
        $response = "Ошибка: Не указан метод.";
        // var_dump($_POST);
    } else {
        // http_response_code(405);
        $response = "Ошибка: Данного метода не существует или недоступен. Проверьте метод отправки.";
        // var_dump($_POST);
    }
}



// Шпора
// GET: Используется для получения данных с сервера. Обычно используется 
//      для получения ресурсов, таких как HTML-страницы, изображения или других статических файлов.

// POST: Используется для отправки данных на сервер для обработки. Обычно используется 
//       для отправки данных из HTML-формы или для создания новых ресурсов на сервере.

// PUT: Используется для обновления существующего ресурса на сервере. Обычно используется 
//      для обновления данных, отправляемых вместе с запросом.

// DELETE: Используется для удаления ресурса на сервере. Обычно используется 
//         для удаления данных или удаления ресурса целиком.

// HEAD: Аналогичен методу GET, но в ответе сервера возвращаются только заголовки без тела ответа.
//       Часто используется для получения метаданных о ресурсе без необходимости получения полного содержимого.

// OPTIONS: Используется для получения информации о возможностях сервера или параметрах конкретного ресурса. 
//          Часто используется для проверки доступных методов и заголовков для конкретного ресурса.

// PATCH: Используется для частичного обновления существующего ресурса на сервере. Обычно используется
//       для обновления только части данных ресурса, отправляемых вместе с запросом.

// На будущее
// Отправка на Cron  запроса на обновление xml файла 
// 0 0 * * * curl -X PATCH http://phpmyadmin.topdom-erp.ru/TopDomApps-Backend/index.php -d '{"method": "createTurboXML","data":{"id":"1"}}'
?>