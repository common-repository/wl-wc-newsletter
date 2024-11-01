<?php
if(isset($_SESSION['flash']['alert_type']))
{
    $flash = $_SESSION['flash'];

    /*
    Icon type class:
    error: no
    warning: warning
    info: info
    success: yes
    */
    if($flash['alert_type'] == 'success')
    {
        $icon_class = 'yes';
    }
    else if($flash['alert_type'] == 'error')
    {
        $icon_class = 'no';
    }
    else if($flash['alert_type'] == 'warning')
    {
        $icon_class = 'warning';
    }
    else
    {
        $icon_class = 'info';
    }
?>
<div class="notice notice-<?php echo esc_attr($flash['alert_type']) ?> is-dismissible">
    <p>
        <span class="dashicons dashicons-<?php echo esc_attr($icon_class) ?>"></span> <?php echo wp_kses_post($flash['alert_msg']) ?>
    </p>
</div>
<?php }
