<?php

use Models\Wlwcn_Model;
use Eloquent\MailingListEM;

function wlwcn_log($msg, $force=false)
{
    if(!WP_DEBUG && !$force)
    {
        return false;
    }

    $dirpath = ABSPATH.'logs';
    if(!file_exists($dirpath))
    {
        @mkdir($dirpath);
    }

    $existing_content = '';
    $filepath = $dirpath.'/log_'.date('Y-m-d').'.log';
    $fp = fopen($filepath, "r+");

    if($fp !== false)
    {
        $filesize = filesize($filepath);
        if($filesize)
        {
            $existing_content = fread($fp, $filesize);
        }

        rewind($fp);
    }
    else
    {
        $fp = fopen($filepath, "w");
    }

    $microtime = microtime();
    $microtime_arr = explode(' ', $microtime);
    $microtime_only = $microtime_arr[0];
    $dec_only = substr($microtime_only, 1);

    $content = date('Y-m-d H:i:s').$dec_only." \r\n".$msg."\r\n\r\n\r\n".$existing_content;

    fwrite($fp, $content);
    fclose($fp);
}

function wlwcn_get_fn_prefix()
{
    return 'wlwcn_';
}

function wlwcn_sanitize_text($field, $arr_key=false)
{
    if(!isset($_REQUEST[$field]))
    {
        return NULL;
    }

    if(!$arr_key)
    {
        return sanitize_text_field($_REQUEST[$field]);
    }
    else
    {
        return sanitize_text_field($_REQUEST[$field][$arr_key]);
    }
}

function wlwcn_sanitize_html($field, $arr_key=false)
{
    if(!$arr_key)
    {
        return wp_kses_post($_REQUEST[$field]);
    }
    else
    {
        return wp_kses_post($_REQUEST[$field][$arr_key]);
    }
}

function wlwcn_getHtmlInputNames()
{
    $html_input_names = [
            // for settings
            'subscription_details',

            // for newsletter
            'message',

            // for subscribers,
            'notes'
        ];

    return $html_input_names;
}

function wlwcn_set_old_inputs()
{
    $html_inputs = wlwcn_getHtmlInputNames();
    $inputs = wlwcn_getAllInputs();

    foreach($inputs as $key => $val)
    {
        if(!is_array($val))
        {
            if(!in_array($key, $html_inputs))
            {
                $_SESSION['old'][$key] = sanitize_text_field($val);
            }
            else
            {
                $_SESSION['old'][$key] = wp_kses_post($val);
            }
        }
        else
        {
            foreach($val as $key2 => $val2)
            {
                if(!is_array($val2))
                {
                    $_SESSION['old'][$key][$key2] = sanitize_text_field($val2);
                }
            }
        }
    }
}

function wlwcn_getAllInputNames()
{
    $html_inputs = wlwcn_getHtmlInputNames();
    $text_inputs = [
            // global
            'page', 'pg',

            // for settings
            'enable_subscription', 'enable_subscription_offer', 'from_email', 'replyto_email', 'type',

            // for subscribers
            'id', 'f_name', 'l_name', 'subscription_coupon',

            // for newsletter
            'subject', 'send_to', 'mailing_list', 'subscriber', 'when_to_send'
        ];

    $inputs = array_merge($html_inputs, $text_inputs);

    return $inputs;
}

function wlwcn_getRequest($fields=[])
{
    $inputs = [];
    foreach($fields as $key => $field)
    {
        if(isset($_REQUEST[$field]))
        {
            $html_inputs = wlwcn_getHtmlInputNames();
            $sanitize_type = in_array($field, $html_inputs) ? 'html' : 'text';
            $function = wlwcn_get_fn_prefix().'sanitize_'.$sanitize_type;
            if(!is_array($_REQUEST[$field]))
            {
                $inputs[$field] = $function($field);
            }
            else
            {
                $inputs[$field] = [];
                foreach($_REQUEST[$field] as $index => $v)
                {
                    if(!is_array($v))
                    {
                        $inputs[$field][] = $function($field, $index);
                    }
                }
            }
        }
        else
        {
            $inputs[$field] = null;
        }
    }

    return $inputs;
}

function wlwcn_getAllInputs()
{
    $all_inputs_names = wlwcn_getAllInputNames();

    $inputs = wlwcn_getRequest($all_inputs_names);

    return $inputs;
}

function wlwcn_getInputFromRules($rules=[])
{
    $inputs = [];
    foreach($rules as $field => $rule_str)
    {
        $single_valid = true;
        $rule_arr = explode('|', $rule_str);
        if(!end($rule_arr))
        {
            unset($rule_arr);
        }

        $last2 = substr($field, -2);
        if($last2 == '.*')
        {
            $field_name = substr($field, 0, -2);
        }
        else
        {
            $field_name = $field;
        }

        $html_inputs = wlwcn_getHtmlInputNames();

        if(!in_array($field_name, $html_inputs))
        {
            $inputs[$field_name] = wlwcn_sanitize_text($field_name);
        }
        else
        {
            $inputs[$field_name] = wlwcn_sanitize_html($field_name);
        }
    }
}

function wlwcn_categorizeSubscribers($subscribers)
{
    $customers = $members = [];

    foreach($subscribers as $key => $row)
    {
        if(is_array($row))
        {
            if($row['is_customer'])
            {
                $customers[] = $row;
            }

            if($row['is_member'])
            {
                $members[] = $row;
            }
        }
        else
        {
            if($row->is_customer)
            {
                $customers[] = $row;
            }

            if($row->is_member)
            {
                $members[] = $row;
            }
        }
    }

    $data = [
            'customers' => $customers,
            'members' => $members
        ];

    return $data;
}

function wlwcn_getServerTimezoneOffsetMins()
{
    $now = new DateTime;
    $tz_offset = timezone_offset_get(new DateTimeZone(date_default_timezone_get()), $now);
    $offset_mins = $tz_offset / 60;

    return $offset_mins;
}

function wlwcn_getDatetimeByTimezone($datetime, $offset_mins, $format='Y-m-d H:i:s')
{
    if(!$offset_mins)
    {
        $offset_mins = wlwcn_getServerTimezoneOffsetMins();
    }
    $datetime = DateTime::createFromFormat($format, $datetime);
    $timezone = wlwcn_getTimezoneByOffsetMins($offset_mins, false, false);

    return $datetime->setTimezone($timezone);
}

function wlwcn_getTimezoneByOffsetMins($offset_mins, $javascript=true, $set_session_tz=true)
{
    if(!wlwcn_isInteger($offset_mins) || ($offset_mins < -840) || ($offset_mins > 720))
    {
        $offset_mins = wlwcn_getServerTimezoneOffsetMins();
    }
    else if($javascript)
    {
        $offset_mins = -1 * $offset_mins;
    }

    if($set_session_tz)
    {
        $_SESSION['client_timezone_offset'] = $offset_mins; // set php based offset in minutes
    }

    $min = abs($offset_mins % 60);
    $hr = (int) abs($offset_mins / 60);
    $offset_str = ($offset_mins < 0) ? '-' : '+';
    $offset_str .= $hr.':'.$min;

    $timezone = new DateTimeZone($offset_str);
    return $timezone;
}

function wlwcn_convertToServerTz($datetime, $format, $offset=0, $javascript=true)
{
    $client_timezone = wlwcn_getTimezoneByOffsetMins($offset, $javascript);
    $datetime = DateTime::createFromFormat($format, $datetime, $client_timezone);
    $server_timezone = new DateTimeZone(date_default_timezone_get());

    return $datetime->setTimezone($server_timezone);
}

function wlwcn_pp($arr)
{
    echo "<pre>"; print_r($arr); echo "</pre>";
}

function wlwcn_ppd($arr)
{
    wlwcn_pp($arr); die;
}

function wlwcn_getInput($field_name, $array_index=false, $request=[])
{
    /*
    Variable empty conditions: as returned by is_empty() fumction
    $var1 = ''; // true
    $var2 = []; // true
    $var3 = ['']; // false
    $var4 = [[]]; // false
    $var5 = false; // true
    $var6 = null; // true
    $var7 = 0; // true
    $var8 = 0.0; // true
    $var9 = '0'; // true
    $var10 = '0.0'; // false
    // any unset variables also return true

    strlen returns the following values:
    $var1 = ''; // 0
    $var1 = 0; // 1
    $var2 = 0.0; // 1
    $var3 = 0.1; //3
    $var4 = '0'; // 1
    $var5 = false; // 0
    $var6 = null; // 0
    */

    if(empty($request))
    {
        $request = wlwcn_getAllInputs();
    }

    $return = '';
    if($array_index !== false)
    {
        if(!empty($request[$field_name][$array_index]))
        {
            $return = is_array($request[$field_name][$array_index]) ? $request[$field_name][$array_index] : trim($request[$field_name][$array_index]);
        }
        else if(isset($request[$field_name][$array_index]))
        {
            $return = is_array($request[$field_name][$array_index]) ? $request[$field_name][$array_index] : trim($request[$field_name][$array_index]);
        }
    }
    else
    {
        if(!empty($request[$field_name]))
        {
            $return = is_array($request[$field_name]) ? $request[$field_name] : trim($request[$field_name]);
        }
        else if(isset($request[$field_name]))
        {
            $return = is_array($request[$field_name]) ? $request[$field_name] : trim($request[$field_name]);
        }
    }

    return $return;
}

function wlwcn_previousUrl()
{
    if(isset($request['_wp_http_referer']) && !empty($request['_wp_http_referer']))
    {
        $referrer = $request['_wp_http_referer'];
    }
    else if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))
    {
        $referrer = $_SERVER['HTTP_REFERER'];
    }
    else
    {
        $referrer = 'admin.php?page='.WLWCN_SETTINGS_SLUG.'-settings';
    }

    return $referrer;
}

function wlwcn_setAlertMessage($msg, $type='info')
{
    $_SESSION['flash']['alert_type'] = $type;
    $_SESSION['flash']['alert_msg'] = $msg;
}


function wlwcn_setValidationMsg($field, $message)
{
    if(isset($_SESSION['flash']['validation_errors'][$field]))
    {
        if(is_array($_SESSION['flash']['validation_errors'][$field]))
        {
            array_push($_SESSION['flash']['validation_errors'][$field], $message);
        }
        else
        {
            $prev_msg = $_SESSION['flash']['validation_errors'][$field];
            $msgs = [$prev_msg, $message];
            $_SESSION['flash']['validation_errors'][$field] = $msgs;
        }
    }
    else
    {
        $_SESSION['flash']['validation_errors'][$field] = $message;
    }
}

function wlwcn_validationErrors()
{
    $errors = isset($_SESSION['flash']['validation_errors']) ? $_SESSION['flash']['validation_errors'] : [];

    return $errors;
}

function wlwcn_issetOld()
{
    return isset($_SESSION['old']);
}

function wlwcn_old($key, $default='')
{
    return isset($_SESSION['old'][$key]) ? wp_kses_post(wp_unslash($_SESSION['old'][$key])) : wp_kses_post(wp_unslash($default));
}

function wlwcn_flash($key, $val)
{
    $_SESSION['flash'][$key] = $val;
}

function wlwcn_getSearchStr($key='s')
{
    $return = false;
    if(isset($_GET[$key]))
    {
        $return = trim(sanitize_text_field($_GET[$key]));
    }

    return $return;
}

function wlwcn_get_fullname($f_name, $l_name='')
{
    $fullname ='';
    if($f_name)
    {
        $fullname = $f_name;
        if($l_name)
        {
            $fullname .= ' '.$l_name;
        }
    }
    else if($l_name)
    {
        $fullname = $l_name;
    }

    return $fullname;
}

function wlwcn_formatEmailName($email, $f_name='', $l_name='')
{
    $return = $email;
    $fullname = wlwcn_get_fullname($f_name, $l_name);
    if($fullname)
    {
        $return = $email.' '.htmlentities('<'.$fullname.'>');

    }

    return $return;
}

function wlwcn_isInteger($var)
{
    if(is_numeric($var))
    {
        $int_val = (int) $var;
        if($var == $int_val)
        {
            return true;
        }
    }

    return false;
}

function wlwcn_trimRequest($request)
{
    $new_request = [];
    foreach($request as $key => $val)
    {
        if(is_array($val))
        {
            foreach($val as $k => $v)
            {
                if(is_array($v))
                {
                    $new_request[$key][$k] = $v;
                }
                else
                {
                    $new_request[$key][$k] = trim($v);
                }
            }
        }
        else
        {
            if($key == 'password')
            {
                $new_request[$key] = $val;
            }
            else
            {
                $new_request[$key] = trim($val);
            }

        }
    }

    return $new_request;
}

function wlwcn_current_url()
{
    global $wp;

    if(is_admin())
    {
        $url = admin_url(add_query_arg([$_GET], $wp->request));
    }
    else
    {
        $url = home_url(add_query_arg([$_GET], $wp->request));
    }

    return $url;
}

function wlwcn_admin_page_url()
{
    global $wp;

    return admin_url('admin.php'.add_query_arg([$_GET], $wp->request));
}

function wlwcn_uriSegment($position='')
{
    global $wp;

    if(is_admin())
    {
        // admin_url() => http://localhost/wl/project/wp-admin/
        $wlwcn_current_url = admin_url(sprintf(basename($_SERVER['REQUEST_URI'])));
        // $wlwcn_current_url = http://localhost/wl/project/wp-admin/admin.php?key=value
    }
    else
    {
        // home_url() => http://localhost/wl/project
        $wlwcn_current_url = home_url(add_query_arg([$_GET], $wp->request));
        // $wlwcn_current_url = http://localhost/wl/project/page-uri/page.php?key=value
    }

    $full_uri = str_ireplace(home_url(), '', $wlwcn_current_url);
    // $full_uri => /my-account/edit-account?dsaf=sadf

    $url_arr = wp_parse_url($full_uri);
    /*
    $url_arr => [
            'path' => '/my-account/edit-account/page.php',
            'query' => 'key1=value1&key2=value2'
        ]
    */

    $uri_path = $url_arr['path'];
    $strlen = strlen($uri_path);

    // remove leading slash
    if($uri_path[0] == '/')
    {
        $uri_path = substr($uri_path, 1);
        $strlen -= 1;
    }

    // remove trailing slash
    if(isset($uri_path[$strlen-1]) && ($uri_path[$strlen-1] == '/'))
    {
        $uri_path = substr($uri_path, 0, -1);
    }

    if(!strlen($position))
    {
        return $uri_path;
    }

    $uri_arr = explode('/', $uri_path);
    $uri = isset($uri_arr[$position]) ? $uri_arr[$position] : '';

    return $uri;
}

function wlwcn_checkPostedCheckbox($field_name, $other_form_input_name, $db_value=true)
{
    // return true if other input checkbox ($other_form_input_name) is also present & checked
	if(isset($_POST) && isset($_POST[$other_form_input_name]))
	{
		$checked = isset($_POST[$field_name]);
	}
	else
	{
		$checked = $db_value;
	}

	return $checked;
}

function wlwcn_getPluginTablePrefix()
{
    require_once __DIR__.'/models/Wlwcn_Model.php';

    $model = new Wlwcn_Model;
    $prefix = $model->prefix;

    return $prefix;
}

function wlwcn_getPluginName()
{
    return WLWCN_NAME;
}

function getSearchStr($key='s')
{
    $return = false;
    if(isset($_GET[$key]))
    {
        $return = trim($_GET[$key]);
    }

    return $return;
}

function wlwcn_undeletable_mailing_lists($slugs_only=true)
{
    $mailing_lists = [
            'customer' => [
                    'title' => 'Customer',
                    'description' => 'Subscribers who have successfully made at least one order from your store. A customer can also be a member.'
                ],
            'member' => [
                    'title' => 'Member',
                    'description' => 'Subscribers who have signed up and created an account. A member can also be a subscriber.'
                ],
            'guest' => [
                    'title' => 'Guest',
                    'description' => 'Any subscribers who is neither a customer or a member is kept in this list.'
                ]
        ];

    if($slugs_only)
    {
        $mailing_lists = array_keys($mailing_lists);
    }

    return $mailing_lists;
}

function wlwcn_is_deletable_mailing_list($title)
{
    $not_deletable = wlwcn_undeletable_mailing_lists();
    $title = strtolower($title);

    return !in_array($title, $not_deletable);
}

function wlwcn_undeletableMailingListStr()
{
    $slugs = wlwcn_undeletable_mailing_lists();
    $models = MailingListEM::whereIn('slug', $slugs)->get();

    $models_count = count($models);
    $str = '';
    foreach($models as $key => $row)
    {
        $str .= '<b>'.$row->title.'</b>';

        if($key == ($models_count - 2))
        {
            $str .= ' & ';
        }
        else if($key < ($models_count -1))
        {
            $str .= ', ';
        }
    }

    $str .= ($models_count > 1) ? ' which are the default mailing lists' : ' which is the default mailing list';

    return $str;
}
