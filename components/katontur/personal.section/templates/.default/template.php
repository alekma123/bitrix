<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

 //$this->addExternalCss('/bitrix/css/main/bootstrap.css');

\CJSCore::Init(array('fx', 'popup', 'ajax','jquery'));
//Main\UI\Extension::load(['ui.mustache']);
//$this->addExternalJs($templateFolder.'/js/component.js');
$imgUser_def = SITE_TEMPLATE_PATH . "/assets/img/account_avatar_default.svg";
$imgUser = !empty($arResult['USER']['PERSONAL_PHOTO']['path']) ? $arResult['USER']['PERSONAL_PHOTO']['path']: $imgUser_def;

?>


<?if($USER->IsAuthorized()):?>
<div class="settings_tabs">
    <a href="settings.php"><img src="<?=$componentPath?>/images/settings.svg" alt="Настройки профиля">
        <h6>Настройки профиля</h6>
    </a>
    <a href="complaint.html">
        <h6>Жалоба</h6>
    </a>
    <a href="Выход">
        <h6>Выход</h6><img src="<?=$componentPath?>/images/exit.svg" alt="">
    </a>
</div>

<div class="photo">
    <div class="guid_info">
        <img id="profile_img" src="<?=$imgUser?>" alt="">
        <h1 id="name"> <?=$arResult['USER']['NAME']?> </h1>
        
        
        <form action="updatePersonalInfo.php" id="update-user-info" enctype="multipart/form-data">
            <input type="hidden" name="old-photo-id" value="<?php echo $arUser['PERSONAL_PHOTO']['file_id']?>">

            <input type="file" id="pen" class="file-input-photo" name="personal-photo">
        </form>

            <!-- <img id="pen" src="<?//=$componentPath?>/images/pen.svg" alt=""> -->
        
        <div class="achivments">
            <div class="done">
                <img src="<?=$componentPath?>/images/tourist.svg" alt="">
                <h5>Прошел:</h5>
                <h4><?=$arResult['INFO_VISITED_TOURES']['COUNT']?> тура</h4>
            </div>
            <div class="done">
                <img src="<?=$componentPath?>/images/direction.svg" alt="">
                <h5>Посетил:</h5>
                <h4><?=$arResult['INFO_VISITED_TOURES']['DIRECTIONS']?> направление</h4>
            </div>
        </div>
    </div>
</div>
<div class="notification">
    <div class="form-check form-switch">
        <label class="form-check-label" for="flexSwitchCheckDefault">Уведомлять о новых турах</label>
        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
    </div>
</div>
<div class="turs">
    <div class="tabs">
        <button id="now_btn" class="tabs_btn"> <h6>Текущие туры</h6> </button>
        <button id="like_btn" class="tabs_btn"> <h6>Избранное</h6> </button>
        <button id="past_btn" class="tabs_btn"> <h6>Прошлые туры</h6> </button>
    </div>
    <div class="container-contents">
        <div id="now_cont" class="content__turs closest_turs_scroll">
            <?include_once( __DIR__ . "/blocks/currentTours.php"); ?>
        </div>
        <div id="like_cont" class="content__turs closest_turs_scroll">
            <?include_once( __DIR__ . "/blocks/favourites.php"); ?>
        </div>
        <div id="past_cont" class="content__turs closest_turs_scroll">
            <?include_once( __DIR__ . "/blocks/pastTours.php"); ?>
        </div>
    </div>
</div>

<?else:?>
<?$APPLICATION->IncludeComponent(
	"bitrix:system.auth.form", 
	"login", 
	array(
		"FORGOT_PASSWORD_URL" => "/auth/forget.php",
		"PROFILE_URL" => "/personal/index.php",
		"REGISTER_URL" => "/auth/registration.php",
		"SHOW_ERRORS" => "N",
		"COMPONENT_TEMPLATE" => "login"
	),
	false
    );?>
<?endif?>



<script>
    BX.ready(function(){
        let tabs = new Tabs();
        BX.Personal.Section.init({
            siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
            AJAX_PATH: '<?=CUtil::JSEscape("$templateFolder/updatePersonalInfo.php")?>',
            templateFolder: '<?=CUtil::JSEscape($templateFolder)?>'
        }); 
        
    });
</script>