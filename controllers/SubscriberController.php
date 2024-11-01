<?php

namespace Controllers;

use Models\Wlwcn_Subscriber;
use Models\Wlwcn_Model;
use Validators\Validator;
use Exception;
use WC_Coupon;

class SubscriberController extends Controller
{
    protected $settings;

    function __construct()
    {
        $this->model = new Wlwcn_Subscriber;
    }

    function edit($id)
    {
        try {
            $item = $this->model->findOrFail($id);
        } catch (Exception $e) {
            wlwcn_flash('alert_type', 'error');
            wlwcn_flash('alert_msg', $e->getMessage());

            wp_redirect(wlwcn_pluginBaseUrl('subscriber'));
            exit;
        }

        return $item;
    }

    function update($request)
    {

        $plugin_prefix = wlwcn_getPluginTablePrefix();
        $rules = [
                'id' => 'required|integer|min:0|exists:'.$plugin_prefix.'email_addresses',
                'subscription_coupon' => 'max:190|exists:posts;post_title;post_type=shop_coupon',
                'f_name' => 'max:190',
                'l_name' => 'max:190',
                'notes' => 'max:100000'
            ];
        $validator = new Validator;
        $validator->validate($rules);
        $subscription_coupon = sanitize_text_field($request['subscription_coupon']);
        $coupon = new WC_Coupon($subscription_coupon);
        $data = [
                'f_name' => trim(sanitize_text_field($request['f_name'])),
                'l_name' => trim(sanitize_text_field($request['l_name'])),
                'subscription_coupon' => trim($subscription_coupon),
                'coupon_id' => $coupon->id,
                'notes' => stripslashes(wlwcn_getInput('notes'))
            ];

        $model = $this->model;
        try {
            $model->where(['id' => sanitize_text_field($request['id'])])->update($data);

            wlwcn_flash('alert_type', 'success');
            wlwcn_flash('alert_msg', 'Subscriber successfully updated.');
        } catch (Exception $e) {
            wlwcn_flash('alert_type', 'error');
            wlwcn_flash('alert_msg', $e->getMessage());
        }

        wp_redirect(wlwcn_previousUrl());
        exit;
    }

    function delete($request)
    {
        $rules = ['id' => 'integer|min:0'];
        $validator = new Validator;
        $validator->validate($rules);

        if($this->model->delete(sanitize_text_field($request['id'])))
        {
            $_SESSION['flash']['alert_type'] = 'success';
            $_SESSION['flash']['alert_msg'] = 'Subscriber successfully deleted.';
        }
        else
        {
            $_SESSION['flash']['alert_type'] = 'danger';
            $_SESSION['flash']['alert_msg'] = "Subscriber couldn't be deleted, please try again later";
        }

        wp_redirect(wlwcn_pluginBaseUrl('subscriber'));
        exit;
    }

    function paginate()
    {
        $model = $this->model;

        $search = wlwcn_getSearchStr();
        if($search)
        {
            $search_query = '%'.$search.'%';
            $where = [
                    'email' => ['like', $search_query],
                    'f_name' => ['like', $search_query],
                    'l_name' => ['like', $search_query],
                    'subscription_coupon' => ['like', $search_query],
                    'created_at' => ['like', $search_query],
                ];
            $model = $model->orWhere($where);
        }

        $valid_order_fields = ['email', 'created_at'];
        $order_types = ['asc', 'desc'];

        $orderby = '';
        $order = 'asc';

        if(isset($_GET['orderby']))
        {
            if(in_array($_GET['orderby'], $valid_order_fields))
            {
                $orderby = sanitize_text_field($_GET['orderby']);

                if(isset($_GET['order']) && ($_GET['order'] == 'desc'))
                {
                    $order = 'desc';
                }
            }
        }

        if($orderby)
        {
            $model = $model->orderby($orderby, $order);
        }

        return $model->paginate();
    }
}
