<?php
/**
 * Class PluginServiceUserParamsFactory - Пользовательские параметры подключения (Фабричный класс)
 */


abstract class PluginServiceUserParamsFactory extends FormModel implements PluginServiceParamsInterface{

    // db attributes
    public $id;
    public $users_id;
    public $plugin_params_id;
    public $params;



    protected $_service_model;


    public function setServiceModel($service_model){
        $this->_service_model = $service_model;

        return $this;
    }


    /**
     * getNewModelByPUPM
     * @param PluginUserParamsModel $pup_model
     * @return PluginServiceUserParamsFactory
     */
    private function setAttributesPUPM(\PluginUserParamsModel $pup_model){
        foreach($pup_model->getAttributes() as $attr_name1 => $value1){
            if($attr_name1 == 'params'){
                $params = $pup_model->getParams();
                if($params){
                    foreach($params as $attr_name2 => $value2){
                        $this->{$attr_name2} = $value2;
                    }
                }
            }

            $this->{$attr_name1} = $value1;
        }

        return $this;
    }


    /**
     * findByUsersId
     * @param null $users_id
     * @return PluginServiceUserParamsFactory|void
     */
    public function findByUsersId($users_id = null){
        if($users_id === null){
            $users_id = WebUser::getUserId();
        }

        $plugin_params_id = $this->_service_model->getParamsModel()->id;

        if($plugin_params_id == false){
            return;
        }

        $pup_model = \PluginUserParamsModel::model()
                        ->scopeUsersId($users_id)
                        ->scopePluginParamsId($plugin_params_id)
                        ->find();

        if($pup_model == false) {
            return;
        }

        $model = new static();
        $model->setAttributesPUPM($pup_model);

        return $model;
    }




    public function findAll(){
        $plugin_params_id = $this->_service_model->getParamsModel()->id;

        if($plugin_params_id == false){
            return [];
        }

        $pup_model_list = \PluginUserParamsModel::model()
            ->scopePluginParamsId($plugin_params_id)
            ->findAll();

        if($pup_model_list == false){
            return [];
        }

        $model_list = [];

        foreach($pup_model_list as $pup_model){
            $model = new static();
            $model->setAttributesPUPM($pup_model);

            $model_list[] = $model;
        }

        return $model_list;
    }



    public function setMyAttributes($attributes){
        if(empty($attributes)){
            return $this;
        }

        foreach($attributes as $property => $value){
            if(property_exists($this, $property)) $this->{$property} = $value;
        }

        return $this;
    }



    public function setAttribute($attribute_name, $value){
        $this->setAttributes([$attribute_name => $value]);

        return $this;
    }






    public function save(){
        $attributes = [
            'users_id' => $this->users_id,
            'plugin_params_id' => $this->plugin_params_id,
            'params' => $this->getPublicAttributes(),
        ];

        $pup_model = \PluginUserParamsModel::model()
                            ->scopeUsersId($this->users_id)
                            ->scopePluginParamsId($this->plugin_params_id)
                            ->find();

        if($pup_model == false){
            $pup_model = new \PluginUserParamsModel();
        }

        $pup_model->setAttributes($attributes);
        $pup_model->save();
    }




}
