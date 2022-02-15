<?php

/**
 * Модель для вебхуков
 *
 * Class WebhookModel
 * @author Aleksandr Roik
 */
class WebhookModel extends ActiveRecord
{
    /**
     * @var string
     */
    public $tableName = 'webhooks';

    /**
     * @param string $className
     * @return $this|mixed|static
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('module_title, url', 'length', 'max' => 255),
            array('method, copy_id, action', 'length', 'max' => 15),
            array('webhook_id, date_create, date_edit, user_create, user_edit, import_status, this_template', 'safe'),
        );
    }

    /**
     * @return array
     */
    public function relations()
    {
        return array(
            'extensionCopy' => array(self::BELONGS_TO, 'ExtensionCopyModel', 'copy_id'),
            'action' => array(self::BELONGS_TO, 'WebhookActionModel', 'action'),
            'method' => array(self::BELONGS_TO, 'WebhookMethodModel', 'method'),
        );
    }
}

