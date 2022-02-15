<?php

class ServiceMailRu extends EmailFactory{

//    Схема $params
//    'email' => 'email_address@gmail.com'
//    'password' => 'yours_password'

    protected $_service_name = 'mail_ru';
    protected $_service_title = 'Mail.ru';

    public $_user_form_params = array(
        'service_name' => 'mail_ru',
        'service_title' => 'Mail.ru',
        'image' => '/static/images/communications/mail-ru.png',
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
