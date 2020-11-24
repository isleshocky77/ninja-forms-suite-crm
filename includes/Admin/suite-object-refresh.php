<?php

/*
 * Process the submitted form to send the data to Suite
 *
 */

use GuzzleHttp\Client;

function nfsuitecrm_refresh_suite_objects() {

    $client = new Client([
        'base_uri' => Ninja_Forms()->get_setting('nfsuitecrm_url'),
        'headers' => [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ],
    ]);

    try {
        $response = $client->request('POST', '/Api/access_token', [
            'json' => [
                'grant_type' => 'client_credentials',
                'client_id' => Ninja_Forms()->get_setting('nfsuitecrm_consumer_key'),
                'client_secret' => Ninja_Forms()->get_setting('nfsuitecrm_consumer_secret'),
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
        $response = $client->request('GET', '/Api/V8/meta/modules');
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
    foreach ($body->data->attributes as $moduleName => $moduleMeta) {
        $new_suite_account_data['object_list'][] = $moduleName;

        $objectsToRetrieve = Ninja_Forms()->get_setting('nfsuitecrm_available_objects', 'Leads,Contacts');
        if (!in_array($moduleName, explode(',', $objectsToRetrieve))) {
            continue;
        }

        // Get Fields
        try {
            $response = $client->request('GET', '/Api/V8/meta/fields/' . $moduleName);
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

        foreach ($body->data->attributes as $field => $fieldAttributes) {

            $allowedType = [ 'bool','date','datetime','email','enum','id','multienum','phone','text','url','varchar'];
            if (!in_array($fieldAttributes->type, $allowedType)) {
                continue;
            }

            $new_suite_account_data['field_list'][$moduleName][$field] = $field;

        }
    }

    nfsuitecrm_update_account_data($new_suite_account_data);

    wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
    exit;
}
