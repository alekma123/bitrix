<pre>
<?php
$IBLOCK_ID_TO = 97; // База документов
// $IBLOCK_ID_FROM = 36; // Официальная информация
$IBLOCK_ID_FROM = 97; // Официальная информация

 $arSelect = Array("ID", "IBLOCK_ID","NAME","DATE_CREATE" ,"IMPORTANT_NEWS");
 $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID_FROM), "PROPERTY_IMPORTANT_NEWS"=>false);

 $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

 while($ob = $res->GetNextElement()){
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();
    print_r($arFields["NAME"]); 
    echo PHP_EOL . "value: ". $arProps["IMPORTANT_NEWS"]["VALUE"] . PHP_EOL;
    $resSearch = searchByName($arFields["NAME"]);
    echo ("cout: " . count($resSearch) . PHP_EOL);
    
    // удалить лишнее
    if(count($resSearch) > 1) {
        //$resDel = CIBlockElement::Delete($arFields["ID"]);
        echo "resDel: ". $resDel .PHP_EOL;
    }
    echo PHP_EOL;
 }


function searchByName($name){
    global $IBLOCK_ID_FROM;
    $arSelect = Array("ID", "IBLOCK_ID","NAME","DATE_CREATE" ,"IMPORTANT_NEWS");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID_FROM), "NAME" => $name);
   
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
    $list = array();
    $index = 0;
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
        $list[]["name"] = $arFields["NAME"];
        $list[]["imp"] = $arProps["IMPORTANT_NEWS"];
        $index +=1;
    }  

    return $list;
}

?>
</pre>
