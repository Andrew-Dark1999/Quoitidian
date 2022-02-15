<?php
/**
 * @author Alex R.
 */

class HeaderNoticeModel{


    const HN_ID_TASKS    = 'header_task_bar';
    const HN_ID_MESSAGES = 'header_messages';
    const HN_ID_NOTICE   = 'header_notification_bar';

    private $_id;
    private $_vars;

    private $_notice_data;
    private $_result;




    public function setId($id){
        $this->_id = $id;
        return $this;
    }


    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }



    private function getStatus(){
        return true;
    }


    public function getResult(){
        $this->_result['status'] = $this->getStatus();

        return $this->_result;
    }


    /**
     * prepare
     */
    public function prepare(){
        $this
            ->prepareLimit()
            ->prepareOffSet()
            ->prepareGetNoticeCount();


        switch($this->_id){
            case self::HN_ID_NOTICE:
                $this->prepareEntitiesNotice();
                break;
            case self::HN_ID_TASKS:
                $this->prepareEntitiesTask();
                break;
        }

        $this
            ->addNoticeHtmlList()
            ->addAfterParams();

        return $this;
    }


    /**
     * prepareEntitiesNotice
     */
    private function prepareEntitiesNotice(){
        $this
            ->prepareDataHistory();
    }


    /**
     * prepareEntitiesTask
     */
    private function prepareEntitiesTask(){
        $this
            ->prepareDataTask();

    }


    /**
     * prepareDataHistory
     */
    private function prepareDataHistory(){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        $this->_notice_data = History::getInstance()->getFromHistory(HistoryMessagesModel::OBJECT_NOTICE, $this->_vars);


        // подготовка данных после события установки отметки об просмотре
        /*
        if($this->isSetVarsProperty('notice_history_id_list') && $this->_vars['notice_history_id_list'] && $this->_notice_data['data']){
            $notice_data_list = [];
            foreach($this->_notice_data['data'] as $notice_data){
                if(!in_array($notice_data['history_model']->history_id, $this->_vars['notice_history_id_list'])){
                    continue;
                }
                $notice_data_list[] = $notice_data;
            }

            $c = count($this->_vars['notice_history_id_list']) - count($notice_data_list);
            if($c){
                $notice_data_list = array_merge($notice_data_list, array_slice($this->_vars['notice_history_id_list'], -$c));
            }

            $this->_notice_data['data'] = $notice_data_list;
        }
        */


        return $this;
    }


    /**
     * prepareDataTask
     */
    private function prepareDataTask(){
        $this->_notice_data = TaskModel::getUserTasks($this->_vars);

        return $this;
    }


    /**
     * prepareNoticeHtmlList
     */
    private function addNoticeHtmlList(){
        $this->_result['notice_html_list'] = array();

        if($this->_notice_data){
            $notices_winget = \Yii::app()->controller->widget('ext.ElementMaster.HeaderNotices.Notices', array('id' => $this->_id, 'data' => $this->_notice_data));
            $this->_result['notice_html_list'] = $notices_winget
                                                    ->initAuto()
                                                    ->buildInner()
                                                    ->getResult();
        }

        return $this;
    }


    /**
     * isSetVarsProperty
     * @param $property_name
     * @return bool|void
     */
    private function isSetVarsProperty($property_name){
        if($this->_vars == false) return;
        if(array_key_exists($property_name, $this->_vars)) return true;
    }




    /**
     * addLastParams
     * @return $this
     */
    private function addAfterParams(){
        $this
            ->addNoticeDataCount()
            ->addNoticeDataUpdated()
            ->addDateLast();

        return $this;
    }


    /**
     * addNoticeDataCount
     */
    private function addNoticeDataCount(){
        if($this->isSetVarsProperty('get_notice_count') == false) return $this;

        if($this->_vars['get_notice_count'] == false) return $this;

        $this->_result['counts'] = [
            'total' => (!empty($this->_notice_data['total']) ? $this->_notice_data['total'] : 0),
            'new' => (!empty($this->_notice_data['new']) ? $this->_notice_data['new'] : 0),
        ];

        return $this;
    }


    /**
     * addNoticeDataUpdated
     */
    private function addNoticeDataUpdated(){
        $this->_result['updated'] = $this->_notice_data['updated'];

        return $this;
    }


    /**
     * addDateLast
     */
    private function addDateLast(){
        if($this->isSetVarsProperty('get_date_last') == false) return $this;

        if($this->_vars['get_date_last'] == false) return $this;

        $this->_result['date_last'] = date('Y-m-d H:i:s');

        return $this;
    }







    /**
     * addRelatedHistoryIdList
     */
    public function getRelatedHistoryIdList($history_id){
        $query = '
            SELECT t1.history_id
            FROM {{history}} as t1
            LEFT JOIN {{history_mark_view}} t3 ON t1.history_id = t3.history_id
            WHERE
                  exists (
                        SELECT t2.history_id
                        FROM {{history}} t2
                        WHERE
                             t2.history_id = '.$history_id.' AND
                             copy_id = t1.copy_id AND
                             data_id = t1.data_id
                             )
                  AND t3.user_id = '.\WebUser::getUserId().'
                  AND t3.is_view is NULL
        ';

        $data = (new \DataModel())->setText($query)->findCol();

        return ($data ? $data : array($history_id));
    }




    /**
     * prepareLimit
     * @return $this|void
     */
    private function prepareLimit(){
        return $this;
        /*
        if($this->isSetVarsProperty('limit') == false) return $this;
        if($this->isSetVarsProperty('get_related') == false) return $this;

        if(count($this->_result['notice_history_id_list']) <= 1){
            return $this;
        }

        $this->_vars['limit'] += ($this->_result['notice_history_id_list'] - 1);

        return $this;
        */
    }


    /**
     * prepareOffSet
     * @return $this|void
     */
    private function prepareOffSet(){
        $offset = 0;

        $this->_vars['offset'] = $offset;

        return $this;
    }



    /**
     * prepareGetNoticeCount
     * @return $this|void
     */
    private function prepareGetNoticeCount(){
        $result = true;

        $this->_vars['get_notice_count'] = $result;

        return $this;
    }




}

