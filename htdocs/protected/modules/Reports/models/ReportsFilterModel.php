<?php
/**
 * ReportsFilterModel widget
 * @author Alex R.
 */
namespace Reports\models;

class ReportsFilterModel extends \FilterModel{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return '{{reports_filter}}';
    }

    public function rules(){
        return array(
            array('title, copy_id, reports_id', 'required'),
            array('params', 'paramsValidate'),
            array('name', 'nameValidate'),
            array('name',   'length', 'max' => 255),
            array('view', 'accessToChange'),
            array('title',  'length', 'max' => 100),
            array('params', 'length', 'max' => 5000),
            array('copy_id, user_create, reports_id, name', 'safe'),
        );
    }




    public function scopes(){
        return array(
            "onlyPersonal" => array(
                "condition" => "(user_create = " . \WebUser::getUserId() . " OR `view` = '" . self::VIEW_GENERAL . "')",
            ),

        );
    }


    /**
     * getFilterDefault
     * @param $reports_id
     * @return array
     */
    public static function getFilterDefault($reports_id){
        return self::getFilterParams(\Reports\models\ReportsModel::getSavedSchema($reports_id));
    }



    public function setFilterParams(array $params){
        if(empty($params)) return;
        $this->params = json_encode($params);

        return $this;
    }


    /**
     * getFilterParams. Список фильтров.
     * @param $schema
     * @return array
     */
    public static function getFilterParams($schema){
        $result = array();
        if(empty($schema)) return $result;
        foreach($schema as $element){
            if($element['type'] != 'filter') continue;
            foreach($element['elements'] as $panel){
                if(!empty($panel['module_copy_id']) && !empty($panel['field_name']) && !empty($panel['condition'])){
                    $result[] = array(
                        'copy_id' => $panel['module_copy_id'],
                        'name' => $panel['field_name'],
                        'condition' => $panel['condition'],
                        'condition_value' => $panel['condition_value'],
                    );
                }
            }
        }

        return $result;
    }



    /**
     * getFilterParamsIndicator. Список фильтров показателя
     * @param $schema
     * @return array
     */
    public static function getFilterParamsIndicator($schema){
        $result = array();
        if(empty($schema)) return $result;
        foreach($schema as $element1){
            if($element1['type'] != 'data_analysis') continue;
            foreach($element1['elements'] as $element2){
                if($element2['type'] != 'data_analysis_indicator') continue;
                if(empty($element2['filters'])) continue;

                foreach($element2['filters'] as $filter){
                    if(!empty($filter['module_copy_id']) && !empty($filter['field_name']) && !empty($filter['condition'])){
                        $result[$element2['unique_index']][] = array(
                            'copy_id' => $filter['module_copy_id'],
                            'name' => $filter['field_name'],
                            'condition' => $filter['condition'],
                            'condition_value' => $filter['condition_value'],
                        );
                    }
                }
            }
        }

        return $result;
    }




    /**
     * deleteUnnecessaryCopyId
     * @param $copy_id
     * @return $this
     */
    public function deleteUnnecessaryCopyId($copy_id){
        $result = array();
        $filter_params = $this->getParams();
        foreach($filter_params as $params){
            if($params['copy_id'] != $copy_id) continue;
            $result[] = $params;
        }

        $this->params = json_encode($result);

        return $this;
    }







    /**
     * clearFilters
     * @param $reports_id
     */
    public static function clearFilters($reports_id){
        $model = static::model();
        $filters_model = $model->findAll(array(
                                    'condition' => 'reports_id=:reports_id',
                                    'params' => array(':reports_id' => $reports_id),
                                ));

        if(empty($filters_model)) return;

        $schema = \Reports\models\ReportsModel::getSavedSchema($reports_id);

        $da = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema);
        if(empty($da)) return;

        $copy_id_list = array();
        foreach($da as $item){
            $copy_id_list[] = $item['module_copy_id'];
        }


        foreach($filters_model as $filter){
            if(empty($filter->params)) continue;
            $params_tmp = array();
            $params = json_decode($filter->params, true);

            if(!is_array($params)) continue;

            foreach($params as $param){
                if(!in_array($param['copy_id'], $copy_id_list)) continue;
                $params_tmp[] = $param;
            }

            if(!empty($params_tmp)){
                $filter->params = $params_tmp;
                $filter->save();
            } else {
                $filter->delete();
            }
        }


    }





}
