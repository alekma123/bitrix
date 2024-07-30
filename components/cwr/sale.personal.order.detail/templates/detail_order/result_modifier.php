<?php

$filesForCheck = array_filter($arResult['ORDER_PROPS'], function($el){
    if ($el['CODE'] == 'FILES_FOR_CHECK') return $el;
    else false;
});
// файлы проверки материалов
$arResult['FILES_FOR_CHECK'] = current($filesForCheck);
// статусы сделки
$arResult['STATUS_DEAL'] = [
    'IS_CHECKED' => false,
    'IS_PAID' => false,
    'IS_DELIVERED' => false 
];
switch ($arResult['STATUS']['ID']) {
    // case 'N':
    //     $arResult['STATUS_DEAL']['IS_CHECKED'] = false;
    //     $arResult['STATUS_DEAL']['IS_PAID'] = false;
    //     $arResult['STATUS_DEAL']['IS_DELIVERED'] = false; 

    case 'VP':
        $arResult['STATUS_DEAL']['IS_CHECKED'] = true;
        break;
    case 'PM':
        $arResult['STATUS_DEAL']['IS_CHECKED'] = true;
        $arResult['STATUS_DEAL']['IS_PAID'] = true;
        break;
    case 'DO':
        $arResult['STATUS_DEAL']['IS_CHECKED'] = true;
        $arResult['STATUS_DEAL']['IS_PAID'] = true;
        $arResult['STATUS_DEAL']['IS_DELIVERED'] = true;
        break;
}

?>
