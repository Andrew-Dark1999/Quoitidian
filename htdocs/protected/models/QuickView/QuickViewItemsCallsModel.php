<?php


class QuickViewItemsCallsModel extends QuickViewItemsModel {


    public function prepareDataModelList(){
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_CALLS);

        $this->_data_model_list = (new CallsModel())->getData(
                                        $extension_copy,
                                        false,
                                        null,
                                        null,
                                        false,
                                        false,
                                        $this->getLimit(),
                                        $this->getOffset());

        $this->prepareThereIsData();

        return $this;
    }


}
