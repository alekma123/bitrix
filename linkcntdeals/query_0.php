<?
echo '<html lang="ru">';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('crm');

$output = '';
$status = '';
$index_limit = 100;

header('Content-type: text/html; charset=utf-8');
print_out('<pre>');
echo('<pre>');


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
// ----------------- TEST DEAL -----------------
function test() {
	$arSelect = [
		'CONTACT_ID', 'LEAD_ID', 'TYPE_ID', 'CREATED_BY_NAME', 'CREATED_BY_LAST_NAME', 'UF_CRM_5F87AEA1BEBCF', 'DATE_CREATE'
		// '*'
	];
	
	$arFilter = [
		//'!LEAD_ID' => '',
		'CONTACT_ID' => false,
		'UF_CRM_5F87AEA1BEBCF' => false, // пользовательское поле 'лид'
		//'CREATED_BY_NAME' => "Александра",
		//'CREATED_BY_LAST_NAME' => "Куличенко",
		'TYPE_ID' => 1
	];
	
	
	$crmDeal = CCrmDeal::GetListEx(false, $arFilter, false, false, $arSelect);
	// всего сделок 
	$total_deal = $crmDeal->SelectedRowsCount();
	echo "Всего записей: " . $total_deal . PHP_EOL ;
	echo var_dump($arFields = $crmDeal->GetNext());
	/*
	while ($arFields = $crmDeal->GetNext()) {
		var_dump($arFields) . PHP_EOL;
	} */
}
// ------------------------------------------

// ----------------- TEST CONTACT -----------------
function testContact($phone) {
	$contact_id = null;
	try {
		$arContacts = \Bitrix\Crm\ContactTable::getList(array(
		"filter" => array("PHONE" => "$phone")
		)) -> fetchAll();
		$contact_id = $arContacts;
		print(PHP_EOL);
		var_dump($contact_id);
		print(PHP_EOL);

	} catch(Exception $e){
		print_out('Выброшено исключение: ',  $e->getMessage(), "\n");
	}
}
// ------------------------------------------

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
	echo "Всего записей: " . $total_deal . PHP_EOL ;
	
    $index = 0; 
    $res = 0;
    $index_limit = $_GLOBALS['index_limit'];

	$array = [];

	while ($arFields = $crmDeal->GetNext()) {
		$lead_id = $arFields['UF_CRM_5F87AEA1BEBCF'];
		$deal_id = $arFields['ID'];
		/* если deal_id имеет значение строки наподобие = [L] ..., то смотрим на наличие поля UF_CRM_PHONE_WORK
		и по нему ищем контакт */ 
		$res_check = checkLeadId($lead_id);
		// если в качестве лидазаписано название сделки, то пропускаем эту сделку
		if(!$res_check) {continue;}  
		$res_check_str = ($res_check) ? 'true' : 'false';

		$phone = getPhoneByEL_ID($lead_id);
		if ($phone == null) print_out('PHONE OF LEAD is NULL');

		// если такого телефона нет в контактах, то мы не привяываем сделку с контаком по номеру тел.
		
        if ($phone !== null) {
			try {
				//$contact_id = getContactID_byPhone($phone);
				//$resUpdateDeal = updateDeal($deal_id, $contact_id);
                /*
				output_log('');
				output_log('DEAL_ID: '. $deal_id );
				output_log('LEAD_ID (UF_CRM_5F87AEA1BEBCF): ' . $lead_id ); 
                output_log('CHECK LEAD_ID: ' . $res_check_str);
				output_log('PHONE OF LEAD: ' . $phone);
				output_log('CONTACT_ID (found): ' . $contact_id);
				//output_log("RESULT: linking DEAL with CONTACT_ID : $resUpdateDeal");

				print_out('');
				print_out('DEAL_ID: '. $deal_id );
				print_out('LEAD_ID (UF_CRM_5F87AEA1BEBCF): ' . $lead_id ); 
				print_out('CHECK LEAD_ID: ' . $res_check_str);
                print_out('PHONE OF LEAD: ' . $phone);
				print_out('CONTACT_ID (found):' . $contact_id);
				//print_out("RESULT: linking DEAL with CONTACT_ID : $resUpdateDeal");
				// $index++;
                // добавить условие, что если contact_id успешно обновился, то кол-во успешных операций увеличивается
                // ТЕСТОВОЕ ЗНАЧЕНИЕ возьмём contact_id
                //if ( $contact_id !== null ) {$res++;}
                */
                $array_tmp = array(
					"DEAl_ID" => $deal_id,
                    "LEAD_ID" => $lead_id,
                    "CHECK_LEAD_ID" => $res_check_str,
                    "PHONE_LEAD" => $phone
                );
                $array[] = $array_tmp;

				if ($index == $index_limit) { break; } 
				} catch (Exception $e) {
    				print_out('Выброшено исключение: ',  $e->getMessage());
					break;
				}
		}
        $index++;

	}
    
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

function getContactID_byPhone($phone) {
	$contact_id = null;
	try {
		$arContacts = \Bitrix\Crm\ContactTable::getList(array(
		"filter" => array("PHONE" => "$phone")
		)) -> fetchAll();
		$contact_id = $arContacts[0]['ID'];
	} catch(Exception $e){
		print_out('Выброшено исключение: ',  $e->getMessage(), "\n");
	} 

	return $contact_id;
} 

function updateDeal($id, $contact_id){
	$arFields = [
		"CONTACT_ID" => $contact_id
	];

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

//-------------GET CONTACT ID ----------------------

function getArrayPhone($arrObj) {
    $arr_phone = [];
    $index = 0;
    $limit = $GLOBALS['index_limit'];
    foreach ($arrObj as $obj) {
        $phone = $obj['PHONE_LEAD'];
        //if (checkPhone($phone)) {
            $arr_phone[] = $phone;
        //}
       // $index++;
       // if ($index == $limit) break; 
    }
    return $arr_phone;
}

function findContactId($arr_phone) {

    $arContacts = \Bitrix\Crm\ContactTable::getList(array(
        "filter" => array("PHONE" => $arr_phone)
        )) -> fetchAll();

        foreach($arContacts as $contact) {
            if ($contact['ID'] == null) { echo 'CONTACT_ID is null' . PHP_EOL; }
            else { var_dump($contact['ID']);}
        }    
}

function checkPhone($phone){
    preg_match_all('/8.*/', $phone, $matches);
    if (count($matches[0][0]) == 0 ) return true;
    else return false;
}
//-----------------------------------------------------------------------

echo "test" . PHP_EOL;
echo('<pre>');

//$arrayObject = getObject();
/*  
$phone1 = "+79247176660";
$phone2 = "+79832454741";
*/
// var_dump($arrayObject);
// $arr_phone = getArrayPhone($arrayObject);
// var_dump($arr_phone);
//findContactId($arr_phone);



$responce = array(
    "text" => $GLOBALS["output"],
    "status" => $GLOBALS["status"]
);
$res = json_encode($responce);

test();
//testContact("+79526337923");
?>