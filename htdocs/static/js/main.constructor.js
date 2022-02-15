(function ($) {
    $(document).ready(function () {
        /*
         * Constructor sctipts
         */
        // sortable-list
            $( ".inputs-block" ).sortable({
              connectWith: ".inputs-block",
            });
         
        /*$( ".inputs block li" ).draggable({
             connectWith: ".inputs-block",
             connectToSortable: ".inputs-block",
             revert: "invalid"
         });*/
        // $( ".inputs block , .inputs block  li" ).disableSelection();
    

        $('.select').selectpicker({
            style: 'btn-white',
        });

        $('.todo-remove-contact').on('click', function() {
            $(this).closest('.contact-item').remove();
        })

        $('.inputs-block .todo-remove').on('click', function() {
            $(this).parent().remove();
        });

        // dropdown-menu fix
        $('.dropdown-menu > li input, .table-dropdown ul, .settings .dropdown-menu, .todo-remove').on('click', function(e){
            e.stopPropagation();
        })

        // edit fields
        $('.edit-dropdown .save-input').on('click', function() {
            var value = $(this).closest('ul').find('.form-control').attr('value');
            $(this).closest('.editable-block').find('.editable-field').text(value);
            $(this).closest('.edit-dropdown').removeClass('open');
        });


        // hide settings submenu
        $('.crm-dropdown > .dropdown-toggle').on('click', function() {
            $('.sub-menu').addClass('hide');
        });
        $(document).on('click', function(event) {
            if ($(event.target).closest('.sub-menu').length) return;
            $('.sub-menu').addClass('hide');
            event.stopPropagation();
        });

        // add or remove columns
        $('.select.xs li a').on('click', function() {
            var columns = parseInt($(this).find('.text').text());
            var col_class = '';
            var section = $(this).closest('.inputs-group').find('.columns-section');
            var section_items = $(this).closest('.inputs-group').find('.columns-section > div');
            switch (columns) {
                case 1:
                    col_class = '';
                    break;
                case 2:
                    col_class = 'col-2';
                    break;
                case 3:
                    col_class = 'col-3';
                    break;
                case 4:
                    col_class = 'col-4';
                    break;
                default:
                    col_class = 'col-5';
            }

            if (section_items.length > columns) {
                $(section_items).slice(columns).remove();
            } else if (section_items.length < columns) {
                for (var i = 0; i < columns - section_items.length; i++) {
                    $(section).append($(section_items).last().clone());
                }
            }

            $(section).removeClass('col-1 col-2 col-3 col-4 col-5').addClass(col_class);
        });

    });


})(jQuery);