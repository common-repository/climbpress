<?php

get_header();
?>
<climbpress-route
        grading-systems="<?= esc_attr( json_encode( climbpress_get_grading_systems() ) ) ?>"
        route="<?= esc_attr(json_encode(climbpress_get_voting_page_route())); ?>"
        meta-structure="<?= esc_attr( json_encode( climbpress_get_route_meta_structure() ) ) ?>"
        expanded="true"
></climbpress-route>
<?php

get_footer();
