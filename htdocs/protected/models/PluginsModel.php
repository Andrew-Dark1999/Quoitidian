<?php

class PluginsModel{

    const SERVICE_NAME_DISABLED = '-1'; // Сервис отключен

    private $_validate;
    private $_result = [];



    public function __construct(){
        $this->_validate = new Validate();
    }


    public function getValidate(){
        return $this->_validate;
    }


    private function addValidateResultError($message, $type_message = 'e'){
        $this->_validate->addValidateResult($type_message, Yii::t('messages', $message));
        return $this;
    }


    public function getValidateResultHtml(){
        return $this->_validate->getValidateResultHtml();
    }


    private function addResult($key, $value){
        if($key === null){
            $this->_result[] = $value;
        } else {
            $this->_result[$key] = $value;
        }
    }


    public function getResult(){
        return $this->_result;
    }



    public function getSourceList(){
        $params = $this->getParams();
        if($params == false){
            return;
        }

        return array_keys($params);
    }


    private function getParams(){
        return \Yii::app()->params['plugins']['sources'];
    }


    private function getSourseParams($source_name){
        $params = $this->getParams();
        if(array_key_exists($source_name, $params)){
            return $params[$source_name];
        }
    }



    public function getSourceModelList($only_enable = true){
        $result = [];
        $source_list = $this->getSourceList();

        if($source_list == false){
            return $result;
        }

        foreach($source_list as $source_name){
            $source_model = $this->getSourceModelByName($source_name, $only_enable);

            if($source_model == false){
                continue;
            }

            $result[$source_name] = $source_model;
        }

        return $result;
    }




    public function getSourceModelByName($source_name, $only_enable = true){
        $params = $this->getSourseParams($source_name);
        if($params == false){
            return;
        }

        $source_model = $params['class']::getInstance();
        if($only_enable && $source_model->getEnable() == false){
            return;
        }

        return $source_model;
    }



    public function getServiceModel($source_name, $service_name){
        $source_model = $this->getSourceModelByName($source_name);

        if($source_model == false){
            return;
        }

        return $source_model->getService($service_name);
    }





    public function validateAll($attributes){
        $status = true;

        foreach($attributes as $attribute){
            // validate general
            if(empty($attribute['source_name']) || empty($attribute['service_name'])){
                $this->addValidateResultError('Not defined data parameters');
                return false;
            }

            if($attribute['service_name'] === self::SERVICE_NAME_DISABLED){
                continue;
            }

            if(empty($attribute['attributes'])){
                $this->addValidateResultError('Not defined data parameters');
                return false;
            }

            // validate service
            $service_model = $this->getServiceModel($attribute['source_name'], $attribute['service_name']);
            if($service_model == false){
                $this->addValidateResultError('Not defined data parameters');
                return false;
            }

            $params_model = $service_model
                                ->getParamsModel()
                                ->setMyAttributes($attribute['attributes']);

            if($params_model->validate() == false){
                if($params_model->hasErrors()){
                    $status = false;
                    $value = [
                        'source_name' => $attribute['source_name'],
                        'html' => $params_model->getHtml(),
                    ];

                    $this->addResult(null, $value);
                }
            }
        }

        return $status;
    }



    /**
     * savePluginBlockParams - контроль и сохранение параметров оперделенного блока
     */
    public function saveAll($attributes){
        foreach($attributes as $attribute){
            if($attribute['service_name'] === self::SERVICE_NAME_DISABLED){
                \PluginParamsModel::model()->updateAll(['active' => '0'], 'source_name=:source_name', [':source_name' => $attribute['source_name']]);
                continue;
            }

            $service_model = $this->getServiceModel($attribute['source_name'], $attribute['service_name']);

            if($service_model == false){
                continue;
            }

            $service_model
                ->getParamsModel()
                ->setMyAttributes($attribute['attributes'])
                ->save();

            $value = [
                'source_name' => $attribute['source_name'],
                'html' => $service_model->getParamsModel()->getHtml(),
            ];

            $this->addResult(null, $value);
        }

        return $this;
    }



}
