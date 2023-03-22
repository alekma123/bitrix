<style>
    .height {
        display:flex;
        height:200px;
    }
</style>

<pre>
<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

$IBLOCK_ID_TO = 200; // Основной каталог товаров new.mirmebely.ru
$IBLOCK_ID_FROM = 2; // Основной каталог товаров
?>

<? 
   $GLOBALS["err_id"] = [];
    $propertyCode_FROM = "TABLE_TYPE";
    $propertyCode_TO = "TABLE_TYPE";

    // $listOldEl = getProperties($IBLOCK_ID_TO, 'old', $propertyCode_TO); 
    // $listNewEl = getProperties($IBLOCK_ID_FROM, 'new', $propertyCode_FROM);     
   
   $listOldEl = getFields($IBLOCK_ID_TO, 'old'); 
   $listNewEl = getFields($IBLOCK_ID_FROM, 'new');     

?>
<h4><?=$propertyCode?></h4>

<?
    $countEl_old = count($listOldEl); //7684
    $countEl_new = count($listNewEl); //7933
    echo "<h4>Кол-во эл-тов в ИБ 200: $countEl_old</h4>";
    echo "<h4>Кол-во эл-тов в ИБ 2: $countEl_new</h4>";
    
    // updateFieldsAndProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, $propertyCode_TO, $propertyCode_FROM, $listOldEl, $listNewEl);
    // updateFields($IBLOCK_ID_FROM, $IBLOCK_ID_TO, $listOldEl, $listNewEl);
?>

</pre>

<?

    function updateFields($IBLOCK_ID_FROM, $IBLOCK_ID_TO, $listOldEl, $listNewEl){
        $log_msg = [];
        foreach ($listOldEl as $key => $old_el) {
            $EXTERNAL_ID = $old_el["EXTERNAL_ID"];
            $new_el = $listNewEl[$EXTERNAL_ID];
    
            if ($new_el == '') {
                continue; 
                } 
            else {
                
                // if ( $new_el["SORT"] == '' && $old_el["SORT"] == '' ) {
                //     continue;
                // } 
                    outputCompare($new_el, $old_el);
                    
                    $res = updateFieldSort($old_el, $new_el); 
                    var_dump($res);               
                }
                
        }
        //ошибки
        echo PHP_EOL . "Ошибки: " . PHP_EOL;
        print_r($GLOBALS["err_id"]);    
    }


    function updateFieldsAndProps($IBLOCK_ID_FROM, $IBLOCK_ID_TO, $propertyCode_TO, $propertyCode_FROM, $listOldEl, $listNewEl)  {
    $index = 0;
    
    foreach ($listOldEl as $key => $old_el) {
        $EXTERNAL_ID = $old_el["EXTERNAL_ID"];
        $new_el = $listNewEl[$EXTERNAL_ID];
        $newVal = $new_el["props"]["VALUE"];

        if ($new_el == '') { continue; }
        if ( $newVal == '' && $old_el["props"]["VALUE"] == '' ) {
            continue;
        } 
       
       outputCompare($new_el, $old_el);
       
       if ($new_el["props"]["PROPERTY_TYPE"] == 'L'){
            if ( is_array($newVal) ){
                $enumId = [];
                foreach ($newVal as $key => $val) {
                    $enumId[] = getEnumID($val, $IBLOCK_ID_TO, $propertyCode_TO);
                }
            } else {
                $enumId = getEnumID($newVal, $IBLOCK_ID_TO, $propertyCode_TO);
            }
            //$resUpd = updValueProperty($old_el["ID"], $propertyCode_TO, $enumId);
        }

        if ($new_el["props"]["PROPERTY_TYPE"] == 'N' || $new_el["props"]["PROPERTY_TYPE"] == 'S' || $new_el["props"]["PROPERTY_TYPE"] == 'E') {
            //$resUpd = updValueProperty($old_el["ID"], $propertyCode_TO, $newVal);            
        }

        if ($new_el["props"]["PROPERTY_TYPE"] == 'F') {
            //$resUpd = updValuePropertyFiles($old_el["ID"], $propertyCode_TO, $old_el["props"]["PROPERTY_VALUE_ID"], $newVal);            
        }
    
       
        if (!$resUpd) {$GLOBALS["err_id"][] = $new_el["EXTERNAL_ID"];}
       
       //$resUpd = updatePicture($old_el, $new_el);
       //$resUpd = updateText($old_el, $new_el);
       // Вывод результата обновления
       var_dump($resUpd);
       
       $index=$index+1;
    }
    
    //ошибки
    echo PHP_EOL . "Ошибки: " . PHP_EOL;
    print_r($GLOBALS["err_id"]);
}



function updateFieldSort( $old_el, $new_el ){
    global $err_id;
    $arFields = array(
        "SORT" => $new_el["SORT"],
    );
    $el = new CIBlockElement();
    $resUpdate = $el->Update( intval($old_el["ID"]), $arFields ,false, true, false, true);    
    
    if (!$resUpdate) {
        $err = $resUpdate->LAST_ERROR;
        $err_id[] = $old_el["EXTERNAL_ID"]; 
        return ["status" => "false","err" => $err];
    }
    return ["status" => "true"];
}



?>






<?

function output_log($log_msg)
{
	$log_filename = __DIR__ ."/log.txt";
    /*if (!file_exists($log_filename)) 
    {
        mkdir($log_filename, 0777, true);
    } */
    if (is_array($log_msg)) {
        $out = '';
        foreach ($log_msg as $msg) {
            $out .= $msg . ', ';
        }
        var_dump($out);
        file_put_contents($log_filename, $out . "\n", FILE_APPEND); 
    } else {
        file_put_contents($log_filename, $log_msg . "\n", FILE_APPEND); 
    } 
} 






function updateFieldsAndProps_1($IBLOCK_ID_FROM, $IBLOCK_ID_TO, $propertyCode) {
    // Взять список элементов из $IBLOCK_ID_FROM
    
    //$listNewEl = getFields($IBLOCK_ID_FROM, 'new'); // новые значения
    // $listOldEl = getFields($IBLOCK_ID_TO, 'old'); // старые значения
    
    $listNewEl = getProperties($IBLOCK_ID_FROM, 'new', $propertyCode); // новые значения
    $listOldEl = getProperties($IBLOCK_ID_TO, 'old', $propertyCode); // старые значения
    

    $index = 0;
    
     foreach ($listOldEl as $key => $old_el) {
         $EXTERNAL_ID = $old_el["EXTERNAL_ID"];
         $new_el = $listNewEl[$EXTERNAL_ID];
         $newVal = $new_el["props"]["VALUE"];
        
         if ( $newVal == '' && $old_el["props"]["VALUE"] == '' ) {
             continue;
         }
        
        outputCompare($new_el, $old_el);
        if ( is_array($newVal) ){
            $enumId = [];
            foreach ($newVal as $key => $val) {
                $enumId[] = getEnumID($val, $IBLOCK_ID_TO, $propertyCode);
            }
        } else {
            $enumId = getEnumID($val, $IBLOCK_ID_TO, $propertyCode);
        }
        
         $resUpd = updValueProperty($old_el["ID"], $propertyCode, $enumId);
         if (!$resUpd) {$GLOBALS["err_id"][] = $new_el["EXTERNAL_ID"];}
        
        //$resUpd = updatePicture($old_el, $new_el);
        //$resUpd = updateText($old_el, $new_el);
        // Вывод результата обновления
        var_dump($resUpd);
        
        $index=$index+1;
    }

    
    // ошибки
    print_r($GLOBALS["err_id"]);
}



function getEnumID($val, $IBLOCK_ID, $propertyCode) {
    $db_enum_list = CIBlockProperty::GetPropertyEnum($propertyCode, Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "VALUE"=>trim($val)));
    $enum = null;
    if($ar_enum_list = $db_enum_list->GetNext())
    {
        $enum = $ar_enum_list;
    }
    // var_dump([$val, $IBLOCK_ID, $propertyCode, $enum]);
    $enumId = $enum["ID"];
    return $enumId;
}



function outputCompare($new_el, $old_el) {
    echo "<h4>Before</h4>";
    echo  "<div class='height'>";
    output($new_el, "Источник");
    output($old_el, "Цель");
    echo "</div>"; 
}


function output($vals, $title){
    echo "<div style='width:50%; overflow: auto; border: solid 1px'>";
    echo "<h4>$title</h4>";
    print_r($vals);
    echo "</div>";
}



function getFields($IBLOCK_ID, $type){
    // $arSelect = Array("ID", "IBLOCK_ID", "NAME", "PREVIEW_PICTURE", "DETAIL_PICTURE", "DETAIL_TEXT","TAGS","EXTERNAL_ID", "CODE");
    $arSelect = Array("ID", "IBLOCK_ID", "NAME","EXTERNAL_ID", "CODE", "SORT");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID));
    if ($type == 'old') {
         //$arNav = Array("nPageSize" => 2557, "iNumPage" => 1);
        $arNav = Array("nPageSize" => 1000, "iNumPage" => 1);
        // $arNav = Array("nPageSize" => 2, "iNumPage" => 1);
    } else {
        $arNav = array();
    }
    $res = CIBlockElement::GetList(Array(), $arFilter, false, $arNav, $arSelect);

    while($ob = $res->GetNextElement()){
        $el = $ob->GetFields();
        $EXTERNAL_ID = $el["EXTERNAL_ID"];  
        $arFields[$EXTERNAL_ID] = $el; 

    }
    return $arFields;
}

function getProperties($IBLOCK_ID, $type, $propertyCode){
    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "EXTERNAL_ID", "PROPERTY_*", "ACTIVE");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID));

    $arNav = array();
    
    if ($type == 'old') {
        $arNav = Array("nPageSize" => 1000, "iNumPage" => 8); // по 8 раз
        // $arNav = Array("nPageSize" => 2557, "iNumPage" => 0);
        // $arNav = Array("nPageSize" => 2, "iNumPage" => 1);
    } 

    $res = CIBlockElement::GetList(Array(), $arFilter, false, $arNav, $arSelect);

    $arrEl = array();
    
    while($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
        
        $EXTERNAL_ID = $arFields["EXTERNAL_ID"];  
        
        $arrEl[$EXTERNAL_ID]= $arFields; 
        $arrEl[$EXTERNAL_ID]['props'] = $arProps[$propertyCode];     

    }
    return $arrEl;
}

function getProperties_1($IBLOCK_ID, $type, $propertyCode, $ext_id) {
    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "EXTERNAL_ID", "PROPERTY_$propertyCode");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID), "EXTERNAL_ID"=>$ext_id);

    $arNav = array();
    /*
    if ($type == 'old') {
        $arNav = Array("nPageSize" => 2557, "iNumPage" => 0);
    } */

    $res = CIBlockElement::GetList(Array(), $arFilter, false, $arNav, $arSelect);

    $arrEl = array();
    
    while($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();
        
        $EXTERNAL_ID = $arFields["EXTERNAL_ID"];  
        
        $arrEl[$EXTERNAL_ID]= $arFields; 
        $arrEl[$EXTERNAL_ID]['props'] = $arProps[$propertyCode];     

    }
    return $arrEl;
}



function updValueProperty($elementId, $propertyCode, $val) {
    
    $newVal = [];

    if (is_array($val)){
        foreach ($val as $key => $v) {
             $newVal[] = array("VALUE" => $v, "DESCRIPTION" => "");
        }        
        $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCode, $newVal);
    }
    else {
        $newVal = array("VALUE" => trim($val), "DESCRIPTION" => "");
        $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCode, $newVal);
    }

    return $res;
}


function updValuePropertyFiles($elementId, $propertyCode_TO, $prop_val_id, $val_id) {    
    
    $res = delFiles($elementId, $propertyCode_TO, $prop_val_id);
    if ($res) {
        $res = addFiles($elementId, $propertyCode_TO, $val_id);
    } 

    return $res;
}

function addFiles($elementId, $propertyCode_TO, $val_id){
    $newVal = [];
    if (is_array($val_id)) {
        foreach ($val_id as $key => $val) {
            $path = getPicture($val);
            $arFile = CFile::MakeFileArray($path);
            $newVal[] = Array("VALUE" => $arFile, "DESCRIPTION" => "");
        } 
    }
    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCode_TO, $newVal );
    // $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCode_TO, Array("VALUE" => $arFile, "DESCRIPTION" => "") );
    return $res;
}

function delFiles($elementId, $propertyCode_TO, $prop_val_id) {
    // Если изначально пуст (нечего удалять)
    if (!$prop_val_id) return true;
    
    $arFile = [];
    foreach ($prop_val_id as $key => $pr_vid) {
        $arFile[$pr_vid] = Array("VALUE" => Array("del" => "Y", "MODULE_ID" => "200" )); 
    }

    $res = CIBlockElement::SetPropertyValueCode($elementId, $propertyCode_TO, $arFile );
    return $res;
}


function getFiles($filesID)
{
    $arrFilesPath = array();
    foreach ($filesID as $key => $id) {
        $filePath = CFile::GetPath($id);
        $arrFilesPath[] = $filePath;
    }

    return $arrFilesPath;
}


function updateText($old_el, $new_el){
    global $err_id;
    $arFields = array(
        "DETAIL_TEXT" => $new_el["DETAIL_TEXT"],
        "DETAIL_TEXT_TYPE" => $new_el["DETAIL_TEXT_TYPE"],
        "TAGS" => $new_el["TAGS"]
    );
    $el = new CIBlockElement();
    $resUpdate = $el->Update( intval($old_el["ID"]), $arFields ,false, true, false, true);    
    
    if (!$resUpdate) {
        $err = $resUpdate->LAST_ERROR;
        $err_id[] = $old_el["EXTERNAL_ID"]; 
        return ["status" => "false","err" => $err];
    }
    return ["status" => "true"];
}



function updatePicture($old_el, $new_el){
    global $err_id;
    $filePath_old = getPicture($old_el["DETAIL_PICTURE"]);
    $filePath_new = $_SERVER["DOCUMENT_ROOT"] . getPicture($new_el["DETAIL_PICTURE"]) ;

    echo "FilePath_old: $filePath_old" . PHP_EOL; 
    echo "FilePath_new: $filePath_new" . PHP_EOL; 
    
    //if ($filePath_new !== null) {
            /*обновить картинку */ 
            $arFields = array(
                "DETAIL_PICTURE" => CFile::MakeFileArray(getPicture($new_el["DETAIL_PICTURE"]))
            ); 
            $el = new CIBlockElement();
            $resUpdate = $el->Update( intval($old_el["ID"]), $arFields);    
            
            if (!$resUpdate) {
               $err = $resUpdate->LAST_ERROR;
               $err_id[] = $old_el["EXTERNAL_ID"];
               return ["status" => "false","err" => $err];
            } 
        // }
        return ["status" => "true"];
    }


function getFieldsByID_EXTERNAL($arrID, $IBLOCK_ID, $arr = 'old'){
    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "PREVIEW_PICTURE", "DETAIL_PICTURE", "DETAIL_TEXT","TAGS","EXTERNAL_ID");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID), "EXTERNAL_ID" => $arrID );
    if ($arr == 'old') {
        // $arNav = Array("nPageSize"=>5, "iNumPage"=>1);
        $arNav = array();
    } else {
        $arNav = array();
    }
    $res = CIBlockElement::GetList(Array(), $arFilter, false, $arNav, $arSelect);

    while($ob = $res->GetNextElement()){
        $el = $ob->GetFields();
        $EXTERNAL_ID = $el["EXTERNAL_ID"];  
        $arFields[$EXTERNAL_ID] = $el; 

    }
    return $arFields;
}    




function getPicture($pictureID){
    $filePath = null;
    if (empty(!$pictureID)) {
        $filePath = \CFile::GetPath(intval($pictureID));
    } 
    return $filePath;
}


// ----------------------------------------------

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


