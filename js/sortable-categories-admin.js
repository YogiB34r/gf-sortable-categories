jQuery(document).ready(function ($) {

    /********************************************/
    /* AJAX SAVE FORM */
    /********************************************/
    //ako dodje do nekog problema ovo treba da se koristi valjda :P
    // $('#theme-options-form').submit(function () {
    //     $(this).ajaxSubmit({
    //         onLoading: $('.loader').show(),
    //         success: function () {
    //             $('.loader').hide();
    //             $('#save-result').fadeIn();
    //             setTimeout(function () {
    //                 $('#save-result').fadeOut('fast');
    //             }, 2000);
    //         },
    //         timeout: 5000
    //     });
    //     return false;
    // });
    
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
    });
    $('.parent-cat-children').sortable({
        handle: "h4",
        axis: 'y',
        cursor: 'move',
        items: 'li',
    });
    $('.child-cat-children').sortable({
        handle: "h5",
        axis: 'y',
        cursor: 'move',
        items: 'li',
    });
});