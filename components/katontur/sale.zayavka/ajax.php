<?php

use Bitrix\Main\Context;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

\CBitrixComponent::includeComponentClass('katontur:sale.zayavka');


$res = null;

if (isset($_GET)){
    $method = $_GET['method'];
    $data = $_POST;
    
    $componentSaleZayavka = new \CSaleZayavka();
    switch ($method) {
        case 'loadBasket':
            $res = $componentSaleZayavka->loadBasket();
            break;
        case 'updBasket':
            $res = $componentSaleZayavka->updBasket($data['basket_item_id'], $data['quantity']);
            break;
        case 'orderSave':
            $res = $componentSaleZayavka->orderSave($data);
            break;  
        case 'getAuthUser':
            $res = $componentSaleZayavka->getAuthUser();
            break;      
        default:
            $res = $_POST;
            break;
    } 
} 


echo json_encode($res);