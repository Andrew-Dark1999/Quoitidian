<?php

class IdentityController extends CController
{

    public $layout = 'default';
    public $left_menu = false;


    public function filters()
    {
        return array(
            'accessControl  - Logout',
        );
    }


    public function filterAccessControl($filterChain)
    {
        if(Yii::app()->user->isGuest == false && !in_array($filterChain->action->id, ['restorePassword', 'changePassword', 'restoreFromEmail'])){
            return Yii::app()->request->redirect('/');
        }

        $filterChain->run();
    }


    public function actionLogin()
    {
        $user_model = new UsersModel();
        $data = array(
            'user_model' => $user_model,
        );

        if(!isset($_POST['UsersModel'])) return $this->render('login', $data);

        $user_model->scenario = 'login';
        $user_model->attributes = $_POST['UsersModel'];

        if($user_model->validate()){
            UsersModel::getUserModel()->setLogout(false);

            $url = History::getUserStorageBackUrl();
            return Yii::app()->request->redirect($url);
        }
        $data['user_model'] = $user_model;
        return $this->render('login', $data);
    }


    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect('login');
    }


    public function actionRegistration()
    {
        $user_model = new UsersModel('registration');

        if(isset($_POST['UsersModel'])){
            $user_model->attributes = $_POST['UsersModel'];
            $user_model->change_password = true;
            if($user_model->save()){

                $params_model = ParamsModel::model()->findAll();

                $mailer = new Mailer();
                $mailer
                    ->setLetter(
                        ParamsModel::getValueFromModel('sending_out', $params_model),
                        ParamsModel::getValueFromModel('sending_out_name', $params_model),
                        $user_model->email,
                        $user_model->getFullName(),
                        Mailer::LETTER_USER_REGISTRATION,
                        array(
                            '{site_url}' => ParamsModel::getValueFromModel('site_url', $params_model),
                            '{site_title}' => preg_replace('~(http://|https://)~', '', ParamsModel::getValueFromModel('site_url', $params_model)),
                            '{company_name}' => ParamsModel::getValueFromModel('crm_name', $params_model),
                            '{service_email}' => ParamsModel::getValueFromModel('service_email', $params_model),
                            '{sales_email}' => ParamsModel::getValueFromModel('sales_email', $params_model),
                            '{support_email}' => ParamsModel::getValueFromModel('support_email', $params_model),
                            '{presentation_link}' => ParamsModel::getValueFromModel('presentation_link', $params_model),
                            '{login}' => $user_model->email,
                            '{password}' => $user_model->password_real,
                            '{user_name}' => $user_model->first_name),
                        MailerLettersOutboxModel::STATUS_IS_SENT
                    );
                $mailer
                    ->prepareLettesFromIdArray()
                    ->send()
                    ->setMarkSended()
                    ->setMarkSend();

                $user_model->password = $user_model->password_real;
                if($user_model->authenticate()){
                    $this->redirect('/');
                } else{
                    $this->redirect('/login');
                }
            }
        }
        $data = array(
            'user_model' => $user_model,
        );
        return $this->render('registration', $data);
    }

    /**
     * Restore password form
     */
    public function actionRestorePassword()
    {
        $token = Yii::app()->request->getQuery('token');
        $email = Yii::app()->request->getQuery('email');
        $restorePassword = UsersRestoreModel::model()
            ->findByAttributes(
                array('token' => $token),
                array(
                    'condition' => 'created_at>=:time',
                    'params' => array(':time' => date('Y-m-d H:i:s', strtotime('-1 hour')))
                )
            );
        $data = array(
            'token' => $token,
            'email' => $email,
        );
        if(!$restorePassword){
            Yii::app()->user->setFlash('error', Yii::t('UsersModule.base', 'Error token'));
        }
        return $this->render('restore-password', $data);
    }

    /**
     * Change password
     */
    public function actionChangePassword()
    {
        $data = array(
            'token' => Yii::app()->request->getPost('token'),
            'email' => Yii::app()->request->getPost('email'),
            'password' => Yii::app()->request->getPost('password'),
            'confirmPassword' => Yii::app()->request->getPost('confirm_password'),
        );
        $restore = UsersRestoreModel::model()->getRestoreUser($data);
        if($restore){
            if($data['password'] === $data['confirmPassword']){
                $restore->user->setScenario('update_password');
                $restore->user->password = $data['password'];
                if($restore->user->setChangePassword(true)->save()){
                    $restore->delete();
                    Yii::app()->user->setFlash('success', Yii::t('UsersModule.base', 'Your password recovery successes'));
                    $this->redirect('/login');
                } else {
                    if($restore->user && $restore->user->hasErrors()){
                        Yii::app()->user->setFlash('error', Yii::t('UsersModule.base', $restore->user->getError('password')));
                    }
                }
            } else{
                Yii::app()->user->setFlash('error', Yii::t('UsersModule.base', 'Error confirm password'));
            }
        } else{
            Yii::app()->user->setFlash('error', Yii::t('UsersModule.base', 'Error token'));
        }
        return $this->redirect('restore-password?token=' . $data['token'] . '&email=' . $data['email']);
    }

    /**
     * Restore password
     *
     * @return array|mixed|string|string[]|void|null
     */
    public function actionRestoreFromEmail()
    {
        $user_model = new UsersModel('restore');

        if(isset($_POST['UsersModel'])){
            $user_model->attributes = $_POST['UsersModel'];
            if($user_model->validate()){
                $user_model = UsersModel::model()->find('email = "' . $user_model->email . '"');
                if(!empty($user_model)){
                    // check if disabled
                    if($user_model->active !== '1'){
                        Yii::app()->user->setFlash('success', Yii::t('UsersModule.messages', 'Sorry, your account is locked') . '.</br>' . Yii::t('UsersModule.messages', 'To unlock, you need to contact the system administrator'));
                        $this->redirect('/restore');
                    }
                    $token = md5(mt_rand(1, 90000) . md5(date('Y-m-d H:i:s')));

                    UsersRestoreModel::model()
                        ->deleteAllByAttributes(
                            array(
                                'users_id' => $user_model->users_id
                            )
                        );
                    $userRestoreModel = new UsersRestoreModel();
                    $userRestoreModel->users_id = $user_model->users_id;
                    $userRestoreModel->token = $token;
                    $userRestoreModel->created_at = date('Y-m-d H:i:s');
                    $userRestoreModel->save();

                    $params_model = ParamsModel::model()->findAll();

                    $mailer = new Mailer();
                    $mailer
                        ->setLetter(
                            ParamsModel::getValueFromModel('sending_out', $params_model),
                            ParamsModel::getValueFromModel('sending_out_name', $params_model),
                            $user_model->email,
                            $user_model->getFullName(),
                            Mailer::LETTER_USER_RESTORE_PASSWORD_TOKEN,
                            array(
                                '{site_url}' => ParamsModel::getValueFromModel('site_url', $params_model) . '/restore-password?token=' . $token . '&email=' . $user_model->email,
                                '{company_name}' => ParamsModel::getValueFromModel('crm_name', $params_model),
                                '{sales_email}' => ParamsModel::getValueFromModel('sales_email', $params_model),
                                '{support_email}' => ParamsModel::getValueFromModel('support_email', $params_model),
                                '{user_name}' => $user_model->first_name),
                            MailerLettersOutboxModel::STATUS_IS_SENT
                        );
                    $mailer
                        ->prepareLettesFromIdArray();
//                    $mailer
//                        ->prepareLettesFromIdArray()
//                        ->send()
//                        ->setMarkSended()
//                        ->setMarkSend();


                    if(Yii::app()->request->isAjaxRequest){
                        echo json_encode(array(
                            'status' => true,
                            'html' => Yii::t('UsersModule.messages', 'Email with instructions for recovery password sent to your Email'),
                        ));
                        return;
                    } else{
                        Yii::app()->user->setFlash('success', Yii::t('UsersModule.messages', 'Email with instructions for recovery password sent to your Email'));
                        $this->redirect('/login');
                    }
                }
            }
        }
        if(Yii::app()->request->isAjaxRequest){
            echo json_encode(array(
                'status' => false,
            ));
            return;
        }
        $data = array(
            'user_model' => $user_model,
        );
        return $this->render('restore', $data);
    }


    public function actionLocked()
    {
        return $this->render('locked');
    }


    public function actionLockedTechnicalWorks()
    {
        return $this->render('locked-technical-works');
    }


    /**
     * getRegBackgroundUrl - возвращает url фонового изображения
     */
    public function getRegBackgroundUrl()
    {
        $defaultUrl = '/static/images/wizz/fullpagebg.jpg';

        if($reg_background = ParamsModel::getValueFromModel('reg_background')){
            $uploadModel = UploadsModel::model()->findByPk($reg_background);

            return $uploadModel ? $uploadModel->getFileUrl() : $defaultUrl;
        }

        return $defaultUrl;
    }


    public function getPackage($key = null)
    {
        $path_to_file = './../package.json';
        $string = file_get_contents($path_to_file);
        $json_arr = json_decode($string, true);

        if($key && array_key_exists($key, $json_arr)){
            return $json_arr[$key];

        }
        return $json_arr;
    }


}
