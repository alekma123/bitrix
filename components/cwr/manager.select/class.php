<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


class AssignoFieldManagerComponent extends CBitrixComponent {

    private $_optionName = 'allowed_users';
    /**
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        /**
         *  @global CUser $USER
         *  @global CMain $APPLICATION
         *  @global CUserTypeManager $USER_FIELD_MANAGER
         */
        global $USER, $USER_FIELD_MANAGER;
        $this->setFrameMode(false);

        Loader::includeModule("crm");
        Loader::includeModule("intranet");


        $selectedEntities = "";

        $fieldOptions = CUtil::JsObjectToPhp(Option::get('main', $this->_optionName));
//        echo "<pre>";
//        var_dump($fieldOptions);
//        echo "</pre>";

        foreach ($fieldOptions['users'] as $u)
            $selectedEntities .= "['user', {$u}],";
        foreach ($fieldOptions['dep_with_users_only'] as $d)
            $selectedEntities .= "['department', '{$d}:F'],";
        foreach ($fieldOptions['dep_with_subdeps_users'] as $d)
            $selectedEntities .= "['department', {$d}],";

        $this->arResult['selected'] = $selectedEntities;
        $this->IncludeComponentTemplate('.default');
    }



    public function ajaxAction(\Bitrix\Main\HttpRequest $request)
    {
        Option::delete('main', ['name'=>$this->_optionName]);
        $action = $request->getPost('action');
        $elements = $request->getPost('data');
        $opt_array = [];
        if($elements['user'])
            foreach ($elements['user'] as $uid)
                $opt_array['users'][] = $uid;

        if($elements['dep'])
            foreach ($elements['dep'] as $depid) {
                if(strstr($depid, ":")) {
                    $a = explode(":", $depid);
                    $opt_array['dep_with_users_only'][] = reset($a);
                } else
                    $opt_array['dep_with_subdeps_users'][] = $depid;
            }

        echo Option::set('main', $this->_optionName, json_encode($opt_array));
//        echo Option::get('main', $this->_optionName);
        return ['result'=>'success'];
    }

}