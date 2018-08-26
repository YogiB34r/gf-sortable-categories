jQuery(document).ready(function ($) {

    /********************************************/
    /* AJAX SAVE FORM */
    /********************************************/
    $('#theme-options-form').submit(function () {
        $(this).ajaxSubmit({
            onLoading: $('.loader').show(),
            success: function () {
                $('.loader').hide();
                $('#save-result').fadeIn();
                setTimeout(function () {
                    $('#save-result').fadeOut('fast');
                }, 2000);
            },
            timeout: 5000
        });
        return false;
    });
    /********************************************/
    /* SORTABLE FILTER FIELDS */
    /********************************************/
    $('.filter-fields-list').sortable({
        handle: ".first-level-cat",
        axis: 'y',
        cursor: 'move'
    });
    $('.parent-cat-children').sortable({
        handle: ".second-level-cat",
        axis: 'y',
        cursor: 'move',
    });
    $('.child-cat-children').sortable({
        handle: ".third-level-cat",
        axis: 'y',
        cursor: 'move',
    });
});