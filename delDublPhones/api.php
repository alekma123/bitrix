<?
//require_once("array.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('crm');

$res_update = "res_update";
/*
$entity = new CCrmLead(true);//true - проверять права на доступ
$fields = array( 
    'TITLE' => 'Test' 
); 
$entity->update(1, $fields); 
*/
/*
$arFields = array(    
);
$CrmContact = CCrmContact::GetListEx(array(), $arFilter, false, false,  $arSelectFields ); 

while ($arFields = $CrmContact->GetNext()) {

}
*/

// $res = [];
//$arFilter = array("ID" => $arr_duble_tel[0]);

/*
$CrmContact = CCrmContact::GetList(array(), $arFilter, $arSelect, false);
while ($arFields = $CrmContact->GetNext()) {
    $res[] = $arFields;
}
*/
/*
$res = array(
    "arr" => $arr_duble_tel,
    "text" => $res_update,
    "total" => count($arr_duble_tel),
    // "res" => $res 
); */
$res = array(
    "arr" => "...",
    "text" => "...",
    "total" => "...",
    // "res" => $res 
);
//echo json_encode($res);
echo $res;
?>