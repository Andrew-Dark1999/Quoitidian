<?php

class UsersStorageModel extends \ActiveRecord
{
    // константы поля type
    const TYPE_LIST_FILTER      = 1;    // установленный фильтр
    const TYPE_LIST_SORTING     = 2;    // установленныя сортировка для ListView и ProcessView
    const TYPE_LIST_PAGINATION  = 3;    // пагинация в ListView
    const TYPE_LIST_TH_HIDE     = 4;    // скрытые поля в ListView
    const TYPE_LIST_TH_POSITION = 14;   // сортировка полей в ListView
    const TYPE_LIST_TH_WIDTH    = 15;    // ширина поля в ListView
    const TYPE_PAGE_PARAMS      = 5;    // параметры активной страницы
    //const TYPE_LANGUAGE         = 6;    // активная локаль
    const TYPE_PV_SORTING_PANEL = 7;    // массив поле для групировки в ProcessView
    const TYPE_EV_BLOCK_DISPLAY = 8;    // атрибуты блоков в EditView
    const TYPE_PV_SECOND_FIELDS = 9;    // ProcessView - второе активное поле
    const TYPE_BACK_AFTER_LOGIN = 10;   // последняя открытая страница
    const TYPE_MENU_COUNT       = 11;   // количество пунктов меню Модулей
    const TYPE_LIST_TH_HIDE_FIRST_TIME = 12; // скрытые поля в ListView, которые первый раз не отображаются
    const TYPE_FINISHED_OBJECT  = 13;   // Параметр (переключатель данных) "Завершенные обьекты"



    public $tableName = 'users_storage';



    /**
     * возвращает константу по ее строковому названию
     */
    public function getType($type_str){
        switch($type_str){
            case 'list_filter' :       return self::TYPE_LIST_FILTER;
            case 'list_pagination' :   return self::TYPE_LIST_PAGINATION;
            case 'list_th_hide' :      return self::TYPE_LIST_TH_HIDE;
            case 'list_th_position' :  return self::TYPE_LIST_TH_POSITION;
            case 'list_th_width' :     return self::TYPE_LIST_TH_WIDTH;
            case 'page_params' :       return self::TYPE_PAGE_PARAMS;
            //case 'language' :          return self::TYPE_LANGUAGE;
            case 'pv_sorting_panel' :  return self::TYPE_PV_SORTING_PANEL;
            case 'ev_block_display' :  return self::TYPE_EV_BLOCK_DISPLAY;
            case 'pv_second_fields' :  return self::TYPE_PV_SECOND_FIELDS;
            case 'menu_count' :        return self::TYPE_MENU_COUNT;
            case 'finished_object' :   return self::TYPE_FINISHED_OBJECT;
        }
    }


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('users_id, type', 'numerical', 'integerOnly'=>true),
            array('storage_index', 'length', 'max'=>255),
            array('storage_value', 'length', 'max'=>2000),
            array('storage_id, date_update, users_id', 'safe'),
        );
    }

    public function relations(){
        return array(
        );
    }


    public function beforeSave(){
        $this->date_update = new CDbExpression('now()');
        $this->users_id = \WebUser::getUserId();
        return true;
    }


    public function setValue($value){
        $this->storage_value = json_encode($value);
    }

    public function getValue(){
        return json_decode($this->storage_value, true);
    }



    public function afterFind(){
        parent::afterFind();


        // check and prepare TYPE_LIST_FILTER (Filter list)
        if($this->type == self::TYPE_LIST_FILTER){
            $filetrs_list = $this->getValue();
            if(empty($filetrs_list)) return;

            $filters = [];
            $storage_index = explode('_', $this->storage_index);
            $filter_class = '\FilterModel';

            if(count($storage_index) > 1 && $storage_index[0] == \ExtensionCopyModel::MODULE_REPORTS){
                \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_REPORTS)->getModule(false);
                $filter_class = '\Reports\models\ReportsFilterModel';
            }

            foreach($filetrs_list as $filter){
                if(empty($filter['id'])) continue;
                if($filter['id'] < 0){
                    $filters[] = ['id' => $filter['id']];
                    continue;
                }

                $filter_model = $filter_class::model()->findByPk($filter['id']);
                if($filter_model == false) continue;
                if($filter_model->getAccessToChange() == false) continue;
                $filters[] = ['id' => $filter['id']];
            }

            $this->value = $filters;
        }


        return true;
    }


}
