<?php
/**
* 
* Extension
* @author Alex R.
*  
*/

class Extension{
    
    public static function getInstance(){
        return new self;
    }

    /**
    * Регистрация модуля(лей) к системе 
    * @param $module string  - название модуля
    * @return this
    */
    public function registerModules($module_name = null){
        if($module_name === null) 
            $modules = ExtensionModel::model()->modulesActive()->findAll();
        else $modules = array($module_name);
        
        if(!empty($modules)){
            foreach($modules as $val){
                Yii::app()->setModules(
                    array(
                        $val->name => array(
                            	'import' => array(
                        			$val->name . '.models.*',
                            	),
                            ),        
                        )
                );
            }
        }
        return $this;
    }    
        
    
    /**
    * Возвращат список установленых модулей для создания экземпляра в контрукторе модулей
    * @return array
    */
    public function getInstaledModules($constructorPrm = true, $clonePrm = true){
        $data = array();
        $modules_db = ExtensionModel::model()->modulesActive()->findAll();
        if(!empty($modules_db)){
            foreach($modules_db as $val){
                $this->registerModules($val);
                $module = Yii::app()->getModule($val->name);
                if($module->constructor === $constructorPrm && $module->clone === $clonePrm)
                    $data[Yii::t('base', 'Module')][$val->extension_id ] = $val->getModule()->getModuleTitleDefault();
                    
            }
        }
        return $data;
    }
    
}
