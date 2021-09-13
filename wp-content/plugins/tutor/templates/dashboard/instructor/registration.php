<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;
$result = $wpdb->get_results("SELECT * FROM  wp_countries");

?>

<?php if(!get_option( 'users_can_register', false )) : ?>

    <?php 
        $args = array(
            'image_path'    => tutor()->url.'assets/images/construction.png',
            'title'         => __('Oooh! Access Denied', 'tutor'),
            'description'   => __('You do not have access to this area of the application. Please refer to your system  administrator.', 'tutor'),
            'button'        => array(
                'text'      => __('Go to Home', 'tutor'),
                'url'       => get_home_url(),
                'class'     => 'tutor-button tutor-button-primary'
            )
        );
        tutor_load_template('feature_disabled', $args); 
    ?>

<?php else:?>

<?php do_action('tutor_before_instructor_reg_form');?>

    <form method="post" enctype="multipart/form-data">

        <?php do_action('tutor_instructor_reg_form_start');?>

        <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
        <input type="hidden" value="tutor_register_instructor" name="tutor_action"/>

        <?php
            $errors = apply_filters('tutor_instructor_register_validation_errors', array());
            if (is_array($errors) && count($errors)){
                echo '<div class="tutor-alert-warning"><ul class="tutor-required-fields">';
                foreach ($errors as $error_key => $error_value){
                    echo "<li>{$error_value}</li>";
                }
                echo '</ul></div>';
            }
        ?>

        <div class="tutor-form-row">
            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('First Name', 'tutor'); ?>
                    </label>

                    <input type="text" name="first_name" value="<?php echo tutor_utils()->input_old('first_name'); ?>" placeholder="<?php _e('First Name', 'tutor'); ?>" required autocomplete="given-name">
                </div>
            </div>

            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('Last Name', 'tutor'); ?>
                    </label>

                    <input type="text" name="last_name" value="<?php echo tutor_utils()->input_old('last_name'); ?>" placeholder="<?php _e('Last Name', 'tutor'); ?>" required autocomplete="family-name">
                </div>
            </div>


        </div>

        <div class="tutor-form-row">

            <div class="tutor-form-col-6 hide">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('User Name', 'tutor'); ?>
                    </label>

                    <input type="text" name="user_login" class="tutor_user_name" value="<?php echo tutor_utils()->input_old('user_login'); ?>" placeholder="<?php _e('User Name', 'tutor'); ?>" autocomplete="username">
                </div>
            </div>

            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('Country', 'tutor'); ?>
                    </label>
                    <select name="billing_country"  placeholder="<?php esc_attr_e( 'Country', 'edumall' ); ?>">
                        <option value="">Please select country</option>
                        <?php foreach($result as $key => $country):
                            $selected = ($country->code == 'IN') ? 'selected': '';
                        ?>
                            <option value="<?php echo $country->code;?>" <?php echo $selected ?> ><?php echo $country->name ?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div> 

            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('E-Mail', 'tutor'); ?>
                    </label>

                    <input type="text" name="email" value="<?php echo tutor_utils()->input_old('email'); ?>" placeholder="<?php _e('E-Mail', 'tutor'); ?>" required autocomplete="email">
                </div>
            </div>

        </div>

        <div class="tutor-form-row">
            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('Password', 'tutor'); ?>
                    </label>

                    <input type="password" name="password" value="<?php echo tutor_utils()->input_old('password'); ?>" placeholder="<?php _e('Password', 'tutor'); ?>" required autocomplete="new-password">
                </div>
            </div>

            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('Password confirmation', 'tutor'); ?>
                    </label>

                    <input type="password" name="password_confirmation" value="<?php echo tutor_utils()->input_old('password_confirmation'); ?>" placeholder="<?php _e('Password Confirmation', 'tutor'); ?>" required autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="tutor-form-row">
            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('Your Phone', 'tutor'); ?>
                    </label>
                    <input type="number" name="phone" value="<?php echo tutor_utils()->input_old('phone'); ?>" placeholder="<?php _e('Your Phone', 'tutor'); ?>" required >
                </div>
            </div>

            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('Years of experience', 'tutor'); ?>
                    </label>
                    <select id="ip_instructor_experience" class="form-control form-input" name="experience" >
                        <option value="0-1 Year">0 - 1 Year</option>
                        <option value="1-2 Years">1 - 2 Years</option>
                        <option value="2-4 Years">2 - 4 Years</option>
                        <option value="4-6 Years">4 - 6 Years</option>
                        <option value="6-8 Years">6 - 8 Years</option>
                        <option value="8-10 Years">8 - 10 Years</option>
                        <option value="10 above Years">10 &gt; Years</option>
                    </select>    
                </div>
            </div>
        </div>

    
       

        <div class="tutor-form-row">
            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                       <?php esc_html_e( 'Choose the subject(s) that you can teach', 'edumall' ); ?>
                    </label>
                    <select class="js-example-placeholder-multiple js-states form-control" name="subject[]" multiple="multiple">
                      <option value="mathematics">Mathematics</option>
                        <option value="physics">Physics</option>
                        <option value="chemistry">Chemistry</option>
                        <option value="english">English</option>
                    </select>
                </div>
            </div>
            <div class="tutor-form-col-6">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('Choose the curriculum you are experienced in', 'tutor'); ?>
                    </label>
                    <select class="js-example-placeholder-multiple js-states form-control" name="curriculum[]" multiple="multiple">
                      <option value="uae-moe-curriculum">UAE MoE Curriculum</option>
                        <option value="jordan">JORDAN</option>
                        <option value="british_curriculum">British Curriculum</option>
                        <option value="american_curriculum">American Curriculum</option>
                        <option value="international_baccalaureate">International Baccalaureate</option>
                        <option value="indian_curriculum">Indian Curriculum (CBSE and/or ICSE)</option>
                    </select>
                </div>
            </div>
        </div> 

         <div class="tutor-form-row">
            <div class="tutor-form-col-12">
                <div class="tutor-form-group">
                    <label for="text"><?php  _e('IN FEW WORDS, TELL US ABOUT YOUR EXPERIENCE AND ACHIEVEMENT')?></label>
                    <textarea name="instructor_about" class="form-control form-input" rows="2" cols="5"></textarea>
                </div>
            </div>
        </div> 

        <div class="tutor-form-row">
            <div class="tutor-form-col-12">
                <div class="tutor-form-group">
                    <?php
                        //providing register_form hook
                        do_action('tutor_instructor_reg_form_middle');
                        do_action('register_form');
                    ?>
                </div>
            </div>
        </div> 

        <?php do_action('tutor_instructor_reg_form_end');?>

        <div class="tutor-form-row">
            <div class="tutor-form-col-12">
                <div class="tutor-form-group tutor-reg-form-btn-wrap">
                    <button type="submit" name="tutor_register_instructor_btn" value="register" class="tutor-button"><?php _e('Register as instructor', 'tutor'); ?></button>
                </div>
            </div>
        </div>

    </form>

<?php do_action('tutor_after_instructor_reg_form');?>
<?php endif; ?>

