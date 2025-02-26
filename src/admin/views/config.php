<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

?>

<div class="mbz-config">
  <form action="<?php echo esc_url(menu_page_url(MAILBIZ_PLUGIN_SLUG, false)) ?>" method="post">
    <input type="hidden" name="action" value="mailbiz-update-admin-config">
    <?php wp_nonce_field(Mailbiz\Admin::NONCE); ?>
    <div class="mbz-logo">
      <img src="<?php echo esc_url(MAILBIZ_PLUGIN_URL) ?>/admin/images/brand-mailbiz.png">
    </div>
    <div class="mbz-form">
      <div class="mbz-form-line-switch">
        <div class="mbz-switch-input">
          <input id="integration-enable" name="integration-enable" type="checkbox" <?php echo esc_attr(get_option('mailbiz_integration_enable')) === 'yes' ? 'checked' : '' ?>>
        </div>
        <div class="mbz-switch-input-label">Habilitar integração com a Mailbiz</div>
      </div>
      <hr>
      <div class="mbz-form-line-switch">
        <div class="mbz-switch-input">
          <input id="journey-enable" name="journey-enable" type="checkbox" <?php echo esc_attr(get_option('mailbiz_journey_enable')) === 'yes' ? 'checked' : '' ?>>
        </div>
        <div class="mbz-switch-input-label">Habilitar o módulo Jornadas</div>
      </div>
      <hr>
      <div class="mbz-form-line-text">
        <div class="mbz-text-input-label">Chave da integração</div>
        <div class="mbz-text-input">
          <input id="integration-key" name="integration-key" type="text" size="24"
            value="<?php echo esc_attr(get_option('mailbiz_integration_key')); ?>">
        </div>
      </div>
    </div>
    <div class="mbz-button">
      <input type="submit" name="submit" id="submit" value="Salvar" onclick="this.value = 'Salvando...'">
    </div>
  </form>
</div>