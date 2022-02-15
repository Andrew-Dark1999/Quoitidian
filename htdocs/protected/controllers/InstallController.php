<?php

class InstallController extends CController {
    
	public $layout = "install";
    
    public $data = array();
  
    private $_connect = null;
    private $_command = null;
    
    private $_html = '';
    private $_validate;

    
    function init(){
        if(isset($_POST['crm_language'])){
            Yii::app()->setLanguage($_POST['crm_language']);
            Yii::app()->session['crm_language'] = Yii::app()->getLanguage();
        }else if(isset(Yii::app()->session['crm_language'])){
            Yii::app()->setLanguage(Yii::app()->session['crm_language']);
        }else{
            Yii::app()->session['crm_language'] = Yii::app()->getLanguage();
        }
        
        $this->_validate = new Validate();
    }


	private function renderJson(array $data) {
		header("Content-Type: application/json; charset=utf-8");
		echo json_encode($data);
	}



    private function getResult(){
        if($this->_validate->error_count > 0)
            return $this->renderJson(array(
                'status' => false,
                'html' => $this->_html,
                'messages' => $this->_validate->getValidateResultHtml(),
            ));
        else
            return $this->renderJson(array(
                'status' => true,
                'html' => $this->_html,
            ));        
    }
    



	public function actionIndex(){
        if((isset($_POST['crm_language']))&&(!empty($_POST['crm_language']))){
            Yii::app()->language = $_POST['crm_language'];
            Yii::app()->session['crm_language'] = $_POST['crm_language'];
            return $this->getResult();
        }
        
        $this->render('//install/step1');
	}
    

    /**
     * 1
     */
    public function actionStep1(){
        $this->_html = $this->renderPartial('//install/step2', array('model' => new ParametersModel()), true);
        return $this->getResult();
    }


    /**
     * 2
     */
    public function actionStep2(){
        $parameters_model = new ParametersModel();
        $parameters_model->setMyAttributes($_POST);
        $parameters_model->setScenario('install');
        if($parameters_model->validate() == false){
            $this->_validate->addValidateResultFromModel($parameters_model->getErrors());
            $this->_html = $this->renderPartial('//install/step2', array('model' => $parameters_model), true);
        } else {
            $this->_html = $this->renderPartial('//install/step3',  array('model' => new DataBaseModel()), true);
        }
        
        return $this->getResult();
    }
    
    
    
    /**
     * 3
     */
    public function actionStep3(){
        $model = new DataBaseModel();
        $model->setMyAttributes($_POST);
        if($model->validate() == false){
            $this->_validate->addValidateResultFromModel($model->getErrors());
            $this->_html = $this->renderPartial('//install/step3', array('model' => $model), true);
        } else {
            // валидация
            $this->validateBeforeInstall();
            if($this->_validate->error_count > 0){
                $this->_html = $this->renderPartial('//install/step3', array('model' => $model, 'errors' => $this->getFormatedErrors()), true);    
            } else {
                //инсталяция
                $this->install($model);
                if($this->_validate->error_count > 0){
                    $this->_html = $this->renderPartial('//install/step3', array('model' => $model, 'errors' => $this->getFormatedErrors()), true);    
                } else {
                    copy(dirname(__FILE__) . DIRECTORY_SEPARATOR. "..". DIRECTORY_SEPARATOR . "install" . DIRECTORY_SEPARATOR . 'index.php',
                         dirname(__FILE__) . DIRECTORY_SEPARATOR. "..". DIRECTORY_SEPARATOR . "..". DIRECTORY_SEPARATOR . 'index.php');
                    // валидация
                    $this->validateAfterInstall();
                    $this->_html = $this->renderPartial('//install/step4', array('errors' => $this->getFormatedErrors()), true);
                }
            }
        }
        
        return $this->getResult();
    }

   
   
   
    /**
     * возвращает массив ошибок 
     */ 
    private function getFormatedErrors(){
        $errors = array();
        $validate_result = $this->_validate->getValidateResult();
        if(empty($validate_result)) return $errors;
        foreach($validate_result as $message){
            $errors[] = $message['message'];
        }
        return $errors;        
    }        
   
   
   
    /**
     * инсталяция
     */
    private function install($db_model){
        if($this->createDataBase($db_model) == false) return;
        
        if($this->dataBaseInstall($db_model)){
            $parameters_model = new ParametersModel();
            $_POST['db_type'] = $db_model->db_type;
            $parameters_model->setMyAttributes($_POST);
            $parameters_model->setScenario('update_install');
            if($parameters_model->validate() == false){
                $this->_validate->addValidateResultFromModel($parameters_model->getErrors());
            } else {
                if($parameters_model->saveParams() == false){
                    $this->_validate->addValidateResult('e', Yii::t('install', 'During installation errors occurred'));
                } else {
                    chmod(dirname(__FILE__) . DIRECTORY_SEPARATOR. "..". DIRECTORY_SEPARATOR ."config". DIRECTORY_SEPARATOR ."local.php", 0755);    
                }

                //language
                $params_model = ParamsModel::model()->titleName('language')->find();
                $params_model->value = Yii::app()->language;
                $params_model->save();  
            }
        }
    }
    
    
    
    
    /**
     * проверка и создание БД
     */
    private function createDataBase($db_model){
        $connected = $db_model->connect(false);
        if($connected == false){
            $this->_validate->addValidateResult('e', Yii::t('install', 'An error has occurred during connecting to the database, check the parameters'));
            return false;
        } 
        
        $create = $db_model->createDataBase();
        if(!$create){
            $this->_validate->addValidateResult('e', Yii::t('install', 'An error occurred during the creation of the database'));
            return false;
        } 
        $connected = $db_model->connect();
        if(!$connected){
            $this->_validate->addValidateResult('e', Yii::t('install', 'An error has occurred during connecting to the database, check the parameters'));
            return false;
        } 
        
        $db_model->disconnect();
        
        return true;
    }
    
    
    /**
     * инсталяция таблиц БД
     */
    private function dataBaseInstall($db_model){
        $connected = $db_model->connect();
        if(!$connected){
            $this->_validate->addValidateResult('e', Yii::t('install', 'An error has occurred during connecting to the database, check the parameters'));
            return false;
        } 
        
        $install = $db_model->install();
        if(!$install){
            $this->_validate->addValidateResult('e', Yii::t('install', 'During installation errors occurred'));
            return false;
        } 

        return true;
    }
    
    
        
        
        
    /**
     * общая проверка до проведения инсталяции 
     */
    private function validateBeforeInstall(){
        $entities = array(
            'directries' => array(
                YiiBase::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . "protected" . DIRECTORY_SEPARATOR . "config",
                YiiBase::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . "static". DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "modules",
            ),
            'files' => array(
                YiiBase::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . "index.php" ,
            )
        );

        foreach($entities['directries'] as $entity){
            if(!file_exists($entity)){
                $this->_validate->addValidateResult('e', Yii::t('install', 'The directory "{s}" does not exist', array('{s}' => $entity)));  
            } else {
                restore_error_handler();
                try {
                    file_put_contents($entity . DIRECTORY_SEPARATOR . 'zero', '0');
                    if(!file_exists($entity . DIRECTORY_SEPARATOR . 'zero'))
                        $this->_validate->addValidateResult('w', Yii::t('install', 'No write access to the directory "{s}"', array('{s}' => $entity)));
                    else 
                        @unlink($entity . DIRECTORY_SEPARATOR . 'zero');
                 } catch (Exception $e){
                    $this->_validate->addValidateResult('w', Yii::t('install', 'No write access to the directory "{s}"', array('{s}' => $entity)));
                 }
                    
            }
        }

        foreach($entities['files'] as $entity){
            if(!file_exists($entity)){
                $this->_validate->addValidateResult('e', Yii::t('install', 'The file "{s}" does not exist', array('{s}' => $entity)));  
            } else {
                if(!is_readable($entity)) $this->_validate->addValidateResult('e', Yii::t('install', 'No read access to the file "{s}"', array('{s}' => $entity)));
                if(!is_writable($entity)) $this->_validate->addValidateResult('e', Yii::t('install', 'No write access to the file "{s}"', array('{s}' => $entity)));
            }
        }
        
        
    }
    

       
        
    /**
     * общая проверка после проведения инсталяции 
     */
    private function validateAfterInstall(){
        $entities = array(
            'files' => array(
                YiiBase::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . "protected" . DIRECTORY_SEPARATOR . "config". DIRECTORY_SEPARATOR . "local.php" ,
            )
        );

        foreach($entities['files'] as $entity){
            if(!file_exists($entity)){
                $this->_validate->addValidateResult('e', Yii::t('install', 'The file "{s}" does not exist', array('{s}' => $entity)));  
            } else {
                if(!is_readable($entity)) $this->_validate->addValidateResult('w', Yii::t('install', 'No read access to the file "{s}"', array('{s}' => $entity)));
                if(!is_writable($entity)) $this->_validate->addValidateResult('w', Yii::t('install', 'No write access to the file "{s}"', array('{s}' => $entity)));
            }
        }
        
        
    }
    

}

