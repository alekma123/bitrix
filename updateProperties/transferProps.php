<html lang="ru">


<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

$IBLOCK_ID_TO = 97; // База документов
$IBLOCK_ID_FROM = 36; // Официальная информация

$GLOBALS["IBLOCK_ID_TO"] = $IBLOCK_ID_TO;
$GLOBALS["IBLOCK_ID_FROM"] = $IBLOCK_ID_FROM;
?>


<? $list_from = getElements($IBLOCK_ID_FROM);?>
<? $list_to = getElements($IBLOCK_ID_TO);?>

<h3>Свойства</h3>
<div style="display:flex;">
    <div>
        <h5>list from (официальная информ)</h5>
        <pre><?var_dump($list_from[0]["fields"]["ID"])?></pre>
        <pre><?var_dump($list_from[0]["fields"]["NAME"])?></pre>
    </div>
    <div>
        <?$list_to_1 = searchElement($list_from[0]['fields']['NAME'], $list_from[0]['fields']['DATE_CREATE'], $GLOBALS["IBLOCK_ID_TO"]);?>

        <h5>list to (база документов)</h5>
        <pre><?var_dump($list_to_1[0]["fields"]["ID"])?></pre>
        <pre><?var_dump($list_to_1[0]["fields"]["NAME"])?></pre>
    </div>
</div>

<h3>Логирование: перенос свойства </h3>
<pre>
    <?// print_r(importProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, "NEWS_FILE_PDF", "DOKUMENT"))?>
    <?//print_r(importProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, "USERS_LOOKED", "USERS_LOOKED"))?>
    <?print_r(importProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, "DOC_TYPE", "DOC_TYPE"))?>
    <?//print_r(importProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, "ADDRESSEE", "ADDRESSEE"))?>
    <?//print_r(importProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, "IMPORTANT_NEWS", "IMPORTANT_NEWS"))?>
    <? //updCheckboxes($list_to, "AKTUALNOST", "-")?>
</pre>




<!-- Опубликовать в новостях. Для всех элементов. -->
<?php
function updCheckboxes($listTo, $propertyCode, $val){
    foreach ($listTo as $key => $el) {
        $resUpd = updValueChecbox($el["fields"]["ID"], $propertyCode, $val);
        echo "resUpd:" .  var_dump($resUpd) . PHP_EOL;
    }
}
?>


<div style="display:flex;">
    <div>
        <h5>list from (официальная информ)</h5>
        <pre><?var_dump(getPropsByCode("DOC_TYPE", $list_from[0]))?></pre>
    </div>
    <div>
        <h5>list to (база документов)</h5>
        <pre><?var_dump(getPropsByCode("DOC_TYPE", $list_to[0]))?></pre>
    </div>
</div>



<?

function getEnumIdDocType($XML_ID){
    $enum_id = null;
    switch ($XML_ID) {
        case 'CHANGES_1C':
            $enum_id = 26902;
            break;
        case 'STOCK':
            $enum_id = 26903;
            break;
        case 'PROVIDER_NEWS':
            $enum_id = 26904;
            break;
        case 'EDUCATION':
            $enum_id = 26905;
            break;
        case 'PREMIUM':
            $enum_id = 26906;
            break;
        case 'ORDER':
            $enum_id = 26907;
            break;
        case 'INSTRUCTION':
            $enum_id = 26908;
            break;
        case 'NEWS':
            $enum_id = 26909;
            break;
        
    }
    return $enum_id;
}
?>


<?
// Перенести свойства из инфоблока 36 -> 102
function importProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, $propertyCodeFrom, $propertyCodeTo) {
    $listElFrom = getElements($IBLOCK_ID_FROM);
    $listElTo = getElements($IBLOCK_ID_TO);
    $index = 0;
    $resAdd = array();

    foreach ($listElFrom as $key => $el_from) {
        // свойства из инфоблока "Офиц. информация"
        $prop_from = getPropsByCode($propertyCodeFrom, $el_from);
        
        $listElTo = searchElement($el_from['fields']['NAME'], $el_from['fields']['DATE_CREATE'], $GLOBALS["IBLOCK_ID_TO"]);
        $el_to = $listElTo[0];
        $prop_to = getPropsByCode($propertyCodeTo, $el_to);
        $elementId = $el_to['fields']['ID'];
        
        switch ($propertyCodeFrom) {
            case 'NEWS_FILE_PDF':
                if ($prop_to["VALUE"] == false) {
                    $files = getFiles($elementId, $prop_from);
                    $resAdd[][$elementId] = addValueFiles($elementId, $propertyCodeTo, $files);
                }
                break;
            case 'USERS_LOOKED':
                if ($prop_to["VALUE"] == false) {
                    $views = $prop_from["VALUE"];
                    $resAdd[][$elementId] = addValueViews($elementId, $propertyCodeTo, $views);
                }
                break;    
            case 'DOC_TYPE':
                //if ($prop_to["VALUE"] == false) {
                    $docType = getEnumIdDocType($prop_from["VALUE_XML_ID"]);
                    $resAdd[][$elementId] = addValueDocType($elementId, $propertyCodeTo, $docType);
                //}
                break;
                
            case 'IMPORTANT_NEWS':
                    $val = $prop_to["VALUE"];
                    echo "elementId: " . $elementId ." , imp: " . empty($val)  . PHP_EOL;
                    if (empty($val)) { 
                        $resAdd[][$elementId] = updValueImp($elementId, $propertyCodeTo, 'N');
                    }
                    else {
                        //$resAdd[][$elementId] = updValueImp($elementId, $propertyCodeTo, $val);
                    }
                break;    
            case 'ADDRESSEE':
                if ($prop_to["VALUE"] == "") {
                    $val = $prop_from["VALUE"];
                    $resAdd[][$elementId] = updValueAddressee($elementId, $propertyCodeTo, $val);
                }
                break;    
            
            default:
                # code...
                break;
        } 
        $index +=1;
        // Рассмотрим для начала пример на первом элементе списка инфоблока
        //if ($index == 1) break;
    }

    return $resAdd;
}


// --- GET ELEMENTS ---
function getElements($IBLOCK_ID, $NAME = NULL, $DATE_CREATE = NULL) {
    $arSelect = Array("ID", "IBLOCK_ID","NAME","DATE_CREATE" ,"PROPERTY_*");
    if ($NAME == NULL) {
        $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID));        
    } else {
        // $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID), "NAME"=>$NAME, "?DATE_CREATE"=>$DATE_CREATE);
        $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID), "NAME"=>$NAME);
    }

    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

    $arrEl = array();
    $index = 0;

    while($ob = $res->GetNextElement()){
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
        
        $arrEl[$index]["fields"] = $arFields; 
        $arrEl[$index]["props"] = $arProps; 
        $index +=1;
    }
    return $arrEl;
}

// ВЗЯТЬ СВОЙСТВО ПО СИМВОЛЬНОМУ КОДУ
function getPropsByCode($code, $el) {
    return $el["props"][$code];
}

/* ПОИСК СООТВЕТСТВУЮЩЕГО ЭЛЕМЕНТА В БАЗЕ ДОКУМЕНТОВ  */
function searchElement($name, $dateCreated, $IBLOCK_ID){
    // вызов функции getElements с параметрами поиска NAME, dateCreated
    $elements = getElements($IBLOCK_ID, $name, $dateCreated);
    return $elements;
}



// ----- РАБОТА С ФАЙЛАМИ --------
/**
 * @param $elementId - id элемента, $prop -само свойство источника 
 */
function getFiles($elementId, $prop)
{
    $arrFilesPath = array();

    $filesID = $prop["VALUE"];
    foreach ($filesID as $key => $id) {
        $filePath = CFile::GetPath($id);
        $arrFilesPath[] = $filePath;
    }

    return $arrFilesPath;
}

/**
 * @param $files - array of path files
 */
function addValueFiles($elementId, $propertyCodeTo, $files) {
    $arFile = array();
    foreach ($files as $key => $file) {
        $arFile[] = array("VALUE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$file),"DESCRIPTION"=>"");
    }
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCodeTo, $arFile);
    return $res;
}


// ----- РАБОТА С ПРОСМОТРАМИ --------
/**
 * @param $elementId - id элемента, $prop -само свойство источника  
 */
function addValueViews($elementId, $propertyCodeTo, $views){
    $arViews = array();
    
    foreach ($views as $key => $view) {
        $arViews[] = array("VALUE" => $view, "DESCRIPTION" => "");
    }
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCodeTo, $arViews);
    return $res;
}

// ------ РАБОТА С ТЕМАТИКОЙ ---------------
// в единсвенном числе
function addValueDocType($elementId, $propertyCodeTo, $docType_enum_id) {
    //$IBLOCK_ID = 98; // инфоблок типы документов
    //$type = getElements($IBLOCK_ID, $docType);
    //$typeId = $type[0]['fields']['ID'];
    $docType = intval($docType_enum_id);
    var_dump(array($elementId, $propertyCodeTo, $docType));
    $arDocType= array("VALUE_ENUM_ID" => $docType, "DESCRIPTION" => ""); 
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCodeTo, $arDocType);
    var_dump($res);
    return $res;

}

// ------ РАБОТА С ЧЕКБОКСОМ ---------------
function updValueChecbox($elementId, $propertyCodeTo, $val) {
    var_dump(array($elementId, $propertyCodeTo, $val));
    $arCheckbox = array("VALUE" => $val, "DESCRIPTION" => "");
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCodeTo, $arCheckbox);
    return $res;
}


function updValueImp($elementId, $propertyCodeTo, $val) {
    $arCheckbox = array("VALUE" => $val, "DESCRIPTION" => "");
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCodeTo, $arCheckbox);
    return $res;
}

function updValueAddressee($elementId, $propertyCodeTo, $val){
    $addresse = array("VALUE" => $val, "DESCRIPTION" => "");
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCodeTo, $addresse);
    return $res;
}

?>

