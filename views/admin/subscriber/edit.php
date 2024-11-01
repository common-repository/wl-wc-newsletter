<div class="wrap">
    <h1>Edit Subscriber - <?php echo wp_kses_post(WLWCN_NAME) ?></h1>
    <?php require_once __DIR__.'/../alert.php' ?>
    <form method="post" action="<?php echo esc_html(admin_url('admin-post.php')) ?>">
        <input type="hidden" name="type" value="subscriber">
        <input type="hidden" name="id" value="<?php echo esc_attr($item->id) ?>">
        <h2>Update subscriber details here.</h2>
        <?php require_once __DIR__.'/../validation_errors.php' ?>
        <div class="row">
            <div class="col-12 col-lg-8 col-xl-6">
                <div class="form-group">
                    <label class="label">Email</label>
                    <br>
                    <input name="email" type="email" value="<?php echo esc_attr($item->email) ?>" class="regular-text" required readonly/>
                </div>
                <br>

                <div class="form-group">
                    <label class="label">First Name</label>
                    <br>
                    <input name="f_name" type="text" value="<?php echo wlwcn_old('f_name', $item->f_name) ?>" class="regular-text" />
                </div>
                <br>

                <div class="form-group">
                    <label class="label">Last Name</label>
                    <br>
                    <input name="l_name" type="text" value="<?php echo wlwcn_old('l_name', $item->l_name) ?>" class="regular-text" />
                </div>
                <br>

                <div class="form-group">
                    <label class="label">Is Customer?</label>
                    <br>
                    <label for="is_customer">
                        <?php if($item->is_customer) { ?>
                        <input type="checkbox" name="is_customer" id="is_customer" value="1" <?php echo ($item->is_customer) ? 'checked' : '' ?> disabled> Yes
                        <?php } else { ?>
                            No
                        <?php } ?>
                    </label>
                </div>
                <br>

                <div class="form-group">
                    <label class="label">Is Member?</label>
                    <br>
                    <label for="is_member">
                        <?php if($item->is_member) { ?>
                        <input type="checkbox" name="is_member" id="is_member" value="1" <?php echo ($item->is_member) ? 'checked' : '' ?> disabled> Yes
                        <?php } else { ?>
                            No
                        <?php } ?>
                    </label>
                </div>
                <br>

                <div class="form-group">
                    <label class="label">Coupon Code</label>
                    <p class="help-text">Please make sure the coupon exists before entering here. You can create coupon from WooCommerce or Marketing menu.</p>
                    <input name="subscription_coupon" type="text" value="<?php echo wlwcn_old('subscription_coupon', $item->subscription_coupon) ?>" class="regular-text"/>
                </div>
                <br>

                <div class="form-group">
                    <label class="label d-block d-bold">Notes:</label>
                    <p class="help-text">Save anything that you need to keep in mind about this subscriber.</p>
                     <?php
                     $old_notes = wlwcn_old('notes', $item->notes) ? wlwcn_old('notes', $item->notes) : '';
                     $settings = [
                             'textarea_name' => 'notes',
                             'media_buttons' => true
                         ];
                     wp_editor($old_notes , 'notes', $settings);
                     ?>
                </div>

                <div class="form-group">
                    <label class="label">Subscribed At</label>
                    <br>
                    <input name="subscribed_at" type="text" value="<?php echo $item->subscribed_at ?>" class="regular-text" readonly/>
                </div>
                <input type="hidden" name="action" value="wlwcn_update_subscriber">
                <?php
                wp_nonce_field('wlwcn_update_subscriber', 'wlwcn_wpnonce');
                submit_button();
                ?>
            </div>
        </div>
    </form>
</div>
