/**
 * Created by andrew on 10/4/17.
 */

;(function (exports) {
    var _public, Url,
        _self = {}; //link for instance

    _public = {
        url: null,
        constructor: function (url) {
            if (url) {
                this.setUrl(url);
            }

            return this;
        },
        setUrl: function (url) {
            this.url = url;

            return this;
        },
        getUrl: function () {
            return this.url;
        },
        appendParams: function (value) {
            this.url = Url.appendParams(this.url, value);

            return this;
        },
        replaceParams: function(key, value){
            var json;

            if (!this.url) {
                return null;
            }

            json = Url.getParams(this.url) || {};

            this.url = this.url.replace(key, json[key]);

            return this.url;
        },
        parse: function() {
            return Url.getParams(this.url);
        },
        getParams: function() {
            return Url.getParams(this.url);
        },
        removeParams: function (key) {
            var json, index;

            json = Url.getParams(this.url) || {};

            this.url = this.url.replace(key+'='+json[key], '');

            index = this.url.lastIndexOf('&');

            if (index > 0) {
                this.url = this.url.substring(0, index);
            }

            return this;
        },
        getCopyId: function () {
            return Url.getCopyId(this.url);
        },
        /*работает с перезаписыванием по ключу параметров в юрл*/
        jsonToUrl: function (json) {
            var result, str,
                _this = this,
                currentUrl = Url.getParams(this.url),
                url = this.url.split('?');

            str = this.url;

            if (!Object.keys(json).length) return this;

            $.each(Object.keys(json), function (key, value) {
                if(url[1]){
                    result = str + '&';
                } else {
                    result = str + '?';
                }

                if (currentUrl && currentUrl[value]) {
                    //replace
                    result = str.replace(value+'='+currentUrl[value], value+'='+json[value])
                } else { // new
                    result += value +'=' + json[value];
                }

                str = result;
                url = result.split('?');

               return this;
            });
            this.url = str;

            return this;
        }
    };

    Url = {
        createInstance: function (url) {
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            return new Obj().constructor(url);
        },
        // добавляет параметры в урлу
        appendParams: function(base_url, params_str){
            var result,
                url = base_url.split("?");

            params_str = (typeof params_str == 'undefined') ? '' : params_str;

            if (params_str) {
                if(url[1]){
                    result = base_url + '&' + params_str;
                } else {
                    result = base_url + '?' + params_str;
                }
            }

            return result;
        },
        parse: function(url){
            return JSON.parse('{"' + decodeURI(url).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
        },
        // возвращает параметры урла как обьект
        parseURLParams: function(url){
            var $_GET = {};

            if(!url) url = window.location.search.substring(1).split("&");

            for(var i = 0; i < url.length; i++){
                var getVar = url[i].split("=");
                if(getVar[0]) $_GET[getVar[0]] = typeof(getVar[1]) == "undefined" ? "" : getVar[1];
            }
            return $_GET;
        },
        setHash: function (hash) {
            history.pushState(null, null, hash);
        },
        replaceHash: function (hash) {
            history.replaceState(null, null, hash);
        },
        getCurrent : function () {
            return document.location.href;
        },
        redirect: function (url) {
            document.location.href = url;
        },
        replace: function (from, to) {
            history.replaceState(null, null, location.href.replace(from, to));
        },
        replaceState: function (from, to) {
            history.replaceState(null, null, location.href.replace(from, to));
        },
        getCopyId: function (url) {
            var copy_id = url.substring(url.lastIndexOf('/')+1);

            return copy_id;
        },
        parseFull: function () {
            var array,
                data = {};

            array = location.pathname.substring(1).split('/');
            data['controller'] = array[1];

            if ($.inArray(array[1],[ListView._type, CalendarView._type, ProcessView.type])>= 0) {
                data['action'] = 'show';
            }

            data['pathname'] = location.pathname.substring();
            data['id'] =  this.getCopyId(location.pathname);

            return data;
        },
        appendParam: function (param) {
            history.replaceState(null, null, param);
        },
        getId: function (url) {
            return url.substring(url.lastIndexOf('/')+1);
        },
        getParams: function(url){
            var result = null;
            url = url.split("?");

            if(url[1]){
                result = {};
                var params = url[1].split("&");

                $.each(params, function(key, value){
                    var value_tmp = value.split("=");
                    if(typeof value_tmp[1] == 'undefined') value_tmp[1] = '';
                    result[value_tmp[0]] = value_tmp[1];
                });
            }

            return result;
        },
        getParam: function(url, key){
            var result = this.getParams(url || decodeURI(location.href));

            return result && result[key];
        },
        getWithOutParams: function(url, with_out_key, return_params_only){
            if(!url) return url;
            url_list = url.split("?");
            var params = this.getParams(url);
            var params_tmp = [];

            if(params && !$.isEmptyObject(params)){
                $.each(params, function(key, value){
                    if($.inArray(key, with_out_key) === -1){
                        params_tmp.push(key + '=' + value);
                    }
                });
            }

            params = '';
            if(params_tmp && !$.isEmptyObject(params_tmp)){
                $.each(params_tmp, function(key, value){
                    params = params_tmp.join('&');
                });
            }

            if(params){
                if(return_params_only == false)
                    return url_list[0] + '?' + params;
                else
                    return params;
            } else {
                if(return_params_only == false)
                    return url_list[0];
                else
                    return '';
            }
        },
        clean: function () {
            var url,
                part = location.href.indexOf('?');

            if (part) {
                if (!location.href.substring(part+1).length) {
                    url = location.href.substring(0, location.href.indexOf('?'));
                    history.replaceState(null, null, url);
                }
            }

            return this;
        },
    }

    exports.Url = Url;
})(window);
