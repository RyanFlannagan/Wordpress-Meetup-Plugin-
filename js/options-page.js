jQuery(document).ready(function($) {
    $('#groups-table tbody td:last-child a').click(function(e) {
        groupName = $(this).parent().parent().find('td:first-child').text();
        if (confirm("Are you sure you want to remove " + groupName + "?")) {
            return true;
        } else return false;
    });
});