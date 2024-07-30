<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$FILES_FOR_CHECK_ID	= 60; // id свойства материалы для проверки
$JSON_ID =  63; // id свойства JSON
$fileIds = [];
$ORDER_ID = $_REQUEST['orderID'];
$ACTION = $_REQUEST['action'];
$POSITION = $_REQUEST['position'];
$TOTAL_POSITION = $_REQUEST['totalPosition'];

if(!empty(current($_FILES)["size"] > 0)) {
    $order = \Bitrix\Sale\Order::load($ORDER_ID);
    $collection = $order->getPropertyCollection();
    $propertyValue = $collection->getItemByOrderPropertyId($FILES_FOR_CHECK_ID);
    // Сохранить файлы    
    $arFiles = prepareFiles(current($_FILES));
    
    foreach ($arFiles as $key => $file) {
        $fileId = CFile::SaveFile($file, 'filesCheck');
        $fileIds[]= $fileId; 
    }
    
    // установить значения с предыдущими 
    if(!empty($fileIds)) {
        $fileIds_current = getCurrentFiles($order);
        $fileIds_all =  array_merge($fileIds_current, $fileIds);
        // сохранить вместе с предыдущими файлами
        $addFile = $propertyValue->setField('VALUE', $fileIds_all);
        if (!$addFile->isSuccess()) {
            $res['status'] = false;
            $res['err'] = $addFile->getErrorMessages();
        } else {
            $res['addFile'] = $addFile;
        }
    }

    // Сохранить в виде json-позиции проверок. 
    $arFilesCheck = getArrJSON($order);
    $diff = $TOTAL_POSITION - count($arFilesCheck);
    // добавить недостающие позиции
    if( $diff > 0 ) {
        for ($i=$diff; $i < $TOTAL_POSITION; $i++) {     
            $arFilesCheck[$i] = (object) array('filesIds' => [], 'isChecked' => false);
        }
    }
    // обновить позицию материалов проверок
    $arFilesCheck[$POSITION]->filesIds = $fileIds;
    // $arFilesCheck[$POSITION]->isChecked = true;
    updJSON($order, $arFilesCheck); 

    // сохранить изменения в заказе
    $resSaveFiles = $order->save();
    if (!$resSaveFiles->isSuccess()) {
        $res['status'] = false;
        $res['err'] = $resSaveFiles->getErrorMessages();
    } 
    
} 


function prepareFiles($files) {
    $arFiles = [];
    $i = 0;
    foreach($files as $name => $value) {
        $i = 0;
        foreach($value as $key => $subValue) {
            $i+=1;
            $arFiles[$i][$name] = $subValue;
        }
    }
    return $arFiles;
}

function getArrJSON($order){
    global $TOTAL_POSITION;
    $PROP_ID = 63;
    $collection = $order->getPropertyCollection();
    $propertyValue = $collection->getItemByOrderPropertyId($PROP_ID);
    $json = $propertyValue->getField('VALUE');
    if (empty($json)) {
        $json = getEmptyJSON($TOTAL_POSITION);
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

// получить id текущих файлов материалов проверок
function getCurrentFiles($order){
    global $FILES_FOR_CHECK_ID; 
    $collection = $order->getPropertyCollection();
    $propertyValue = $collection->getItemByOrderPropertyId($FILES_FOR_CHECK_ID);
    $filesCurrent = $propertyValue->getField('VALUE');

    $fileIdsCurrent = [];
    foreach ($filesCurrent as $key => $file) {
        $fileIdsCurrent[]= $file['ID'];
    }
    return $fileIdsCurrent;
}

$res = ['request'=>$_REQUEST, 'FILES' => $_FILES, "fileIds"=>$fileIds, "POST"=>$_POST, 'arFiles' => $arFiles, 'fileIds_all' => $fileIds_all, "res"=>$res];
echo json_encode($res);

?>