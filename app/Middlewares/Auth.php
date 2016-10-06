<?php
namespace CartRabbit\Middlewares;

use CartRabbit\Helper;
use Exception;

/**
 * Class Middleware
 * @package CartRabbit\library
 */
class Auth
{
    /**
     * @var
     */
    protected $request;

    /**
     * @var string
     */
    protected $rights = 'administrator';

    /**
     * Auth constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Managing Requests
     */
    public function handle($request)
    {
        $user_rights = $this->getUserRights();
        try {
            if (in_array($this->rights, $user_rights)) {
                return $request;
            } else {
                wp_die(__('You do not have sufficient permissions to access this page.'), 403);
            }
        } catch (Exception $e) {
            wp_die(__($e->getMessage()), 403);;
        }
    }

    /**
     * @return mixed
     */
    public function getUserRights()
    {
        return wp_get_current_user()->roles;
    }

}