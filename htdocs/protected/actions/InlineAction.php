<?php

/**
 * Класс разширяет стандартные возможности после выполнения действия контроллера.
 * В частности добавлено свойство response, в котором будет содержаться результат выпосления
 * действия. Из метода действия результат надо возвратить через return
 * Class InlineAction
 */
class InlineAction extends CInlineAction
{
    /**
     * Результат выполнения метода action
     *
     * @var Response
     */
    protected $response;

    /**
     * Экземпляр контроллера
     *
     * @var CController
     */
    protected $controller;

    /**
     * Установка Response
     *
     * @param $actionResponse
     */
    protected function setResponse($responseData)
    {
        $response = $this->getController()->getResponse();
        $this->response = new $response($responseData);
    }

    /**
     * Возвращает Response выполнения действия
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Runs the action.
     */
    public function run()
    {
        $method = 'action' . $this->getId();
        $this->setResponse($this->getController()->$method());
    }

    /**
     * Executes a method of an object with the supplied named parameters.
     * This method is internally used.
     *
     * @param mixed $object the object whose method is to be executed
     * @param ReflectionMethod $method the method reflection
     * @param array $params the named parameters
     * @return boolean whether the named parameters are valid
     * @since 1.1.7
     */
    protected function runWithParamsInternal($object, $method, $params)
    {
        $ps = [];
        foreach ($method->getParameters() as $i => $param) {
            $name = $param->getName();
            if (isset($params[$name])) {
                if ($param->isArray()) {
                    $ps[] = is_array($params[$name]) ? $params[$name] : [$params[$name]];
                } elseif (!is_array($params[$name])) {
                    $ps[] = $params[$name];
                } else {
                    return false;
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $ps[] = $param->getDefaultValue();
            } else {
                return false;
            }
        }
        try {
            $this->setResponse($method->invokeArgs($object, $ps));
        } catch (\Error $e) {
            $this->toLog($e);
            $this->setResponse($e);
        } catch (\Exception $e) {
            $this->toLog($e);
            $this->setResponse($e);
        }

        return true;
    }

    /**
     * Runs the action with the supplied request parameters.
     *
     * @param array $params
     * @return bool
     * @throws ReflectionException
     */
    public function runWithParams($params)
    {
        $methodName = 'action' . $this->getId();
        $controller = $this->getController();
        $method = new ReflectionMethod($controller, $methodName);
        if ($method->getNumberOfParameters() > 0) {
            return $this->runWithParamsInternal($controller, $method, $params);
        }

        try {
            $this->setResponse($controller->$methodName());
        } catch (\Error $e) {
            $this->toLog($e);
            $this->setResponse($e);
        } catch (\Exception $e) {
            $this->toLog($e);
            $this->setResponse($e);
        }

        return true;
    }

    /**
     * @param Throwable $e
     */
    protected function toLog(Throwable $e)
    {
        Yii::log($e->getCode() . ' ' . $e->getFile() . '[' . $e->getLine() . ']', 'error', 'system-error');
        Yii::log($e->getMessage(), 'error', 'system-error');
    }
}
