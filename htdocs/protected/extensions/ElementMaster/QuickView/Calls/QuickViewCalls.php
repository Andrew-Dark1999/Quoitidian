<?php
/**
 * QuickViewCalls widget
 */

class QuickViewCalls extends CWidget{

    public $view;
    public $quick_view_model;
    public $content;


    public function init(){
        switch($this->view){
            case 'block' :
                echo $this->getBlock();
                break;
            case 'items' :
                echo $this->getItems();
                break;
        }

    }



    private function getBlock(){
        return $this->render('block', null, true);
    }





    private function getItems(){
        $html = '';
        foreach ($this->quick_view_model->getDataModelList() as $communications_model){
            $html .= $this->getItem($communications_model);
        }
        return $html;
    }



    private function getItem($calls_model){
        $calls_model = CallsModel::model()->findByPk($calls_model['calls_id']);
        $data = array(
            'calls_model' => $calls_model,
        );

        $html = $this->render('item', $data, true);

        return $html;
    }



    public function getButtonSwitch(){
        if(QuickViewModel::getInstance()->countBlockGroupName(\QuickViewBlockGroup::BLOCK1) <= 1){
            return;
        }

        $html = Yii::app()->controller->widget('ext.ElementMaster.QuickView.Elements.QuickViewElements',
            array(
                'view' => 'button_switch',
                'quick_view_model' => $this->quick_view_model,
            ),
            true);


        return $html;
    }


    public function getFooterButtons(){
        return $this->render('footer_buttons', null, true);
    }





    public function isCallsActive(){
        $source_model = (new \PluginsModel())->getSourceModelByName(PluginSources::SOURCE_PHONE);

        if($source_model == false){
            return false;
        }

        return $source_model->countServices() && $source_model->getActiveService() ? true : false;
    }



}
