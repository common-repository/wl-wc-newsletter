<div class="wrap wlnl">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php
    require_once __DIR__.'/../alert.php';
    require_once __DIR__.'/../validation_errors.php';

    $get = isset($_GET) ? $_GET : [];
    $orderby = '';
    $order = 'asc';

    if(isset($get['orderby']))
    {
        $selected_orderby = $get['orderby'];
        $valid_orderbys = ['subject', 'sent_to', 'sent_at', 'created_at'];
        if(in_array($selected_orderby, $valid_orderbys))
        {
            $orderby = $selected_orderby;
            if(isset($get['order']) && ($get['order'] == 'desc'))
            {
                $order = 'desc';
            }
        }
    }
    ?>
    <p>
        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'action'=>'create']) ?>" class="btn btn-md btn-primary p-t-3"> <span class="dashicons dashicons-plus p-t-4"></span> Compose</a>
    </p>
    <form method="get" class="m-b-50">
    	<p class="search-box">
        	<label class="screen-reader-text" for="search-input">Search Newsletters:</label>
            <input type="hidden" name="page" value="<?php echo esc_attr(WLWCN_SETTINGS_SLUG) ?>-newsletter">
        	<input type="search" id="search-input" name="s" value="<?php echo isset($_GET['s']) ? esc_attr(wp_unslash($_GET['s'])) : '' ?>"/>
            <button type="submit" class="button">Search</button>
        </p>
    </form>
    <?php if(isset($_GET['s'])) { ?>
    <h2 class="txt-dec-reset">Search results for: <b><?php echo esc_attr(wp_unslash($_GET['s'])) ?></b></h2>
    <?php } ?>
    <table class="wp-list-table widefat fixed striped table-view-list newsletter">
        <thead>
            <?php require 'thead.php' ?>
    	</thead>
    	<tbody id="the-list" data-wp-lists='list:newsletter'>
            <?php
            if($items->count() > 0)
            {
                foreach($items as $key => $row)
                {
            ?>
            <tr id="tr-<?php echo $row->id ?>">
                <?php /*
                <th scope="row" class="check-column">
                    <label class="screen-reader-text" for="subscriber_<?php echo $row->id ?>"></label>
                    <input type="checkbox" name="subscribers[]" id="subscriber_<?php echo $row->id ?>" class="customer" value="<?php echo $row->id ?>" />
                </th>
                */ ?>
                <?php /*
                Primary Column template:
                <td class="username column-username has-row-actions column-primary" data-colname="Username">
                    <img alt="" src="#" srcset="#" class='avatar avatar-32 photo' height='32' width='32' loading='lazy'/>
                    <strong><a href="#">admin</a></strong>
                    <br />
                    <div class="row-actions">
                        <span class="edit"><a href="#">Edit</a> | </span>
                        <span class="view"><a href="#" aria-label="View posts by admin">View</a></span>
                    </div>
                    <button type="button" class="toggle-row">
                        <span class="screen-reader-text">Show more details</span>
                    </button>
                </td>
                */ ?>
                <td class="subject column-subject column-primary" data-colname="Subject">
                    <span class="subject"><?php echo $row->subject ?></span>
                    <div class="wlwnl-row-actions">
                        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$row->id]) ?>" class="btn btn-sm btn-info" title="View">
                            <span class="dashicons dashicons-visibility"></span>
                        </a>
                        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$row->id, 'action'=>'duplicate']) ?>" target="_blank" class="btn btn-sm btn-success m-l-5" title="Duplicate">
                            <span class="dashicons dashicons-admin-page"></span>
                        </a>
                        <?php if(!$row->sent_at) { ?>
                        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$row->id, 'action'=>'edit']) ?>" class="btn btn-sm btn-warning m-l-5" title="Edit">
                            <span class="dashicons dashicons-edit"></span>
                        </a>
                        <form class="form-delete" data-confirmed="0" action="<?php echo esc_html( admin_url('admin-post.php')) ?>" method="post">
                            <?php wp_nonce_field('wlwcn_delete_newsletter', 'wlwcn_wpnonce'); ?>
                            <input type="hidden" name="action" value="wlwcn_delete_newsletter">
                            <input type="hidden" name="id" value="<?php echo $row->id ?>">
                            <button type="submit" class="btn btn-sm btn-danger m-l-5 pos-rel bottom-1" title="Delete">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </form>
                        <?php } ?>
                    </div>
                    <button type="button" class="toggle-row">
                        <span class="screen-reader-text">Show more details</span>
                    </button>
                </td>
                <td class="name column-name" data-colname="Sent To">
                    <?php if($row->sent_to && $row->subscriber) { ?>
                        <a href="<?php echo wlwcn_pluginBaseUrl('subscriber', ['type'=>'subscriber', 'action'=>'edit', 'id'=>$row->subscriber->id]) ?>" class="" title="<?php echo $row->subscriber->email ?> (<?= wlwcn_get_fullname($row->subscriber->f_name, $row->subscriber->l_name); ?>)">
                            <?php echo wlwcn_str_limit($row->sent_to, 25) ?>
                        </a>
                    <?php } else if($row->sent_at) { ?>
                        <?= $row->subscribersSent->count() ?> subscriber<?= ($row->subscribersSent->count() > 1) ? 's' : '' ?>
                    <?php } else { ?>
                        <span aria-hidden="true">&#8212;</span>
                        <span class="screen-reader-text">Unknown</span>
                    <?php } ?>
                </td>
                <td class="sent-at column-sent-at" data-colname="Sent At">
                    <?php if($row->sent_at) { ?>
                        <?php echo $row->sent_at ?>
                    <?php } else { ?>
                        <span aria-hidden="true">&#8212;</span>
                        <span class="screen-reader-text">Unknown</span>
                    <?php } ?>
                </td>
                <td class="created-at column-created-at" data-colname="Created At">
                    <?php if($row->created_at) { ?>
                        <?php echo $row->created_at ?>
                    <?php } else { ?>
                        <span aria-hidden="true">&#8212;</span>
                        <span class="screen-reader-text">Unknown</span>
                    <?php } ?>
                </td>
                <td class="action column-action" data-colname="Action">
                    <div class="btn-container">
                        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$row->id]) ?>" class="btn btn-sm btn-info btn-view" title="View">
                            <span class="dashicons dashicons-visibility"></span>
                        </a>
                    </div>
                    <div class="btn-container">
                        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$row->id, 'action'=>'duplicate']) ?>" target="_blank" class="btn btn-sm btn-success m-l-5 btn-duplicate" title="Duplicate">
                            <span class="dashicons dashicons-admin-page"></span>
                        </a>
                    </div>
                    <?php if(!$row->sent_at) { ?>
                    <div class="btn-container">
                        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$row->id, 'action'=>'edit']) ?>" class="btn btn-sm btn-warning m-l-5 btn-edit" title="Edit">
                            <span class="dashicons dashicons-edit"></span>
                        </a>
                    </div>
                    <div class="btn-container">
                        <form class="form-delete" data-confirmed="0" action="<?php echo esc_html( admin_url('admin-post.php')) ?>" method="post">
                            <?php wp_nonce_field('wlwcn_delete_newsletter', 'wlwcn_wpnonce'); ?>
                            <input type="hidden" name="action" value="wlwcn_delete_newsletter">
                            <input type="hidden" name="id" value="<?php echo $row->id ?>">
                            <button type="submit" class="btn btn-sm btn-danger m-l-5 pos-rel bottom-1" title="Delete">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </form>
                    </div>
                    <?php } ?>
                </td>
            </tr>
            <?php
                }
            } else { ?>
            <tr>
                <td colspan="5" class="text-warning text-center">No newsletter found.</td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <?php require 'thead.php' ?>
        </tfoot>
    </table>
    <?php wlwcn_display_pagination($items->total(), $items->perPage(), $items->currentPage(), $items->count()); ?>
</div>
