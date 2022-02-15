<?php

/**
 * EditViewBuilder - Мастер динамических полей модуля EditView
 * Мастер динамических полей модуля
 */

namespace Finances\extensions\ElementMaster;

class EditViewBuilder extends \EditViewBuilder{
    

   /**
    * Возвращает елемент "Блок кнопок" (BlockButton)
    * @return string (html)  
    */
    public function getEditViewElementBlockButton($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;

        $content = $this->buildEditViewPage($schema['elements']);

        $result = \Yii::app()->controller->widget('\Finances\extensions\ElementMaster\EditView\Elements\Buttons\Buttons',
                                   array(
                                    'schema' => $schema,
                                    'view' => 'block',
                                    'content' => $content,
                                    'extension_copy' => $this->_extension_copy,
                                    'extension_data' => $this->_extension_data,
                                   ),
                                   true);
        return $result; 
    }

}
