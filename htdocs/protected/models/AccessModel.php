<?php

class AccessModel {

    
    public static function getInstance(){
        return new self();
    }    
    
    

    /**
     * возвращает список обьектов(модули, доп. досдупы)
     */
    public function getAccess(){
        $data_model1 = new DataModel();
        $data_model1
            ->setSelect('d1.regulation_id as id, d1.title, concat("'.Access::ACCESS_TYPE_REGULATION.'") as type')
            ->setFrom('{{regulation}} d1');
        $data_model2 = new DataModel();
        $data_model2
            ->setSelect(array('d1.copy_id as id', 'd1.title', 'concat("'.Access::ACCESS_TYPE_MODULE.'") as type'))
            ->setFrom('{{extension_copy}} d1')
            ->setWhere('d1.set_access = "1"');
        $data_model1->setUnion($data_model2->getText());
        
        $query = '(' . $data_model1->getText() .') as d1';
        $params = $data_model1->getParams();
        $data_model1->reset();
        $select = array_merge(array('d1.*'));
        $data_model1
            ->setSelect($select)
            ->setFrom($query)
            ->setGroup('d1.id')
            ->setOrder('d1.type, d1.title');
        $data_model1->setParams($params);

        $data = $data_model1->findAll();
        return $data;
    }
    
    
    /**
     * возвращает список обьектов(модули, доп. досдупы) для select
     */
    public function getSelectAccessList(){
        return $this->getAccess();
    }
    
    
    /**
     * возвращает название доступа
     */
    public function getAccessTitle($params){
        if(empty($params) || !is_array($params)) return;
        if(empty($params['type']) || empty($params['id'])) return;
        $result = '';
        switch((integer)$params['type']){
            case Access::ACCESS_TYPE_MODULE :
                    $result = ExtensionCopyModel::model()->findByPk($params['id'])->title; 
                    break;
            case Access::ACCESS_TYPE_REGULATION :
                    $result = Yii::t('base', RegulationModel::model()->findByPk($params['id'])->title); 
                    break;
        }
        return $result;
    }
    
    
    
}
