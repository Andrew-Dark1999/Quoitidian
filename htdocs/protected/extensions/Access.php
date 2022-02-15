<?php
/**
* Access - Онициализация и проверка прав доступа
* 
* @author Alex R.
*/


class Access{
    
    // типы доступов: модули (MODULE), доп.доспуты (REGULATION)
    const ACCESS_TYPE_REGULATION = 1;
    const ACCESS_TYPE_MODULE     = 2;

    private static $_catch_data = array();
    
    
    // параметры для подальшего ипользования при проверке доступа в шаблонах
    private static $_access_check_params = array();
    
    
    private static $_permission_loaded = false;
    
   
    public static $user_role = array();
    public static $user_permission = array();

    
    public static function getInstance(){
        return new Access();
    }
    
    
    public function __construct(){
        self::initPermission();
    }
    
    private static function clearParams(){
        self::$_access_check_params = array();
        self::$user_role = array();
        self::$user_permission = array();
    }
    
    
    /**
     * инициализация списка доступов пользователя
     */
    private static function initPermission($user_id = null){
        if($user_id === null){
            if(!self::$_permission_loaded){
                self::clearParams();
                self::loadUserPermission();
                self::$_permission_loaded = true;
            }
        } else {
            self::clearParams();
            self::$_permission_loaded = false;
            self::loadUserPermission($user_id);    
        }
    }




    /**
     * инициализация списка доступов для роли
     */
    private static function initPermissionRoles($roles_id = null){
        self::clearParams();
        self::loadRolesPermission($roles_id);
    }







    /**
     * проверка доступа
     * @param integer $permission_name - название права доступа. Описано в константах класса PermissionModel
     * @param integer $access_id - copy_id модуля или код списка доступа из класса RegulationModel
     * @param integer $access_id_type - типы доступа: MODULE|REGULATION. Определяется константой данного класса
     * @param integer $users_id - ИД пользователя. Используется для загрузки  и определения прав доступа пользователя. Если не указан - подставляется ИД авторизированого пользователя
     */    
    public static function checkAccess($permission_name, $access_id = null, $access_id_type = null, $users_id = null, $check_authorize = true){
        // for console
        WebUser::setAppType();
        $app_type = WebUser::getAppType();

        switch($app_type){
            case WebUser::APP_CONSOLE :
                return true;
            case WebUser::APP_WEB:
                if($check_authorize) self::checkAuthorize();
                break;
        }

        self::initPermission($users_id);

        $access = false;
        if(is_array($permission_name)){
            foreach($permission_name as $code){
                $access = self::check($code, $access_id, $access_id_type);
                if($access) break;
            }
        } else {
            $access = self::check($permission_name, $access_id, $access_id_type);
        }
        return $access;
    }





    /**
     * проверка доступа роли к опеределенному модулю
     * @param integer|array $permission_name - название права доступа. Описано в константах класса PermissionModel
     * @param integer $access_id - copy_id модуля или код списка доступа из класса RegulationModel
     * @param integer $access_id_type - типы доступа: MODULE|REGULATION. Определяется константой данного класса
     * @param integer $user_id - ИД пользователя. Используется для загрузки  и определения прав доступа пользователя. Если не указан - подставляется ИД авторизированого пользователя
     */
    public static function checkAccessRole($permission_name, $access_id = null, $access_id_type = null, $roles_id = null){
        // for console
        WebUser::setAppType();
        $app_type = WebUser::getAppType();

        switch($app_type){
            case WebUser::APP_CONSOLE :
                return true;
        }

        self::initPermissionRoles($roles_id);

        $access = false;
        if(is_array($permission_name)){
            foreach($permission_name as $code){
                $access = self::check($code, $access_id, $access_id_type);
                if($access) break;
            }
        } else {
            $access = self::check($permission_name, $access_id, $access_id_type);
        }
        return $access;
    }





    /**
     * проверка доступа к записи
     * @param integer $user_id - ИД пользователя. Используется для загрузки  и определения прав доступа пользователя. Если не указан - подставляется ИД авторизированого пользователя
     * @return bool|array
     */
    public static function checkAccessDataOnParticipant($copy_id, $data_id){
        if(empty($copy_id)) return true;
        if(empty($data_id)) return true;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        if(empty($extension_copy)) return false;

        if(!$extension_copy->dataIfParticipant()){
            return true;
        }

        return \DataListModel::getInstance()
                    ->setExtensionCopy($extension_copy)
                    ->setDataId($data_id)
                    ->prepare(\DataListModel::TYPE_PARENT_LIST_VIEW, \DataListModel::METHTOD_LOAD_DATA_BOOL)
                    ->getData();
    }




    /**
    * проверка доступа. Учитывает проверку на админ. доступ 
     */
    public static function checkAdvancedAccess($permission_name, $copy_id = null, $user_id = null){
        if(self::moduleAdministrativeAccess($copy_id)){
            $access = self::checkAccess($permission_name, RegulationModel::REGULATION_SYSTEM_SETTINGS, self::ACCESS_TYPE_REGULATION, $user_id);
        } else {
            $access = self::checkAccess($permission_name, $copy_id, self::ACCESS_TYPE_MODULE, $user_id);
        }
        
        return $access;
    }
    




    /**
     * возвращает статус, если модуль требует административного доступа 
     */
    public static function moduleAdministrativeAccess($copy_id){
        return
            in_array($copy_id,
                array(
                    ExtensionCopyModel::MODULE_USERS,
                    ExtensionCopyModel::MODULE_PERMISSION,
                    ExtensionCopyModel::MODULE_ROLES,
                )
            );
    }




    /**
     * проверка авторизации пользователя
     */
    public static function checkAuthorize(){
        if(Yii::app()->user->isGuest == true){
            return Yii::app()->request->redirect(Yii::app()->createUrl('logout'));
        }
    }


    /**
     * проверка елемента права доступа
     */        
    private static function check($permission_name, $access_id = null, $access_id_type = null){
        $access = false;
        if(empty(self::$user_permission)) return $access;
        foreach(self::$user_permission as $permission){
            if(!array_key_exists($permission_name, $permission)) return $access;
            if(((integer)$permission[$permission_name] === PermissionModel::PERMISSION_ACCESS_ALLOWED)){
                if($access_id === null || ($access_id !== null && (integer)$permission['access_id'] == (integer)$access_id)){
                    if($access_id_type === null || ($access_id_type !== null && (integer)$permission['access_id_type'] == (integer)$access_id_type)){
                        $access = true;
                        break;
                    }
                }
            }            
        }
        return $access;
    }
    



    private static function setCatch($key, $value){
        self::$_catch_data[$key] = $value;
    }


    private static function getCatch($key){
        if(!empty(self::$_catch_data) && in_array($key, self::$_catch_data)){
            return self::$_catch_data[$key];
        }
        return false;
    }



    /**************************************************
    *   Загрузка прав доступа пользователя
    **************************************************/ 




    /**
    *   Возвращает данные 
    */ 
    private static function getData($extension_copy, $criteria = null){

        /*
        if($catch){
            $data = self::getCatch($extension_copy->copy_id);
            if($data !== false && !empty($data)){
                return $data;
            }
        }
        */

        $data_model = new DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();

        if(!empty($criteria)){
            foreach($criteria as $key => $value){
                switch($key){
                    case 'select' :
                        $data_model->setSelect($value);
                        break;
                    case 'condition' :
                        $data_model->setWhere($value);
                        break;
                }
            }
        }

        if(!empty($criteria) && array_key_exists('find_one', $criteria)){
            $data = $data_model->findRow();
        } else {
            $data = $data_model->findAll();
        }


        /*
        if($catch && !empty($data)){
            self::setCatch($extension_copy->copy_id, $data);
        }
        */

        return $data;
    }
   
    


    /**
    *   Возвращает данные модуля 
    */ 
    public static function getModuleData($copy_id, $criteria = null){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id)->getModule(false, true);
        if(empty($extension_copy)) return;
        
        $data = self::getData($extension_copy, $criteria);

        return $data;
    }
    
    
    /**
    *   Возвращает роли пользователя (через группы пользователей)
    */ 
    private static $_roles_set = array();
    
    private static function getUserRoles($roles_id){
        $result = array();

        if(empty($roles_id)){
            self::$_roles_set = array();
            return $result;
        }

        $roles = self::getModuleData(\ExtensionCopyModel::MODULE_ROLES, array('select' => '{{roles}}.roles_id, permission_id', 'condition' => '{{roles}}.roles_id = ' . $roles_id));

        if(!empty($roles)) {
            foreach ($roles as $r_value) {
                if(!in_array($r_value['roles_id'], self::$_roles_set))
                    self::$user_role[] = $r_value;
                self::$_roles_set[] = $r_value['roles_id'];
                $result[] = $r_value;
            }
        }

        self::$_roles_set = array();
        
        return $result;
    }
    

    /**
    *   Устанавливает права пользователя исходя из ролей пользователей
    */ 
    private static  $_permission_set = array();
    
    private static function setPermission(array $permision_list){
        if(empty($permision_list)) return;
        $permission = self::getModuleData(\ExtensionCopyModel::MODULE_PERMISSION, array('condition' => 'permission_id in (' . implode(',', $permision_list) . ')'));
        
        if(empty($permission)) return;
        foreach($permission as $p_value){
            if(!in_array($p_value['permission_id'], self::$_permission_set))
                self::$user_permission[] = $p_value;
            self::$_permission_set[] = $p_value['permission_id'];
        }

        self::$_permission_set = array();
    }

    
    
    /**
     * загрузка прав доступа пользователя: группы пользователя, роли, права 
     */
    public static function loadUserPermission($user_id = null){
        if(WebUser::getAppType() == WebUser::APP_WEB && Yii::app()->user->isGuest == true){
            return;
        }
        
        if($user_id === null) $user_id = WebUser::getUserId();

        //Roles
        $user = self::getModuleData(\ExtensionCopyModel::MODULE_USERS, array('find_one' => true, 'select' => '{{users}}.users_id, {{users_roles}}.roles_id', 'condition' => '{{users}}.users_id = ' . $user_id));;
        if(!empty($user)){
            $user_roles_data = self::getUserRoles($user['roles_id']);
        }
        
        // Permission
        if(empty($user_roles_data)) return;
        $permision_list = array();
        foreach($user_roles_data as $role){
            $permision_list[] = $role['permission_id'];
        }

        if(empty($permision_list)) return;

        self::setPermission($permision_list);
    }





    /**
     * загрузка прав доступа для определенной роли: группы пользователя, роли, права
     */
    public static function loadRolesPermission($roles_id = null){
        if(WebUser::getAppType() == WebUser::APP_WEB && Yii::app()->user->isGuest == true){
            return;
        }

        $user_roles_data = self::getUserRoles($roles_id);

        // Permission
        if(empty($user_roles_data)) return;
        $permision_list = array();
        foreach($user_roles_data as $role){
            $permision_list[] = $role['permission_id'];
        }
        self::setPermission($permision_list);
    }












    /**
     * Установка параметров для подальшего ипользования при проверке доступа в шаблонах
     * $access_id, $access_id_type
     */
    public static function setAccessCheckParams($copy_id){
        if(self::moduleAdministrativeAccess($copy_id)){
            self::$_access_check_params = array(
                'access_id' => RegulationModel::REGULATION_SYSTEM_SETTINGS,
                'access_id_type' => self::ACCESS_TYPE_REGULATION,
            );
        } else {
            self::$_access_check_params = array(
                'access_id' => $copy_id,
                'access_id_type' => self::ACCESS_TYPE_MODULE,
            );
        }
    }



    /**    
     * Возвращает параметры для ипользования при проверке доступа в шаблонах
     */
    public static function getAccessCheckParams($param_name = null){
        if($param_name === null)
            return self::$_access_check_params;
        else
            return self::$_access_check_params[$param_name];
    }




}
