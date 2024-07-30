<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
die();

use Bitrix\Catalog;
use Bitrix\Highloadblock as HL;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Basket;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\PriceMaths;

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule("catalog");
require_once 'Buyers.php';


class CSaleZayavka extends CBitrixComponent {    
    public $SALE_KIDS;
    public $BASKET_ITEM;
    const PERSON_TYPE = 1;
    const PAY_SYSTEM_1 = 2; // наличные курьеру
    const PAY_SYSTEM_2 = 1; // внутренний счет
    const PRICE_ADULT = 0;
    const PRICE_KID = 0;
    const GROUP_KID_ID = Buyers::GROUP_KID_ID; 

    public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
        $this->BASKET_ITEM = $this->loadBasket();
        $PRODUCT_ID = current($this->BASKET_ITEM)['PRODUCT_ID'];
        $this->PRICE_ADULT = $this->getPriceByGroupID($PRODUCT_ID);
        $this->PRICE_KID = $this->getPriceByGroupID($PRODUCT_ID, [self::GROUP_KID_ID]);
	}

    function getBasket(){
        $basket_storage = Sale\Basket\Storage::getInstance(Fuser::getId(), SITE_ID);
        $basket = $basket_storage->getBasket();
        return $basket;
    }
    
    public function loadBasket() {
        $basket = $this->getBasket();
        $product = [];
        foreach ($basket as $basket_item) {
            if ($basket_item->getField('DELAY') == "N") {
                $product[$basket_item->getId()] = $this->processBasketItem($basket_item);
            }
        }
        return $product;   
    }

    public function getBasketItem(){
        return $this->BASKET_ITEM;
    }


    public function processBasketItem(Sale\BasketItem $item){
        $basketItem = $item->getFieldValues();

		if ($this->isCompatibleMode())
		{
			$this->makeCompatibleArray($basketItem);
		}
        $basketItem['PROPS'] = $this->getBasketItemProperties($item);
        $basketItem['PROPS_ALL'] = $item->getPropertyCollection()->getPropertyValues();

        $props = $this->getProps($basketItem['PRODUCT_ID']);
        $basketItem['PROPS_ALL'] = array_merge($basketItem['PROPS_ALL'], $props);
        
        // дата проведения тура
        $basketItem['DATE_TOUR'] = $this->getDateTour($basketItem['PRODUCT_ID']);

        return $basketItem;
    }

    public function getBasketItemProperties(Sale\BasketItem $basketItem){
        $properties = [];
        // @var Sale\BasketPropertiesCollection $propertyCollection 
		$propertyCollection = $basketItem->getPropertyCollection();
        $basketId = $basketItem->getBasketCode();
        // Свойства записи, массив объектов Sale\BasketPropertyItem
        
        foreach ($propertyCollection->getPropertyValues() as $property)
		{
			if ($property['CODE'] == 'CATALOG.XML_ID' || $property['CODE'] == 'PRODUCT.XML_ID' || $property['CODE'] == 'SUM_OF_CHARGE')
				continue; 

			$property = array_filter($property, ['CSaleBasketHelper', 'filterFields']);
			$property['BASKET_ID'] = $basketId;
			$this->makeCompatibleArray($property);

			$properties[] = $property;
		}
        return $properties;

    }

    protected function makeCompatibleArray(&$array)
	{
		if (empty($array) || !is_array($array))
			return;

		$arr = [];
		foreach ($array as $key => $value)
		{
			if (is_array($value) || preg_match("/[;&<>\"]/", $value))
			{
				$arr[$key] = htmlspecialcharsEx($value);
			}
			else
			{
				$arr[$key] = $value;
			}

			$arr["~{$key}"] = $value;
		}

		$array = $arr;
	}
    protected function isCompatibleMode()
	{
		return $this->arParams['COMPATIBLE_MODE'] === 'Y';
	}

    protected function getDateTour($tour_id){
        $scheduleTour = [];
        $IBLOCK_ID = 10;
        
        $arFilter = Array("IBLOCK_ID" => $IBLOCK_ID, "PROPERTY_LINK_SCHEDULE_TOUR" => $tour_id, 'ACTIVE'=>'Y');
        $arSelected = ['*','PROPERTY_DATE_START','PROPERTY_DATE_END','PROPERTY_FREE_PLACES','PROPERTY_TOUR_STATUS'];
        $items = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelected);
        while ($arItem = $items->GetNextElement()) {
            $shedule['ID'] = $arItem->fields['ID'];
            $shedule['NAME'] = $arItem->fields['NAME'];
            $shedule['ACTIVE'] = $arItem->fields['ACTIVE'];
            $shedule['PROPERTY_DATE_START'] = $arItem->fields['PROPERTY_DATE_START_VALUE'];
            $shedule['PROPERTY_DATE_END'] = $arItem->fields['PROPERTY_DATE_END_VALUE'];
            $shedule['PROPERTY_FREE_PLACES'] = $arItem->fields['PROPERTY_FREE_PLACES_VALUE'];
            $shedule['PROPERTY_TOUR_STATUS'] = $arItem->fields['PROPERTY_TOUR_STATUS_VALUE'];
            $shedule['all'] = $arItem;

            // массив дат проведения туров
            $scheduleTour[]=$shedule;
        }
        return $scheduleTour;
    }

    protected function getProps($tour_id){
        $arProps = [];
        $IBLOCK_ID = 5;
        
        $arFilter = Array('IBLOCK_ID' => $IBLOCK_ID, 'ID'=>$tour_id, 'ACTIVE'=>'Y');
        $arSelected = [
            'ID',
            'PROPERTY_STUFF_LIST',
            'PROPERTY_CONTRACT_TEMPLATE',
            'PROPERTY_DISTANCE'
        ];
        $items = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelected);
        while ($arItem = $items->GetNextElement()) {
            // расстояние
            $arProps['DISTANCE']['ID'] = $arItem->fields['PROPERTY_DISTANCE_VALUE_ID'];            
            $arProps['DISTANCE']['VALUE'] = $arItem->fields['PROPERTY_DISTANCE_VALUE'];

            // шаблон договора
            $arProps['CONTRACT_TEMPLATE']['ID'] = $arItem->fields['PROPERTY_CONTRACT_TEMPLATE_VALUE_ID'];            
            $arProps['CONTRACT_TEMPLATE']['VALUE'] = $arItem->fields['PROPERTY_CONTRACT_TEMPLATE_VALUE'];
            $arProps['CONTRACT_TEMPLATE']['PATH'] = $this->getFilePath($arProps['CONTRACT_TEMPLATE']['VALUE']);

            // список снаряжения       
            $arProps['STUFF_LIST']['ID'] = $arItem->fields['PROPERTY_STUFF_LIST_VALUE_ID'];    
            $arProps['STUFF_LIST']['VALUE'] = $arItem->fields['PROPERTY_STUFF_LIST_VALUE'];
            $arProps['STUFF_LIST']['PATH'] = $this->getFilePath($arProps['STUFF_LIST']['VALUE']);
        }
        return $arProps;
    }

    function createOrder($data, $user_id){
        $basket = $this->getBasket();
        
        if (count($basket) > 0){
            $order =  Bitrix\Sale\Order::create(SITE_ID, $user_id);
            $order->setPersonTypeId(1); // тип плательщика(физ.лицо)
            $order->setBasket($basket);
            $this->savePropsOrder($order, $data);
            // Комментарий к заказу
            $order->setField('USER_DESCRIPTION', $data['notes']);
            $result = $order->save();
            
            if (!$result->isSuccess())
            {
                return ['status' => 'err', 'err'=> $result->getErrors()];
            } else {
                return ['status' => 'ok'];
            } 
        }
        else return ['status' => 'emptyBasket'];
    }


    //---------------------------
    // Оформление тура
    function bookingTour($data){
        // 1. Регистрируем пользователей, если их нет. Сохраняем все id пользователей в массив.
        $arrUsers = Buyers::getUsers($data);
        // 2. Создаём базовый заказ. Клиент - первый из массива, зарег. пользователей + тек. корзина + 
        //свойства заказа[тип клиента, доп.оборудование]. Возвращает ID базового заказа.
        $payer = $arrUsers[0];

        $orderBase = $this->makeOrderBase($payer, $data);
        $orderBaseID = $orderBase['ID'];
        unset($arrUsers[0]);
        //3. Создаем заказ для всех остальных участников.
        foreach ($arrUsers as $index => $user) {
            $payer = $arrUsers[$index];    
            $orderID = $this->makeOrder($payer, $orderBaseID, $data);    
            
        }
        return [ 'data' => $data, 'arrUsers' => $arrUsers, 'orderBaseID' => $orderBase];
        
    }
    // получить данные авторизованного пользователя
    function getAuthUser(){
        return Buyers::getAuthUser();
    } 
    
    // Создание основного заказа
    function makeOrderBase($payer, $data){
        $basket = $this->getBasket();
        $user_id = $payer['id'];

        if (count($basket) > 0){
            $order =  Bitrix\Sale\Order::create(SITE_ID, $user_id);
            $order->setPersonTypeId(self::PERSON_TYPE); // тип плательщика(физ.лицо)
            //$basket = $this->setBuyerType($basket, $payer['type_buyer']);
            $order->setBasket($basket);
            $this->savePropsOrder($order, $payer);
            // Комментарий к заказу
            $order->setField('USER_DESCRIPTION', $data['notes']);
            // Оплата 
            $this->createPayment($order, true);
            $result = $order->save();
            
            if (!$result->isSuccess()) return [
                'ID'=> false, 
                'msg' => $result->getErrorMessages()
            ];
            else return ['ID' => $order->getField('ID'), 'order' => $order]; 
        }
        else return [
            'ID' => false,
            'msg' => 'emptyBasket'
        ];
    }
    // создание корзины
    function createBasket($user_id){
        $basket = Bitrix\Sale\Basket::create(SITE_ID);
        $basket->setFUserId($user_id);
        // корзина тура
        $fieldsValues = current($this->getBasketItem());

        $basketItem  = $basket->createItem("catalog", $fieldsValues['PRODUCT_ID']);
        unset($fieldsValues['PRODUCT_ID']);
        $basketItem->setField('QUANTITY', 1);
        $basketItem->setField('NAME', $fieldsValues['NAME']);
        $basketItem->setField('BASE_PRICE', $fieldsValues['BASE_PRICE']);
        $basketItem->setField('CURRENCY', $fieldsValues['CURRENCY']);        
        $basketItem->setField('PRODUCT_PROVIDER_CLASS', $fieldsValues['PRODUCT_PROVIDER_CLASS']);        

        // свойства товара в корзине
        $propsBaket = $fieldsValues['PROPS_ALL'];
        $basketPropertyCollection = $basketItem->getPropertyCollection();
        $basketPropertyCollection->setProperty([
            [
                'NAME' => 'Направления',
                'CODE' => 'DIRECTIONS',
                'VALUE' => $propsBaket['DIRECTIONS']['VALUE'],
                'SORT' => 1,
            ],
            [
                'NAME' => 'Сложность',
                'CODE' => 'DIFFICULTY_LEVEL',
                'VALUE' => $propsBaket['DIFFICULTY_LEVEL']['VALUE'],
                'SORT' => 2,
            ],
            [
                'NAME' => 'Сезон',
                'CODE' => 'SEASON',
                'VALUE' => $propsBaket['SEASON']['VALUE'],
                'SORT' => 3,
            ],
            [
                'NAME' => 'Категории',
                'CODE' => 'CATEGORIES',
                'VALUE' => $propsBaket['CATEGORIES']['VALUE'],
                'SORT' => 4,
            ],
            [
                'NAME' => 'Расстояние',
                'CODE' => 'DISTANCE',
                'VALUE' => $propsBaket['DISTANCE']['VALUE'],
                'SORT' => 5,
            ],
        ]);
        
        $basket->save();
        return $basket;
    } 
    //тип покупателя
    function setBuyerType($basket, $typeBuyer){
        $basketItem = $basket[0];
        $arrCode = [];

        $basketPropertyCollection = $basketItem->getPropertyCollection();
            foreach($basketPropertyCollection as $property){
                $arrCode[]=$property->getField('CODE');
            }
            $hasPropTypeBuyer = in_array('TYPE_BUYER', $arrCode);
         
            if (!$hasPropTypeBuyer) {
                $basketPropertyCollection->setProperty([
                    [
                        'NAME' => 'Тип покупателя',
                        'CODE' => 'TYPE_BUYER',
                        'VALUE' => $typeBuyer,
                        'SORT' => 6,
                    ],
                ]);
            }
         
         $basket->save();
         return $basket;
    }

    // Оплата заказов. Оплата основго заказа через банковскую карту. Остаток идет на внутренний счет клиента.
    // С внутреннего счета оплачивать остальные заказы.
    function createPayment($order, $orderBase = false) {
        $PAY_SYSTEM = $orderBase === true ? self::PAY_SYSTEM_1 : self::PAY_SYSTEM_2; 
        
        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem(Bitrix\Sale\PaySystem\Manager::getObjectById($PAY_SYSTEM));
        $payment->setField("SUM", $order->getPrice());
        $payment->setField("CURRENCY", $order->getCurrency());
    }
    // создание заказа
    function makeOrder($payer, $orderBaseID, $data) {
        $user_id = $payer['id'];
        $orderBase = Bitrix\Sale\Order::load($orderBaseID);
        $order = Bitrix\Sale\Order::create(SITE_ID, $user_id);
        $order->setPersonTypeId(self::PERSON_TYPE); // тип плательщика(физ.лицо)
        // создание корзины
        $basketBase = $orderBase->getBasket();
        $basket = $this->createBasket($user_id);
        //$basket = $this->setBuyerType($basket, $payer['type_buyer']);
        $order->setBasket($basket);

        // Сохранить доп.снаряжение, тип покупателя, id базового заказа
        $payer['id_order_base'] = $orderBase->getField('ID');
        $this->savePropsOrder($order, $payer);
        // Комментарий к заказу
        $order->setField('USER_DESCRIPTION', $data['notes']);
        // Оплата 
        $this->createPayment($order, false);

        $result = $order->save();
        if (!$result->isSuccess()) return [
            'ID'=> false, 
            'msg' => $result->getErrorMessages()
        ];
        else return ['ID' => $order->getField('ID')]; 
    }

    //------------------------

    // оформить заявку
    function orderSave($data){
        global $USER;
        $members = $data['members'];
        $ID_USER = '';
        $res = '';
        foreach ($members as $key => $member) {
            $login = $member['LOGIN'];
            $user = CUser::GetByLogin($login);
            $userFilds = $user->Fetch();
            // Если нет юзера, то создаём его
            if (empty($userFilds)){
                $ID_USER = Buyers::regUser($member);
                if ($ID_USER) {
                    // создать заказ с привязкой к пользователю
                    $res = $this->createOrder($data, $ID_USER);
                }
                else {
                    $res = "Заявка не оформлена";
                }    
            } else {
                $ID_USER = $userFilds['ID'];
                // создать заказ с привязкой к пользователю
                $res = $this->createOrder($data, $ID_USER);
            }
        }
        return $res;
    }      

    // установить значение расстояние
    function setDistance($val){
        $basket = $this->getBasket();
        $basketItem = $basket[0];
        $collection = $basketItem->getPropertyCollection();
        $item = $collection->createItem();
        $item->setFields([
            'NAME' => 'Расстояние',
            'CODE' => 'DISTANCE',
            'VALUE' => $val,
        ]);
        $res = $basket->save();
        return $res; 
    }


    function savePropsOrder($order, $data){
        $propertyCollection = $order->getPropertyCollection();  
        // Тип покупателя
        $property = $propertyCollection->getItemByOrderPropertyCode('type_buyer');
        $property->setValue($data['type_buyer']);
        //Дата проведения тура
        $property = $propertyCollection->getItemByOrderPropertyCode('tour_date_start');
        $property->setValue($data['tour_date']['PROPERTY_DATE_START']);
        $property = $propertyCollection->getItemByOrderPropertyCode('tour_date_end');
        $property->setValue($data['tour_date']['PROPERTY_DATE_END']); 
        // Доп. оборудование
        foreach ($data['equipment'] as $key => $eq) {
            $property = $propertyCollection->getItemByOrderPropertyCode($key);
            $property->setValue(intval($eq));
        }
        // свойство id базового заказа
        if (!empty($data['id_order_base'])) {
            $property = $propertyCollection->getItemByOrderPropertyCode('id_order_base');
            $property->setValue($data['id_order_base']);
        }
        
        // Свойства плательщика
        /*
        foreach ($data['payer'] as $key => $payerProp) {
            if (!empty($payerProp)) {
                $property = $propertyCollection->getItemByOrderPropertyCode($key);
                if ($key == 'BIRTHDAY') {
                    $dateTour = strtotime($payerProp); 
                    $payerProp = time();
                    continue;
                } 
                if (!empty($property)) {
                    $property->setValue($payerProp);
                }
            }
        } */
    }



    // Обновить корзину при изменении кол-ва взрослых+дети и вернуть пересчитанную цену тура
    /*function updBasket($basket_item_id, $quantity, $sale_kids){
        $total_adults = $quantity['total_adults'];
        $total_kids = $quantity['total_kids'];
        $total = $total_adults + $total_kids;
        $sale = intval($sale_kids);
        //$sale = 20; // 20% скидка
        // $res = $this->setBasketSale($basket_item_id,$sale);
        $res = $this->setCustomPrice($basket_item_id, $total_adults, $total_kids, $sale);
        if ($res['status']) {
            $this->refreshBasket();
        }
        return $res;
    }*/
    


    function setCustomPrice($basket_item_id, $total_adults, $total_kids, $sale){
        $basket = $this->getBasket();
        $basketItem = $basket->getItemById($basket_item_id);
        // Формула расчета стоимости
        $coefficient_kids = (100 - $sale)/100; 
        $quantityTotal = $total_adults + $total_kids * $coefficient_kids;
        $priceTotal = $quantityTotal * $basket->getBasePrice();
        
        // $priceTotal = $quantityTotal * $basket->getPrice();
        $basketItem->setPrice(intval($priceTotal), true);
        $res = $basket->save();
        if (!$res->isSuccess()) return ['status' => false, 'err'=> $res->getErrors()];
        return ['status' => $res->isSuccess(), 'priceTotal' => $priceTotal];
    }
    // Установить скидку на тур
    /*
    function setBasketSale($basket_item_id,$sale){
        $basket = $this->getBasket();
        $basketItem = $basket->getItemById($basket_item_id);
        $basketItem->setField('DISCOUNT_PRICE', intval($sale));
        $res = $basket->save();
        return $res->isSuccess();
    }*/
    // установить кол-во тура
    /*
    function updateBasketQuantity($basket_item_id, $quantity){
        $basket = $this->getBasket();
        $basketItem = $basket->getItemById($basket_item_id);
        $basketItem->setField('QUANTITY', intval($quantity));
        $basket->save();
    }*/
    // обнивить корзину
    function refreshBasket(){
        $basket = $this->getBasket();
        $basket->refresh();
    }

    // получить цену для 
    function getPriceByGroupID($PRODUCT_ID, $arrGROUP_ID = []){
        //$basketItem = current($this->getBasketItem());
        //$PRODUCT_ID = $basketItem['PRODUCT_ID'];

        $dbPrice = CPrice::GetList(
            array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC", "SORT" => "ASC"),
            array("PRODUCT_ID" => $PRODUCT_ID),
            false,
            false,
            array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO")
        );
        while ($arPrice = $dbPrice->Fetch())
        {
            $arDiscounts = CCatalogDiscount::GetDiscountByPrice(
                    $arPrice["ID"],
                    $arrGROUP_ID,
                    "N",
                    SITE_ID
                );
            $discountPrice = CCatalogProduct::CountPriceWithDiscount(
                    $arPrice["PRICE"],
                    $arPrice["CURRENCY"],
                    $arDiscounts
                );
            $arPrice["DISCOUNT_PRICE"] = $discountPrice;
        }
        return $discountPrice;
    }


    // пересчет итоговой цены за тур
    function updPriceTotal($quantity){
        global $USER;

        $priceForAdult = $this->PRICE_ADULT;
        $priceForKid = $this->PRICE_KID;

        $totalAdults = intval($quantity['total_adults']);
        $totalKids = intval($quantity['total_kids']);
        $priceTotal = $totalAdults*$priceForAdult + $totalKids*$priceForKid;
        return ["priceForAdult"=>$priceForAdult, "priceForKid"=>$priceForKid, 'priceTotal' => $priceTotal];
    }    

    function getFilePath($file_id){
        $filePath = '';
        if (!empty($file_id)) {
            $filePath = \CFile::GetPath($file_id);
        } 
        return $filePath;
    }

    public function executeComponent()
    {
        new CSaleZayavka();
        $arParams = $this->arParams;
        $this->includeComponentTemplate();
    }

}


