<?php

return array(
    "rules" => array(

        'module/<controller:\w+>/<action:\w+>/<copy_id:\w+>/' => '/module/module',
        'moduleOverName/<module_name:\w+>/<controller:\w+>/<action:\w+>/<extension_copy:w*>' => '/module/moduleOverName',

        'roles' => '/module/module/controller/listView/action/show/copy_id/4',
        'roles/<pdi:\d+>' => '/module/module/controller/listView/action/showPermission/copy_id/3',
        'users' => '/module/module/controller/listView/action/show/copy_id/1',
        'webhook' => '/module/module/controller/listView/action/show/copy_id/15',

        'participant' => '/module/module/controller/listView/action/show/copy_id/6',
        'participantusers' => '/module/module/controller/listView/action/addParticipantUsers/copy_id/6',

        'profile' => '/module/moduleOverName/module_name/Users/controller/profile/action/profile',
        'profile-overview' => '/module/moduleOverName/module_name/Users/controller/profile/action/profile',
        'profile-notification-settings' => '/module/moduleOverName/module_name/Users/controller/profile/action/profile',
        'profile/activity' => '/module/moduleOverName/module_name/Users/controller/profile/action/activity',
        'profile/profileSave' => '/module/moduleOverName/module_name/Users/controller/profile/action/profileSave',
        'profile/profileHtmlRefresh' => '/module/moduleOverName/module_name/Users/controller/profile/action/profileHtmlRefresh',
        'profile/personalContactSave' => '/module/moduleOverName/module_name/Users/controller/profile/action/personalContactSave',
        'profile/apiKeyRegenerate' => '/module/moduleOverName/module_name/Users/controller/profile/action/apiKeyRegenerate',
        'profile/apiRegenerateToken' => '/module/moduleOverName/module_name/Users/controller/profile/action/apiRegenerateToken',

        'login' => '/module/moduleOverName/module_name/Users/controller/identity/action/login',
        'logout' => '/module/moduleOverName/module_name/Users/controller/identity/action/logout',
        //'registration' => '/module/moduleOverName/module_name/Users/controller/identity/action/registration',
        'restore' => '/module/moduleOverName/module_name/Users/controller/identity/action/restoreFromEmail',
        'locked' => '/module/moduleOverName/module_name/Users/controller/identity/action/locked',
        'locked-technical-works' => '/module/moduleOverName/module_name/Users/controller/identity/action/lockedTechnicalWorks',
        'restore-password' => '/module/moduleOverName/module_name/Users/controller/identity/action/restorePassword',
        'restore-password-change' => '/module/moduleOverName/module_name/Users/controller/identity/action/changePassword',

        'parameters' => 'site/parameters',
        'plugins' => 'site/pluginsShow',
        'plugins-change' => 'site/pluginsChange',
        'plugins-save' => 'site/pluginsSave',
        'plugins-cancel' => 'site/pluginsCancel',
        'mailing_services' => 'site/mailingServices',
        'mailing_services_refresh' => 'site/mailingServicesRefresh',

        'api/<action:\w+>' => 'api/api/<action>',
        'sip/external-event-handler/*' => 'sip/externalEventHandler',

        'file' => 'file/fileLoad',

        '<controller:\w+>/<id:\d+>' => '<controller>/index',
        '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',

        'gii' => 'gii',
        'gii/<controller:\w+>' => 'gii/<controller>',
        'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
    ),
);
