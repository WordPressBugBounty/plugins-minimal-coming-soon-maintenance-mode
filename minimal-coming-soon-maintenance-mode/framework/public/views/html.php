<?php

/**
 * Renders the html template for the plugin.
 *
 * @link       http://www.webfactoryltd.com
 * @since      1.0
 */

if (!defined('WPINC')) {
    die;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php esc_html(stripslashes($options['description'])); ?>">
    <meta name="generator" content="Free Coming Soon & Maintenance Mode plugin for WordPress">
    <title><?php esc_html(stripslashes($options['title'])); ?></title>
    <?php if (isset($options['favicon']) && !empty($options['favicon'])) : ?>
        <link rel="shortcut icon" href="<?php echo esc_url($options['favicon']); ?>" />
    <?php endif; ?>
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php
    //we don't want to call wp_head to load any other enqueued scripts or styles so we load it directly
    echo '<link rel="stylesheet" type="text/css" href="' . esc_url(CSMM_URL) . '/framework/public/css/public.css" />'; //phpcs:ignore
    ?>

    <?php
        if(!in_array($options["header_font"], array('Arial','Helvetica','Georgia','Times New Roman','Tahoma','Verdana','Geneva')) || !in_array($options["secondary_font"], array('Arial','Helvetica','Georgia','Times New Roman','Tahoma','Verdana','Geneva'))){
        echo '<script src="' . esc_url(CSMM_URL) . '/framework/admin/js/webfont.js"></script>'; //phpcs:ignore
        ?>    
        <script>
            WebFont.load({
                bunny: {
                    families: ['<?php echo esc_attr($options["header_font"]); ?>', '<?php echo esc_attr($options["secondary_font"]); ?>']
                }
            });
        </script>
        <?php
        }
    ?>

    <?php require_once CSMM_PATH . '/framework/public/include/styles.php'; ?>

    <?php
    // analytics
    if (csmm_convert_ga($options['analytics'])) {
        echo "<script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
        ga('create', '" . esc_attr(csmm_convert_ga($options['analytics'])) . "', 'auto');
        ga('send', 'pageview');
      </script>";
    }
    ?>
</head>

<body class="signals-plugin">
    <div class="maintenance-mode">
        <div class="s-container">
            <div class="content">
                <?php

                // Logo
                if (!empty($options['logo'])) {
                    $signals_arrange['logo'] = '<div class="logo-container">' . "\r\n";
                    $signals_arrange['logo'] .= '<img title= "' . get_bloginfo('name') . '" alt= "' . get_bloginfo('name') . '" src="' . $options['logo'] . '" class="logo" />' . "\r\n";
                    $signals_arrange['logo'] .= '</div>' . "\r\n";
                }

                // Header text
                if (!empty($options['header_text'])) {
                    $signals_arrange['header'] = '<h1 class="header-text">' . stripslashes(nl2br($options['header_text'])) . '</h1>' . "\r\n";
                }

                // Secondary text
                if (!empty($options['secondary_text'])) {
                    $signals_arrange['secondary'] = '<p class="secondary-text">' . stripslashes(nl2br($options['secondary_text'])) . '</p>' . "\r\n";
                }

                // Form

                if (!empty($options['mailchimp_api']) && !empty($options['mailchimp_list']) && isset($options['mail_system_to_use']) && $options['mail_system_to_use'] == 'mc') {
                    // Checking if the form is submitted or not
                    $signals_email = '';
                    if (isset($_POST['signals_email']) && isset($_POST['csmm_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['csmm_nonce'])), 'submit_csmm')) {
                        // Processing begins
                        $signals_email = isset($_POST['signals_email'])?sanitize_email(wp_unslash($_POST['signals_email'])):'';

                        if ('' === $signals_email) {
                            $code         = 'danger';
                            $response     = $options['message_noemail'];
                        } elseif (!empty($options['gdpr_text']) && empty($_POST['gdpr_consent'])) {
                            $code     = 'danger';
                            $response = $options['gdpr_fail'];
                        } else {
                            $signals_email = filter_var(strtolower(trim($signals_email)), FILTER_SANITIZE_EMAIL);

                            if (strpos($signals_email, '@')) {
                                require_once CSMM_PATH . '/framework/admin/include/classes/class-mailchimp.php';

                                $MailChimp = new Signals_MailChimp($options['mailchimp_api']);
                                $api_url  = "/lists/" . $options['mailchimp_list'] . "/members";

                                $out_array =  array(
                                    'email_address' => $signals_email,
                                    'status' => 'pending'
                                );

                                $result = $MailChimp->post($api_url, $out_array);

                                if ($result['status'] == 400) {
                                    $code         = 'danger';
                                    if ($result['title'] == 'Member Exists') {
                                        $response     = $options['message_subscribed'];
                                    } else {
                                        $response     = $result['detail'];
                                    }
                                } elseif (isset($result['unique_email_id'])) {
                                    $code         = 'success';
                                    $response     = $options['message_done'];
                                }
                            } else {
                                $code             = 'danger';
                                $response         = $options['message_noemail'];
                            }
                        }
                    } // signals_email




                    // Subscription form
                    // Displaying errors as well if they are set
                    $signals_arrange['form'] = '<div class="subscription">';

                    if (isset($code) && isset($response)) {
                        $signals_arrange['form'] .= '<div class="signals-alert signals-alert-' . $code . '">' . $response . '</div>';
                    }

                    $signals_arrange['form'] .= '<form role="form" method="post">
							<input value="' . $signals_email . '" type="text" name="signals_email" autocomplete="email" placeholder="' . esc_attr($options['input_text']) . '">';
                    if ($options['gdpr_text']) {
                        $signals_arrange['form'] .= '<div class="gdpr_consent"><input type="checkbox" value="1" name="gdpr_consent" id="gdpr_consent"> <label for="gdpr_consent">' . $options['gdpr_text'] . '</label></div>';
                    }
                    $signals_arrange['form'] .= wp_nonce_field('submit_csmm', 'csmm_nonce', true, false);
                    $signals_arrange['form'] .= '<input type="submit" name="submit" value="' . esc_attr($options['button_text']) . '">
						</form>';

                    // antispam text
                    if (!empty($options['antispam_text'])) {
                        // The best part, we don't do spam!
                        $signals_arrange['form'] .= '<p class="anti-spam">' . stripslashes($options['antispam_text']) . '</p>';
                    }

                    $signals_arrange['form'] .= '</div>';
                } // mailchimp_api && mailchimp_list

                // Custom HTML
                $signals_arrange['html'] = stripslashes($options['custom_html']);

                // Let's show the sections now!
                if (!empty($options['arrange'])) {
                    $signals_sections = explode(',', $options['arrange']);
                } else {
                    $signals_sections = array('logo', 'header', 'secondary', 'form', 'html');
                }

                foreach ($signals_sections as $signals_section) {
                    if (isset($signals_arrange[$signals_section])) {
                        CSMM::wp_kses_wf($signals_arrange[$signals_section]);
                    }
                }

                ?>
            </div><!-- .content -->
            <?php CSMM::wp_kses_wf(csmm_linkback()); ?>
        </div><!-- .s-container -->
    </div><!-- .maintenance-mode -->
    <?php

    if (@$options['show_login_button'] == '1') {
        if (is_user_logged_in()) {
            echo '<div id="login-button" class="loggedin">';
            echo '<a title="' . esc_html__('Open WordPress admin', 'minimal-coming-soon-maintenance-mode') . '" href="' . esc_url(get_site_url()) . '/wp-admin/"><img src="' . esc_url(CSMM_URL) . '/framework/public/img/wp-logo-white.png" alt="' . esc_html__('Open WordPress admin', 'minimal-coming-soon-maintenance-mode') . '" title="' . esc_html__('Open WordPress admin', 'minimal-coming-soon-maintenance-mode') . '"></a>';
        } else {
            echo '<div id="login-button" class="loggedout">';
            echo '<a title="' . esc_html__('Log in to WordPress admin', 'minimal-coming-soon-maintenance-mode') . '" href="' . esc_url(get_site_url()) . '/wp-login.php"><img src="' . esc_url(CSMM_URL) . '/framework/public/img/wp-logo-white.png" alt="' . esc_html__('Log in to WordPress admin', 'minimal-coming-soon-maintenance-mode') . '" title="' . esc_html__('Log in to WordPress admin', 'minimal-coming-soon-maintenance-mode') . '"></a>';
        }
        echo '</div>';
    }
    ?>
</body>

</html>
