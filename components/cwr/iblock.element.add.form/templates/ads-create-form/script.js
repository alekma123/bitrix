'use strict';
BX.namespace('BX.ADS.FormComponent');
BX.ADS.FormComponent= {
    energy_consumption_measure : 'Вт/Th',
    ids: {
        form: 'form-ads',
        name: 'NAME',
        code: 'CODE',
        manufacturer : 'IBLOCK_PARENT_SECTION', 
        model : 'IBLOCK_SECTION',
        performance: 'PERFORMANCE',
        energy_consumption: 'UF_ENERGY_EFFICIENCY',
        payment_method: 'PAYMENT_METHOD',
        performance_measure: 'UF_PERFORMANCE_MEASURE'
    },

    init: function(params){
        this.form = BX(this.ids['form']);
        this.ajaxUrl = params.AJAX_PATH;
        this.iblockId = params.IBLOCK_ID;
        this.login = params.LOGIN;
        this.selectedModelID = params.SECTION_ID;
        this.initAction();
        this.initPrimaryFields();
    },
    initPrimaryFields: function(){
        this.generateName('');
        this.generateCode('');
        this.initModel();
        this.initMethodsPayment();
        this.initLimis();
    },
    initAction: function(){
        $(`#${this.ids['form']} select`).selectric({ disableOnMobile: false, nativeOnMobile: false });
        this.bindSelectManufacturer();
        this.bindSelectModel();
        this.bindMethodsPayment();
    },
    initLimis: function(){
        let numberLimit = '';
        let valueLimit = '';
        let limitsFrom = document.querySelectorAll('input[data-limit].range-from');
        let limitsTo = document.querySelectorAll('input[data-limit].range-to');
        
        limitsTo.forEach(limit => {
            numberLimit = limit.dataset['limit'];
            valueLimit = limit.value;
            if (numberLimit == 0) {
                limitsFrom[1].min = parseInt(valueLimit) + 1; 
            }
            if(numberLimit == 1) {
                limitsFrom[2].min = parseInt(valueLimit) + 1; 
            }
            limit.addEventListener('change', function(){
                numberLimit = this.dataset['limit'];
                valueLimit = this.value;
                this.min = parseInt(limitsFrom[numberLimit].value);
                if (numberLimit == 0) {
                    limitsFrom[1].min = parseInt(valueLimit) + 1; 
                }
                if(numberLimit == 1) {
                    limitsFrom[2].min = parseInt(valueLimit) + 1; 
                }   
            })
        });
    },
    initMethodsPayment: function(){ 
        $(`#${this.ids['payment_method']}`).selectric({
            optionsItemBuilder: function(itemData, element, index) {
                if (itemData.value == '') {
                   return '';//`<label hidden>${itemData.text}</label>`;
                } 
                const customCheckbox = '<div class="psevdo-button psevdo-button_green checkbox"></div>';
                let html = `<input id="payment_${index}" type="checkbox" disabled/> ${customCheckbox} <label for="payment_${index}">` + itemData.text + '</label>';
                if (itemData.selected) {
                    html = `<input id="payment_${index}" type="checkbox" disabled checked /> ${customCheckbox} <label for="payment_${index}">` + itemData.text + '</label>'
                }
                return html;
            }
          });

    },
    initModel: function(){
        const data = { section_id : this.selectedModelID};
        const action = 'getModelProps';
        // $('select#PERFORMANCE_UNIT').selectric().prop( "disabled", true );
        this.sendRequest(action, data, this.refreshModelProps, this.fail);
    },
    // событие при выборе производителя из списка
    bindSelectManufacturer: function(){
        $(`select#${this.ids['manufacturer']}`).on('selectric-select', function(event, element, selectric) {
			const items = selectric.items;
			const selectedIdx = selectric.state.selectedIdx;
            const data = { section_id : items[selectedIdx]['value']};
            const action = 'getModel';
            this.sendRequest(action, data, this.refreshModels, this.fail);
        }.bind(this));       
    },
    // событие при выборе модели из списка
    bindSelectModel: function(){
        $(`select#${this.ids['model']}`).on('selectric-select', function(event, element, selectric) {
            const items = selectric.items;
            const selectedIdx = selectric.state.selectedIdx;
            const data = { section_id : items[selectedIdx]['value']};
            const action = 'getModelProps';
            this.sendRequest(action, data, this.refreshModelProps, this.fail);
        }.bind(this));
    },
    // событие при выборе способа оплаты
    bindMethodsPayment: function(){
        $(`select#${this.ids['payment_method']}`).on('selectric-select', function(event, element, selectric) {
            selectric.items.forEach(item => {
                const index = item.index;
                const checkbox = selectric.$li[index].querySelector('input[type=checkbox]');
                if (checkbox == null) { return; } 
                
                if (item.selected) {
                    selectric.$li[index].querySelector('input[type=checkbox]').checked=true;
                }
                else {
                    selectric.$li[index].querySelector('input[type=checkbox]').checked=false;
                }
            });
        });
    },
    // показать список моделей при выбранном производители
    refreshModels: function(data){
        let select = $(`select#${this.ids['model']}`); 
        select.empty();    

        var models = data.models;
        if (models.length == 0) {
            select.append($('<option>',{text: 'выберите модель', value: ''}));
        } else {
            models.forEach(model => {
                select.append($('<option>',{text: model['NAME'], value: model['ID']}));
            });
        }
        select.selectric('refresh');
        if (models.length > 0 ) {
            // показать свойства первого элемента списка моделей
            this.sendRequest('getModelProps', { section_id : models[0]['ID']}, this.refreshModelProps, this.fail);
            // сгенирировать название для объявления
            this.generateName(models[0]['NAME']);
            // сгенирировать символьный код для объявления
            this.generateCode(models[0]['NAME']);
        }
        else {
            this.generateName('');
            this.generateCode('');
        }
    
    },
    // генерация названия объявления
    generateName: function(modelName){
        // const name = modelName ? modelName + '_' + this.login : this.login;
        const name = modelName;
        $(`#${this.ids['name']}`).val(name) ;
    },
    // генерация символьного кода объявления
    generateCode: function(modelName){
        const name = modelName ? modelName + '_' + this.login : this.login;
        $(`#${this.ids['code']}`).val(name.toLowerCase()) ;
    },

    // показать свойства выбранной модели
    refreshModelProps: function(data) {
        const props = data['model'];
        if(!props) return;
        // энергоэффективность
        const energy_consumption = props['UF_ENERGY_EFFICIENCY'] ? props['UF_ENERGY_EFFICIENCY'] + ' ' + this.energy_consumption_measure: '';

        $(`#${this.ids['energy_consumption']}`).val(energy_consumption);
        // производительность
        //const performance = props['UF_PERFORMANCE'] && props['UF_PERFORMANCE_MEASURE'] ? props['UF_PERFORMANCE'] + ' ' + props['UF_PERFORMANCE_MEASURE'] : '';
        //$(`#${this.ids['performance']}`).val(performance);
        
        // производительность с диапазоном
        $(`#${this.ids['performance']}`).attr({
            "max" : props['UF_PERFORMANCE_TO'],        // производительность до
            "min" : props['UF_PERFORMANCE_FROM']       // производительность от
         });
        // единица измерения производительности
        $(`#${this.ids['performance_measure']}`).text(props['UF_PERFORMANCE_MEASURE']['VALUE']);
        // $(`#${this.ids['performance_measure']}`).val(props['UF_PERFORMANCE_MEASURE']);
        
        //поиск единицы производительности
        let selectInedx = 0;
        coll = document.getElementById('PERFORMANCE_UNIT').options;
        for (let index = 0; index < coll.length; index++) {
            const option = coll[index];            
            // if(option.text.includes(props['UF_PERFORMANCE_MEASURE']['VALUE'])) {
                console.log(`option.dataset['xml']: ${option.dataset['xml']}`);
                console.log(`props['UF_PERFORMANCE_MEASURE']['XML_ID'].toLowerCase(): ${props['UF_PERFORMANCE_MEASURE']['XML_ID'].toLowerCase()}`);
            if(option.dataset['xml'] == props['UF_PERFORMANCE_MEASURE']['XML_ID'].toLowerCase()) {
                selectInedx = index;
                break;
            }
        }
        $('select#PERFORMANCE_UNIT').selectric().prop('selectedIndex', selectInedx).selectric('refresh');  
        
        // генерация названия объявления
        const name = props['NAME'] ? props['NAME'] : '';
        this.generateName(name);
        this.generateCode(name);
  
    },

    prepareParams: function(params) {
        let str = '';
        for(var key in params) {
            str = str + '&' + key + '=' + params[key];
        }
        return str;
    },
    
  
    sendRequest: function(action, data, callback_1, callback_2)
    {
        data['sessid'] = BX.bitrix_sessid();
        data['action'] = action;
        data['iblock_id'] = this.iblockId;
        const params = this.prepareParams(data);
        BX.ajax({
            method: 'GET',
            dataType: 'json',
            url: `${this.ajaxUrl}?${params}`,
            data: data,
            onsuccess: BX.delegate(callback_1, this),
            onfailure: BX.delegate(callback_2, this) 
        }); 
    },
   
    success: function(data){
    },
    fail: function(err, status){
        console.error('err: ', err);
    }
}


