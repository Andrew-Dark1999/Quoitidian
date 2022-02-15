<?php

class ServiceIMAP extends EmailFactory{

    protected $_service_name = 'imap';
    protected $_service_title = 'IMAP';

    public $_user_form_params = array(
        'service_name' => 'imap',
        'service_title' => 'IMAP',
        'image' => '/static/images/communications/iMap.png',
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
            'imap_server_host' => array(
                'name' => 'imap_server_host',
                'title' => 'Host IMAP',
                'data_type' => 'text',
                'validate_type' => 'text',
                'element_type' => 'input',
            ),
            'imap_server_connect_secure' => array(
                'name' => 'imap_server_connect_secure',
                'title' => 'Protocol SSL or TLS for IMAP',
                'data_type' => 'text',
                'validate_type' => 'ssl',
                'element_type' => 'select',
                'list' => [
                    'ssl' => 'SSL',
                    'tls' => 'TLS',
                ]
            ),
            'imap_server_port' => array(
                'name' => 'imap_server_port',
                'title' => 'Port IMAP',
                'data_type' => 'number',
                'validate_type' => 'imap_port',
                'element_type' => 'input',
            ),
            'smtp_server_host' => array(
                'name' => 'smtp_server_host',
                'title' => 'Host SMTP',
                'data_type' => 'text',
                'validate_type' => 'text',
                'element_type' => 'input',
            ),
            'smtp_server_connect_secure' => array(
                'name' => 'smtp_server_connect_secure',
                'title' => 'Protocol SSL or TLS for SMTP',
                'data_type' => 'text',
                'validate_type' => 'ssl',
                'element_type' => 'select',
                'list' => [
                    'ssl' => 'SSL',
                    'tls' => 'TLS',
                ]
            ),
            'smtp_server_port' => array(
                'name' => 'smtp_server_port',
                'title' => 'Port SMTP',
                'data_type' => 'number',
                'validate_type' => 'smtp_port',
                'element_type' => 'input',
            ),
        ),
    );







    /**
     * actionCheckServiceParams - проверка параметров подключения к сервису
     */
    protected function actionCheckServiceParams(){
        error_reporting(0);

        $this->mergeParams($this->_action_vars['params']);

        $this->imapOpen();

        if($this->hasImapStream() == false){
            $this
                ->clearMessages()
                ->addMessageErrorBefore('<br>')
                ->addMessageErrorBefore('Can\'t connecting with service "{service}"', array('{service}' => $this->_service_title))
                ->addImapMessagesErrors()
                ->imapClose();

            error_reporting(E_ALL);
        }

        $this->ImapClose();

        error_reporting(E_ALL);

        return $this;
    }






}
