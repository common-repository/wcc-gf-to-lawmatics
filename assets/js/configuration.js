jQuery(document).ready(function($) {
    $('.wcc-table-js').tablesorter({
        headers: {
            2: {
                sorter: false
            },
            5: {
                sorter: false
            }
        }
    });
})