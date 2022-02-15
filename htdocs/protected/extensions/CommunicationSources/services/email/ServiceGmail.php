<?php

class ServiceGmail extends EmailFactory{

    protected $_service_name = 'gmail';
    protected $_service_title = 'Gmail';

    protected $_user_form_params = array(
        'service_name' => 'gmail',
        'service_title' => 'Gmail',
        'image' => '/static/images/communications/gmail.png',
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
