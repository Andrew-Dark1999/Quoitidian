<?php

class ProcessViewController extends ProcessView{
    
   

   
    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        throw new CHttpException(404);
    }       



}