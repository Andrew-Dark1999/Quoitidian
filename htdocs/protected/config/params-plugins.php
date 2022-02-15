<?php

return array(
    'params' => array(
        'plugins' => array(
            //sources
            'sources' => array(
                'phone' => array(
                    'class' => 'PluginSourcePhone',
                    'enable' => true,
                    'services' => array(
                        'mango_office',
                    ),
                ),
                'email' => array(
                    'class' => 'PluginSourceEmail',
                    'enable' => true,
                    'services' => array(
                        'system',
                        'unisender',
                    ),
                ),
                'sms' => array(
                    'class' => 'PluginSourceSms',
                    'enable' => true,
                    'services' => array(
                        'unisender',
                    ),
                ),
            ),

            //services - general params
            'services' => array(
                'mango_office' => array(
                    'class' => 'MangoOffice',
                    'enable' => true,
                    'api_url' => 'https://app.mango-office.ru/vpbx/',
                    'params' => array(
                        'domain' => null,
                        'port' => 5060,
                        'api_url' => null,
                        'api_key' => null,
                        'api_salt' => null,
                    ),
                ),
                'unisender' => array(
                    'class' => 'Unisender',
                    'enable' => true,
                    'api_url' => 'https://api.unisender.com/{lang}/api/',
                    'params' => array(
                        'api_key' => null,
                    ),
                ),
            ),
        ),
    )
);
