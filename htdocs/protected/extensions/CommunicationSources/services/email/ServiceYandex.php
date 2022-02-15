<?php

class ServiceYandex extends EmailFactory{

//    Схема $params
//    'email' => 'email_address@gmail.com'
//    'password' => 'yours_password'

    protected $_service_name = 'yandex';
    protected $_service_title = 'Yandex';

    public $_user_form_params = array(
        'service_name' => 'yandex',
        'service_title' => 'Yandex',
        'image' => '/static/images/communications/yandex.png',
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
