<?php
/**
 * Created by PhpStorm.
 * User: alex_r
 * Date: 26.10.2017
 * Time: 23:52
 */

namespace application\modules\test1\controllers;

use application\modules\test1\models;

class TestController extends \Controller{




    public function actionTestNs(){
        echo (new \application\modules\test1\models\TestModel())->getText();
    }

    public function actionRobert(){
        echo 'Robert';
    }



}
