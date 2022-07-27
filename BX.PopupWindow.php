<?CUtil::InitJSCore(array('window'));?>
<script>
var Dialog = new BX.CDialog({
	title: "Заголовок окна",
	head: 'Текст до формы',
	content: '<div>\
				<h4>TEST</h4>\
			</div>',
	icon: 'head-block',

	resizable: true,
	draggable: true,
	height: '168',
	width: '400',
	buttons: ['<input type="submit" value="Я посмотрел" />', BX.CDialog.btnSave, BX.CDialog.btnCancel, BX.CDialog.btnClose],
	//buttons: [BX.CDialog.btnClose] 
});

	//Dialog.Show();
</script>

<?CUtil::InitJSCore( array('ajax' , 'popup' ));?>
<script>
 var addAnswer = new BX.PopupWindow(
         "my_answer",                
          null, 
         {
            content: BX( 'ajax-add-answer'),
			 //closeIcon: {right: "20px", top: "10px" },
            titleBar: {content: BX.create("span", {html: '<b>Это заголовок окна</b>', 'props': {'className': 'access-title-bar'}})}, 
            zIndex: 0,
            offsetLeft: 0,
            offsetTop: 0,
			 //draggable: {restrict: false},
			overlay: {
			 backgroundColor: 'black', opacity: '80'
		  	},
			 buttons: [/*
               new BX.PopupWindowButton({
                  text: "Сохранить" ,
                  className: "popup-window-button-accept" ,
                  events: {click: function(){
                     this.popupWindow.close();
                  }}
			   }), */
               new BX.PopupWindowButton({
                  text: "Я прочитал" ,
                  className: "webform-button-link-cancel" ,
                  events: {click: function(){
                     this.popupWindow.close();
                  }}
               })
            ]
         });
	addAnswer.show();

	window.BXDEBUG = true;
	BX.ready(function(){
	   var oPopup = new BX.PopupWindow('call_feedback', window.body, {
		  autoHide : true,
		  offsetTop : 1,
		  offsetLeft : 0,
		  lightShadow : true,
		  closeIcon : true,
		  closeByEsc : true,
		  overlay: {
			 backgroundColor: 'red', opacity: '80'
		  }
	   }); 
/*
   oPopup.setContent(BX('hideBlock'));
   BX.bindDelegate(
      document.body, 'click', {className: 'css_popup' },
         BX.proxy(function(e){
            if(!e)
               e = window.event;
            oPopup.show();
            return BX.PreventDefault(e);
         }, oPopup)
   );*/
	//oPopup.show();
});

</script>
