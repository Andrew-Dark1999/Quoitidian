<?php


namespace Communications\extensions\ElementMaster;


class EditViewBuilder extends \EditViewBuilder{

    private $_schema_block_participant = null;
    private $_block_participant_two = false;





    /**
     * Возвращает елемент "Блок Активаность" (Activity)
     * @return string (html)
     */
    protected function getEditViewElementBlockActivity($schema){
        \ViewList::setViews(array(
            'ext.ElementMaster.EditView.Elements.Activity.Activity' => 'Communications\extensions\ElementMaster\EditView\Elements\Activity\Activity')
        );

        return parent::getEditViewElementBlockActivity($schema);
    }




    /**
     * Блокировка поля выбора сервисов
     */
    public function getEditViewElementEdit($schema){
        if( $schema['params']['type'] == \Fields::MFT_SELECT && $schema['params']['name'] == 'communication_source') {
            if($this->_extension_data->isNewRecord == false && $this->_extension_data['communication_source']){
                $schema['params']['input_attr'] = array('disabled' => true);
            }
        }
        return parent::getEditViewElementEdit($schema);
    }


    public function getEditViewElementBlock($schema){
        $result = parent::getEditViewElementBlock($schema);

        if($this->_schema_block_participant){
            $this->_block_participant_two = true;
            $schema['params']['title'] = \Yii::t('communications', 'Participants by email');
            $result.= parent::getEditViewElementBlock($schema);
            $this->_schema_block_participant = null;
            $this->_block_participant_two = false;
        }

        return $result;
    }


    /**
     * Возвращает елемент "картка Учасников" (BlockParticipant)
     * @return string (html)
     */
    public function getEditViewElementBlockParticipant($schema){
        if($this->_schema_block_participant === null){
            $this->_schema_block_participant = $schema;
        }

        if(empty($schema)) return false;
        if(count($schema) == 0) return false;


        \ViewList::setViews(array(
                'ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock' => 'Communications\extensions\ElementMaster\EditView\Elements\ParticipantBlock\ParticipantBlock')
        );

        $participant_model_list = [];
        $edit_partisipants = true;
        $content = '';
        $bil_type_item_list_email = ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT;

        if($this->_extension_data->isNewRecord == false){
            //проверка доступа на изменение подписчиков
            if($this->_block_participant_two == false){
                $edit_partisipants = \ParticipantModel::model()->checkUserSubscription(
                    $this->_extension_copy->copy_id,
                    $this->_extension_data->{$this->_extension_copy->prefix_name . '_id'},
                    $this->_extension_data);

                // participant
                $participant_model = \ParticipantModel::getParticipantSaved(
                    $this->_extension_copy->copy_id,
                    $this->_extension_data->{$this->_extension_copy->prefix_name . '_id'}
                );

                $content.= $this->getEditViewElementBlockParticipantParticipantContent($participant_model);
                $participant_model_list = array_merge($participant_model_list, $participant_model);

            } else {
                // email
                $participant_model = \ParticipantEmailModel::getParticipantSaved(
                    $this->_extension_copy->copy_id,
                    $this->_extension_data->{$this->_extension_copy->prefix_name . '_id'}
                );

                $content .= $this->getEditViewElementBlockParticipantEmailContent($participant_model);
                $participant_model_list = array_merge($participant_model_list, $participant_model);
            }

        }

        if($this->_block_participant_two){
            $bil_type_item_list_email = ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL;
        }


        $result = \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock'),
            array(
                'view' => 'block',
                'schema' => $schema,
                'extension_copy' => $this->_extension_copy,
                'content' => $content,
                'participant_data' => $participant_model_list,
                'edit_partisipants' => $edit_partisipants,
                'extension_copy_data' => $this->_extension_data,
                'bil_type_item_list_email' => $bil_type_item_list_email,
            ),
            true);

        return $result;
    }




    /**
     * Возвращает елемент "картка Учасников" (BlockParticipant)
     * @return string (html)
     */
    public function getEditViewElementCardParticipant($participant_data_entities){
        \ViewList::setViews(array(
            'ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock' => 'Communications\extensions\ElementMaster\EditView\Elements\ParticipantBlock\ParticipantBlock')
        );
        return parent::getEditViewElementCardParticipant($participant_data_entities);
    }




}
