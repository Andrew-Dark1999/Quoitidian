<?php

/**
 * Class LogsController
 */
class LogsController extends Controller
{

    /**
     * filter
     */
    public function filters()
    {
        return array(
            'checkAccess',
        );
    }

    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain)
    {
        switch(Yii::app()->controller->action->id){
            case 'systemError':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }
        $filterChain->run();
    }

    /**
     * Лог системных ошибок
     */
    public function actionSystemError()
    {
        $filePath = Yii::getPathOfAlias('application') . '/runtime/system-error.log';
        if(!file_exists($filePath)){
            return $this->renderTextOnly('File "system-error.log" not found');
        }

        echo nl2br(file_get_contents($filePath));
    }
}
