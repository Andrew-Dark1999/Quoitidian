<?php

class Vars
{
    private $vars;

    private static $instance;

    /**
     * @param null $vars
     */
    public static function getInstance($vars = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($vars);
        }

        return self::$instance;
    }

    /**
     * Vars constructor.
     *
     * @param null $vars
     */
    private function __construct($vars = null)
    {
        $this->setVars($vars, true);
    }

    /**
     * @return void
     */
    public function setVars($vars = null, $getGlobal = false)
    {
        if ($vars === null && $getGlobal) {
            $vars = Yii::app()->request->getPost('vars');
        }

        if ($vars) {
            // предположительно, что данные в виде строки это json-массив
            if (is_string($vars)) {
                $vars = json_decode($vars, true);
            }
            $this->vars = $vars;
        }
    }

    /**
     * @return mixed
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param $name
     */
    public function getVar($name)
    {
        $vars = $this->getVars();

        if (!$vars) {
            return;
        }

        return array_key_exists($name, $vars) ? $vars[$name] : null;
    }

}
