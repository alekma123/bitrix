<?
//echo '<html lang="ru">';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('crm');

echo '<pre>';

$arSelect = [
    'CONTACT_ID', 'LEAD_ID', 'TYPE_ID', 'CREATED_BY_NAME', 'CREATED_BY_LAST_NAME', 'UF_CRM_5F87AEA1BEBCF', 'UF_CRM_PHONE_WORK'
];

$arFilter = [
    'CONTACT_ID' => '115',
    '!UF_CRM_5F87AEA1BEBCF' => '', // пользовательское поле 'лид'
    'CREATED_BY_NAME' => "Александра",
    'CREATED_BY_LAST_NAME' => "Куличенко",
    'TYPE_ID' => 1,
];

$crmDeal = CCrmDeal::GetListEx(false, $arFilter, false, false, $arSelect);
// всего сделок 
$total_deal = $crmDeal->SelectedRowsCount();

//updateDeal("335873", '');
echo "total: $total_deal" .PHP_EOL;


while ($arFields = $crmDeal->GetNext()) {
    //print_out($arFields);
   // $lead_id = $arFields['UF_CRM_5F87AEA1BEBCF'];
   // $phone_work = $arFields['UF_CRM_PHONE_WORK'];
    $deal_id = $arFields['ID'];

    $resUpdateDeal = updateDeal($deal_id, null);
    var_dump($resUpdateDeal);
    var_dump($arFields);
}

// Обновить поле contact_id у сделки
function updateDeal($id, $contact_id){
    $deal = new CCrmDeal(true);
    $fields = array( 
        'CONTACT_ID' => "$contact_id" 
    ); 
    $res = $deal->update($id, $fields);
	return $res; 
}


