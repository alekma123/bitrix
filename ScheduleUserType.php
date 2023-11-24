<?php

namespace SB\Site\CustomTypeField;

use \Bitrix\DocumentGenerator\Document;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Loader;
use Illuminate\Support\Str;
use SB\Tools\LoggerFactory;
use \Bitrix\Crm\Model\Dynamic\TypeTable;

class ScheduleUserType
{
    const USER_TYPE = 'Schedule';

    public function __construct ()
    {
        \Bitrix\Main\UI\Extension::load("ui.select");

        Loader::includeModule('main');
        Loader::includeModule('iblock');
        Loader::includeModule('crm');

        $eventManager = EventManager::getInstance();

        $eventManager->addEventHandler(
            'iblock', 'OnIBlockPropertyBuildList',
            [__CLASS__, 'getUserTypeDescription']
        );
        $eventManager->addEventHandler(
            'main', 'OnUserTypeBuildList',
            [__CLASS__, 'getUserTypeDescription']
        );
        $eventManager->addEventHandler(
            'crm', 'onCrmDynamicItemUpdate',
            [__CLASS__, 'saveFields']
        );
    }

    public static function getUserTypeDescription ()
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE'     => self::USER_TYPE,
            'DESCRIPTION'   => 'График',
            'BASE_TYPE'     => 'string',
            'CLASS_NAME'    => __CLASS__,
            'USER_TYPE_ID'  => self::USER_TYPE,

            "EDIT_CALLBACK" => [__CLASS__, 'getPublicEditHtml'],
            "VIEW_CALLBACK" => [__CLASS__, 'getPublicViewHtml'],
        ];
    }

    public static function saveFields (\Bitrix\Main\Event $event)
    {
        $item = $event->getParameter('item');
        self::writeLog('item', $item);
        self::writeLog('data', $item->getData);

        if ($item->getEntityTypeId() === self::getEntityType('SHIPPING_POINT')) {
            $schedule = $item->getUfCrm_4Schedule();
            $arrSchedule = json_decode($schedule);

            $arrFields = [
                "UF_CRM_4_DAY_ORDER_STR"    => array_column($arrSchedule, 'DAY_ORDER'),
                "UF_CRM_4_DAY_SHIPMENT_STR" => array_column($arrSchedule, 'DAY_SHIPMENT'),
                "UF_CRM_4_DAY_DELIVERY_STR" => array_column($arrSchedule, 'DAY_DELIVERY'),
                "UF_CRM_4_TRAVEL_TIME_STR"  => array_column($arrSchedule, 'TRAVEL_TIME'),
            ];
            foreach ($arrFields as $key => $fields) {
                $item->set($key, implode(' ', $fields));
            }

            $item->save();
        }
    }

    public static function getEntityType ($code): int
    {
        $entityTypeId = TypeTable::query()
                            ->where('CODE', $code)
                            ->addSelect('ENTITY_TYPE_ID')
                            ->fetch()['ENTITY_TYPE_ID'];

        return (int)$entityTypeId;
    }

    public static function writeLog ($event, $msg)
    {
        $fileName = "ScheduleUserType/ScheduleUserTypeSaveFields";
        LoggerFactory::get($fileName)->info($event, ['data' => $msg]);
    }

    public static function getdbcolumntype ($_)
    {
        return 'text(64000)';  // for mysql
    }

    /**
     * Return html for public view value.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @param array $controlSettings Form data.
     * @return string
     */
    public static function getAdminListViewHtml ($property, $value, $controlSettings = false) // fix for hl
    {
        return 'getAdminListViewHtml';
    }

    public static function getPublicEditHtml ($prop)
    {
        Asset::getInstance()->addJs('/local/assets/lib/vue.2.6.0.prod.min.js');
        $uid = 'r' . Str::random(6);
        $value = $prop['VALUE'] ?: '[]';
        $name = $prop['FIELD_NAME'];

        ob_start();

        ?>
        <div id="<?= $uid ?>">
            <table>
                <tr>
                    <th style="width: 27% !important">День заказа</th>
                    <th style="width: 27% !important">День отгрузки</th>
                    <th style="width: 27% !important">День доставки</th>
                    <th class="number">Время в пути</th>
                    <th style="width: 40px !important" class="remove"></th>
                </tr>
                <tr v-for="(row, index) of value">
                    <td>
                        <select v-model="row.DAY_ORDER">
                            <option v-for="day in days" :key="day.id">
                                {{ day.text }}
                            </option>
                        </select>
                    </td>
                    <td>
                        <select v-model="row.DAY_SHIPMENT">
                            <option v-for="day in days" :key="day.id">
                                {{ day.text }}
                            </option>
                        </select>
                    </td>
                    <td>
                        <select v-model="row.DAY_DELIVERY" required>
                            <option v-for="day in days" :key="day.id">
                                {{ day.text }}
                            </option>
                        </select>
                    </td>
                    <td>
                        <input type="number" min="0" v-model="row.TRAVEL_TIME" required>
                    </td>
                    <td class="remove">
                        <div class="ui-btn ui-btn-xs ui-btn-light ui-btn-icon-remove"
                             style="width: 100%"
                             @click="remove(index)"
                        ></div>
                    </td>
                </tr>
            </table>

            <div class="ui-btn ui-btn-xs ui-btn-light ui-btn-icon-add"
                 @click="add()"
            ></div>
            <input type="text"
                   :value="getSink()"
                   name="<?= $name ?>"
                   style="display: none"
                   ref="sink"
                   class="hiddenInput"
            >

        </div>

        <script>

            new Vue({
                el: '#<?= $uid ?>',
                data () {
                    return {
                        value: <?= $value ?>,
                        days: [
                            { id: 'not', text: 'нет' },
                            { id: 'mo', text: 'пн' },
                            { id: 'tu', text: 'вт' },
                            { id: 'we', text: 'ср' },
                            { id: 'th', text: 'чт' },
                            { id: 'fr', text: 'пт' },
                            { id: 'sa', text: 'сб' },
                            { id: 'su', text: 'вс' },
                        ],
                    }
                }
                ,
                mounted () {
                    this.value.length || this.add()
                }
                ,
                methods: {
                    add () {
                        this.value.push({
                            DAY_ORDER: '',
                            DAY_SHIPMENT: '',
                            DAY_DELIVERY: '',
                            TRAVEL_TIME: '',
                        })
                    }
                    ,
                    remove (index) {
                        this.value.splice(index, 1)
                    }
                    ,
                    getSink () {
                        BX.fireEvent(this.$refs['sink'], 'change')
                        let sink = JSON.stringify(this.value.filter(el => el.DAY_SHIPMENT))
                        console.log('value: ', this.value.filter(el => el))
                        return JSON.stringify(this.value.filter(el => el))
                    },

                },

            })

        </script>
        <style>

            <?= "#$uid" ?>
            {
                background: white
            ;
                box-shadow: 0 0 5px #3332
            ;
                font-family: OpenSans-Regular, sans-serif
            ;
                padding: 5px
            ;
            }
            <?= "#$uid" ?>
            table {
                width: 100%;
                border-collapse: collapse;
            }

            <?= "#$uid" ?>
            input {
                width: 100%;
                border: none;
                background: none;
                font-size: 15px;
            }

            <?= "#$uid" ?>
            th {
                width: calc((100% - 80px) / 2) !important;
                font-size: 10px;
            }

            <?= "#$uid" ?>
            input[type=number] {
                width: 100% !important;
            }

            <?= "#$uid" ?>
            td,
            <?= "#$uid" ?> th {
                border-bottom: 1px solid #3333;
                border-right: 1px solid #3333;
            }

            <?= "#$uid" ?>
            td.remove,
            <?= "#$uid" ?> th.remove {
                border-right: none;
            }

            <?= "#$uid" ?>
            tr:last-child td {
                border-bottom: none !important;
            }

            <?= "#$uid select"?>
            {
                outline: none
            ;
                background: white
            ;
                border: none
            ;
            }

        </style>
        <?php

        return ob_get_clean();
    }

    public static function getPublicViewHtml ($prop)
    {
        $value = array_map(
            fn ($el) => "
                <li style='list-style-type: \"-\"'> 
                    ${el['DAY_ORDER']}, 
                    <span style='color: #3337'>
                        ${el['DAY_SHIPMENT']}, ${el['DAY_DELIVERY']} ${el['TRAVEL_TIME']}
                    </span>
                </li>
            ",
            json_decode($prop['VALUE'], true) ?: []
        );

        return implode('<br>', $value);
    }

    /**
     * Check fields before inserting into the database.
     *
     * @param array $property Property data.
     * @param $value
     * @return array An empty array, if no errors.
     */
    public static function checkFields ($property, $value)
    {
        $msg = [];
        $rows = json_decode($value);
        $emptyCols = [];
        foreach ($rows as $row) {
            foreach ($row as $fieldName => $col) {
                if ($col == '') {
                    $emptyCols[] = self::getNameField($fieldName);
                }
            }
        }
        $emptyCols = array_unique($emptyCols);
        if (empty($emptyCols)) {
            return $msg;
        }

        $msg[] = [
            'id'   => $property['FIELD_NAME'],
            'text' => 'В поле График не заполнены поля: ' . implode(', ', $emptyCols),
        ];

        return $msg;
    }

    public static function getNameField ($codeName)
    {
        $arr = [
            "DAY_ORDER"    => 'День заказа',
            "DAY_SHIPMENT" => 'День отгрузки',
            "DAY_DELIVERY" => 'День доставки',
            "TRAVEL_TIME"  => 'Время в пути',
        ];

        return $arr[$codeName];
    }

    /**
     * Get the length of the value. Checks completion of mandatory.
     *
     * @param array $property Property data.
     * @param $value
     * @return int
     */
    public static function getLength ($property, $value)
    {
        return 0;
    }

    /**
     * Convert the property value into a format suitable for storage in a database.
     *
     * @param array $property Property data.
     * @param $value
     * @return mixed
     */
    public static function convertToDb ($property, $value)
    {
        return $value;
    }

    /**
     * Convert the value of properties suitable format for storage in a database in the format processing.
     *
     * @param array $property Property data.
     * @param $value
     * @return mixed
     */
    public static function convertFromDb ($property, $value)
    {
        return $value;
    }
}
