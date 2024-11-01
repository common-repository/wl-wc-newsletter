<div class="wrap wlnl custom">
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
        $valid_orderbys = ['email', 'created_at'];
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

    <form method="get" class="m-b-50">
    	<p class="search-box">
        	<label class="screen-reader-text" for="subscriber-search-input">Search Subscribers:</label>
            <input type="hidden" name="page" value="<?php echo esc_attr(WLWCN_SETTINGS_SLUG) ?>-subscriber">
        	<input type="search" id="subscriber-search-input" name="s" value="<?php echo isset($_GET['s']) ? esc_attr(wp_unslash($_GET['s'])) : '' ?>"/>
            <button type="submit" class="button">Search</button>
        </p>
    </form>
    <?php if(isset($_GET['s'])) { ?>
    <h2 class="txt-dec-reset">Search results for: <b><?php echo esc_attr(wp_unslash($_GET['s'])) ?></b></h2>
    <?php } ?>
    <table class="wp-list-table widefat fixed striped table-view-list subscriber">
        <thead>
            <?php require 'thead.php' ?>
    	</thead>
    	<tbody id="the-list" data-wp-lists='list:subscriber'>
            <?php
            if($data['total_rows'] > 0)
            {
                foreach($data['result'] as $key => $row)
                {
                    $fullname = wlwcn_get_fullname($row->f_name, $row->l_name);
            ?>
            <tr id="tr-<?php echo esc_attr($row->id) ?>">
                <td class="email column-email" data-colname="Email">
                    <a href="<?php echo wlwcn_pluginBaseUrl('subscriber', ['type'=>'subscriber', 'id'=>$row->id]) ?>"><?php echo esc_attr($row->email) ?></a>
                    <div class="wlwnl-row-actions">
                        <a href="<?php echo wlwcn_pluginBaseUrl('subscriber', ['type'=>'subscriber', 'id'=>$row->id]) ?>" class="btn btn-sm btn-warning" title="Edit">
                            <span class="dashicons dashicons-edit"></span>
                        </a>
                        <form class="form-delete" data-confirmed="0" action="<?php echo esc_html( admin_url('admin-post.php')) ?>" method="post">
                            <?php wp_nonce_field('wlwcn_delete_subscriber', 'wlwcn_wpnonce'); ?>
                            <input type="hidden" name="action" value="wlwcn_delete_subscriber">
                            <input type="hidden" name="id" value="<?php echo esc_attr($row->id) ?>">
                            <button type="submit" class="btn btn-sm btn-danger m-l-5 pos-rel bottom-1" title="Delete">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </form>
                    </div>
                </td>
                <td class="name column-name" data-colname="Name">
                    <?php if($fullname) { ?>
                        <?php echo esc_attr($fullname) ?>
                    <?php } else { ?>
                        <span aria-hidden="true">&#8212;</span>
                        <span class="screen-reader-text">Unknown</span>
                    <?php } ?>
                </td>
                <td class="role column-role" data-colname="Roles">
                    <?php
                    if($row->is_customer || $row->is_member)
                    {
                        if($row->is_customer)
                        {
                    ?>
                    <label class="label lable-success">Customer</label>
                    <?php echo ($row->is_member) ? ' | ' : '' ?>
                    <?php } ?>
                    <?php if($row->is_member) { ?>
                    <label class="label lable-success">Member</label>
                    <?php } ?>
                    <?php } else { ?>
                    <span aria-hidden="true">&#8212;</span>
                    <span class="screen-reader-text">Unknown</span>
                    <?php } ?>
                </td>
                <td class="subscription_coupon column-subscription_coupon" data-colname="Subscription Coupon">
                    <?php if($row->subscription_coupon) { ?>
                        <?php echo esc_attr($row->subscription_coupon) ?>
                    <?php } else { ?>
                        <span aria-hidden="true">&#8212;</span>
                        <span class="screen-reader-text">Unknown</span>
                    <?php } ?>
                </td>
                <td class="created_at column-created_at"><?php echo esc_attr($row->created_at) ?></td>
                <td class="action column-action" data-colname="Action">
                    <a href="<?php echo wlwcn_pluginBaseUrl('subscriber', ['type'=>'subscriber', 'id'=>$row->id]) ?>" class="btn btn-sm btn-warning" title="Edit">
                        <span class="dashicons dashicons-edit"></span>
                    </a>
                    <form class="form-delete" data-confirmed="0" action="<?php echo esc_html( admin_url('admin-post.php')) ?>" method="post">
                        <?php wp_nonce_field('wlwcn_delete_subscriber', 'wlwcn_wpnonce'); ?>
                        <input type="hidden" name="action" value="wlwcn_delete_subscriber">
                        <input type="hidden" name="id" value="<?php echo esc_attr($row->id) ?>">
                        <button type="submit" class="btn btn-sm btn-danger m-l-5 pos-rel bottom-1" title="Delete">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                    </form>
                </td>
            </tr>
            <?php
                }
            } else { ?>
            <tr>
                <td colspan="6" class="text-warning text-center">No subscriber found.</td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <?php require 'thead.php' ?>
        </tfoot>
    </table>
    <?php wlwcn_display_pagination($data['total_rows'], $data['per_page'], $data['cur_page']); ?>
</div>
