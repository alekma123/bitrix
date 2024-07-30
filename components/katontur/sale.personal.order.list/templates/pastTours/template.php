<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixPersonalOrderListComponent $component */
/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'clipboard',
	'fx',
]);

Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/.default/script.js");
Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/.default/style.css");
$this->addExternalCss("/bitrix/css/main/bootstrap.css");

Loc::loadMessages(__FILE__);

?>
<?

if (!empty($arResult['ERRORS']['FATAL']))
{
	foreach($arResult['ERRORS']['FATAL'] as $error)
	{
		ShowError($error);
	}
	$component = $this->__component;
	if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED]))
	{
		$APPLICATION->AuthForm('', false, false, 'N', false);
	}

}
else
{
	$filterHistory = ($_REQUEST['filter_history'] ?? '');
	$filterShowCanceled = ($_REQUEST["show_canceled"] ?? '');

	if (!empty($arResult['ERRORS']['NONFATAL']))
	{
		foreach($arResult['ERRORS']['NONFATAL'] as $error)
		{
			ShowError($error);
		}
	}
	if (empty($arResult['ORDERS']))
	{
		if ($filterHistory === 'Y')
		{
			if ($filterShowCanceled === 'Y')
			{
				?>
				<h3><?= Loc::getMessage('SPOL_TPL_EMPTY_CANCELED_ORDER')?></h3>
				<?
			}
			else
			{
				?>
				<h3><?= Loc::getMessage('SPOL_TPL_EMPTY_HISTORY_ORDER_LIST')?></h3>
				<?
			}
		}
		else
		{
			?>
			<h3><?= Loc::getMessage('SPOL_TPL_EMPTY_ORDER_LIST')?></h3>
			<?
		}
	}
	?>
	<div class="orders-container"> 
		<div class="row">
			<?
			$nothing = !isset($_REQUEST["filter_history"]) && !isset($_REQUEST["show_all"]);
			$clearFromLink = array("filter_history","filter_status","show_all", "show_canceled");

			if ($nothing || $filterHistory === 'N')
			{
				?>
				<a class="sale-order-history-link" href="<?=$APPLICATION->GetCurPageParam("filter_history=Y", $clearFromLink, false)?>">
					<?echo Loc::getMessage("SPOL_TPL_VIEW_ORDERS_HISTORY")?>
				</a>
				<?
			}
			?>
		

		
		<?

		if ($filterHistory !== 'Y')
		{
			$paymentChangeData = array();
			$orderHeaderStatus = null;

			foreach ($arResult['ORDERS'] as $key => $order)
			{
				if ($orderHeaderStatus !== $order['ORDER']['STATUS_ID'] && $arResult['SORT_TYPE'] == 'STATUS')
				{
					$orderHeaderStatus = $order['ORDER']['STATUS_ID'];

					?>
					<h6 class="sale-order-title">
						<?= Loc::getMessage('SPOL_TPL_ORDER_IN_STATUSES') ?> &laquo;<?=htmlspecialcharsbx($arResult['INFO']['STATUS'][$orderHeaderStatus]['NAME'])?>&raquo;
					</h6>
					<?
				}
				?>
				<div class="col-md-12 col-sm-12 sale-order-list-container">
					
					<div class="row">
						<div class="col-md-12">
							<?$APPLICATION->IncludeComponent(
									'bitrix:catalog.item',
									'order',
									array(
										'RESULT' => array(
											'ITEM' => reset($order['BASKET_ITEMS']),
											'AREA_ID' => rand(),
											'TYPE' => 'CARD',
											'BIG_LABEL' => 'N',
											'BIG_DISCOUNT_PERCENT' => 'N',
											'BIG_BUTTONS' => 'N',
											'SCALABLE' => 'N'
										),
										'PARAMS' => [
											'ORDER' => $order['ORDER'],
											'TYPE' => 'PAST'
										],
									),
									$component,
									array('HIDE_ICONS' => 'Y')
								);
								?>
						</div>
					</div>
				</div>
				<?
			}
		}
		else
		{
			$orderHeaderStatus = null;

			if ($filterShowCanceled === 'Y' && !empty($arResult['ORDERS']))
			{
				?>
				<h6 class="sale-order-title">
					<?= Loc::getMessage('SPOL_TPL_ORDERS_CANCELED_HEADER') ?>
				</h6>
				<?
			}

			foreach ($arResult['ORDERS'] as $key => $order)
			{
				?>				
					<?$APPLICATION->IncludeComponent(
						'bitrix:catalog.item',
						'order',
						array(
							'RESULT' => array(
								'ITEM' => reset($order['BASKET_ITEMS']),
								'AREA_ID' => rand(),
								'TYPE' => 'CARD',
								'BIG_LABEL' => 'N',
								'BIG_DISCOUNT_PERCENT' => 'N',
								'BIG_BUTTONS' => 'N',
								'SCALABLE' => 'N'
							),
							'PARAMS' => [
								'ORDER' => $order['ORDER'],
								'TYPE' => 'PAST'
							],
						),
						$component,
						array('HIDE_ICONS' => 'Y')
					);
					?>
				<?
			}
		}
		?>
		</div>
	</div>

	<script>console.log("arResult.sale.personal.order.list: ", <?=json_encode($arResult)?>);</script>

	<div class="clearfix"></div>
	<?
	echo $arResult["NAV_STRING"];

	if ($filterHistory !== 'Y')
	{
		$javascriptParams = array(
			"url" => CUtil::JSEscape($this->__component->GetPath().'/ajax.php'),
			"templateFolder" => CUtil::JSEscape($templateFolder),
			"templateName" => $this->__component->GetTemplateName(),
			"paymentList" => $paymentChangeData,
			"returnUrl" => CUtil::JSEscape($arResult["RETURN_URL"]),
		);
		$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
		?>
		<script>
			BX.Sale.PersonalOrderComponent.PersonalOrderList.init(<?=$javascriptParams?>);
		</script>
		<?
	}
}

