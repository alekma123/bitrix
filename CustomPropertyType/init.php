<?
// ---- кастомный тип свойства для iblock -----
AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("IblockCustomProperty_ADDRESSEE", "GetUserTypeDescription"));
require_once(__DIR__ . "/CustomPropertyType/CUserTypeUserId.php");

class IblockCustomProperty_ADDRESSEE
{
    public static function GetUserTypeDescription()
    {
        $addressee = new \propertyAddressee\IblockAddressee();
        CJSCore::Init(array('bx'));
        return array(
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => "Addressee",
            "DESCRIPTION" => "Адресаты",
            "GetPropertyFieldHtml" => array($addressee, "GetPropertyFieldHtml"),
            // "GetPropertyFieldHtmlMulty" => array($addressee, "GetPropertyFieldHtml"),
            "GetSettingsHTML" => array($addressee, "GetSettingsHTML"),
            "GetPublicEditHTML" => array($addressee, "GetPublicEditHTML")
        );
    }
}
