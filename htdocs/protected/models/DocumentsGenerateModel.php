<?php

class DocumentsGenerateModel{

    const FIELD_NAME_MARK = 'is_main';
    const MARK_TRUE = 1;

    const NUMBER_AS_TEXT = '_NUMBER_AS_TEXT';
    const SUM_AS_TEXT = '_SUM_AS_TEXT';
    const FLOAT_AS_TEXT = '_FLOAT_AS_TEXT';
    const FRACTION_AS_TEXT = '_FRACTION_AS_TEXT';
    const DATE_AS_TEXT = '_DATE_AS_TEXT';
    const DATE_AS_FULLTEXT = '_DATE_AS_FULLTEXT';
    const DATE_AS_TEXT_QUOTES = '_DATE_AS_TEXT_QUOTES';
    
    /**
     * Именительный падеж
     */
    const CONVERT_TO_CASE_1 = '_CONVERT_TO_CASE_1';
    
    /**
     * Родительный падеж
     */
    const CONVERT_TO_CASE_2 = '_CONVERT_TO_CASE_2';
    
    /**
     * Дательный падеж
     */
    const CONVERT_TO_CASE_3 = '_CONVERT_TO_CASE_3';
    
    /**
     * Винительный падеж
     */
    const CONVERT_TO_CASE_4 = '_CONVERT_TO_CASE_4';
    
    /**
     * Творительный падеж
     */
    const CONVERT_TO_CASE_5 = '_CONVERT_TO_CASE_5';
    
    /**
     * Предложный падеж
     */
    const CONVERT_TO_CASE_6 = '_CONVERT_TO_CASE_6';
    

    /**
     * Числовые значения для вывода текста прописью
     */
    private $zero = 'ноль';
    private $nmb_ten = array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    private $nmb_ten_mod1 = array(
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    private $nmb_ten_case2 = array(
        array('','первого','второго','третьего','четвертого','пятого','шестого','седьмого', 'восьмого','девятого'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    private $nmb_ten_fraction = array(
        array('','первая','вторая','третья','четвертая','пятая','шестая','седьмая', 'восьмая','девятая'),
        array('','первых','вторых','третих','четвертых','пятых','шестых','седьмых', 'восьмых','девятых'),
    );
            
    private $a20 = array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    private $a20_fraction_mod1 = array('десятых','одиннадцатых','двенадцатых','тринадцатых','четырнадцатых' ,'пятнадцатых','шестнадцатых','семнадцатых','восемнадцатых','девятнадцатых');
    private $a20_fraction_mod2 = array('десятая','одиннадцатая','двенадцатая','тринадцатая','четырнадцатая' ,'пятнадцатая','шестнадцатая','семнадцатая','восемнадцатая','девятнадцатая');
    private $a20_case2 = array('десятого','одиннадцатого','двенадцатого','тринадцатого','четырнадцатого' ,'пятнадцатого','шестнадцатого','семнадцатого','восемнадцатого','девятнадцатого');
    
    private $tens = array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    private $tens_case2 = array(2=>'двадцатого','тридцатого','сорокового','пятидесятого','шестидисятого','семидесятого' ,'восьмидесятого','девяностого');
    private $tens_fraction_mod1 = array(2=>'двадцатых','тридцатых','сороковых','пятидесятых','шестидесятых','семидесятых' ,'восемидесятых','девяностых');
    private $tens_fraction_mod2 = array(2=>'двадцатая','тридцатая','сороковая','пятидесятая','шестидесятая','семидесятая' ,'восемидесятая','девяностая');
    
    private $hundred = array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    
    private $units = array(
        array('копейка' ,'копейки' ,'копеек',	 1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    private $units_mod1 = array( 
        array('сотая' ,'сотых' ,'сотых',	 1),
        array('целая'   ,'целых'   ,'целых'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );

    //поиск определенных СРМ связей. по умолчанию все
    private $findCRM = false;
    
    //максимальный уровень вложенности рекурсии. по умолчанию 3
    private $maxIterationLevel = 7;
    
    //данные модуля Документы
    private $doc_extension_copy_id = null;
    private $doc_extension_copy = null;
    private $doc_extension_copy_schema = null;
    
    //данные модуля, из-под которого идет печать
    private $extension_copy_id = null;
    private $extension_copy = null;
    private $extension_copy_schema = null;
    
    private $use_extended_generate = false;

    public function __construct(){
        $this->doc_extension_copy_id = \ExtensionCopyModel::MODULE_DOCUMENTS;
        $this->doc_extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($this->doc_extension_copy_id);
        $this->doc_extension_copy_schema = $this->doc_extension_copy->getSchema();
        
        if(@class_exists('DocumentsGenerateModelExt', true))
            $this->use_extended_generate = true;
        
    }
    
    public static function getInstance(){
        return new self();
    } 
    
    public function getExtensionCopyId(){
        return $this->extension_copy_id;
    } 
    
    public function getExtensionCopy(){
        return $this->extension_copy;
    } 
    
    public function getExtensionCopySchema(){
        return $this->extension_copy_schema;
    } 
    
    public function getMaxIterationLevel(){
        return $this->maxIterationLevel;
    } 
    
    
    /**
     *   Получаем массив СМ записей из документа
     */ 
    public function getDocumentSM($import_file, $extension_copy, $extension_copy_id, $gen_module_id, $sm=array()){
        
        $result = array();
        
        switch(mb_strtolower(pathinfo($import_file, PATHINFO_EXTENSION))){
            
            case 'tpl':
            
                //получаем все переменные с родительского шаблона
                $vars = \SmartyGenerate::getInstance()->getVariablesFromTemplate($import_file, $gen_module_id);
                
            break;
            
            case 'docx':

                $vars = \WordGenerate::getInstance()->getVariablesFromTemplate($import_file, $gen_module_id);

            break;
            
            case 'xlsx':
            
                $vars = \ExcelGenerate::getInstance()->getVariablesFromTemplate($import_file, $gen_module_id);

            break;
            
            default:
            
                //загрузка всех СМ записей
                //$result = $this->getAllSM($extensionCopy, $extensionCopyId, $genModuleId, $sm);
            
            break;
            
        }

        $vars = $this->processingVars($vars); 
        $this->setExtensionCopy($extension_copy, $extension_copy_id);
        $result = $this->getPreparedSM($vars, $import_file, $sm);

        return array($vars, $result);
        
    }
    

    /**
     *   Дополнительная обработка переменных
     *   (переменная должна всегда начинаться с названия модуля Документы,
     *   в случае совпадения имен за основу берем данные с модуля Документы)
     *   Отсюда алгоритм: 1) оставляем только переменные, начинающиеся с названия модуля
     *   2) удаляем везде название модуля
     *   3) достаем связи модуля Документы, и добавляем этот префис к тем переменным, где есть связь
     */ 
    private function processingVars($vars){
        
        $simple_variables = array(); //обычные переменные + СДМ
        $sm_1st_variables = array(); //СМ 1-го уровня
        $sm_other_variables = array(); //СМ 2-го и вышего уровней (выбор только единичных записей)
        $matching_variables = array(); //массив соответсвий имен
        $postprocessing_variables = array(); //массив постобработки переменных
        $no_popup = array(); //модули, которые не показываем в попапе
        
        if(isset($vars['no_popup']))
            $no_popup = $vars['no_popup'];
        
        //пп. 1-2
        if(count($vars[0]))
            foreach($vars[0] as $k => $v) {
                //if(mb_substr($v, 0, mb_strlen($docTitle)) == $docTitle)
                    $simple_variables[]=  mb_substr($v, mb_strlen($this->doc_extension_copy->attributes['title'])+1);
                    $matching_variables[$v]=  mb_substr($v, mb_strlen($this->doc_extension_copy->attributes['title'])+1);
                
            }    

        if(count($vars[1]))
            foreach($vars[1] as $k => $v){
                if(mb_substr($v, mb_strlen($this->doc_extension_copy->attributes['title'])+1) != '') { 
                    $sm_1st_variables[]= mb_substr($v, mb_strlen($this->doc_extension_copy->attributes['title'])+1);
                    $matching_variables[$v]=  mb_substr($v, mb_strlen($this->doc_extension_copy->attributes['title'])+1);
                }    
            }    
        
        if(count($vars[2]))
            foreach($vars[2] as $k => $v) {
                //if(mb_substr($v, 0, mb_strlen($docTitle)) == $docTitle)
                    $sm_other_variables[]=  mb_substr($v, mb_strlen($this->doc_extension_copy->attributes['title'])+1);
                    $matching_variables[$v]=  mb_substr($v, mb_strlen($this->doc_extension_copy->attributes['title'])+1);
                
            }       

        //теперь загружаем связи модуля Документы и добавляем префикс
        if(count($simple_variables)) {
            
            //обычные переменные или СДМ 
            $structure = $this->getData($this->doc_extension_copy, 0, true);
            
            //ищем модули СДМ Документов
            $modules = array();
            $r =  ModuleTablesModel::model()->findAllByAttributes(array('copy_id'=>$this->doc_extension_copy_id , 'type'=>'relate_module_one'));
            
            if(count($r))
                foreach($r as $v)
                    $modules []= $this->deleteSpaces(\ExtensionCopyModel::model()->modulesActive()->findByPk($v->relate_copy_id)->title);
                
            foreach($simple_variables as $k => $v){
                
                $x = explode(":", $v);
              
                if(count($x)==1) {
                    
                    //обычная переменная
                    if(array_key_exists($x[0], $structure)) {
                       $simple_variables[$k]= $this->doc_extension_copy->attributes['title'] . ':' . $v;
                       $matching_variables[array_search($v, $matching_variables)] = $this->doc_extension_copy->attributes['title'] . ':' . $v;
                    }
                }else {
                    
                    //СДМ переменная
                    if(in_array($x[0], $modules)) {
                        $simple_variables[$k]= $this->doc_extension_copy->attributes['title'] . ':' . $v;
                        $matching_variables[array_search($v, $matching_variables)] = $this->doc_extension_copy->attributes['title'] . ':' . $v;
                    }   
                }
                
            }
                
            
        }        
                
        if(count($sm_1st_variables) || count($sm_other_variables)) {
            
            //СМ переменные
            
            //ищем модули СДМ Документов
            unset($modules);
            $modules = array();
            $r =  ModuleTablesModel::model()->findAllByAttributes(array('copy_id'=>$this->doc_extension_copy_id, 'type'=>'relate_module_many'));
            
            if(count($r))
                foreach($r as $v)
                    $modules []= $this->deleteSpaces(\ExtensionCopyModel::model()->modulesActive()->findByPk($v->relate_copy_id)->title);
            
            if(count($sm_1st_variables))
                foreach($sm_1st_variables as $k => $v){
                    $x = explode(":", $v);
                    if(in_array($x[0], $modules)) {
                       $sm_1st_variables[$k]= $this->doc_extension_copy->attributes['title'] . ':' . $v;
                       $matching_variables[array_search($v, $matching_variables)] = $this->doc_extension_copy->attributes['title'] . ':' . $v;
                    }   
                }
            
            if(count($sm_other_variables)) {
                
                foreach($sm_other_variables as $k => $v){
                    $x = explode(":", $v);
                    if(in_array($x[0], $modules)) {
                        $sm_other_variables[$k]= $this->doc_extension_copy->attributes['title'] . ':' . $v;
                        $matching_variables[array_search($v, $matching_variables)] = $this->doc_extension_copy->attributes['title'] . ':' . $v;
                    }else {
                            
                        if(count($x)==2) {
                            
                            //эта связь не принадлежит Документам, если элементов 2, то перемещаем в предыдущий массив
                            
                            $sm_1st_variables[] = $x[1];  //копируем в массив СМ записей
                            unset($sm_other_variables[$k]); //и удаляем отсюда

                            $matching_variables[array_search($v, $matching_variables)] = $x[1];

                        }else {
                            
                            //больше двух, удаляем первый элемент (название модуля)
                            unset($x[0]); 
                            $sm_other_variables[$k] = implode(":", $x);
                            
                            $matching_variables[array_search($v, $matching_variables)] = implode(":", $x);

                        }  
                    }
                } 
            }
        }
      
        $simple_variables = array_unique($simple_variables);
        
        if(count($simple_variables)>0){
            foreach ($simple_variables as $k=>$v) {
                if(mb_substr($v, -strlen(DocumentsGenerateModel::NUMBER_AS_TEXT)) == DocumentsGenerateModel::NUMBER_AS_TEXT) {
                    $simple_variables[$k] = mb_substr($v, 0, strlen($v) - strlen(DocumentsGenerateModel::NUMBER_AS_TEXT));
                    $postprocessing_variables[$simple_variables[$k]] = DocumentsGenerateModel::NUMBER_AS_TEXT;
                }
                
                if(mb_substr($v, -strlen(DocumentsGenerateModel::SUM_AS_TEXT)) == DocumentsGenerateModel::SUM_AS_TEXT) {
                    $simple_variables[$k] = mb_substr($v, 0, strlen($v) - strlen(DocumentsGenerateModel::SUM_AS_TEXT));
                    $postprocessing_variables[$simple_variables[$k]] = DocumentsGenerateModel::SUM_AS_TEXT;
                }
                
                if(mb_substr($v, -strlen(DocumentsGenerateModel::FLOAT_AS_TEXT)) == DocumentsGenerateModel::FLOAT_AS_TEXT) {
                    $simple_variables[$k] = mb_substr($v, 0, strlen($v) - strlen(DocumentsGenerateModel::FLOAT_AS_TEXT));
                    $postprocessing_variables[$simple_variables[$k]] = DocumentsGenerateModel::FLOAT_AS_TEXT;
                }
                
                if(mb_substr($v, -strlen(DocumentsGenerateModel::FRACTION_AS_TEXT)) == DocumentsGenerateModel::FRACTION_AS_TEXT) {
                    $simple_variables[$k] = mb_substr($v, 0, strlen($v) - strlen(DocumentsGenerateModel::FRACTION_AS_TEXT));
                    $postprocessing_variables[$simple_variables[$k]] = DocumentsGenerateModel::FRACTION_AS_TEXT;
                }
                
                if(mb_substr($v, -strlen(DocumentsGenerateModel::DATE_AS_TEXT)) == DocumentsGenerateModel::DATE_AS_TEXT){
                    $simple_variables[$k] = mb_substr($v, 0, strlen($v) - strlen(DocumentsGenerateModel::DATE_AS_TEXT));
                    $postprocessing_variables[$simple_variables[$k]] = DocumentsGenerateModel::DATE_AS_TEXT;
                }
                
                if(mb_substr($v, -strlen(DocumentsGenerateModel::DATE_AS_FULLTEXT)) == DocumentsGenerateModel::DATE_AS_FULLTEXT){
                    $simple_variables[$k] = mb_substr($v, 0, strlen($v) - strlen(DocumentsGenerateModel::DATE_AS_FULLTEXT));
                    $postprocessing_variables[$simple_variables[$k]] = DocumentsGenerateModel::DATE_AS_FULLTEXT;
                }
                
                if(mb_substr($v, -strlen(DocumentsGenerateModel::DATE_AS_TEXT_QUOTES)) == DocumentsGenerateModel::DATE_AS_TEXT_QUOTES){
                    $simple_variables[$k] = mb_substr($v, 0, strlen($v) - strlen(DocumentsGenerateModel::DATE_AS_TEXT_QUOTES));
                    $postprocessing_variables[$simple_variables[$k]] = DocumentsGenerateModel::DATE_AS_TEXT_QUOTES;
                }
                
            }   
        }

        $sm_1st_variables = array_unique($sm_1st_variables);
        $sm_other_variables = array_unique($sm_other_variables);
        $matching_variables = array_unique($matching_variables);
        //$p_vars5 = array_unique($p_vars5);

        return array('simple_variables'=>$simple_variables, 'sm_1st_variables'=>$sm_1st_variables, 'sm_other_variables'=>$sm_other_variables, 'matching_variables'=>$matching_variables, 'postprocessing_variables'=>$postprocessing_variables, 'no_popup'=>$no_popup);
        
    }
    
    
    /**
     *   Получаем массив связей из шбалонов
     */ 
    private function getPreparedSM($vars, $importFile, $sm=array()){
        
        $result = array();

        if(count($vars['sm_1st_variables'])>0) {
            foreach($vars['sm_1st_variables'] as $value) {
                if(array_search($value, $vars['no_popup']) === false){
             
                    //$x - массив с вложенными модулями, получаем СМ из первого
                    $x = explode(":", $value);
                    
                    //СМ записи модуля, с которого идет генерация
                    $result = $this->collectSM($this->extension_copy_schema, $this->extension_copy->copy_id, $this->extension_copy_id , $result, $x[0]);

                    if($this->deleteSpaces($x[0])==$this->deleteSpaces($this->doc_extension_copy->attributes['title'])) {
                        
                        //это СМ записи модуля Документы
                        if(isset($x[1])) {

                            //в смарти переменной указан конкретный сабмодуль Документов
                            if(count($sm)>0) {
                             
                                //данные id передаются из формы где ссылка Сгенерировать
                                foreach($sm as $v) {
                                    $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($v->relate_copy_id);
                                    $field_params_list = $this->getSchemaFieldsParams($extension_copy);  

                                    if($this->deleteSpaces($extension_copy->attributes['title'])==$this->deleteSpaces($x[1])) {
                                         
                                        //совпадает имя модуля с переменной смарти
                                        if(isset($v->data_id_list[0])) {
                                            $data = $this->getData($extension_copy, $v->data_id_list);      
                                            $result [$this->doc_extension_copy->attributes['title'] . ':' . $extension_copy->attributes['title']] = array($v->relate_copy_id, '0', $data);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $result;  
    }
    
    
    /**
     *  Генерация нового документа, все данные у нас уже есть
     */ 
    public function generateDocument($vars, $upload_export_model, $import_file, $export_file, $extension_copy, $extension_copy_id, $gen_module_id, $form_data=array(), $sdm_ids=array(), $sm=array(), $doc_id){
         
        $result = array(false);

        $this->setExtensionCopy($extension_copy, $extension_copy_id);
        
        $params = $this->getParams($vars, $form_data, $sdm_ids, @$sm, $doc_id); 
        $res = $this->processingVarsRevert($params, $vars); 

        $params = $res[0];
        $vars = $res[1];

        $new_filename = false;
        if($this->use_extended_generate) {
            if(method_exists('DocumentsGenerateModelExt', 'getFilename'))
                $new_filename = DocumentsGenerateModelExt::getFilename($params, $extension_copy, $extension_copy_id, $upload_export_model);
        }

        switch(mb_strtolower(pathinfo($import_file, PATHINFO_EXTENSION))){
            
            case 'tpl':

                $result = (\SmartyGenerate::getInstance()->generateDocument($vars, $import_file, $upload_export_model , $params, $new_filename));

            break;
            
            case 'docx':
            
                $result = (\WordGenerate::getInstance()->generateDocument($vars, $import_file, $upload_export_model, $params, $new_filename));

            break;
            
            case 'xlsx':

                $result = (\ExcelGenerate::getInstance()->generateDocument($vars, $import_file, $export_file, $params));
                
            break;
                 
            default:
            
                //получить все параметры для замены
                //$params = $this->getAllParams($extensionCopy, $extensionCopyId, $genModuleId, $formData, $sdmIDs, @$sm);
            
            break;
            
        }
        
        return $result;
   
    }
    
    
    /**
     *   Получаем переменные для замены
     */ 
    private function getParams($vars, $form_data=array(), $sdm_ids=array(), $sm=array(), $doc_id){

        //проверяем переменные на СДМ, до какого уровня перебирать (макс $this->maxIterationLevel) и какие именно
        $sdm = array();
        $maxLevel = 1;
            
        if(count($vars['simple_variables'])>0)
            foreach($vars['simple_variables'] as $value) {
            
                //$x - массив с вложенными модулями
                $x = explode(":", $value);
                
                if(count($x)>1) {
                    
                     //получаем СДМ, где есть вложенность, иначе это обычная переменная
                     if(count($x) <= $this->maxIterationLevel) {
                     
                        //не больше уровня вложенности
                        if($maxLevel < count($x))
                            $maxLevel = count($x);
                     
                        array_pop($x);
                        $sdm []= implode(":", $x);
                    }
                }
            }
            
        $this->maxIterationLevel = $maxLevel;
        $this->findCRM = array_unique($sdm);

        $result = array();

        //данные текущего модуля
        if(in_array($this->extension_copy->attributes['title'], $this->findCRM))
            $result[$this->extension_copy->attributes['title']] = $this->getData($this->extension_copy, $this->extension_copy_id, true);

        //СДМ текущего модуля
        if($this->findCRM) {
            $res = array();
            foreach($this->findCRM as $sdm){
                $x = explode(':', $sdm);
                if((count($x)>=2) && ($this->deleteSpaces($this->extension_copy->attributes['title'])==$x[count($x)-2])) 
                    $res[] = $x[count($x)-1];   
            }
            $rlts = $this->getSDMRelatesDataFromExtensionCopy($this->extension_copy, $this->extension_copy_id, $res);
            if(count($rlts)){
                foreach($rlts as $rlt_key => $rlt_value) {
                    //запись единым массивом (в будущем предпочтительней)
                    //$result [$this->deleteSpaces($this->extension_copy->attributes['title']) . ':' . $rlt_key] = $rlt_value;
                    //запись отдельными переменными
                    if(count($rlt_value)){
                        foreach($rlt_value as $k=>$v){
                            $result [$this->doc_extension_copy->attributes['title'] . ':' . $this->deleteSpaces($this->extension_copy->attributes['title']) . ':' . $rlt_key . ':' . $k] = $v;
                        }
                    }
                }
            }
        }
       
        //генерация массива с заменами с формы Документы
        if(count($form_data)>0)
            foreach($form_data as $k => $v) {
                $kn = (preg_match("#\[(.*?)\]#", $k, $arr)) ? substr($arr[0], 1, -1) : $k;
                
                if($v)
                    $result[$this->doc_extension_copy->attributes['title'] . ':' . $kn] = $v;
                
            }
            
        
        //генерация массива с заменами с СДМ с формы Документы
        if(count($sdm_ids)>0)
            foreach($sdm_ids as $k => $v)
                if($v)
                    $result = $this->getRelate($k, $v, $result, 2, $this->doc_extension_copy->attributes['title'] . ':');
             

        //обработка СМ связей
        
        //разрешаем СДМ (последнего уровня) для СМ записи
        $this->findCRM = false;

        if(!isset($sm))
            $sm = new stdClass();
        
        //no popup. загружаем ВСЕ СМ записи модулей
        if(isset($vars['no_popup'])){
            if(count($vars['no_popup'])>0){
                foreach($vars['no_popup'] as $v){
                    
                    //проверяем, есть ли такая переменная в шаблоне, чтобы не загружать лишнее
                    if(array_search($v, $vars['sm_1st_variables']) !== false){
                    
                        $x = explode(':', $v);
                        
                        $title = (isset($x[1])) ? $x[1] : $v;
                        $copy_id = (isset($x[1])) ? $this->doc_extension_copy->copy_id : $this->extension_copy->copy_id;
                        $ex_copy_id = (isset($x[1])) ? $doc_id : $this->extension_copy_id;
                        $schema = (isset($x[1])) ? $this->doc_extension_copy_schema : $this->extension_copy_schema;

                        if(isset($x[1]) && !$doc_id) {
                            //СМ запись модуля Документы, но запись только что создана, не сохранена, поэтому пропускаем
                            $all_sm = array();
                       }else
                            $all_sm = $this->collectSM($schema, $copy_id, $ex_copy_id, array(), $title);

                        if(count($all_sm)>0) {
                            
                            $sm->{$all_sm[$title][0]} = new stdClass();
                            $sm->{$all_sm[$title][0]}->copy_id_list = array();
                            $sm->{$all_sm[$title][0]}->relate_result = (count(@$all_sm[$title][2])>0) ? 1 : false;
                            $sm->{$all_sm[$title][0]}->its_no_documents_module = (isset($x[1])) ? 0 : 1;
                            
                            $e_copy_c = \ExtensionCopyModel::model()->modulesActive()->findByPk($all_sm[$title][0]);
                            
                            //записи были найдены
                            if(count(@$all_sm[$title][2])>0) 
                                foreach($all_sm[$title][2] as $row) {
                                    //var_dump($extension_copy->prefix_name);
                                    $sm->{$all_sm[$title][0]}->copy_id_list[] = $row[$e_copy_c->prefix_name. '_id'];
                                }
                        }
                    
                    }
                }
            } 
        }
        

        if(count($sm)>0) {
             foreach($sm as $copy_id => $v){
                if(!empty($v->copy_id_list)){
                    $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($copy_id);
                    $data = $this->getData($extension_copy, $v->copy_id_list);    //$v[1] array(1, 2, 3)
              
                    $title = ($v->its_no_documents_module) ? $extension_copy->title : $this->doc_extension_copy->attributes['title'] . ':' . $extension_copy->title;
                    
                    $x = array();
                    
                    /*
                    for($i=0; $i < count($data[0]); $i++)
                        $x[] = $data[0][$i];

                    $result [$title] = $x;
                    */
                    
                    //$data - необработанные данные, обрабатываем их
                    $field_params_list = $this->getSchemaFieldsParams($extension_copy); 

                    $data_tmp = DataValueModel::getInstance()
                            ->setSchemaFields($field_params_list)
                            ->setExtensionCopy($extension_copy)
                            ->prepareData($data)
                            ->getProcessedData()// без обьединения значений
                            ->getData();


                     if(count($data_tmp)>0)
                        foreach($data_tmp as $k => $value) {
                           foreach($value as $k2 =>$v2)
                             if($v2['value'])
                                $x[$k2] = $v2['value'];

                            //$result [$title][] = $x;
                            
                            //теперь ищем СДМ последнего уровня
                            $prefix = ($v->its_no_documents_module) ? '' : $this->doc_extension_copy->attributes['title'] . ':';
                            $params = $this->getRelate($copy_id, $data[$k][$extension_copy->prefix_name. '_id'], array(), $this->maxIterationLevel-1, $prefix, true);
                            $x['_copy_id'] = $copy_id;
                            $x['_extension_copy_id'] = $data[$k][$extension_copy->prefix_name. '_id'];
                            $result [$title][] = array_merge($x, $params);

                        }
                     //данные преобразованы

                     
                }
            }            
        }
            
        //теперь ищем данные СМ 2-го и вышего уровней (если есть)    
        if(count($vars['sm_other_variables']>0))
            foreach($vars['sm_other_variables'] as $sm2) {
                
                unset($sm2_array);
                $sm2_array = array();
                
                $ex_copy = $this->extension_copy->copy_id;
                $ex_copy_id = $this->extension_copy_id;
                
                $x = explode(":", $sm2);
                
                if(isset($doc_id)) {
                    
                    //документ открыт для редактирования, проверяем модуль Документы
                    if($this->doc_extension_copy->attributes['title'] == $x[0]) {
                        
                        $ex_copy = $this->doc_extension_copy->copy_id;
                        $ex_copy_id = $doc_id;
                        
                        array_shift($x);
                        //unset($x[0]);
                    }
                    
                }

                if(count($x)<=$this->maxIterationLevel) {

                    foreach($x as $k => $module_title) {
                        
                        $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($ex_copy);
                        $schema = $extension_copy->getSchema();
                    
                        $sm_current_level = $this->collectSM($schema, $ex_copy, $ex_copy_id, array(), $module_title);

                        if(count(@$sm_current_level>0)) {
                            
                            //записи были найдены
                            if(count(@$sm_current_level[$module_title][2])>0) {
 
                                $single_data = $this->getSingleData($sm_current_level[$module_title][2]);

                                if(count($x)==$k+1) {
                                    
                                    //последний шаг, добавляем массив найденных записей
                                    $sm2_array = $single_data;
                                    
                                }else {
                                    
                                    //продолжаем дальше
                                    $ex_copy = $sm_current_level[$module_title][0];
                                    $extension_copy_relate = ExtensionCopyModel::model()->findByPk($ex_copy);
                                    $ex_copy_id = $single_data[$extension_copy_relate->prefix_name . '_id'];
                                    
                                }
                                
                            }else
                                break; 
                        }
                        
                    }

                    $result [$sm2][] = $sm2_array;
                    
                }
            }

        //фикс, ищем СМ после СДМ связей 
        if(count($vars['simple_variables']>0)){
            foreach($vars['simple_variables'] as $sm2) {
                if(!isset($result[$sm2])) {
                    
                    //переменная не была обработана, возможно это СМ связь
                    $x = explode(":", $sm2);
                    
                    if($doc_id) {

                        //документ открыт для редактирования, проверяем модуль Документы
                        if($this->doc_extension_copy->attributes['title'] == $x[0])
                            array_shift($x);
 
                    }

                    if(count($x)>1) {
                        $mf = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>$x[0]));
      
                        if($mf !== null) {
                            if(isset($sdm_ids->{$mf->copy_id})) {

                                $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($mf->copy_id);
                                $schema = $extension_copy->getSchema();
                            
                                $sm = $this->collectSM($schema, $mf->copy_id, $sdm_ids->{$mf->copy_id}, array(), $x[1]);
                                    
                                if(count($sm)>0) {
                                    
                                    //удаляем пробелы с ключа массива
                                    foreach($sm as $k=>$v) {
                                        unset($sm[$k]);
                                        $sm[$this->deleteSpaces($k)] = $v;
                                    }
                                    
                                    if(isset($sm[$x[1]][2])){
                                        $single_data = $this->getSingleData($sm[$x[1]][2]);
                                        if(array_key_exists(end($x), $single_data))
                                            $result[$sm2] = $single_data[end($x)];
                                        
                                    }
                                }
                            }
                        }   
                    }
                }
            }
        }

        $result = $this->getParamsExt($result);    

        //print_r($result); die();
        return $result;
        
    }
    
    
    /**
     *   Дополнительная обработка переменных
     */ 
    private function getParamsExt($result){

        if($this->use_extended_generate)
            if(method_exists('DocumentsGenerateModelExt', 'getParams'))
                $result = DocumentsGenerateModelExt::getParams($result, $this);

        return $result;
    
    }
    
    
    /**
     *   Обратная обработка переменных, меняем как переменные, так и массив с результатом
     *   (добавляем названия модуля Документы, если не указано)
     */ 
    private function processingVarsRevert($params, $vars){

        $p_params = array();
        
        $doc_title = \ExtensionCopyModel::model()->modulesActive()->findByPk(ExtensionCopyModel::MODULE_DOCUMENTS)->attributes['title'];
        
        if(count($vars['postprocessing_variables']))    
            foreach($vars['postprocessing_variables'] as $k=>$v) {

                if(isset($params[$k])) {

                    $tmp = $this->postProcessingVar($params[$k], $v);

                    //unset($params[$k]);
                    $params[$k . $v] = $tmp; 
                    
                }elseif(isset($params[$doc_title.':'.$k])) {
                    
                    $tmp = $this->postProcessingVar($params[$doc_title.':'.$k], $v);

                    //unset($params[$doc_title.':'.$k]);
                    $params[$doc_title . ':'. $k . $v] = $tmp; 
                }
            }   

        if(count($params))
            foreach($params as $k=>$v) {
                
                if(in_array($k, $vars['matching_variables']))
                    $p_params[array_search($k, $vars['matching_variables'])] = $v;else
                    $p_params[$k] = $v;
                
            }
            
          
        if(count($vars['simple_variables'])) {
            foreach($vars['simple_variables'] as $k=>$v) {
                
                if(array_key_exists($v, $vars['postprocessing_variables'])) 
                    $v .= $vars['postprocessing_variables'][$v]; 
                
                if(in_array($v, $vars['matching_variables'])) {
                    $vars['simple_variables'][$k] = array_search($v, $vars['matching_variables']);
                    
                    if(mb_substr(array_search($v, $vars['matching_variables']), -strlen(DocumentsGenerateModel::NUMBER_AS_TEXT)) == DocumentsGenerateModel::NUMBER_AS_TEXT)
                        $vars['simple_variables'][] = mb_substr(array_search($v, $vars['matching_variables']), 0, mb_strlen(array_search($v, $vars['matching_variables'])) - mb_strlen(DocumentsGenerateModel::NUMBER_AS_TEXT));
                    
                    if(mb_substr(array_search($v, $vars['matching_variables']), -strlen(DocumentsGenerateModel::SUM_AS_TEXT)) == DocumentsGenerateModel::SUM_AS_TEXT)
                        $vars['simple_variables'][] = mb_substr(array_search($v, $vars['matching_variables']), 0, mb_strlen(array_search($v, $vars['matching_variables'])) - mb_strlen(DocumentsGenerateModel::SUM_AS_TEXT));
                    
                    if(mb_substr(array_search($v, $vars['matching_variables']), -strlen(DocumentsGenerateModel::FLOAT_AS_TEXT)) == DocumentsGenerateModel::FLOAT_AS_TEXT)
                        $vars['simple_variables'][] = mb_substr(array_search($v, $vars['matching_variables']), 0, mb_strlen(array_search($v, $vars['matching_variables'])) - mb_strlen(DocumentsGenerateModel::FLOAT_AS_TEXT));
                    
                    if(mb_substr(array_search($v, $vars['matching_variables']), -strlen(DocumentsGenerateModel::FRACTION_AS_TEXT)) == DocumentsGenerateModel::FRACTION_AS_TEXT)
                        $vars['simple_variables'][] = mb_substr(array_search($v, $vars['matching_variables']), 0, mb_strlen(array_search($v, $vars['matching_variables'])) - mb_strlen(DocumentsGenerateModel::FRACTION_AS_TEXT));
                    
                    if(mb_substr(array_search($v, $vars['matching_variables']), -strlen(DocumentsGenerateModel::DATE_AS_TEXT)) == DocumentsGenerateModel::DATE_AS_TEXT)
                        $vars['simple_variables'][] = mb_substr(array_search($v, $vars['matching_variables']), 0, mb_strlen(array_search($v, $vars['matching_variables'])) - mb_strlen(DocumentsGenerateModel::DATE_AS_TEXT));

                    if(mb_substr(array_search($v, $vars['matching_variables']), -strlen(DocumentsGenerateModel::DATE_AS_FULLTEXT)) == DocumentsGenerateModel::DATE_AS_FULLTEXT)
                        $vars['simple_variables'][] = mb_substr(array_search($v, $vars['matching_variables']), 0, mb_strlen(array_search($v, $vars['matching_variables'])) - mb_strlen(DocumentsGenerateModel::DATE_AS_FULLTEXT));

                    if(mb_substr(array_search($v, $vars['matching_variables']), -strlen(DocumentsGenerateModel::DATE_AS_TEXT_QUOTES)) == DocumentsGenerateModel::DATE_AS_TEXT_QUOTES)
                        $vars['simple_variables'][] = mb_substr(array_search($v, $vars['matching_variables']), 0, mb_strlen(array_search($v, $vars['matching_variables'])) - mb_strlen(DocumentsGenerateModel::DATE_AS_TEXT_QUOTES));

                }    
                
            }
            
            $vars['simple_variables'] = array_unique($vars['simple_variables']);
        }
            
        if(count($vars['sm_1st_variables']))
            foreach($vars['sm_1st_variables'] as $k=>$v) 
                if(in_array($v, $vars['matching_variables']))
                    $vars['sm_1st_variables'][$k] = array_search($v, $vars['matching_variables']);
            
            
        if(count($vars['sm_other_variables']))
            foreach($vars['sm_other_variables'] as $k=>$v) 
                if(in_array($v, $vars['matching_variables']))
                    $vars['sm_other_variables'][$k] = array_search($v, $vars['matching_variables']);

        return array($p_params, $vars);
    }
    
    
    /**
     *  Пост обработка переменной из CRM
     */ 
    private function postProcessingVar($var, $type){
    
        
        if($type == DocumentsGenerateModel::NUMBER_AS_TEXT)
            $var = $this->nmbToText($var);
        
        if($type == DocumentsGenerateModel::SUM_AS_TEXT)
            $var = $this->sumToText($var);
        
        if($type == DocumentsGenerateModel::FLOAT_AS_TEXT)
            $var = $this->floatToText($var);
        
        if($type == DocumentsGenerateModel::FRACTION_AS_TEXT)
            $var = $this->fractionToText($var);
        
        if($type == DocumentsGenerateModel::DATE_AS_TEXT)
            $var = $this->dateToText($var);
        
        if($type == DocumentsGenerateModel::DATE_AS_FULLTEXT)
            $var = $this->dateToText($var, true);
        
        if($type == DocumentsGenerateModel::DATE_AS_TEXT_QUOTES)
            $var = $this->dateToText($var, false, true);
        
        return $var;
    
    }
    
    
    /**
     *  Начало текста в верхнем регистре
     */ 
    private function usr_ucfirst($str){
        
        return mb_convert_case($str, MB_CASE_TITLE, "utf-8");
        
    }
    
    
    /**
     *  Число прописью
     */ 
    public function nmbToText($var){
        
        $var = $this->num2str(floor($var));
        
        return $var;
        
    }
    
    
    /**
     *  Сумма прописью
     */ 
    public function sumToText($var){
        
        $var = $this->toUcFirst($this->num2str($var, true));
        
        return $var;
    
    }
    
   
    /**
     *  Сумма прописью с выводом целых и сотых
     */ 
    public function floatToText($var){
        
        $show_float = true;
        
        //проверям на дробную часть, если ее нет, то выводим целую часть в именительном падеже в мужском роде
        $v = explode('.', $var);
        
        if(count($v)==1) {
            $show_float = false;
        }else {
            if((int)$v[1]==0)
                $show_float = false;
        }
        
        $var = ($show_float) ? $this->float2str($var) : $this->num2str(floor($var));
        
        return $var;
        
    }
    
    
    /**
     *  Вывод дробей прописью
     */ 
    public function fractionToText($var){
        
        $x = explode('/', $var);
        
        //числитель
        $numerator = (int)$x[0];
        
        //знаменатель
        $denominator = (isset($x[1])) ? (int)$x[1] : false;
        
        /*
        for($i = 0; $i <= 32; $i++) {
            for($y = 0; $y <= 32; $y++) {
                
                $var1 = $this->num2str($i, false, 1);
                $var2 = $this->num2str($y, false, false, ($i%10==1) ? 0 : 1);
                
                echo "$i/$y = $var1 $var2\r\n";
            }
        }   
        die();
        */
        
        $var1 = $this->num2str($numerator, false, 1);
        $var2 = ($denominator) ? $this->num2str($denominator, false, false, ($numerator%10==1) ? 0 : 1) : '';
            
        return $var1 . ' ' . $var2;
        
    }
    
    
    /**
     * Возвращает сумму прописью
     * @author runcore
     * @uses morph(...)
     */
    private function num2str($num, $currency=false, $force_gender=false, $fraction=false, $case2=false) {
        $nul=$this->zero;
        $ten=$this->nmb_ten;
        $a20=$this->a20;
        $tens=$this->tens;
        
        if($fraction!==false){
            $force_gender = $fraction;
            $ten=$this->nmb_ten_fraction;
            
            $a20 = ($fraction) ? $this->a20_fraction_mod1
                               : $this->a20_fraction_mod2;
             
        }
        
        if($case2!==false) {
            $ten = $this->nmb_ten_case2;
            $a20 = $this->a20_case2;
        }
        
        $hundred=$this->hundred;
        $unit=$this->units;
        //
        list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub)>0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit)-$uk-1; // unit key
                $gender = $unit[$uk][3];
                
                if($force_gender!==false)
                    $gender = $force_gender;
                
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) {
                    # 20-99
                    
                    if($fraction!==false){ 
                        if($i3==0) {
                            $tens = ($fraction) ? $this->tens_fraction_mod1
                                                : $this->tens_fraction_mod2;
                        }
                    }
                    
                    if($case2!==false) {
                        if($i3==0) {
                            $tens = $this->tens_case2;
                        }
                    }
                    
                    $out[]= $tens[$i2].' '.$ten[$gender][$i3]; 
                } 
                else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) $out[]= $this->morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            } //foreach
        }
        else $out[] = $nul;
        
        if($currency) {
            $out[] = $this->morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
            if((int)$kop>0)
                $out[] = $kop.' '.$this->morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
        }
        return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
    }
    
    
    private function float2str($num, $first=true) {
        $nul=$this->zero;
        $ten=$this->nmb_ten_mod1;
        $a20=$this->a20;
        $tens=$this->tens;
        $hundred=$this->hundred;
        $unit=$this->units_mod1;
        //
        list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub)>0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit)-$uk-1; // unit key
                $gender = $unit[$uk][3];
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
                else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) $out[]= $this->morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            } //foreach
        }
        else $out[] = $nul;
        
        if($first && (int)$kop>0) {
            $out[] = $this->morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // целые
            $kop_x = $this->float2str($kop, false);
            $out[] = $kop_x.' '.$this->morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // сотые
        }
        return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
    }
    
    
   /**
     *  Переводим первую букву в верхний регистр
     */ 
    private function toUcFirst($var) {
        $array_str = explode(' ', $var);
        if(!empty($array_str[0]))
            $array_str[0] = $this->usr_ucfirst($array_str[0]);
        
        return implode(' ', $array_str);
    }
    
    
    /**
     * Склоняем словоформу
     * @ author runcore
     */
    private function morph($n, $f1, $f2, $f5) {
        $n = abs(intval($n)) % 100;
        if ($n>10 && $n<20) return $f5;
        $n = $n % 10;
        if ($n>1 && $n<5) return $f2;
        if ($n==1) return $f1;
        return $f5;
    }
    
    
    /**
     *  Дата прописью
     */ 
    public function dateToText($var, $full=false, $quotes=false){
        
        $x = explode(" ", $var);
        
        if(isset($x[0])) {
            
            $date = explode("-", $x[0]);

            if(isset($date[1])) {
                switch ($date[1]){
                    case 1: $m='января'; break;
                    case 2: $m='февраля'; break;
                    case 3: $m='марта'; break;
                    case 4: $m='апреля'; break;
                    case 5: $m='мая'; break;
                    case 6: $m='июня'; break;
                    case 7: $m='июля'; break;
                    case 8: $m='августа'; break;
                    case 9: $m='сентября'; break;
                    case 10: $m='октября'; break;
                    case 11: $m='ноября'; break;
                    case 12: $m='декабря'; break;
                }
                
                $d = (int)$date[2];
                if($quotes)
                    $d = '«'.$d.'»';
                
                $var = $d.'&nbsp;'.$m.'&nbsp;'.$date[0];  
            }else {
                
                $date = explode(".", $x[0]);
                
                switch ($date[1]){
                    case 1: $m='января'; break;
                    case 2: $m='февраля'; break;
                    case 3: $m='марта'; break;
                    case 4: $m='апреля'; break;
                    case 5: $m='мая'; break;
                    case 6: $m='июня'; break;
                    case 7: $m='июля'; break;
                    case 8: $m='августа'; break;
                    case 9: $m='сентября'; break;
                    case 10: $m='октября'; break;
                    case 11: $m='ноября'; break;
                    case 12: $m='декабря'; break;
                }
                
                $d = (int)$date[0];
                if($quotes)
                    $d = '«'.$d.'»';
                
                $var = $d.'&nbsp;'.$m.'&nbsp;'.$date[2];  
            }

        }
        
        
        if($full) {
            
            //полная запись числа, в родительном падеже
            
            $x = explode("&nbsp;", $var);

            $d = $this->toUcFirst($this->num2str($x[0], false, false, false, true));

            /*
            for($i = 1; $i <= 32; $i++)
                echo $this->num2strRod($i, true) . "\r\n";
            die();
            */
            $m = $x[1];
            $y = $this->num2str($x[2], false, false, false, true);
            
            $var = $d.'&nbsp;'.$m.'&nbsp;'.$y.'&nbsp;года';  
            
        }
        
        return $var;
        
    }
        
    
    /**
     *  Получаем числовое значение по дроби
     */ 
    public function calcFromFraction($amount, $fraction){
        
        $x = explode('/', $fraction);
        
        //числитель
        $numerator = (int)$x[0];
        
        //знаменатель
        $denominator = (isset($x[1])) ? (int)$x[1] : false;
        
        $result = $amount * $numerator;
        
        if($denominator)
            $result = $result / $denominator;

        return sprintf("%.2f", ($result*100)/100);
    }
    
    
    /**
     *  Получаем единичную запись из массива
     * @data 
     */ 
    public function getSingleData($data){

        if(count($data)>1) {
            
            //записей несколько, ищем через поле is_main. если поле не найдено, показываем первую
            if(array_key_exists(DocumentsGenerateModel::FIELD_NAME_MARK, $data[0])) {
                
                //поле найдено, ищем запись по этому полю
                foreach($data as $v) {
                    
                    if($v[DocumentsGenerateModel::FIELD_NAME_MARK] == DocumentsGenerateModel::MARK_TRUE)
                        return $v;
                    
                }
                
                //запись так и не была найдена, показываем первую
                return $data[0];
                
            }else 
                return $data[0];
            
        }else 
            return $data[0];
        
    }

    
    /**
     *   Рекурсивно получаем СМ записи из схемы
     * @extensionCopy из какого модуля запускается генерация документов
     * @extensionCopyId экземпляр того модуля
     * @sm СМ связи
     */ 
    public static function collectSM($schema, $copy_id, $extensionCopyId, $result, $only=false, $skip_docs=true) {
        
        foreach($schema as $value){
            if(isset($value['type'])) {
                if($value['type']=='sub_module') {
                 
                    //СМ данные, при этом пропускаем СМ модуля Документы
                    $next_iteration = true;
                    if($skip_docs)
                        if($value['params']['relate_module_copy_id']==\ExtensionCopyModel::MODULE_DOCUMENTS)
                            $next_iteration = false;
                        
                    if($next_iteration) { 
                 
                        $extension_copy_relate = ExtensionCopyModel::model()->findByPk($value['params']['relate_module_copy_id']);

                        //проверка, показываем ли текущий сабмодуль, или нет
                        $showThis = true;
                        
                        if($only)
                            $showThis = (\DocumentsGenerateModel::deleteSpaces($extension_copy_relate->attributes['title'])==$only) ? true : false;

                        if($showThis) {
                            
                            $table_module_relate = ModuleTablesModel::model()->find(array(
                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                'params' => array(
                                        ':copy_id'=>$copy_id,
                                        ':relate_copy_id'=>$extension_copy_relate->copy_id,
                                      )
                                )
                            ); 


                            $extension_copy_data = array();
                            $submodule_id_list = \DocumentsGenerateModel::getSubModuleIdList($extension_copy_relate, $table_module_relate, $extensionCopyId);

                            if(!empty($submodule_id_list)){
                                
                                $data_model = DataModel::getInstance()
                                                        ->setExtensionCopy($extension_copy_relate)
                                                        ->setFromModuleTables();
                                //responsible
                                if($extension_copy_relate->isResponsible())
                                    $data_model->setFromResponsible();
                                //participant
                                if($extension_copy_relate->isParticipant())
                                    $data_model->setFromParticipant();
                                                                    
                                $data_model                                                
                                        ->setWhere(array(
                                                    'in',
                                                    $extension_copy_relate->getTableName() . '.' . $extension_copy_relate->prefix_name. '_id',
                                                    $submodule_id_list
                                                  ))
                                        ->setCollectingSelect() 
                                        ->setGroup();

                                /*
                                if(Yii::app()->controller->module->dataIfParticipant($extension_copy_relate) && ($extension_copy_relate->isParticipant() || $extension_copy_relate->isResponsible())){
                                    $data_model->setOtherPartisipantAllowed();
                                }

                                if(Yii::app()->controller->module->dataIfParticipant($extension_copy_relate) == false && ($extension_copy_relate->isParticipant() || $extension_copy_relate->isResponsible())){
                                    $data_model->setDataBasedParentModule($extension_copy_relate->copy_id);
                                }
                                */

                                $extension_copy_data = $data_model->findAll();
                            }
                                
                            if(count($extension_copy_data)>0)
                                $result [$extension_copy_relate->attributes['title']]= array($value['params']['relate_module_copy_id'], '1', $extension_copy_data);
                            
                        }
                        
                    }    
                }
                
                if(in_array($value['type'], array('block', 'block_panel', 'block_panel_contact', 'block_button', 'panel', 'block_field_type', 'block_field_type_contact'))) {
                    
                    //в случае определенных типов, запускаем дальше рекурсию
                    if(!empty($value))
                        if(count($value) > 0)
                            $result = \DocumentsGenerateModel::collectSM($value['elements'], $copy_id, $extensionCopyId, $result, $only, $skip_docs);
                    
                }    
                
            }
            
        }
        
        return $result;
        
    }
    
    
    private static function getSubModuleIdList($extension_copy_relate, $table_module_relate, $extensionCopyId){
        $result = array();
        if(empty($extensionCopyId)) return $result;
        $submodule_id_list = DataModel::getInstance()
            ->setFrom('{{' . $table_module_relate->table_name . '}}')
            ->setWhere(array('in', $table_module_relate->parent_field_name , $extensionCopyId))
            ->findAll();
        if(!empty($submodule_id_list)){
            foreach($submodule_id_list as $value)
                $result[] = $value[$table_module_relate->relate_field_name];
        }

        return $result;
    }
    
    
    /**
     *   Загружаем связанные модули, по рекурсии 
     */ 
    public function getRelate($extensionCopy, $extensionCopyId, $params=array(), $level = 1, $prefix='', $onlyRelate = false){
        
        if($level <= $this->maxIterationLevel) {
        
            $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($extensionCopy);
            $field_params_list = $this->getSchemaFieldsParams($extension_copy);  
 
            $data = $this->getData($extension_copy, $extensionCopyId);        

            $data_tmp = DataValueModel::getInstance()
                            ->setSchemaFields($field_params_list)
                            ->setExtensionCopy($extension_copy)
                            ->prepareData($data)
                            ->getProcessedData()// без обьединения значений
                            ->getData();
            
            if(count($data_tmp)>0){
                foreach($data_tmp as $k => $v) {
                    foreach($v as $k2 =>$v2) {
                        if($v2['value']) {

                            //проверяем связь, можно ли ее добавлять
                            if($level > 1) {
                                    
                                $addParams = ($this->findCRM) ? false : true;  
                                  
                                if(is_array($this->findCRM))
                                    if(in_array($this->deleteSpaces($prefix . $extension_copy->title), $this->findCRM))
                                        $addParams = true;

                            }else
                                $addParams = true;
                              
                            if($onlyRelate) 
                                $addParams = false;
                              
                            if($addParams) {
                              
                                $title = ($level == 1) ?  '' : $extension_copy->title . ':';
                                $params[$prefix . $title . $k2] = $v2['value'];
                              
                            }
                        }
                    }
                }
            }
                
           $findRelate = true;
           
           if($findRelate) {
                
               //ищем связи СДМ текущего модуля
               $relates = ModuleTablesModel::model()->findAllByAttributes(array('copy_id'=>$extensionCopy, 'type'=>'relate_module_one'));

               if(count($relates)>0)
                    foreach($relates as $relate) {
                        
                        if(isset($data[0][$relate['table_name'].'_'.$relate['relate_field_name']])) {
                            $value = $data[0][$relate['table_name'].'_'.$relate['relate_field_name']];
                            
                            $title = ($level == 1) ?  '' :  $prefix . $extension_copy->title . ':';
                            $params = $this->getRelate($relate['relate_copy_id'], $value, $params, $level + 1, $title);
                          
                        }
                    }
                
           }
           
           /*
           //ищем СМ связи для текущего модуля
           $relatesSM = ModuleTablesModel::model()->findAll("`copy_id` = $extensionCopy AND `type` = 'relate_module_many' AND `relate_copy_id` <> '$genModuleId' ");
                   
           if(count($relatesSM)>0)
                foreach($relatesSM as $relateSM) {
                    
                    if(isset($data[1][$relateSM['table_name'].'_'.$relateSM['relate_field_name']])) {
                     
                        //получаем первое значение из СМ записей
                        $value = $data[1][$relateSM['table_name'].'_'.$relateSM['relate_field_name']];
                        
                        $params = $this->getRelate($relateSM['relate_copy_id'], $value, $genModuleId, $params, $level + 1, $prefix . $extension_copy->title . ':');
                        
                    }
                           
                } 
            */
                     
            //print_r($params);
           
            }
                   
        return $params;
        
    }
    
    
    private function setExtensionCopy($extension_copy, $extension_copy_id){
        $this->extension_copy_id  = $extension_copy_id;
        $this->extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($extension_copy);
        $this->extension_copy_schema = $this->extension_copy->getSchema();
    }
  
    
   /**
    *   Возвращает данные модуля 
    */ 
    public static function getData($extension_copy, $extension_copy_id=0, $find_row=false, $where=false){

  
        //*********************
        // *** get data
        $data_model = new DataModel();


        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();
            
        if($where)    
            $data_model->setWhere($where);
        
       if($extension_copy_id!=0) 
            $data_model->setWhere(array(
                'in',
                $extension_copy->getTableName() . '.' . $extension_copy->prefix_name. '_id',
                $extension_copy_id
            ));

        //responsible
        if($extension_copy->isResponsible())
            $data_model->setFromResponsible();
            
        //participant
        if($extension_copy->isParticipant())
            $data_model->setFromParticipant();
         
         
        //order
        Sorting::getInstance()->setParamsFromUrl();
        $data_model->setOrder($data_model->getOrderFromSortingParams());

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup();
        
        //parent module
        if(!empty($only_id)) $data_model->setParentModule($only_id);            
            
        //participant only
        /*
        if(Yii::app()->controller->module->dataIfParticipant() && ($extension_copy->isParticipant() || $extension_copy->isResponsible())){
            $data_model->setOtherPartisipantAllowed();
        }

        if(Yii::app()->controller->module->dataIfParticipant() == false && ($extension_copy->isParticipant() || $extension_copy->isResponsible())){
            $data_model->setDataBasedParentModule($extension_copy->copy_id);
        }
        */

        return ($find_row) ? $data_model->findRow() : $data_model->findAll();
    }
    
    
    /**
     * Удаление пробелов в строке
     */
    public static function deleteSpaces($text){
    
        return str_replace(" ","",$text);
        
    }
    
    
   /**
    *   Разделяем название переменной с названием поля
    *  (отбрасываем последнюю часть после ":" - это название поля)
    */
    public function getSeparateSM($text){
        
        $t = substr($text, 4, strlen($text));
        $x = explode(":", $t);
        $last = array_pop($x);
        
        return array(implode(":", $x), $last);
   
    }    
    
    
   /**
    *   Разделяем СМ записи на первый уровень и остальные
    */
    public function separateSMFirstLevel($sm, $gen_module_id){
        
        $first_lvl = array();
        $other_lvl = array();
        
        $doc_title = \ExtensionCopyModel::model()->modulesActive()->findByPk($gen_module_id)->attributes['title'];
        
        foreach($sm as $v) {
            
            $x = explode(':', $v);
            
            if(count($x)>1) {
                
                //больше чем первый уровень, допустимо для модуля Документы, но только для второго уровня
                if((count($x)==2) && $x[0] == $doc_title) {
                    $first_lvl []= $v;
                }else {
                    $other_lvl []= $v;
                }
                          
            }else
                $first_lvl[] = $v;
            
        }
                
        return array($first_lvl, $other_lvl);
    }
    
    
    /**
     * getSchemaFieldsParams
     */
    private function getSchemaFieldsParams($extension_copy){
        
        $extension_copy->getSchema();
        $schema = $extension_copy->getSchemaParse();
        
        $result = array();
        if(empty($schema) || !isset($schema['elements'])) return $this;
        foreach($schema['elements'] as $element){
            if(isset($element['field'])){
                if($element['field']['params']['type'] == 'activity') continue;
                if($element['field']['params']['type'] == 'relate') continue;
                $result[] = $element['field'];
            }
        }
        return $result;
    }
    
    
    /**
     * Получаем СДМ из определенной extension_copy
     * @id - id записи
     * @titles - массив названий искомых СДМ модуля
     */
    private function getSDMRelatesDataFromExtensionCopy($extension_copy, $id, $titles){
        
        $result = array();
        
        //ищем связи СДМ текущего модуля
        $relates = ModuleTablesModel::model()->findAllByAttributes(array('copy_id'=>$extension_copy->copy_id, 'type'=>'relate_module_one'));

        if(count($relates)>0){
            foreach($relates as $relate) {
                if(isset($relate['relate_copy_id'])){
                    $extension_copy_relate = \ExtensionCopyModel::model()->modulesActive()->findByPk($relate['relate_copy_id']);
                    if($extension_copy_relate !== null) {
                        //модуль найден, сверяем название
                        if(in_array($this->deleteSpaces($extension_copy_relate->title), $titles)){
                            //данные данного модуля нас интересуют
                            //получаем id через связь
                            $lnk = DataModel::getInstance()
                                ->setFrom('{{' . $relate->table_name . '}}')
                                ->setWhere($extension_copy->prefix_name . '_id=:id', array(':id' => $id))
                                ->FindRow();
                            if(!empty($lnk)){
                                $target_id = $extension_copy_relate->prefix_name . '_id';
                                $result[$this->deleteSpaces($extension_copy_relate->title)] = $this->getData($extension_copy_relate, 0, true, "$target_id='$lnk[$target_id]'");
                            }
                        }
                    }
                }
            }
        }
        
        return $result;
        
    }
    

       
    
}


