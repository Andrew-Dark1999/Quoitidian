<?php
/**
* Avatar widget  
* @author Alex R.
* @version 1.0
*/ 

class Avatar extends CWidget{

    //включает/отключает автоматическое использования метода init
    public $use_init = true;
    //extension_copy
    public $extension_copy = null;
    // Данные строки
    public $data_array;
    // Размер
    public $thumb_size = 32;
    // атрибуты елемента <ing>
    public $attr = array('class' => 'list-view-avatar');
    // показать блок Ответственного
    public $avatar_view_responsible = false;
    // показать блок Учасников как блок Ответственного
    public $avatar_view_participant_as_responsible = false;
    // генирить блок Учасников как блок Ответственного только один раз
    public $avatar_view_participant_as_responsible_once = false;
    // путь к файлу аватара (значение по умолчанию)
    public $src;
    // span|img - тег, что будет возвращен для вывода картинки
    public $tag = 'span';
    // Указывает, что аватар - заглушка
    public $avatar_is_thumb_stub = false;

    // переменые для сохранения данных, когда $avatar_view_participant_as_responsible_once = true
    private static $participant_data_entities  = -1;

    
    public function init(){
        if($this->use_init == false){
            return $this;
        }

        $responsible = array();
        $participant = array();

        // подготовка данных о участниках и ответственных
        if($this->extension_copy){
            if($this->avatar_view_participant_as_responsible == true){
                $responsible = $this->extension_copy->getResponsibleField();
                $participant = $this->extension_copy->getParticipantField();
                $this->avatar_view_responsible = true;
            } else {
                if($this->avatar_view_responsible == true) {
                    $responsible = $this->extension_copy->getResponsibleField();
                }

            }

        }

        //avatar - если нет схемы участников или ответственных - возвращаем только аватар
        if(empty($participant) && empty($responsible)){
            return $this->getAvatar(false);
        }



        //avatar_view_responsible - ворзвращает список ответственных
        if($this->avatar_view_responsible == true) {

            $responsible_data = ParticipantModel::model()->find(array(
                'condition' => 'copy_id = :copy_id AND data_id = :data_id AND responsible = "1"',
                'params' => array(
                    ':copy_id' => $this->extension_copy->copy_id,
                    ':data_id' => $this->data_array[$this->extension_copy->prefix_name . '_id'],
                ),
            ));

            $participant_data_entities = -1;

            if($this->avatar_view_participant_as_responsible_once){
                if(self::$participant_data_entities !== -1) {
                    $participant_data_entities = self::$participant_data_entities;
                }
            }

            if($participant_data_entities === -1) {
                $participant_data_entities = ParticipantModel::model()->getOtherEntities(
                    null,
                    $this->extension_copy->copy_id,
                    $this->data_array[$this->extension_copy->prefix_name . '_id'],
                    Yii::app()->request->getParam('pci'),
                    Yii::app()->request->getParam('pdi'),
                    array(ParticipantModel::PARTICIPANT_UG_TYPE_USER)
                );
            }

            if($this->avatar_view_participant_as_responsible_once){
                if(self::$participant_data_entities === -1) {
                    self::$participant_data_entities = $participant_data_entities;
                }
            }
                        
            return $this->render('responsible', array(
                                        'responsible_data' => $responsible_data,
                                        'participant_data_entities' => $participant_data_entities,
                                        'copy_id' => $this->extension_copy->copy_id,
                                        'data_id' => $this->data_array[$this->extension_copy->prefix_name . '_id']
                                     )
            );

        }
    }


    public function getAvatar($return = true){
        $attr = array();
        foreach($this->attr as $key => $value){
            $attr[] = $key . '="' . $value . '"';
        }
        $attr = implode(' ', $attr);


        if($this->src){
            $src = $this->src;
        } else {
            $src = UploadsModel::getThumbStub();
            $this->avatar_is_thumb_stub = true;

            if(!empty($this->data_array['ehc_image1'])){
                $file = UploadsModel::model()
                    ->setRelateKey($this->data_array['ehc_image1'])
                    ->find();
                if($file){
                    $src = $file
                        ->setFileType('file_image')
                        ->getFileThumbsUrl($this->thumb_size);
                    $this->avatar_is_thumb_stub = false;
                }
            }
        }

        $avatar = $this->render('avatar', array(
                        'attr' => $attr,
                        'src' => $src,
                    ), $return
                );

        $avatar = trim($avatar);

        return $avatar;
    }


    /**
     * Возвращает инициалы пользователя
     */
    public function getUserInitials(){
        if($this->avatar_is_thumb_stub == false){
            return;
        }

        if(empty($this->data_array['users_id'])){
            return;
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_STAFF)->getModule(false);
        
        return StaffModel::model()->findByPk($this->data_array['users_id'])->getInitials();
    }




}
