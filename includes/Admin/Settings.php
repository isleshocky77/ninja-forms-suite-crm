<?php

if (!defined('ABSPATH'))
    exit;

/**
 * Configures the plugin settings and formats support data
 *
 * Uses shared functions from Functions.php
 */
final class NF_SugarCRM_Admin_Settings {

    /**
     *
     * @var array Communication data, used to update support fields
     */
    protected $comm_data;

    /**
     *
     * @var string URL for requesting the authorization code
     */
    protected $auth_code_url_link;

    /**
     *
     * @var array Stored Sugar settings retrieved from global variable
     *
     * Global variable is used b/c it is common with 2.9x and downstream classes
     * rely on it
     */
    protected $sugar_settings;

    /**
     *
     * @var array Account data of Version, Objects, and Fields
     */
    protected $account_data;

    public function __construct() {

        global $nfsugarcrm_settings; // bring in global settings shared with 2.

        $this->sugar_settings = $nfsugarcrm_settings;

        $this->comm_data = NF_SugarCRM()->get_nfsugarcrm_comm_data();

        $this->account_data = nfsugarcrm_output_account_data();


        add_filter('ninja_forms_plugin_settings', array($this, 'plugin_settings'), 10, 1);
        add_filter('ninja_forms_plugin_settings_groups', array($this, 'plugin_settings_groups'), 10, 1);
    }

    public function plugin_settings($settings) {

        $configured_settings = NF_SugarCRM()->config('PluginSettings');

        $configured_settings['nfsugarcrm_authorization_code_instructions']['html'] = $this->build_authorization_code_markup();

        $configured_settings['nfsugarcrm_refresh_token_instructions']['html'] = $this->build_refresh_token_instructions_markup();

        $configured_settings['nfsugarcrm_refresh_token']['html'] = Ninja_Forms()->get_setting('nfsugarcrm_access_token');

        $configured_settings['nfsugarcrm_refresh_objects_instructions']['html'] = $this->build_refresh_objects_instructions_markup();

        $configured_settings['nfsugarcrm_comm_data_status']['html'] = $this->build_status_markup();

        $configured_settings['nfsugarcrm_comm_data_debug']['html'] = serialize($this->comm_data['debug']); // debug is an array - need to explode and markup

        $configured_settings['nfsugarcrm_account_data']['html'] = $this->account_data;

        $advanced_codes = nfsugarcrm_extract_advanced_codes();

        $support_mode_code = 'support';

        if (!in_array($support_mode_code, $advanced_codes)) {

            $support_mode_settings = array(
                'nfsugarcrm_comm_data_debug',
            );

            foreach ($support_mode_settings as $setting) {

                unset($configured_settings[$setting]);
            }
        }

        $hide_setup_code = 'hide_setup';

        if (in_array($hide_setup_code, $advanced_codes)) {

            $setup_settings_array = array(
                'nfsugarcrm_url',
                'nfsugarcrm_consumer_key',
                'nfsugarcrm_consumer_secret',
                'nfsugarcrm_authorization_code_instructions',
                'nfsugarcrm_authorization_code',
                'nfsugarcrm_refresh_token_instructions',
                'nfsugarcrm_refresh_token',
                'nfsugarcrm_refresh_objects_instructions',
                'nfsugarcrm_available_objects',
                'nfsugarcrm_account_data'
            );

            foreach ($setup_settings_array as $setting) {

                unset($configured_settings[$setting]);
            }
        }

        $settings['sugarcrm'] = $configured_settings;

        return $settings;
    }

    public function plugin_settings_groups($groups) {

        $groups = array_merge($groups, NF_SugarCRM()->config('PluginSettingsGroups'));
        return $groups;
    }

    /**
     * Used to provide current status of API connection
     */
    protected function build_authorization_code_markup() {

        $markup = ''; //initialize

        $generate_code_link = $this->build_generate_code_listener_link();

        $markup .= __('Enter your Consumer Key and Secret and SAVE your settings before the next step.', 'ninja-forms-sugar-crm');

        $markup .= '<br />';

        $markup .= '<span><a href="' . $generate_code_link . '" target="_blank">Click to generate open authorization code</a></span>';

        $markup .= '<br />';

        $markup .= __('Copy the "Token authorization code" from the Sugar response and SAVE it in the Authorization Code box.', 'ninja-forms-sugar-crm');

        return $markup;
    }

    /**
     *
     * @return string HTMl markup of listener link for refresh token generation
     */
    protected function build_refresh_token_instructions_markup() {

        $markup = '';  // initialize

        $refresh_token_link = $this->build_refresh_token_listener_link();

        $markup .= '<span><a href="' . $refresh_token_link . '" target="_self">Click to generate Access Token</a></span>';

        return $markup;
    }

    /**
     *
     * @return string HTML markup of the status array
     */
    protected function build_status_markup(){

        $markup = ''; // initialize

        if(!is_array($this->comm_data['status']) || empty($this->comm_data['status'])){
            $markup = htmlentities($this->comm_data['status']);
        } else {

            foreach ($this->comm_data['status'] as $status) {
                $markup .= '<br />' . htmlentities($status);
            }
        }

        return $markup;
    }

    /**
     * Builds URL for refresh token listener
     *
     * @return string Listener URL for refresh token
     *
     * Needs to match the URL in nfsugarcrm_listener
     *
     */
    protected function build_refresh_token_listener_link() {

        $link = ''; //initialize

        $link .= home_url();
        $link .= '?nfsugarcrm_instructions=refresh_token';

        return $link;
    }

    protected function build_refresh_objects_instructions_markup() {

        $markup = '';  // initialize

        $refresh_objects_link = $this->build_refresh_objects_listener_link();

        $markup .= '<span><a href="' . $refresh_objects_link . '" target="_self">Click to retrieve your objects and fields</a></span>';

        $markup .= '<br />';

        $markup .= __('Enter Sugar objects that you wish to use in your forms in the Objects to Retrieve box and click \'Refresh Objects\' to make them available in your forms.', 'ninja-forms-sugar-crm');

        return $markup;
    }

    /**
     * Builds URL for refresh objects listener
     *
     * @return string Listener URL for objects token
     *
     * Needs to match the URL in nfsugarcrm_listener
     *
     */
    protected function build_refresh_objects_listener_link() {

        $link = ''; //initialize

        $link .= home_url();
        $link .= '?nfsugarcrm_instructions=refresh_objects';

        return $link;
    }

    /**
     * Builds URL for generate code listener
     *
     * @return string Listener URL for egnerate code code
     *
     * Needs to match the URL in nfsugarcrm_listener
     *
     */
    protected function build_generate_code_listener_link() {

        $link = ''; //initialize

        $link .= home_url();
        $link .= '?nfsugarcrm_instructions=generate_code';

        return $link;
    }
}
