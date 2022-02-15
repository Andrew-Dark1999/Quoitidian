<?php
/**
 * class ImportRelatesModel
 *
 * @author Alex R.
 */

class ImportRelatesModel{


    private $_relate_data = array();
    private $_relate_copy_id_data = array();

    private $_this_template = false;
    private $_pci;
    private $_pdi;



    /**
     * getInstance
     */
    public static function getInstance(){
        return new self();
    }



    /**
     * setRelateData
     */
    private function setRelateData($key, $relate_data){
        $this->_relate_data[$key] = $relate_data;

        return $this;
    }



    /**
     * setRelateCopyIdData
     */
    private function setRelateCopyIdData($key, $copy_id){
        $this->_relate_copy_id_data[$key] = $copy_id;

        return $this;
    }



    /**
     * getRelateCopyIdData
     */
    public function getRelateCopyIdData($key){
        return $this->_relate_copy_id_data[$key];
    }



    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }


    public function setPciPdi($pci, $pdi){
        $this->_pci = $pci;
        $this->_pdi = $pdi;
        return $this;
    }





    /**
     * prepareRelateList - подготовка данных связаого модуля
     */
    public function prepareRelateList($field_params){
        if(!in_array($field_params['params']['type'], array('relate', 'relate_participant'))) return;
        if($field_params['params']['type'] == 'relate' && empty($field_params['params']['relate_module_copy_id'])) return;


        switch($field_params['params']['type']){
            case 'relate':
                $extension_copy = \ExtensionCopyModel::model()->findByPk($field_params['params']['relate_module_copy_id']);
                if(empty($extension_copy)) return;

                //  get data
                $data_model = new \DataModel();
                $data_model
                    ->setSelect($extension_copy->prefix_name . '_id')
                    ->setExtensionCopy($extension_copy)
                    ->setFrom($extension_copy->getTableName());

                $data_model = $data_model->findAll();
                $data = array();

                if(!empty($data_model)){
                    foreach($data_model as $value){
                        $data[] = $value[$extension_copy->prefix_name . '_id'];
                    }
                }
                break;

                /*  // загрузка массива ид-значение. Функционал отложен
                $extension_copy = \ExtensionCopyModel::model()->findByPk($field_params['params']['relate_module_copy_id']);
                if(empty($extension_copy)) return;

                    //  get data
                $data_model = new \DataModel();
                $data_model
                ->setExtensionCopy($extension_copy)
                ->setFromModuleTables();

                    //responsible
                if($extension_copy->isResponsible())
                $data_model->setFromResponsible(true);

                    //participant
                if($extension_copy->isParticipant())
                $data_model->setFromParticipant(true);

                    //this_template
                if($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
                    $data_model->andWhere(array('AND', $extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_TEMPLATE.'" '));
                }
                else {
                        if(!empty($this->_pci))
                            $data_model->andWhere(array('AND', $extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE_CM . '" OR ' . $extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $extension_copy->getTableName() . '.this_template is null'));
                        else
                            $data_model->andWhere(array('AND', $extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $extension_copy->getTableName() . '.this_template is null'));
                }

                $data_model
                    ->setFromFieldTypes()
                    ->setCollectingSelect()
                    ->setGroup()
                    ->replaceParamsOnRealValue();
                $data_model = $data_model->findAll();
                break;
                */
            case 'relate_participant':
                $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_STAFF);

                //  get data
                $data_model = new \DataModel();
                $data_model
                    ->setExtensionCopy($extension_copy)
                    ->setFromModuleTables()
                    ->andWhere(array('AND', $extension_copy->getTableName() . '.active = "1"'))
                    ->setFromFieldTypes()
                    ->setCollectingSelect()
                    ->setGroup()
                    ->replaceParamsOnRealValue();
                $data_model = $data_model->findAll();
                $field_params['params']['relate_field'] = 'sur_name,first_name,father_name';

                // prepare data
                $data = array();
                if(!empty($data_model)){
                    $field_params_list = $this->getSchemaFieldsParams($extension_copy);

                    $data_tmp = DataValueModel::getInstance()
                        ->setSchemaFields($field_params_list)
                        ->setExtensionCopy($extension_copy)
                        ->setFileType(DataValueModel::FILE_TYPE_IMAGE)
                        ->setSetIdKey(true)
                        ->setAddAvatar(false)
                        ->prepareData($data_model)
                        ->getProcessedData()// без обьединения значений
                        ->getData();




                    foreach($data_tmp as $primary_key => $data_row){
                        $value = '';
                        foreach(explode(',', $field_params['params']['relate_field']) as $field_name){
                            $value_tmp = $this->getPreparedData($field_name, $data_row[$field_name]);
                            if($value_tmp !== '' && !is_null($value_tmp)){
                                if($value === '' || is_null($value)){
                                    $value = $value_tmp;
                                } else{
                                    $value .= ' ' . $value_tmp;
                                }
                            }
                        }
                        if($value !== ''){
                            $data[$primary_key] = $value;
                        }
                    }
                }

                break;
        }


        $this->setRelateCopyIdData($field_params['params']['name'], $extension_copy->copy_id);
        $this->setRelateData($field_params['params']['name'], $data);
    }






    /**
     * getId
     */
    public function getId($key, $value){
        if($value === null || $value === '') return;
        if(!array_key_exists($key, $this->_relate_data)) return;

        $id = array_search($value, $this->_relate_data[$key]);
        if($id === false){
            return;
        } else {
            return $id;
        }
    }






    /**
     * getId
     */
    public function getCheckedId($key, $id){
        $list =  $this->_relate_data[$key];
        if(empty($list)) return null;

        if(in_array($id, $list)) return $id;
        return null;
    }





    /**
     * getSchemaFieldsParams
     */
    private function getSchemaFieldsParams($extension_copy){
        $schema = $extension_copy->getSchemaParse();
        $result = array();
        if(empty($schema) || !isset($schema['elements'])) return $this;
        foreach($schema['elements'] as $element){
            if(isset($element['field'])){
                if($element['field']['params']['type'] == 'activity') continue;
                $result[] = $element['field'];
            }
        }
        return $result;
    }





    /**
     * Возвращает данные для поля relate
     */
    private function getFindData($value, &$result){
        if(isset($value[0]) && is_array($value[0]))
            return $this->getFindData($value[0], $result);

        if(isset($value['value']) && !empty($value['value'])){
            if($result['value_concat'] === ''){
                $result['value_concat'] = $value['value'];
            } else {
                $result['value_concat'] .= ' ' . $value['value'];
            }
        }
        $result = $result;
        return $result;
    }





    /**
     *  Вставка елемента данных из массыва данних
     */
    private function getPreparedData($field_name, $data){
        if(isset($data['params'][$field_name])){
            // other fields
            if($data['params'][$field_name]['type'] != 'relate'){
                if(isset($data['value'])){
                    if(isset($data['value'])) return $data['value'];
                }

                // field "relate"
            } elseif($data['params'][$field_name]['type'] == 'relate') {
                $result = array(
                    'value_concat' => '',
                    'type' => '',
                    'params' => array(),
                );

                foreach($data['value'] as $value){
                    $this->getFindData($value, $result);
                }

                return $result['value_concat'];
            }
        } else {
            if(isset($data['value'])){
                if(isset($data['value'])) return $data['value'];
            }
        }
    }









}
