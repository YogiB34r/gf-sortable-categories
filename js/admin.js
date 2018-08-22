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
    $('.sortable-item').mouseover(function () {
        $(this).find('.sort-arrows').stop(true, true).show();
    });
    $('.sortable-item').mouseout(function () {
        $(this).find('.sort-arrows').stop(true, true).hide();
    });

    $('.filter-fields-list').sortable({
        axis: 'y',
        curosr: 'move'
    });
    $('.sortable-list').sortable({
        connectWith: $('.sortable-list')
    });

    $('.property-detail-items-list').sortable({
        axis: 'y',
        curosr: 'move'
    });

    $('.agent-detail-items-list').sortable({
        axis: 'y',
        curosr: 'move'
    });
});