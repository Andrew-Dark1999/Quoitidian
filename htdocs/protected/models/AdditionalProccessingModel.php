<?php
/**
 * AdditionalProccessingModel
 * @author Alex B.
 * @version 1.0
 */

class AdditionalProccessingModel{

    private $_run = false;
    
    public function __construct(){
        
        if(@class_exists('DocumentsGenerateModelExt', true)) 
            $this->_run = true;
        
    }
    
    public static function getInstance(){
        return new self();
    }
    
   
   /**
    *   Дополнительная обработка карточки после сохранения
    */
    public function afterSave($parent_data, $copy_id, $id, $is_new_card, $linked_cards){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::afterSave($parent_data, $copy_id, $id, $is_new_card, $linked_cards);

    }
    
    
   /**
    *   Расширения действий для кнопки в listview
    */
    public function getAdditionalBtnActions($extension_copy){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::getAdditionalBtnActions($extension_copy);

    }
        
    
   /**
    *   Запуск по крону
    */
    public function daily($deal_id=false, $date=false, $manual=false){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::daily($deal_id, $date, $manual);

    }
    
    
   /**
    *   Очистка временных данных
    */
    public function clearRubbish($card_id=null){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::clearRubbish($card_id);

    }
        
    
   /**
    *   Добавление связи 
    */
    public function addLinkedCard($card_id, $parent_card_id){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::addLinkedCard($card_id, $parent_card_id);

    }
    
    
   /**
    *   Удаление связи 
    */
    public function clearLinked($copy_id, $parent_copy_id, $parent_card_id, $card_id){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::clearLinked($copy_id, $parent_copy_id, $parent_card_id, $card_id);

    }
    
    
   /**
    *   Обновление карточки, после изменения СМ 
    */
    public function updateSubModule($copy_id, $parent_copy_id, $parent_card_id, $card_ids){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::updateSubModule($copy_id, $parent_copy_id, $parent_card_id, $card_ids);

    }

    
   /**
    *   Данные привязанной карточки
    */
    public function getDataFromLinkedCard($card_id){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::getDataFromLinkedCard($card_id);

    }
    
    
   /**
    *   Дополнительная обработка
    */
    public function additionalUpdate($copy_id, $cards_ids){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::additionalUpdate($copy_id, $cards_ids);

    }
    
    
    public function SRExport($copy_id, $cards_ids, $all_cards){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::SRExport($copy_id, $cards_ids, $all_cards);

    }
    
    
   /**
    *   Регистрация дополнительных скриптов
    */
    public function registerScript($copy_id){

        if(!$this->_run)
            return;
    
        return DocumentsGenerateModelExt::registerScript($copy_id);

    }

}
