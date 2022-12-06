<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<pre>

<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

require_once "Classes/PHPExcel.php";
$tmpfname = "Шахматка2.xlsx";
$GLOBALS["IBLOCK_ID"] = 151;

$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
$excelObj = $excelReader->load($tmpfname);
$worksheet = $excelObj->getSheet(2);
$lastRow = $worksheet->getHighestRow() - 6;

echo "count: " . $lastRow . PHP_EOL;
$el = array();
$index = 0;
for ($row = 10; $row <= $lastRow; $row++) {

    $el["floor"] = $worksheet->getCell('B'.$row)->getValue();
    $el["number_kv_arch"] = $worksheet->getCell('C'.$row)->getValue();
    $el["home"] = $worksheet->getCell('D'.$row)->getValue();
    $el["number_kv"] = $worksheet->getCell('E'.$row)->getValue();
    $el["porch"] = $worksheet->getCell('F'.$row)->getValue();
    $el["apartment_type"] = $worksheet->getCell('G'.$row)->getValue();
    $el["live_square"] = $worksheet->getCell('H'.$row)->getValue();
    $el["common_square_with_balcony"] = $worksheet->getCell('I'.$row)->getValue();
    $el["square_without_balcony"] = $el["common_square_with_balcony"] - $el["square_balcony_loggia"];//$worksheet->getCell('L'.$row)->getValue();
    $el["balcony_loggia"] = $worksheet->getCell('K'.$row)->getValue();
    $el["square_balcony_loggia"] = $worksheet->getCell('L'.$row)->getValue();
    $el["square_reduced"] = getSquare_reduced($el);//$worksheet->getCell('M'.$row)->getValue();
    
    $el["price_m_square"] = $worksheet->getCell('N'.$row)->getValue();
    $el["price_total"] = $worksheet->getCell('O'.$row)->getValue();
    $el["pantry_commerce"] = "no";
    
    $el["name"] = getName($el["home"], $el["floor"], $el["number_kv"]);
    
    //$res = addElement($el);
    //var_dump($res);
    
    var_dump($el);
    
    // if ($row == 3) break;
    $index +=1;
}

echo "count: $index" .PHP_EOL;
// getEl();

function getSquare_reduced($el){
    $res = 0;
    if (intval($el["square_balcony_loggia"]) == 0) {
        $res = $el["square_without_balcony"];
    } else {
        $res = $el["square_balcony_loggia"]*0.3 + $el["square_without_balcony"];
    }
    return $res;
}


function getName($home, $floor, $apartment){
    $name = "дом". $home .'_этаж'. $floor .'_кв'. $apartment;
    return $name;
}
function getEntrance($val){
    $res = null;
    $val = intval($val);
    switch ($val) {
        case 1:
            $res = "1910";
            break;
        case 2:
            $res = "1911";
            break;
        case 3:
            $res = "1912";
            break;
        
        default:
            $res = null;
            break;
    }
    return $res;
}

function getApartment_type($val){
    $res = null;
    switch ($val) {
        case '1к':
            $res = "1900";
            break;
        case '1кС':
            $res = "1901";
            break;
        case '2кЕ':
            $res = "1902";
            break;
        case '3кЕ':
            $res = "1903";
            break;
        case '4кЕ':
            $res = "1904";
            break;
        
        default:
            $res = null;
            break;
    }
    return $res;
}

function getBalcony_loggia($val){
    $res = null;
    switch ($val) {
        case 'нет':
            $res = "1915";
            break;
        case 'б':
            $res = "1908";
            break;
        case 'л':
            $res = "1909";
            break;
        
        default:
            $res = null;
            break;
    }
    return $res;
}

function getPantry_commerce($val){
    $res = null;
    switch ($val) {
        case 'pantry':
            $res = "1913";
            break;
        case 'commerce':
            $res = "1914";
            break;
        case 'no':
            $res = "1916";
            break;
        
        default:
            $res = null;
            break;
    }
    return $res;
} 



function addElement($el){
    $CIBlockElement = new CIBlockElement;
    $arFields = Array(
        "NAME" => $el["name"],
        "IBLOCK_SECTION_ID" => "807",
        "PROPERTY_VALUES" => array(
            "FLOOR"=>$el["floor"], /*этаж */
            "APARTMENT" => $el["number_kv"], /*номер квартиры */ 
            "APARTMENT_ARCH_SOL"=>$el["number_kv_arch"], /**Номер квартиры в АР */
            "SECTION"=>$el["home"], /*дом */
            "ENTRANCE"=> getEntrance($el["porch"]), /* подъезд */
            "APARTMENT_TYPE"=>getApartment_type($el["apartment_type"]), /*тип квартиры */
            "LIVING_SQUARE"=> $el["live_square"], /*жилая площадь */
            "COMMON_SQUARE_WITH_BALCONY" => $el["common_square_with_balcony"], /*Общая площадь с балконом:*/
            "BALCONY_LOGGIA"=> getBalcony_loggia($el["balcony_loggia"]),/*Балкон/лоджия:*/
            "S_BALCONY_LOGGIA" => $el["square_balcony_loggia"], /*Площадь балкона/лоджии: */
            "WITHOUT_S_BALCONY" =>  $el["square_without_balcony"], /*Площадь без балкона: */
            "REDUCED_SQUARE" => $el["square_reduced"],  /*Приведенная площадь: */
            "PRICE_S_METER" => $el["price_m_square"], /*Цена за кв.м.: */
            "PRICE" => $el["price_total"],/*Стоимость*/
            "PANTRY_COMMERCE" => getPantry_commerce($el["pantry_commerce"]),/*Кладовая/коммерция:*/
        ),

        "IBLOCK_ID" => $GLOBALS["IBLOCK_ID"],
    );

    if($EL_ID = $CIBlockElement->Add($arFields))
    $res = "New ID: ".$EL_ID;
    else
    $res = "Error: ".$CIBlockElement->LAST_ERROR;
    return $res;
}




function getEl(){
    $IBLOCK_ID = 151;
    $arSelect = Array("ID", "IBLOCK_ID", "NAME","PROPERTY_*");//IBLOCK_ID и ID обязательно должны быть 
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID));
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
    $arProps = [];
    $arFields = [];
    $el = [];
    $index = 0;
    while($ob = $res->GetNextElement()){ 
        $arFields = $ob->GetFields();  
        //print_r($arFields);
        $arProps = $ob->GetProperties();
        //print_r($arProps);
        $el[$index]["fields"] = $arFields; 
        $el[$index]["props"] = $arProps; 
        $index +=1;
    }

    var_dump($el[0]);
}
?>
</pre>



</html>