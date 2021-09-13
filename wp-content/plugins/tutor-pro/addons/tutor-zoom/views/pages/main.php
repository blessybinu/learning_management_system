<?php
$check_api = tutor_zoom_check_api_connection();
$currentSubPage = ($check_api) ? 'meetings' : 'set_api';
$currentName = ($check_api) ? __( 'All Meetings', 'tutor-pro' ) : __( 'Set API', 'tutor-pro' );
if ( $check_api ) {
    $subPages = array(
        'meetings' => __('Active Meetings', 'tutor-pro'),
        'expired' => __('Expired', 'tutor-pro'),
        'set_api' => __('Set API', 'tutor-pro'),
        'settings' => __('Settings', 'tutor-pro'),
        'help' => __('Help', 'tutor-pro'),
    );
} else {
    $subPages = array(
        'set_api' => __('Set API', 'tutor-pro'),
        'settings' => __('Settings', 'tutor-pro'),
        'help' => __('Help', 'tutor-pro'),
    );    
}


global $wp_query, $wp;
$paged = 1;
$url      = home_url( $wp->request );
$url_path = parse_url($url, PHP_URL_PATH);
$basename = pathinfo($url_path, PATHINFO_BASENAME);

if ( isset($_GET['paged']) && is_numeric($_GET['paged']) ) {
    $paged = $_GET['paged'];
} else {
    is_numeric( $basename ) ? $paged = $basename : '';
}
/**
 * Frontend zoom sub pages link and page url
 * 
 * only for frontend purpose
 * 
 * @since 1.9.3
 */
if ( $check_api ) {
    $frontend_subpages = array(
        'meetings'  => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom' ),
        'expired'   => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom/expired' ),
        'set_api'   => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom/set-api' ),
        'settings'  => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom/settings' ),
        'help'      => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom/help' ),
    );
} else {
    $frontend_subpages = array(
        'set_api'   => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom/set-api' ),
        'settings'  => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom/settings' ),
        'help'      => esc_url( tutor_utils()->tutor_dashboard_url().'/zoom/help' ),
    );  
}


$error_msg = '';
if (!empty($_GET['sub_page'])) {
    $currentSubPage = sanitize_text_field($_GET['sub_page']);
    if(!$check_api && ($currentSubPage == 'meetings' || $currentSubPage == 'settings')) {
        $error_msg = __('Please set your API Credentials. Without valid credentials, Zoom integration will not work', 'tutor-pro');
        $currentSubPage = 'set_api';
    }
    $currentName = isset($subPages[$currentSubPage]) ? $subPages[$currentSubPage] : '';
}
?>

<div class="wrap">
    <div class="report-main-wrap">
        <div class="tutor-report-left-menus">
            <div class="tutor-report-title">
                <?php if ( is_admin() ): ?>
                    <strong><?php _e('Zoom', 'tutor-pro'); ?></strong>
                    <span>/ <?php echo $currentName; ?></span>
                <?php else: ?>
                <h3> <?php _e( 'Zoom', 'tutor-pro'); ?> </h3>
                <?php endif;?>    
            </div>
            <div class="tutor-dashboard-inline-links tutor-report-menu">
                <ul>
                    <?php
                    /**
                     * Check if user on the admin side or frontend
                     * 
                     * change menu url as per view
                     * 
                     * @since 1.9.3
                     */
                        if ( is_admin() ) {
                            foreach ($subPages as $pageKey => $pageName) {
                                $activeClass = ($pageKey === $currentSubPage) ? 'active' : '';
                                echo "<li class='{$activeClass}'><a href='" . add_query_arg(array('page' => 'tutor_zoom', 'sub_page' => $pageKey), admin_url('admin.php')) . "'>{$pageName}</a></li>";
                            }
                        } else {
                            global $wp_query;
                            $query_vars = $wp_query->query_vars;

                            foreach ( $frontend_subpages as $key => $sub_page_link ) {
                                if ( isset($query_vars['tutor_dashboard_sub_page'] ) ) {
                                    if ( $query_vars['tutor_dashboard_sub_page'] == 'set-api' ) {
                                        $active_query_vars = 'set_api';
                                    } else if ( 
                                        $query_vars['tutor_dashboard_sub_page'] == 'settings' || 
                                        $query_vars['tutor_dashboard_sub_page'] == 'help' ||
                                        $query_vars['tutor_dashboard_sub_page'] == 'expired'
                                        ) {
                                        $active_query_vars = $query_vars['tutor_dashboard_sub_page'];
                                    } elseif ( $query_vars['tutor_dashboard_sub_page'] == "expired/page/$paged" ) {
                                        $active_query_vars = 'expired';
                                    } else {
                                        $active_query_vars = 'meetings';
                                    }
                                } else {
                                    if ( !$check_api ) {
                                        $active_query_vars = 'set_api';
                                    } else {
                                        $active_query_vars = 'meetings';
                                    }
                                    
                                }

                                $active =  $active_query_vars == $key ? 'active' : '' ;

                                echo 
                                "<li class=".$active.">
                                    <a href=".esc_url($sub_page_link).">
                                        $subPages[$key]
                                    </a>
                                </li>
                                ";
                            }
                        }
                    ?>
                </ul>
            </div>
        </div>

        <?php 
            $frontend_class = !is_admin() ? 'tutor-zoom-frontend' : '' ;
            if ($error_msg) {
                echo "<div class='tutor-alert zoom-api-error'>{$error_msg}</div>";
            } 
        ?>

        <div class="tutor-zoom-content <?php echo $frontend_class?>">
            <?php
            $page = sanitize_text_field($currentSubPage);
            $view_page = TUTOR_ZOOM()->path . 'views/pages/';

            /**
             * If only frontend check query vars & set page name
             * 
             * @since 1.9.3
             */
            if( !is_admin() ) {
                global $wp_query;
                $query_vars = $wp_query->query_vars;
                if( !isset($query_vars['tutor_dashboard_sub_page']) ) {
                    
                } elseif ( isset($query_vars['tutor_dashboard_sub_page']) && $query_vars['tutor_dashboard_sub_page'] == 'set-api' ) {
                    $page = 'set_api';
                } else if ( $query_vars['tutor_dashboard_sub_page'] == 'meetings' || $query_vars['tutor_dashboard_sub_page'] == 'settings' || $query_vars['tutor_dashboard_sub_page'] == 'help'  ) {
                    $page = $query_vars['tutor_dashboard_sub_page'];
                } else {
                    $page = "expired";
                }
                if ( $page == 'meetings' ) {
                    $page = 'frontend-meetings' ;
                }
                if ( !$check_api && $page === 'settings' ) {
                    $page = 'set_api';
                }
            } else {
                if ( $page == 'expired' ) {
                    $page = 'meetings';
                }
            }
       
            /**
             * Change zoom /all mettings page for the frontend
             * 
             * as design style changed
             * 
             * @since 1.9.4
             */
            if (file_exists($view_page . "/{$page}.php")) {
                include_once $view_page . "/{$page}.php";
            } else {
                if ( !is_admin() ) {
                    include_once $view_page . "/frontend-meetings.php";
                }
            }

            ?>
        </div>
    </div>
</div>