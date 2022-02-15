<?php


class AjaxController extends Controller{




    /**
     * filter
     */
    public function filters(){
        return array(
            'checkAccess',
        );
    }



    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'nextDropdownOptionList':
            case 'startupGuideActionRun':
            case 'startup':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, \Yii::app()->request->getParam('copy_id'), Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }

        $filterChain->run();
    }




    public function actionNextDropdownOptionList(){
        $validate = new Validate();

        if(
            !array_key_exists('vars', $_POST) ||
            !array_key_exists('search', $_POST) ||
            !array_key_exists('limit', $_POST) ||
            !array_key_exists('offset', $_POST) ||
            !array_key_exists('active_group_data', $_POST)
        ){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result =  array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );

        } else{

            $params = array(
                'active_group_data' => \Yii::app()->request->getParam('active_group_data'),
                'vars' => \Yii::app()->request->getParam('vars'),
                'search' => \Yii::app()->request->getParam('search'),
                'limit' => \Yii::app()->request->getParam('limit'),
                'offset' => \Yii::app()->request->getParam('offset'),
                'filters' => \Yii::app()->request->getParam('filters'),
            );

            $result = \DropDownListOptionsModel::getInstance()
                            ->setAllParams($params)
                            ->initEntities()
                            ->setPrepareDataList(true)
                            ->prepareHtmlList()
                            ->getResult();
        }


        $this->renderJson($result);
    }







    public function actionStartupGuideActionRun(){
        $result = (new \StartupGuideModel())
                            ->runAction(
                                    \Yii::app()->request->getParam('action'),
                                    \Yii::app()->request->getParam('vars'))
                            ->getResult();

        return $this->renderJson($result);
    }




}
