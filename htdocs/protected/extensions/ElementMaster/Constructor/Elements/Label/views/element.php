<!-- label -->
<?php 
switch ($schema['params']['title']) {
    case "Title":
        $schema['params']['title']= "Título";
        break;
    case "Summary":
        $schema['params']['title']= "Resumen";
        break;
    case "Customer":
        $schema['params']['title']= "Cliente";
        break;
	case "Project":
        $schema['params']['title']= "Proyecto";
        break;
	case "Phase":
        $schema['params']['title']= "Fase";
        break;
	case "Priority":
        $schema['params']['title']= "Prioridad";
        break;
	case "Related object":
        $schema['params']['title']= "Objeto relacionado";
        break;
}

?>
<input type="text" class="main-input form-control element_label element" data-type="label" placeholder="<?php echo Yii::t('base', 'Name'); ?>" value="<?php echo $schema['params']['title']?>">
<!-- label END  -->
