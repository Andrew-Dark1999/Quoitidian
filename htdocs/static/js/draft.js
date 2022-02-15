;(function (exports) {
    var _private, _public, ModelDraft,
        _self = {};

    _private = {
        saveDraft(key, data) {
            key = key || this._key;

            var data = {
                'uid': key,
                'data': data
            }

            AjaxObj
                .createInstance()
                .setUrl('/draft/saveJson')
                .setData(data)
                .setType('POST')
                .setAsync(true)
                .setDataType('json')
                .setCallBackSuccess(function(data){
                    Console.log('save draft', data);
                })
                .setCallBackError(function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                })
                .send();

        },
    };

    _public = {
        _interface: 'draft',
        _instance: null,
        _key: 'draft',


    };

    var Draft = {
        getInstance: function(){
            if(!Draft._instance){
                Draft._instance = Draft.createInstance();
            }

            return Communication._instance;
        },
        getKey: function() {
          return _self._key
        },
        createInstance: function () {
            var Obj = function () {
                for (var key in Draft) {
                    this[key] = Draft[key];
                }
            }

            return new Obj().constructor();
        },
        constructor: function () {

            return this;
        },

        createKeyByEV(evModel) {
            var copy_id = evModel.copy_id;
            var pci = evModel.pci;
            var pdi = evModel.pdi;
            var card_id = evModel.id || 0;
            var this_template = evModel.this_template;
            var finished_object = evModel.finished_object;

            return Global.createUniqueKey(copy_id, card_id, pci, pdi, this_template, finished_object);
        },

        importModel: function (data) {
            for (var key in data) {
                if (typeof data[key] != 'object') {
                    this[key] = data[key];
                }
            }

            return this;
        },
        runCron: function() {
            var _this = this;

            setInterval(function () {
                var data = JSON.parse(LocalStorageObject.readStorage(_this.getKey()) || '{}');

                Object.keys(data).filter(function(item) {
                    _self.saveDraft(item, data[item]);
                });
                LocalStorageObject.writeStorage(_this.getKey(), JSON.stringify({}));
            }, Environments.draftInterval);
        },
        removeDraftFromLocalStorage: function(getKeyOfDraftHandler) {
            if (!getKeyOfDraftHandler) return;

            var key = getKeyOfDraftHandler();
            var data = JSON.parse(LocalStorageObject.readStorage(this.getKey()) || '{}');

            delete data[key];
            LocalStorageObject.writeStorage(this.getKey(), JSON.stringify(data));
        },
        removeDraft: function (key) {
            key = key || this._key;
            Console.log('removeDraft()');

            AjaxObj
                .createInstance()
                .setUrl('/draft/deleteByIndex?uid='+ key)
                .setType('GET')
                .setAsync(true)
                .setDataType('json')
                .setCallBackSuccess(function(data){
                    Console.log('remove draft', data);
                })
                .setCallBackError(function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                })
                .send();

        },
        destroy: function () {
            var instance = Draft._instance;

            if (instance) {
                Draft._instance = null;
            }
            delete this;
        }
    }

    for(var key in _public) {
        _self[key] = _public[key];
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    exports.Draft = Draft;
})(window);

