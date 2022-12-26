<?
namespace propertyAddressee;
use Bitrix\Main\Loader,
Bitrix\Main\Localization\Loc,
Bitrix\Iblock,
Bitrix\Main\Page\Asset,
\Bitrix\Intranet;


class IblockAddressee
{
    function __construct() {
        \Bitrix\Main\UI\Extension::load('ui.entity-selector');
    }


    public function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName){
        global $APPLICATION;
        // подключить js-скрипт
        $asset = Asset::getInstance();
        $dir = "/local/php_interface/CustomPropertyType";
        $asset->addJs($dir . '/chooseAddressee.js');
        
        // $jsonVal = json_encode($value['VALUE'], JSON_UNESCAPED_UNICODE); 
        $jsonVal = $value['VALUE']; 
        ob_start();
        ?>

        <textarea hidden id="json-addressee_hidden" rows="6" cols="75" name="<?=$strHTMLControlName["VALUE"] ?>">
            <?=$jsonVal?>
        </textarea>

        <?
        $hiddenJSON = ob_get_contents();
        ob_end_clean();
        $content = "
        <div class='json-addressee'>$hiddenJSON</div>
        <div class='chooseAddressee'></div>";

        return $content;
    }

 /*   
    public function GetPublicEditHTMLMulty($arProperty, $value, $strHTMLControlName) {
        $content = "<div>GetPublicEditHTML</div>";
        return $content;
    }
*/
    function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        /*
        $val = '[{"id":"all-users","entityId":"meta-user","entityType":"all-users","title":"Все сотрудники","avatar":""}]'; */

        $arPropertyFields = array(
            "HIDE" => array("MULTIPLE","ROW_COUNT", "COL_COUNT"),
            //"SET" => array("DEFAULT_VALUE" => $val)
        );
        return  '';
    }
    
    // --------------------------
    function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        // подключить js-скрипт
        $asset = Asset::getInstance();
        $dir = "/local/php_interface/CustomPropertyType";
        $asset->addJs($dir . '/chooseAddressee.js');
        
        $jsonVal = $value['VALUE']; 
        ob_start(); ?>
        
        <textarea hidden id="json-addressee_hidden" rows="6" cols="75" name="<?=$strHTMLControlName["VALUE"] ?>">
            <?=$jsonVal?>
        </textarea>
        
        <?
        $hiddenJSON = ob_get_contents();
        ob_end_clean();
        $content = "
        <div class='json-addressee'>$hiddenJSON</div>
        <div class='chooseAddressee'></div>";

        return $content;
    }

   
    function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $jsonVal = $value['VALUE']; 
        $arr = json_decode($jsonVal, true);
        $title = [];
        foreach ($arr as $key => $val) {
            $title[] = $val["title"];
        }
        $str = implode(", ", $title);

        return $str;    
    }

    function GetPublicFilterHTML($arProperty, $strHTMLControlName)
    {
        $name = $strHTMLControlName["VALUE"];

        $str = '[{"id":"all-users","entityId":"meta-user","entityType":"all-users","title":"Все сотрудники","avatar":""}]';
        $str = "test_input";
    
        // подключить js-скрипт

        //Asset::getInstance()->addString('<script type="module" src="/local/php_interface/CustomPropertyType/filterAddressee.js?ver'. time() .'"></script>');

        //$value = '[{"id":1586,"entityId":"user","entityType":"employee","title":"Ксения Кронштейн","avatar":"/upload/resize_cache/main/fea/100_100_2/avatar.png"},{"id":496,"entityId":"department","entityType":"default","title":"Коммерческий отдел","avatar":""}]';

        //$value = 'test';

        $content = '
         <input id="json-addressee_filter"  name="'. $strHTMLControlName["VALUE"] .'" value="'. $str .'" type="text">
         <!--<div class="chooseAddressee"></div>-->';
         
         return $content;

    }


    function ConvertToDB($arProperty, $value)
    {
        if(strlen($value["VALUE"])>0)
        {
            $val = $value["VALUE"];
            $value["VALUE"] = $val;
        }
        return $value;
    }

}
