<?php
class TGLogin{

  private $user;
  private $action;
  private $bot_token;
  public $error;
  public $msg;

  function __construct($user, $action){
    $this->user = $user;
    $this->action = $action;
    $this->bot_token = esc_attr( get_option('bot_token'));
    $this->error = false;
  }
  /* This function check if the data returned form Telegram Login is valid.
  * If not throw new exceptions:
  * Data is NOT from Telegram: the hash cheking is fail.
  * Data is outdated: the hash is valid only for 24 hours from calling Telegram Login API
  */
  private function checkTelegramAuthorization() {
    $check_hash = $this->user['hash'];
    unset($this->user['hash']);
    $data_check_arr = [];
    foreach ($this->user as $key => $value) {
      $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);
    $secret_key = hash('sha256', $this->bot_token, true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);
    if (strcmp($hash, $check_hash) !== 0) {
      throw new Exception(__('Data is NOT from Telegram', 'wp-tg-login'));
    }
    if ((time() - $auth_data['auth_date']) > 86400) {
      throw new Exception(__('Data is outdated', 'wp-tg-login'));
    }
    $auth_data['action'] = $action;
    return $auth_data;
  }

  private function registerUser(){
    global $wpdb;
    $userdata['user_login'] = $this->user['username'];
    $userdata['first_name'] = htmlspecialchars($this->user['first_name']);
    $userdata['id'] = $this->user['id'];
    if(!empty($this->user['last_name'])){
      $userdata['last_name'] = htmlspecialchars($this->user['last_name']);
    }
    $userdata['user_pass'] = NULL;
    $user_id = wp_insert_user( $userdata ) ;
    add_user_meta( $user_id, 'tg_login_id', $this->user['id'] );
    add_user_meta( $user_id, 'tg_login_username', $this->user['username'] );

    if ( ! is_wp_error( $user_id ) ) {
        $msg = __( "Thanks for sign up using Telegram Login for Websites.<br />
        Your username as the same of your Telegram username (<b>%s</b>).<br />
        <a href=\"%s\">Click here to write your password.</a>", 'wp-tg-login' );
        $user = get_user_by('id', $user_id);
        $rp_key = get_password_reset_key($user);
        $rp_link = get_site_url()."/wp-login.php?action=rp&key=$rp_key&login=".rawurlencode($userdata['user_login']);
        $this->msg = sprintf( wp_kses($msg, array( 'br' => array(), 'b' => array(), 'a' => array( 'href' => array() ) ) ), $userdata['user_login'], esc_url( $rp_link ) );
    }else{
      $this->error = true;
      $this->msg = __('Sorry, this Telegram account as linked to another user account or a error ocurred to create.', 'wp-tg-login');
    }
  }

  private function loginUser(){
    if ($user = $this->checkUserExists()){
      echo $user;
      wp_set_auth_cookie($user->ID);
      $this->msg = __('Sign in correctly', 'wp-tg-login');
    }else{
      $this->msg = __("This Telegram account isn't linked to any site account. If you have account, login with your user and vinculed your Telegram account on your user panel.", 'wp-tg-login');
      $this->error = true;
    }
  }

  private function linkUser(){
    if ($user = $this->checkUserExists()){
      $this->msg = __("This Telegram account is already linked to another user account. Verify that you already have it linked in your user's configuration panel. If not, contact an administrator of this site.", 'wp-tg-login');
      $this->error = true;
    }else{
      $user = wp_get_current_user();
      add_user_meta( $user->ID, 'tg_login_id', $this->user['id'] );
      add_user_meta( $user->ID, 'tg_login_username', $this->user['username'] );
      $this->msg = __('Account link correctly', 'wp-tg-login');
    }
  }

  private function unlinkUser(){
    $user = wp_get_current_user();
    delete_user_meta( $user->ID, 'tg_login_id');
    delete_user_meta( $user->ID, 'tg_login_username');
    $this->msg = __('Account unlink correctly', 'wp-tg-login');
  }

  private function checkUserExists(){
    if($this->checkTelegramAuthorization()){
      $user = get_users(
      array(
        'meta_key' => 'tg_login_id',
        'meta_value' => $this->user['id'],
        'number' => 1,
        'count_total' => false
      ));

      if ( ! is_wp_error($user) ){
        if(empty ($user)){
          return false;
        }else{
          return $user[0];
        }
      }
    }
  }

  public function getTelegramUserData() {
    if($this->action == 'login'){
      $this->loginUser();
    }else if($this->action == 'register'){
      $this->registerUser();
    }else if($this->action == 'link'){
      $this->linkUser();
    }else if($this->action == 'unlink'){
      $this->unLinkUser();
    }
  }
}

?>
