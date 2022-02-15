<?php
/**
* ConstructorChangeElementModel 
*/
namespace Reports\models;

class ConstructorChangeElementModel {
    
    private $_params;
    public $_result;
    

    
    public static function getInstance(){
        return new self(); 
    } 
    
    public function setParams($params){
        $this->_params = $params;
        return $this;
    }
    
    public function getResult($json = false){
        if($json) return json_encode($this->_result);
        
        return $this->_result;
    }
    


    /**
     * prepareElementData
     */
    public function prepareElementData($element_changed){
        if(empty($element_changed)) return $this;
        
        switch($element_changed){
            case 'indicator_block' :
                $this->addIndicatorBlock();
                break;
                
            case 'indicator_indicator' : 
                $this->getIndicatorPanel();
                break;
                
            case 'indicator_add' : 
                $this->getIndicatorSetting();
                $this->getIndicatorPanel();
                break;
            
            case 'graph' :
                $this->getGraphSettingIndicator();
                $this->getGraphElement(); 
                break;
            
            case 'data_analysis_param_module' :
                $this->getIndicatorSetting();
                $this->getIndicatorPanel();
                $this->getGraphSettingIndicator();
                $this->getGraphElement(); 
                $this->addDataAnalysisParamSettings();
                $this->addDataAnalysisInsicatorModuleParams();
                $this->addFilter();
                $this->addFilterModule();
                break;

            case 'data_analysis_indicator_module' :
                $this->getIndicatorSetting();
                $this->getIndicatorPanel();
                $this->getGraphSettingIndicator();
                $this->getGraphElement(); 
                $this->addFilter();
                $this->addFilterModule();
                $this->addDataAnalysisInsicatorSettings();
                break;

            case 'data_analysis_panel_settings' :
                $this->getIndicatorSetting();
                $this->getIndicatorPanel();
                $this->getGraphSettingIndicator();
                $this->addFilter();
                $this->addFilterModule();
                break;

            case 'data_analysis_indicator_settings' : //
                $this->getIndicatorSetting();
                $this->getIndicatorPanel();
                $this->getGraphSettingIndicator();
                //$this->getGraphElement();
                $this->addDataAnalysisInsicatorSettings();
                break;

            case 'filter_module_params' :
                $this->addFilterFields();
                break;

            case 'update_output_elements' :
                $this->getIndicatorPanels();
                $this->getGraphElement();
                break;
        }
        
        return $this;
    }

    /**
     * 
     */        
    private function addDataAnalysisParamSettings(){
        $this->_result['data_analysis_param_settings'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('data_analysis_param_settings', $this->_params, true);

        return $this;        
    }
    


    /**
     * 
     */        
    private function addDataAnalysisInsicatorModuleParams(){
        $this->_result['data_analysis_indicator_module_params'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('data_analysis_indicator_module_params', $this->_params, true);

        return $this;        
    }
    


    /**
     * 
     */        
    private function addDataAnalysisInsicatorSettings(){
        $this->_result['data_analysis_indicator_settings'] = \Reports\extensions\ElementMaster\Schema::getInstance()->generateConstructorSchema('data_analysis_indicator_settings', $this->_params, true);

        return $this;        
    }
    


    /**
     * 
     */        
    private function addFilter(){
        $this->_result['filter_base'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('filter', ($this->_params + array('remove' => false, 'drag_marker' => false)) , true);
        $this->_result['filter'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('filter', $this->_params, true);
        
        return $this;        
    }
    


    /**
     * 
     */        
    private function addFilterModule(){
        $this->_result['filter_module'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('filter_module', ($this->_params) , true);

        return $this;        
    }

    

    /**
     * 
     */        
    private function addFilterFields(){
        $this->_result['filter_field_params'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('filter_field_params', ($this->_params) , true);

        return $this;        
    }
        


    /**
     * 
     */        
    private function addIndicatorBlock(){
        $this->_result['indicator_block'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('indicator_block', ($this->_params) , true);

        return $this;        
    }



    /**
     * 
     */        
    private function getIndicatorPanel(){
        $this->_result['indicator_panel'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('indicator_panel', ($this->_params) , true);

        return $this;        
    }



    /**
     *
     */
    private function getIndicatorPanels(){
        $this->_result['indicator_panels'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('indicator_panels', ($this->_params) , true);

        return $this;
    }



    /**
     * 
     */        
    private function getIndicatorSetting(){
        $this->_result['indicator_setting_indicator'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('indicator_setting_indicator', ($this->_params) , true);

        return $this;        
    }


    /**
     * 
     */        
    private function getGraphSettingIndicator(){
        $this->_result['graph_setting_indicator'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('graph_setting_indicator', ($this->_params) , true);

        return $this;        
    }


    /**
     * 
     */        
    private function getGraphElement(){
        $this->_result['graph_element'] = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateConstructorSchema('graph_element', ($this->_params) , true);

        return $this;        
    }





}
