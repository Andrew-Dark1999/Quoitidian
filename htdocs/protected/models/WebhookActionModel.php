<?php

/**
 * Модель для действий вебхуков
 * Class WebhookActionModel
 *
 * @author Aleksandr Roik
 */
class WebhookActionModel extends ActiveRecord
{

    /**
     * Действия
     */
    const ACTION_MODULE_CREATED_ENTITY = 'module_created_entity'; // Создание сущности модуля
    const ACTION_MODULE_CHANGED_ENTITY = 'module_changed_entity'; // Изменение сущности модуля

    /**
     * @var string
     */
    public $tableName = 'webhooks_action';

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
        return [];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'webhooks' => [self::HAS_MANY, 'WebhookModel', 'action'],
        ];
    }

}

