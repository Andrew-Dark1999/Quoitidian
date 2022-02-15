<?php

class FileController extends Controller
{

    /**
     * filter
     */
    public function filters()
    {
        return [
            'checkAccess',
        ];
    }

    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain)
    {
        switch (Yii::app()->controller->action->id) {
            case 'uploadFile':
            case 'uploadUrlLink':
            case 'showGoogleDocView':
                if (empty($_POST['copy_id'])) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'Not defined parameters'), false);
                }
                if ((integer)$_POST['copy_id'] > 0) {
                    Access::setAccessCheckParams($_POST['copy_id']);
                    if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Access::getAccessCheckParams('access_id'), Access::getAccessCheckParams('access_id_type'))) {
                        return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                    }
                } else {
                    if ((integer)$_POST['copy_id'] === -1) {
                        if (isset($_POST['thumb_scenario']) && $_POST['thumb_scenario'] == 'profile') {
                            $_POST['copy_id'] = ExtensionCopyModel::MODULE_STAFF;
                        }
                    } else {
                        return false;
                    }
                }
                break;

        }

        $filterChain->run();
    }

    /**
     * Загрузка файла на сервер
     */
    public function actionUploadFile()
    {
        $fileUpload = new FileUpload($_POST);

        if ($fileUpload->upload()) {
            return $this->renderJson([
                'status'   => true,
                'fileInfo' => $fileUpload->getFileInfo(),
                'view'     => $fileUpload->getView(),
            ]);
        } else {
            return $this->renderJson([
                'status'   => false,
                'messages' => $fileUpload->getValidateResultHtml(),
            ]);
        }
    }

    /**
     * Загрузка урла на сервер
     */
    public function actionUploadUrlLink()
    {
        $validate = new Validate();
        $view = '';

        if (empty($_POST['url'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));

            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }

        if ($validate->error_count === 0) {
            $model = new UploadsModel();
            $model->setScenario('upload_url');
            $model->setFileType('file_google_doc');

            $model->file_source = UploadsModel::SOURCE_GOOGLE_DOC;
            $model->status = 'temp';
            $model->file_path = $_POST['url'];
            $model->copy_id = $_POST['copy_id'];

            if (!$model->save()) {
                $validate->addValidateResult('e', CHtml::errorSummary($model));
            } else {
                if (isset($_POST['get_view']) && (boolean)$_POST['get_view']) {
                    $model->refresh();
                    $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);

                    if ($_POST['thumb_scenario'] == 'activity') {
                        $view = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
                            [
                                'view'               => 'attachments_one',
                                'schema'             => $extension_copy->getFieldSchemaParamsByType('activity'),
                                'extension_copy'     => $extension_copy,
                                'upload_data'        => $model,
                                'attachents_buttons' => ['download_file', 'delete_file'],
                            ],
                            true);
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
                'view'   => $view,
            ]);
        }

    }

    /**
     * диaлог добавления файла Google Doc
     */
    public function actionShowGoogleDocView()
    {
        $this->renderPartial('//dialogs/upload-google-doc', $_POST);
    }

    /**
     * Удаление файла
     */
    public function actionDeleteFile()
    {
        $validate = new Validate();
        if (empty($_POST['id'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));

            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }
        $model = UploadsModel::model()->findAllByPk($_POST['id']);
        if (empty($model)) {
            $validate->addValidateResult('e', Yii::t('messages', 'Data not available'));
        } else {
            $check = false;
            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_DOCUMENTS)->getModule(false);
            foreach ($model as $model_data) {
                // доступ...
                if (!$check) {
                    Access::setAccessCheckParams($model_data->copy_id);

                    if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, Access::getAccessCheckParams('access_id'), Access::getAccessCheckParams('access_id_type'))) {
                        $validate->addValidateResult('e', Yii::t('messages', 'You do not have access to this object'));
                        break;
                    }
                    $check = true;
                }
                // само удаление
                if ($model_data->relate_key && $_POST['be_relate_key'] == 'false') {
                    continue;
                } //не удаляем, если удаление с формы EditView и есть "relate_key"

                if ($model_data->copy_id == \ExtensionCopyModel::MODULE_DOCUMENTS) {

                    if ($model_data->status == 'temp') {
                        continue;
                    } //не удаляем, это модуль Документы и файл имеет временный статус, т.е. он должен быть заменен генерируемым документом или вручную

                    /*
                    //перед удалением, проверяем таблицу связей с родителем
                    //если удаляемый файл или родитель или не родитель, удаляем в любом случае связь
                    UploadsParentsModel::model()->deleteAll('upload_id=:t1 OR parent_upload_id=:t1', array(':t1'=>$model_data->id));
                    */

                    DocumentsModel::updateUploadsParents($model_data);

                }
                $model_data->delete();
            }
        }

        return $this->renderJson([
            'status'   => ($validate->error_count > 0 ? false : true),
            'messages' => $validate->getValidateResultHtml(),
        ]);
    }

    /**
     * Удаление файла аватара из профиля пользователя
     */
    public function actionDeleteFileAvatar()
    {
        $status = true;
        $view = '';
        $extension_copy = ExtensionCopyModel::model()->findByPk(ExtensionCopyModel::MODULE_STAFF);

        $params = ['elements' => ['field' => $extension_copy->getFieldSchemaParams('ehc_image1')]];

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $extension_copy->getTableName(null, false, true),
            'params'    => Fields::getInstance()->getActiveRecordsParams($params),
        ];

        $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk(WebUser::getUserId());
        $extension_data->scenario = 'update';
        $extension_data->setElementSchema($extension_copy->getSchemaParse());
        $extension_data->extension_copy = $extension_copy;
        $extension_data->setMyAttributes(['ehc_image1' => []]);
        if ($extension_data->save()) {
            ExtensionModel::model()->findByPk(ExtensionModel::MODULE_USERS)->getModule();
            $view = [
                'avatar_140' => ProfileModel::getFileBlockAvatar(),
                'avatar_32'  => (new AvatarModel())->setDataArrayFromUserId()->getAvatar(),
            ];
        } else {
            $status = false;
        }

        return $this->renderJson([
            'status' => $status,
            'view'   => $view,
        ]);
    }

    public function actionUploadFileProgress()
    {
        $percent = 0;
        $data = [''];

        if (isset($_SESSION['upload_progress_test']) and is_array($_SESSION['upload_progress_test'])) {
            $percent = ($_SESSION['upload_progress_test']['bytes_processed'] * 100) / $_SESSION['upload_progress_test']['content_length'];
            $percent = round($percent, 2);
            $data = [
                'percent'         => $percent,
                'content_length'  => $_SESSION['upload_progress_test']['content_length'],
                'bytes_processed' => $_SESSION['upload_progress_test']['bytes_processed'],
            ];
        }
        echo json_encode($data);

    }

    public function actionFileLoad()
    {
        if (isset($_GET) && !empty($_GET)) {
            $result = UploadsModel::fileLoad($_GET);
            if ($result == false) {
                return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
            }
        }
    }

    public function actionLetterImage()
    {
        // генерируем картинку и отдаем ее
        $image = imagecreatetruecolor(1, 1); // создаем холст 1 на 1 пиксель
        imagefill($image, 0, 0, 0xFFFFFF); // делаем его белым
        header('Content-type: image/png'); // задаем заголовок
        imagepng($image); // выводим картинку
        imagedestroy($image); // очищаем память от картинки

        foreach ($_GET as $get_key => $get_value) {
            if (substr($get_key, -4) === ".png") {
                $mailer_id = substr($get_key, 0, -4);
                $outbox_model = MailerLettersOutboxModel::model()->find(
                    'mailer_id=:mailer_id',
                    [':mailer_id' => $mailer_id]
                );

                if ($outbox_model == false) {
                    return;
                }

                MailerLettersOutboxMarkViewModel::insertMarkView($outbox_model->mailer_id);
                ActivityMessagesModel::updateDateEditByLetter($outbox_model);
            }
        }
    }

}
