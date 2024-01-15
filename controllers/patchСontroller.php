<?php
class PatchController
{
    private $dbh;
    function __construct()
    {
        include("./db.php");
        $this->dbh = conncect();
    }

    public function createCianXML()
    {
        $projects = $this->dbh->prepare("SELECT `id`,`floor`,`number`,`sq`,`category_obj`,`address`,`phone`,`cadastr_number`,`K`,`S`,`G`,`area_land`,`price`,`photo`,`photo_planirovka`,`description`  FROM `projects` WHERE `is_cian` = 1;");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);

        $xml = new DomDocument("1.0", "UTF-8");

        // Создание корневого элемента
        $root = $xml->createElement("feed");
        $xml->appendChild($root);

        // Создание элемента версии
        $version = $xml->createElement("feed_version", "12");
        $root->appendChild($version);
        foreach ($houses as $house) {
            // Создание элемента объекта
            $object = $xml->createElement("object");
            $root->appendChild($object);

            // Категория объявления (String)
            $category_obj = $house['category_obj'] == 'Готовый дом' ? "cottageSale" : ($house['category_obj'] == 'Участок' ? "landSale" : '');
            $category = $xml->createElement("Category", $category_obj);
            $object->appendChild($category);

            // 	Внешний идентификатор объявления (String)	Уникальный номер объекта в фиде / CRM-системе.
            $ExternalId = $xml->createElement("ExternalId", "");
            $object->appendChild($ExternalId);

            // 	Текст объявления и описание объекта недвижимости (String)	Объём описания: от 15 до 3 000 символов. Чтобы разбить описание на абзацы, начните текст с новой строки XML-кода.
            $Description = $xml->createElement("Description", $house['description']);
            $object->appendChild($Description);

            // Адрес объекта недвижимости. (String)	Укажите адрес точно как на Яндекс Картах. Редактировать адрес можно в течение 2 дней после публикации. Чтобы отредактировать позже, напишите в поддержку.
            $Address = $xml->createElement("Address", $house['address']);
            $object->appendChild($Address);

            // Phones	Телефон	Можно указать только два номера. PhoneSchema В объявлении номера будут в том же порядке, что и в фиде. CountryCode	Код страны (String) Number	Номер (String)
            $Phones = $xml->createElement("Phones");
            $object->appendChild($Phones);
            $PhoneSchema = $xml->createElement("PhoneSchema");
            $Phones->appendChild($PhoneSchema);
            $CountryCode = $xml->createElement("CountryCode", "+7");
            $PhoneSchema->appendChild($CountryCode);
            $Number = $xml->createElement("Number", $house['phone']);
            $PhoneSchema->appendChild($Number);

            // Кадастровый номер дома (String)	Для загородной недвижимости в поле указывается кадастровый номер земельного участка.
            $BuildingCadastralNumber = $xml->createElement("BuildingCadastralNumber", $house['cadastr_number']);
            $object->appendChild($BuildingCadastralNumber);

            if ($category_obj === "cottageSale") {
                // Общая площадь, м² (Double)	Общая площадь — метраж объекта целиком. Сумма жилой площади и площади кухни не должна быть больше или равна общей.
                $TotalArea = $xml->createElement("TotalArea", $house['sq']);
                $object->appendChild($TotalArea);

                // Количество спален (Int64)
                $BedroomsCount = $xml->createElement("BedroomsCount", $house['K']);
                $object->appendChild($BedroomsCount);

                // 	Расположение санузла: indoors — В доме outdoors — На улице
                $WcLocationType = $xml->createElement("WcLocationType", "indoors");
                $object->appendChild($WcLocationType);

                // Фото планировки
                $LayoutPhoto = $xml->createElement("LayoutPhoto");
                $object->appendChild($LayoutPhoto);
                $FullUrl_Planirovka = $xml->createElement("FullUrl", "http://example.com/flat1.jpg");
                $LayoutPhoto->appendChild($FullUrl_Planirovka);
                $IsDefault_Planirovka = $xml->createElement("IsDefault", "true");
                $LayoutPhoto->appendChild($IsDefault_Planirovka);
            }

            // Фото дома или участка
            $Photos = $xml->createElement("Photos");
            $object->appendChild($Photos);

            $PhotoSchema = $xml->createElement("PhotoSchema");
            $Photos->appendChild($PhotoSchema);
            $FullUrl_Photo = $xml->createElement("FullUrl", "http://example.com/flat1.jpg");
            $PhotoSchema->appendChild($FullUrl_Photo);
            $IsDefault_Photo = $xml->createElement("IsDefault", "true");
            $PhotoSchema->appendChild($IsDefault_Photo);

            if ($category_obj === "cottageSale") {
                // Количество санузлов (Int64)
                $WcsCount = $xml->createElement("WcsCount", $house['S']);
                $object->appendChild($WcsCount);

                // Количетсво этажей
                $Building = $xml->createElement("Building");
                $object->appendChild($Building);
                $FloorsCount = $xml->createElement("FloorsCount", $house['floor']);
                $Building->appendChild($FloorsCount);
            }

            // Участок
            $Land = $xml->createElement("Land");
            $object->appendChild($Land);
            $Area = $xml->createElement("Area", $house['area_land']);
            $Land->appendChild($Area);
            $AreaUnitType = $xml->createElement("AreaUnitType", "sotka");
            $Land->appendChild($AreaUnitType);

            // Условия сделки
            $BargainTerms = $xml->createElement("BargainTerms");
            $object->appendChild($BargainTerms);
            $Price = $xml->createElement("Price", $house['price']);
            $BargainTerms->appendChild($Price);
            $Currency = $xml->createElement("Currency", "rur");
            $BargainTerms->appendChild($Currency);


        }
        $filename = "cian.xml";

        try {
            // Сохранение XML-документа в файл
            $xml->save($filename);
            return $response = "XML-документ успешно Обновлён.";
        } catch (Error $e) {
            return $response = "Ошибка: " . $e->getMessage();
        }
        // // Отображение XML файла
        // header('Content-type: text/xml');
        // readfile($filename);
    }

}

?>