<?php
    $params = $this->model->getBlockAvatarParams();
?>
<button data-toggle="dropdown"
        data-type="drop_down_button"
        <?php echo $params['btn_attr']; ?>
><?php
    if($params['btn_html']){
        echo $params['btn_html'];
    } else {
        ?><img src="/static/images/button/plus.png"><?php
    }
?></button>
