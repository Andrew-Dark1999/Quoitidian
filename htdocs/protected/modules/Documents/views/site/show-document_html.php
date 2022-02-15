<script src="/static/js/ckeditor/ckeditor.js"></script>
<form action="" method="POST">
<div class="buttons-section" style="margin: 20px; display: none; align-items: center; justify-content: center; height: 100%;">
    <button type="submit" class="btn btn-primary edit_view_document_btn-save"><?php echo Yii::t('base', 'Save')?></button>
    <button type="button" class="btn btn-default edit_view_document_btn-print"><?php echo Yii::t('base', 'Print')?></button>
</div>
<div class="buttons-section" style="margin: 20px; display: flex; align-items: center; justify-content: center; height: 100%;">
    <div id="document_html_wrapper" style="margin-top: -9999px;">
        <textarea style="margin-top: 20px; width:90%;" rows="35" id="text" name="text"><?php echo $text; ?></textarea>
    </div>
    <script>
        var config = {
            toolbarGroups : [
                { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
                { name: 'links' },
                { name: 'insert' },
                { name: 'forms' },
                //{ name: 'tools' },
                { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
                '/',
                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
                { name: 'styles' },
                { name: 'colors' }
            ],
            fullPage : true,
            extraPlugins : 'print,onchange,save,maximize',
            baseHref : '/', 
            on: {
                'instanceReady': function (evt) { 
                    evt.editor.execCommand('maximize'); 
                }
            },
        }
        CKEDITOR.replace('text', config);
    </script>
</div>

<script>
var iCK = CKEDITOR.instances.text;
  
/*  
iCK.on('change', function(e) {
    $(window).bind('beforeunload', function(e){ 
        e = e || window.event;
        // For IE and Firefox prior to version 4
        if (e) {
            e.returnValue = '<?php echo Yii::t('DocumentsModule.messages', 'Are you sure you want to leave this page?')?>';
        }
        // For Safari
        return '<?php echo Yii::t('DocumentsModule.messages', 'Are you sure you want to leave this page?')?>';
    });
});
*/
/*
$( ".edit_view_document_btn-save" ).click(function() {
    window.onbeforeunload = null;
    
     params = {};
     params['upload_id'] = '<?php echo $upload_id;?>';
     params['data'] = iCK.getData();
     params['type'] = 'html';
    
    //сохраняем документ
    $.ajax({
        url: '/module/document/SaveDocumentData/' + <?php echo $copy_id;?>,
        data : params,
        dataType: "json",
        type: "POST",
        success: function(data){
            alert(data.message);
            //Message.show(data.message, false); 
        },
        error: function(){
            Message.show([{'type':'error', 'message':Global.urls.url_ajax_error}], true);
        },
    });

});
*/

$( ".edit_view_document_btn-print" ).click(function() {
    iCK.execCommand( 'print' );
});

</script>
<input type="hidden" name="type" value="html">
<input type="hidden" name="upload_id" value="<?php echo $upload_id;?>">
</form>