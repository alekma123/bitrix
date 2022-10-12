<html lang="ru">
<head>
<style>
.el {
    border: 1px solid black;
    margin: 10px 0;
}
</style>
</head>


<pre>
    <?
// Шаблон компонента 
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule('iblock')){
    // раздел перцы
    // $SECTION_ID = "228";
    // раздел капуста
    $SECTION_ID = "194";

    $IBLOCK_ID = 9;
    $arFilter = Array(
        "IBLOCK_ID"=>IntVal($IBLOCK_ID), 
        "ACTIVE_DATE"=>"Y", 
        "ACTIVE"=>"Y",
        "SECTION_ID" => $SECTION_ID 
    );
    $arSelect = Array("ID", "IBLOCK_ID", "NAME","PROPERTY_*");//IBLOCK_ID и ID обязательно должны быть указаны, см. описание arSelectFields выше
    // $arNavStartParams = Array("nPageSize"=>1);
    $arNavStartParams = Array();
    $res = \CIBlockElement::GetList(Array(), $arFilter, false, $arNavStartParams, $arSelect);
    
    $semena = Array();
    $index = 0;
    while($ob = $res->GetNextElement()){ 
        $arFields = $ob->GetFields();
        
        unset($arFields["~NAME"]);
        unset($arFields["~ID"]);
        unset($arFields["~IBLOCK_ID"]);
        unset($arFields["IBLOCK_ID"]);
        
        $semena[$index]["FIELDS"] = $arFields;
        
        $arProps = $ob->GetProperties(false, Array("CODE"=> "VES_PLODA"));
        $semena[$index]["PROPS"] = $arProps; 
        // $arProps = $ob->GetProperties(false, Array("CODE"=> Array("VES_PLODA","UPAKOVKA")));
        $index+=1;
    }
}


function el_formatting($el){
    $new_el = array();
    $new_el["FIELDS"] = $el["FIELDS"]; 

    $source = $el["PROPS"]["VES_PLODA"];

    $new_el["PROPS"]["VES_PLODA"]["ID"] = $source["ID"]; 
    $new_el["PROPS"]["VES_PLODA"]["NAME"] = $source["NAME"]; 
    $new_el["PROPS"]["VES_PLODA"]["ACTIVE"] = $source["ACTIVE"]; 
    $new_el["PROPS"]["VES_PLODA"]["PROPERTY_TYPE"] = $source["PROPERTY_TYPE"]; 
    $new_el["PROPS"]["VES_PLODA"]["PROPERTY_VALUE_ID"] = $source["PROPERTY_VALUE_ID"]; 
    $new_el["PROPS"]["VES_PLODA"]["VALUE"] = $source["VALUE"]; 
    $new_el["PROPS"]["VES_PLODA"]["VALUE_XML_ID"] = $source["VALUE_XML_ID"]; 
    // var_dump($new_el);
    return $new_el;
}

function renderEl($el){
    $fields = $el["FIELDS"];
    echo "<div class='el'>";
    echo "FIELDS" . PHP_EOL;
    foreach ($fields as $key => $val) {
        echo "$key: $val" .PHP_EOL;
    }
    $props = $el["PROPS"]["VES_PLODA"];
    echo PHP_EOL . "PROPS: VES_PLODA" . PHP_EOL;
    foreach ($props as $key => $val) {
        if ($key == "VALUE") echo "$key: $val" . "(" .gettype($val) .")" . PHP_EOL;
        else echo "$key: $val" .PHP_EOL;
    }
    echo "</div>";
}


echo PHP_EOL . "Кол-во капусты: ". count($semena) . PHP_EOL. PHP_EOL;


foreach ($semena as $key => $value) {
    // $new_el = el_formatting($value);
    renderEl($value);    
}

// $new_el = el_formatting($semena[0]);
// renderEl($new_el);


?>

</pre>
<body>
