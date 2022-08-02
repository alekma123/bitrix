<?
//echo '<html lang="ru">';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('crm');

$output = '';
$status = '';

header('Content-type: text/html; charset=utf-8');
print_out('<pre>');
//print_out('Loading...' . PHP_EOL);

// --------------- OUTPUT -------------------
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

function print_out($msg) {
    $GLOBALS['output'] .= $msg . PHP_EOL;
}

function print_all_out($deal_id = 'null', $lead_id = 'null', $res_check_str = 'null', $phone = 'null',
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
    print_out('CONTACT_ID (found):' . $contact_id);
    print_out('RESULT: linking DEAL with CONTACT_ID : ' . $resUpdateDeal);
}
// ------------------------------------------

function getObject() {
	date_default_timezone_set('UTC');
	$current_date = date('l jS \of F Y h:i:s A') . ' UTC';
	print_out('DATE: ' . $current_date);
	output_log(PHP_EOL . PHP_EOL .  'DATE: ' . $current_date);

	$arSelect = [
		'CONTACT_ID', 'LEAD_ID', 'TYPE_ID', 'CREATED_BY_NAME', 'CREATED_BY_LAST_NAME', 'UF_CRM_5F87AEA1BEBCF'
	];

	$arFilter = [
		'CONTACT_ID' => '',
		'!UF_CRM_5F87AEA1BEBCF' => '', // пользовательское поле 'лид'
		'CREATED_BY_NAME' => "Александра",
		'CREATED_BY_LAST_NAME' => "Куличенко",
		'TYPE_ID' => 1
	];


	$crmDeal = CCrmDeal::GetListEx(false, $arFilter, false, false, $arSelect);
    // всего сделок 
    $total_deal = $crmDeal->SelectedRowsCount();
	
    $index = 0; 
    $res = 0;
    $index_limit = 2000;
	$array = [];

	while ($arFields = $crmDeal->GetNext()) {
		//print_out($arFields);
		$lead_id = $arFields['UF_CRM_5F87AEA1BEBCF'];
		$deal_id = $arFields['ID'];
		/* если deal_id имеет значение строки наподобие = [L] ..., то смотрим на наличие поля UF_CRM_PHONE_WORK
		и по нему ищем контакт */ 
		$res_check = checkLeadId($lead_id);
		$res_check_str = ($res_check) ? 'true' : 'false';
		// если в качестве лидазаписано название сделки, то пропускаем эту сделку

		if(!$res_check) {
            print_all_out($deal_id, $lead_id);
            continue;
        }  

		$phone = getPhoneByEL_ID($lead_id);
		if ($phone == null) {
            print_all_out($deal_id, $lead_id, $res_check_str);
            continue;    
        };

		// если такого телефона нет в контактах, то мы не привяываем сделку с контаком по номеру тел.
		/*
        try {
            $contact_id = getContactID_byPhone($phone);
            if ($contact_id == null) {
                print_all_out($deal_id, $lead_id, $res_check_str, $phone, $contact_id);
            } else  {
                $resUpdateDeal = updateDeal($deal_id, $contact_id);
                $resUpdateDeal_str = $resUpdateDeal ? 'true' : 'false';
                print_all_out($deal_id, $lead_id, $res_check_str, $phone, $contact_id, $resUpdateDeal_str);
                // добавить условие, что если contact_id успешно обновился, то кол-во успешных операций увеличивается
                if ( (int) $resUpdateDeal == 1 ) {$res++;}
            }
            
            if ($index == $index_limit) { break; }
        } catch (Exception $e) {
            print_out('Выброшено исключение: ',  $e->getMessage());
            break;
        } */
        $array_obj[] = array(
            "DEAl_ID" => $deal_id,
            "LEAD_ID" => $lead_id,
            "CHECK_LEAD_ID" => $res_check_str,
            "PHONE_LEAD" => $phone
        ); 
        $array_phone[] = $phone;         
        $index++;

        /*if ($index == $index_limit) {

            $array['array_obj'] = $array_obj;
            $array['array_phone'] = $array_phone;
            
            return $array; 
            break; 
        }*/
		$array['array_obj'] = $array_obj;
        $array['array_phone'] = $array_phone;

	}



	echo (PHP_EOL . "сделок: " . $total_deal . PHP_EOL);
	echo ("index: $index" . PHP_EOL);

    print_out("Всего сделок для обработки: " . $total_deal);
    print_out("Кол-во сделок, привязанных к контактам: " . $res);
    
    $GLOBALS["status"] = "Всего сделок для обработки: " . $total_deal . '<br>' . 
    "Кол-во сделок, привязанных к контактам: " . $res ;

	return $array;
} 


// Взять номер телефона по лиду
function getPhoneByEL_ID($el_id){
	$Element = CCrmFieldMulti::GetList([], [
		"ELEMENT_ID" => $el_id,
	   	"TYPE_ID" => 'PHONE',
	   	'ENTITY_ID' => 'LEAD',
	]); 
	$el = $Element->Fetch();
	return $el['VALUE'];
} 

function getContactID_byPhone($array_phone) {
	$contact_id = null;
	var_dump($array_phone);
	echo(PHP_EOL . 'phones: ' . count($array_phone) .PHP_EOL);
	//$array_phone = ["+79086643537", "+79832415815", "+79834062323", "+79501120779"];
	//echo($array_phone[0]);
	try {
		$arContacts = \Bitrix\Crm\ContactTable::getList(array(
		"filter" => array("PHONE" => $array_phone)
		)) -> fetchAll();

		//$contact_id = $arContacts[0]['ID'];
        //var_dump($arContacts);
		echo "CONTACT_ID: " . count($arContacts) . PHP_EOL;
		foreach( $arContacts as $contact) {
			var_dump($contact['ID']); // . PHP_EOL;
		}
        
	} catch(Exception $e){
		print_out('Выброшено исключение: ',  $e->getMessage(), "\n");
	} 

	//return $contact_id;
} 
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

$arrayObj = getObject();
echo('<pre>'); 
//echo('array_obj' . PHP_EOL);
echo('array_phone: ' . PHP_EOL);
//var_dump($arrayObj);
//var_dump($arrayObj['array_obj']); 

getContactID_byPhone($arrayObj['array_phone']);

$responce = array(
    "text" => $GLOBALS["output"],
    "status" => $GLOBALS["status"]
);
$res = json_encode($responce);
//echo($res);




function showDeal($deal_id = null) {
	$arSelect = [
		//'*', 'UF_*'
		'CONTACT_ID', 'LEAD_ID', 'TYPE_ID', 'CREATED_BY_NAME', 
		'CREATED_BY_LAST_NAME', 'UF_CRM_5F87AEA1BEBCF', 'UF_CRM_PHONE_WORK'
	];
	if ($deal_id == null) {
		$arFilter = [
			//'!LEAD_ID' => '',
			'CONTACT_ID' => '',
			'!UF_CRM_5F87AEA1BEBCF' => '',
			'CREATED_BY_NAME' => "Александра",
			'CREATED_BY_LAST_NAME' => "Куличенко",
			'TYPE_ID' => 1,
			'!UF_CRM_PHONE_WORK' => ''
		];
	} else {
		$arFilter = ['ID' => [$deal_id] ];
	}

	$crmDeal = CCrmDeal::GetListEx(false, $arFilter, false, false, $arSelect);
	//$deal = $crmDeal->GetNext();
	$count = $crmDeal->SelectedRowsCount();
	print_out(PHP_EOL . 'count: ' . $count . PHP_EOL);

	while ($deal = $crmDeal->GetNext()) {
		print_out( PHP_EOL . $deal['UF_CRM_5F87AEA1BEBCF'] . PHP_EOL );
		if ($deal['UF_CRM_PHONE_WORK'] == null) {
			print_out( PHP_EOL . 'UF_CRM_PHONE_WORK is null' . PHP_EOL );
		} else {
			print_out( PHP_EOL . $deal['UF_CRM_PHONE_WORK'] . PHP_EOL );
		}

	}
} 


?>