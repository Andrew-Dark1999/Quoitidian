<?php
/**
* InlineEdit widget  
* @author Alex R.
* @version 1.0
*/

namespace Tasks\extensions\ElementMaster\InLineEdit\Elements\InLineEdit;

use \Tasks\models\DataListModel;

class InLineEdit extends \InLineEdit{


    private $_no_data = true;

    public function init(){
        $this->view = 'ext.ElementMaster.InLineEdit.Elements.InLineEdit.views.' . $this->view;
        parent::init();
    }



    public function getSelectList(){
        $select_list = (new DataListModel())
            ->setGlobalParams([
                'card_id' => \Yii::app()->request->getParam('id'),
                'pci' => \Yii::app()->request->getParam('pci'),
                'pdi' => \Yii::app()->request->getParam('pdi'),
                'this_template' => \Yii::app()->request->getParam('this_template'),
                'finished_object' => \Yii::app()->request->getParam('finished_object'),
                'schema_field' => $this->params,
            ])
            ->setExtensionCopy($this->extension_copy)
            ->prepare(\DataListModel::TYPE_FOR_SELECT_TYPE_LIST, null)
            ->getData();

        $this->_no_data = !$select_list;

        return $select_list;
    }




    public function getSelectHtmlOptions(){
        $options = array(
            'id' => $this->params['name'],
            'class' => 'select',
        );

        if($this->_no_data){
            $options['title'] = \Yii::t('messages', 'ToDo lists are not created');
        }

        if(!empty($this->params['input_attr'])){
            $options += (array)$this->params['input_attr'];
        }

        return $options;
    }




}
