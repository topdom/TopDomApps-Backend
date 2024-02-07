<?php
class PatchController
{
    private $dbh;
    function __construct()
    {
        include("./classes/databaseClass.php");
        $database = new Database();
        $this->dbh = $database->conncect();
    }

    public function createCianOrDomclickXML($data)
    {
        if (isset($data['is_cian']) && !empty($data['is_cian'])) {
            $filename = "cian.xml";
            $projects = $this->dbh->prepare("SELECT `id`,`floor`,`number`,`sq`,`category_obj`,`address`,`phone`,`cadastr_number`,`K`,`S`,`G`,`area_land`,`price`,`photo`,`photo_planirovka`,`description`  FROM `projects` WHERE `is_cian` = 1;");
        } else if (isset($data['is_domclick']) && !empty($data['is_domclick'])) {
            $filename = "domclick.xml";
            $projects = $this->dbh->prepare("SELECT `id`,`floor`,`number`,`sq`,`category_obj`,`address`,`phone`,`cadastr_number`,`K`,`S`,`G`,`area_land`,`price`,`photo`,`photo_planirovka`,`description`  FROM `projects` WHERE `is_domclick` = 1;");
        }
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

    public function createAvitoXML()
    {
        $projects = $this->dbh->prepare("SELECT `id`, `phone`,`description`, `image`,`address`,`category_obj`,`price`,`rooms`,`floor`,`wallsType`,`sq`,`area_land`,`landStatus`,`renovation` FROM `projects` WHERE `is_avito` = 1;");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);

        $xml = new DomDocument("1.0", "UTF-8");

        // Создание корневого элемента
        $root = $xml->createElement('Ads');
        $root->setAttribute('formatVersion', '3');
        $root->setAttribute('target', 'Avito.ru');
        $xml->appendChild($root);

        foreach ($houses as $house) {
            $Ad = $xml->createElement('Ad');
            $root->appendChild($Ad);
            $Id = $xml->createElement('Id', $house['id']);
            $Ad->appendChild($Id);
            $category_type = $house['category_obj'] == 'Готовый дом' ? "Дома, дачи, коттеджи" : ($house['category_obj'] == 'Участок' ? "Земельные участки" : '');
            $Category = $xml->createElement('Category', $category_type);
            $Ad->appendChild($Category);
            $OperationType = $xml->createElement('OperationType', 'Продам');
            $Ad->appendChild($OperationType);
            $category_obj = $house['category_obj'] == 'Готовый дом' ? "Коттедж" : ($house['category_obj'] == 'Участок' ? "Поселений (ИЖС)" : '');
            $ObjectType = $xml->createElement('ObjectType', $category_obj);
            $Ad->appendChild($ObjectType);
            $Address = $xml->createElement('Address', $house['address']);
            $Ad->appendChild($Address);
            $LandArea = $xml->createElement('LandArea', $house['area_land']);
            $Ad->appendChild($LandArea);
            $ContactPhone = $xml->createElement('ContactPhone', "+7" . $house['phone']);
            $Ad->appendChild($ContactPhone);
            $Description = $xml->createElement('Description', $house['description']);
            $Ad->appendChild($Description);
            $Price = $xml->createElement('Price', $house['price']);
            $Ad->appendChild($Price);
            $PropertyRights = $xml->createElement('PropertyRights', "Собственник");
            $Ad->appendChild($PropertyRights);
            $LandStatus = $xml->createElement('LandStatus', $house['landStatus']);
            $Ad->appendChild($LandStatus);

            if ($category_obj == "Коттедж") {
                $Square = $xml->createElement('Square', $house['sq']);
                $Ad->appendChild($Square);
                $Floors = $xml->createElement('Floors', $house['floor']);
                $Ad->appendChild($Floors);
                $WallsType = $xml->createElement('WallsType', $house['wallsType']);
                $Ad->appendChild($WallsType);
                $Rooms = $xml->createElement('Rooms', $house['rooms']);
                $Ad->appendChild($Rooms);
                $Renovation = $xml->createElement('Renovation', $house['renovation']);
                $Ad->appendChild($Renovation);
            }

        }
        $filename = "avito.xml";
        try {
            // Сохранение XML-документа в файл
            $xml->save($filename);
            return $response = "XML-документ успешно Обновлён.";
        } catch (Error $e) {
            return $response = "Ошибка: " . $e->getMessage();
        }
    }

    public function createDirectXML()
    {
        $projects = $this->dbh->prepare("SELECT * FROM `projects` WHERE `is_direct` = 1;");
        $projects->execute();
        $houses = $projects->fetchAll(PDO::FETCH_ASSOC);

        $xml = new DomDocument("1.0", "UTF-8");

        // Создание корневого элемента
        $realty_feed = $xml->createElement('realty-feed');
        $realty_feed->setAttribute('xmlns', 'http://webmaster.yandex.ru/schemas/feed/realty/2010-06');
        $xml->appendChild($realty_feed);

        $generation_date = $xml->createElement('generation-date', date('Y-m-d\TH:i:sP'));
        $realty_feed->appendChild($generation_date);

        foreach ($houses as $house) {
            $offer = $xml->createElement('offer');
            $offer->setAttribute('internal-id', $house['id']);
            $realty_feed->appendChild($offer);

            $type = $xml->createElement('type', "продажа");
            $offer->appendChild($type);
            $property_type = $xml->createElement('property-type', "продажа");
            $offer->appendChild($property_type);

            $category_type = $house['category_obj'] == 'Готовый дом' ? "коттедж" : ($house['category_obj'] == 'Участок' ? "участок" : '');
            $category = $xml->createElement('category', $category_type);
            $offer->appendChild($category);

            $cadastral_number = $xml->createElement('cadastral-number', $house['cadastr_number']);
            $offer->appendChild($cadastral_number);
            $creation_date = $xml->createElement('creation-date', date('Y-m-d\TH:i:sP'));
            $offer->appendChild($creation_date);

            $location = $xml->createElement('location');
            $offer->appendChild($location);
            $country = $xml->createElement('country', "Россия");
            $location->appendChild($country);
            $address = $xml->createElement('address', $house['address']);
            $location->appendChild($address);

            $sales_agent = $xml->createElement('sales-agent');
            $offer->appendChild($sales_agent);
            $phone = $xml->createElement('phone', "+7" . $house['phone']);
            $sales_agent->appendChild($phone);
            $category = $xml->createElement('category', "застройщик");
            $sales_agent->appendChild($category);
            $organization = $xml->createElement('organization', "ТОПДОМ");
            $sales_agent->appendChild($organization);
            $url = $xml->createElement('url', "топдом.рф");
            $sales_agent->appendChild($url);
            $photo = $xml->createElement('photo', "https://топдом.рф/img/topdomLogo.jpg");
            $sales_agent->appendChild($photo);

            $price = $xml->createElement('price');
            $offer->appendChild($price);
            $value = $xml->createElement('value', $house['price']);
            $price->appendChild($value);
            $currency = $xml->createElement('currency', "RUB");
            $price->appendChild($currency);

            if ($category_type == "коттедж") {
                $area = $xml->createElement('area');
                $offer->appendChild($area);
                $value = $xml->createElement('value', $house['sq']);
                $area->appendChild($value);
                $unit = $xml->createElement('unit', "кв. м");
                $area->appendChild($unit);
                $floors_total = $xml->createElement('floors-total', $house['floor']);
                $offer->appendChild($floors_total);
            }

            $lot_area = $xml->createElement('lot-area');
            $offer->appendChild($lot_area);
            $value = $xml->createElement('value', $house['area_land']);
            $lot_area->appendChild($value);
            $unit = $xml->createElement('unit', "сотка");
            $lot_area->appendChild($unit);

            // Вставить фотки через foreach потом. минимум 4 фото, планировка должна быть первой
            $image = $xml->createElement('image', "https://топдом.рф/img/topdomLogo.jpg");
            $offer->appendChild($image);



            $description = $xml->createElement('description', $house['description']);
            $offer->appendChild($description);
            if ($category_type == "коттедж") {
                $room_furniture = $xml->createElement('room-furniture', "нет");
                $offer->appendChild($room_furniture);
                switch ($house['renovation']) {
                    case 'Требуется':
                        $house['renovation'] = 'требует ремонта';
                        break;
                    case 'Косметический':
                        $house['renovation'] = 'косметический';
                        break;
                    case 'Евро':
                        $house['renovation'] = 'евроремонт';
                        break;
                    case 'Дизайнерский':
                        $house['renovation'] = 'дизайнерский';
                        break;
                    default:
                        $house['renovation'];
                        break;
                }
                // $renovation = $xml->createElement('renovation', $house['renovation']);
                // $offer->appendChild($renovation);
            }

        }
        $filename = "direct.xml";
        try {
            // Сохранение XML-документа в файл
            $xml->save($filename);
            return $response = "XML-документ успешно Обновлён.";
        } catch (Error $e) {
            return $response = "Ошибка: " . $e->getMessage();
        }
    }
    public function createTurboXML($data)
    {
        $company = $this->dbh->prepare("SELECT * FROM `company` WHERE `id` = ?;");
        $company->bindValue(1, $data['id']);
        $company->execute();
        $company = $company->fetch(PDO::FETCH_ASSOC);

        $turbo = $this->dbh->prepare("SELECT *  FROM `yandex_turbo_pages` WHERE `company` = ?;");
        $turbo->bindValue(1, $data['id']);
        $turbo->execute();
        $turbo_elements = $turbo->fetchAll(PDO::FETCH_ASSOC);

        $xml = new DomDocument("1.0", "UTF-8");

        // Создание корневого элемента
        $rss = $xml->createElement('rss');
        $rss->setAttribute('xmlns:yandex', 'http://news.yandex.ru');
        $rss->setAttribute('xmlns:yandex', 'http://search.yahoo.com/mrss/');
        $rss->setAttribute('xmlns:turbo', 'http://turbo.yandex.ru');
        $rss->setAttribute('version', '2.0');
        $xml->appendChild($rss);

        $channel = $xml->createElement('channel');
        $rss->appendChild($channel);

        $title = $xml->createElement('title', $company["title"]);
        $channel->appendChild($title);
        $link = $xml->createElement('link', $company["link"]);
        $channel->appendChild($link);
        $description = $xml->createElement('description', $company["description"]);
        $channel->appendChild($description);
        $language = $xml->createElement('language', 'ru');
        $channel->appendChild($language);
        foreach ($turbo_elements as $turbo_element) {
            $item = $xml->createElement('item');
            $item->setAttribute('turbo', 'true');
            $channel->appendChild($item);

            $extendedHtml = $xml->createElement('turbo:extendedHtml', 'true');
            $item->appendChild($extendedHtml);
            $title = $xml->createElement('title', $turbo_element['title']);
            $item->appendChild($title);
            $link = $xml->createElement('link', $turbo_element['link']);
            $item->appendChild($link);
            $pubDate = $xml->createElement('pubDate', date("D, d M Y H:i:s O"));
            $item->appendChild($pubDate);
            // if( $turbo_element['link']===){

            // }
            $yandex_related = $xml->createElement('yandex:related');
            $item->appendChild($yandex_related);
            $description = $xml->createElement('description', $turbo_element['description']);
            $item->appendChild($description);
            $turbo_content = $xml->createElement('turbo:content', $turbo_element['turbo_content']);
            $item->appendChild($turbo_content);
        }
        $filename = "dg.xml";
        try {
            // Сохранение XML-документа в файл
            $xml->save($filename);
            // Перенос файла на сервер топдома
            // $localFile = $filename;
            // $remoteFile = '/home/admin/web/топдом.рф/public_html/rss/dg.xml';
            // $connection = ssh2_connect('92.255.78.125');
            // ssh2_auth_password($connection, 'root', 'dJS7HYiw');
            // ssh2_scp_send($connection, $localFile, $remoteFile, 0644);
            // return $response = "XML-документ успешно Обновлён на ТопДом.";
            return $turbo_element['turbo_content'];
        } catch (Error $e) {
            return $response = "Ошибка: " . $e->getMessage();
        }
    }
}

//  <![CDATA[ <figure> <img src="https://xn--d1aqebdq.xn--p1ai/rss/img/gazobeton/gazobeton-3.jpg" alt="Строительство домов из газобетона под ключ: проекты и цены" /> </figure> <a href="https://xn--d1aqebdq.xn--p1ai/proekty-domov-iz-gazobetona" class="main-site">Перейти на основной сайт</a> <figure> <img src="https://xn--d1aqebdq.xn--p1ai/rss/img/gazobeton/1.jpg" alt="Проект ТОПДОМ 1.10, 83 м² под ключ" /> <figcaption>Проект ТОПДОМ 1.10, 83 м² под ключ</figcaption> </figure> <a href="https://xn--d1aqebdq.xn--p1ai/stroitelstvo-odnoehtazhnyh-domov-pod-klyuch-proekty-i-ceny/proekt-topdom-1-10-83-kv-m" class="project-site">Подробнее</a> <figure> <img src="https://xn--d1aqebdq.xn--p1ai/rss/img/gazobeton/2.jpg" alt="Проект ТОПДОМ 1.11, 87 м² под ключ" /> <figcaption>Проект ТОПДОМ 1.11, 87 м² под ключ</figcaption> </figure> <a href="https://xn--d1aqebdq.xn--p1ai/stroitelstvo-odnoehtazhnyh-domov-pod-klyuch-proekty-i-ceny/proekt-topdom-1-11-87-kv-m" class="project-site">Подробнее</a> <figure> <img src="https://xn--d1aqebdq.xn--p1ai/rss/img/gazobeton/3.jpg" alt="Проект ТОПДОМ 1.12, 112 м² под ключ" /> <figcaption>Проект ТОПДОМ 1.12, 112 м² под ключ</figcaption> </figure> <a href="https://xn--d1aqebdq.xn--p1ai/stroitelstvo-odnoehtazhnyh-domov-pod-klyuch-proekty-i-ceny/proekt-topdom-1-12-112-kv-m" class="project-site">Подробнее</a> <figure> <img src="https://xn--d1aqebdq.xn--p1ai/rss/img/gazobeton/4.jpg" alt="Проект ТОПДОМ 1.13, 86 м² под ключ" /> <figcaption>Проект ТОПДОМ 1.13, 86 м² под ключ</figcaption> </figure> <a href="https://xn--d1aqebdq.xn--p1ai/stroitelstvo-odnoehtazhnyh-domov-pod-klyuch-proekty-i-ceny/proekt-topdom-1-13-86-kv-m" class="project-site">Подробнее</a> <figure> <img src="https://xn--d1aqebdq.xn--p1ai/rss/img/karkas/img1.jpg" alt="Проект ТОПДОМ 1.50, 177 м² под ключ" /> <figcaption>Проект ТОПДОМ 1.50, 177 м² под ключ</figcaption> </figure> <a href="https://xn--d1aqebdq.xn--p1ai/stroitelstvo-odnoehtazhnyh-domov-pod-klyuch-proekty-i-ceny/proekt-topdom-1-50-177-kv-m" class="project-site">Подробнее</a> <figure> <img src="https://xn--d1aqebdq.xn--p1ai/rss/img/gazobeton/5.jpg" alt="Проект ТОПДОМ 1.14, 88 м² под ключ" /> <figcaption>Проект ТОПДОМ 1.14, 88 м² под ключ</figcaption> </figure> <a href="https://xn--d1aqebdq.xn--p1ai/stroitelstvo-odnoehtazhnyh-domov-pod-klyuch-proekty-i-ceny/proekt-topdom-1-14-88-kv-m" class="project-site">Подробнее</a> ]]> 
?>