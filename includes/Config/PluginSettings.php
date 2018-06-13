<?php if ( ! defined( 'ABSPATH' ) ) exit;

return apply_filters( 'nf_suite_plugin_settings', array(

    /*
    |--------------------------------------------------------------------------
    | Client Key and Secret
    |--------------------------------------------------------------------------
    */

    'nfsuitecrm_url' => array(
        'id'    => 'nfsuitecrm_url',
        'type'  => 'textbox',
        'label' => __( 'Url', 'ninja-forms-suite-crm' ),
        'desc'  => __( 'Enter your installation url (e.g. https://example.localhost)' ),
    ),
    'nfsuitecrm_consumer_key' => array(
        'id'    => 'nfsuitecrm_consumer_key',
        'type'  => 'textbox',
        'label' => __( 'Consumer Key', 'ninja-forms-suite-crm' ),
    ),
    'nfsuitecrm_consumer_secret' => array(
        'id'    => 'nfsuitecrm_consumer_secret',
        'type'  => 'textbox',
        'label' => __( 'Consumer Secret', 'ninja-forms-suite-crm' ),
    ),
    /*
    |--------------------------------------------------------------------------
    | Open Auth - authorization code
    | Retrieved after storing Key and Secret
    |--------------------------------------------------------------------------
    */
    'nfsuitecrm_authorization_code_instructions'=> array(
        'id'    => 'nfsuitecrm_authorization_code_instructions',
        'type'  => 'html',
        'label' => __( 'Authorization Code Setup', 'ninja-forms-suite-crm' ),
        'html' => '',// created on construction
    ),
    'nfsuitecrm_authorization_code' => array(
        'id'    => 'nfsuitecrm_authorization_code',
        'type'  => 'textbox',
        'label' => __( 'Authorization Code', 'ninja-forms-suite-crm' ),
    ),
    'nfsuitecrm_refresh_token_instructions'=> array(
        'id'    => 'nfsuitecrm_refresh_token_instructions',
        'type'  => 'html',
        'label' => __( 'Generate Access Token', 'ninja-forms-suite-crm' ),
        'html' => '',// created on construction
    ),
    /*
     * REFRESH TOKEN, in 3.0 is stored in account data
     * In 2.9, it is stored with settings by using readonly field
     */
    'nfsuitecrm_refresh_token' => array(
        'id'    => 'nfsuitecrm_refresh_token',
        'type'  => 'html',
        'label' => __( 'Access Token', 'ninja-forms-suite-crm' ),
        'html' => '',// created on construction
    ),
    /*
    |--------------------------------------------------------------------------
    | List of Objects to refresh
    |
    |--------------------------------------------------------------------------
    */

    'nfsuitecrm_refresh_objects_instructions' => array(
        'id'    => 'nfsuitecrm_refresh_objects_instructions',
        'type'  => 'html',
        'label' => __( 'Click to refresh Objects', 'ninja-forms-suite-crm' ),
        'html' => '',// created on construction
    ),

    'nfsuitecrm_available_objects' => array(
        'id'    => 'nfsuitecrm_available_objects',
        'type'  => 'textbox',
        'label' => __( 'Objects to Retrieve', 'ninja-forms-suite-crm' ),
    ),

     'nfsuitecrm_comm_data_status' => array(
        'id'    => 'nfsuitecrm_comm_data_status',
        'type'  => 'html',
        'label' => __( 'Status', 'ninja-forms-suite-crm' ),
        'html' => '',// created on construction
    ),

    /*
    |--------------------------------------------------------------------------
    | Advanced commands
    |--------------------------------------------------------------------------
    */
    'nfsuitecrm_advanced_codes' => array(
        'id'    => 'nfsuitecrm_advanced_codes',
        'type'  => 'textbox',
        'label' =>__( 'Advanced Commands', 'ninja-forms-suite-crm' ),
    ),
    /*
    |--------------------------------------------------------------------------
    | Support Functions
    |--------------------------------------------------------------------------
    */
    'nfsuitecrm_comm_data_debug' => array(
        'id'    => 'nfsuitecrm_comm_data_debug',
        'type'  => 'html',
        'label' => __( 'Debug', 'ninja-forms-suite-crm' ),
        'html' => '',// created on construction
    ),

    /*
    |--------------------------------------------------------------------------
    | Suite Objects and Fields
    |--------------------------------------------------------------------------
    */
    'nfsuitecrm_account_data' => array(
        'id'    => 'nfsuitecrm_account_data',
        'type'  => 'html',
        'label' => __( 'Availalable Suite Fields and Objects', 'ninja-forms-suite-crm' ),
        'html' => '',// created on construction
    ),




));
