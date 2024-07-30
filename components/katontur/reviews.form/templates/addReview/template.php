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

\CJSCore::Init(array('fx', 'popup', 'ajax','masked_input', 'phone_number'));
?>
<script>
    BX.ready(
        function(){
            BX.Reviews.FormComponent.init({
                AJAX_PATH: '<?=CUtil::JSEscape("$templateFolder/ajax.php")?>'
            });
        }
    );
</script>

<div id="callback-msg" class="callback_msg">
    <div class="err"></div>
    <div class="success"><h2 class="success-text"></h2></div>
</div>

<div id="loading">
    <div class="text-center">
        <div class="spinner-border" role="status">
            <span class="sr-only"></span>
        </div>
    </div>
</div>
<?
global $USER;
$id = $USER->GetID();
$rsUser = CUser::GetByID($id);
$userFields = $rsUser->Fetch();

$name = $userFields['NAME'];
$phone = $userFields['PERSONAL_PHONE'];

?>
<form id="review-form" action="">
    <div class="form-content">
        <!-- Автор отзыва -->
        <input class="iputs" type="text" name="LINK_REVIEW_PERSON" placeholder="Автор отзыва" value="<?=$arResult['USER_ID']?>" hidden id="LINK_REVIEW_PERSON">
        <!-- Ссылка на тур -->
        <input class="iputs" type="text" name="LINK_REVIEW_TOUR" placeholder="Тур" hidden id="LINK_REVIEW_TOUR">

        <input class="iputs" type="text" name="NAME" placeholder="Имя*" required value="<?=$name?>" id="NAME">
        <input class="iputs" type="text" name="REVIEW_PHONE" placeholder="+7 ( ___ ) - __ - __ - ___ *" required value="<?=$phone?>" id="phone">
        <textarea class="iputs" placeholder="Комментарий*" required name="DETAL_TEXT" id='DETAL_TEXT' rows="6"></textarea>

        <h6 id="rate_text">Оцените тур</h6>
        <div class="rating-area" id="RATING_REVIEW">
            <?foreach($arResult['PROPS']['RATING_REVIEW']['ENAM_VAL'] as $key => $val):?>
                <input type="radio" id="star-<?=$val?>" name="RATING_REVIEW" value="<?=$key?>" <?if ($arResult['PROPS']['RATING_REVIEW']['DEFAULT_VALUE'] == $val) { echo "checked"; }?> >
                <label for="star-<?=$val?>" title="Оценка «<?=$val?>»"></label>
            <?endforeach?>
        </div>
        <div class="modal-footer" id="text_us_footer">
            <button type="submit" class="btn-accept">Отправить</button>
        </div>  
    </div>   
</form>

