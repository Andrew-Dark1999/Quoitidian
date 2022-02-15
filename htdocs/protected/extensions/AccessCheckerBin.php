<?php

/**
 * Сравние и проверка значений с пормощью биранарного выражения
 * Class AccessCheckBin
 *
 * @author Aleksandr Roik
 */
class AccessCheckerBin
{
    /**
     * Значение, созданое бинарным выражением, с которым будет проводится проверка
     *
     * @var string|integer
     */
    private $withConsts;

    /**
     * AccessCheckBin constructor.
     *
     * @param $withConsts - Список констант, с которым будет проводится проверка
     * @param null $withClassName - Название класса, где находятся константы
     */
    public function __construct($withConsts)
    {
        $this->setWithConsts($withConsts);
    }

    /**
     * Установка списка констант, с которым будет проводится проверка
     *
     * @param string|integer $withConsts
     * @return AccessCheckerBin
     */
    private function setWithConsts($withConsts)
    {
        if (is_string($withConsts)) {
            $withConsts = eval('return  ' . $withConsts . ';' );
        }

        $this->withConsts = $withConsts;
    }

    /**
     * Сама проверка
     *
     * @param string|integer $whatConst - значение, что будут проверятся по бинарному алгоритму
     * @return bool
     */
    public function check($whatConst)
    {
        return ($whatConst) & ($this->withConsts);
    }
}
