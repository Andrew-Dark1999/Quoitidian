<?php
/**
 * ModuleController
 * 
 * @author Alex R.
 * @copyright 2014
*/

class ModuleController extends Controller{


  
    /**
    * подключает и возвращает модуль по "copy_id" 
    */ 
    public function actionModule($controller, $action, $copy_id){
        if(empty($controller) || empty($action) || empty($copy_id))
            return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));
        
        $extension_copy = ExtensionCopyModel::model()->modulesActive()->findByPk($copy_id);
        if(!$extension_copy){
            return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
        }

        $module = $extension_copy->extension->getModule($extension_copy);
        
        $register_scripts = \AdditionalProccessingModel::getInstance()->registerScript($copy_id);
        if($register_scripts && is_array($register_scripts) && count($register_scripts))
           foreach($register_scripts as $register_script)
                Yii::app()->clientScript->registerScript($register_script['name'], $register_script['js']);
        
        $url = Yii::app()->createUrl($module->getModuleName() . '/' . $controller . '/' . $action);
        Yii::app()->runController($url);
    }



    /**
    * подключает и возвращает модуль по его названию
    */ 
    public function actionModuleOverName($module_name, $controller, $action, $extension_copy=null){

        if(empty($module_name) || empty($controller) || empty($action))
            return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));

        $extension = ExtensionModel::model()->modulesActive()->find('name=:name', array(':name'=>$module_name));
        if(!$extension){
            return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
        }
        
        $extension->getModule($extension_copy);
        
        $url = Yii::app()->createUrl($module_name . '/' . $controller . '/' . $action);
        Yii::app()->runController($url);
    }


    /**
    * исполняет контроллер модуля и возвращает результат
    */
    public function runModuleController($module_name, $controller, $action){
        $extension = ExtensionModel::model()->modulesActive()->find('name=:name', array(':name'=>$module_name));
        $module = $extension->getModule();
        $module->setExtension()->setFirstExtensionCopy();

        list($controller) = Yii::app()->createController($module_name . '/' . $controller);
        
        if(!empty($controller)) return $controller->{$action}();
        return false; 
    } 




}
