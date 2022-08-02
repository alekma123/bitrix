<?
//echo '<html lang="ru">';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('crm');

print_out('<pre>');

$output = '';
$status = array(
	'totalDeals' => 0,
	'updateDeals' => 0
);
$pageSize = 150;

header('Content-type: text/html; charset=utf-8');
print_out('<pre>');

if (isset($_POST["page"])) {
	$page = ($_POST["page"]);
} else { $page = 1; }

// --------------- OUTPUT ----------------------
function output_log($log_msg)
{
	$log_filename = __DIR__ ."/log";
    if (!file_exists($log_filename)) 
    {
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.txt';
	file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND); 
} 

function output_log_133($log_msg)
{
	$log_filename = __DIR__ ."/log";
    if (!file_exists($log_filename)) 
    {
        mkdir($log_filename, 0777, true);
    }
    if (is_array($log_msg)) {
        $out = 'DEAL_ID: ' . PHP_EOL;
        foreach ($log_msg as $msg) {
            $out .= $msg . ', ';
        }
        $log_file_data = $log_filename.'/log_133_' . date('d-M-Y') . '.txt';
        file_put_contents($log_file_data, $out . "\n", FILE_APPEND); 
    } else {
        $log_file_data = $log_filename.'/log_133_' . date('d-M-Y') . '.txt';
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND); 
    } 
} 


function print_out($msg) {
    $GLOBALS['output'] .= $msg . PHP_EOL;
}

function print_all_out($phone_work = 'null', $deal_id = 'null', $lead_id = 'null', $res_check_str = 'null', $phone = 'null',
$contact_id = 'null', $resUpdateDeal = 'null') {
    output_log('');
    output_log('DEAL_ID: '. $deal_id );
    output_log('LEAD_ID (UF_CRM_5F87AEA1BEBCF): ' . $lead_id ); 
    output_log('CHECK LEAD_ID: ' . $res_check_str);
    output_log('PHONE OF LEAD: ' . $phone);
    output_log('CONTACT_ID (found): ' . $contact_id);
    output_log('RESULT: linking DEAL with CONTACT_ID : ' . $resUpdateDeal);
    
    print_out('');
    print_out('DEAL_ID: '. $deal_id );
    print_out('LEAD_ID (UF_CRM_5F87AEA1BEBCF): ' . $lead_id ); 
    print_out('CHECK LEAD_ID: ' . $res_check_str);
    print_out('PHONE OF LEAD: ' . $phone);
    print_out('PHONE_WORK: ' . $phone_work);
    print_out('CONTACT_ID (found):' . $contact_id);
    print_out('RESULT: linking DEAL with CONTACT_ID : ' . $resUpdateDeal);
}
// ------------- MAIN FUNCTION -----------------------------

function init() {
	date_default_timezone_set('UTC');
	$current_date = date('l jS \of F Y h:i:s A') . ' UTC';
	print_out('DATE: ' . $current_date);
	output_log(PHP_EOL . PHP_EOL .  'DATE: ' . $current_date);

	$arSelect = [
		'CONTACT_ID', 'LEAD_ID', 'TYPE_ID', 'CREATED_BY_NAME', 'CREATED_BY_LAST_NAME', 'UF_CRM_5F87AEA1BEBCF', 'UF_CRM_PHONE_WORK'
	];

	$arFilter = [
		'CONTACT_ID' => '',
		'?UF_CRM_5F87AEA1BEBCF' => "[L]", // пользовательское поле 'лид'
		'CREATED_BY_NAME' => "Александра",
		'CREATED_BY_LAST_NAME' => "Куличенко",
		'TYPE_ID' => 1,
	];


	// $crmDeal = CCrmDeal::GetListEx(false, $arFilter, false, false, $arSelect);
    $arNavStartParams = ['iNumPage' => $GLOBALS['page'], 'nPageSize' => $GLOBALS['pageSize']];
	$crmDeal = CCrmDeal::GetListEx(false, $arFilter, false, $arNavStartParams, $arSelect);
    // всего сделок 
    $total_deal = $crmDeal->SelectedRowsCount();


    while ($arFields = $crmDeal->GetNext()) {
		//print_out($arFields);
		$lead_id = $arFields['UF_CRM_5F87AEA1BEBCF'];
        $phone__work = $arFields['UF_CRM_PHONE_WORK'];
		$deal_id = $arFields['ID'];

       
        $res_check = checkLeadId($lead_id);
		// если в качестве лидазаписано название сделки, то пропускаем эту сделку
		if(!$res_check) {
            print_all_out($phone_work, $deal_id, $lead_id);
            //if ($phone_work !== null)
            continue;
        }  
		$res_check_str = ($res_check) ? 'true' : 'false';

		//$phone = getPhoneByEL_ID($lead_id);
		if ($phone_work == null) {
            print_all_out("null", $deal_id, $lead_id, $res_check_str);
            continue;    
        };
            $phone_work = formatPhone($phone__work);
            $contact_id = null;
            $contact_id_by_phone = getContactID_byPhone($phone_work);
            if ($contact_id_by_phone == null) {
                $contact_id = getContactID_byExtraPhone($phone_work);
            } else {
                $contact_id = $contact_id_by_phone;
            }
            
            
            if ($contact_id == null) {
                $arr_not_contact_id[] = $deal_id;
                print_all_out($phone_work, $deal_id, $lead_id, $res_check_str, $phone);
            } else  {
                $resUpdateDeal = updateDeal($deal_id, $contact_id);
                $resUpdateDeal_str = $resUpdateDeal ? 'true' : 'false';
                //$resUpdateDeal_str = 'false';
                print_all_out($phone_work, $deal_id, $lead_id, $res_check_str, $phone, $contact_id, $resUpdateDeal_str);
                // добавить условие, что если contact_id успешно обновился, то кол-во успешных операций увеличивается
                if ( (int) $resUpdateDeal == 1 ) {$res++;}
            }
            
    }
    print_out("Всего сделок для обработки: " . $total_deal);
    print_out("Кол-во сделок, привязанных к контактам: " . $res);
   
	$GLOBALS["status"]["totalDeals"] = $total_deal;
	$GLOBALS["status"]["updateDeals"] = $res;
}

// ----------------------------------------------------------------
function formatPhone($phone) {
    //$string = '89648046671';
    $pattern = '/^8/i';
    $replacement = '+7';
    $res = preg_replace($pattern, $replacement, $phone);
    return $res;
}


// Получить contact_id по полю PHONE
function getContactID_byPhone($phone) {
	$contact_id = null;
	try {
		$arContacts = \Bitrix\Crm\ContactTable::getList(array(
		"filter" => array(
            "PHONE" => "$phone",
            //"?UF_CRM_1607621506833" => "$phone"
            //"LAST_NAME" => "",
            //"NAME" => ""
            )
		)) -> fetchAll();
		$contact_id = $arContacts[0]['ID'];
	} catch(Exception $e){
		print_out('Выброшено исключение: ',  $e->getMessage(), "\n");
	} 
	return $contact_id;
}

// Получить contact_id по полю UF_CRM_1607621506833. Поиск по дополнительным номерам
function getContactID_byExtraPhone($phone) {
	$contact_id = null;
	try {
		$arContacts = \Bitrix\Crm\ContactTable::getList(array(
		"filter" => array(
            "?UF_CRM_1607621506833" => "$phone",
            "TYPE_ID" => "CLIENT",
            )
		)) -> fetchAll();
		$contact_id = $arContacts[0]['ID'];
	} catch(Exception $e){
		print_out('Выброшено исключение: ',  $e->getMessage(), "\n");
	} 
	return $contact_id;
} 
// Обновить поле contact_id у сделки
function updateDeal($id, $contact_id){
    $deal = new CCrmDeal(true);
    $fields = array( 
        'CONTACT_ID' => $contact_id 
    ); 
    $res = $deal->update($id, $fields);
	return $res; 
}
function checkLeadId($lead_id) {
	// проверка lead_id на значение. Если lead_id - название сделки, то возвр. false
	$pos = strpos($lead_id, '[L]');
	if ($pos === false) return true;
	else return false;
}
//----------------- START SCRIPT ------------------------

init();

$responce = array(
    "text" => $GLOBALS["output"],
    "status" => $GLOBALS["status"]
);
$res = json_encode($responce);
echo($res);