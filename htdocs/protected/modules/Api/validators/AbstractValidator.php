<?php

/**
 * Class AbstractValidator
 *
 * @author Aleksandr Roik
 */
abstract class AbstractValidator extends Validate
{
    /**
     * @var AbstractAction
     */
    protected $action;

    /**
     * @var array
     */
    protected $request;

    /**
     * ApiRun constructor.
     *
     * @param $request
     */
    public function __construct($action, $request)
    {
        $this->action = $action;
        $this->request = $request;
    }

    /**
     * Сама проверка
     */
    abstract public function validate();


    /**
     * @param $type_message
     * @param $message
     * @return $this
     */
    public function addValidateGeneral($type_message, $message)
    {
        $this->validate_result['general'][] = [
            'type'    => $this->type_messages[$type_message],
            'message' => $message,
        ];

        $attr_name = $this->type_messages[$type_message] . '_count';
        $this->$attr_name++;

        return $this;
    }

    /**
     * @param $type_message
     * @param $field_name
     * @param $message
     * @return $this
     */
    public function addValidateModule($type_message, $field_name, $message)
    {
        $this->validate_result['module'][$field_name][] = [
            'type'    => $this->type_messages[$type_message],
            'message' => $message,
        ];

        $attr_name = $this->type_messages[$type_message] . '_count';
        $this->$attr_name++;

        return $this;
    }


    /**
     * Возвращает наличие модуля в системе
     *
     * @param $copy_id
     * @return bool
     */
    public function isSetCopyId($copy_id, $check_access = true, $permission_name = null)
    {
        if (!$copy_id) {
            return false;
        }

        $result = (boolean)\DataModel::getInstance()->setFrom('{{extension_copy}}')->setWhere('copy_id=:copy_id', [':copy_id' => $copy_id])->findCount();

        if (!$result) {
            return false;
        }

        if ($check_access) {
            return Access::checkAccess($permission_name, $copy_id, Access::ACCESS_TYPE_MODULE);
        }

        return $result;
    }
}
