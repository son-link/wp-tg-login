<?php
/*
  Plugin Name: WordPress Telegran Login
  Plugin URI: https://github.com/son-link/wp-tg-login
  Description: Social login using Telegram Login for Websites.
  Version: 0.1.0
  Author: Alfonso Saavedra "Son link"
  Author URI: https://son-link.github.io
  Text Domain: wp-tg-login
  Domain Path: /languages
 */

/* Load the translations
*/
load_plugin_textdomain( 'wp-tg-login', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/* Update the plugin options on submit
*/
if (!empty($_POST['action']) && $_POST['action'] == 'update'){
  update_option( 'bot_name', $_POST['bot_name'] );
  update_option( 'bot_token', $_POST['bot_token'] );
}

/* Shortcode for insert Telegram Login script on other pages
* [tg login action="login|register|link" text="text to show"]
* action and text are optional. action by default is login.
*/
add_shortcode( 'tg_login', 'tg_login_sc' );
function tg_login_sc($attr){
  $action = 'login';
  if (!empty($attr['action'])) $action = $attr['action'];
  if (!empty($attr['text'])) $text = $attr['text'];
  else $text = __('Or login using your Telegram account', 'wp-tg-login');
  ob_start();
	?>
  <p>
    <?=$text?>:
    <br />
    <script async src="https://telegram.org/js/telegram-widget.js?2" data-telegram-login="<?= esc_attr( get_option('bot_name') )?>" data-size="medium" data-onauth="tg_login_cb(user, '<?=$action?>')"></script>
    <div id="tg-spinner"></div>
    <div id="tg-login-msg">
      <a href="#"></a>
      <span></span>
    </div>
  </p>
  <?php
	return ob_get_clean();
}

// Adding Telegram Login script to WP login Form
add_action( 'login_form', 'add_tg_login_script' );
function add_tg_login_script() {
    echo do_shortcode('[tg_login action="login"]');
}

// The same of after but for the register form
add_action( 'register_form', 'add_tg_register_script' );
function add_tg_register_script() {
  echo do_shortcode('[tg_login action="register" text="'.__('Or create using your Telegram account', 'wp-tg-login').'"]');
}

// Add the script for (un)link Telegram account on the user panel
add_action( 'edit_user_profile', 'extra_user_profile_fields' );
add_action( 'show_user_profile', 'extra_user_profile_fields' );
function extra_user_profile_fields( $user ) { ?>
  <h3>Telegram Login</h3>
  <?php
  if(get_user_meta($user->ID, 'tg_login_id')){
    printf( esc_html__( 'This account is linked to this Telegram user: %s.', 'wp-tg-login' ), get_user_meta($user->ID, 'tg_login_username')[0] );
    echo '&nbsp;<a href="#" id="unlink_tg">'.__('Unlink account', 'wp-tg-login')."</a>";
  }else{
    echo do_shortcode('[tg_login action="link" text="'.__('Link your Telegram account for login with him', 'wp-tg-login').'"]');
  }
}

// Add dashboard menu option for configure the plugin
add_action('admin_menu', 'tg_login_menu');
function tg_login_menu(){
	add_menu_page(__('Telegram Login Options', 'wp-tg-login'), __('Telegram Login', 'wp-tg-login'), 'manage_options', 'wp_tg_login', 'tg_login_opt', plugin_dir_url( __FILE__ ).'telegram_logo_menu.png' );
  add_action( 'admin_init', 'tg_login_add_options' );
}

// Register the new WP options used by the plugin
function tg_login_add_options() {
  register_setting( 'tg_login_opts', 'bot_name' );
  register_setting( 'tg_login_opts', 'bot_token' );
}

// Show the form for configure the puglin in the plugin page
function tg_login_opt(){
  require('tg_login_options.php');
}

// Add the javascript and CSS in dashboard, site, login and register pages
add_action( 'wp_enqueue_scripts', 'ajax_tg_enqueue_scripts' );
add_action( 'login_enqueue_scripts', 'ajax_tg_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'ajax_tg_enqueue_scripts' );
function ajax_tg_enqueue_scripts() {
  wp_enqueue_style('wp-tg-spinner', plugin_dir_url( __FILE__ ) . 'wp-tg-login.css');
  wp_register_script('wp-tg-ajax', plugin_dir_url( __FILE__ ) . 'wp-tg-login.js', array('jquery') );
  wp_enqueue_script('wp-tg-ajax');

  wp_localize_script( 'wp-tg-ajax', 'tg_login_ajax', array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
  ));
}

// This is the action called on AJAX call for submit and retrieve data from Telegram Login
add_action( 'wp_ajax_tg_ajax_login', 'tg_ajax_login' );
add_action( 'wp_ajax_nopriv_tg_ajax_login', 'tg_ajax_login' );
function tg_ajax_login() {
  $user = $_GET['user'];
  $action = $_GET['type'];

  require('tg_login.php');

  $tglogin = new TGLogin($user, $action);
  $tglogin->getTelegramUserData();
  $base_url = get_site_url();
  if($action == 'link' || $action == 'unlink') $base_url = get_edit_user_link();
  if ($tglogin->msg){
    if ($tglogin->error){
      wp_send_json_error(
        array('msg' => $tglogin->msg)
      );
    }else{
      wp_send_json_success(
        array('msg' => $tglogin->msg, 'base_url' => $base_url)
      );
    }
  }
  exit();
}
