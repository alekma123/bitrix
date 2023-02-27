<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

$IBLOCK_ID_TO = 97; // База документов
$IBLOCK_ID_FROM = 36; // Официальная информация
?>

<? $importedFields= importFields($IBLOCK_ID_FROM, $IBLOCK_ID_TO);?>


<h3>Поля</h3>
<pre><?var_dump($importedFields);?></pre> 


<?
// Перенесит поля из инфоблока 36 -> 102
function importFields($IBLOCK_ID_FROM, $IBLOCK_ID_TO) {
    // Взять список элементов из $IBLOCK_ID_FROM: взять поля
    // $listEl_fileds = getFields($IBLOCK_ID_FROM);
    //$listEl_fileds = getFields_last($IBLOCK_ID_FROM);
    // Создать список элементов в $IBLOCK_ID_TO: заполнить поля
    // $resAdd = addElementsFields($IBLOCK_ID_TO, $listEl_fileds);
    return $resAdd;
}



// --- WORKS WITH FIELDS --- 
function getFields_last($IBLOCK_ID){
    $ID = array("280731", "280692");

    $arSelect = Array("ID", "IBLOCK_ID", "*");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID), "ID" => $ID );
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

    while($ob = $res->GetNextElement()){ 
        $arFields[] = $ob->GetFields();  
    }
    return $arFields;
}
// -------------------------------------------------

function getFields($IBLOCK_ID){
    $arSelect = Array("ID", "IBLOCK_ID", "*");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID));
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

    while($ob = $res->GetNextElement()){ 
        $arFields[] = $ob->GetFields();  
    }
    return $arFields;
}

// --- ADD ELEMENTS----
function addElementsFields($IBLOCK_ID, $listEl) {
    foreach ($listEl as $key => $el) {
        $resAdd[] = addElement($IBLOCK_ID, $el);
    }
    return $resAdd;
}

function addElement($IBLOCK_ID, $el){
    $CIBlockElement = new CIBlockElement;
    $arFields = Array(
        "NAME" => $el["~NAME"],
        "IBLOCK_ID" => $IBLOCK_ID,
        "ACTIVE" => $el["ACTIVE"],
        "MODIFIED_BY" => $el["MODIFIED_BY"],
        "TIMESTAMP_X" => $el["TIMESTAMP_X"],
        "TIMESTAMP_X_UNIX" => $el["TIMESTAMP_X_UNIX"],
        "MODIFIED_BY" => $el["MODIFIED_BY"],
        "DATE_CREATE" => $el["DATE_CREATE"],
        "DATE_CREATE_UNIX" => $el["DATE_CREATE_UNIX"],
        "CREATED_BY" => $el["CREATED_BY"],
        "ACTIVE" => $el["ACTIVE"],
        "ACTIVE_FROM" => $el["ACTIVE_FROM"],
        "ACTIVE_TO" => $el["ACTIVE_TO"],
        "DATE_ACTIVE_FROM" => $el["DATE_ACTIVE_FROM"],
        "DATE_ACTIVE_TO" => $el["DATE_ACTIVE_TO"],
        "PREVIEW_PICTURE" => $el["PREVIEW_PICTURE"],
        "PREVIEW_TEXT" => $el["PREVIEW_TEXT"],
        "PREVIEW_TEXT_TYPE" => $el["PREVIEW_TEXT_TYPE"],
        //"DETAIL_PICTURE" => $el["DETAIL_PICTURE"],
        "DETAIL_TEXT" => $el["DETAIL_TEXT"],
        "DETAIL_TEXT_TYPE" => $el["DETAIL_TEXT_TYPE"],
        "SEARCHABLE_CONTENT" => $el["SEARCHABLE_CONTENT"],
        "TAGS" =>$el["TAGS"]

    );

    if($EL_ID = $CIBlockElement->Add($arFields))
    $res = "New ID: ".$EL_ID;
    else
    $res = "Error: ".$CIBlockElement->LAST_ERROR;

    return $res;
}

// --- GET ELEMENTS ---
function getElements($IBLOCK_ID) {
    $arSelect = Array("ID", "IBLOCK_ID","PROPERTY_*");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID));
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

?>