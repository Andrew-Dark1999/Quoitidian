<?php

/**
 * ProfileModel
 * @author Alex R.
 */
 
class ProfileModel {
    
    
    
    
    
    
    /**
     *  возвращает блок-файл изображения аватара для редактирования в профиле пользователя  
     */
    public static function getFileBlockAvatar($users_id = null, $thumb_size = 140, $read_only = false){
        if(!$users_id) $users_id = Yii::app()->user->id;
        $user_model = UsersModel::model()->findByPk($users_id);

        if(!empty($user_model->ehc_image1)){
            $upload_model = UploadsModel::model()->setRelateKey($user_model->ehc_image1)->find();
        }
        else $upload_model = null;
        
        $params = array(
                    'view' => 'element_contact',
                    'thumb_size' => $thumb_size,
                    'schema' => null,
                    'upload_model' => $upload_model,
                    'extension_copy' => null,
                    'extension_data' => $user_model,
                    'remove_element_name' => 'profile',
                    'upload_element_name' => 'profile',
                   );
 
        if($read_only) $params['buttons'] = array();
        
        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
                   $params,
                   true);
    }
    

    
    
    
    /**
     * возвращает сведенную информаціио о пользователе
     */
    public static function getUserInfo($users_id = null){
        if($users_id === null) $users_id = Yii::app()->user->id;
        $user_model = UsersModel::model()->findByPk($users_id);

        $info = array(
            'user_model' => $user_model,
            'user_description' => self::getUserDescription($users_id),
        );        

        return $info;
    }



    
    

    /**
     * загрузка прав доступа пользователя: группы пользователя, роли, права 
     */
    private static function getUserDescription($users_id){
        $user_roles = array();
        $users = Access::getModuleData(\ExtensionCopyModel::MODULE_USERS);
        if(!empty($users)){
            foreach($users as $u_value){
                if((integer)$u_value['users_id'] != (integer)$users_id) continue;
                $user_roles = self::getUserRoles($u_value['roles_id']);
                break;
            }
        }
        
        if(!empty($user_roles)){
            return $user_roles[0]['description'];
        }
    }            
    
    
    /**
    *   Возвращает роли пользователя (через группы пользователей)
    */ 
    private static function getUserRoles($roles_id){
        $result = array();
        $roles = Access::getModuleData(\ExtensionCopyModel::MODULE_ROLES);
        if(!empty($roles)) {
            foreach ($roles as $r_value) {
                if ($roles_id == $r_value['roles_id']){
                    $result[] = $r_value;
                }
            }
        }
        return $result;
    }
        


   


    public static function saveProfile($attibutes){
        $result = array('status' => false);

        switch($attibutes['action']){
            case 'personal_information' :
                $result = self::savePersonalInformation($attibutes['data']);
                break;
            case 'restore_password' :
                $result = self::savePersonalInformation($attibutes['data']);
                break;
            case 'notification_settings' :
                $result = self::saveNotificationSettings($attibutes['data']);
                break;
            case 'api' :
                $result = self::saveApi($attibutes['data']);
                break;
        }

        return $result;
    }



    private static function savePersonalInformation($attibutes){
        $status = true;
        $html = '';

        $users_id = WebUser::getUserId();
        $model = new ProfilePersonalInformationModel();
        $model->setAttributes($attibutes);

        if($model->validate()){
            $model->save($users_id);
        } else {
            $status = false;
            $data = array(
                'user_info' => ProfileModel::getUserInfo($users_id),
                'personal_info_model' => $model,
                'personal_info_data' => $model->getAttributes()
            );
            $html = Yii::app()->controller->renderPartial('profile-personal-information', $data, true);
        }

        Yii::app()->user->setFlash('tab_active', 'personal_information');

        return array(
            'status' => $status,
            'html' => $html
        );
    }

    private static function saveNotificationSettings($attibutes){
        $status = true;
        $html = '';

        $model = ProfileNotificationSettingModel::getModel();
        $model
            ->setMyAttributes($attibutes)
            ->prepareData('set');

        if($model->validate()){
            $model->save();
        } else {
            $status = false;
            $data = array('notification_setting_model' => ProfileNotificationSettingModel::getModel()->prepareData());
            $html = Yii::app()->controller->renderPartial('profile-notification-settings', $data, true);
        }

        Yii::app()->user->setFlash('tab_active', 'notification_settings');

        return array(
            'status' => $status,
            'html' => $html
        );
    }

    private static function saveApi($attibutes){
        $result = array(
            'status' => true,
            'html' => ''
        );

        $user_model = UsersModel::model()->findByPk(WebUser::getUserId());
        if(!$user_model){
            return $result;
        }

        $user_model->setScenario('api');
        $user_model->setMyAttributes($attibutes);

        if(!$user_model->save()){
            $data = array(
                'user_info' => ProfileModel::getUserInfo(WebUser::getUserId()),
            );
            $result['status'] = false;
            $result['html'] = Yii::app()->controller->renderPartial('profile-api', $data, true);
        }

        Yii::app()->user->setFlash('tab_active', 'api');

        return $result;

    }


    public static function refreshProfile($attibutes){
        $result = array('status' => false);

        switch($attibutes['action']){
            case 'notification_settings' :
                $result = self::refreshNotificationSettings($attibutes['data']);
                break;
        }

        return $result;
    }





    private static function refreshNotificationSettings($attibutes){
        $model = new ProfileNotificationSettingModel();

        $model
            ->setMyAttributes($attibutes)
            ->prepareData('set');

        $data = array('notification_setting_model' => $model->prepareData('get'));
        $html = Yii::app()->controller->renderPartial('profile-notification-settings', $data, true);

        return array(
            'status' => true,
            'html' => $html,
        );

    }


}



