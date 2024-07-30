<?php

const STOP_STATISTICS = true;
const NO_KEEP_STATISTIC = "Y";
const NO_AGENT_STATISTIC = "Y";
const DisableEventsCheck = true;
const BX_SECURITY_SHOW_MESSAGE = true;
const NOT_CHECK_PERMISSIONS = true;

use \Bitrix\Main\Application;


$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!check_bitrix_sessid() && !$request->isPost())
{
	die();
}

$orderData = $request->get("orderData");
$templateName = $request->get("templateName");


$action = $request->get("action");
$data = $request->get("data");

if (!empty($action) ) {
	\Bitrix\Main\Loader::includeModule('sale');
	// номер заказа
	$accountNumber = current($orderData)['order'];
	$order = \Bitrix\Sale\Order::loadByAccountNumber($accountNumber);
	// изменить статус заказа
	if($action == 'changeStatus') {
		$order->setField('STATUS_ID', $data['status_id']);
		$orderSaved = $order->save();
		if (!$orderSaved->isSuccess())
			{ 
				$res['status'] = 0;
				$res['err'] = $orderSaved->getErrorMessages();
			}
		else {
			$res['status'] = 1;
			$res['status_id'] = $order->getField('STATUS_ID');
		}	
		echo json_encode($res);
	}
	
	// изменить статус оплаты заказа
	if ($action == 'changePayment') {
		$isPayed = $data['isPayed'];
		$paymentCollection = $order->getPaymentCollection();
		foreach ($paymentCollection as $payment)
		{
			$payed = $payment->setPaid($isPayed);
			if (!$payed->isSuccess())
			{
				$res['status'] = 0;
				$res['err'] = $payed->getErrorMessages();
			}
		}

		$orderSaved = $order->save();
		if (!$orderSaved->isSuccess())
			{ 
				$res['status'] = 0;
				$res['err'] = $orderSaved->getErrorMessages();
			}
		else {
			$res['status'] = 1;
			$res['status_id'] = $order->getField('STATUS_ID');
		}	
		echo json_encode($res);
	}
	// запрос на изменении позиции проверки
	if ($action == 'updIsChecked') {
		
		// Сохранить в виде json-позиции проверок.
		$totalPosition = intval($data['totalPosition']); 
		$position = intval($data['position']);
		$arFilesCheck = getArrJSON($order, $totalPosition);
		$diff = $totalPosition - count($arFilesCheck);
		// добавить недостающие позиции
		if( $diff > 0 ) {
			for ($i=$diff; $i < $totalPosition; $i++) {     
				$arFilesCheck[$i] = (object) array('filesIds' => [], 'isChecked' => 0);
			}
		}
		// обновить позицию материалов проверок в JSON
		$arFilesCheck[$position]->isChecked = $data['isChecked'];
		updJSON($order, $arFilesCheck); 

		// сохранить изменения в заказе
		$orderSaved = $order->save();
		if (!$orderSaved->isSuccess())
			{ 
				$res['status'] = 0;
				$res['err'] = $orderSaved->getErrorMessages();
			}
		else {
			$res['status'] = 1;
			$res['status_id'] = $order->getField('STATUS_ID');
		}	
		echo json_encode($res);
	}
	die();
}

if(empty($templateName))
{
	$templateName = "";
}

$params['ACCOUNT_NUMBER'] = $orderData['order'];
$params['PAYMENT_NUMBER'] = $orderData['payment'];
$params['PATH_TO_PAYMENT'] = $orderData['path_to_payment'] <> '' ? htmlspecialcharsbx($orderData['path_to_payment']) : "";
$params['REFRESH_PRICES'] = ($orderData['refresh_prices'] === 'Y') ? 'Y' : 'N';
$params['RETURN_URL'] = $orderData['returnUrl'] ?? "";
if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
{
	$params['ALLOW_INNER'] = $orderData['allow_inner'];
	$params['ONLY_INNER_FULL'] = $orderData['only_inner_full'];
}
else
{
	$params['ALLOW_INNER'] = "N";
	$params['ONLY_INNER_FULL'] = "Y";
}

CBitrixComponent::includeComponentClass("bitrix:sale.order.payment.change");
$orderPayment = new SaleOrderPaymentChange();
$orderPayment->initComponent('bitrix:sale.order.payment.change');
$orderPayment->includeComponent($templateName, $params, null);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');



function getArrJSON($order, $total_position){
    $PROP_ID = 63;
    $collection = $order->getPropertyCollection();
    $propertyValue = $collection->getItemByOrderPropertyId($PROP_ID);
    $json = $propertyValue->getField('VALUE');
    if (empty($json)) {
        $json = getEmptyJSON($total_position);
    }
    $arFilesCheck = json_decode($json);
    return $arFilesCheck;
}

function getEmptyJSON($totalPosition){
    $arFilesCheck = [];
    for ($i = 0; $i < $totalPosition ; $i++) { 
        $arFilesCheck[$i]['filesIds']= [];
        $arFilesCheck[$i]['isChecked']= false;
    }
    return json_encode($arFilesCheck);
}

function updJSON($order, $value) {
    $PROP_ID = 63; // кастомное свойство JSON
    $collection = $order->getPropertyCollection();
    $propertyValue = $collection->getItemByOrderPropertyId($PROP_ID);
    $value = json_encode($value);
    $updJSON = $propertyValue->setField('VALUE', $value);
}
