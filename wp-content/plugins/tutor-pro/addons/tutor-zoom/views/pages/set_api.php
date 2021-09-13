<?php
if (!defined('ABSPATH'))
    exit;
/**
 * Specific button style for frontend & admin side
 * 
 * @since 1.9.4
 */
$save_button            =  is_admin() ? 'button button-primary' : 'tutor-button' ;
$api_button             =  is_admin() ? 'button' : 'tutor-button tutor-button-primary tutor-button-zoom-api-check' ;
$check_api              = tutor_zoom_check_api_connection();
?>
<?php if ( !is_admin() ):?>
<div class="zoom-configure-wrapper">
    <div class="tutor-zoom-icon-content-wrapper">
        <i class="tutor-icon-zoom"></i>
        <div class="zoom-content">
            <h3>
                <?php _e( 'Setup your Zoom Integration', 'tutor-pro' );?>
            </h3>
            
            <p>
                <?php 
                    $content = "Please set your API Credentials. Without valid credentials, Zoom integration will not work. Create credentials by following ".'<a href="https://marketplace.zoom.us/develop/create" target="_blank">this link</a>'." .";
                    echo wp_kses_post( $content, 'tutor-pro' );
                ?>
            </p>
        </div>
    </div>
    <div class="zoom-image">
        <img src="<?php echo esc_url( TUTOR_ZOOM()->url.'/assets/images/mask-group.png', 'tutor-pro' );?>" alt="zoom-config">
    </div>
</div>
<?php endif;?>

<div class="tutor-zoom-api-container">
    <form id="tutor-zoom-settings" action="">
        <input type="hidden" name="action" value="tutor_save_zoom_api">
        <div class="tutor-zoom-form-container">
            <div class="input-area">
                <h3><?php _e('Setup your Zoom Integration', 'tutor-pro'); ?></h3>
                <?php if ( is_admin() ):?>
                    <p>
                        <?php _e('Visit your Zoom account and fetch the API key to connect Zoom with your eLearning website. Go to ', 'tutor-pro'); ?><a href="https://marketplace.zoom.us/develop/create" target="_blank"> <?php _e('Zoom Website.', 'tutor-pro'); ?></a>
                    </p>
                <?php endif;?>
                <div class="tutor-form-group">
                    <label for="tutor_zoom_api_key"><?php _e('API Key', 'tutor-pro'); ?></label>
                    <input type="text" id="tutor_zoom_api_key" name="<?php echo $this->api_key; ?>[api_key]" value="<?php echo $this->get_api('api_key'); ?>" placeholder="<?php _e('Enter Your Zoom Api Key', 'tutor-pro'); ?>"/>
                </div>
                <div class="tutor-form-group">
                    <label for="tutor_zoom_api_secret"><?php _e('Secret Key', 'tutor-pro'); ?></label>
                    <input type="text" id="tutor_zoom_api_secret" name="<?php echo $this->api_key; ?>[api_secret]" value="<?php echo $this->get_api('api_secret'); ?>" placeholder="<?php _e('Enter Your Zoom Secret Key', 'tutor-pro'); ?>"/>
                </div>
                <div class="set-api-buttons <?php if( is_admin() ) { echo 'tutor-zoom-button-container';} ?>">
                    <button type="submit" id="save-changes" class="<?php echo $save_button;?>">
                        <?php _e('Save Changes', 'tutor-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>