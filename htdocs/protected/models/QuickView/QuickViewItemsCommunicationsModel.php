<?php


class QuickViewItemsCommunicationsModel extends QuickViewItemsModel {


    public function prepareDataModelList(){
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_COMMUNICATIONS);

        $this->_data_model_list = (new CommunicationsModel())->getData(
                                            $extension_copy,
                                            false,
                                            null,
                                            null,
                                            false,
                                            false,
                                            $this->getLimit(),
                                            $this->getOffset(),
                                            true);
        $this->prepareThereIsData();

        return $this;
    }


}
