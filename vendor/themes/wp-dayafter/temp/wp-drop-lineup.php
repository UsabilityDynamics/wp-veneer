<?php

/**
 * This is a temporary file where we're going to store some artist lineup code, until
 * we get the full plugin completed
 * 
 * Generated by: http://pippinsplugins.com/easy-content-types/
 * 
 * @author Reid Williams
 */

/*******************************************************
 * Metabox code for artist post type
 *******************************************************/

$artistmeta_1_metabox = array(
	'id' => 'artistmeta',
	'title' => 'Artist Meta',
	'page' => array('artist'),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
				array(
					'name' 			=> 'Artist Website',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-website',
					'class' 			=> 'ecpt_artist-website',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
			
				array(
					'name' 			=> 'Artist Facebook',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-facebook',
					'class' 			=> 'ecpt_artist-facebook',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
			
				array(
					'name' 			=> 'Artist Google+',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-googleplus',
					'class' 			=> 'ecpt_artist-googleplus',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
			
				array(
					'name' 			=> 'Artist Twitter',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-twitter',
					'class' 			=> 'ecpt_artist-twitter',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
			
				array(
					'name' 			=> 'Artist SoundCloud',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-soundcloud',
					'class' 			=> 'ecpt_artist-soundcloud',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
			
				array(
					'name' 			=> 'Artist YouTube',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-youtube',
					'class' 			=> 'ecpt_artist-youtube',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
			
				array(
					'name' 			=> 'Artist Instagram',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-instagram',
					'class' 			=> 'ecpt_artist-instagram',
					'type' 			=> 'text',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
			
				array(
					'name' 			=> 'Artist Showtime',
					'desc' 			=> '',
					'id' 				=> 'ecpt_artist-showtime',
					'class' 			=> 'ecpt_artist-showtime',
					'type' 			=> 'repeatable',
					'rich_editor' 	=> 0,
					'max' 			=> 0				),
				)
);

add_action('admin_menu', 'ecpt_add_artistmeta_1_meta_box');
function ecpt_add_artistmeta_1_meta_box() {

	global $artistmeta_1_metabox;

	foreach($artistmeta_1_metabox['page'] as $page) {
		add_meta_box($artistmeta_1_metabox['id'], $artistmeta_1_metabox['title'], 'ecpt_show_artistmeta_1_box', $page, 'normal', 'high', $artistmeta_1_metabox);
	}
}

// function to show meta boxes
function ecpt_show_artistmeta_1_box()	{
	global $post;
	global $artistmeta_1_metabox;
	global $ecpt_prefix;
	global $wp_version;

	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_artistmeta_1_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

	echo '<table class="form-table">';

	foreach ($artistmeta_1_metabox['fields'] as $field) {
		// get current post meta data

		$meta = get_post_meta($post->ID, $field['id'], true);

		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', stripslashes($field['name']), '</label></th>',
				'<td class="ecpt_field_type_' . str_replace(' ', '_', $field['type']) . '">';
		switch ($field['type']) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:97%" /><br/>', '', stripslashes($field['desc']);
				break;
			case 'date':
				if($meta) { $value = ecpt_timestamp_to_date($meta); } else {  $value = ''; }
				echo '<input type="text" class="ecpt_datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="'. $value . '" size="30" style="width:97%" />' . '' . stripslashes($field['desc']);
				break;
			case 'upload':
				echo '<input type="text" class="ecpt_upload_field" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : '', '" size="30" style="width:80%" /><input class="ecpt_upload_image_button" type="button" value="Upload Image" /><br/>', '', stripslashes($field['desc']);
				break;
			case 'textarea':

				if($field['rich_editor'] == 1) {
					if($wp_version >= 3.3) {
						echo wp_editor($meta, $field['id'], array('textarea_name' => $field['id']));
					} else {
						// older versions of WP
						$editor = '';
						if(!post_type_supports($post->post_type, 'editor')) {
							$editor = wp_tiny_mce(true, array('editor_selector' => $field['class'], 'remove_linebreaks' => false) );
						}
						$field_html = '<div style="width: 97%; border: 1px solid #DFDFDF;"><textarea name="' . $field['id'] . '" class="' . $field['class'] . '" id="' . $field['id'] . '" cols="60" rows="8" style="width:100%">'. $meta . '</textarea></div><br/>' . __(stripslashes($field['desc']));
						echo $editor . $field_html;
					}
				} else {
					echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : '', '</textarea></div>', '', stripslashes($field['desc']);
				}

				break;
			case 'select':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ($field['options'] as $option) {
					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>', '', stripslashes($field['desc']);
				break;
			case 'radio':
				foreach ($field['options'] as $option) {
					echo '<input type="radio" name="', $field['id'], '" value="', $option, '"', $meta == $option ? ' checked="checked"' : '', ' /> ', $option;
				}
				echo '<br/>' . stripslashes($field['desc']);
				break;
			case 'multicheck':
				foreach ($field['options'] as $option) {
					echo '<input type="checkbox" name="' . $field['id'] . '[' . $option . ']" value="' . $option . '"' . checked( true, in_array( $option, $meta ), false ) . '/> ' . $option;
				}
				echo '<br/>' . stripslashes($field['desc']);
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' /> ';
				echo stripslashes($field['desc']);
				break;
			case 'slider':
				echo '<input type="text" rel="' . $field['max'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="1" style="float: left; margin-right: 5px" />';
				echo '<div class="ecpt-slider" rel="' . $field['id'] . '" style="float: left; width: 60%; margin: 5px 0 0 0;"></div>';
				echo '<div style="width: 100%; clear: both;">' . stripslashes($field['desc']) . '</div>';
				break;
			case 'repeatable' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_field_name" value=""/>';
				if(is_array($meta)) {
					$count = 1;
					foreach($meta as $key => $value) {
						$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[$key] . '" size="30" style="width:90%" />';
						if($count > 1) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_wrapper"><input type="text" class="ecpt_repeatable_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:90%" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_field button-secondary">' . __('Add New', 'ecpt') . '</button>  ' . __(stripslashes($field['desc']));

				echo $field_html;

				break;

			case 'repeatable upload' :

				$field_html = '<input type="hidden" id="' . $field['id'] . '" class="ecpt_repeatable_upload_field_name" value=""/>';
				if(is_array($meta)) {
					$count = 1;
					foreach($meta as $key => $value) {
						$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta[$key] . '" size="30" style="width:80%" /><button class="button-secondary ecpt_upload_image_button">Upload File</button>';
						if($count > 1) {
							$field_html .= '<a href="#" class="ecpt_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count++;
					}
				} else {
					$field_html .= '<div class="ecpt_repeatable_upload_wrapper"><input type="text" class="ecpt_repeatable_upload_field ecpt_upload_field" name="' . $field['id'] . '[]" id="' . $field['id'] . '[]" value="' . $meta . '" size="30" style="width:80%" /><input class="button-secondary ecpt_upload_image_button" type="button" value="Upload File" /></div>';
				}
				$field_html .= '<button class="ecpt_add_new_upload_field button-secondary">' . __('Add New', 'ecpt') . '</button>  ' . __(stripslashes($field['desc']));

				echo $field_html;

				break;
		}
		echo     '<td>',
			'</tr>';
	}

	echo '</table>';
}

// Save data from meta box
add_action('save_post', 'ecpt_artistmeta_1_save');
function ecpt_artistmeta_1_save($post_id) {
	global $post;
	global $artistmeta_1_metabox;

	// verify nonce
	if ( ! isset( $_POST['ecpt_artistmeta_1_meta_box_nonce'] ) || ! wp_verify_nonce($_POST['ecpt_artistmeta_1_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}

	foreach ($artistmeta_1_metabox['fields'] as $field) {

		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];

		if ($new && $new != $old) {
			if($field['type'] == 'date') {
				$new = ecpt_format_date($new);
				update_post_meta($post_id, $field['id'], $new);
			} else {
				if(is_string($new)) {
					$new = $new;
				}
				update_post_meta($post_id, $field['id'], $new);


			}
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
}


function ecpt_export_ui_scripts() {

	global $ecpt_options, $post;
	?>
	<script type="text/javascript">
			jQuery(document).ready(function($)
			{

				if($('.form-table .ecpt_upload_field').length > 0 ) {
					// Media Uploader
					window.formfield = '';

					$('body').on('click', '.ecpt_upload_image_button', function() {
					window.formfield = $('.ecpt_upload_field',$(this).parent());
						tb_show('', 'media-upload.php?type=file&post_id=<?php echo $post->ID; ?>&TB_iframe=true');
										return false;
						});

						window.original_send_to_editor = window.send_to_editor;
						window.send_to_editor = function(html) {
							if (window.formfield) {
								imgurl = $('a','<div>'+html+'</div>').attr('href');
								window.formfield.val(imgurl);
								tb_remove();
							}
							else {
								window.original_send_to_editor(html);
							}
							window.formfield = '';
							window.imagefield = false;
						}
				}
				if($('.form-table .ecpt-slider').length > 0 ) {
					$('.ecpt-slider').each(function(){
						var $this = $(this);
						var id = $this.attr('rel');
						var val = $('#' + id).val();
						var max = $('#' + id).attr('rel');
						max = parseInt(max);
						//var step = $('#' + id).closest('input').attr('rel');
						$this.slider({
							value: val,
							max: max,
							step: 1,
							slide: function(event, ui) {
								$('#' + id).val(ui.value);
							}
						});
					});
				}

				if($('.form-table .ecpt_datepicker').length > 0 ) {
					var dateFormat = 'mm/dd/yy';
					$('.ecpt_datepicker').datepicker({dateFormat: dateFormat});
				}

				// add new repeatable field
				$(".ecpt_add_new_field").on('click', function() {
					var field = $(this).closest('td').find("div.ecpt_repeatable_wrapper:last").clone(true);
					var fieldLocation = $(this).closest('td').find('div.ecpt_repeatable_wrapper:last');
					// set the new field val to blank
					$('input', field).val("");
					field.insertAfter(fieldLocation, $(this).closest('td'));

					return false;
				});

				// add new repeatable upload field
				$(".ecpt_add_new_upload_field").on('click', function() {
					var container = $(this).closest('tr');
					var field = $(this).closest('td').find("div.ecpt_repeatable_upload_wrapper:last").clone(true);
					var fieldLocation = $(this).closest('td').find('div.ecpt_repeatable_upload_wrapper:last');
					// set the new field val to blank
					$('input[type="text"]', field).val("");

					field.insertAfter(fieldLocation, $(this).closest('td'));

					return false;
				});

				// remove repeatable field
				$('.ecpt_remove_repeatable').on('click', function(e) {
					e.preventDefault();
					var field = $(this).parent();
					$('input', field).val("");
					field.remove();
					return false;
				});

			});
	  </script>
	<?php
}

function ecpt_export_datepicker_ui_scripts() {
	global $ecpt_base_dir;
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-slider');
}
function ecpt_export_datepicker_ui_styles() {
	global $ecpt_base_dir;
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, '1.8', 'all');
}

// these are for newest versions of WP
add_action('admin_print_scripts-post.php', 'ecpt_export_datepicker_ui_scripts');
add_action('admin_print_scripts-edit.php', 'ecpt_export_datepicker_ui_scripts');
add_action('admin_print_scripts-post-new.php', 'ecpt_export_datepicker_ui_scripts');
add_action('admin_print_styles-post.php', 'ecpt_export_datepicker_ui_styles');
add_action('admin_print_styles-edit.php', 'ecpt_export_datepicker_ui_styles');
add_action('admin_print_styles-post-new.php', 'ecpt_export_datepicker_ui_styles');

if ((isset($_GET['post']) && (isset($_GET['action']) && $_GET['action'] == 'edit') ) || (strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php')))
{
	add_action('admin_head', 'ecpt_export_ui_scripts');
}

// converts a time stamp to date string for meta fields
if(!function_exists('ecpt_timestamp_to_date')) {
	function ecpt_timestamp_to_date($date) {

		return date('m/d/Y', $date);
	}
}
if(!function_exists('ecpt_format_date')) {
	function ecpt_format_date($date) {

		$date = strtotime($date);

		return $date;
	}
}

/*******************************************************
 * Registration code for artist post type
 *******************************************************/
function register_artist_posttype() {
	$labels = array(
		'name' 				=> _x( 'Artists', 'post type general name' ),
		'singular_name'		=> _x( 'Artist', 'post type singular name' ),
		'add_new' 			=> __( 'Add New' ),
		'add_new_item' 		=> __( 'Add New Artist' ),
		'edit_item' 		=> __( 'Edit Artist' ),
		'new_item' 			=> __( 'New Artist' ),
		'view_item' 		=> __( 'View Artist' ),
		'search_items' 		=> __( 'Search Artists' ),
		'not_found' 		=> __( 'No Artists found' ),
		'not_found_in_trash'=> __( 'No Artists found in the trash' ),
		'parent_item_colon' => __( '' ),
		'menu_name'			=> __( '' )
	);
	
	$taxonomies = array();

	$supports = array('title','editor','author','thumbnail','revisions');
	
	$post_type_args = array(
		'labels' 			=> $labels,
		'singular_label' 	=> __('Artist'),
		'public' 			=> true,
		'show_ui' 			=> true,
		'publicly_queryable'=> true,
		'query_var'			=> true,
		'exclude_from_search'=> true,
		'show_in_nav_menus'	=> false,
		'capability_type' 	=> 'post',
		'has_archive' 		=> false,
		'hierarchical' 		=> false,
		'rewrite' 			=> array('slug' => 'artist', 'with_front' => false ),
		'supports' 			=> $supports,
		'menu_position' 	=> 5,
		'taxonomies'		=> $taxonomies
	 );
	 register_post_type('artist',$post_type_args);
}
add_action('init', 'register_artist_posttype');