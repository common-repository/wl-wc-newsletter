<?php
$get = isset($_GET) ? $_GET : [];

if(isset($get['pg']))
{
    unset($get['pg']);
}

$page_type_arr = explode('-', wlwcn_getInput('page'));
unset($page_type_arr[0]);
$page_type = implode('-', $page_type_arr);

$first_pg_params = $get;
$prev_pg_params = $next_pg_params = $last_pg_params = $get;
$prev_pg_params['pg'] = $cur_page - 1;
$next_pg_params['pg'] = $cur_page + 1;
$last_pg_params['pg'] = $total_pages;
?>
<div class="tablenav bottom">
    <div class="tablenav-pages">
        <span class="displaying-num">Displaying <?php echo $current_rows ? $current_rows.' of ' : '' ?><?php echo $total_rows ?> item<?php echo ($total_rows > 1) ? 's' : '' ?></span>
        <span class="pagination-links">
            <?php if($cur_page == 1) { ?>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
            <?php } else { ?>
            <a class="first-page button" href="<?php echo wlwcn_pluginBaseUrl($page_type, $first_pg_params) ?>">
				<span class="screen-reader-text">First page</span>
				<span aria-hidden="true">&laquo;</span>
			</a>
			<a class="prev-page button" href="<?php echo wlwcn_pluginBaseUrl($page_type, $prev_pg_params) ?>">
				<span class="screen-reader-text">Previous page</span>
				<span aria-hidden="true">&lsaquo;</span>
			</a>
            <?php } ?>
            <span class="paging-input">
                <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                <form method="get" action="" class="d-inline-block">
                    <?php foreach($get as $key => $val) { ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= $val ?>">
                    <?php } ?>
                    <input class="current-page" id="current-page-selector" type="number" min="1" max="<?php echo $total_pages ?>" name="pg" value="<?php echo $cur_page ?>" size="2" aria-describedby="table-paging" <?= ($total_pages < 2) ? 'disabled' : '' ?> />
                </form>
                <span class="tablenav-paging-text"> of <span class="total-pages"><?php echo $total_pages ?></span></span>
            </span>
            <?php if(($cur_page == $total_pages) || ($total_pages < 1)) { ?>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
			<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
            <?php } else { ?>
            <a class="next-page button" href="<?php echo wlwcn_pluginBaseUrl($page_type, $next_pg_params) ?>">
                <span class="screen-reader-text">Next page</span><span aria-hidden="true">&rsaquo;</span>
            </a>
            <a class="last-page button" href="<?php echo wlwcn_pluginBaseUrl($page_type, $last_pg_params) ?>">
                <span class="screen-reader-text">Last page</span><span aria-hidden="true">&raquo;</span>
            </a>
            <?php } ?>
        </span>
    </div>
</div>
