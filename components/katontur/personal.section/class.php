<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
die();

use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
use Bitrix\Main\UserTable;

CModule::IncludeModule("sale");
class CPersonalSection extends CBitrixComponent { 
    public $countPastTours = 0;
    public $countPastTypeToures = 0;

    public function __construct($component = null){
        parent::__construct($component);
        $this->errorCollection = new ErrorCollection();
    }
    function getAuthUser(){
        global $USER;
        $user = null;
        if ($USER->IsAuthorized()) {
            $rsUser = CUser::GetByID($USER->GetID());
			if ($arUser = $rsUser->Fetch())
			{
				$user['NAME'] = CUser::FormatName(
					CSite::GetNameFormat(false),
					[
						'NAME' => $arUser['NAME'],
						'LAST_NAME' => $arUser['LAST_NAME'],
						'SECOND_NAME' => $arUser['SECOND_NAME'],
					],
					false,
					false
				);
                $user['PERSONAL_PHOTO']['file_id'] = $arUser['PERSONAL_PHOTO'];
                $user['PERSONAL_PHOTO']['path'] = $this->getPathById($arUser['PERSONAL_PHOTO']);
			}
        } 
        //Fuser::getId();
        //SITE_ID;
        return $user;
    }
    function getPathById($id){
        $path = '';
        if (!empty($id)) $path = CFile::GetPath($id);
        return $path;
    }

    function getOrderTour() {
        // id ордера со статусом заказа
        $arOrders = $this->getInfoVisitedTours();
        $arOrderId = [];
        foreach ($arOrders as $key => $order) {
            $arOrderId[] = $order["ID"];
        }
        $ordesTour['basket'] = $this->getBasketOrder($arOrderId);
        $ordesTour['order'] = $arOrders;
        return $ordesTour;
    }

    function getInfoVisitedTours() {
        global $USER;
        $arOrders = [];
        $arBasket=[];
        $arDirections = [];
        $directions='';
        $USER_ID = $USER->GetID();

        $currDate = new \Bitrix\Main\Type\Date();
        $format = $currDate->getFormat();
        $dateTime = new \Bitrix\Main\Type\DateTime();
        $dateTimeFormatted = $dateTime->format($format);
        
        $dbRes = \Bitrix\Sale\Order::getList([
            'select' => ['*'],
            'filter' => [
                'USER_ID' => $USER_ID,
                'CANCELED' => 'N',
                'PROPERTY.ORDER_PROPS_ID' => 22,
                '<PROPERTY.VALUE' => $dateTimeFormatted
            ],
            'order' => ['ID' => 'DESC']
        ]);
        while ($order = $dbRes->fetch())
            {
                $order['PROPS'] = $this->getPropsOrder($order['ID']);
                $arOrders[]=$order;

                $basketOrder = $this->getBasketOrder($order['ID'])[0];
                $arBasket[] = $basketOrder;
                
                $directionVal = trim($basketOrder['PROPS']['DIRECTIONS']['VALUE']);
                if (!empty($directionVal)) {
                    $arDirections []= $directionVal;
                }
            } 
        
        $directions = implode('/', $arDirections);
        $arDirections = explode('/', $directions);
        $arDirections = array_unique($arDirections);
    
        return [
            "COUNT" => count($arOrders), 
            "DIRECTIONS" => count($arDirections), 
            "ORDERS" => $arOrders,
            "BASKET" => $arBasket   
        ];    
    }

    function getTotalDirections(){

    }



    function getBasketOrder($arOrderId){
        $basketOrder = [];
        $dbRes = \Bitrix\Sale\Basket::getList([
            'select' => ['*'],
            'filter' => [
                //'=FUSER_ID' => \Bitrix\Sale\Fuser::getId(), 
                // '!ORDER_ID' => null,
                'ORDER_ID' => $arOrderId,
                //'DELAY' => 'N',
            ]
        ]);
        
        while ($item = $dbRes->fetch())
        {
            $item['PROPS'] = $this->getPropsBasketOrder($item['ID']);
            $basketOrder[] = $item;
        }
        return $basketOrder;
    }
    // Свойства заказа
    function getPropsOrder($id) {
        $orderProps = [];
        $dbRes = \Bitrix\Sale\PropertyValueCollection::getList([
            'select' => ['*'],
            'filter' => [
                '=ORDER_ID' => $id, 
            ]
        ]);
        while ($item = $dbRes->fetch())
        {
            $orderProps[]=$item;
        }
        return $orderProps;
    }

    function getPropsBasketOrder($id){
        $basketProps = [];
        $dbRes = \Bitrix\Sale\BasketPropertiesCollection::getList([
            'select' => ['*'],
            'filter' => [
                '=BASKET_ID' => $id, 
            ]
        ]);
        while ($item = $dbRes->fetch())
        {
            $basketProps[$item['CODE']]=$item;
        }
        return $basketProps;
    }

    // отложенные туры
    function getDelayTours(){
        $delayTours = [];
        $dbRes = \Bitrix\Sale\Basket::getList([
            'select' => ['*'],
            'filter' => [
                '=FUSER_ID' => \Bitrix\Sale\Fuser::getId(), 
                'ORDER_ID' => null,
                '!DELAY' => NULL
            ]
        ]);
        
        while ($item = $dbRes->fetch())
        {
            $delayTours[] = $item;
        }
        return $delayTours;
    }


}
?>