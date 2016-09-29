<?php

namespace CartRabbit\Helper;

class OrderMail
{

    public static $instance = null;

    public function __construct($properties = null)
    {
    }

    /**
     * get an instance
     * @param array $config
     * @return object
     */
    public static function getInstance(array $config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * For Subscription Pending
     * */
    public static function newOrderMail($status)
    {
        global $current_user;
        $user_email = $current_user->user_email;
        if (\CartRabbit\Helper\Util::validateEmail($user_email)) {
            $mail = SendMail::getInstance();
            $mail->set('to', $user_email);
            $mail->set('subject', 'New Order Created Successfully !');
            $template = view('@CartRabbit/Site/Account/MailTemplates/NewOrder.twig', compact('status'));
            if ($template->getStatusCode() == 200) {
                $body = $template->getBody();
                $mail->set('body', $body);
                return $mail->sendMail();
            } else {
                return false;
            }
        }
    }

    public static function orderCompletedMail()
    {
        global $current_user;
        $user_email = $current_user->user_email;
        if (\CartRabbit\Helper\Util::validateEmail($user_email)) {
            $mail = SendMail::getInstance();
            $mail->set('to', $user_email);
            $mail->set('subject', 'Order Payment Completed !');
            $template = view('@CartRabbit/Site/Account/MailTemplates/OrderComplete.twig');
            if ($template->getStatusCode() == 200) {
                $body = $template->getBody();
                $mail->set('body', $body);
                return $mail->sendMail();
            } else {
                return false;
            }
        }
    }
}