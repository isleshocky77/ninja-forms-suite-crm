<?php if ( ! defined( 'ABSPATH' ) ) exit;

return apply_filters( 'nf_sugar_plugin_settings', array(

    /*
    |--------------------------------------------------------------------------
    | Client Key and Secret
    |--------------------------------------------------------------------------
    */

    'nfsugarcrm_url' => array(
        'id'    => 'nfsugarcrm_url',
        'type'  => 'textbox',
        'label' => __( 'Url', 'ninja-forms-sugar-crm' ),
        'desc'  => __( 'Enter your installation url (e.g. https://example.localhost)' ),
    ),
    'nfsugarcrm_consumer_key' => array(
        'id'    => 'nfsugarcrm_consumer_key',
        'type'  => 'textbox',
        'label' => __( 'Consumer Key', 'ninja-forms-sugar-crm' ),
    ),
    'nfsugarcrm_consumer_secret' => array(
        'id'    => 'nfsugarcrm_consumer_secret',
        'type'  => 'textbox',
        'label' => __( 'Consumer Secret', 'ninja-forms-sugar-crm' ),
    ),
    /*
    |--------------------------------------------------------------------------
    | Open Auth - authorization code
    | Retrieved after storing Key and Secret
    |--------------------------------------------------------------------------
    */
    'nfsugarcrm_authorization_code_instructions'=> array(
        'id'    => 'nfsugarcrm_authorization_code_instructions',
        'type'  => 'html',
        'label' => __( 'Authorization Code Setup', 'ninja-forms-sugar-crm' ),
        'html' => '',// created on construction
    ),
    'nfsugarcrm_authorization_code' => array(
        'id'    => 'nfsugarcrm_authorization_code',
        'type'  => 'textbox',
        'label' => __( 'Authorization Code', 'ninja-forms-sugar-crm' ),
    ),
    'nfsugarcrm_refresh_token_instructions'=> array(
        'id'    => 'nfsugarcrm_refresh_token_instructions',
        'type'  => 'html',
        'label' => __( 'Generate Access Token', 'ninja-forms-sugar-crm' ),
        'html' => '',// created on construction
    ),
    /*
     * REFRESH TOKEN, in 3.0 is stored in account data
     * In 2.9, it is stored with settings by using readonly field
     */
    'nfsugarcrm_refresh_token' => array(
        'id'    => 'nfsugarcrm_refresh_token',
        'type'  => 'html',
        'label' => __( 'Access Token', 'ninja-forms-sugar-crm' ),
        'html' => '',// created on construction
    ),
    /*
    |--------------------------------------------------------------------------
    | List of Objects to refresh
    |
    |--------------------------------------------------------------------------
    */

    'nfsugarcrm_refresh_objects_instructions' => array(
        'id'    => 'nfsugarcrm_refresh_objects_instructions',
        'type'  => 'html',
        'label' => __( 'Click to refresh Objects', 'ninja-forms-sugar-crm' ),
        'html' => '',// created on construction
    ),

    'nfsugarcrm_available_objects' => array(
        'id'    => 'nfsugarcrm_available_objects',
        'type'  => 'textbox',
        'label' => __( 'Objects to Retrieve', 'ninja-forms-sugar-crm' ),
    ),

     'nfsugarcrm_comm_data_status' => array(
        'id'    => 'nfsugarcrm_comm_data_status',
        'type'  => 'html',
        'label' => __( 'Status', 'ninja-forms-sugar-crm' ),
        'html' => '',// created on construction
    ),

    /*
    |--------------------------------------------------------------------------
    | Advanced commands
    |--------------------------------------------------------------------------
    */
    'nfsugarcrm_advanced_codes' => array(
        'id'    => 'nfsugarcrm_advanced_codes',
        'type'  => 'textbox',
        'label' =>__( 'Advanced Commands', 'ninja-forms-sugar-crm' ),
    ),
    /*
    |--------------------------------------------------------------------------
    | Support Functions
    |--------------------------------------------------------------------------
    */
    'nfsugarcrm_comm_data_debug' => array(
        'id'    => 'nfsugarcrm_comm_data_debug',
        'type'  => 'html',
        'label' => __( 'Debug', 'ninja-forms-sugar-crm' ),
        'html' => '',// created on construction
    ),

    /*
    |--------------------------------------------------------------------------
    | Sugar Objects and Fields
    |--------------------------------------------------------------------------
    */
    'nfsugarcrm_account_data' => array(
        'id'    => 'nfsugarcrm_account_data',
        'type'  => 'html',
        'label' => __( 'Availalable Sugar Fields and Objects', 'ninja-forms-sugar-crm' ),
        'html' => '',// created on construction
    ),




));
