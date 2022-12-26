<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");



if (isset($_GET["idDepart"])) {
    $IBLOCK_ID = 1;
    $idDepart = IntVal($_GET["idDepart"]);
    $sections = array();
    // произвести поиск всех подотделов данного отдела

    $rsParentSection = CIBlockSection::GetByID($idDepart);
    if ($arParentSection = $rsParentSection->GetNext())
    {
    $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'],'>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],'>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']); // выберет потомков без учета активности
    $arSection = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
    // получаем подразделы
    while ($arSect = $arSection->GetNext())
    {
            $obj = ["id" => $arSect["ID"], "entityId" => "department", "title" => $arSect["NAME"]];
            $sections[] = $obj;
        }
    }


    $res = array("status" => "true", "idDepart" => $_GET["idDepart"], "sections" => $sections);

} else {
    $res = array("status" => "false", "idDepart" => $_GET["idDepart"]);
}

print_r(json_encode($res));

?>