<?php

namespace Validators;

use Models\Wlwcn_Model;
use Exception;
use DateTime;

class Validator
{
    protected $request;

    function __construct()
    {

    }

    function validate($rules, $request=[])
    {
        if(empty($request))
        {
            $request = $_REQUEST;
        }

        $this->request = $request;
        $overall_valid = true;

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

                $i = 0;
                $field_value = wlwcn_getInput($field_name);
                if(!isset($request[$field_name]) || !is_array($request[$field_name]))
                {
                    if(in_array('required', $rule_arr))
                    {
                        $single_valid = self::required($field_name);

                        if(!$single_valid)
                        {
                            $overall_valid = false;
                            break;
                        }
                    }
                }
                else
                {
                    foreach($request[$field_name] as $fa_key => $fa_val)
                    {
                        $single_valid = true;
                        foreach($rule_arr as $key => $rule)
                        {
                            $rule_subarr = explode(':', $rule);
                            $rule = $rule_subarr[0];

                            if($rule == 'required')
                            {
                                continue;
                            }

                            $count_rule_subarr = count($rule_subarr);
                            if($count_rule_subarr > 1)
                            {
                                $new_arr = $rule_subarr;
                                unset($new_arr[0]);
                                $param_str = implode(':', $new_arr);
                            }
                            else
                            {
                                $param_str = trim(end($rule_subarr));
                            }

                            if(($count_rule_subarr > 1) && strlen($param_str))
                            {
                                $single_valid = $this->{$rule}($field_name.'.'.$fa_key, $param_str);
                            }
                            else
                            {
                                $single_valid = $this->{$rule}($field_name.'.'.$fa_key);
                            }

                            if(!$single_valid)
                            {
                                $overall_valid = false;
                                break;
                            }
                        }
                        ++$i;
                    }
                }
            }
            else
            {
                foreach($rule_arr as $key => $rule)
                {
                    $rule_subarr = explode(':', $rule);
                    $rule = $rule_subarr[0];

                    $count_rule_subarr = count($rule_subarr);
                    if($count_rule_subarr > 1)
                    {
                        $new_arr = $rule_subarr;
                        unset($new_arr[0]);
                        $param_str = implode(':', $new_arr);
                    }
                    else
                    {
                        $param_str = trim(end($rule_subarr));
                    }

                    if(($count_rule_subarr > 1) && strlen($param_str))
                    {
                        $single_valid = $this->{$rule}($field, $param_str);
                    }
                    else
                    {
                        $single_valid = $this->{$rule}($field);
                    }

                    if(!$single_valid)
                    {
                        $overall_valid = false;
                        break;
                    }
                }
            }
        }

        if(!$overall_valid)
        {
            // alert types: error | warning | info | success
            $_SESSION['flash']['alert_type'] = 'error';
            $_SESSION['flash']['alert_msg'] = 'Please enter all the required fields correctly & submit again.';
            wp_redirect(wlwcn_previousUrl());
            exit;
        }
    }

    function exists($field, $param)
    {
        /*
        rule format => exists:table;column;column1=value1,column2=value2
        $field => input_field_name
        $param => table;column;col1=val1,col2=val2
        */

        $param_arr = explode(';', $param);
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $field_name = $field;
            $submitted_val = wlwcn_getInput($field_name);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        if(isset($param_arr[1]))
        {
            $where[$param_arr[1]] = $submitted_val;
            if(isset($param_arr[2]))
            {
                $where_arr = explode(',', $param_arr[2]);
                foreach($where_arr as $key => $val)
                {
                    $field_val_arr = explode('=', $val);
                    if(strtolower($field_val_arr[1]) == 'null')
                    {
                        $where[$field_val_arr[0]] = NULL;
                    }
                    else
                    {
                        $where[$field_val_arr[0]] = $field_val_arr[1];
                    }
                }
            }
        }
        else
        {
            $where[$field_name] = $submitted_val;
        }

        $table = $param_arr[0];
        $model = new Wlwcn_Model($table);
        try {
            $model = $model->where($where)->withTrashed()->firstOrFail();
            $return = true;
        } catch (Exception $e) {
            $name = self::humanize_field($field);
            $msg = $name.' <b>'.$submitted_val.'</b> '.'does not exist in the system.';

            wlwcn_setValidationMsg($field, $msg);

            $return = false;
        }

        return $return;
    }

    function datetime($field, $param)
    {
        /*
        Getting timezone offset (seconds) when timezone is set to Asia/Kolkata:
        $offset_seconds = timezone_offset_get(new DateTimeZone(date_default_timezone_get()), new DateTime);
        $offset_seconds => 19800

        Javascript offset returns in minutes in negative for the same timezone:
        offset in js => -330
        */

        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $field_name = $field;
            $submitted_val = wlwcn_getInput($field_name);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        $name = self::humanize_field($field);

        $param_arr = explode(',', $param);
        $format = $param_arr[0];
        $dateTime = DateTime::createFromFormat($format, $submitted_val);

        if(!$dateTime)
        {
            $msg = $name." must be a valid date-time with format '".$format."'.";
            wlwcn_setValidationMsg($field, $msg);
            return false;
        }
        else if(!isset($param_arr[1]))
        {
            return true;
        }

        $date_type = $param_arr[1];
        $timezone_offset = wlwcn_getInput('timezone') ? (wlwcn_getInput('timezone')) : 0;
        $timezone = wlwcn_getTimezoneByOffsetMins($timezone_offset);

        $now = new DateTime;
        $schedule_at = DateTime::createFromFormat($format, $submitted_val, $timezone);

        $type = $param_arr[1];
        $return = true;
        if($date_type == 'future')
        {
            if($schedule_at < $now)
            {
                $msg = $name.' must be a future date-time.';
                wlwcn_setValidationMsg($field, $msg);
                $return = false;
            }
        }
        else
        {
            if($schedule_at > $now)
            {
                $msg = $name.' must be a future date-time.';
                wlwcn_setValidationMsg($field, $msg);
                $return = false;
            }
        }

        return $return;
    }

    function required_or($field, $param)
    {
        $param_arr = explode(',', $param);
        array_unshift($param_arr, $field);

        foreach($param_arr as $input)
        {
            $val = wlwcn_getInput($input);
            if($val)
            {
                return true;
            }
        }

        $input_name_str = '';
        $param_count = count($param_arr);
        foreach($param_arr as $key => $input)
        {
            $input_name_str .= self::humanize_field($input);
            if($key < ($param_count - 2))
            {
                $input_name_str .= ', ';
            }
            else if($key < ($param_count - 1))
            {
                $input_name_str .= ' or ';
            }
        }

        $msg = 'At least one of '.$input_name_str.' is required.';
        wlwcn_setValidationMsg($field, $msg);

        return false;
    }

    function required_when($field, $param)
    {
        if(isset($this->request[$param]))
        {
            if(!isset($this->request[$field]) || !strlen(trim($this->request[$field])))
            {
                $name = self::humanize_field($field);
                $other_name = self::humanize_field($param);

                $msg = $name.' is required when '.$other_name.' is selected.';
                $_SESSION['flash']['validation_errors'][$field] = $msg;

                return false;
            }
        }

        return true;
    }

    function in($field, $params)
    {
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $field_name = $field;
            $submitted_val = wlwcn_getInput($field_name);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        $param_arr = explode(',', $params);
        $count = count($param_arr);

        if(!strlen(trim(end($param_arr))))
        {
            unset($param_arr[$count-1]);
        }

        if(in_array($submitted_val, $param_arr))
        {
            return true;
        }

        $name = self::humanize_field($field);
        $param_str = str_replace(',', ', ', $params);
        $msg = $name.' must be any of: '.$param_str.'.';
        $_SESSION['flash']['validation_errors'][$field] = $msg;

        return false;
    }

    function numeric($field)
    {
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $field_name = $field;
            $submitted_val = wlwcn_getInput($field_name);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        if(is_numeric($submitted_val))
        {
            return true;
        }

        $name = self::humanize_field($field);
        $msg = $name.' must be a number.';
        $_SESSION['flash']['validation_errors'][$field] = $msg;

        return false;
    }

    function integer($field)
    {
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $submitted_val = wlwcn_getInput($field);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        if(wlwcn_isInteger($submitted_val))
        {
            return true;
        }

        $name = self::humanize_field($field);
        $msg = $name.' must be an integer.';
        $_SESSION['flash']['validation_errors'][$field] = $msg;

        return false;
    }

    function min($field, $param)
    {
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $field_name = $field;
            $submitted_val = wlwcn_getInput($field_name);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        $valid = true;
        $name = self::humanize_field($field);
        if(is_numeric($submitted_val))
        {
            if($submitted_val < $param)
            {
                $valid = false;
                $msg = $name.' must not be less than '.$param.'.';
            }
        }
        else if(strlen($submitted_val) < $param)
        {
            $valid = false;
            $msg = $name.' must not be less than '.$param.' characters long.';
        }

        if(!$valid)
        {
            $_SESSION['flash']['validation_errors'][$field] = $msg;
            return false;
        }

        return true;
    }

    function max($field, $param)
    {
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $field_name = $field;
            $submitted_val = wlwcn_getInput($field_name);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        $valid = true;
        $name = self::humanize_field($field);
        if(is_numeric($submitted_val))
        {
            if($submitted_val > $param)
            {
                $valid = false;
                $msg = $name.' must not be greater than '.$param.'.';
            }
        }
        else if(strlen($submitted_val) > $param)
        {
            $valid = false;
            $msg = $name.' must not be greater than '.$param.' characters long.';
        }

        if(!$valid)
        {
            $_SESSION['flash']['validation_errors'][$field] = $msg;
            return false;
        }

        return true;
    }

    function required($field)
    {
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $submitted_val = wlwcn_getInput($field);
        }

        if((is_array($submitted_val) && !empty($submitted_val)) || strlen($submitted_val))
        {
            return true;
        }

        $name = self::humanize_field($field);
        $msg = $name.' is required.';

        $_SESSION['flash']['validation_errors'][$field] = $msg;

        return false;
    }

    function email($field)
    {
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $submitted_val = wlwcn_getInput($field);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        if(filter_var($submitted_val, FILTER_VALIDATE_EMAIL))
        {
            return true;
        }

        $name = self::humanize_field($field);
        $msg = $name.' must be a valid email address.';
        $_SESSION['flash']['validation_errors'][$field] = $msg;

        return false;
    }

    function unique($field, $param)
    {
        /*
        rule format => unique:table;column;except_id
        $field => input_field_name
        $param => table;column;except_id
        */

        $param_arr = explode(';', $param);
        $field_arr = explode('.', $field);

        if(isset($field_arr[1]))
        {
            $index = $field_arr[1];
            $field_name = $field_arr[0];
            $submitted_val = wlwcn_getInput($field_name, $index);
        }
        else
        {
            $field_name = $field;
            $submitted_val = wlwcn_getInput($field_name);
        }

        if(!strlen($submitted_val))
        {
            return true;
        }

        if(isset($param_arr[1]))
        {
            $where[$param_arr[1]] = $submitted_val;
            if(isset($param_arr[2]))
            {
                $where['id'] = ['!=', $param_arr[2]];
            }
        }
        else
        {
            $where[$field_name] = $submitted_val;
        }

        $table = $param_arr[0];
        $model = new Model($table);
        try {
            $model = $model->where($where)->withTrashed()->firstOrFail();

            $name = wlwcn_humanize_field($field);
            $msg = $name.' <b>'.$submitted_val.'</b> '.'already exist in the system.';
            setValidationMsg($field, $msg);

            $return = false;
        } catch (Exception $e) {
            $return = true;
        }

        return $return;
    }

    function humanize_field($field)
    {
        return ucfirst(trim(str_replace('_', ' ', $field)));
    }
}
