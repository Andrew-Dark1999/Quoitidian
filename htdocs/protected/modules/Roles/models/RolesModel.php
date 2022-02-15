<?php

/**
 *
 * RolesModel
 *
 * @author Alex R.
 * @copyright 2017
 */
class RolesModel extends ActiveRecord
{
    public $tableName = 'roles';

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function rules(){
        return array(
            array('module_title, description', 'safe'),
        );
    }




    public function relations(){
        return array(
            'usersRoles' => array(self::HAS_MANY, 'UsersRolesModel', 'roles_id'),
            'usersModel'=>array(self::HAS_MANY,'UsersModel',array('users_id'=>'users_id'),'through'=>'usersRoles'),
        );
    }


    public function scopes(){
        return array();
    }

    /**
     * исключает ИД из запроса
     */
    public function scopeExtensionID($exception_id){
        if(empty($exception_id)) return $this;
        $criteria = new CDBCriteria();
        $criteria->addNotInCondition('roles_id', $exception_id);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }



    public function getModuleTitle(){
        return $this->module_title;
    }


    public static function getAvatarSrc(){
        return 'static/images/user_group.png';
    }


    public function getAvatar($thumb_size = 32, $attr = array('class' => 'list-view-avatar')){
        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
            array(
                'thumb_size' => $thumb_size,
                'attr' => $attr,
                'src' => self::getAvatarSrc(),
            ),
            true);
    }





    /**
     * getRolesModel
     */
    public static function getRolesModel($roles_id = null){
        return self::model()->findByPk($roles_id);
    }



    /**
     * getUsersIdListInRoles
     */
    public function getUsersIdList(){
        $users_roles = $this->usersRoles;
        if(empty($users_roles)) return;

        return array_keys(CHtml::listData($users_roles, 'users_id', ''));
    }




    /**
     * getUsersIdListInRoles
     */
    public function getUsersModelList(){
        $users_list = $this->usersModel();
        if(empty($users_list)) return;

        return $users_list;
    }



}
