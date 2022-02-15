

/**
 * EntityContainer
 */
var EntityContainer = {
    _instance_list : {},

    getInstance : function(key){
        var el = this._instance_list[key];
        if(!el){
            return;
        }

        return el;
    },

    removeInstance : function(key){
        var el = this._instance_list[key];
        if(!el){
            return;
        }
        delete this._instance_list[key];
    },

    registerInstance : function(entity){
        this._instance_list[entity.getKey()] = entity;
    },

    registerEntityList : function(entity_list, parent_entity_key){
        return;
        if(!entity_list){
            return;
        }

        for(key in entity_list){
            var entity = entity_list[key];

            if(parent_entity_key){
                entity.parent_key = parent_entity_key;
            }

            Entity
                .createInstance(entity.key, entity.parent_key)
                .setEntityProperties(entity.properties);
        }
    },


}





/**
 * Entity
 */
var Entity = {
    _key : null,
    _parent_key : null,

    _entity_element_parent : null,
    _entity_element_children : {},

    _vars : null,
    _events : [],

    callbacks : {
        //destroy : null,
        //ready : null,

    },

    //createInstance
    createInstance : function(key, parent_key){
        if(!key){
            return;
        }

        var Obj = CloneObject.createInstance(Entity);

        Obj.setKey(key);
        Obj.setParentKey(parent_key);
        Obj.init()

        return Obj;
    },

    //getInstance
    getInstance : function(key){
        if(!key){
            return this;
        }

        var el = EntityContainer.getInstance(key)
        if(el){
            return el;
        }
    },

    //init
    init : function(){
        EntityContainer.registerInstance(this);

        this.setEntityParent();
        this.appendAsChildToParentEntity();
    },

    //eventRun
    /*
    events : {
        runByName : function(event_name){
            if(!this.parent._events){
                return this.parent;
            }

            for(key in this.parent._events){
                if(this.parent._events[key].action == event_name){
                    var func = this.parent._events[key].func;
                    eval(func+"(this.parent)")
                }
            }

            return this.parent;
        },
    },
    */

    //setKey
    setKey : function(key){
        this._key = key;
        return this;
    },

    //getKey
    getKey : function(){
        return this._key;
    },

    //setParentKey
    setParentKey : function(key){
        if(key){
            this._parent_key = key;
        }
        return this;
    },

    //getParentKey
    getParentKey : function(){
        return this._parent_key;
    },

    //setEntityParent
    setEntityParent : function(){
        var key = this.getParentKey();
        if(!key){
            return this;
        }

        var el = EntityContainer.getInstance(key)
        if(!el){
            return this;
        }

        this._entity_element_parent = el;

        return this;
    },

    //getEntityParent
    getEntityParent : function(){
        return this._entity_element_parent;
    },

    //getEntityParent
    issetEntityParent : function(){
        return Boolean(this._entity_element_parent);
    },

    //appendAsChildToParentEntity
    appendAsChildToParentEntity : function(){
        var key = this.getParentKey();
        if(!key){
            return this;
        }
        var el = EntityContainer.getInstance(key)
        if(!el){
            return this;
        }

        el.addEntityChildren(this);

        return this;
    },

    //addEntityChildren
    addEntityChildren : function(element){
        this._entity_element_children[element.getKey()] = element;
        return this;
    },

    //getEntityChild
    getEntityChild : function(key){
        if(this._entity_element_children[key]){
            return this._entity_element_children[key];
        }
    },

    //getEntityChildren
    getEntityChildren : function(){
        return this._entity_element_children;
    },

    //removeEntityChildren
    removeEntityChildren : function(key){
        if(this._entity_element_children[key]){
            delete this._entity_element_children[key];
        }
        return this;
    },

    //destroy
    destroy : function(destroy_only){
        if((destroy_only === false || typeof destroy_only == 'undefined') && typeof this.callbacks.destroy == 'function'){
            var callback = this.callbacks.destroy;
            this.callbacks.destroy = null;
            callback(this);
        }

        this.destroyChildren(destroy_only);
        this.destroyChildrenInParent();

        this.destroyEvents();
        EntityContainer.removeInstance(this.getKey());

        delete this;
    },

    //destroyChildren
    destroyChildren : function(destroy_only){
        var children = this.getEntityChildren();

        if(!children){
            return;
        }

        for(key in children){
            children[key].destroy(destroy_only);
        }
    },

    //destroyChildrenInParent
    destroyChildrenInParent : function(){
        var parent = this.getEntityParent();
        if(!parent){
            return;
        }

        parent.removeEntityChildren(this.getKey());
    },

    //cloneObject
    cloneObject : function(obj){
        var obj_str = JSON.stringify(obj);
        return JSON.parse(obj_str);
    },

    //setEntityProperties
    setEntityProperties : function(properties){
        for(var key in properties){
            switch(key){
                case 'vars':
                    this.setVars(properties[key]);
                    break;
                case 'callbacks':
                    this.setCallbacks(properties[key]);
                    break;
                case 'events':
                    this.setEvents(properties[key]);
                    break;
            }
        }

        return this;
    },

    //setVars
    setVars : function(vars){
        this._vars = vars;
        return this;
    },

    //getVars
    getVars : function(){
        return this._vars;
    },

    //getModuleVars
    getModuleVars : function(){
        var _vars = this._vars.module;
            _vars['entity_parent_key'] = this.getKey();
            //_vars['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));

        return _vars;
    },

    //setEvents
    setEvents : function(events){
        if(!events){
            return this;
        }

        for(var key in events){
            this.appendEvent(events[key]);
        }

        return this;
    },

    //appendEvent
    appendEvent : function(property){
        var vars = {};

        if(property.vars){
            vars = property.vars
            vars.instance = this;
            vars.selector = property.selector;
        } else {
            vars = {
                instance : this,
                selector : property.selector
            };
        }


        var event = {
            'selector' : property.selector,
            'event' : property.event,
            'vars' : vars,
            'func' : this.prepareObjectMethod(property.func)
        }

        switch(property.event){
            case 'ready':
                this.initEventReady(event);
                break;
            default :
                this._events.push(event);
                this.initEvent(event);
        }


        return this;
    },

    prepareObjectMethod : function(object_params){
        if(!object_params || $.isEmptyObject(object_params)){
            return;
        }

        var func;

        switch($(object_params).length){
            case 1 :
                func = object_params;
                break;
            case 2 :
                func = window[object_params[0]][object_params[1]];
                break;
            case 3 :
                func = window[object_params[0]][object_params[1]][object_params[2]];
                break;
            case 4 :
                func = window[object_params[0]][object_params[1]][object_params[2]][object_params[3]];
                break;
            case 5 :
                func = window[object_params[0]][object_params[1]][object_params[2]][object_params[3]][object_params[4]];
                break;
        }

        return func;
    },

    //initEvent
    initEvent : function(event){
        /*
        if(!event.parent){
            event.parent = document;
        }
        */

        $(event.selector)
            .off(event.selector)
            .on(event.event, (event.vars ? event.vars : {}), event.func);

        /*
        $(event.parent)
            .off(event.event, event.selector)
            .on(event.event, event.selector, event.vars ? event.vars : {}, event.func);
            */

        return this;
    },

    //initEventReady
    initEventReady : function(event){
        event.func({data : event.vars});
        return this;
    },

    //setCallbacks
    setCallbacks : function(callbacks){
        if(!callbacks){
            return this;
        }

        for(key in callbacks){
            this.appendCallback(callbacks[key]);
        }

        return this;
    },

    //appendCallback
    appendCallback : function(callback){
        this.callbacks[callback.name] = this.prepareObjectMethod(callback.func);
        return this;
    },


    getCallBack : function(name){
        if(this.callbacks && this.callbacks[name]){
            return this.callbacks[name];
        }

        return false;
    },


    //destroyEvents
    destroyEvents : function(){
        if(!this._events){
            return this;
        }

        for(key in this._events){
            $(this._events[key].selector).off();
            delete this._events[key];
        }

        return this;
    },



}
