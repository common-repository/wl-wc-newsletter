<tr>
    <?php
    $sortable_class = 'sortable';
    $to_sort = 'desc';

    if($orderby == 'subject')
    {
        $sortable_class = 'sorted';
        $to_sort = 'asc';
        if($order == 'desc')
        {
            $to_sort = 'desc';
        }
    }

    $request_sort = ($to_sort == 'asc') ? 'desc' : 'asc';

    $query_params = [];
    if(isset($get['s']))
    {
        $query_params['s'] = $get['s'];
    }

    $subject_query_params = $query_params;
    $subject_query_params['orderby'] = 'subject';
    $subject_query_params['order'] = $request_sort;
    ?>
    <th scope="col" id="subject" class="manage-column column-subject column-primary <?php echo esc_attr($sortable_class) ?> <?php echo esc_attr($to_sort) ?>">
        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', $subject_query_params) ?>">
            <span>Subject</span>
            <span class="sorting-indicator"></span>
        </a>
    </th>

    <?php
    $sortable_class = 'sortable';
    $to_sort = 'desc';

    if($orderby == 'sent_to')
    {
        $sortable_class = 'sorted';
        $to_sort = 'asc';
        if($order == 'desc')
        {
            $to_sort = 'desc';
        }
    }

    $request_sort = ($to_sort == 'asc') ? 'desc' : 'asc';

    $to_query_params = $query_params;
    $to_query_params['orderby'] = 'sent_to';
    $to_query_params['order'] = $request_sort;
    ?>
    <th scope="col" id="sent_to" class="manage-column column-sent_to <?php echo esc_attr($sortable_class) ?> <?php echo esc_attr($to_sort) ?>" title="Sent to email address is displayed if the newsletter was sent to only one subscriber.">
        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', $to_query_params) ?>">
            <span>Sent To</span>
            <span class="sorting-indicator"></span>
        </a>
    </th>

    <?php
    $sortable_class = 'sortable';
    $to_sort = 'desc';

    if($orderby == 'sent_at')
    {
        $sortable_class = 'sorted';
        $to_sort = 'asc';
        if($order == 'desc')
        {
            $to_sort = 'desc';
        }
    }

    $request_sort = ($to_sort == 'asc') ? 'desc' : 'asc';

    $sent_query_params = $query_params;
    $sent_query_params['orderby'] = 'sent_at';
    $sent_query_params['order'] = $request_sort;
    ?>
    <th scope="col" id="sent_at" class="manage-column column-sent_at <?php echo esc_attr($sortable_class) ?> <?php echo esc_attr($to_sort) ?>">
        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', $sent_query_params) ?>">
            <span>Sent On</span>
            <span class="sorting-indicator"></span>
        </a>
    </th>

    <?php
    $sortable_class = 'sortable';
    $to_sort = 'desc';

    if($orderby == 'created_at')
    {
        $sortable_class = 'sorted';
        $to_sort = 'asc';
        if($order == 'desc')
        {
            $to_sort = 'desc';
        }
    }

    $request_sort = ($to_sort == 'asc') ? 'desc' : 'asc';

    $created_query_params = $query_params;
    $created_query_params['orderby'] = 'created_at';
    $created_query_params['order'] = $request_sort;
    ?>
    <th scope="col" id="created_at" class="manage-column column-created_at <?php echo esc_attr($sortable_class) ?> <?php echo esc_attr($to_sort) ?>">
        <a href="<?php echo wlwcn_pluginBaseUrl('newsletter', $created_query_params) ?>">
            <span>Created On</span>
            <span class="sorting-indicator"></span>
        </a>
    </th>

    <th scope="col" id="action" class="manage-column column-action">Action</th>
</tr>
