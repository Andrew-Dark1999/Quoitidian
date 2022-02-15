<?php
/**
* ApiModule - модуль API  
*
* @author Alex R.
* @version 1.0
*/ 

class ApiModule extends Module {

    // внешний хост, для которого разрашен доступ к API. "*"" - все хосты
    public $access_control_allow_origin = '*';
    // метод запроса, для которого разрашен доступ к API
    public $access_control_allow_methods = 'POST';

    /**
     * ApiModule constructor.
     *
     * @param $id
     * @param $parent
     * @param null $config
     * @throws CException
     */
    public function __construct($id, $parent, $config = null)
    {
        parent::__construct($id, $parent, $config);

        Yii::import('application.modules.Api.components.*');
        Yii::import('application.modules.Api.components.response.*');
        Yii::import('application.modules.Api.definitions.*');
        Yii::import('application.modules.Api.exceptions.*');
        Yii::import('application.modules.Api.validators.*');
    }


    public function setModuleName(){
        $this->_moduleName = 'Api';
    }

    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    }

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t('base', 'Api');
    }
}
