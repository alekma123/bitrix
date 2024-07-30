
BX.namespace('BX.Personal.Section');
BX.Personal.Section = {
    init: function(parameters){
        this.siteId = parameters.siteId || '';
        this.ajaxUrl = parameters.AJAX_PATH || '';
        this.templateFolder = parameters.templateFolder || '';
        this.initAction(this.ajaxUrl);
    },
    initAction: function(ajaxUrl){
        $('#update-user-info').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                // url: $(this).attr('action'),
                url: ajaxUrl,
                type: 'POST',
                cache: false,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                enctype: 'multipart/form-data',
                success: function(result) {
                    console.log('person_photo: ', result.status);
                    if(result.status == 'success'){
                        window.location.reload();
                    }
                },
                error: function(err){
                    console.log('error: ', err);
                }
            });
        });

        $("#pen").on("change", function() {
            $("#update-user-info").submit();
        });

    }
}




class Tabs {

    constructor() {
      this.ids = ['now', 'like', 'past'];
      this.initBtns();
      this.initContents();
      this.display('now');
      this.initActions();
    }

    initBtns(){
        let btns = Object.create({});
        this.ids.forEach(id => {
            btns[`${id}`] = document.getElementById(`${id}_btn`);
        });
        this.btns = btns;
    }

    initContents(){
        let contents = Object.create({});
        this.ids.forEach(id => {
            contents[`${id}`] = document.getElementById(`${id}_cont`);
        });
        this.contents = contents;
    }

    display(idActive){
        this.ids.forEach(id => {
            if (id == idActive) {
                this.contents[id].classList.add('active');
                this.btns[id].classList.add('active');
            } else {
                this.contents[id].classList.remove('active');
                this.btns[id].classList.remove('active');
            }
        });
    }

    initActions(){
        let btns = Object.entries(this.btns);
        let id = '';
        let node = '';
        btns.forEach(btn => {
          id = btn[0];  
          node = btn[1]; 
          node.addEventListener('click', this.display.bind(this, id));
        });
    }
  }
  

