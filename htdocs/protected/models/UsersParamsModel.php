<?php
/**
 * UsersParamsModel
 *
 * @author Alex R.
 */

class UsersParamsModel extends ActiveRecord
{

    public $tableName = 'users_params';

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    protected function afterSave()
    {
        parent::afterSave();

        if($this->background){
            $uploadModel = UploadsModel::model()->findByPk($this->background);
            if($uploadModel){
                $uploadModel->status = 'asserted';
            }
        }
    }

    public function scopeActiveUser()
    {
        $this->scopeUsersId(WebUser::getUserId());

        return $this;
    }

    public function scopeUsersId($users_id)
    {
        $this->getDbCriteria()->mergeWith([
            'condition' => 'users_id=:users_id',
            'params'    => [':users_id' => $users_id],
        ]);

        return $this;
    }

    /**
     * getRegBackgroundUrl - возвращает url фонового изображения
     */
    public function getBackgroundUrl()
    {
        if (!$this->background) {
            return;
        }

        $uploadModel = UploadsModel::model()->findByPk($this->background);

        return $uploadModel ? $uploadModel->getFileUrl() : null;
    }

    /**
     * @param null $background
     * @return mixed|null|void
     */
    public function getBackgroundFileTitle($background = null)
    {
        if($background === null){
            $background = $this->background;
        }

        if (!$background) {
            return;
        }

        $uploadModel = UploadsModel::model()->findByPk($background);

        return $uploadModel ? $uploadModel->file_title : null;
    }

}


