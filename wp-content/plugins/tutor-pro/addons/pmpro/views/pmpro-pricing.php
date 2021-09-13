<form class="tutor-pmpro-single-course-pricing">
    <h3><?php _e('Pick a plan', 'tutor-pro'); ?></h3>

    <?php 
        // Tutoe Setting for PM Pro
        $no_commitment = tutor_utils()->get_option('pmpro_no_commitment_message');
        $money_back = tutor_utils()->get_option('pmpro_moneyback_day');
        $money_back = (is_numeric( $money_back ) && $money_back>0) ? $money_back : false;

        $level_page_id = apply_filters('tutor_pmpro_checkout_page_id', pmpro_getOption("checkout_page_id"));
        $level_page_url = get_the_permalink($level_page_id);

        if($no_commitment) {
            ?>
            <small><?php _e($no_commitment, 'tutor-pro'); ?></small>
            <?php
        }
        
        foreach($required_levels as $level) {
            $id = 'tutor_pmpro_level_radio_' . $level->id;
            $highlight = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_level_highlight', true);

            ?>
            <input type="radio" name="tutor_pmpro_level_radio" id="<?php echo $id; ?>" <?php echo $highlight ? 'checked="checked"' : ''; ?>/>
            <label for="<?php echo $id; ?>" class="<?php echo $highlight ? 'tutor-pmpro-level-highlight' : ''; ?>">
                <div class="tutor-pmpro-level-header">
                    <div>
                        <span class="tutor-pmpro-circle "></span>
                    </div>
                    <div>
                        <h4><?php echo $level->name; ?></h4>
                    </div> 
                    <div>
                        <?php 
                            $billing_amount = round($level->billing_amount);
                            $initial_payment = round($level->initial_payment);

                            $billing_text = '<b>';
                                $currency_position=='left' ? $billing_text.= $currency_symbol : 0;
                                    $billing_text.= ($level->cycle_period ? $billing_amount : $initial_payment);
                                $currency_position=='right' ? $billing_text.= $currency_symbol : 0;
                            $billing_text.= '</b>';

                            $billing_text.= ($level->cycle_period ? '<small>/' . substr( $level->cycle_period, 0, 2 ). '</small>' : '');

                            echo $billing_text;
                        ?>  
                    </div>
                </div>
                <div class="tutor-pmpro-level-desc">
                    <p><?php echo $level->description; ?></p>
                    <div>
                        <a href="<?php echo $level_page_url . '?level=' . $level->id; ?>" class="tutor-button tutor-button-primary">
                            <?php _e('Buy Now', 'tutor-pro'); ?>
                        </a>
                        <?php 
                            echo $money_back ? '<span>' . sprintf(__('%d-day money-back guarantee', 'tutor-pro'), $money_back) . '</span>' : '';
                        ?>
                    </div>
                </div>
            </label>
            <?php
        }
    ?>
</form>