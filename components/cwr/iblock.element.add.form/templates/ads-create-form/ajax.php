<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
CModule::IncludeModule("iblock");

if (!check_bitrix_sessid()){
    return;
}

if($_REQUEST['action'] == 'getModel') {
    $sectionId = $_REQUEST['section_id'];
    $iblocId = $_REQUEST['iblock_id'];
	if(!empty($sectionId) && !empty($iblocId)) {
		$arModel = [];
		$rsIBlockSectionList = CIBlockSection::GetList(
			array("left_margin"=>"asc"),
			array(
				"ACTIVE"=>"Y",
				"IBLOCK_ID"=> $iblocId,
				"SECTION_ID"=>$sectionId
			),
			false,
			array("ID", "NAME", "DEPTH_LEVEL","IBLOCK_SECTION_ID")
		);
		
		while ($arSection = $rsIBlockSectionList->GetNext())
		{
		
			$arModel[] = $arSection;
		}
		echo json_encode(['models'=>$arModel]);
	} 
	else {
		echo json_encode(['models'=>[]]);
	}
}

if($_REQUEST['action'] == 'getModelProps') {
    $sectionId = $_REQUEST['section_id'];
    $iblocId = $_REQUEST['iblock_id'];
	if(!empty($sectionId) && !empty($iblocId)) {
		$arModelProps = [];
		$rsIBlockSectionList = CIBlockSection::GetList(
			array("left_margin"=>"asc"),
			array(
				"ACTIVE"=>"Y",
				"IBLOCK_ID"=> $iblocId,
				"ID"=>$sectionId
			),
			false,
			array("ID", "NAME", "DEPTH_LEVEL","UF_PERFORMANCE","UF_PERFORMANCE_MEASURE","UF_ENERGY_EFFICIENCY", "UF_PERFORMANCE_FROM","UF_PERFORMANCE_TO")
		);
		
		while ($arSection = $rsIBlockSectionList->GetNext())
		{
			$arModelProps = $arSection;
		}
		if (!empty($arModelProps['UF_PERFORMANCE_MEASURE'])) {
			$arModelProps['UF_PERFORMANCE_MEASURE'] = getValueEnum($arModelProps['UF_PERFORMANCE_MEASURE']);
		}

		echo json_encode(['model'=> $arModelProps]);
	} 
	else {
		echo json_encode(['model' => false]);
	}
}


function getValueEnum($idEnum){
	$obEnum = new CUserFieldEnum();
	$rsEnum = $obEnum->GetList(array(), array("ID"=>$idEnum));
	$enum = array();
	while($arEnum = $rsEnum->Fetch())
	{
		$enum = $arEnum;
	}
	return $enum;
}



?>