<?
$arResult['location'] = false;
$index = array_search("51", $arResult["PROPERTY_LIST"]);
if ($index) {
    unset($arResult["PROPERTY_LIST"][$index]);
    $arResult['location']['props']['ID'] = "51";
}

$arResult['payment'] = false;
$index = array_search("48", $arResult["PROPERTY_LIST"]);
if ($index) {
    unset($arResult["PROPERTY_LIST"][$index]);
    $arResult['payment']['props']['ID'] = "48";
}
// Показать только модели опр. производителя
if (!empty($arResult["ELEMENT"]["IBLOCK_SECTION"])) {
    $selectedModelID = current($arResult["ELEMENT"]["IBLOCK_SECTION"])["VALUE"];
    if (!empty($selectedModelID)) {
        $arResult['PROPERTY_LIST_FULL']['IBLOCK_SECTION']['SELECTED'] = $selectedModelID;
        
        $selectedModelID = intval($selectedModelID);
        $selectedManID = $arResult['PROPERTY_LIST_FULL']['IBLOCK_SECTION']['ENUM'][$selectedModelID]['IBLOCK_SECTION_ID'];
      
        $arResult['PROPERTY_LIST_FULL']['IBLOCK_PARENT_SECTION']['SELECTED'] = $selectedManID;
        foreach ($arResult['PROPERTY_LIST_FULL']['IBLOCK_SECTION']['ENUM'] as $key => $model) {
            if ($model['IBLOCK_SECTION_ID'] !== $selectedManID) {
                unset($arResult['PROPERTY_LIST_FULL']['IBLOCK_SECTION']['ENUM'][$key]);
            }
        }
    
    }
}
// сортировка полей
$arSort = [
    "NAME",
    "CODE",
    "IBLOCK_PARENT_SECTION",
    "IBLOCK_SECTION",
    "116",
    "UF_ENERGY_EFFICIENCY",
    "50"
];
// Если есть среди выбранных свойств все свойства, что нужно отсртировать, то применяем сортировку
$arrDiff = array_diff($arSort, $arResult['PROPERTY_LIST']);
if (count($arrDiff) == 0 ) {
    $arResult['PROPERTY_LIST'] = $arSort;
}
?>

<script>console.log(<?=json_encode($arResult)?>)</script>