<?php

namespace CartRabbit\Helper;

class SendMail
{

    public static $instance = null;

    public $to = array();
    public $cc = array();
    public $subject = '';
    public $body = '';
    public $headers = array();
    public $attachments = array();

    public function __construct($properties = null)
    {
    }

    /**
     * get an instance
     * @param array $config
     * @return object
     * * */
    public static function getInstance(array $config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * For Sending mail
     * */
    public function sendMail()
    {
        if (!empty($this->cc)) {
            foreach ($this->cc as $val) {
                $this->headers[] = 'Cc: ' . $val;
            }
        }
        $this->headers[] = 'MIME-Version: 1.0' . "\r\n";
        $this->headers[] = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        return wp_mail($this->to, $this->subject, $this->body, $this->headers, $this->attachments);
    }

    /**
     * get property
     * */
    public function get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    /**
     * set property
     * */
    public function set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}