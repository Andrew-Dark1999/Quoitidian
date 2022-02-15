<?php if($this->tag == 'span'){ ?>
<span <?php echo $attr; ?>  style="background-image: url(/<?php echo $src; ?>);" ><?php echo $this->getUserInitials(); ?></span>
<?php } ?>
<?php if($this->tag == 'img'){ ?>
<img <?php echo $attr; ?> src="/<?php echo $src; ?>">
<?php } ?>
