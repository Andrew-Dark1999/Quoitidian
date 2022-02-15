<?php

class PermissionController extends Controller
{

    public function actionIfPermissionFromModule()
    {
        if(empty($_POST) || empty($_POST['permission_id']) || empty($_POST['copy_id']) ){
            return $this->renderJson(['status' => false]);
        }

        $permission = PermissionsModel::model()->find('permission_id=:permission_id AND access_id_type=:access_id_type AND access_id=:access_id',
            array(
                ':permission_id' => $_POST['permission_id'],
                ':access_id_type' => '2',
                ':access_id' => $_POST['copy_id'],
            ));

        if(!empty($permission))
        {
            return $this->renderJson(['status' => true]);
        }

        return $this->renderJson(['status' => false]);
    }

    public function actionCheckModulePermission()
    {
        if(empty($_POST) || empty($_POST['permission_const']) || empty($_POST['copy_id']) ){
            return $this->renderJson(['status' => false]);
        }

        $status=Access::checkAccess($_POST['permission_const'], $_POST['copy_id'], Access::ACCESS_TYPE_MODULE);
        if(!empty($_POST['check_active_module']) && \ExtensionCopyModel::model()->findByPk($_POST['copy_id'])->active == 0) {
            $status=false;
        }
        return $this->renderJson(['status' => $status]);
    }

}