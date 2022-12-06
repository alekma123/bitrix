<html lang="ru">
<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock"); ?>

<pre>
<?
// ---- GET LIST ----
function searchElement($type_kv, $IBLOCK_ID){
    $arSelect = Array("ID", "IBLOCK_ID","NAME","DATE_CREATE" ,"PROPERTY_*");
    $arFilter = Array("PROPERTY_APARTMENT_TYPE" => "1900", "PROPERTY_BALCONY_LOGGIA"=>"1909");

    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

    $arrEl = array();
    $index = 0;

    while($ob = $res->GetNextElement()){
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
        
        $arrEl[$index]["fields"] = $arFields; 
        $arrEl[$index]["props"]["WITHOUT_S_BALCONY"] = $arProps["WITHOUT_S_BALCONY"]; 
        $arrEl[$index]["props"]["APARTMENT_TYPE"] = $arProps["APARTMENT_TYPE"]; 
        $arrEl[$index]["props"]["S_BALCONY_LOGGIA"] = $arProps["S_BALCONY_LOGGIA"]; 
        $arrEl[$index]["props"]["REDUCED_SQUARE"] = $arProps["REDUCED_SQUARE"]; 
        $arrEl[$index]["props"]["BALCONY_LOGGIA"] = $arProps["BALCONY_LOGGIA"]; 
        $index +=1;
    }
    return $arrEl;
}

// ---- UPDATE PROPS ----
function updateProps($elementId, $propertyCode, $val) {
    $val = array("VALUE" => $val, "DESCRIPTION" => "");
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCode, $val);
    return $res;
}



$IBLOCK_ID=151; 
$APARTMENT_TYPE = "1к";

$res = searchElement($IBLOCK_ID, $APARTMENT_TYPE);

foreach ($res as $key => $val) {
    $type_kv = $val["props"]["APARTMENT_TYPE"]["VALUE"];
    if ($type_kv == "1к") {
        $s_balcony_loggia = $val["props"]["S_BALCONY_LOGGIA"]["VALUE"];
        $without_s_balcony = $val["props"]["WITHOUT_S_BALCONY"]["VALUE"];
        $reduced_square = $s_balcony_loggia * 0.5 + $without_s_balcony;
        //REDUCED_SQUARE
        echo "ID: ". $val["fields"]["ID"] ." NAME: " . $val["fields"]["NAME"] . " REDUCED_SQUARE: $reduced_square" . PHP_EOL;
        //$resUpd = updateProps($val["fields"]["ID"], "REDUCED_SQUARE", $reduced_square);
        echo "resUpd: " . $resUpd . PHP_EOL;
    }
}



// count($res);
// var_dump($res);

?>
</pre>