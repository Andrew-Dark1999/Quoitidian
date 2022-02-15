<?php
$this->beginContent('//layouts/default'); ?>

<span id="top_menu_params" style="display: none;" ><?php echo json_encode($this->data['menu_main']); ?></span>
<!--header start-->
<header class="header fixed-top">
	<div class="navbar-header overflowHidden">
		<!--logo-->
		<div class="brand">
    		<a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(7))->prepare()->getKey(); ?>" href="javascript:void(0)" >
                <img src="<?php echo FileOperations::getRedefinedFile('logo_black_v1.png', 'images/wizz'); ?>">
            </a>
		</div>
		<!--logo end-->
        <?php echo $this->renderBlock('main-top-module-menu'); ?>
        <?php echo $this->renderBlock('main-top-messages'); ?>
        <?php echo $this->renderBlock('main-top-profile-menu'); ?>
	</div>
<div style="text-align: right;">
    <?php
    /*
        echo $this->renderBlock('language', array(
                                                'language' => LanguageModel::model()->scoreActive()->findAll(),
                                                'language_value' => Yii::app()->getLanguage(),
                                             ),
                                             true);*/
    ?>
</div>
</header>
<!--header end-->

<!--main content start-->
<section id="main-content" data-type="main-content">
    <section class="wrapper">
    <div class="row settings-module" >
        <?php
            echo $this->renderBlock('main-left-module-menu');
        ?>
        <div class="settings-section col-xs-<?php if($this->left_menu === true) echo 9; else echo 12; ?>">
            <div id="content_container"><?php echo $content; ?></div>
        </div>
    </div>
    </section>
</section>
<!--main content end-->

<?php
    echo $this->renderBlock('quick-view');
?>
<!--right sidebar end-->

<?php
    $this->endContent();
?>

<script>
    $(document).ready(function(){
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        instanceGlobal.contentReload.setVarsFromPage(content_vars, null, null);
    });
</script>
