<?php
/**
 * Class History
 */
namespace Reports\extensions;

class History extends \History{

    protected $filter_model = '\Reports\models\ReportsFilterModel';




    /**
     * возвращает состояние обьекта из пользовательской истории в виде параметров урла
     */
    public function getUserStorageUrlParams($index, $pci = null, $pdi = null, $return_string = true){
        $storage_index = array(
            $index['destination'] . '_' . substr($index['copy_id'], 0, strpos($index['copy_id'], '_')),
            $index['copy_id'],
        );

        $criteria = new \CDBCriteria();
        $criteria->addCondition('users_id=:users_id');
        $criteria->params = array(':users_id' => \WebUser::getUserId());
        $criteria->addInCondition('storage_index', $storage_index);
        $storage = \Reports\models\ExtUsersStorageModel::model()->findAll($criteria);

        if(empty($storage)) return;
        $url = array();

        $user_storage_type = array(
            \Reports\models\ExtUsersStorageModel::TYPE_LIST_FILTER,
            \Reports\models\ExtUsersStorageModel::TYPE_LIST_PAGINATION_REPORT,
        );

        foreach($storage as $value){
            foreach($user_storage_type as $type){
                if($value->type == $type){
                    $storage_value = $value->getValue();
                    switch($type){
                        case \Reports\models\ExtUsersStorageModel::TYPE_LIST_FILTER :
                            if(!empty($storage_value)){
                                $lich = 0; $filter_params = array();
                                foreach($storage_value as $filter){
                                    $filter_model = new $this->filter_model;
                                    if($filter_model->count('filter_id=:filter_id', array(':filter_id' => $filter['id'])) == 0 && \FilterVirtualModel::isShowFilter($filter['id'], $index['copy_id']) == false) continue;

                                    $filter_params[]= 'filters['.$lich.']='.$filter['id'];
                                    $lich++;
                                }
                                if(!empty($filter_params)) $url[] = implode('&', $filter_params);
                            }

                            break;
                        case \Reports\models\ExtUsersStorageModel::TYPE_LIST_PAGINATION_REPORT :
                            if(!isset($index['destination']) || $index['destination'] != 'processView') {
                                if (isset($storage_value['page'])) $url[] = 'page=' . $storage_value['page'];
                                if (isset($storage_value['page_size'])) $url[] = 'page_size=' . $storage_value['page_size'];
                            }
                            break;
                    }
                }
            }
        }
        if(!empty($url)){
            if($return_string) return implode('&', $url);
            else return $url;
        }
    }




    /**
     * Возвращает урл на основании даных хранилища
     */
    public function getUserStorageUrl(array $params){

        $destination_def =  "view";

        // формируем основной УРЛ
        $url = \Yii::app()->createUrl('/module/reports/view/' . \ExtensionCopyModel::MODULE_REPORTS);

        $url_params = $this->getUserStorageUrlParams(array('destination' => $destination_def, 'copy_id' => $params['copy_id']));

        // параметры
        if(!empty($url_params)) $url.='?' . $url_params;
        // доп. параметры из post-a
        if(isset($params['params']) && is_array($params['params']) && count($params['params']) > 0){
            $params_post = array();
            foreach($params['params'] as $key => $value){
                if($key == 'this_template') continue;
                $params_post[] = $key . '=' . $value;
            }
            if(!empty($params_post))
                if(empty($url_params)) $url.='?' . implode('&', $params_post);
                else   $url.='&' . implode('&', $params_post);
        }

        return $url;
    }









    /**
     * обновление состояния обьекта пользовательской истории из урла
     */
    public function updateUserStorageFromUrl($index, $page_name = null, $this_template = null, $pci = null, $pdi = null){
        $user_storage_type = array(
            \Reports\models\ExtUsersStorageModel::TYPE_LIST_FILTER,
            \Reports\models\ExtUsersStorageModel::TYPE_LIST_PAGINATION_REPORT,
        );

        foreach($user_storage_type as $type){
            $storage_index = $index['copy_id'];
            $value = array();

            switch($type){
                case \Reports\models\ExtUsersStorageModel::TYPE_LIST_FILTER :
                    if($page_name != null) continue;
                    $filters = new \Filters();
                    $filters->setTextFromUrl();
                    if(!$filters->isTextEmpty()){
                        foreach($filters->getText() as $filter_name) $value[] = array('id'=>$filter_name);
                    }
                    break;
                case \Reports\models\ExtUsersStorageModel::TYPE_LIST_PAGINATION_REPORT :
                    if($page_name != null || (isset($index['destination']) && $index['destination'] == 'processView')){
                        continue;
                    }
                    $min_page = array_keys(\Pagination::getInstance()->page_sizes);
                    $min_page = \Pagination::getInstance()->page_sizes[$min_page[0]];
                    if(\Pagination::$active_page_size != $min_page) $value['page_size'] = \Pagination::$active_page_size;
                    if(\Pagination::$active_page != 1) $value['page'] =  \Pagination::$active_page;

                    $storage_index = $index['destination'] . '_' . substr($index['copy_id'], 0, strpos($index['copy_id'], '_'));

                    break;
            }

            // если значения параметра нет - удаляем его из истории
            if(empty($value)){
                $this->deleteFromUserStorage($type, $storage_index, $pci, $pdi);
                continue;
            }

            $this->setUserStorage($type, $storage_index, $value, false, $pci, $pdi);
        }

    }



}
