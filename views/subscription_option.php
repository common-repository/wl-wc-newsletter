<p class="form-row form-row-wide subscribe-newsletter <?php echo isset($fg_class) ? esc_attr($fg_class) : '' ?>">
    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
        <input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="subscribe_newsletter" <?php checked(true, $checked, true); ?> type="checkbox" name="subscribe_newsletter" value="1" />
        <span><?php esc_html_e( 'Subscribe to our newsletter?', 'woocommerce' ); ?></span>
        <?php if($subs_details) { ?>
        <span class="fw-normal wd-100-pct d-block lh-1-5"><?php echo wp_kses_post($subs_details) ?></span>
        <?php } ?>
    </label>
</p>
