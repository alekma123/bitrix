<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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
$this->setFrameMode(false);
?>

<div class="page-adv">
	<div class="container">
		<?if (!empty($arResult["ERRORS"])):?>
			<?ShowError(implode("<br />", $arResult["ERRORS"]))?>
		<?endif;
		if ($arResult["MESSAGE"] <> ''):?>
			<?ShowNote($arResult["MESSAGE"])?>
		<?endif?>

		<div class="form-header">
			<h1>Новое объявление</h1>
		</div>
		<form class="form" id="form-ads" name="iblock_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
			<?=bitrix_sessid_post()?>
			<?if ($arParams["MAX_FILE_SIZE"] > 0):?><input type="hidden" name="MAX_FILE_SIZE" value="<?=$arParams["MAX_FILE_SIZE"]?>" /><?endif?>
			<div class="data-form">
				<?if (is_array($arResult["PROPERTY_LIST"]) && !empty($arResult["PROPERTY_LIST"])):?>
				<div class="form-body">
					<?foreach ($arResult["PROPERTY_LIST"] as $propertyID):?>
						<div <?if($propertyID == '115') echo "style='display:none;'" ?> class="form-row" <?if($arResult["PROPERTY_LIST_FULL"][$propertyID]['HIDE']) echo 'hidden'?>>
							<label for="">
								<?if (intval($propertyID) > 0):?>
									<?=$arResult["PROPERTY_LIST_FULL"][$propertyID]["NAME"]?>
									<? $CODE = $arResult["PROPERTY_LIST_FULL"][$propertyID]["CODE"]?>
								<?else:?>
									<?=!empty($arParams["CUSTOM_TITLE_".$propertyID]) ? $arParams["CUSTOM_TITLE_".$propertyID] : GetMessage("IBLOCK_FIELD_".$propertyID)?>
									<? $CODE = $propertyID?>
								<?endif?>
								<?if(in_array($propertyID, $arResult["PROPERTY_REQUIRED"])):?>
									<span class="starrequired">*</span>
								<?endif?>
								</label>	
							<?$INPUT_TYPE = $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"];?>
							
							<div>
								<?
								if (intval($propertyID) > 0)
								{
									if (
										$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "T"
										&&
										$arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] == "1"
									)
										$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "S";
									elseif (
										(
											$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "S"
											||
											$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "N"
										)
										&&
										$arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] > "1"
									)
										$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "T";
								}
								elseif (($propertyID == "TAGS") && CModule::IncludeModule('search'))
									$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "TAGS";

								if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y")
								{
									if ($arResult["ERRORS"] == false) $arResult["ERRORS"] = [];
									if ($arResult["ELEMENT_PROPERTIES"][$propertyID] == false) $arResult["ELEMENT_PROPERTIES"][$propertyID] = [];

									$inputNum = ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) ? count($arResult["ELEMENT_PROPERTIES"][$propertyID]) : 0;
									$inputNum += $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE_CNT"];
								}
								else
								{
									$inputNum = 1;
								}

								if($arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"])
									$INPUT_TYPE = "USER_TYPE";
								else
									$INPUT_TYPE = $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"];

								switch ($INPUT_TYPE):
									case "USER_TYPE":
										for ($i = 0; $i<$inputNum; $i++)
										{
											if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
											{
												$value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["~VALUE"] : $arResult["ELEMENT"][$propertyID];
												$description = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["DESCRIPTION"] : "";
											}
											elseif ($i == 0)
											{
												$value = intval($propertyID) <= 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
												$description = "";
											}
											else
											{
												$value = "";
												$description = "";
											}
											echo call_user_func_array($arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"],
												array(
													$arResult["PROPERTY_LIST_FULL"][$propertyID],
													array(
														"VALUE" => $value,
														"DESCRIPTION" => $description,
													),
													array(
														"VALUE" => "PROPERTY[".$propertyID."][".$i."][VALUE]",
														"DESCRIPTION" => "PROPERTY[".$propertyID."][".$i."][DESCRIPTION]",
														"FORM_NAME"=>"iblock_add",
													),
												));
										?><br /><?
										}
									break;
									case "TAGS":
										$APPLICATION->IncludeComponent(
											"bitrix:search.tags.input",
											"",
											array(
												"VALUE" => $arResult["ELEMENT"][$propertyID],
												"NAME" => "PROPERTY[".$propertyID."][0]",
												"TEXT" => 'size="'.$arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"].'"',
											), null, array("HIDE_ICONS"=>"Y")
										);
										break;
									case "HTML":
										$LHE = new CHTMLEditor;
										$LHE->Show(array(
											'name' => "PROPERTY[".$propertyID."][0]",
											'id' => preg_replace("/[^a-z0-9]/i", '', "PROPERTY[".$propertyID."][0]"),
											'inputName' => "PROPERTY[".$propertyID."][0]",
											'content' => $arResult["ELEMENT"][$propertyID],
											'width' => '100%',
											'minBodyWidth' => 350,
											'normalBodyWidth' => 555,
											'height' => '200',
											'bAllowPhp' => false,
											'limitPhpAccess' => false,
											'autoResize' => true,
											'autoResizeOffset' => 40,
											'useFileDialogs' => false,
											'saveOnBlur' => true,
											'showTaskbars' => false,
											'showNodeNavi' => false,
											'askBeforeUnloadPage' => true,
											'bbCode' => false,
											'siteId' => SITE_ID,
											'controlsMap' => array(
												array('id' => 'Bold', 'compact' => true, 'sort' => 80),
												array('id' => 'Italic', 'compact' => true, 'sort' => 90),
												array('id' => 'Underline', 'compact' => true, 'sort' => 100),
												array('id' => 'Strikeout', 'compact' => true, 'sort' => 110),
												array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 120),
												array('id' => 'Color', 'compact' => true, 'sort' => 130),
												array('id' => 'FontSelector', 'compact' => false, 'sort' => 135),
												array('id' => 'FontSize', 'compact' => false, 'sort' => 140),
												array('separator' => true, 'compact' => false, 'sort' => 145),
												array('id' => 'OrderedList', 'compact' => true, 'sort' => 150),
												array('id' => 'UnorderedList', 'compact' => true, 'sort' => 160),
												array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
												array('separator' => true, 'compact' => false, 'sort' => 200),
												array('id' => 'InsertLink', 'compact' => true, 'sort' => 210),
												array('id' => 'InsertImage', 'compact' => false, 'sort' => 220),
												array('id' => 'InsertVideo', 'compact' => true, 'sort' => 230),
												array('id' => 'InsertTable', 'compact' => false, 'sort' => 250),
												array('separator' => true, 'compact' => false, 'sort' => 290),
												array('id' => 'Fullscreen', 'compact' => false, 'sort' => 310),
												array('id' => 'More', 'compact' => true, 'sort' => 400)
											),
										));
										break;
									case "T":
										for ($i = 0; $i<$inputNum; $i++)
										{

											if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
											{
												$value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
											}
											elseif ($i == 0)
											{
												$value = intval($propertyID) > 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
											}
											else
											{
												$value = "";
											}
										?>
								<textarea id="<?=$CODE?>" cols="<?=$arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"]?>" rows="<?=$arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"]?>" name="PROPERTY[<?=$propertyID?>][<?=$i?>]" <?if (in_array($propertyID, $arResult['PROPERTY_REQUIRED'])) echo "required"?> ><?=$value?></textarea>
										<?
										}
									break;

									case "S":
									case "N":
										for ($i = 0; $i<$inputNum; $i++)
										{
											if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
											{
												$value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
											}
											elseif ($i == 0)
											{
												$value = intval($propertyID) <= 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];

											}
											else
											{
												$value = "";
											}
										?>
										<?if ($CODE == 'PERFORMANCE'):?>
											<div class="flex-box flex-no-wrap">
												<input id="<?=$CODE?>" type="number" name="PROPERTY[<?=$propertyID?>][<?=$i?>]" size="<?=$arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"]; ?>" value="<?=$value?>" min="0"  <?if (in_array($propertyID, $arResult['PROPERTY_REQUIRED'])) echo "required"?> />
												<!-- <input type="text" id="UF_PERFORMANCE_MEASURE" name="PROPERTY[UF_PERFORMANCE_MEASURE]"  class="measure" disabled > -->
												<span id="UF_PERFORMANCE_MEASURE" class="measure"></span>

											</div>
										<?else:?>	
											<input id="<?=$CODE?>" type="text" name="PROPERTY[<?=$propertyID?>][<?=$i?>]" size="<?=$arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"]; ?>" value="<?=$value?>" <?if($CODE == 'UF_ENERGY_EFFICIENCY' ) echo 'disabled'?>  <?if (in_array($propertyID, $arResult['PROPERTY_REQUIRED'])) echo "required"?> />
										<?endif?>
										<br />
										<?
										if($arResult["PROPERTY_LIST_FULL"][$propertyID]["USER_TYPE"] == "DateTime"):?><?
											$APPLICATION->IncludeComponent(
												'bitrix:main.calendar',
												'',
												array(
													'FORM_NAME' => 'iblock_add',
													'INPUT_NAME' => "PROPERTY[".$propertyID."][".$i."]",
													'INPUT_VALUE' => $value,
												),
												null,
												array('HIDE_ICONS' => 'Y')
											);
											?><br /><small><?=GetMessage("IBLOCK_FORM_DATE_FORMAT")?><?=FORMAT_DATETIME?></small><?
										endif
										?><br /><?
										}
									break;

									case "F":
										for ($i = 0; $i<$inputNum; $i++)
										{
											$value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
											?>
								<input  type="hidden" name="PROPERTY[<?=$propertyID?>][<?=$arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i?>]" value="<?=$value?>" />
								<input type="file" size="<?=$arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"]?>"  name="PROPERTY_FILE_<?=$propertyID?>_<?=$arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i?>" /><br />
											<?

											if (!empty($value) && is_array($arResult["ELEMENT_FILES"][$value]))
											{
												?>
							<input id="<?=$CODE?>" type="checkbox" name="DELETE_FILE[<?=$propertyID?>][<?=$arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i?>]" id="file_delete_<?=$propertyID?>_<?=$i?>" value="Y" /><label for="file_delete_<?=$propertyID?>_<?=$i?>"><?=GetMessage("IBLOCK_FORM_FILE_DELETE")?></label><br />
												<?

												if ($arResult["ELEMENT_FILES"][$value]["IS_IMAGE"])
												{
													?>
							<img src="<?=$arResult["ELEMENT_FILES"][$value]["SRC"]?>" height="<?=$arResult["ELEMENT_FILES"][$value]["HEIGHT"]?>" width="<?=$arResult["ELEMENT_FILES"][$value]["WIDTH"]?>" border="0" /><br />
													<?
												}
												else
												{
													?>
							<?=GetMessage("IBLOCK_FORM_FILE_NAME")?>: <?=$arResult["ELEMENT_FILES"][$value]["ORIGINAL_NAME"]?><br />
							<?=GetMessage("IBLOCK_FORM_FILE_SIZE")?>: <?=$arResult["ELEMENT_FILES"][$value]["FILE_SIZE"]?> b<br />
							[<a href="<?=$arResult["ELEMENT_FILES"][$value]["SRC"]?>"><?=GetMessage("IBLOCK_FORM_FILE_DOWNLOAD")?></a>]<br />
													<?
												}
											}
										}

									break;
									case "L":

										if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["LIST_TYPE"] == "C")
											$type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "checkbox" : "radio";
										else
											$type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "multiselect" : "dropdown";

										switch ($type):
											case "checkbox":
											case "radio":
												echo "<div class='flex-box'>";
												foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum)
												{
													$checked = false;
													if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
													{
														if (is_array($arResult["ELEMENT_PROPERTIES"][$propertyID]))
														{
															foreach ($arResult["ELEMENT_PROPERTIES"][$propertyID] as $arElEnum)
															{
																if ($arElEnum["VALUE"] == $key)
																{
																	$checked = true;
																	break;
																}
															}
														}
													}
													else
													{
														if ($arEnum["DEF"] == "Y") $checked = true;
													}

													?>
													
													<div class="form-row-psevdo simple">
														<input type="<?=$type?>" name="PROPERTY[<?=$propertyID?>]<?=$type == "checkbox" ? "[".$key."]" : ""?>" value="<?=$key?>" id="property_<?=$key?>"<?=$checked ? " checked=\"checked\"" : ""?>  <?if (in_array($propertyID, $arResult['PROPERTY_REQUIRED'])) echo "required"?> />
														<div class="psevdo-button"></div>
														<label for="property_<?=$key?>"><?=$arEnum["VALUE"]?></label>
													</div>
													<?
												}
												echo "</div>";
											break;

											case "dropdown":
											case "multiselect":
											?>
											<?if ($CODE == 'IBLOCK_PARENT_SECTION' || $CODE == 'IBLOCK_SECTION') $type="single";?>
									<select <?if($CODE == '115') echo 'disabled'?> id="<?=$CODE?>" id="<?=$CODE?>" name="PROPERTY[<?=$propertyID?>]<?=$type=="multiselect" ? "[]\" size=\"".$arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"]."\" multiple=\"multiple" : ""?>"  <?if (in_array($propertyID, $arResult['PROPERTY_REQUIRED'])) echo "required"?> >
										<?if ($CODE == 'IBLOCK_PARENT_SECTION' || $CODE == 'IBLOCK_SECTION' && empty($arResult["ELEMENT"]["IBLOCK_SECTION"]) ):?>
											<option value=""><?echo GetMessage("CT_BIEAF_PROPERTY_VALUE_NA_$propertyID")?></option>
										<?endif?>	
											<?
												if (intval($propertyID) > 0) $sKey = "ELEMENT_PROPERTIES";
												else $sKey = "ELEMENT";

												foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum)
												{
													$checked = false;
													if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
													{
														foreach ($arResult[$sKey][$propertyID] as $elKey => $arElEnum)
														{
															if ($key == $arElEnum["VALUE"])
															{
																$checked = true;
																break;
															}
															
														}
													}
													else
													{
														if ($arEnum["DEF"] == "Y") $checked = true;
													}
													?>
										<? if ( $CODE == 'IBLOCK_PARENT_SECTION' && $arResult['PROPERTY_LIST_FULL']['IBLOCK_PARENT_SECTION']['SELECTED'] == $key) {
											$checked = true;
										}?>
										<option data-xml="<?=$arEnum["XML_ID"]?>" value="<?=$key?>" <?=$checked ? " selected=\"selected\"" : ""?>><?=$arEnum["VALUE"]?></option>
													<?
												}
											?>
									</select>
											<?
											break;

										endswitch;
									break;
								endswitch;?>
							</div>
							</div>
					<?endforeach;?>
					<?if($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0):?>
						<div>
							<div><?=GetMessage("IBLOCK_FORM_CAPTCHA_TITLE")?></div>
							<div>
								<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
								<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
							</div>
						</div>
						<div>
							<div><?=GetMessage("IBLOCK_FORM_CAPTCHA_PROMPT")?><span class="starrequired">*</span>:</div>
							<div><input type="text" name="captcha_word" maxlength="50" value=""></div>
						</div>
					<?endif?>

					<?#Блок с лимитами?>
					<div class="container-limits">
						<div class="wrapper-input">
							<label>Общее количество *</label>
							<div class="field-input">
								<input type="number" name="PROPERTY[AVAILABLE_QUENTITY]" required size="30" value="<?=$arResult['CATALOG']['AVAILABLE_QUENTITY']?>" min=0>
							</div>
						</div>

						<div class="deal-pay-check">
                        Укажите стоимость за единицу товара для каждого лимита
                    	</div>
						
						<div class="wrapper-input">
							<label>Первый лимит</label>
							<div class="field-input">
								<?$propertyID = $arResult['payment']['props']['ID']?>
								<?$type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "multiselect" : "dropdown";?>
								<select id="PAYMENT_METHOD" required name="PROPERTY[<?=$propertyID?>]<?=$type=="multiselect" ? "[]\" size=\"".$arResult["PROPERTY_LIST_FULL"]["$propertyID"]["ROW_COUNT"]."\" multiple=\"multiple" : ""?>">
								<? $name = strtolower($arResult["PROPERTY_LIST_FULL"]["$propertyID"]["NAME"]); ?>
									<option class="form-row-psevdo simple def" value=""><label><?=GetMessage("CT_BIEAF_PROPERTY_VALUE_NA_PAYMENT") ?></label></option>
										<?
											$sKey = "ELEMENT_PROPERTIES";

											foreach ($arResult["PROPERTY_LIST_FULL"]["$propertyID"]["ENUM"] as $key => $arEnum)
											{
												$checked = false;
												if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
												{
													foreach ($arResult[$sKey]["$propertyID"] as $elKey => $arElEnum)
													{
														if ($key == $arElEnum["VALUE"])
														{
															$checked = true;
															break;
														}
													}
												}
												else
												{
													if ($arEnum["DEF"] == "Y") $checked = true;
												}
												?>
									<option class="form-row-psevdo simple ml-16" value="<?=$key?>" <?=$checked ? " selected=\"selected\"" : ""?>> 
										<?=$arEnum["VALUE"]?>
									</option>
												<?
											}
										?>
								</select>
							</div>
						</div>

						<div class="wrapper-input">
							<div class="field-input limits">
								<input type="hidden" name="PROPERTY[EXT_PRICE][0][ID]" value="<?=$arResult['CATALOG']['EXT_PRICES'][0]['ID']?>" >
								<div class="range">
									<input required data-limit='0' class="range-from" type="number" name="PROPERTY[EXT_PRICE][0][QUANTITY_FROM]" placeholder="от" value="<?=$arResult['CATALOG']['EXT_PRICES'][0]['QUANTITY_FROM']?>" min="1">
									<span class="dash">—</span>
									<input type="number" data-limit='0' class="range-to" name="PROPERTY[EXT_PRICE][0][QUANTITY_TO]" placeholder="до" value="<?=$arResult['CATALOG']['EXT_PRICES'][0]['QUANTITY_TO']?>" min="1">
								</div>
								<input required class="price" name="PROPERTY[EXT_PRICE][0][VALUE]" type="text" placeholder="USDT" value="<?=$arResult['CATALOG']['EXT_PRICES'][0]['PRICE']?>">
							</div>
						</div>

						<div class="wrapper-input">
							<label>Второй лимит</label>
							<div class="field-input limits">
								<input type="hidden" name="PROPERTY[EXT_PRICE][1][ID]" value="<?=$arResult['CATALOG']['EXT_PRICES'][1]['ID']?>" >
								<div class="range">
									<input data-limit='1' class="range-from" type="number" name="PROPERTY[EXT_PRICE][1][QUANTITY_FROM]" placeholder="от" value="<?=$arResult['CATALOG']['EXT_PRICES'][1]['QUANTITY_FROM']?>" min="1" >
									<span class="dash">—</span>
									<input data-limit='1' class="range-to" type="number" name="PROPERTY[EXT_PRICE][1][QUANTITY_TO]" placeholder="до"  value="<?=$arResult['CATALOG']['EXT_PRICES'][1]['QUANTITY_TO']?>" min="1">
								</div>
								<input class="price" name="PROPERTY[EXT_PRICE][1][VALUE]" type="text" placeholder="USDT" value="<?=$arResult['CATALOG']['EXT_PRICES'][1]['PRICE']?>">
							</div>
						</div>
						<div class="wrapper-input">
							<label>Третий лимит</label>
							<div class="field-input limits">
								<input type="hidden" name="PROPERTY[EXT_PRICE][2][ID]" value="<?=$arResult['CATALOG']['EXT_PRICES'][2]['ID']?>" >
								<div class="range">
									<input data-limit='2' class="range-from" type="number" name="PROPERTY[EXT_PRICE][2][QUANTITY_FROM]" placeholder="от"  value="<?=$arResult['CATALOG']['EXT_PRICES'][2]['QUANTITY_FROM']?>" min="1">
									<span class="dash">—</span>
									<input data-limit='2' class="range-to" type="number" name="PROPERTY[EXT_PRICE][2][QUANTITY_TO]" placeholder="до"  value="<?=$arResult['CATALOG']['EXT_PRICES'][2]['QUANTITY_TO']?>" min="1">
								</div>
								<input class="price" name="PROPERTY[EXT_PRICE][2][VALUE]" type="text" placeholder="USDT" value="<?=$arResult['CATALOG']['EXT_PRICES'][2]['PRICE']?>">
							</div>
						</div>

					</div>

					<?if($arResult['location'])?>
					<? 	$propertyID = $arResult['location']['props']['ID'];
						$type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "multiselect" : "dropdown";
					?>
					<div class="form-row">
						<label><?=$arResult["PROPERTY_LIST_FULL"][$propertyID]["NAME"]?><?if(in_array($propertyID, $arResult["PROPERTY_REQUIRED"])):?>
									<span class="starrequired">*</span>
								<?endif?></label>
						<div class="field-input">
							<select name="PROPERTY[<?=$propertyID?>]<?=$type=="multiselect" ? "[]\" size=\"".$arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"]."\" multiple=\"multiple" : ""?>"  <?if (in_array($propertyID, $arResult['PROPERTY_REQUIRED'])) echo "required"?> >
							<? $name = strtolower($arResult["PROPERTY_LIST_FULL"]["$propertyID"]["NAME"]); ?>
								<option value=""><?=GetMessage("CT_BIEAF_PROPERTY_VALUE_NA_$propertyID") ?></option>
									<?
										$sKey = "ELEMENT_PROPERTIES";

										foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum)
										{
											$checked = false;
											if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0)
											{
												foreach ($arResult[$sKey][$propertyID] as $elKey => $arElEnum)
												{
													if ($key == $arElEnum["VALUE"])
													{
														$checked = true;
														break;
													}
												}
											}
											else
											{
												if ($arEnum["DEF"] == "Y") $checked = true;
											}
											?>
								<option value="<?=$key?>" <?=$checked ? " selected=\"selected\"" : ""?>><?=$arEnum["VALUE"]?></option>
											<?
										}
									?>
							</select>
						</div>
					</div>
				</div>
				<?endif?>
				<div>
					<?if ($_REQUEST['edit'] == 'Y'):?>
						<!-- <input class="btn btn_green" type="submit" name="iblock_apply" value="<?=GetMessage("IBLOCK_FORM_APPLY")?>" /> -->
						<button class="btn btn_green" type="submit" name="iblock_apply" value="<?=GetMessage("IBLOCK_FORM_APPLY")?>"> <?=GetMessage("IBLOCK_FORM_APPLY")?> </button>	
					<?else:?>
						<!-- <input class="btn btn_green" type="submit" name="iblock_submit" value="<?//=GetMessage("IBLOCK_FORM_SUBMIT")?>" /> -->
						<button class="btn btn_green" type="submit" name="iblock_submit" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>"> <?=GetMessage("IBLOCK_FORM_SUBMIT")?> </button>
					<?endif?>
				</div>
			</div>
		</form>
	</div>
</div>
<?
$USER_ID =$USER->GetID();
$rsUser = CUser::GetByID($USER_ID);
$arUser = $rsUser->Fetch();
?>

<script>
	BX.ready(function(){
		BX.ADS.FormComponent.init({
			AJAX_PATH: '<?="$templateFolder/ajax.php"?>',
			IBLOCK_ID: '<?=$arParams["IBLOCK_ID"]?>',
			LOGIN: '<?=$arUser['LOGIN']?>',
			SECTION_ID: '<?=empty($arResult["ELEMENT"]["IBLOCK_SECTION"]) ? '' : current($arResult["ELEMENT"]["IBLOCK_SECTION"])["VALUE"] ?>'
		}); 
	});
</script>


