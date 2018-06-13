<?php

/*
 * Process the submitted form to send the data to Suite
 *
 */

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

function nfsuitecrm_refresh_suite_objects() {

    $client = new Client([
        'base_uri' => Ninja_Forms()->get_setting('nfsuitecrm_url'),
        'headers' => [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ],
    ]);

    try {
        $response = $client->request('POST', '/api/oauth/access_token', [
            'json' => [
                'grant_type' => 'client_credentials',
                'client_id' => Ninja_Forms()->get_setting('nfsuitecrm_consumer_key'),
                'client_secret' => Ninja_Forms()->get_setting('nfsuitecrm_consumer_secret'),
                'scope' => 'standard:create',
            ]
        ]);
        nfsuitecrm_update_comm_data(['status' => 'Success','debug' => 'Success',]);

        $accessToken = json_decode($response->getBody()->getContents());
    } catch (Exception $e) {
        nfsuitecrm_update_comm_data([
            'status' => 'Error connecting to API:' . $e->getMessage(),
            'debug' => 'Error connecting to API:' . $e->getMessage(),
        ]);

        wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
        exit;
    }


    $client = new Client([
        'base_uri' => Ninja_Forms()->get_setting('nfsuitecrm_url'),
        'headers' => [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            'Authorization' => 'Bearer ' . $accessToken->access_token,
        ],
    ]);

    try {
        $response = $client->request('GET', '/api/v8/modules/meta/list');
        nfsuitecrm_update_comm_data(['status' => 'Success','debug' => 'Success',]);
    } catch (Exception $e) {
        nfsuitecrm_update_comm_data([
            'status' => 'Error connecting to API:' . $e->getMessage(),
            'debug' => 'Error connecting to API:' . $e->getMessage(),
        ]);

        wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
        exit;
    }

    $body = json_decode((string) $response->getBody());

    $new_suite_account_data['object_list'] = [];
    foreach ($body->meta->modules->list as $moduleName => $moduleMeta) {
        $new_suite_account_data['object_list'][] = $moduleName;

        // Get Language
        try {
            $response = $client->request('GET', '/api/v8/modules/' . $moduleName . '/meta/language');
            nfsuitecrm_update_comm_data(['status' => 'Success','debug' => 'Success',]);
        } catch (Exception $e) {
            nfsuitecrm_update_comm_data([
                'status' => 'Error connecting to API:' . $e->getMessage(),
                'debug' => 'Error connecting to API:' . $e->getMessage(),
            ]);

            wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
            exit;
        }

        $body = json_decode((string) $response->getBody());
        $language = $body->meta->$moduleName->language;

        // Get Fields
        try {
            $response = $client->request('GET', '/api/v8/modules/' . $moduleName . '/meta/attributes');
            nfsuitecrm_update_comm_data(['status' => 'Success','debug' => 'Success',]);
        } catch (Exception $e) {
            nfsuitecrm_update_comm_data([
                'status' => 'Error connecting to API:' . $e->getMessage(),
                'debug' => 'Error connecting to API:' . $e->getMessage(),
            ]);

            wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
            exit;
        }

        $body = json_decode((string) $response->getBody());

        if (!is_object($body->meta->$moduleName->attributes) || !property_exists($body->meta->$moduleName->attributes, 'id')) {
            continue;
        }
        foreach ($body->meta->$moduleName->attributes as $field => $fieldAttributes) {
            if (property_exists($fieldAttributes, 'vname') && property_exists($language, $fieldAttributes->vname)) {
                $new_suite_account_data['field_list'][$moduleName][$fieldAttributes->name] = $language->{$fieldAttributes->vname};
            } else {
                $new_suite_account_data['field_list'][$moduleName][$fieldAttributes->name] = $fieldAttributes->name;
            }

        }
    }

    nfsuitecrm_update_account_data($new_suite_account_data);

    wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
    exit;
}
