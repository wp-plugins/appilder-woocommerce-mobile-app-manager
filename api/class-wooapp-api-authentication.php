<?php
/**
 * woocommerce_mobapp API Authentication Class
 *
 * @author      WooThemes
 * @category    API
 * @package     woocommerce_mobapp/API
 * @since       2.1
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WOOAPP_API_Authentication
{

    /**
     * Setup class
     *
     * @since 2.1
     * @return WOOAPP_API_Authentication
     */
    public $titan;

    public function __construct()
    {
        // to disable authentication, hook into this filter at a later priority and return a valid WP_User
        add_filter('woocommerce_mobapp_api_check_authentication', array($this, 'authenticate'), 0);
        add_filter('woocommerce_mobapp_api_set_auth_key', array($this, 'set_user_auth_key'));

        // Reset keys on password change
        add_action('password_reset', function ($user, $pass) {
            $this->set_user_auth_key($user,true);
        }, 10, 2);
        add_action('profile_update', function ($user_id, $old_user_data) {
            if (!isset($_POST['pass1']) || '' == $_POST['pass1']) {
                return;
            }
            $this->set_user_auth_key($user,true);
        }, 10, 2);
        add_action('profile_update', 'my_profile_update');
    }

    /**
     * @param WP_User $user
     * @return bool
     */
    public function set_user_auth_key($user,$force_new = false)
    {
        $user_key = get_user_meta($user->ID, 'woocommerce_mobapp_api_user_key', true);
        if(empty($user_key) || $force_new){
            $user_key = 'uk_' . hash('md5', $user->user_login . date('U') . mt_rand());
            $user_secret = 'us_' . hash('md5', $user->ID . date('U') . mt_rand());
            update_user_meta($user->ID, 'woocommerce_mobapp_api_user_key', $user_key);
            update_user_meta($user->ID, 'woocommerce_mobapp_api_user_secret', $user_secret);
        }
        return true;
    }

    /**
     * Authenticate the request. The authentication method varies based on whether the request was made over SSL or not.
     *
     * @since 2.1
     * @param WP_User $user
     * @return null|WOOAPP_API_Error|WP_User
     */
    public function authenticate($user)
    {
        // allow access to the index by default

        try {
            $this->perform_api_key_check();
            if (in_array(getapi()->server->current_route,
                array('/push/gcm/register', '/', '/meta', '/products', '/products/product', '/products/filters', '/products/reviews', '/menu', '/user_register', '/inAppPages', '/inAppPages/page'
                )))
                return new WP_User(0);
            elseif ('/user_login' === getapi()->server->path) {
                $user = $this->password_auth();
            } else {

                $user = $this->user_auth();
            }

        } catch (Exception $e) {

            $user = new WOOAPP_API_Error('woocommerce_mobapp_api_authentication_error', $e->getMessage(), array('status' => $e->getCode()));
        }
        return $user;
    }

    private function perform_api_key_check()
    {
        $params = getapi()->server->params['GET'];
        // get consumer key
        // get consumer secret
        if (!empty($params['api_key'])) {
            $api_key = $params['api_key'];
        } else {
            throw new Exception(__('API Key is missing', 'woocommerce_mobapp'), 404);
        }

        if (!empty($params['api_secret'])) {
            $api_secret = $params['api_secret'];
        } else {
            throw new Exception(__('API Secret is missing', 'woocommerce_mobapp'), 404);
        }
        global $mobappSettings;
        $app_api_key = $mobappSettings['mobapp-api-key'];
        $app_api_secret = $mobappSettings['mobapp-api-secret'];
        if (is_null($app_api_key) || empty($app_api_key) || is_null($app_api_secret) || empty($app_api_secret)) {
            throw new Exception(__('Plugin has not setup', 'woocommerce_mobapp'), 401);
        }

        if ($app_api_key == $api_key && $app_api_secret == $api_secret)
            return true;
        else
            throw new Exception(__('Invalid API KEY AND SECRET combination ', 'woocommerce_mobapp'), 401);

    }

    /**
     * Password auth
     *
     * @throws Exception
     * @internal param \WP_User $user
     * @return null|WOOAPP_API_Error|WP_User
     */
    private function password_auth()
    {
        $params = getapi()->server->params['POST'];
        if (!empty($params['user_username'])) {
            $user_username = $params['user_username'];
        } else {
            throw new Exception(__('Username is missing', 'woocommerce_mobapp'), 404);
        }
        if (!(empty($params['user_password']) && empty($params['user_pass']))) {
            $user_password = empty($params['user_password'])?$params['user_pass']:$params['user_password'];
        } else {
            throw new Exception(__('Password is missing', 'woocommerce_mobapp'), 404);
        }
        $user = get_user_by('login', $user_username);
        if(!$user){
            $user = get_user_by('email', $user_username);
        }
        if ($user && wp_check_password($user_password, $user->data->user_pass, $user->ID)) {
            $this->set_user_auth_key($user);
            return $user;
        } else
            throw new Exception(__('Invalid username/password', 'woocommerce_mobapp'), 404);
    }

    /**
     * @throws Exception
     * @internal param WP_User $user
     */
    private function user_auth()
    {
        $params = getapi()->server->params['GET'];
        if (!empty($params['user_auth_key'])) {
            $user_auth_key = $params['user_auth_key'];
        } else {
            throw new Exception(__('User auth key is missing', 'woocommerce_mobapp'), 404);
        }
        if (!empty($params['user_auth_secret'])) {
            $user_auth_password = $params['user_auth_secret'];
        } else {
            throw new Exception(__('User auth password is missing', 'woocommerce_mobapp'), 404);
        }
        $user = $this->get_user_by_user_auth_key($user_auth_key);

        if ($user)
            $secret = get_user_meta($user->data->ID, 'woocommerce_mobapp_api_user_secret', true);
        else
            throw new Exception(__('Authentication error', 'woocommerce_mobapp'), 401);

        if ($user && $user_auth_password == $secret)
            return $user;
        else
            throw new Exception(__('Authentication error', 'woocommerce_mobapp'), 401);
    }

    /**
     * Return the user for the given user key
     *
     * @param $user_key
     * @throws Exception
     * @internal param string $consumer_key
     * @return WP_User
     */
    private function get_user_by_user_auth_key($user_key)
    {

        $user_query = new WP_User_Query(
            array(
                'meta_key' => 'woocommerce_mobapp_api_user_key',
                'meta_value' => $user_key,
            )
        );

        $users = $user_query->get_results();

        if (empty($users[0]))
            throw new Exception(__('User Key is invalid', 'woocommerce'), 401);

        return $users[0];
    }

    /**
     * Check that the API keys provided have the proper key-specific permissions to either read or write API resources
     *
     * @param WP_User $user
     * @throws Exception if the permission check fails
     */
    public function check_api_key_permissions($user)
    {
        $key_permissions = $user->woocommerce_mobapp_api_key_permissions;
        switch (getapi()->server->method) {
            case 'HEAD':
            case 'GET':
                if ('read' !== $key_permissions && 'read_write' !== $key_permissions) {
                    throw new Exception(__('The API key provided does not have read permissions', 'woocommerce_mobapp'), 401);
                }
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                if ('write' !== $key_permissions && 'read_write' !== $key_permissions) {
                    throw new Exception(__('The API key provided does not have write permissions', 'woocommerce_mobapp'), 401);
                }
                break;
        }
    }
}
