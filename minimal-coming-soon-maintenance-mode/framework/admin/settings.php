<?php

/**
 * Settings page for the plugin
 *
 * @link       http://www.webfactoryltd.com
 * @since      0.1
 */

if (!defined('WPINC')) {
	die;
}

function csmm_admin_settings() {

	// Including the mailchimp class
	require_once 'include/classes/class-mailchimp.php';



	// List of Bunny fonts
	require_once 'include/fonts.php';

  if (!empty($_POST['save-license']) && 'save-license' == sanitize_text_field(wp_unslash($_POST['save-license'])) && isset($_POST['csmm_save_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['csmm_save_nonce'])), 'csmm_save_settings')) {
    $meta = csmm_get_meta();
    if (empty($_POST['license_key'])) {
        $options['license_type'] = '';
        $options['license_expires'] = '1900-01-01';
        $options['license_active'] = false;
        $options['license_key'] = '';
        set_transient('csmm_error_msg', '<div class="signals-alert signals-alert-info"><strong>License key saved.</strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', 1);
      } else {
        if(isset($_POST['license_key'])){
            $license_key = sanitize_text_field(wp_unslash($_POST['license_key']));
        } else {
            $license_key = '';
        }
        $tmp = csmm_license::validate_license_key($license_key);
        if ($tmp['success']) {
          $options['license_type'] = $tmp['license_type'];
          $options['license_expires'] = $tmp['license_expires'];
          $options['license_active'] = $tmp['license_active'];
          $options['license_key'] = $license_key;
          if ($tmp['license_active']) {
            set_transient('csmm_error_msg', '<div class="signals-alert signals-alert-info"><strong>License key saved and activated!</strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', 1);
            set_site_transient('update_plugins', null);
          } else {
            set_transient('csmm_error_msg', '<div class="signals-alert signals-alert-info"><strong>License not active. ' . $tmp['error'] . '</strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', 1);
          }
        } else {
          set_transient('csmm_error_msg', '<div class="signals-alert signals-alert-info"><strong>Unable to contact licensing server. Please try again in a few moments.</strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', 1);
        }
      }
      $meta = array_merge($meta, $options);
      update_option('signals_csmm_meta', $meta);
  } elseif ( isset( $_POST['signals_csmm_submit'] ) && isset($_POST['csmm_save_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['csmm_save_nonce'])), 'csmm_save_settings')) {

		// Checking whether the status option is checked or not
		if ( isset( $_POST['signals_csmm_status'] ) ) :
			$tmp_options['status'] = absint( $_POST['signals_csmm_status'] );
		else :
			$tmp_options['status'] = '2';
		endif;

    // Checking whether the love option is checked or not
    if ( isset( $_POST['signals_csmm_love'] ) ) :
      $tmp_options['love'] = absint( $_POST['signals_csmm_love'] );
    else :
      $tmp_options['love'] = '0';
    endif;


		// Checking whether the user logged in option is checked or not
		if ( isset( $_POST['signals_csmm_showlogged'] ) ) :
			$tmp_options['logged'] = absint( $_POST['signals_csmm_showlogged'] );
		else :
			$tmp_options['logged'] = '2';
		endif;


		// Checking whether the search engine exclusion option is checked or not
		if ( isset( $_POST['signals_csmm_excludese'] ) ) :
			$tmp_options['exclude_se'] = absint( $_POST['signals_csmm_excludese'] );
		else :
			$tmp_options['exclude_se'] = '2';
		endif;


		// For the MailChimp list ID
		if ( isset( $_POST['signals_csmm_list'] ) ) :
			$tmp_options['list'] = wp_strip_all_tags(wp_unslash($_POST['signals_csmm_list']));
		else :
			$tmp_options['list'] = '';
		endif;


		// For content overlay
		if ( isset( $_POST['signals_csmm_overlay'] ) ) :
			$tmp_options['overlay'] = absint( $_POST['signals_csmm_overlay'] );
		else :
			$tmp_options['overlay'] = '2';
		endif;


		// Checking whether the ignore form styles option is checked or not
		if ( isset( $_POST['signals_csmm_ignore_styles'] ) ) :
			$tmp_options['form_styles'] = absint( $_POST['signals_csmm_ignore_styles'] );
		else :
			$tmp_options['form_styles'] = '2';
		endif;


		// Checking whether the disable plugin option is checked or not
		if ( isset( $_POST['signals_csmm_disable'] ) ) :
			$tmp_options['disabled'] = absint( $_POST['signals_csmm_disable'] );
		else :
			$tmp_options['disabled'] = '2';
		endif;

        // Checking whether the disable status bar menu option is checked or not
        if ( isset( $_POST['signals_csmm_disable_adminbar'] ) ) :
        $tmp_options['disable_adminbar'] = '1';
        else :
        $tmp_options['disable_adminbar'] = '0';
        endif;

        // Checking whether the show login button option is checked or not
        if ( isset( $_POST['signals_csmm_showloginbutton'] ) ) :
        $tmp_options['show_login_button'] = '1';
        else :
        $tmp_options['show_login_button'] = '0';
        endif;


		// Saving the record to the database
		$update_options = array(
            'settings_customized'   => true,
            'status'                => $tmp_options['status'],
            'love'				    => $tmp_options['love'],
            'title'                 => isset($_POST['signals_csmm_title'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_title'])):get_bloginfo('name') . ' is coming soon',
			'description' 			=> isset($_POST['signals_csmm_description'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_description'])):'We are doing some maintenance on our site. Please come back later.',
			'header_text' 			=> isset($_POST['signals_csmm_header'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_header'])):'Our site is coming soon',
			'secondary_text' 		=> isset($_POST['signals_csmm_secondary'])?wp_kses_post(wp_unslash($_POST['signals_csmm_secondary'])):'We are doing some maintenance on our site. It won\'t take long, we promise. Come back and visit us again in a few days. Thank you for your patience!',
			'antispam_text' 		=> isset($_POST['signals_csmm_antispam'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_antispam'])):'And yes, we hate spam too!',
			'custom_login_url' 		=> isset($_POST['signals_csmm_custom_login'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_custom_login'])):'/login/',
			'show_logged_in' 		=> $tmp_options['logged'],
			'show_login_button' 	=> $tmp_options['show_login_button'],
			'exclude_se'			=> $tmp_options['exclude_se'],
			'arrange' 				=> isset($_POST['signals_csmm_arrange'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_arrange'])):'logo,header,secondary,form,html',
			'analytics' 			=> isset($_POST['signals_csmm_analytics'])?sanitize_html_class(wp_unslash($_POST['signals_csmm_analytics'])):'',

			'mail_system_to_use'    => isset($_POST['mail_system_to_use'])?sanitize_html_class(wp_unslash($_POST['mail_system_to_use'])):'mc',
			'mailchimp_api'			=> isset($_POST['signals_csmm_api'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_api'])):'',
			'mailchimp_list' 		=> $tmp_options['list'],
			'message_noemail' 		=> isset($_POST['signals_csmm_message_noemail'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_message_noemail'])):'Please provide a valid email address.',
			'message_subscribed' 	=> isset($_POST['signals_csmm_message_subscribed'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_message_subscribed'])):'You are already subscribed!',
			'message_wrong' 		=> isset($_POST['signals_csmm_message_wrong'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_message_wrong'])):'Oops! Something went wrong.',
			'message_done' 			=> isset($_POST['signals_csmm_message_done'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_message_done'])):'Thank you! We\'ll be in touch!',

			'logo'					=> isset($_POST['signals_csmm_logo'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_logo'])):CSMM_URL . '/framework/public/img/mm-logo.png',
			'favicon'				=> isset($_POST['signals_csmm_favicon'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_favicon'])):'',
			'bg_cover' 				=> isset($_POST['signals_csmm_bg'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_bg'])):CSMM_URL . '/framework/public/img/mountain-bg.jpg',
			'content_overlay' 		=> $tmp_options['overlay'],
			'content_width'			=> isset($_POST['signals_csmm_width'])?absint(wp_unslash($_POST['signals_csmm_width'])):'600',
			'bg_color' 				=> isset($_POST['signals_csmm_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_color'])):'FFFFFF',
			'content_position'		=> isset($_POST['signals_csmm_position'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_position'])):'center',
			'content_alignment'		=> isset($_POST['signals_csmm_alignment'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_alignment'])):'left',
			'header_font' 			=> isset($_POST['signals_csmm_header_font'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_header_font'])):'Karla',
			'secondary_font' 		=> isset($_POST['signals_csmm_secondary_font'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_secondary_font'])):'Karla',
			'header_font_size' 		=> isset($_POST['signals_csmm_header_size'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_header_size'])):'28',
			'secondary_font_size' 	=> isset($_POST['signals_csmm_secondary_size'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_secondary_size'])):'14',
			'header_font_color' 	=> isset($_POST['signals_csmm_header_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_header_color'])):'FFFFFF',
			'secondary_font_color' 	=> isset($_POST['signals_csmm_secondary_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_secondary_color'])):'FFFFFF',
			'antispam_font_size' 	=> isset($_POST['signals_csmm_antispam_size'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_antispam_size'])):'13',
			'antispam_font_color' 	=> isset($_POST['signals_csmm_antispam_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_antispam_color'])):'BBBBBB',

			'input_text' 			=> isset($_POST['signals_csmm_input_text'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_input_text'])):'Enter your best email address',
            'button_text'           => isset($_POST['signals_csmm_button_text'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_button_text'])):'Subscribe',
			'gdpr_text' 			=> isset($_POST['signals_csmm_gdpr_text'])?sanitize_textarea_field(wp_unslash($_POST['signals_csmm_gdpr_text'])):'',
			'gdpr_fail' 			=> isset($_POST['signals_csmm_gdpr_fail'])?sanitize_textarea_field(wp_unslash($_POST['signals_csmm_gdpr_fail'])):'',
			'ignore_form_styles' 	=> $tmp_options['form_styles'],
			'input_font_size'		=> isset($_POST['signals_csmm_input_size'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_input_size'])):'13',
			'button_font_size'		=> isset($_POST['signals_csmm_button_size'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_button_size'])):'12',
			'input_font_color'		=> isset($_POST['signals_csmm_input_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_input_color'])):'FFFFFF',
			'button_font_color'		=> isset($_POST['signals_csmm_button_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_button_color'])):'FFFFFF',
			'input_bg'				=> isset($_POST['signals_csmm_input_bg'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_input_bg'])):'',
			'button_bg'				=> isset($_POST['signals_csmm_button_bg'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_button_bg'])):'0F0F0F',
			'input_bg_hover'		=> isset($_POST['signals_csmm_input_bg_hover'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_input_bg_hover'])):'',
			'button_bg_hover'		=> isset($_POST['signals_csmm_button_bg_hover'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_button_bg_hover'])):'0A0A0A',
			'input_border'			=> isset($_POST['signals_csmm_input_border'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_input_border'])):'EEEEEE',
			'button_border'			=> isset($_POST['signals_csmm_button_border'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_button_border'])):'0F0F0F',
			'input_border_hover'	=> isset($_POST['signals_csmm_input_border_hover'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_input_border_hover'])):'BBBBBB',
			'button_border_hover'	=> isset($_POST['signals_csmm_button_border_hover'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_button_border_hover'])):'0A0A0A',
			'success_background'	=> isset($_POST['signals_csmm_success_bg'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_success_bg'])):'90C695',
			'success_color'			=> isset($_POST['signals_csmm_success_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_success_color'])):'FFFFFF',
			'error_background'		=> isset($_POST['signals_csmm_error_bg'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_error_bg'])):'E08283',
			'error_color'			=> isset($_POST['signals_csmm_error_color'])?wp_strip_all_tags(wp_unslash($_POST['signals_csmm_error_color'])):'FFFFFF',
            'form_placeholder_color'=> isset($_POST['form_placeholder_color'])?wp_strip_all_tags(wp_unslash($_POST['form_placeholder_color'])):'DEDEDE',

            'disable_settings'      => $tmp_options['disabled'],
			'disable_adminbar' 		=> $tmp_options['disable_adminbar'],
            //phpcs:ignore as we allow users to enter whatever HTML and CSS they need 
			'custom_html'			=> isset($_POST['form_placeholder_color'])?wp_unslash($_POST['signals_csmm_html']):'', //phpcs:ignore
			'custom_css'			=> isset($_POST['form_placeholder_color'])?wp_unslash($_POST['signals_csmm_css']):''  //phpcs:ignore
		);

    $update_options = stripslashes_deep($update_options);

		// Updating the options in the database and showing message to the user
		update_option( 'signals_csmm_options', $update_options );
		$signals_csmm_err = '<div class="signals-alert signals-alert-info"><strong>Great!</strong> Options have been updated.<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

    wp_cache_flush();

    if (function_exists('w3tc_flush_all')) {
      w3tc_flush_all();
    }
    if (function_exists('wp_cache_clear_cache')) {
      wp_cache_clear_cache();
    }
    if (method_exists('LiteSpeed_Cache_API', 'purge_all')) {
      LiteSpeed_Cache_API::purge_all();
    }
    if (class_exists('Endurance_Page_Cache')) {
      $epc = new Endurance_Page_Cache;
      $epc->purge_all();
    }
    if (class_exists('SG_CachePress_Supercacher') && method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
      SG_CachePress_Supercacher::purge_cache(true);
    }
    if (class_exists('SiteGround_Optimizer\Supercacher\Supercacher')) {
      SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
    }
    if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
      $GLOBALS['wp_fastest_cache']->deleteCache(true);
    }
    if (is_callable(array('Swift_Performance_Cache', 'clear_all_cache'))) {
      Swift_Performance_Cache::clear_all_cache();
    }
    if (is_callable(array('Hummingbird\WP_Hummingbird', 'flush_cache'))) {
      Hummingbird\WP_Hummingbird::flush_cache(true, false);
    }
    if (function_exists('rocket_clean_domain')) {
      rocket_clean_domain();
    }
    do_action('cache_enabler_clear_complete_cache');
	}


	// Grab options from the database
  $signals_csmm_options = csmm_get_options();
	$meta = csmm_get_meta();

	// View template for the settings panel
	require 'views/settings.php';
} // csmm_admin_settings


// AJAX request for user support
function csmm_ajax_support() {

	// We are going to store the response in the $response() array
	$response = array(
		'code' 		=> 'error',
		'response' 	=> __( 'Please fill in both the fields to create your support ticket.', 'minimal-coming-soon-maintenance-mode' )
	);


	// Sending proper headers and sending the response back in the JSON format
	header( "Content-Type: application/json" );
	echo json_encode( $response );


	// Exiting the AJAX function. This is always required
	exit();

}
add_action( 'wp_ajax_signals_csmm_support', 'csmm_ajax_support' );
add_action( 'wp_ajax_csmm_rate_hide', 'csmm_rate_hide' );
add_action( 'wp_ajax_csmm_welcome_hide', 'csmm_welcome_hide' );
add_action( 'wp_ajax_csmm_olduser_hide', 'csmm_olduser_hide' );
add_action( 'wp_ajax_csmm_dismiss_pointer', 'csmm_dismiss_pointer_ajax');

function csmm_rate_hide() {
  check_ajax_referer('csmm_notice_nonce');
  set_transient('csmm_rate_hide', true, DAY_IN_SECONDS * 700);

  wp_send_json_success();
} // csmm_rate_hide

function csmm_welcome_hide() {
  check_ajax_referer('csmm_notice_nonce');
  set_transient('csmm_welcome_hide', true, DAY_IN_SECONDS * 700);

  wp_send_json_success();
} // csmm_welcome_hide

function csmm_olduser_hide() {
  check_ajax_referer('csmm_notice_nonce');
  set_transient('csmm_olduser_hide', true, DAY_IN_SECONDS * 700);

  wp_send_json_success();
} // csmm_olduser_hide
