<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WOOAPP_API_pushNotification  extends WOOAPP_API_Resource {
    /** @var string $base the route base */
    protected $base = '/push';
    protected $pushNotification;

    /**
     * @param $routes
     * @return mixed
     */
    public function register_routes( $routes )
    {
        # GET /coupons/code/<code>, note that coupon codes can contain spaces, dashes and underscores
        $routes[$this->base . '/gcm/register'] = array(
            array(array($this, 'gcm_register'), WOOAPP_API_Server::METHOD_POST),
        );
        $routes[$this->base . '/gcm/remove'] = array(
            array(array($this, 'gcm_remove'), WOOAPP_API_Server::METHOD_POST),
        );
        $routes[$this->base . '/send'] = array(
            array(array($this, 'send'), WOOAPP_API_Server::METHOD_POST),
        );
        return $routes;
    }

    /**
     * @param $id
     * @return array
     */
    public function gcm_register($id)
    {
        if (!is_a($this->pushNotification, "WOOAPP_API_Core_pushNotification"))
            $this->pushNotification = new WOOAPP_API_Core_pushNotification();
        $return = array();
        if ($this->pushNotification->register($id, WOOAPP_API_Core_pushNotification::$gcm))
            $return['status'] = 1;
        else
           $return = WOOAPP_API_Error::setError($return, "push_notification_already_registered", "Device already registered");
        return $return;
    }

    /**
     * @param $id
     * @return array
     */
    public function gcm_remove($id)
    {
        if (!is_a($this->pushNotification, "WOOAPP_API_Core_pushNotification"))
            $this->pushNotification = new WOOAPP_API_Core_pushNotification();
        $return = array();
        if ($this->pushNotification->remove($id, WOOAPP_API_Core_pushNotification::$gcm))
            $return['status'] = 1;
        else
           $return = WOOAPP_API_Error::setError($return, "push_notification_not_registered", "Device not registered");
        return $return;
    }

    /**
     * @param $message
     * @param $title
     * @param $content
     * @param $actionType
     * @param $actionParam
     * @return array
     *  @todo Make proper Response
     */
    public function send($message,$title,$content,$actionType,$actionParam){
        if (!is_a($this->pushNotification, "WOOAPP_API_Core_pushNotification"))
            $this->pushNotification = new WOOAPP_API_Core_pushNotification();
        $this->pushNotification->sendPush($message,$title,$actionType,$actionParam);
        return array("status"=>1);
    }
} 