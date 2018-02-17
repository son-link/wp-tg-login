<h1><?=__('WordPress Telegram Login Options', 'wp-tg-login')?></h1>
<p>
  <?=__('For use this plugin you need a Telegram bot.', 'wp-tg-login')?> <a href="https://github.com/son-link/wp-tg-login/wiki"><?=__('Click here for how to', 'wp-tg-login')?></a>
  <!--'Para usar este plugin necesita crear un Bot en Telegram.')?> <a href="#">Aquí puede ver como hacerlo.</a-->
</p>
<form method="post">
  <?php settings_fields( 'tg_login_opts' ); ?>
  <?php do_settings_sections( 'tg_login_opts' ); ?>
  <table class="form-table">
    <tr>
      <td>
        <?=__("Bot's name", 'wp-tg-login')?>:
      </td>
      <td>
        <input type="text" name="bot_name" value="<?php echo esc_attr( get_option('bot_name') ); ?>" />
      </td>
    </tr>
    <tr>
      <td>
        <?=__("Bot's token", 'wp-tg-login')?>:
      </td>
      <td>
        <input type="text" name="bot_token" value="<?php echo esc_attr( get_option('bot_token') ); ?>" />
      </td>
    </tr>
    <tr>
      <td>
        <?php submit_button(); ?>
      </td>
    </tr>
  </table>
</form>
