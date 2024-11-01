<?php

namespace Controllers;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

use Eloquent\EmailAddressMessage as EAM;
use Eloquent\NewsletterEM;
use Eloquent\SubscriberEM;
use Eloquent\MailingListEM;
use Models\Wlwcn_Settings;
use Models\Wlwcn_Model;
use Validators\NewsletterValidator;
use Validators\Validator;
use Mail\NewsletterMail;
use Carbon\Carbon;
use Exception;
use Eloquent\Repositories\NewsletterRepository AS NLR;

class NewsletterController extends Controller
{
    protected $model, $subscriber, $mailing_list, $eam, $nlr;

    function __construct()
    {
        $this->model = new NewsletterEM;
        $this->subscriber = new SubscriberEM;
        $this->mailing_list = new MailingListEM;
        $this->eam = new EAM;
        $this->nlr = new NLR;
    }

    function sendSchdeuledMail($request)
    {
        $now = Carbon::now();
        $to = $now->format('Y-m-d H:i:s');
        $from = $now->subMinutes(5)->format('Y-m-d H:i:s');
        $model = $this->model;
        $items = $model->whereNull('sent_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', $to);
            // ->whereBetween('scheduled_at', [$from, $to]);

        if(WP_DEBUG)
        {
           $items = $items->take(3);
        }
        $items = $items->get();

        $item_count = count($items);
        $pretext = "Till: ".$to.'; count: '.$item_count;

        if($item_count)
        {
            $settings = new Settings;
            $this->mail_from = $settings->getMetaSingle('mail_from_address');
            $this->mail_replyto = $settings->getMetaSingle('mail_replyto_address');
            foreach($items as $row)
            {
                $response = self::_sendMail($row);
                $log_msg .= "\n\n------------New Newsletter------------\n".$response;
            }
        }

        $log_msg = "\n".$pretext.$log_msg;

        wlwcn_log($log_msg, true);
    }

    private function _getReceiversFromOptions($receiver_options)
    {
        $subscribers = collect([]);
        if(isset($receiver_options[0]) && ($receiver_options[0] == 'all'))
        {
            $subscribers = $this->subscriber->get();
        }
        else if(isset($receiver_options['selected_mailing_list']))
        {
            $selected_mailing_list = $receiver_options['selected_mailing_list'];
            if($selected_mailing_list)
            {
                $mailing_list = $this->mailing_list->with('subscribers')->where('id', $selected_mailing_list)->first();
                $subscribers = $subscribers->merge($mailing_list->subscribers);
            }
        }
        else if(isset($receiver_options['selected_subscriber']))
        {
            $selected_subscriber = $receiver_options['selected_subscriber'];
            $subscriber = $this->subscriber->where('id', $selected_subscriber)->get();
            $subscribers = $subscribers->merge($subscriber);
        }

        return $subscribers;
    }

    private function _sendMail($newsletter)
    {
        $subscribers = self::_getReceiversFromOptions($newsletter->receiver_options);
        $log_msg = '';
        $eams = [];
        $receivers_count = count($subscribers);
        if($receivers_count)
        {
            $subject = $newsletter->subject;
            $message = $newsletter->message;
            $now = date('Y-m-d H:i:s');
            $sent = 0;
            $exceptions = [];

            foreach($subscribers as $key => $row)
            {
                $fullname = wlwcn_get_fullname($row->f_name, $row->l_name);
                $fullname = $fullname ? $fullname : 'there';

                $msg = preg_replace('/\[Subscriber Name\]/', $fullname, $message, 1);

                $eam = [
                        'subscriber_id' => $row->id,
                        'message_id' => $newsletter->id,
                        'sent_at' => NULL
                    ];

                try {
                    $nm = new NewsletterMail;
                    $nm->send($subject, $msg, $row->email, $this->mail_from, $this->mail_replyto);
                    $eam['sent_at'] = $now;
                    $sent_to = $row->email;
                    $sent += 1;
                } catch (Exception $e) {
                    $exact_now = date('Y-m-d H:i:s');
                    $log_msg .= $exact_now.": ".$e->getMessage()."\n";
                    $exceptions[] = $e->getMessage();
                }

                $eams[] = $eam;
            }

            if(count($eams) && $sent)
            {
                if($sent)
                {
                    $model = $this->model;
                    $update_data['sent_at'] = $now;
                    if($sent == 1)
                    {
                        $update_data['sent_to'] = $sent_to;
                    }
                    $model->where(['id' => $newsletter->id])->update($update_data);
                }

                EAM::insert($eams);
            }
        }

        return $log_msg;
    }

    function show($id)
    {
        try {
            $item = $this->model->with(['subscribersSent', 'subscribersUnsent'])->findOrFail($id);
            $receiver = $this->nlr->getReceiversFromOptions($item->receiver_options);
        } catch (Exception $e) {
            flash('alert_type', 'error');
            flash('alert_msg', 'Item not found');

            wp_redirect(wlwcn_pluginBaseUrl('newsletter'));
            exit;
        }

        $data = [
                'item' => $item,
                'receiver' => $receiver
            ];

        return $data;
    }

    function duplicate($id)
    {
        try {
            $item = $this->model->findOrFail($id);
            $subscribers = $this->subscriber->get();
        } catch (Exception $e) {
            setAlertMessage('Item not found.', 'error');
            wp_redirect(wlwcn_pluginBaseUrl('newsletter'));
            exit;
        }

        $subscribers = $this->subscriber->select('id', 'f_name', 'l_name', 'email')->get();
        $mailing_lists = $this->mailing_list->select('id', 'title')->withCount('subscribers')->get();

        $data = [
                'item' => $item,
                'subscribers' => $subscribers,
                'mailing_lists' => $mailing_lists
            ];
        return $data;
    }

    function edit($id)
    {
        try {
            $item = $this->model->findOrFail($id);
            $subscribers = $this->subscriber->get();
        } catch (Exception $e) {
            setAlertMessage('Item not found.', 'error');
            wp_redirect(wlwcn_pluginBaseUrl('newsletter'));
            exit;
        }

        $subscribers = $this->subscriber->select('id', 'f_name', 'l_name', 'email')->get();
        $mailing_lists = $this->mailing_list->select('id', 'title')->withCount('subscribers')->get();

        $data = [
                'item' => $item,
                'subscribers' => $subscribers,
                'mailing_lists' => $mailing_lists
            ];
        return $data;
    }

    function store()
    {
        $nv = new NewsletterValidator;
        $nv->validateStore();

        $redirect_params = self::_processNewsletter();

        wp_redirect(wlwcn_pluginBaseUrl('newsletter', $redirect_params));
        exit;
    }

    function create()
    {
        $subscribers = $this->subscriber->select('id', 'f_name', 'l_name', 'email')->get();
        $mailing_lists = $this->mailing_list->select('id', 'title')->withCount('subscribers')->get();

        $data = [
                'subscribers' => $subscribers,
                'mailing_lists' => $mailing_lists
            ];

        return $data;
    }

    private function _processNewsletter($update=false)
    {
        $data = [
                'subject' => wlwcn_getInput('subject'),
                'message' => stripslashes(wlwcn_getInput('message'))
            ];

        $send_to = wlwcn_getInput('send_to');
        $receiver_options = [];
        if($send_to == 'selected_mailing_list')
        {
            $mailing_list = wlwcn_getInput('mailing_list');
            $receiver_options = ['selected_mailing_list' => $mailing_list];
        }
        else if($send_to == 'selected_subscriber')
        {
            $subscriber = wlwcn_getInput('subscriber');
            $receiver_options = ['selected_subscriber' => $subscriber];
        }
        else if($send_to == 'all')
        {
            $receiver_options = ['all'];
        }

        $data['receiver_options'] = $receiver_options;

        $when_to_send = wlwcn_getInput('when_to_send');
        $send_now = ($when_to_send == 'now');
        $redirect_params = [];

        if(!$send_now)
        {
            $alert_type = 'success';
            $alert_msg = $update ? 'Newsletter successfully updated.' : 'Newsletter successfully saved.';
        }

        if(!$update)
        {
            $model = $this->model;
            $model->fill($data)->save();
            $newsletter_id = $model->id;
            if($send_now)
            {
                $redirect_params = ['type'=>'newsletter', 'id'=>$newsletter_id];
            }
        }
        else
        {
            $newsletter_id = wlwcn_getInput('id');
            $model = $this->model;
            $model = $model->find($newsletter_id);
            $model->fill($data)->save();

            $redirect_params = ['type'=>'newsletter', 'id'=>$newsletter_id];
        }

        if($send_now && count($receiver_options))
        {
            $subscribers = self::_getReceiversFromOptions($data['receiver_options']);
            $log_msg = '';
            $eams = [];
            $receivers_count = $subscribers->count();
            if($receivers_count)
            {
                $subject = $data['subject'];
                $message = $data['message'];
                $now = date('Y-m-d H:i:s');
                $sent = 0;
                $exceptions = [];

                $settings = new Wlwcn_Settings;
                $mail_from = $settings->getMetaSingle('mail_from_address');
                $mail_replyto = $settings->getMetaSingle('mail_replyto_address');

                foreach($subscribers as $key => $row)
                {
                    $fullname = wlwcn_get_fullname($row->f_name, $row->l_name);
                    $fullname = $fullname ? $fullname : 'there';

                    $msg = preg_replace('/\[Subscriber Name\]/', $fullname, $message, 1);

                    $msg = do_shortcode(wpautop(stripslashes($msg)));

                    $eam = [
                            'subscriber_id' => $row->id,
                            'message_id' => $newsletter_id,
                            'sent_at' => NULL
                        ];

                    try {
                        $nm = new NewsletterMail;
                        $nm->send($subject, $msg, $row->email, $mail_from, $mail_replyto);
                        $eam['sent_at'] = $now;
                        $sent_to = $row->email;
                        $sent += 1;
                    } catch (Exception $e) {
                        $exact_now = date('Y-m-d H:i:s');
                        $log_msg .= "\n".$exact_now.": ".$e->getMessage()." \n";
                        $exceptions[] = $e->getMessage();
                    }

                    $eams[] = $eam;

                    if(WP_DEBUG && $key)
                    {
                        $sent_to_count = (int)$key + 1;
                        $interrupt_msg = "Email sending skipped due to app running on development environment. Email sent to ". $sent_to_count ." of ". $receivers_count." subscribers.\n";
                        wlwcn_log($interrupt_msg, true);
                        break;
                    }
                }

                if(count($eams) && $sent)
                {
                    if($sent)
                    {
                        $model = $this->model;
                        $update_data['sent_at'] = $now;
                        if($sent == 1)
                        {
                            $update_data['sent_to'] = $sent_to;
                        }
                        $model->where(['id' => $newsletter_id])->update($update_data);
                    }

                    EAM::insert($eams);
                    $receiver_plurality = ($receivers_count > 1) ? 's' : '';
                    $sent_plurality = ($sent > 1) ? 's' : '';

                    if($receivers_count == $sent)
                    {
                        $alert_type = 'success';
                        $alert_msg = "Newsletter successfully sent to ".$sent." subscriber".$sent_plurality.'.';
                    }
                    else if($sent >= 1)
                    {
                        $alert_type = 'warning';
                        $alert_msg = "Newsletter only sent to ".$sent." of ".$receivers_count." subscriber".$receiver_plurality.".";
                        $redirect_to = wlwcn_pluginBaseUrl('newsletter', ['type'=>'newsletter', 'id'=>$newsletter_id]);
                    }

                    $redirect_params = ['type'=>'newsletter', 'id'=>$newsletter_id];
                }
                else
                {
                    $alert_type = 'error';
                    $alert_msg = "Newsletter couldn't be sent currently. Please identify & fix the issue & try again later.";
                    $redirect_params = ['type'=>'newsletter', 'id'=>$newsletter_id, 'action'=>'edit'];
                }

                if($log_msg)
                {
                    $title = "//////////////////////////////////////////\n\nMail Exception on: ".date("F j, Y, g:i:s a")."\n"."Total Receivers: ".$receivers_count;

                    $log_msg = $title.$log_msg;

                    wlwcn_log($log_msg, true);
                }
            }
        }

        wlwcn_setAlertMessage($alert_msg, $alert_type);

        return $redirect_params;
    }

    function update($request)
    {
        $validator = new NewsletterValidator;
        $validator->validateUpdate();

        $redirect_params = self::_processNewsletter($request, true);
        wp_redirect(wlwcn_pluginBaseUrl('newsletter', $redirect_params));
        exit;
    }

    function delete($request)
    {
        $rules = ['id' => 'integer|min:0'];
        $validator = new Validator;
        $validator->validate($rules);
        if($this->model->where('id', $request['id'])->delete())
        {
            $_SESSION['flash']['alert_type'] = 'success';
            $_SESSION['flash']['alert_msg'] = 'Newsletter successfully deleted.';
        }
        else
        {
            $_SESSION['flash']['alert_type'] = 'error';
            $_SESSION['flash']['alert_msg'] = "Newsletter couldn't be deleted, please try again later";
        }

        wp_redirect(wlwcn_pluginBaseUrl('newsletter'));
        exit;
    }

    function paginate()
    {
        $model = $this->model;
        $model = $model->with('subscriber')->withCount(['subscribers', 'subscribersSent']);

        $search = getSearchStr();
        $where_arr = [];
        if($search)
        {
            $search_query = '%'.$search.'%';
            $where_arr = [
                    ['subject', 'like', $search_query],
                    ['sent_to', 'like', $search_query],
                    ['sent_at', 'like', $search_query],
                    ['scheduled_at', 'like', $search_query],
                    ['created_at', 'like', $search_query]
                ];
        }

        foreach($where_arr as $key => $where)
        {
            $model = $model->orWhere($where[0], $where[1], $where[2]);
        }

        $valid_order_fields = ['subject', 'sent_to', 'scheduled_at', 'sent_at', 'created_at'];

        $orderby = 'id';
        $order = 'desc';

        if(isset($_GET['orderby']))
        {
            if(in_array($_GET['orderby'], $valid_order_fields))
            {
                $orderby = $_GET['orderby'];
                $order = 'asc';
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

        $bm = new Wlwcn_Model;
        $per_page = $bm->getPerPage();
        $select_columns = ['*'];
        $page_name = 'pg';
        $pg = wlwcn_getInput($page_name) ? wlwcn_getInput($page_name) : 1;

        $model = $model->paginate($per_page, $select_columns, $page_name, $pg);

        return $model;
    }
}
