<?php
/**
* Table widget
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\ElementMaster\Report\Table;

class Table extends \CWidget{
 
    public $table_data = null;
    public $schema = null;

    // Показывает аватар связаного модуля;
    public $title_add_avatar = false;
    // Для файлов возвращаются только ссылки
    public $files_only_url = false;


    public static $_parent_extension_copy;
    public static $_data = array();

    
    
    public function init(){
        $indicators = \Reports\extensions\ElementMaster\Schema::getInstance()->getDataAnalysisEntityesBySchema($this->schema);

        $schema_param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($this->schema, 'data_analysis_param');

        self::$_parent_extension_copy = \ExtensionCopyModel::model()->findByPk($schema_param['module_copy_id']);

        self::$_data = \Reports\models\ReportsTableModel::getInstance()
                                ->setSchema($this->schema)
                                ->setData($this->table_data)
                                ->setParentExtensionCopy(self::$_parent_extension_copy)
                                ->prepare('id')
                                ->getResultData();

        $this->render('element', array(
                                'table_data' => $this->table_data,
                                'indicators' => $indicators,
                    ));
    }
 
 




    public function showColumn($field_name){
        if($field_name != 'param_x') return true;;

        if($field_name == 'module_title'){
            $params = self::$_parent_extension_copy->getFieldSchemaParams('module_title');
            if($params['params']['type'] == 'display_none'){
                return false;
            }
        }

        return true;
    }



    public function getTd($unique_index, $value, $data_id){
        $schema_param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($this->schema, 'data_analysis_param');
        $is_primary = null;

        if(\Reports\models\ConstructorModel::isPeriodConstant($schema_param['field_name']) && $unique_index == 'param_x'){
            return $value;
        } else {
            if($unique_index == 'param_x'){
                if($data_id === null){
                    return $value;
                }

                $field_name = $schema_param['field_name'];

                if($field_name == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
                    $field_name = self::$_parent_extension_copy->getPrimaryViewFieldName();
                    $is_primary = true;
                }
                return $this->getListViewElememnt($field_name, self::$_data[$data_id], $is_primary);
            } else {

                $schema_indicators = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($this->schema, 'data_analysis_indicator');
                foreach($schema_indicators as $indicator){
                    $indicator_type = $indicator['type_indicator'];
                    if($indicator['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT) $indicator_type = 'amount';

                    if('f' . $indicator['unique_index'] == $unique_index){
                        if($indicator['field_name'] == false) return;
                        if($schema_param['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
                            if(
                                ($indicator['module_copy_id'] == self::$_parent_extension_copy->copy_id && !empty($indicator['field_type']) && $indicator['field_type'] == 'numeric' && $indicator['type_indicator'] == \Reports\models\ConstructorModel::TI_PERCENT) ||
                                ($indicator['module_copy_id'] != self::$_parent_extension_copy->copy_id && !empty($indicator['field_type']) && $indicator['field_type'] == 'numeric') ||
                                ($indicator['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT)
                            ){
                                return \Reports\models\ConstructorModel::formatNumber($indicator_type, $value, ',', ' ', array('percent_value' => '%'));
                            } else {
                                return $this->getListViewElememnt($indicator['field_name'], self::$_data[$data_id]);
                            }
                        } else {
                            return \Reports\models\ConstructorModel::formatNumber($indicator_type, $value, ',', ' ', array('percent_value' => '%'));
                        }

                    }
                }
            }
        }

        return false;
    }




    private function getListViewElememnt($field_name, $value_data, $is_primary = null){
        $params = self::$_parent_extension_copy->getFieldSchemaParams($field_name);
        $params = $params['params'];
        if($params['type'] == 'relate_string') $params['type'] = 'display';
        if($is_primary !== null){
            $params['is_primary'] = $is_primary;
        }
        \ListViewBulder::$primary_link_aded = true;

        $html = \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'),
            array(
                'extension_copy' => self::$_parent_extension_copy,
                'params' => $params,
                'value_data' => $value_data,
                'primary_link' => \ListViewBulder::PRIMARY_LINK_REPORT,
                'be_primary_field' => false,
                'relate_add_avatar' => true,
                'title_add_avatar' => $this->title_add_avatar,
                'files_only_url' => $this->files_only_url,

            ), true);

        return $html;
    }




}
