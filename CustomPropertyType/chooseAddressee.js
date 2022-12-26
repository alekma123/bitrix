BX.ready(function(){
    const items = getItems();
    const tagSelector = new BX.UI.EntitySelector.TagSelector({
        id: 'addressee',
        multiple: 'Y',
        items: items,
        dialogOptions: {
            context: 'fiels_addressee',
            entities: [
                {
                    id: 'user', // users
                },
                {
                    id: 'department', // company structure
                    options: {
                        selectMode: 'usersAndDepartments' // department and user selection
                    }
                },
                {
                    id: 'meta-user',
                    options: {
                        'all-users': true // All employees
                    }
                },
            ],
        },
        events: {
            onAfterTagAdd: async function(event) {
                let arrTags = event.target.tags;
                let listTags = Array();
                for(tag of arrTags) {
                    let obj = { 
                        id: tag.id, 
                        entityId: tag.entityId, 
                        entityType: tag.entityType, 
                        title: tag.title,
                        avatar: tag.avatar 
                    };
                    listTags.push(obj);
                    idDepart = tag.id;
                    // добавить подотделы выбранного отдела.
                    /*
                    if (tag.entityId == 'department') {
                        console.log('idDepart: ', idDepart);
                        let res = await getSubDepartment (idDepart);
                        let subSections = res['sections'];
                        console.log('subSections: ', subSections);
                        // добавить через цикл 
                        for( subSection of subSections ){
                            listTags.push(subSection);
                        }
                    } */
                }
                
                let jsonTags = JSON.stringify(listTags);
                BX.adjust(BX('json-addressee_hidden'), {text: jsonTags});
            },
            onAfterTagRemove: function(event){
                console.log("onAfterTagDelete");
                let tags = event.target.tags;
                let listTags = Array();
                tags.forEach(tag => {
                    let obj = { 
                        id:tag.id, 
                        entityId:tag.entityId, 
                        entityType:tag.entityType, 
                        title:tag.title,
                        avatar: tag.avatar 
                    };
                    listTags.push(obj);
                });
                
                let jsonTags = JSON.stringify(listTags);
                BX.adjust(BX('json-addressee_hidden'), {text: jsonTags});
            }
        }
    });
        // поле тегов
        tagSelector.renderTo(document.querySelector(".chooseAddressee")); 
});


function getItems() {
    try {
        const json = BX("json-addressee_hidden");
        let objStr = json.value.trim();
        let obj = JSON.parse(objStr);
        console.log('obj: ', obj);
        // obj = JSON.parse(obj);
        return obj;
    } catch (error) {
        console.error(error);
        return null;
    }    
}

/*
function getItems() {
    try {
        const json = BX("json-addressee_hidden");
        let objStr = json.value.trim();
        let obj = JSON.parse(objStr);
        console.log('obj: ', obj);
        obj = JSON.parse(obj);
        return obj;
    } catch (error) {
        console.error(error);
        return null;
    }    
} */

function getSubDepartment_old (idDepart){
    const PATH = "local/php_interface/CustomPropertyType";
    const DOMAIN = window.location.hostname;
    const url = `https://${DOMAIN}/${PATH}/getSubDepartment.php`;
    
    $.ajax({        
            url: url,         
            method: 'get',  
            data: { "idDepart" : idDepart},             
            dataType: 'json',          
            success: function(data) {   
                console.log("DATA: ", data);
            },
            error: function(err) {
                console.log("Error: ", err);
                let statusError =  err.status;
                console.log("statusError: ", statusError);
            },
            complete: function(data) {
                // window.location = url; 
                return "test_val";
            }
        });  

}


async function getSubDepartment (idDepart) {
    const PATH = "local/php_interface/CustomPropertyType";
    const DOMAIN = window.location.hostname;
    const url = `https://${DOMAIN}/${PATH}/getSubDepartment.php`;

    let json = null, res = null;

    let response = await fetch(`${url}?idDepart=${idDepart}`);
    if (response.ok) { 
        json = await response.json();
        res = json;
      } else {
        console.log('Error: ', response.status);
        res = response;
      }

    return res;;  
}