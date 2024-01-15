<?php

$method = $_SERVER['REQUEST_METHOD'];
$jsonData = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    // require_once 'postСontroller.php';
    // $controller = new PostController();
} elseif ($method === 'GET') {
    require_once 'controllers/getController.php';
    $controller = new GetController();
} elseif ($method === 'PATCH') {
    require_once 'controllers/patchСontroller.php';
    $controller = new PatchController();
} else {
    $controller = "";
}

$methodName = isset($jsonData['method']) && !empty($jsonData['method']) ? $jsonData['method'] : "";
$data = isset($jsonData['data']) && !empty($jsonData['data']) ? $jsonData['data'] : "";

try {
    if (empty($data)) {
        $response = $controller->$methodName();
    }
     else {
        $response = $controller->$methodName($data);
    }
} catch (Error $e) {
    if ($controller==="") {
        $response = "Ошибка: Не поддерживаемый HTTP метод.";
    }elseif (empty($methodName)) {
        $response = "Ошибка: Не указан метод.";
    }
    else{
        $response = "Ошибка: Данного метода не существует или недоступен. Проверьте метод отправки.";
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);

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
// 0 0 * * * curl -X PATCH http://phpmyadmin.topdom-erp.ru/TopDomApps-Backend -d '{"method": "createXML"}'
?>