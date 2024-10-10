<div class="mailbiz__settings">
  <h3>Chave de integração</h3>
  <form action="<?php echo esc_url(menu_page_url('mailbiz-woocommerce-tracker', false)) ?>" method="post">
    <?php wp_nonce_field(Mailbiz_Admin::NONCE); ?>
    <input type="hidden" name="action" value="set-integration-key">
    <span class="integration-key">
      <input id="integration-key" name="integration-key" type="text" size="24"
        value="<?php echo esc_attr(get_option('mailbiz_integration_key')); ?>" class="">
    </span>
    <input type="submit" name="submit" id="submit" class="button button-primary" value="Salvar">
  </form>
</div>