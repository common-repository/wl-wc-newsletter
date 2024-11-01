<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php require_once 'alert.php' ?>
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
        <h2>Update newsletter subscription settings here.</h2>
        <?php require_once 'validation_errors.php' ?>
        <div class="row">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div class="form-group">
                    <label class="label">Enable Newsletter Subscription?</label>
                    <p class="help-text">Checking this will allow customers to subscribe to the newsletter from Checkout, Registration & Account Details (if logged in) pages.</p>
                    <label for="enable_subscription">
                        <input type="checkbox" name="enable_subscription" id="enable_subscription" value="1" <?php echo ($enable_subscription) ? 'checked' : '' ?>> Yes
                    </label>
                </div>
                <br>

               <div class="form-group">
                   <label class="label">Enable Subscription Coupon Offer?</label>
                   <p class="help-text">By checking this coustomers will get a unique one time discount coupon code to their email address. They won't be sent a separate new coupon code even if they unsubscribe & subscribe back again.</p>
                   <label for="enable_subscription_offer">
                       <input type="checkbox" name="enable_subscription_offer" id="enable_subscription_offer" value="1" <?php echo ($enable_subscription_offer) ? 'checked' : '' ?>> Yes
                   </label>
               </div>
               <br>

               <div class="form-group">
                   <label class="label">Discount Coupon Type</label>
                   <br />
                  <select style="" id="discount_type" disabled>
                       <option value="percent" selected>Percentage Discount</option>
                   </select>
               </div>
               <br>

               <div class="form-group">
                    <label class="label">Discount Coupon Amount</label>
                    <br />
                    <div class="input-group">
                        <input type="number" value="5" class="small-text" disabled />
                        <span class="symbol post">%</span>
                    </div>
                </div>
               <br>

               <div class="form-group">
                   <label class="label">Expires In</label>
                    <p class="help-text">The no. of days after which the coupon will expire.</p>
                   <div class="input-group">
                       <input type="number" value="30" class="small-text" disabled/>
                       <span class="symbol post">Days</span>
                   </div>
               </div>
               <br>

               <div class="form-group">
                   <p class="premium-text m-t-0"><big>Please <a href="https://checkout.freemius.com/mode/dialog/plugin/11618/plan/19815/?trial=paid&coupon=free2premium" target="_blank">upgrade to our premium version</a> to be able to customize (edit/change) the above fields and unlock many other useful &amp; helpful features.</big></p>
               </div>

               <div class="form-group">
                   <label class="label">From Email</label>
                    <p class="help-text">Enter the email from which you'd like to send email to the subscriber.</p>
                    <input name="from_email" type="email" value="<?php echo esc_attr(wlwcn_old('from_email', $from_email)) ?>" class="regular-text" required />
               </div>
               <br>

               <div class="form-group">
                   <label class="label">Reply To Email</label>
                   <p class="help-text">Enter the email to which you'd like the subscriber to contact you while replying to the coupon email.</p>
                   <input name="replyto_email" type="text" value="<?php echo esc_attr(wlwcn_old('replyto_email', $replyto_email)) ?>" class="regular-text" required />
               </div>
               <br>

               <div class="form-group">
                   <label class="label">Subscription Details:</label>
                   <p class="help-text">The information below will be displayed to customer while subscribing (This content can work like terms of subscription).</p>
                    <?php
                    $settings = [
                            'textarea_name' => 'subscription_details',
                            'media_buttons' => false,
                            'teeny' => true
                        ];
                    $subscription_details = wlwcn_old('subscription_details', $subscription_details);
                    wp_editor($subscription_details , 'subscription_details', $settings);
                    ?>
               </div>
               <input type="hidden" name="action" value="wlwcn_update_settings">
               <?php
               wp_nonce_field( 'wlwcn_update_settings', 'wlwcn_wpnonce' );
               submit_button();
               ?>
            </div>
        </div>
    </form>
</div>
