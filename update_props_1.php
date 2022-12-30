<pre>
<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

$IBLOCK_ID_TO = 97; // База документов
$IBLOCK_ID_FROM = 36; // Официальная информация

$IBLOCK_ID = 97;
?>

<?
 $arSelect = Array("ID", "IBLOCK_ID", "NAME", "PREVIEW_TEXT", "PROPERTY_*", "UF_*");
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

//$propertyCode = "IMPORTANT";
 $matches = [];
foreach ($arrEl as $key => $el) {
    /* if ($el["fields"] == "") {
    } */
    echo "ID: " . $el["fields"]["ID"] . PHP_EOL;
    echo "NAME: " . $el["fields"]["NAME"] . PHP_EOL;
    // var_dump($el["props"]["ADDRESSEE"]);
    
    $addressee = $el["props"]["ADDRESSEE"]["~VALUE"]; //&quot
    $id = $el["fields"]["ID"]; 
    
    // preg_match('/\\\/i', $addressee, $matches);
    preg_match('/\"avatar\":\"\}/i', $addressee, $matches);
    $found = count($matches);
    //var_dump($found);
    
    $found = 1;
    if ($found > 0) {
        var_dump($el["props"]["ADDRESSEE"]);
        // $addressee_new = str_replace("\\", "", $addressee);
        /*
        $addressee_new = str_replace("\"\\[", '\'\\[', $addressee);
        $addressee_new = trim($addressee_new);
        */
        
        // $pattern = '/\"\s*\"/';
        /*$pattern_1 = '/^\"/';
        $pattern_2 = '/\]\"/';
        */
        $pattern = '/\"avatar\":\"\}/';
        $addressee_new = preg_replace($pattern, '"avatar":""}', $addressee);
        /*
        $addressee_new_1 = preg_replace($pattern_1, '', $addressee);
        $addressee_new = preg_replace($pattern_2, ']', $addressee_new_1); */


        echo "addressee_new_2: " . $addressee_new . PHP_EOL; 
        
        //$resUpd = updValueProperty($id, "ADDRESSEE" ,$addressee_new);
        
        //$ResUpdEl = array("run"=> "update", "status" => $resUpd); 
        var_dump($resUpd);


    }

}  



function updValueField($id, $name, $previewText){
    $arr = array($id, $name, $previewText);
    var_dump($arr);
    
    $el = new CIBlockElement;
    $arLoadProductArray = Array(
        "NAME" => $name,    
        "PREVIEW_TEXT"   => $previewText,
    );
    $res = $el->Update($id, $arLoadProductArray);
    return $res;

}


function updValueProperty($elementId, $propertyCode, $val) {
    $val = trim($val);
    //var_dump([ $elementId, $propertyCode, $val ]);
    $arCheckbox = array("VALUE" => $val, "DESCRIPTION" => "");
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCode, $arCheckbox);
    return $res;
}

?>


</pre>
