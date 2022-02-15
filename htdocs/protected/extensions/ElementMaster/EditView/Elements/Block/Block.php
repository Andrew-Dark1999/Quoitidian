<?php
/**
* Block widget  
* @author Alex R.
* @version 1.0
*/ 

class Block extends CWidget{

    // Схема
    public $schema;
    // Внутренний контент 
    public $content;
    // Данные блока:   block_fields|block_sub_module
    public $block_name = 'block_fields';
    
    public function init()
    {
        if((boolean)$this->schema['params']['edit_view_show'] == false) return;
        
        
        $block_title = '';
        $unique_index = '';
        
        //block_title
        if($this->block_name == 'block_sub_module'){
            $block_title = ExtensionCopyModel::model()->findByPk($this->schema['elements'][0]['params']['relate_module_copy_id'])->title;
        } elseif($this->block_name == 'block_fields'){
            if(!empty($this->schema['params']['title']))
            $block_title = $this->schema['params']['title'];
        }
        //unique_index        
        if(!array_key_exists('unique_index', $this->schema['params']) || empty($this->schema['params']['unique_index'])){
            $unique_index = md5($block_title . date('YmdHis') . mt_rand(1, 1000));
        } else {
            $unique_index = $this->schema['params']['unique_index'];
        }
            
        $this->render('element', array(
                                    'schema'=>$this->schema,
                                    'block_name' => $this->block_name,
                                    'block_title' => $block_title,
                                    'unique_index' => $unique_index,
                                    'content' => $this->content,
                                 )
        );
    }
 

}