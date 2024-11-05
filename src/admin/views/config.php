<div class="mailbiz-config">
  <form action="<?php echo esc_url(menu_page_url('mailbiz-woocommerce-tracker', false)) ?>" method="post">
    <input type="hidden" name="action" value="mailbiz-update-admin-config">
    <?php wp_nonce_field(Mailbiz_Admin::NONCE); ?>
    <h3>Habilitar integração com a Mailbiz</h3>
    <span class="integration-enable">
      <input id="integration-enable" name="integration-enable" type="checkbox"
        <?php echo esc_attr(get_option('mailbiz_integration_enable')) == 'true' ? 'checked' : '' ?>>
    </span>
    <h3>Chave de integração</h3>
    <span class="integration-key">
      <input id="integration-key" name="integration-key" type="text" size="24"
        value="<?php echo esc_attr(get_option('mailbiz_integration_key')); ?>">
    </span>
    <h3>Habilitar Jornadas</h3>
    <span class="integration-journey">
      <input id="integration-journey" name="integration-journey" type="checkbox"
        <?php echo esc_attr(get_option('mailbiz_integration_journey')) == 'true' ? 'checked' : '' ?>>
    </span>
    <div style="margin-top: 1em">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Salvar">
    </div>
  </form>
</div>