<?php

/**
 * Class HistoryActionsModel - Создания новых уведомлений по опеределенных собитиях в системе
 */




class HistoryActionsModel{

    const ACTION_DATE_ENDING_BECOME = 1;

    private $_history_model;
    private $_extension_copy_list = array();
    private $_ug_type = ParticipantModel::PARTICIPANT_UG_TYPE_USER;
    private $_to_minutes;




    public function setUgType($ug_type)
    {
        $this->_ug_type = $ug_type;
        return $this;
    }


    public function setExtensionCopyList($extension_copy_list){
        if($extension_copy_list == false){
            return $this;
        }

        $this->_extension_copy_list = $extension_copy_list;

        return $this;
    }


    public function setHistoryModel($history_model){
        $this->_history_model = $history_model;
        return $this;
    }



    public function setToMinutes($to_minutes){
        $this->_to_minutes = $to_minutes;
        return $this;
    }




    public function run($action){
        switch($action){
            case self::ACTION_DATE_ENDING_BECOME:
                $this->actionDateEndingBecome();
                break;
        }
    }


    private function actionDateEndingBecome(){
        $this
            ->setExtensionCopyList(\ExtensionCopyModel::getUsersModule())
            ->leaveCopyDateEnding()
            ->leaveCopyWithResponsibles()
            ->createNotifyParticipants();
    }


    //Оставить только модули, в схеме которых есть Ответственный или Участники
    private function leaveCopyWithResponsibles(){
        $new_list = array();
        foreach ($this->_extension_copy_list as $extension_copy){
            if($extension_copy->isResponsible() || $extension_copy->isParticipant()){
                $new_list[] = $extension_copy;
            }
        }

        $this->setExtensionCopyList($new_list);

        return $this;
    }



    //Оставить только модули, в схеме которых есть кнопка "Дата окончания"
    private function leaveCopyDateEnding(){
        $new_list = array();

        foreach ($this->_extension_copy_list as $extension_copy){
            if($extension_copy->getDateEndingField()){
                $new_list[] = $extension_copy;
            }
        }

        $this->setExtensionCopyList($new_list);

        return $this;
    }




    private function getPreparedHistoryMessageIndexByDateEnding($schema_field_date, $card_data){
        $field_name_ad = $schema_field_date['params']['name'] . '_ad';
        $all_day = $card_data[$field_name_ad];

        if($all_day){
            $history_message_index = HistoryMessagesModel::MT_DATE_ENDING_BECOME;
        } else {
            $history_message_index = HistoryMessagesModel::MT_DATE_ENDING_BECOME_TO;
        }

        return $history_message_index;
    }



    //Создать уведомление Участникам карточек
    private function createNotifyParticipants(){
        $extension_copy_list = $this->_extension_copy_list;

        if($extension_copy_list == false){
            return $this;
        }

        $modules_cards = $this->getDateEndingCards($extension_copy_list);

        if($modules_cards == false){
            return $this;
        }

        foreach($extension_copy_list as $extension_copy){
            $copy_id = $extension_copy->copy_id;

            if(array_key_exists($copy_id,$modules_cards)) {
                foreach($modules_cards[$copy_id] as $card_data){
                    $data_id = $card_data[$extension_copy->prefix_name . '_id'];
                    $responsibles = ParticipantModel::getParticipants($copy_id, $data_id, $this->_ug_type, true);

                    $schema_field_date = $extension_copy->getDateEndingField();
                    $history_message_index = $this->getPreparedHistoryMessageIndexByDateEnding($schema_field_date, $card_data);

                    foreach ($responsibles as $responsible) {
                        $user_id = $responsible->ug_id;

                        $this->_history_model->addToHistory(
                            $history_message_index,
                            $copy_id,
                            $data_id,
                            array(
                                '{module_data_title}' => $card_data['module_title'],
                                '{datetime}' => $card_data[$schema_field_date['params']['name']],
                                '{user_id}' => $user_id,
                            ),
                            false,
                            false,
                            true,
                            true
                        );
                    }
                }
            }
        }

        return $this;
    }




    /**
     * getDateEndingCards - возвращает список карточек
     */
    private function getDateEndingCards($extension_copy_list){
        if($this->_to_minutes == false){
            $result = $this->getDateEndingCardsAllDay($extension_copy_list);
        } else {
            $result = $this->getDateEndingCardsToMinutes($extension_copy_list);
        }

        return $result;
    }



    /**
     * getDateEndingCards - возвращает список карточек, где дата окончания наступает "сегодня"
     */
    private function getDateEndingCardsAllDay($extension_copy_list){
        $extension_copy_list_cards = array();

        if($extension_copy_list == false){
            return $extension_copy_list_cards;
        }

        foreach($extension_copy_list as $extension_copy){
            $schema_field_date = $extension_copy->getDateEndingField();

            $between_from = date('Y-m-d 00:00:00');
            $between_to = date('Y-m-d 23:59:59');

            $condition = [
                $schema_field_date['params']['name'] . ' between "' . $between_from . '" AND "' . $between_to .'"',
                $schema_field_date['params']['name'] . '_ad = "1"',
            ];

            $module_cards = DataModel::getInstance()
                ->setFrom($extension_copy->getTableName())
                ->setWhere(implode(' AND ', $condition))
                ->findAll();

            if($module_cards){
                $extension_copy_list_cards[$extension_copy->copy_id] = $module_cards;
            }
        }

        return $extension_copy_list_cards;
    }




    /**
     * getDateEndingCards - возвращает список карточек, где дата окончания за N минут от текувщего времени
     */
    private function getDateEndingCardsToMinutes($extension_copy_list){
        $extension_copy_list_cards = array();

        if($extension_copy_list == false){
            return $extension_copy_list_cards;
        }

        foreach($extension_copy_list as $extension_copy){
            $schema_field_date = $extension_copy->getDateEndingField();

            $date = new DateTime();
            $date->modify('+' . $this->_to_minutes . ' minutes');

            $between_from = $date->format('Y-m-d H:i:00');
            $between_to = $date->format('Y-m-d H:i:59');

            $condition = [
                $schema_field_date['params']['name'] . ' between "' . $between_from . '" AND "' . $between_to .'"',
                '(' . $schema_field_date['params']['name'] . '_ad is null OR ' . $schema_field_date['params']['name'] . '_ad = "0")',
            ];


            $status_fo_id = $this->getFinishedObjectSelectIdList($extension_copy);
            if($status_fo_id){
                $status_params = $extension_copy->getStatusField();
                $condition[] = $status_params['params']['name'] . '!=' . $status_fo_id;

            }

            $module_cards = DataModel::getInstance()
                ->setFrom($extension_copy->getTableName())
                ->setWhere(implode(' AND ', $condition))
                ->findAll();

            if($module_cards){
                $extension_copy_list_cards[$extension_copy->copy_id] = $module_cards;
            }
        }

        return $extension_copy_list_cards;
    }







    public function getFinishedObjectSelectIdList($extension_copy){
        $status_params = $extension_copy->getStatusField();

        if(empty($status_params)){
            return;
        }

        $id = \DataModel::getInstance()
            ->setSelect($status_params['params']['name'] . '_id')
            ->setFrom($extension_copy->getTableName($status_params['params']['name']))
            ->setWhere($status_params['params']['name'] . '_finished_object = "1"')
            ->findScalar();

        if($id){
            return $id;
        }
    }


}
