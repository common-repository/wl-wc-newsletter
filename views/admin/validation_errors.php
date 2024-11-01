<?php
if(isset($_SESSION['flash']['validation_errors']))
{
    $error_html = '';
    foreach($_SESSION['flash']['validation_errors'] as $key => $row)
    {
        if(!is_array($row))
        {
            $error_html .= '<li>'.wp_kses_post($row).'</li>';
        }
        else
        {
            foreach($row as $key2 => $row2)
            {
                $error_html .= '<li>'.wp_kses_post($row).'</li>';
            }
        }
    }

    if($error_html)
    {
        $error_html = '<ul class="validation-errors">'.$error_html.'</ul>';
        echo wp_kses_post($error_html);
    }
}
