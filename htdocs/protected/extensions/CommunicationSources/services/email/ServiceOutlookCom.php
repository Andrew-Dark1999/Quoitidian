<?php

class ServiceOutlookCom extends EmailFactory{

//    Схема $params

    protected $_service_name = 'outlook_com';
    protected $_service_title = 'Outlook.com';

    public $_user_form_params = array(
        'service_name' => 'outlook_com',
        'service_title' => 'Outlook.com',
        'image' => '/static/images/communications/outlook.png',
        'user_params' => array(
            'login' => array(
                'name' => 'user_login',
                'title' => 'Login',
                'data_type' => 'email',
                'validate_type' => 'email',
                'element_type' => 'input',
            ),
            'password' => array(
                'name' => 'user_password',
                'title' => 'Password',
                'data_type' => 'password',
                'validate_type' => 'password',
                'element_type' => 'input',
            ),
        ),
    );



}
