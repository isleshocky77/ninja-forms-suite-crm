<?php

/*
 * Process the submitted form to send the data to Suite
 *
 */

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

function nfsuitecrm_refresh_suite_objects() {

    $stack = HandlerStack::create();

    $middleware = new Oauth1([
        'request_method'    => Oauth1::REQUEST_METHOD_QUERY,
        'signature_method'  => Oauth1::SIGNATURE_METHOD_HMAC,
        'consumer_key'      => Ninja_Forms()->get_setting('nfsuitecrm_consumer_key'),
        'consumer_secret'   => Ninja_Forms()->get_setting('nfsuitecrm_consumer_secret'),
        'token'             => Ninja_Forms()->get_setting('nfsuitecrm_access_token'),
        'token_secret'      => Ninja_Forms()->get_setting('nfsuitecrm_access_token_secret'),
    ]);
    $stack->push($middleware);

    $client = new Client([
        'base_uri' => Ninja_Forms()->get_setting('nfsuitecrm_url'),
        'handler' => $stack,
        'auth' => 'oauth',
    ]);

    try {
        // Supressing notices until PR https://github.com/guzzle/oauth-subscriber/pull/49
        $response = @$client->get('/service/v4_1/rest.php', [
            'query' => [
                'request_type' => 'JSON',
                'response_type' => 'JSON',
                'input_type' => 'JSON',
                'method' => 'get_available_modules',
            ]]);
    } catch (Exception $e) {
        nfsuitecrm_update_comm_data([
            'status' => 'Error connecting to API:' .  $e->getMessage(),
            'debug' => 'Error connecting to API:' .  $e->getMessage(),
        ]);

        wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
        exit;
    }

    $object = json_decode((string) $response->getBody());

    $new_suite_account_data['object_list'] = [];
    foreach ($object->modules as $module) {
        $new_suite_account_data['object_list'][] = $module->module_key;
    }

    # Get Session Id
    try {
        // Supressing notices until PR https://github.com/guzzle/oauth-subscriber/pull/49
        $response = @$client->get('/service/v4_1/rest.php', [
            'query' => [
                'request_type' => 'JSON',
                'response_type' => 'JSON',
                'input_type' => 'JSON',
                'method' => 'oauth_access',
            ]]);
    } catch (Exception $e) {
        nfsuitecrm_update_comm_data([
            'status' => 'Error connecting to API:' .  $e->getMessage(),
            'debug' => 'Error connecting to API:' .  $e->getMessage(),
        ]);

        wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
        exit;
    }
    $object = json_decode((string) $response->getBody());
    $session_id = $object->id;


    /*
     * Cycle through each object specified in settings and
     * create a Field List for each object
     *
     */
    if (isset($nfsuitecrm_settings['nfsuitecrm_available_objects']) && 0 < strlen($nfsuitecrm_settings['nfsuitecrm_available_objects'])) {

        $available_object_array = explode(',', $nfsuitecrm_settings['nfsuitecrm_available_objects']);
    } else {

        $available_object_array = array('Leads');
    }

    /*
     * Cycle through each object in the list to retrieve the field list
     *
     */
    foreach ($available_object_array as $untrimmed_object) {

        $object_name = trim($untrimmed_object);

        $get_module_fields_parameters = [
            'session'       => $session_id,
            'module_name'   => $object_name,
            'fields'        => [],
        ];

        try {
            // Supressing notices until PR https://github.com/guzzle/oauth-subscriber/pull/49
            $response = @$client->post('/service/v4_1/rest.php', [
                'query' => [
                    'request_type' => 'JSON',
                    'response_type' => 'JSON',
                    'input_type' => 'JSON',
                    'method' => 'get_module_fields',
                    'rest_data' => json_encode($get_module_fields_parameters)
                ]]);
        } catch (Exception $e) {
            nfsuitecrm_update_comm_data([
                'status' => 'Error connecting to API:' .  $e->getMessage(),
                'debug' => 'Error connecting to API:' .  $e->getMessage(),
            ]);

            wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
            exit;
        }
        $object = json_decode((string) $response->getBody());

        foreach ($object->module_fields as $field) {
            $new_suite_account_data['field_list'][$object_name][$field->name] = $field->label;
        }

    }

    nfsuitecrm_update_account_data($new_suite_account_data);

    wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
    exit;
}
