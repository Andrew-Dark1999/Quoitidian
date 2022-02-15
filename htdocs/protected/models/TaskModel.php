<?php
/**
 * TaskModel
 */

class TaskModel extends ActiveRecord{

    public $tableName = 'tasks';
    public $new = true;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return array
     */
    /*
    public function relations()
    {
        return array(
            'taskView' => array(self::HAS_MANY, 'HistoryTaskMarkViewModel', array('task_id' => 'zadachi_id')),
            //'processOperations' => array(self::HAS_ONE, '\Process\models\OperationsModel', array('card_id' => 'zadachi_id'), 'on' => 'processOperations.copy_id = ' . \ExtensionCopyModel::MODULE_TASKS)
        );
    }
    */






    private static function getCountNewEntites($date_start, $data_model){
        $data_model->andWhere('date_create > "'.$date_start.'"');

        $count = $data_model->findCount();

        return ($count ? (int)$count : 0);
    }



    private static function getCountUpdatedEntites($date_start){
        //if($date_start == false) return false;
        return true;
    }



    private static function getTasksDataModelForNotice(){
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_TASKS);
        $schema_status = $extension_copy->getStatusField();
        if(empty($schema_status)){
            return;
        }

        $data_model = (new DataModel)
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();

        $condition_participant = '
                 EXISTS (
                    SELECT t1.data_id
                    from
                        {{participant}} t1
                    where
                         t1.copy_id = ' . \ExtensionCopyModel::MODULE_TASKS . ' AND
                         t1.data_id = {{tasks}}.zadachi_id AND
                         t1.ug_id = ' . \WebUser::getUserId() . ' AND
                         t1.ug_type = "' . \ParticipantModel::PARTICIPANT_UG_TYPE_USER . '" AND
                         t1.responsible = "1"
                    )';

        $data_model->andWhere($condition_participant);
        $data_model->andWhere('({{tasks}}.is_bpm_operation is NULL OR {{tasks}}.is_bpm_operation = "0")');
        $data_model->andWhere('({{tasks}}.' . $schema_status['params']['name'] . ' is NULL OR {{tasks}}.' . $schema_status['params']['name'] . ' != 1)');


        //responsible
        if($extension_copy->isResponsible()){
            $data_model->setFromResponsible(false);
        }

        //participant
        if($extension_copy->isParticipant()){
            $data_model->setFromParticipant(false);
        }

        $data_model->andWhere(array('AND', $extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_MODULE.'" OR ' . $extension_copy->getTableName() . '.this_template is null'));

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup();


        //participant only
        if($extension_copy->dataIfParticipant() && ($extension_copy->isParticipant() || $extension_copy->isResponsible())){
            $data_model->setOtherPartisipantAllowed($extension_copy->copy_id);
        }

        // Добавляет условие отбора данных "только участники по связи через модуль через поле Название
        if(!$extension_copy->dataIfParticipant() && ($extension_copy->isParticipant() || $extension_copy->isResponsible())){
            $data_model->setDataBasedParentModule($extension_copy->copy_id);
        }

        $data_model->withOutRelateTitleTemplate();

        $data_model->setSelectNew();

        return $data_model;
    }





    /**
     * getUserTasks - список активных задач пользователя для вывода уведомлений
     */
    public static function getUserTasks(array $query_vars = null){
        $result = array(
            'total' => 0,       //количество всех данных
            'new'   => 0,       //количество новых данных
            'updated' => false, //отметка об обновлении старых данных
            'data'  => array(), //данные
        );

        $offset = 0;
        $limit_default = 20;
        $limit = $limit_default;
        $date_last = null;
        $limit_append = false;
        $get_new = false;
        $get_notice_count = true;      // возвратить суммы

        if($query_vars){
            foreach($query_vars as $key => $value){
                ${$key} = $value;
            }
        }
        unset($value);


        if($query_vars){
            foreach($query_vars as $key => $value){
                ${$key} = $value;
            }
        }
        unset($value);

        if(!Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_VIEW, \ExtensionCopyModel::MODULE_TASKS)){
            return $result;
        }

        $extension_copy = ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_TASKS);
        if(!$extension_copy){
            return $result;
        }




        $schema_status = $extension_copy->getStatusField();
        if(empty($schema_status)){
            return $result;
        }


        $data_model = self::getTasksDataModelForNotice();

        // offset, limit
        if($date_last !== null){
            $count_new_entities = static::getCountNewEntites($date_last, $data_model);
            if($count_new_entities){
                if($get_new){
                    $limit += $count_new_entities;
                } else{
                    $offset = $count_new_entities - 1;
                }
            }
        }

        if($limit_append){
            $count_update_entities = static::getCountUpdatedEntites($date_last);
            if($count_update_entities){
                $result['updated'] = true;
                $limit += $limit_default;
            } else {
                $offset += (($limit) ? $limit : 0);
                $limit = $limit_default;
            }
        }


        // count
        if($get_notice_count){
            // total
            $data_model = self::getTasksDataModelForNotice();
            $total = $data_model->findCount();
            if(!$total){
                return $result;
            }

            $data_model = self::getTasksDataModelForNotice();
            $data_model->join('history_tasks_mark_view', 'zadachi_id = {{history_tasks_mark_view}}.task_id AND {{history_tasks_mark_view}}.user_id = ' . WebUser::getUserId());
            $data_model->andWhere('{{history_tasks_mark_view}}.id is not NULL');

            $total_old = $data_model->findCount();

            $result['total'] = $total;
            $result['new'] = $total - $total_old;
        }

        // data
        $data_model = self::getTasksDataModelForNotice();
        $data_model
            ->setSelect('data.*, if({{history_tasks_mark_view}}.id, 0, 1) as xsort, {{process_operations}}.process_id, {{process_operations}}.unique_index')
            ->join('history_tasks_mark_view', 'zadachi_id = {{history_tasks_mark_view}}.task_id AND {{history_tasks_mark_view}}.user_id = ' . WebUser::getUserId())
            ->clearJoinedList()
            ->join('process_operations', '{{process_operations}}.copy_id = '.\ExtensionCopyModel::MODULE_TASKS.' AND zadachi_id = {{process_operations}}.card_id AND {{process_operations}}.element_name in ("task", "agreetment")')
            ->setOrder('xsort desc, module_title')
            ->setOffSet($offset)
            ->setLimit($limit);

        $task_list = $data_model->findAll();



        if($task_list){
            $schema_data_end = $extension_copy->getDateEndingField();

            foreach($task_list as $attributes){
                $attributes['date_end'] = '';
                $attributes['date_end_ad'] = false;
                $attributes['copy_id'] = \ExtensionCopyModel::MODULE_TASKS;
                $attributes['new'] = ($attributes['xsort'] ? true : false);

                if(!empty($schema_data_end)){
                    $attributes['date_end'] = $attributes[$schema_data_end['params']['name']];
                    $attributes['date_end_ad'] = (bool)$attributes[$schema_data_end['params']['name'] . '_ad'];;
                    unset($attributes[$schema_data_end['params']['name']]);
                }

                $result['data'][] = $attributes;
            }
        }

        return $result;
    }










    /**
     * @param $task_id
     */
    public static function markTaskIsView($task_id){
        $participant = \ParticipantModel::getParticipantsByUserId(WebUser::getUserId(), ExtensionCopyModel::MODULE_TASKS, false, $task_id);
        if(empty($participant)){
           return;
        }

        if(!HistoryTaskMarkViewModel::model()->exists(
            'task_id=:task_id and user_id =:user_id',
            array(
                ':task_id' => $task_id,
                ':user_id' => WebUser::getUserId()
            )
        )){
            $taskView = new HistoryTaskMarkViewModel();
            $taskView->setAttribute('user_id', WebUser::getUserId());
            $taskView->setAttribute('task_id', $task_id);
            $taskView->save();
        }
    }







    /**
     * @param $task_id
     */
    public static function deleteMarkTaskIsView($task_id){
        if(WebUser::getUserId()) {
            HistoryTaskMarkViewModel::model()->deleteAll(
                'task_id =:task_id and user_id !=:user_id',
                array(
                    ':task_id' => $task_id,
                    ':user_id' => WebUser::getUserId()
                )
            );
        }
    }





}
