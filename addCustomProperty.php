<?php
AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("IblockCustom", "GetUserTypeDescription"));
class IblockCustom extends CUserTypeInteger
{
  public static function GetUserTypeDescription()
  {
      return array(
          "PROPERTY_TYPE" => "S",
          "USER_TYPE" => "stringDate",
          "DESCRIPTION" => "Кастомное свойство",
          "GetPublicViewHTML" => array(__CLASS__,"GetPublicViewHTML"),
          "GetPublicEditHTML" => array(__CLASS__,"GetPublicEditHTML"),
          "GetAdminListViewHTML" => array(__CLASS__,"GetAdminListViewHTML"),
          "GetPropertyFieldHtml" => array(__CLASS__,"GetPropertyFieldHtml"),
          "CheckFields" => array(__CLASS__,"CheckFields"),
          "ConvertToDB" => array(__CLASS__,"ConvertToDB"),
          "ConvertFromDB" => array(__CLASS__,"ConvertFromDB"),
          "GetSettingsHTML" => array(__CLASS__,"GetSettingsHTML"),
          "GetAdminFilterHTML" => array(__CLASS__,"GetAdminFilterHTML"),
          "GetPublicFilterHTML" => array(__CLASS__,"GetPublicFilterHTML"),
          "AddFilterFields" => array(__CLASS__,"AddFilterFields"),
          "BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
      );
  }
    // Отображение при редактировании элемента
    /*
    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $out = '<div class="feed-add-post-strings-blocks feed-add-post-destination-block">
        <input type="hidden" id="entity-selector-data-oPostFormLHE_blogPostForm" name="DEST_DATA" value="[]">
        <div id="entity-selector-oPostFormLHE_blogPostForm"><div class="ui-tag-selector-outer-container"><div class="ui-tag-selector-container"><div class="ui-tag-selector-items"><input type="text" class="ui-tag-selector-item ui-tag-selector-text-box ui-tag-selector-item-hidden" autocomplete="off" placeholder="" value=""><span class="ui-tag-selector-item ui-tag-selector-add-button"><span class="ui-tag-selector-add-button-caption">Добавить сотрудников, группы или отделы</span></span></div><div class="ui-tag-selector-create-button ui-tag-selector-item-hidden"><span class="ui-tag-selector-create-button-caption">Создать</span></div></div></div></div></div>';
    
        $FOLDER = __DIR__ . "/UserType/";
        //Asset::getInstance()->addJs($FOLDER . 'chooseAddressee.js');
        
        return $out;
    } */

    /*
    function GetEditFormHtml($arUserField, $arHtmlControl)
    {

        if(!$arUserField['VALUE']){
            $arHtmlControl['VALUE'] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
        } else {
            $arHtmlControl['VALUE'] = $arUserField['VALUE'];
        } 

        //CSS файлвы не захотели подключаться через Asset::getInstance()->addCss() поэтому подтягиваем
        // их через HTML загружаемый на странице редактирования свойства
        // $return = '	<link rel="stylesheet" href="' . APP_MEDIA_FOLDER .'css/colorpicker.css?v='. md5(date("h:i:s")) .'" type="text/css" />
        // <link rel="stylesheet" media="screen" type="text/css" href="' . APP_MEDIA_FOLDER .'css/layout.css?v='. md5(date("h:i:s")) .'" />';

        $FOLDER = __DIR__ . "/UserType/";
        Asset::getInstance()->addJs($FOLDER . 'chooseAddressee.js');
        $return = "<div>GetEditFormHTML</div>";

        return $return;
    } */

     /*--------- вывод поля свойства на странице редактирования ---------*/
     public function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
     {
         // переводим JSON хронящийся в поле в массив
         $jsonToArr = json_decode($value['VALUE'], true);
         $value['VALUE'] = $jsonToArr === null ? $value['VALUE'] : $jsonToArr;
         // включаем буфер
         ob_start();
         // выводим информацию
        ?>
         <div class="test">test</div>
 
         <script type='module' src='/local/php_interface/UserType/chooseAddressee.js
'></script>
 
        <?
         // сохраняем всё что есть в буфере в переменную $content
         $content = ob_get_contents();
         // отключаем и очищаем буфер
         ob_end_clean();
 
         return $content;
     }

}
?>
