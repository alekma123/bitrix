<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

\CBitrixComponent::includeComponentClass('katontur:personal.section');

$USER = new CUser;
$user_id = $USER->GetID();


if(!empty($_FILES["personal-photo"]['name']))
{
    $fileId = CFile::SaveFile($_FILES["personal-photo"],'avatar');
    $arFile = CFile::MakeFileArray ($fileId);
   // $arFile["MODULE_ID"] = "main";
    $arFile['del'] = "Y";
    $arFile['old_file'] = $_POST['old-photo-id'];
    $arFields['PERSONAL_PHOTO'] = $arFile;
}


$result = $USER->Update($user_id, $arFields);

if($result){
    $result = array(
        'status' => 'success',
        'msg' => 'Profile updated',
    );
} else {
    $result = array(
        'status' => 'err',
        'msg' => $result->getErrors(),
    );
}
echo json_encode($result);
?>