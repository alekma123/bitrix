BX.ready(function(){
    //const items = getItems();
    const tagSelector = new BX.UI.EntitySelector.TagSelector({
        id: 'addresseeFilter',
        multiple: 'Y',
        //items: items,
        dialogOptions: {
            context: 'fields_addressee',
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
            onAfterTagAdd: function(event) {
                let tags = event.target.tags;
                let listTags = Array();
                tags.forEach(tag => {
                    let obj = { 
                        id: tag.id, 
                        entityId: tag.entityId, 
                        entityType: tag.entityType, 
                        title: tag.title,
                        avatar: tag.avatar 
                    };
                    listTags.push(obj);
                });
                //console.log('listTags.Add: ', listTags);
                let jsonTags = JSON.stringify(listTags);
                console.log('jsonTags.Add: ', jsonTags);
                BX.adjust(BX('json-addressee_filter'), {text: jsonTags});
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
                BX.adjust(BX('json-addressee_filter'), {text: jsonTags});
            }
        }
    });
    tagSelector.renderTo(document.querySelector(".chooseAddressee"));
});


/*
function getItems() {
    try {
        const json = BX("json-addressee_filter");
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

*/