<?php

/**
 * Модель для действий методов
 * Class WebhookMethodModel
 *
 * @author Aleksandr Roik
 */
class WebhookMethodModel extends ActiveRecord
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    /**
     * @var string
     */
    public $tableName = 'webhooks_method';

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
            'webhooks' => [self::HAS_MANY, 'WebhookModel', 'method'],
        ];
    }

}

