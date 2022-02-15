<?php

/**
 * EditViewBuilder - Мастер динамических полей модуля EditView
 * Мастер динамических полей модуля
 */
namespace Reports\extensions\ElementMaster;

class EditViewBuilder extends \EditViewBuilder{




    /**
     * Возвращает елемент "Блок кнопок" (BlockButton)
     * @return string (html)
     */
    public function getEditViewElementBlockButton($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;

        $content = $this->buildEditViewPage($schema['elements']);

        $result = \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.Buttons.Buttons'),
            array(
                'schema' => $schema,
                'view' => 'block',
                'content' => $content,
                'button_attr' => array('save'=> array('class' => 'edit_view_report_constructor_btn-save')),
                'extension_copy' => $this->_extension_copy,
            ),
            true);

        return $result;
    }







}
