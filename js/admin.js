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
    $('.accordion-first-level').accordion({
        collapsible: true,
        header: ">h2",
        heightStyle: "content",
        active:false,
        icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
    });
    $('.accordion-second-level').accordion({
        collapsible: true,
        header: ">h4",
        heightStyle: "content",
        active:false,
        icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
    });
    $('.filter-fields-list').sortable({
        handle: "h2",
        axis: 'y',
        cursor: 'move',
        items: 'li',
        stop: function (event, ui) {
            // IE doesn't register the blur when sorting
            // so trigger focusout handlers to remove .ui-state-focus
            ui.item.children("h4").triggerHandler("focusout");

            // Refresh accordion to handle new order
            $(this).accordion("refresh");
        }
    });
    $('.parent-cat-children').sortable({
        handle: "h4",
        axis: 'y',
        cursor: 'move',
        items: 'li',
        stop: function (event, ui) {
            // IE doesn't register the blur when sorting
            // so trigger focusout handlers to remove .ui-state-focus
            ui.item.children("h4").triggerHandler("focusout");

            // Refresh accordion to handle new order
            $(this).accordion("refresh");
        }
    });
    $('.child-cat-children').sortable({
        handle: "h5",
        axis: 'y',
        cursor: 'move',
        items: 'li',
        stop: function (event, ui) {
            // IE doesn't register the blur when sorting
            // so trigger focusout handlers to remove .ui-state-focus
            ui.item.children("h5").triggerHandler("focusout");

            // Refresh accordion to handle new order
            $(this).accordion("refresh");
        }
    });
});