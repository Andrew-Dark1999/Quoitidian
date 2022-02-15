<?php 
/**
* AvatarModel
* @author Alex R.
*/


class AvatarModel {

    private $_extension_copy;
    private $_data_array;
    private $_attr;
    private $_thumb_size = 32;
    private $_src;
    private $_tag = 'span'; // span|img - тег, что будет возвращен для вывода картинки





    public function loadUserModule(){
        ExtensionModel::model()->findByPk(ExtensionModel::MODULE_USERS)->getModule();
        
        return $this;        
    }


    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;

        return $this;
    }

    public function setDataArray($data_array){
        $this->_data_array = $data_array;

        return $this;
    }


    public function setAttr($attr){
        $this->_attr = $attr;

        return $this;
    }


    public function setThumbSize($thumb_size){
        $this->_thumb_size = $thumb_size;

        return $this;
    }


    public function setSrc($src){
        $this->_src = $src;

        return $this;
    }


    public function setTag($tag){
        $this->_tag = $tag;

        return $this;
    }


    public function setDataArrayFromUserId($uses_id = null){
        if($uses_id === null) {
            $uses_id = WebUser::getUserId();
        }

        $user = UsersModel::model()->findByPk($uses_id);
        if($uses_id == WebUser::getUserId() && !$user){
            Yii::app()->user->logout();
            Yii::app()->request->redirect('/');
            return $this;
        }

        $this->_data_array = $user ? $user->getAttributes() : array();

        return $this; 
    }



    private function getWidgetProperties(){
        $properties = array(
            'use_init' => false,
            'tag' => $this->_tag,
        );

        if($this->_extension_copy){
            $properties['extension_copy'] = $this->_extension_copy;
        }
        if($this->_attr){
            $properties['attr'] = $this->_attr;
        }
        if($this->_data_array){
            $properties['data_array'] = $this->_data_array;
        }
        if($this->_thumb_size){
            $properties['thumb_size'] = $this->_thumb_size;
        }
        if($this->_src){
            $properties['src'] = $this->_src;
        }

        return $properties;
    }

    
    public function getAvatar(){
        return \Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
                    $this->getWidgetProperties()
        )->getAvatar();
    }

}
