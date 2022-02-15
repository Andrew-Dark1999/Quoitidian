<?php
/**
 * PPanelTitle widget
 * @author Alex R.
 */

class PPanelTitle extends CWidget{

    //экземпляр ExtensionCopyModel
    public $extension_copy;
    // Параметры елемента схемы
    public $params = array();
    // Данные поля
    public $value_data;
    // Указывает на активацию провью для изображений и ссылок для файлов
    public $file_link = true;
    // Показывает аватар связаного модуля;
    public $relate_add_avatar = true;
    // разрешает красить елемент (значение) в соответствии его свойств
    public $element_dye = true;
    // выводит только название файла
    public $show_file_name_only = false;
    // список блоков
    public $blocks = array();
    //
    public $field_name_as = null;





    public function init(){
        $this->render('element',
            array(
                'extension_copy' => $this->extension_copy,
                'params' =>  $this->params,
                'value_data' => $this->value_data,
                'file_link' => $this->file_link,
                'relate_add_avatar' => $this->relate_add_avatar,
                'element_dye' => $this->element_dye,
                'show_file_name_only' => $this->show_file_name_only,
                'blocks' => $this->blocks,
            )
        );

    }





    public function getHtmlRelateParticipant(){
        $html = '';

        if(!empty($this->value_data['participant_ug_id'])){
            $relate_select_list = DataModel::getInstance()
                ->setFrom('{{users}}')
                ->setWhere('users_id = ' . $this->value_data['participant_ug_id'])
                ->findRow();

            if(!empty($relate_select_list)){
                $html = DataValueModel::getInstance()
                    ->setFileLink(false)
                    ->getRelateValuesToHtml($relate_select_list, array(
                        'relate_field' => array('sur_name', 'first_name', 'father_name'),
                        'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF), $this->relate_add_avatar); //берем данные из пользователей
            }
        }

        return $html;
    }





    public function getHtmlSelect(){
        $field_name = $this->params['name'];
        $id = $this->value_data[$field_name];

        if(!$id){
            return;
        }

        $relate_select = DataModel::getInstance()
            ->setFrom($this->extension_copy->getTableName($this->params['name']))
            ->setWhere($field_name . '_id = ' . $id)
            ->findRow();

        return $relate_select[$field_name . '_title'];
    }







    public function getHtmlRelate(){
        $html = array();

        $id = $this->value_data[$this->field_name_as];

        if(!$id){
            return;
        }

        $relate_extension_copy = ExtensionCopyModel::model()->findByPk($this->params['relate_module_copy_id']);

        $relate_data = DataModel::getInstance()
            ->setExtensionCopy($relate_extension_copy)
            ->setFromModuleTables();

        $relate_data
            ->setFromFieldTypes();
        //responsible
        if($relate_extension_copy->isResponsible())
            $relate_data->setFromResponsible();
        //participant
        if($relate_extension_copy->isParticipant())
            $relate_data->setFromParticipant();
        $relate_data
            ->setCollectingSelect()
            ->andWhere(array('AND', $relate_extension_copy->getTableName() . '.' . $relate_extension_copy->prefix_name . '_id' . '=:' . $relate_extension_copy->prefix_name . '_id'),
                array(':' . $relate_extension_copy->prefix_name . '_id' => $id));

        $relate_data->setGroup($relate_extension_copy->prefix_name . '_id');

        $relate_data = $relate_data->findAll();

        if(!empty($relate_data)){
            foreach($relate_data as $relate_value){
                $html[] = DataValueModel::getInstance()->setFileLink(false)->getRelateValuesToHtml($relate_value, $this->params, $this->relate_add_avatar);
            }
        }

        return implode(' ', $html);
    }




    public function isDateTimeAllDay(){
        $field_name = $this->params['name'] . '_ad';

        if(empty($this->value_data[$field_name])){
            return true;
        }

        $value = $this->value_data[$field_name];

        return (bool)$value;
    }



}
