<?php
/**
 * ReportsUsersStorageModel
 * 
 * @author Alex R.
 * @copyright 2014
 */
 
namespace Reports\models;
 

class ReportsUsersStorageModel extends \ActiveRecord{


    public $tableName = 'reports_users_storage';
    
    
	public static function model($className=__CLASS__){
		return parent::model($className);
	}
    


    public static function getSchemaModel(){
        return self::$_schema_model;
    }




	public function rules(){
		return array(
			array('date_create,users_id,reports_id,storage_value', 'safe'),
		);
	}



    public function beforeSave(){
        if($this->isNewRecord){
            $this->date_create = new \CDbExpression('now()');
            $this->users_id = \Yii::app()->user->id;
        }
        return true;
    }
    
    
    public static function getStorage($reports_id){
        $result = false;
        
        $model = self::model()->find(array(
                                'condition' => 'users_id=:users_id AND reports_id=:reports_id',
                                'params' => array(
                                            'users_id' => \Yii::app()->user->id, 
                                            'reports_id' => $reports_id,
                                             
                                            ),
                                        ));
        if(empty($model)) return $result;
        
        return json_decode($model->storage_value, true);
    }
    
    


    public static function addToStorage($reports_id, $type, $value){
        $model = self::model()->find(array(
                                'condition' => 'users_id=:users_id AND reports_id=:reports_id',
                                'params' => array(
                                            'users_id' => \Yii::app()->user->id, 
                                            'reports_id' => $reports_id,
                                             
                                            ),
                                        ));
        if(!empty($model)){
            $storage_value = json_decode($model->storage_value, true);    
        } else {
            $model = new \Reports\models\ReportsUsersStorageModel();
            $storage_value = array();
        }  
        
        
        switch($type){
            case 'date_interval' :
                if(isset($value['date_interval']))
                foreach($value['date_interval'] as $key => &$data){
                    $data = date('Y-m-d', strtotime($data));
                }
                unset($data);
                unset($storage_value['date_interval']);
                
                break; 
            case 'graph_indicators' :
                $unique_index = array_keys($value['graph_indicators']);
                if(isset($storage_value['graph_indicators'][$unique_index[0]]))
                    unset($storage_value['graph_indicators'][$unique_index[0]]);
                break;
            case 'graph_period' :
                $unique_index = array_keys($value['graph_period']);
                if(isset($storage_value['graph_period'][$unique_index[0]]))
                    unset($storage_value['graph_period'][$unique_index[0]]);
                break;
            case 'graph_display_option' :
                $unique_index = array_keys($value['graph_display_option']);
                if(isset($storage_value['graph_display_option'][$unique_index[0]]))
                    unset($storage_value['graph_display_option'][$unique_index[0]]);
                break;
            default :
                return; 
        }

        $storage_value = \Helper::arrayMerge($storage_value, $value);
        
        
        if($model->isNewRecord){
            $model->reports_id = $reports_id;
        }
        
        $model->storage_value = json_encode($storage_value);
        $model->save();
    }











    /**
     * clearStorage
     * @param $reports_id
     */
    public static function clearStorage($reports_id){
        $model = static::model();
        $storage_model = $model->findAll(array(
            'condition' => 'reports_id=:reports_id',
            'params' => array(':reports_id' => $reports_id),
        ));

        if(empty($storage_model)) return;

        $schema = \Reports\models\ReportsModel::getSavedSchema($reports_id);

        $indicators = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema, 'data_analysis_indicator');
        if(empty($indicators)) return;

        $unique_index_list = array();
        foreach($indicators as $item){
            $unique_index_list[] = $item['unique_index'];
        }


        foreach($storage_model as $storage){
            if(empty($storage->storage_value)) continue;
            $storage_value = json_decode($storage->storage_value, true);

            if(!is_array($storage_value)) continue;
            if(empty($storage_value['graph_indicators'])) continue;

            $sv_tmp = array();
            $changed = false;
            foreach($storage_value as $key => $value){
                if($key != 'graph_indicators'){
                    $sv_tmp[$key] = $value;
                } else {
                    $indicator_list = array();
                    if(!empty($value)){
                        foreach($value as $key_ind => $indicators){
                            if(!empty($indicators)){
                                foreach($indicators as $indicator){
                                    if(!in_array($indicator, $unique_index_list)){
                                        $changed = true;
                                        continue;
                                    }
                                    $indicator_list[$key_ind][] = $indicator;
                                }
                            }
                        }
                    }
                    if(!empty($indicator_list))
                        $sv_tmp[$key] = $indicator_list;
                }
            }

            if($changed && !empty($sv_tmp)){
                $storage->storage_value = json_encode($sv_tmp);
                $storage->save();
            } elseif(empty($sv_tmp)){
                $storage->delete();
            }
        }
    }




}
