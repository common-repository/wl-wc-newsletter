<div class="wrap">
    <h1>Newsletter Details - <?php echo wp_kses_post(WLWCN_NAME) ?></h1>
    <?php require_once __DIR__.'/../alert.php' ?>
    <h2>Details of the newsletter:</h2>
    <div class="row">
        <div class="col-12">
            <?php
            $item = $data['item'];
            $receiver = $data['receiver'];
            ?>
            <div class="row">
                <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                    <div class="form-group">
                        <fieldset>
                            <label class="d-block bold">Subject:</label>
                            <p class="m-t-0"><?php echo esc_attr(wp_unslash($item->subject)) ?></p>
                        </fieldset>
                    </div>
                    <div class="form-group">
                        <fieldset>
                            <label class="d-block bold">Message:</label>
                            <?php echo do_shortcode(wpautop(stripslashes($item->message))); ?>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <fieldset>
                    <label for="" class="d-block bold">Created On:</label>
                    <?php echo esc_attr($item->created_at->format('F j, Y, g:i a')) ?>
                </fieldset>
            </div>
            <?php if($item->sent_at) { ?>
            <div class="form-group">
                <fieldset>
                    <label class="d-block bold">Sent On:</label>
                    <?php echo esc_attr($item->sent_at->format('F j, Y, g:i a')) ?>
                </fieldset>
            </div>
            <?php if($item->subscribersSent->count()) { ?>
            <div class="form-group">
                <fieldset>
                    <label for="" class="d-block bold">Sent To:</label>
                    <?= $item->subscribersSent->count() ?> subscriber<?= ($item->subscribersSent->count() > 1) ? 's' : '' ?>
                    <div class="row">
                        <?php foreach($item->subscribersSent as $key => $row) { ?>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <span class="tag btn-success tag-newsletter">
                                <a href="<?php echo wlwcn_pluginBaseUrl('subscriber', ['type'=>'subscriber', 'action'=>'edit', 'id'=>$row->id]) ?>" class="" title="<?php echo $row->email ?> (<?= wlwcn_get_fullname($row->f_name, $row->l_name); ?>)">
                                    <?php echo wlwcn_str_limit($row->email, 35) ?>
                                </a>
                            </span>
                        </div>
                        <?php } ?>
                    </div>
                </fieldset>
            </div>
            <?php } ?>
            <?php if($item->subscribersUnsent->count()) { ?>
            <div class="form-group">
                <fieldset>
                    <label for="" class="d-block bold">Failed Sending To:</label>
                    <?= $item->subscribersUnsent->count() ?> subscriber<?= ($item->subscribersUnsent->count() > 1) ? 's' : '' ?>
                    <div class="row">
                        <?php foreach($item->subscribersUnsent as $key => $row) { ?>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                            <span class="tag btn-danger tag-newsletter">
                                <a href="<?php echo wlwcn_pluginBaseUrl('subscriber', ['type'=>'subscriber', 'action'=>'edit', 'id'=>$row->id]) ?>" class="" title="<?php echo $row->email ?> (<?= wlwcn_get_fullname($row->f_name, $row->l_name); ?>)">
                                    <?php echo wlwcn_str_limit($row->email, 35) ?>
                                </a>
                            </span>
                        </div>
                        <?php } ?>
                    </div>
                </fieldset>
            </div>
            <?php } ?>
            <?php } else { ?>
            <div class="form-group">
                <fieldset>
                    <label for="" class="d-block bold">Intended To:</label>
                    <div class="row">
                        <div class="col-12">
                            <?php if(isset($item->receiver_options[0]) && ($item->receiver_options[0] == 'all')) { ?>
                                <span class="tag btn-primary tag-newsletter">All Subscribers</span>
                            <?php } else if(isset($item->receiver_options['selected_mailing_list'])) { ?>
                                <span class="tag btn-info tag-newsletter">
                                    <a href="<?php echo wlwcn_pluginBaseUrl('mailing-list', ['type'=>'mailing-list', 'id'=>$receiver->id]) ?>" class="" title="Click to see the subscribers in the mailing list"><?php echo $receiver->title." (".$receiver->subscribers_count.")" ?></a>
                                </span>
                            <?php } else if(isset($item->receiver_options['selected_subscriber'])) { ?>
                                <span class="tag btn-success tag-newsletter">
                                    <a href="<?php echo wlwcn_pluginBaseUrl('subscriber', ['type'=>'subscriber', 'id'=>$receiver->id]) ?>" class="" title="<?php echo $receiver->email ?> (<?= wlwcn_get_fullname($receiver->f_name, $receiver->l_name); ?>)">
                                        <?php echo wlwcn_str_limit($receiver->email, 50) ?>
                                    </a>
                                </span>
                            <?php } else { ?>
                                None
                            <?php } ?>
                        </div>
                    </div>
                </fieldset>
            </div>
            <?php } ?>
        </div>
    </div>
    <hr>
    <div class="row m-t-20">
        <div class="col-12">
            <a href="<?php echo wlwcn_pluginBaseUrl('newsletter') ?>" class="btn btn-530-block btn-md btn-primary"> <span class="dashicons dashicons-arrow-left-alt <?php echo $item->sent_at ? '' : 'p-530-t-6' ?>"></span> Go back to Newsletters</a>
            <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$item->id, 'action'=>'duplicate']) ?>" class="btn btn-530-block btn-md btn-success m-530-l-5 m-529-t-10" title="Duplicate">
                <span class="dashicons dashicons-admin-page <?php echo (!$item->sent_at) ? 'lh-530-15' : '' ?>"></span>
                Duplicate
            </a>
            <?php if(!$item->sent_at) { ?>
            <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$item->id, 'action'=>'edit']) ?>" class="btn btn-530-block btn-md btn-warning m-530-l-5 m-529-t-10"> <span class="dashicons dashicons-edit p-530-t-6"></span> Edit</a>
            <form class="show form-delete d-530-inline-block m-529-t-10" data-confirmed="0" action="<?php echo esc_html( admin_url('admin-post.php')) ?>" method="post">
                <?php wp_nonce_field('wlwcn_delete_newsletter', 'wlwcn_wpnonce'); ?>
                <input type="hidden" name="action" value="wlwcn_delete_newsletter">
                <input type="hidden" name="id" value="<?php echo esc_attr($item->id) ?>">
                <button type="submit" class="btn btn-530-block btn-md btn-danger m-530-l-5 pos-rel" title="Delete">
                    <span class="dashicons dashicons-no"></span> Delete
                </button>
            </form>
            <?php } ?>
        </div>
    </div>
</div>
