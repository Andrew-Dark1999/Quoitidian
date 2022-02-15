<?php
/**
 * Created by PhpStorm.
 * User: alex_r
 * Date: 26.10.2017
 * Time: 23:50
 */

namespace application\modules\test1;


class Test1Module extends \Module{


    public function __construct($id, $parent, $config = null){
        parent::__construct($id, $parent, $config);

        \Yii::import('test1.models.*');
    }

    public $controllerNamespace = '\application\modules\test1\controllers';

    public function setModuleName(){
        $this->_moduleName = 'Test1';
    }

    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    }

    public function setConstructorTitle(){
        $this->_constructor_title = \Yii::t('base', 'New Test1 module');
    }


}
