<div class="wrap">
    <h1>Compose Newsletter - <?php echo wp_kses_post(WLWCN_NAME) ?></h1>
    <?php require_once __DIR__.'/../alert.php' ?>
    <form class="form-newsletter" data-confirmed="0" method="post" action="<?php echo esc_html(admin_url('admin-post.php')) ?>">
        <?php wp_nonce_field('wlwcn_update_newsletter', 'wlwcn_wpnonce'); ?>
        <input type="hidden" name="type" value="newsletter">
        <input type="hidden" name="id" value="<?php echo esc_attr($data['item']->id) ?>">
        <input type="hidden" name="action" value="wlwcn_update_newsletter">
        <h2>Create newsletter here.</h2>
        <?php require_once __DIR__.'/../validation_errors.php' ?>
        <div class="row">
            <div class="col-12 col-md-8 col-lg-10 col-xl-6">
                <?php
                $item = $data['item'];
                $mailing_lists = $data['mailing_lists'];
                $subscribers = $data['subscribers'];
                ?>
                <div class="form-group">
                    <fieldset>
                        <label for="subject" class="d-block bold">Enter Subject:</label>
                        <input name="subject" type="text" value="<?php echo wlwcn_old('subject', $item->subject) ?>" class="regular-text" required/>
                    </fieldset>
                </div>
                <div class="form-group">
                    <label class="label d-block d-bold">Message:</label>
                    <p class="help-text">The <b>[Subscriber Name]</b> will be automatically replaced by respective names (full name if available or first or last name if not) of the subscriber, or it will be replaced by <b>there</b> if the subscriber first and last names are not set.</p>
                     <?php
                     $old_msg = wlwcn_old('message', $item->message) ? wlwcn_old('message', $item->message) : 'Hello [Subscriber Name],';
                     $settings = [
                             'textarea_name' => 'message',
                             'media_buttons' => true
                         ];
                     wp_editor($old_msg , 'message', $settings);
                     ?>
                </div>
                <div class="form-group">
                    <?php
                    $none_check = $selected_list_check = $selected_check = $all_check = '';
                    $mailing_list_select_class = $subscriber_select_class = 'd-none';
                    $radio_required = $select_required = '';
                    $old_mailing_list = $old_subscriber = '';

                    $receiver_options = $item->receiver_options;

                    if(wlwcn_issetOld())
                    {
                        $old_send_to = wlwcn_old('send_to');

                        if(wlwcn_old('mailing_list'))
                        {
                            $old_mailing_list = wlwcn_old('mailing_list');
                        }
                        else if(wlwcn_old('subscriber'))
                        {
                            $old_subscriber = wlwcn_old('subscriber');
                        }
                    }
                    else if(isset($receiver_options[0]) && ($receiver_options[0] == 'all') )
                    {
                        $old_send_to = 'all';
                    }
                    else if(isset($receiver_options['selected_mailing_list']))
                    {
                        $old_send_to = 'selected_mailing_list';
                        $old_mailing_list = $receiver_options['selected_mailing_list'];
                    }
                    else if(isset($receiver_options['selected_subscriber']))
                    {
                        $old_send_to = 'selected_subscriber';
                        $old_subscriber = $receiver_options['selected_subscriber'];
                    }
                    else
                    {
                        $old_send_to = 'none';
                    }

                    if($old_send_to == 'none')
					{
						$none_check = 'checked';
					}
                    else if($old_send_to == 'selected_mailing_list')
                    {
                        $selected_list_check = 'checked';
                        $mailing_list_select_class = '';
                    }
                    else if($old_send_to == 'selected_subscriber')
                    {
                        $selected_check = 'checked';
                        $subscriber_select_class = '';
                    }
                    else if($old_send_to == 'all')
                    {
                        $all_check = 'checked';
                    }
                    ?>
                    <fieldset class="">
                        <label class="label">Send to:</label>
                    </fieldset>
                    <fieldset class="">
                    	<label class="radio-checkbox block">
                    		<input class="child-type" type="radio" name="send_to" value="none" <?php echo $none_check ?> required/>
                    		<span>No one, just save now</span>
                    	</label>
                    	<label class="radio-checkbox block">
                    		<input class="child-type" type="radio" name="send_to" value="selected_mailing_list" <?php echo $selected_list_check ?> required/>
                    		<span>Selected Mailing List</span>
                    	</label>
                        <label class="radio-checkbox block">
                    		<input class="child-type" type="radio" name="send_to" value="selected_subscriber" <?php echo $selected_check ?> required/>
                    		<span>Selected Subscriber</span>
                    	</label>
                    	<label class="radio-checkbox block">
                    		<input class="child-type" type="radio" name="send_to" value="all" <?php echo $all_check ?> required/>
                    		<span>All Subscribers</span>
                    	</label>
                        <p class="premium-text m-t-0">Please <a href="https://checkout.freemius.com/mode/dialog/plugin/11618/plan/19815/?trial=paid&coupon=free2premium" target="_blank">upgrade to our premium version</a> to be able to send newsletter to <b>multiple mailing lists</b>, <b>all mailing lists except a particular one</b>, <b>multiple selected subscribers</b>, <b>all subsccribers except selected ones</b> and for more features.</p>
                    </fieldset>

                    <fieldset class="child-1 select2-wlwcn-common wlwcn-mailing-lists <?php echo $mailing_list_select_class ?>">
                        <label for="">Select Mailing List</label>
                        <span class="d-block">
                            <select class="select2-wlwcn mailing-list" name="mailing_list">
                                <option value="">Select one item</option>
                                <?php foreach($mailing_lists as $key => $row) { ?>
                                <option value="<?php echo $row->id ?>" <?php echo ($row->id == $old_mailing_list) ? 'selected' : '' ?>><?php echo $row->title.' ('.$row->subscribers_count.')' ?></option>
                                <?php } ?>
                            </select>
                        </span>
                    </fieldset>

                    <fieldset class="child-1 select2-wlwcn-common wlwcn-subscribers <?php echo $subscriber_select_class ?>">
                        <label for="">Select Subscriber</label>
                        <span class="d-block">
                            <select class="select2-wlwcn subscribers" name="subscriber">
                                <option value="">Select one item</option>
                                <?php
                                foreach($subscribers as $key => $row)
                                {
                                    $name = wlwcn_get_fullname($row->f_name, $row->l_name);
                                    $email_name = wlwcn_formatEmailName($row->email, $row->f_name, $row->l_name);
                                ?>
                                <option value="<?php echo $row->id ?>" <?php echo ($row->id == $old_subscriber) ? 'selected' : '' ?>><?php echo $email_name ?></option>
                                <?php } ?>
                            </select>
                        </span>
                    </fieldset>
                </div>
                <div class="form-group">
                    <fieldset>
                        <?php
                        $now_checked = $later_checked = $now_disabled = '';

                        if($old_send_to == 'none')
                        {
                            $now_disabled = 'disabled';
                            $later_checked = 'checked';
                        }
                        else
                        {
                            if(wlwcn_issetOld())
                            {
                                if(wlwcn_old('when_to_send') == 'now')
                                {
                                    $now_checked = 'checked';
                                }
                                else if(wlwcn_old('when_to_send') == 'later')
                                {
                                    $later_checked = 'checked';
                                }
                            }
                        }
                        ?>
                        <label for="" class="d-block bold">When to send?</label>
                        <label class="radio-checkbox inline m-l-0">
                    		<input class="" type="radio" name="when_to_send" value="now" <?php echo $now_checked ?> <?php echo $now_disabled ?> required />
                    		<span>Now</span>
                    	</label>
                    	<label class="radio-checkbox inline">
                    		<input class="" type="radio" name="when_to_send" value="later" <?php echo $later_checked ?> required />
                    		<span>Later</span>
                    	</label>
                    </fieldset>
                    <fieldset class="child-1 wlwcn-dt-container <?php echo (wlwcn_old('when_to_send') != 'later') ? 'd-none' : '' ?>">
                        <label for="datetime" class="d-block bold">Select Date-Time</label>
                        <p class="help-text">Leave blank to just save and send later at your convinient time.</p>
                        <input id="datetime" name="datetime" type="text" value="" class="regular-text" placeholder="YYYY-MM-DD HH:MM (24 Hr format)" disabled/>
                        <input type="hidden" name="timezone" value="<?php echo wlwcn_old('timezone') ?>">
                        <p class="premium-text m-t-5">Please <a href="https://checkout.freemius.com/mode/dialog/plugin/11618/plan/19815/?trial=paid&coupon=free2premium" target="_blank">upgrade to our premium version</a> to be able to use this feature to automatically send newsletters at your pre-scheduled date-time.</p>
                    </fieldset>
                </div>
            </div>
        </div>
        <button type="submit" class="button-primary">Save Changes</button>
    </form>
</div>
