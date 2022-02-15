<?php

class SipController extends Controller {

    /**
     * filter
     */
    public function filters(){
        return array(
            'checkAccess',
        );
    }




    /*
     *
     *
     *



        Виклик по ссилці
            Відправити на АПИ
            Записати інформацію про звінок
            Інші операторції на беку


        Звінок
            Зробити звінок
            Записати інформацію про звінок
            Інші операторції на беку




        Клік на ссилці
        JS

        Sip
            runAction(action_name, operation, params, callback)
                ACTION_CALL
                ACTION_HUNGUP




        SipPhone
            events
                actionCall() - вызов
                actionHangup() - повесить трубку

        SipElement
            events
                actionCall() - вызов





        PHP

        SipController
            runAction()



        Sip


            __construct($operation)
            init()
            initOperation()
            setParams()
            runAction($action_name)
            beforeAction()
            afterAction()



        SipPhone

        SipElement

        SipActions
            ACTION_CALL
            ACTION_HUNGUP








        PluginsModel
            PluginSourcePhone
                actions
                    MangoOfficeActions
                        actionCall() - вызов
                        actionHangup() - повесить трубку

                api
                    MangoOfficeApi




        Calls module
            ListView


        QuickView - Праве меню

                Communications
                Calls
                SipPhone


        CallsModel
        SipModel







     */




    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'showSipPhone':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }
        $filterChain->run();
    }



    public function actionShowSipPhone(){
       return $this->renderJson(array(
            'status' => true,
            'html' => $this->renderBlock('sip-phone', array(), true),
        ));
    }



    /*
    public function actionGetServiceUserParams(){
        $service_model = PluginSourcePhone::getInstance()->getActiveService();

        if($service_model == false){
            return;
        }

        $user_params_model = $service_model->getUserParamsModel();

        $params = $user_params_model->findByUsersId(\WebUser::getUserId());

        $params = $params;
    }
    */


    /**
     * actionOperationEvent - обработка внешних событий от операторов
     */
    public function actionExternalEventHandler(){
            echo 1;

            print_r($_GET);
    }


    /**
     * actionInternalAction - обработка внутренних действий в СРМ
     */
    public function actionInternalAction(){
        $service_model = PluginSourcePhone::getInstance()->getActiveService();

        if($service_model == false){
            return $this->renderJson(['status' => false]);
        }

        $internal_actions_model = $service_model->getInternalActions();

        $internal_actions_model
            ->setParams(\Yii::app()->request->getParam('params'))
            ->run(\Yii::app()->request->getParam('action_name'));

        $result = $internal_actions_model->getResult();

        return $this->renderJson($result);
    }







}
