<?php


class CommunicationController extends Controller
{


    public function filters(){
        return array(
            'checkAccess',
        );
    }

    public function filterCheckAccess($filterChain)
    {
        switch (Yii::app()->controller->action->id) {
            case 'serviceParams':
            case 'CheckServiceParams':
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, ExtensionCopyModel::MODULE_COMMUNICATIONS, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;
            case 'UpdateService':
            case 'SaveServiceParams':
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, Yii::app()->getModule('Communications')->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->getModule('Communications')->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;

        }

        $filterChain->run();
    }


    public function actionUpdateService()
    {
        $data = array(
            'communication_params_model' => (new \CommunicationsServiceParamsModel())->getParamsModel(\Communications\models\ServiceModel::SERVICE_NAME_EMAIL),
        );

        return $this->renderJson(array(
                'status' => true,
                'html' => $this->renderPartial(ViewList::getView('dialogs/communicationServiceParams'), $data, true),
            )
        );
    }



    public function actionServiceParams(){
        if (!empty($_POST)){

            $data = array(
                'communication_params_model' => (new \CommunicationsServiceParamsModel())->getParamsModel(\Communications\models\ServiceModel::SERVICE_NAME_EMAIL),
            );

            $this->renderPartial(ViewList::getView('dialogs/communicationServiceParams'), $data);
        }
    }



    public function actionSaveServiceParams(){

        if(empty($_POST)){
            return false;
        }

        $params_model = new CommunicationsServiceParamsModel();
        $params_model
            ->getParamsModel($_POST['source_name'])
            ->setAttributeParams($_POST);

        $result = $params_model->validateEmailParams();

        if (!empty($result['status'])) {
            if ($result['status'] === 'error') {
                return $this->renderJson(array(
                    'status' => 'error_validate',
                    'messages' => $params_model->getMessages(),
                ));
            }

            if ($result['status'] === 'error_email_connect') {
                return $this->renderJson(array(
                    'status' => 'error_email_connect',
                    'messages' => $result['messages'],
                ));
            }
            if(($result['status'] === true) && (!empty($result['result']['changed_params']))){
                $attribute_params = $params_model->getAttributeParams();
                foreach ($result['result']['changed_params'] as $key => $value){
                    $attribute_params['list'][$key] = $value;
                }
                $params_model->setAttributeParams($attribute_params);
            }
        }

        $params_model
            ->prepareParams()
            ->saveParams(\Communications\models\ServiceModel::SERVICE_NAME_EMAIL);

        return $this->renderJson(['status' => true]);
    }


    public function actionCheckServiceParams()
    {
            $check = CommunicationsServiceParamsModel::issetUserParams() ? true : false;

            return $this->renderJson(array(
                    'status' => true,
                    'check' => $check,
                )
            );
    }




}
