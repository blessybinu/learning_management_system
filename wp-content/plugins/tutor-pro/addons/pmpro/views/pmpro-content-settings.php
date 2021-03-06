<style>
	#tutor-pmpro-setting-wrapper+hr {
		visibility: hidden;
	}
</style>

<div id="tutor-pmpro-setting-wrapper" style="background: white; padding: 10px 20px;">
	<h3><?php _e('Tutor LMS Content Settings', 'tutor-pro'); ?></h3>

	<?php
		global $wpdb;

		if(isset($_REQUEST['edit']))
			$edit = intval($_REQUEST['edit']);
		else
			$edit = false;

		// get the level...
		if(!empty($edit) && $edit > 0) {
			$level = $wpdb->get_row( $wpdb->prepare( "
							SELECT * FROM $wpdb->pmpro_membership_levels
							WHERE id = %d LIMIT 1",
				$edit
			),
				OBJECT
			);
			$temp_id = $level->id;
		} elseif(!empty($copy) && $copy > 0) {
			$level = $wpdb->get_row( $wpdb->prepare( "
							SELECT * FROM $wpdb->pmpro_membership_levels
							WHERE id = %d LIMIT 1",
				$copy
			),
				OBJECT
			);
			$temp_id = $level->id;
			$level->id = NULL;
		}
		else

			// didn't find a membership level, let's add a new one...
			if(empty($level)) {
				$level = new \stdClass();
				$level->id = NULL;
				$level->name = NULL;
				$level->description = NULL;
				$level->confirmation = NULL;
				$level->billing_amount = NULL;
				$level->trial_amount = NULL;
				$level->initial_payment = NULL;
				$level->billing_limit = NULL;
				$level->trial_limit = NULL;
				$level->expiration_number = NULL;
				$level->expiration_period = NULL;
				$edit = -1;
			}

		//defaults for new levels
		if(empty($copy) && $edit == -1) {
			$level->cycle_number = 1;
			$level->cycle_period = "Month";
		}

		// grab the categories for the given level...
		if(!empty($temp_id))
			$level->categories = $wpdb->get_col( $wpdb->prepare( "
							SELECT c.category_id
							FROM $wpdb->pmpro_memberships_categories c
							WHERE c.membership_id = %d",
				$temp_id
			) );
		if(empty($level->categories))
			$level->categories = array();

		$level_categories = $level->categories;
		$highlight = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_level_highlight', true);

		function generate_categories_for_pmpro($cats, $level_categories = array()) {

			if(!count($cats)) {
				return;
			}

			echo '<ul>';
				foreach ($cats as $cat) {
					$name = 'membershipcategory_' . $cat->term_id;
					if (!empty($level_categories)) {
						$checked = checked(in_array($cat->term_id, $level_categories), true, false);
					} else {
						$checked = '';
					}

					echo "<li class=membershipcategory>
						<label><input type=checkbox name='{$name}' value='yes' {$checked}/> {$cat->name}</label>";
						generate_categories_for_pmpro($cat->children, $level_categories);
					echo '</li>';
				}
			echo '</ul>';
		}
	?>


	<input type="hidden" value="pmpro_settings" name="tutor_action"/>

	<table class="form-table">
		<tbody>
			<tr class="membership_model">
				<th width="200"><label for="tutor_pmpro_membership_model_select"><?php _e('Membership Model', 'tutor-pro'); ?>:</label></th>
				<td>
					<?php
					$membership_model = get_pmpro_membership_level_meta( $level->id, 'tutor_pmpro_membership_model', true );
					?>
					<select name="tutor_pmpro_membership_model" id="tutor_pmpro_membership_model_select" class="tutor_select2">
						<option value=""><?php _e('Select a membership model', 'tutor-pro'); ?></option>
						<option value="full_website_membership" <?php selected('full_website_membership', $membership_model) ?> ><?php _e('Full website membership', 'tutor-pro'); ?></option>
						<option value="category_wise_membership" <?php selected('category_wise_membership', $membership_model) ?>><?php _e('Category wise membership', 'tutor-pro'); ?></option>
					</select>
				</td>
			</tr>

			<tr class="membership_categories membership_course_categories" style="display: <?php echo $membership_model === 'category_wise_membership' ? '' : 'none'; ?>;">
				<th width="200"><label><?php _e('Course Categories', 'tutor-pro'); ?>:</label></th>
				<td>
					<?php generate_categories_for_pmpro(tutor_utils()->get_course_categories(), $level_categories); ?>
				</td>
			</tr>

			<tr class="">
				<th width="200"><label><?php _e('Add Recommend badge', 'tutor-pro'); ?>:</label></th>
				<td>
					<label class="tutor-switch">
                        <input type="checkbox"  value="1" name="tutor_pmpro_level_highlight" <?php echo $highlight ? 'checked="checked"' : ''; ?>/>
                        <span class="slider round tutor-switch-blue"></span>
                    </label>
				</td>
			</tr>
		</tbody>
	</table>
</div>