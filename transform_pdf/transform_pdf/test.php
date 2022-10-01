<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/customB24/Approval_stamp/BP/transform_pdf/index.php");

$rootActivity = $this->GetRootActivity();

$approvers= $rootActivity->GetVariable("approvers");
$voted = $rootActivity->GetVariable("vote");
$files = $rootActivity->GetVariable("file");
$object = $rootActivity->GetVariable("object");
$organization = $rootActivity->GetVariable("organization");

$extraData =  array(array("Объект", $object), array("Организация",$organization));

// --- Вывод файлов в лог
$files_str = outArray($files);
$this->WriteToTrackingService('Файлы: ' . $files_str);
// ----

foreach($files as $file) {
  renderStamp($file, $voted, $object, $organization, $extraData);
} 
if (count($voted) == 0) {
   $this->WriteToTrackingService("Нет согласовавших");
}

// --- Добавить штамп согласования
function renderStamp($file, $voted, $object, $organization, $extraData) {
  if (isset($file)) {
      $CFile = new CFile();
      $info_file = $CFile->MakeFileArray($file);
      //$info_file = CFile::MakeFileArray($file);
      // Вывод в лог
      $info_file_str = outArray($info_file );
      $this->WriteToTrackingService("info_file[$file]: ". $info_file_str );

      // --- Получить путь до файла  
      $path_file = getPathFile($info_file);
      $this->WriteToTrackingService("path_file[$file]: " . $path_file );

      // --- Run script
      if (count($voted) !== 0) { 
            $fileContent = init($path_file, $voted, $extraData);
            $resOverwrite = file_put_contents($path_file, $fileContent);

         // --- Лог перезаписи файла
         $this->WriteToTrackingService("resOverwrite[$file] : " . $resOverwrite);  
      } else {
         $this->WriteToTrackingService("Нет согласовавших");
      }

  } else {
    $this->WriteToTrackingService('Нет файла  "Счёт"');
  }
}

function outArray($arr) {
 foreach($arr as $key =>$val) {
    $str = $str . "[$key] = $val".'; ';
 }
  return $str;
}

function getPathFile($file) {
 $path = $file['tmp_name'];
 return $path;
}
?>