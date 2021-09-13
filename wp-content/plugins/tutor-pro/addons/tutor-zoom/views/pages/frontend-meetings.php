<?php
/**
 * Zoom meeting frontend template
 * 
 * @since 1.9.4
 */
if ( ! defined( 'ABSPATH' ) )
exit;

global $wpdb;
// Pagination
$per_page = 20;
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

// Search Filter
$_search    = isset($_GET['search']) ? $_GET['search'] : '';
$_course    = isset($_GET['course']) ? $_GET['course'] : '';
$_date      = isset($_GET['date']) ? tutor_get_formated_date( 'Y-m-d', $_GET['date']) : '';
$_filter    = 'upcoming';
$orderby    = ( !isset( $_GET['orderby'] ) || $_GET['orderby'] !== 'post_title' ) ? 'datetime' : 'post_title';
$order      = ( !isset($_GET['order']) || $_GET['order'] !== 'desc' ) ? 'asc' : 'desc';

$user_id = get_current_user_id();
$has_items = count(get_tutor_zoom_meetings(array(
    'author'    =>  sanitize_text_field( $user_id ),
)));

$total_items = count(get_tutor_zoom_meetings(array(
    'author'    => sanitize_text_field( $user_id ),
    'search'    => sanitize_text_field( $_search ),
    'course_id' => sanitize_text_field( $_course ),
    'date'      => sanitize_text_field( $_date ),
    'filter'    => sanitize_text_field( $_filter )
)));

$meetings = get_tutor_zoom_meetings(array(
    'author'    => sanitize_text_field( $user_id ),
    'paged'     => sanitize_text_field( $paged ),
    'per_page'  => sanitize_text_field( $per_page ),
    'search'    => sanitize_text_field( $_search ),
    'course_id' => sanitize_text_field( $_course ),
    'date'      => sanitize_text_field( $_date ),
    'filter'    => sanitize_text_field( $_filter ),
    'orderby'   => sanitize_text_field( $orderby ),
    'order'     => sanitize_text_field( $order )
));

$courses = get_posts(array(
    'author'        => sanitize_text_field( $user_id ),
    'numberposts'   => -1,
    'post_type'     => tutor()->course_post_type,
    'post_status'   => 'publish'
));

if ($has_items > 0) { ?>

    <div class="tutor-dashboard-announcement-sorting-wrap">

        <div class="tutor-form-group">
            <label>
                <?php _e( 'Search', 'tutor-pro' );?>
            </label>
            <div class="tutor-zoom-field-icon-wrapper">
                <input type="text" class="tutor-report-search" value="<?php echo $_search; ?>" autocomplete="off" placeholder="<?php _e('Search meeting', 'tutor-pro'); ?>" />
                <button class="tutor-zoom-search-action tutor-report-search-btn"><i class="tutor-icon-magnifying-glass-1"></i></button>
            </div>
        </div>

        <div class="tutor-form-group course-select-box">
            <label>
                <?php _e('Course', 'tutor-pro'); ?>
            </label>
            <select class="tutor-zoom-course ignore-nice-select">
                <option value=""><?php _e('All', 'tutor-pro'); ?></option>
                <?php
                    if (!empty($courses)) {
                        foreach ($courses as $key => $course) {
                            echo '<option '.($_course == $course->ID ? "selected" : "").' value="'.$course->ID.'">'.$course->post_title.'</option>';
                        }
                    }
                ?>
            </select>
        </div>

        <div class="tutor-form-group tutor-announcement-datepicker">
            <label>
                <?php _e('Date', 'tutor-pro'); ?>
            </label>
            <div>
                <input type="text" class="tutor_date_picker tutor-zoom-date"  id="tutor-announcement-datepicker" value="<?php echo $_date != '' ? tutor_get_formated_date( get_option( 'date_format' ), $_date ) : ''; ?>" autocomplete="off" placeholder="<?php echo __( get_option( 'date_format' ), 'tutor-pro' ); ?>" />
                <i class="tutor-icon-calendar"></i>
            </div>
        </div>


    </div>

    <div class="tutor-announcement-table-wrap">
        <?php 
        if (!empty($meetings)) { 
            
            $sort_order = array('order' => $order=='asc' ? 'desc' : 'asc');

            $time_sort = http_build_query(array_merge($_GET, ( $orderby=='datetime' ? $sort_order : array() ), array( 'orderby' => 'datetime' )));
            $name_sort = http_build_query(array_merge($_GET, ( $orderby=='post_title' ? $sort_order : array() ), array( 'orderby' => 'post_title' )));
            
            $time_icon = $orderby=='datetime' ? (strtolower( $order ) == 'asc' ? 'up' : 'down') : 'up';
            $name_icon = $orderby=='post_title' ? (strtolower( $order ) == 'asc' ? 'up' : 'down') : 'up';

            ?>

            <table class="tutor-dashboard-announcement-table" width="100%">
                <thead>
                    <tr>
                        <th style="width:23%">
                            <div class="tutor-zoom-datetime-sorting">
                                <span>
                                    <?php _e('Date & Time', 'tutor-pro'); ?>
                                </span>
                                <a href="?<?php echo $time_sort; ?>" style="position: relative; top: 6px;">
                                    <img src="<?php echo tutor_pro()->url; ?>addons/tutor-zoom/assets/images/order-<?php echo $time_icon; ?>.svg"/>
                                </a>
                            </div>
                        </th>
                        <th style="text-align:left"><?php _e('Meeting Name', 'tutor-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    
                    $current_time = time();
                    $i = 0;
                    foreach ($meetings as $key => $meeting) { 
                        if ( $meeting->is_expired ) {
                            continue;
                        }
                        $i++;
                        $tzm_start      = get_post_meta($meeting->ID, '_tutor_zm_start_datetime', true);
                        $meeting_data   = get_post_meta($meeting->ID, $this->zoom_meeting_post_meta, true);
                        $meeting_data   = json_decode($meeting_data, true);
                        $input_date     = \DateTime::createFromFormat('Y-m-d H:i:s', $tzm_start);
                        $start_date     = $input_date->format('j M, Y,<\b\r>h:i A');
                        $course_id      = get_post_meta($meeting->ID, '_tutor_zm_for_course', true);
                        $topic_id       = get_post_meta($meeting->ID, '_tutor_zm_for_topic', true);

                        $duration       = get_post_meta($meeting->ID, '_tutor_zm_duration', true);
                        $unit           = get_post_meta($meeting->ID, '_tutor_zm_duration_unit', true);
                        $duration       = $unit == 'hr' ? $duration * 60 : $duration;
                        $is_past        = $current_time >= ($input_date->getTimestamp() + ($duration * 60));
                        ?>
                        <tr class="tutor-zoom-meeting-item">
                            <td>
                                <?php echo $start_date; ?> 
                            </td>
                            <td class="tutor-announcement-content-wrap">
                                <div class="tutor-zoom-content-wrap tutor-zoom-frontend-content">
                                    <h4><?php esc_html_e( $meeting->post_title ); ?></h4>
                                    <p><?php echo __('Course:', 'tutor-pro').' '.get_the_title($course_id); ?></p>
                                </div>

                                <div class="tutor-announcement-buttons tutor-zoom-frontend-buttons">
                                    <li>
                                        <?php
                                            $button_text    = 'Start Meeting';
                                            $button_class   = 'button-fill';
                                            if ( $meeting->is_expired ) {
                                                $button_text  = 'Expired';
                                                $button_class = 'button-disabled';
                                            } elseif ( $meeting->is_started ) {
                                                $button_text  = 'Join Now';
                                                $button_class = 'button-outlined'; 
                                            }
                                        ?>
                                        <a href="<?php echo !$is_past ? $meeting_data['start_url'] : 'javascript:void(0)'; ?>" class="tutor-btn bordered-btn <?php esc_attr_e( $button_class ); ?>" target="<?php echo !$is_past ? '_blank' : ''; ?>">
                                            <i class="tutor-icon-zoom"></i>
                                            <?php esc_html_e($button_text, 'tutor-pro'); ?>
                                        </a>
                                    </li>

                                    <li class="tutor-dropdown">
                                        <span class="tutor-btn bordered-btn border-black" target="<?php echo !$is_past ? '_blank' : ''; ?>">
                                            <?php _e( 'Info', 'tutor-pro' );?> <i class="tutor-icon-angle-down"></i>
                                        </span>
                                        <ul class="tutor-dropdown-menu zoom-info-dropdown small-card bg-white">
                                            <li>
                                                <div class="tutor-form-group">
                                                    <label for="">
                                                        <?php _e( 'Meeting ID', 'tutor-pro' );?>
                                                    </label>
                                                    <span>
                                                        <?php esc_html_e( $meeting_data['id'] );?>
                                                        <i class="tutor-icon-copy" data-zoom-info="<?php esc_html_e( $meeting_data['id'] );?>"></i>
                                                    </span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="tutor-form-group">
                                                    <label for="">
                                                        <?php _e( 'Password', 'tutor-pro' );?>
                                                    </label>
                                                    <span>
                                                        <?php esc_html_e( $meeting_data['password'] );?>
                                                        <i class="tutor-icon-copy" data-zoom-info="<?php esc_html_e( $meeting_data['password'] );?>"></i>
                                                    </span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="tutor-form-group">
                                                    <label for="">
                                                        <?php _e( 'Host Email', 'tutor-pro' );?>
                                                    </label>
                                                    <span>
                                                        <?php esc_html_e( $meeting_data['host_email'] );?>
                                                        <i class="tutor-icon-copy" data-zoom-info="<?php esc_html_e( $meeting_data['host_email'] );?>"></i>
                                                    </span>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="tutor-dropdown">
                                        <i class="tutor-icon-action"></i>
                                        <ul class="tutor-dropdown-menu">
                                            <li class="tutor-zoom-meeting-modal-open-btn edit" data-meeting-id="<?php echo $meeting->ID; ?>" data-topic-id="<?php echo $topic_id; ?>" data-course-id="<?php echo $course_id; ?>" data-click-form="0">
                                                <i class="tutor-icon-pencil"></i>
                                                <?php _e('Edit', 'tutor'); ?>
                                            </li>
                                            <li class="tutor-zoom-meeting-delete-btn delete" data-meeting-id="<?php echo $meeting->ID; ?>">
                                                <i class="tutor-icon-garbage"></i>
                                                <?php _e('Delete', 'tutor'); ?>
                                            </li>
                                        </ul>
                                    </li>
                                </div>  
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                </table>
                <!-- table end -->

        <?php
        } else { ?>
            <div class="no-data-found">
                <img src="<?php echo tutor_pro()->url."addons/tutor-report/assets/images/empty-data.svg"?>" alt="">
                <span><?php _e('No Zoom meetings found', 'tutor-pro'); ?></span>
            </div>

        <?php } if ( count($meetings) ):?>
        <div class="tutor-list-footer">
            <div class="tutor-report-count">
                <?php 
                    $item = _n( 'item', 'items', $i, 'tutor-pro' ); 
                    if($total_items > 0){
                        printf( __('Showing <strong> %s </strong> of <strong> %s </strong> '.$item.' '), $i,  $total_items );
                    }
                ?>
            </div>
            <div class="zoom-pagination">
                <?php
                    $big = 999999;
                    $url = esc_url( tutor_utils()->get_tutor_dashboard_page_permalink()."zoom/?paged=%#%");
                    echo paginate_links( array(
                        'base'      => str_replace( 1, '%#%', $url ),
                        'current'   => sanitize_text_field( $paged ),
                        'format'    => '?paged=%#%',
                        'total'     => ceil($total_items/$per_page),
                        'prev_text' => __( "<i class='tutor-icon-angle-left'></i>", 'tutor-pro' ),
                        'next_text' => __( "<i class='tutor-icon-angle-right'></i>", 'tutor-pro' )
                    ) );
                ?>
            </div>
        </div>
        <?php endif;?>
    </div>
<?php 
} else { ?>
    <div class='tutor-alert tutor-alert-info'>
        <?php _e('To add a new meeting, please open a course in editing mode.', 'tutor-pro'); ?>
    </div>
    <div class="tutor-zoom-data-found">
        <img src="<?php echo TUTOR_ZOOM()->url.'assets/images/empty-meeting.png'; ?>" alt="" />
        <p><?php _e('Not enough data to show', 'tutor-pro'); ?></p>
    </div>
<?php 
} ?>

<div class="tutor-modal-wrap tutor-zoom-meeting-modal-wrap">
    <div class="tutor-modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <h1><?php _e('Zoom Meeting', 'tutor-pro'); ?></h1>
            </div>
            <div class="modal-close-wrap">
                <a href="javascript:;" class="modal-close-btn"><i class="tutor-icon-line-cross"></i> </a>
            </div>
        </div>
        <div class="modal-container"></div>
    </div>
</div>