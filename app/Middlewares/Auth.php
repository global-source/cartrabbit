<?php
namespace CartRabbit\Middlewares;

use CartRabbit\Helper;
use Exception;

//use MongoDB\Driver\Exception\Exception

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
     *
     */
    public function handle($request)
    {
        $user_rights = $this->getUserRights();
        try {
            if (in_array($this->rights, $user_rights)) {
                $request->handle();
            } else {
                throw new Exception('Error On Handling');
            }
        } catch (Exception $e) {
            dd($e->getMessage());
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