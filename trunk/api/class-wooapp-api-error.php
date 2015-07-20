<?php
/**
 * User: Mohammed Anees
 * Date: 5/10/14
 */

class WOOAPP_API_Error extends WP_Error{
    function __construct($code = '', $message = '', $data = '') {
        parent::__construct($code, $message, $data);
           // if ( !empty($this->error_data[$code]) && isset($this->error_data[$code]['status']))
           //     $this->error_data[$code]['status'] = 200;
    }

    /**
     * @param WOOAPP_API_Error|string $error
     * @param string $code
     * @param string $message
     * @param string $data
     * @return array|\WOOAPP_API_Error
     */
    static function setError($error,$code = '', $message = '', $data = ''){
        if(is_wooapp_api_error($error)){
            $error->add($code,$message,$data);
        }else{
            $error = new WOOAPP_API_Error($code,$message,$data);
        }
        return $error;
    }
}

/**
 * Check whether variable is a API Error.
 *
 * Returns true if $thing is an object of the WOOAPP_API_Error class.
 * @param mixed $thing Check if unknown variable is a WP_Error object.
 * @return bool True, if WP_Error. False, if not WP_Error.
 */
function is_wooapp_api_error($thing) {
    if ( is_object($thing) && is_a($thing, 'WOOAPP_API_Error') )
        return true;
    return false;
}