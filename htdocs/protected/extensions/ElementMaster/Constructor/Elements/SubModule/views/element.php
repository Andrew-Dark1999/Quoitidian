<!-- SubModule -->
<div class="panel-body element_sub_mudule element" data-type="sub_module" data-relate_module_copy_id="<?php echo $schema['params']['relate_module_copy_id']; ?>" data-relate_module_template="<?php echo (array_key_exists('relate_module_template', $schema['params']) ? (integer)$schema['params']['relate_module_template'] : '0'); ?>">
    <span class="element options" data-type="sub_module_params">
    <?php
        $model = ExtensionCopyModel::model()->findByPk($schema['params']['relate_module_copy_id'])->setAddDateCreateEntity(false);
        $sub_module_schema_parse = $model->getSchemaParse();

        $params = SchemaConcatFields::getInstance()
            ->setSchema($sub_module_schema_parse['elements'])
            ->setWithoutFieldsForSubModuleGroup((array_key_exists('relate_module_template', $schema['params']) ? (integer)$schema['params']['relate_module_template'] : false))
            ->parsing()
            ->primaryOnFirstPlace()
            ->prepareWithConcatName()
            ->getResult();

        if(isset($schema['type']) && $schema['type'] == 'sub_module'){
            if(empty($params['header'])){
                echo Yii::t('messages', 'Absent fields to display');
            } else {
				$params['header'][0]['title'] = "Título";
				$params['header'][1]['title'] = "Responsable";
				$params['header'][2]['title'] = "Proyecto";
				$params['header'][3]['title'] = "Tarea";
				$params['header'][4]['title'] = "Fecha";
				$params['header'][5]['title'] = "Horas";
			
                foreach($params['header'] as $aField){
					
					
                    $field_name = $aField["name"];
                    $field_title = $aField["title"];
                    ?>
                    <div class="checkbox">
                        <label><input type="checkbox"<?php if(empty($schema['params']['values']) || in_array($field_name, $schema['params']['values'])) echo 'checked="checked"'; ?>value="<?php echo $field_name ?>" /><?php echo $field_title; ?></label>
                    </div>
                <?php
                }
            }
        }
    ?>
    </span>
</div>
<!-- SubModule END -->
