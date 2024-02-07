<?php
class PhotoManager
{
    public $dbh;
    function __construct()
    {
        $database = new Database();
        $this->dbh = $database->conncect();
    }

    public function checkingPhoto($data)
    {
        $answer = $this->checkingPhotoInDB($data);
        if (!empty($answer)) {
            return $answer;
        } else {
            return $this->checkingPhotoInDir($data);
        }
    }

    public function checkingPhotoInDB($data)
    {
        $projects_info = $this->dbh->prepare("SELECT i_src.url, i_src.url as name, i_src.id as uid FROM projects p left join img_src_for_projects i_src on p.id = i_src.id_project where i_src.id_project = :id and i_src.type= :img_type");
        $projects_info->bindValue(':id', $data['id']);
        $projects_info->bindValue(':img_type', $data['img_type']);
        $projects_info->execute();
        return $projects_info->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCodeProject($data)
    {
        $projects_info = $this->dbh->prepare("SELECT concat(floor,'-',number) as code FROM `projects` where id = :id");
        $projects_info->bindValue(':id', $data['id']);
        $projects_info->execute();
        return $projects_info->fetchAll(PDO::FETCH_ASSOC);
    }
    public function checkingPhotoInDir($data)
    {
        $code = $this->getCodeProject($data);
        if (empty($code[0]['code'])) {
            http_response_code(404);
            return "Проект не найден!";
        } else {
            $dir = './images/' . $code[0]['code'];
            $files = glob($dir . '/*' . $data['img_type'] . '*.{jpg,png}', GLOB_BRACE);
            if ($files) {
                foreach ($files as &$file) {
                    $file = str_replace('./', 'https://phpmyadmin.topdom-erp.ru/TopDomApps-Backend/', $file);
                    $projects_info = $this->dbh->prepare("INSERT INTO img_src_for_projects (id_project, type, url) VALUES (:id_project, :type, :url)");
                    $projects_info->bindValue(':id_project', $data['id']);
                    $projects_info->bindValue(':type', $data['img_type']);
                    $projects_info->bindValue(':url', $file);
                    $projects_info->execute();
                }
                return $this->checkingPhotoInDB($data);
            } else {
                http_response_code(404);
                return "Нет фотографий у проекта по заданному типу!";
            }
        }

    }

    public function addPhotoInDbAndFolders($data)
    {
        $check_in_dir = $this->getCodeProject($data);
        $fullPath = './images/' . $check_in_dir[0]['code'];

        //Создаем папку если её нет
        if (!is_dir($fullPath)) {
            mkdir($fullPath);
        }
        $expansion = explode('/', $data['files']['file']['type']); //Расширение файла
        $new_filename = 'TOPDOM.RF-' . $check_in_dir[0]['code'] . '_' . $data['type'] . '_' . rand(1, 20) . '.' . end($expansion);
        $filepath = $fullPath . '/' . $new_filename;

        //Проверяем есть ли такой файл, если есть генерируем другое ия пока не получится проти проверку
        while (file_exists($filepath)) {
            $new_filename = 'TOPDOM.RF-' . $check_in_dir[0]['code'] . '_' . $data['type'] . '_' . rand(1, 20) . '.' . end($expansion);
            $filepath = $fullPath . '/' . $new_filename;
        }
        $data['files']['file']['name'] = $new_filename;
        $uploadedFile = $data['files']['file']['tmp_name'];
        $newFilePath = $fullPath . '/' . $new_filename;

        if (move_uploaded_file($uploadedFile, $newFilePath)) {
            // return "Файл успешно сохранен";
            $url = 'https://phpmyadmin.topdom-erp.ru/TopDomApps-Backend/images/' . $check_in_dir[0]['code'] . '/' . $data['files']['file']['name'];
            $projects_info = $this->dbh->prepare("INSERT INTO img_src_for_projects (id_project, type, url) VALUES (:id_project, :type, :url)");
            $projects_info->bindValue(':id_project', $data['id']);
            $projects_info->bindValue(':type', $data['type']);
            $projects_info->bindValue(':url', $url);
            if ($projects_info->execute()) {
                return "Файл успешно сохранен  в папку и БД";
            } else {
                return "Ошибка при сохранении файла в БД";
            }

        } else {
            return "Ошибка при сохранении файла";
        }
    }

    public function getPhotoOnId($data)
    {
        $projects_info = $this->dbh->prepare("SELECT * FROM `img_src_for_projects` where id = :id");
        $projects_info->bindValue(':id', $data['id']);
        $projects_info->execute();
        return $projects_info->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePhotoInDbAndFolders($data)
    {
        $photo = $this->getPhotoOnId($data);
        if (empty($photo)) {
            http_response_code(404);
            return "Фото не найдено!";
        } else {
            $url_parse = explode('/', $photo[0]['url']); //Расширение файла
            $length = count($url_parse);
            $photo_path = './images/' . $url_parse[$length - 2] . '/' . end($url_parse);
            if (file_exists($photo_path)) {
                unlink($photo_path);
                $sql = "DELETE FROM img_src_for_projects WHERE id = " . $data["id"];
                $projects = $this->dbh->prepare($sql);
                if ($projects->execute()) {
                    return "Файл " . $photo_path . " успешно удален";
                } else {
                    http_response_code(409);
                    return "Ошибка при удалении файла " . $photo_path . " в БД.";
                }
            } else {
                http_response_code(404);
                return "Файл не существует";
            }
            // return $photo_path;

        }

        // $projects_info = $this->dbh->prepare("DELETE FROM img_src_for_projects WHERE id = :id");
        // $projects_info->bindValue(':id', $data['id']);
        // $projects_info->execute();
        // return $this->checkingPhotoInDB($data);
    }
}