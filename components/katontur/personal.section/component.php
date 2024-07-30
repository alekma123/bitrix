<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
if($this->startResultCache()) //для кеширования arResult
{
    $arResult= $this->getAuthUser();
}
*/

$arResult['USER'] = $this->getAuthUser();
$arResult['INFO_VISITED_TOURES'] = $this->getInfoVisitedTours();


$this->includeComponentTemplate();
?>
