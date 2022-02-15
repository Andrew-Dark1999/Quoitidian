<?php

return array(

    'params' => array(
        'global' => array(
        ),
        'db' => array(
            'set_default_timezone' => true,
            'time_zone' => '+03:00',
        ),
        'cache' => array(
            'db' => array(
                'enabled' => false,
                'duration' => 3600,
                'ar_models' => array(
                    'ExtensionCopyModel',
                    'ModuleTablesModel',
                ),
            ),
        ),
        'reports' => array(
            'logging_query' => false,
        ),
        'process' => array(
            'start_time_log_enabled' => false,
        ),
        'to_user' => array(
            'enabled' => false,
            'only_user_change' => false,
            'key' => 'zaqw1231',
        ),
        'repo' => array(
            'root_path' => '/var/www',
            'last_path' => 'current',
            'host'     => '93.175.31.35:5088/CRM-platform',
            'login'    => 'ashpumpkin',
            'password' => 'MicrosofT1',
            'branch'   => 'production',
            'domain_list' => array(),
        ),
        'console' => array(
            'back_up_db' => array(
                'path'            => '/var/backups_crm/',
                'max_files_count' => 5,
            ),
            // For host "My"
            'deploy_copy_collect_activity' => array(
                'enabled' => true,
                'copy_id' => 1013,
                'field_pk' => 'accounts_id',
                'field_name_domain' => 'accounts_domen',
                'field_name_bd' => 'accounts_begin',    //начало периода
                'field_name_lld' => 'last_login_date',  //последняя дата входа
                'condition' => 'accounts_status = 3 OR accounts_status = 8', // площадка создана
                'base_path' => '/var/www',
            ),
            // For host "My"
            'collect_activity_console_run_all' => array(
                'enabled' => true,
                'copy_id' => 1013,
                'field_pk' => 'accounts_id',
                'field_name_domain' => 'accounts_domen',
                'condition' => 'accounts_status = 3 OR accounts_status = 8', // площадка создана
                'base_path' => '/var/www',
            ),
            // For host "My"
            'deploy_copy_back_up_db' => array(
                'enabled' => true,
                'copy_id' => 1013,
                'field_pk' => 'accounts_id',
                'field_name_domain' => 'accounts_domen',
                'condition' => 'accounts_status = 3 OR accounts_status = 8', // площадка создана
                'base_path' => '/var/www',
            ),
            'regular_mail_dispatch' => array(
                'methods' => array(
                    'rmdAfterOneHour',
                    'rmdAfterOneWeek',
                ),
                'rmdAfterOneHour' => array(
                    'from' => '',
                    'from_name' => '',
                ),
                'rmdAfterOneWeek' => array(
                    'from' => '',
                    'from_name' => '',
                ),
                'rmdAfterSetStatusNedozvon' => array(
                    'from' => '',
                    'from_name' => '',
                    'deals_status' => '11',
                ),
            ),
        ),
    ),
);
