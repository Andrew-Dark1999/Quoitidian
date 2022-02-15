<?php if($params['params']['type'] == 'string'){ ?>
    <input type="text" class="element" autofocus data-name="<?php echo $params['params']['name'] ?>" value="<?php echo $field_data['value']; ?>" data-id="" />
<?php } ?>


<?php if($params['params']['type'] == 'select'){ ?>
    <input type="text" class="element" autofocus data-name="<?php echo $params['params']['name'] ?>" value="<?php echo $field_data['text']; ?>" data-id="<?php echo $field_data['value'] ?>" />
<?php } ?>