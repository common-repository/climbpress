<?php

use ClimbPress\Model\IGradingSystem;
use ClimbPress\Model\Route;
use ClimbPress\Model\RouteMeta;

/**
 * @var array $i18n
 * @var RouteMeta[] $routeMetaStructure
 * @var Route[] $routes
 * @var IGradingSystem[] $gradingSystems
 */
?>
<climbpress-routes
        i18n="<?= esc_attr( json_encode( $i18n ) ) ?>"
        grading-systems="<?= esc_attr( json_encode( $gradingSystems ) ) ?>"
        routes="<?= esc_attr( json_encode( $routes ) ); ?>"
        route-meta-structure="<?= esc_attr( json_encode( $routeMetaStructure ) ) ?>"
        count="5"
>
    <button
            class="wp-element-button"
            slot="button"
    >
        Alle Routen anzeigen
    </button>
</climbpress-routes>
