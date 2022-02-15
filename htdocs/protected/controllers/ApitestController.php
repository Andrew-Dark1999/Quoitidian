<?php

class ApitestController extends Controller

{

    private $apiKey = '5afea425be23f24def205988f59c2e02';

    private $userEmail = '313prodev@gmail.com';

    public function filters()
    {
        return array();
    }

    public function actionList()
    {
        try {

            if ($this->apiKey && $this->userEmail) {
                $criteria = new CDbCriteria();
                $criteria->addCondition('language_id = :language_id');
                $criteria->params[':language_id'] = 3;
                $models = LanguageModel::model()->findAllByAttributes(['language_id' => '1']);
                //var_dump(CJSON::encode($models));
                echo $json = CJSON::encode($models, JSON_PRETTY_PRINT);

            } else {
                throw new Exception("Plz check apiKey in dashboard ");
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }




// Uncomment the following methods and override them if needed
    /*
    public function filters()
    {
        // return the filter configuration for this controller, e.g.:
        return array(
            'inlineFilterName',
            array(
                'class'=>'path.to.FilterClass',
                'propertyName'=>'propertyValue',
            ),
        );
    }

    public function actions()
    {
        // return external action classes, e.g.:
        return array(
            'action1'=>'path.to.ActionClass',
            'action2'=>array(
                'class'=>'path.to.AnotherActionClass',
                'propertyName'=>'propertyValue',
            ),
        );
    }
    */
}