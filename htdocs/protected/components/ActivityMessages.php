<?php

/**
 * ActivityMessagesController
 *
 * @author Alex R.
 */

class ActivityMessages extends Controller
{

    /**
     * возвращает контент картинок для блока Вложения
     *
     * @return array()
     */
    private function getAttachments($extension_copy, $data_id, $thumb_size = 60)
    {
        $content = [];
        // добавляем файлы блока Активность
        $schema_activity = $extension_copy->getFieldSchemaParamsByType('activity');
        if (!empty($schema_activity)) {
            $activity_model = ActivityMessagesModel::model()->findAll([
                'condition' => 'copy_id=:copy_id AND data_id=:data_id',
                'params'    => [
                    ':copy_id' => $extension_copy->copy_id,
                    ':data_id' => $data_id,
                ],
            ]);

            if (!empty($activity_model)) {
                foreach ($activity_model as $activity) {
                    if (!empty($activity->attachment)) {
                        $buttons = ['download_file', 'delete_file'];
                        if ($activity->user_create != WebUser::getUserId()) {
                            $buttons = ['download_file',];
                        }

                        $upload_model = UploadsModel::model()->setRelateKey($activity->attachment)->findAll();
                        $attachments = (new EditViewBuilder())
                            ->setExtensionCopy($extension_copy)
                            ->getAttachmentsContent($upload_model, $schema_activity, $buttons, true, $thumb_size);
                        if (!empty($attachments)) {
                            foreach ($attachments as $key => $value) {
                                $content[$key] = $value;
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * возвращает контент картинок для блока Вложения
     *
     * @return array()
     */
    private function getAttachmentsByRelateKey($extension_copy, $relate_key, $list_other_id, $thumb_size = 60)
    {
        $content = [];
        if (empty($relate_key)) {
            return $content;
        }

        $schema_activity = $extension_copy->getFieldSchemaParamsByType('activity');
        if (!empty($schema_activity)) {
            $upload_model = UploadsModel::model()->setRelateKey($relate_key)->findAll();
            $attachments = (new \EditViewBuilder())->setExtensionCopy($extension_copy)->getAttachmentsContent($upload_model, $schema_activity, ['download_file', 'delete_file'], true, $thumb_size);
            if (!empty($attachments)) {
                foreach ($attachments as $key => $value) {
                    $content[$key] = $value;
                }
            }

            if (!empty($list_other_id)) {
                $data_model = new DataModel();
                $data_model
                    ->setFrom('{{activity_messages}}')
                    ->setWhere('activity_messages_id in (' . addslashes(implode(',', $list_other_id)) . ')');
                $data_model = $data_model->findAll();
                if (!empty($data_model)) {
                    foreach ($data_model as $data_value) {
                        if ($data_value['attachment'] == $relate_key) {
                            continue;
                        }
                        $upload_model = UploadsModel::model()->setRelateKey($data_value['attachment'])->findAll();
                        $attachments = (new \EditViewBuilder())->setExtensionCopy($extension_copy)->getAttachmentsContent($upload_model, $schema_activity, ['download_file', 'delete_file'], true, $thumb_size);
                        if (!empty($attachments)) {
                            foreach ($attachments as $key => $value) {
                                $content[$key] = $value;
                            }
                        }
                    }
                }
            }

        }

        return $content;
    }

    /**
     * Возвращает список уведомлений, что не преднадлежат активному пользователю
     */
    private function getMessageList($extension_copy, $data_id, $date_edit = [])
    {
        $be_editing_message = false;

        $schema = $extension_copy->getFieldSchemaParamsByType('activity');
        $message_alien_new = [];
        $message_alien_editing = [];
        $attachments_new = [];
        $message_alien_deleted = array_keys($date_edit);

        $activity_messages_model_list = ActivityMessagesModel::getActivityMessagesListByDataId($extension_copy, $data_id);

        if (!empty($activity_messages_model_list)) {
            foreach ($activity_messages_model_list as $activity_messages_model) {
                // новое сообщение
                if (empty($date_edit) || (!empty($date_edit) && !array_key_exists($activity_messages_model->activity_messages_id, $date_edit))) {
                    $be_editing_message = true;
                    $message_alien_new[$activity_messages_model->activity_messages_id] = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
                        [
                            'view'                    => 'message_one',
                            'schema'                  => $schema,
                            'extension_copy'          => $extension_copy,
                            'activity_messages_model' => $activity_messages_model,
                        ],
                        true);
                } elseif (isset($date_edit[$activity_messages_model->activity_messages_id]) && DateTimeOperations::dateDiff($date_edit[$activity_messages_model->activity_messages_id], $activity_messages_model->date_edit) !== 0) {
                    $be_editing_message = true;
                    // измененное сообщение
                    $message_alien_editing[$activity_messages_model->activity_messages_id] = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
                        [
                            'view'                    => 'message_one',
                            'schema'                  => $schema,
                            'extension_copy'          => $extension_copy,
                            'activity_messages_model' => $activity_messages_model,
                        ],
                        true);

                }
                // удаленное сообщение
                if (
                    $activity_messages_model->user_create == WebUser::getUserId() ||
                    (in_array($activity_messages_model->activity_messages_id, $message_alien_deleted) && $activity_messages_model->user_create != WebUser::getUserId())
                ) {
                    $key = array_search($activity_messages_model->activity_messages_id, $message_alien_deleted);
                    unset($message_alien_deleted[$key]);
                }
            }

            sort($message_alien_deleted);

            $message_alien[$activity_messages_model->activity_messages_id] =
                Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
                    [
                        'view'                    => 'message_one',
                        'schema'                  => $schema,
                        'extension_copy'          => $extension_copy,
                        'activity_messages_model' => $activity_messages_model,
                    ],
                    true);
        }

        // файлы
        if ($be_editing_message) {
            $attachments_new['thumb_60'] = $this->getAttachments($extension_copy, $data_id, 60);
        }

        return [
            'message_alien_new'     => $message_alien_new,
            'attachments_new'       => $attachments_new,
            'message_alien_editing' => $message_alien_editing,
            'message_alien_deleted' => $message_alien_deleted,
        ];
    }

    private function getNewMessageHtml($extension_copy, $activity_messages_model)
    {
        $html = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
            [
                'view'                         => 'message_one',
                'schema'                       => $extension_copy->getFieldSchemaParamsByType('activity'),
                'extension_copy'               => $extension_copy,
                'activity_messages_model'      => $activity_messages_model,
                'attachments_image_thumb_size' => null,
                'attachents_buttons'           => ['download_file', 'delete_file'],
            ],
            true);

        return $html;
    }

    private function getNewAttachmentHtml($extension_copy, $activity_model, $list_other_id)
    {
        if (!empty($_POST['data_id'])) {
            return $this->getAttachments($extension_copy, $_POST['data_id'], 60);
        } else {
            return $this->getAttachmentsByRelateKey($extension_copy, $activity_model->attachment, $list_other_id, 60);
        }
    }












    //***********************************************************
    //              Actions
    //***********************************************************

    /**
     * возвращает данные об одном уведомлении по указаным паметрам
     */
    public function actionGetMessageById()
    {
        $validate = new Validate();
        $message_text = '';
        $message_attachments = '';

        if (!isset($_POST['copy_id'], $_POST['activity_messages_id'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));

            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }

        $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);
        $activity_model = ActivityMessagesModel::model()->findByPk($_POST['activity_messages_id']);

        if (!empty($activity_model)) {
            $message_text = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
                [
                    'view'                    => 'message_only',
                    'schema'                  => $extension_copy->getFieldSchemaParamsByType('activity'),
                    'extension_copy'          => $extension_copy,
                    'activity_messages_model' => $activity_model,
                ],
                true);
            $message_attachments = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
                [
                    'view'                         => 'attachments',
                    'schema'                       => $extension_copy->getFieldSchemaParamsByType('activity'),
                    'extension_copy'               => $extension_copy,
                    'activity_messages_model'      => $activity_model,
                    'attachents_buttons'           => ['download_file', 'delete_file'],
                    'attachments_image_thumb_size' => 60,
                ],
                true);
        } else {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
        }

        if ($validate->error_count > 0) {
            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        } else {
            return $this->renderJson([
                'status'  => true,
                'message' => [
                    'text'        => $message_text,
                    'attachments' => $message_attachments,
                ],
            ]);
        }
    }

    /**
     * Екшин на список уведомлений, что не преднадлежат активному пользователю
     */
    public function actionGetMessageList()
    {
        if (!isset($_POST['copy_id'], $_POST['data_id']) || empty($_POST['data_id'])) {
            return $this->renderJson([
                'status' => 'ok',
            ]);
        }
        $date_edit = (isset($_POST['date_edit']) ? $_POST['date_edit'] : []);

        $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);

        $message_list = $this->getMessageList($extension_copy, $_POST['data_id'], $date_edit);

        return $this->renderJson([
            'status'       => true,
            'message_list' => $message_list,
        ]);
    }

    /**
     * Добавление сообщения
     */
    public function actionSaveMessage()
    {
        if (empty($_POST['id'])) {
            $activity_model = new ActivityMessagesModel();
        } else {
            $activity_model = ActivityMessagesModel::model()->findByPk($_POST['id']);
        }

        $date_edit = (isset($_POST['date_edit']) ? $_POST['date_edit'] : []);
        $list_other_id = (isset($_POST['list_other_id']) ? $_POST['list_other_id'] : null);

        unset($_POST['id']);
        unset($_POST['date_edit']);
        unset($_POST['list_other_id']);

        $activity_model
            ->setMyAttributes($_POST);

        if ($activity_model->save() == false) {
            return $this->renderJson([
                'status'   => false,
                'messages' => $activity_model->getErrorsHtml(),
            ]);
        }

        if (!empty($_POST['data_id'])) {
            $activity_model->communicationSaveMessage();
        }

        $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);

        return $this->renderJson([
            'status'       => true,
            'message'      => [
                'html'        => $this->getNewMessageHtml($extension_copy, $activity_model),
                'attachments' => $this->getNewAttachmentHtml($extension_copy, $activity_model, $list_other_id),
            ],
            'message_list' => (!empty($_POST['data_id'])) ? $this->getMessageList($extension_copy, $_POST['data_id'], $date_edit) : [],
        ]);
    }

    /**
     * Удаление сообщения
     */
    public function actionDeleteMessage()
    {
        $validate = new Validate();

        if (!isset($_POST['id'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
        } else {
            $activity_model = ActivityMessagesModel::model()->findByPk($_POST['id']);
            if (!empty($activity_model)) {
                if ($activity_model->type_comment == ActivityMessagesModel::TYPE_COMMENT_EMAIL) {
                    if (!$activity_model->isDeleteAvailable()) {
                        return $this->renderJson([
                            'status'   => 'error',
                            'messages' => [\Yii::t('messages', 'Message already sent')],
                        ]);
                    }
                    $activity_model->delete();
                } else {
                    $activity_model->delete();
                }
            }
        }

        if ($validate->error_count > 0) {
            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        } else {
            return $this->renderJson([
                'status' => true,
            ]);
        }
    }

    /**
     * Удаление файла
     */
    public function actionDeleteFile()
    {
        $validate = new Validate();

        if (!isset($_POST['upload_id'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
        } else {
            $uploads_model = UploadsModel::model()->find([
                'condition' => 'id=:id AND user_create=:user_create ',
                'params'    => [
                    ':id'          => $_POST['upload_id'],
                    ':user_create' => WebUser::getUserId(),
                ],
            ]);

            if (empty($uploads_model)) {
                $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            } else {
                $relate_key = $uploads_model->relate_key;
                $uploads_model->delete();
                $uploads_model = UploadsModel::model()->setRelateKey($relate_key)->count();

                if ($uploads_model == 0) {
                    $activity_model = ActivityMessagesModel::model()->find('attachment = "' . $relate_key . '"');
                    if (!empty($activity_model)) {
                        $activity_model->attachment = null;
                        $activity_model->update();
                    }
                }
            }
        }

        if ($validate->error_count > 0) {
            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        } else {
            return $this->renderJson([
                'status' => true,
            ]);
        }

    }

}
