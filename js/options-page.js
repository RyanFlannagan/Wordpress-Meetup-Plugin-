jQuery(document).ready(function($) {
    
    /**
     * Group deletion confirmation
     */
    $('#groups-table tbody td:last-child a').click(function(e) {
        groupName = $(this).parent().parent().find('td:first-child').text();
        if (confirm("Are you sure you want to remove " + groupName + "?")) {
            return true;
        } else return false;
    });
    
    /**
     * Color picker for groups
     */
    $('<div class="colorpicker"></div>').insertAfter('.color').hide();
    
    $('.colorpicker').each(function(index) {
        $(this).farbtastic($(this).prev());
    });
    
    $('.color').focus(function() {
        $(this).next().show();
    }).blur(function() {
        $(this).next().hide();
    });
    
    
});