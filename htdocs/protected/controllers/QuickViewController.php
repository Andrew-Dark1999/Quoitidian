<?php

class QuickViewController extends Controller {




    public function actionGetBlocks(){
        $block_model_list = QuickViewModel::getInstance()
            ->prepareDataItemsModelList()
            ->getBlockModelListJs();

        return $this->renderJson([
            'status' => ($block_model_list ? true : false),
            'list' => $block_model_list,
        ]);
    }





    public function actionGetItems(){
        $block_name = \Yii::app()->request->getParam('block_name');

        $quick_model = QuickViewModel::getInstance();
        $block_model = $quick_model->getBlockModelByName($block_name);

        if($block_model == false){
            return $this->renderJson([
                'status' => false,
            ]);
        }

        $items_model = $block_model->getItemsModel();
        $items_model
            ->setLimit(\Yii::app()->request->getParam('limit'))
            ->setOffset(\Yii::app()->request->getParam('offset'))
            ->prepareDataModelList();

        $html_result = (new QuickViewBuilder())
                            ->setQuickViewItemsModel($items_model)
                            ->prepare(QuickViewBuilder::VIEW_ITEMS)
                            ->getResult();

        $result = [
            'status' => true,
            'html_result' => $html_result,
            'there_is_data' => $items_model->getThereIsData(),
        ];

        return $this->renderJson($result);
    }






}
