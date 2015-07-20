<?php
class WOOAPP_Session extends WC_Session_Handler{

    /**
     * Make persistent session for API function.
     *
     * @access public
     * @return mixed
     */
    public function get_session_cookie() {
        if(!is_user_logged_in())
        {
            return false;
        }
        $session_expiring    = time() + intval( apply_filters( 'wc_session_expiring', 60 * 60 * 47 ) ); // 47 Hours
        $session_expiration  = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) ); // 48 Hours
        list( $customer_id, $session_expiration, $session_expiring ) = array(get_current_user_id(),$session_expiration,$session_expiring);
        $to_hash      = get_current_user_id() . $session_expiration;
        $cookie_hash  = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
        return array( $customer_id, $session_expiration, $session_expiring, $cookie_hash );
    }

}