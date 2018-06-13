<?php

if (!defined('ABSPATH'))
    exit;

/*
 * Plugin Name: Ninja Forms - Sugar CRM
 * Plugin URI: http://github.com/isleshocky77/ninja-form-sugar-crm
 * Description: Sugar Extension connecting Ninja Forms to your SugarCRM (6.5) or SuiteCRM Account
 * Version: 3.2.0
 * Author: Stephen Ostrow
 * Author URI: https://github.com/isleshocky77
 * Text Domain: ninja-forms-sugar-crm
 *
 * Copyright 2016 Stuart Sequeira.
 * Copyright 2017 Stephen Ostrow.
 */

if (version_compare(get_option('ninja_forms_version', '0.0.0'), '3.0.0', '<') || get_option('ninja_forms_load_deprecated', FALSE)) {

    throw new \Exception("Must update Ninja Forms to version 3.0 or later");

} else {

    // define Sugar mode as POST3
    if (!defined('NFSUGARCRM_MODE')) {
        /**
         * @var string Which NF version is used - POST3 is for all 3.0+
         */
        define('NFSUGARCRM_MODE', 'POST3');
    }

    /*
     * Include shared functions
     */
    include_once 'includes/Admin/Functions.php';
    include_once 'includes/Admin/sugar-object-refresh.php';
    include_once 'includes/Admin/sugar-api-parameters.php';
    include_once 'includes/Admin/build-sugar-field-list.php';

    /**
     * Class NF_SugarCRM
     */
    final class NF_SugarCRM {

        const VERSION = '3.3.0';
        const SLUG = 'sugar-crm';
        const NAME = 'Sugar CRM';
        const AUTHOR = 'Stephen Ostrow';
        const PREFIX = 'NF_SugarCRM';

        /**
         * @var string ID of Sugar settings section for redirects
         */
        const BOOKMARK = 'ninja_forms_metabox_sugarcrm_settings';

        /**
         * @var NF_SugarCRM
         * @since 3.0
         */
        private static $instance;

        /**
         * Plugin Directory
         *
         * @since 3.0
         * @var string $dir
         */
        public static $dir = '';

        /**
         * Plugin URL
         *
         * @since 3.0
         * @var string $url
         */
        public static $url = '';

        /**
         * Main Plugin Instance
         *
         * Insures that only one instance of a plugin class exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 3.0
         * @static
         * @static var array $instance
         * @return NF_SugarCRM Highlander Instance
         */
        public static function instance() {

            if (!isset(self::$instance) && !(self::$instance instanceof NF_SugarCRM)) {
                self::$instance = new NF_SugarCRM();

                self::$dir = plugin_dir_path(__FILE__);

                self::$url = plugin_dir_url(__FILE__);

                /*
                 * Register our autoloader
                 */
                spl_autoload_register(array(self::$instance, 'autoloader'));
            }

            return self::$instance;
        }

        public function __construct() {
            /*
             * load the global variables
             * function in Admin/Functions.php
             */
            nfsugar_load_globals();

            /*
             * Set up Licensing
             */
            add_action('admin_init', array($this, 'setup_license'));

            /*
             * Create Admin settings
             */
            add_action('ninja_forms_loaded', array($this, 'setup_admin'));

            /*
             * Load Classes
             */
            add_action('ninja_forms_loaded', array($this, 'load_classes'));

            /*
             * Load Templates
             *
             * Removed  on version 3.0.3
             */
//            add_action('ninja_forms_builder_templates', array($this, 'builder_templates'));

            /*
             * Register Actions
             */
            add_filter('ninja_forms_register_actions', array($this, 'register_actions'));
        }

        public function register_actions($actions) {

            // key needs to match $_name property from action
            $actions['addtosugar'] = new NF_SugarCRM_Actions_AddToSugar();

            return $actions;
        }

        /*
         * Set up the licensing
         */

        public function setup_license() {

            if (!class_exists('NF_Extension_Updater'))
                return;

            new NF_Extension_Updater(self::NAME, self::VERSION, self::AUTHOR, __FILE__, self::SLUG);
        }

        /**
         * Create the settings page
         */
        public function setup_admin() {

            if (!is_admin())
                return;

            new NF_SugarCRM_Admin_Settings();
            new NF_SugarCRM_Admin_Metaboxes_Submission();
        }

        public function load_classes() {

            NF_SugarCRM::file_include('Comm', 'class-sugar-build-request');
            NF_SugarCRM::file_include('Comm', 'class-sugar-communication');

            NF_SugarCRM::file_include('Comm/authentication', 'class-sugar-security-credentials');
            NF_SugarCRM::file_include('Comm/authentication', 'class-sugar-get-refresh-token');
            NF_SugarCRM::file_include('Comm/authentication', 'class-sugar-access-token');
            NF_SugarCRM::file_include('Comm/authentication', 'class-sugar-version');


            NF_SugarCRM::file_include('Comm/request', 'class-sugar-describe-object');
            NF_SugarCRM::file_include('Comm/request', 'class-sugar-list-of-objects');
            NF_SugarCRM::file_include('Comm/request', 'class-sugar-post-new-record');
            NF_SugarCRM::file_include('Comm/request', 'class-sugar-check-for-duplicate');


            include self::$dir . 'vendor/autoload.php';
        }

        /**
         * Returns a configuration specified in a given Config file
         * @param string $file_name
         * @return mixed
         */
        public static function config($file_name) {

            return include self::$dir . 'includes/Config/' . $file_name . '.php';
        }

        /**
         * Includes a specific file in an Includes directory
         *
         * @param string $sub_dir
         * @param string $file_name
         */
        public static function file_include($sub_dir, $file_name) {

            include self::$dir . 'includes/' . $sub_dir . '/' . $file_name . '.php';
        }

        /**
         * Creates a template for display
         *
         * @param string $file_name
         * @param array $data
         * @return mixed
         */
        public static function template($file_name = '', array $data = array()) {

            if (!$file_name) {
                return;
            }
            extract($data);

            include self::$dir . 'includes/Templates/' . $file_name;
        }

        /*
         * Optional methods for convenience.
         */

        public function autoloader($class_name) {

            if (class_exists($class_name))
                return;

            if (false === strpos($class_name, self::PREFIX))
                return;

            $class_name = str_replace(self::PREFIX, '', $class_name);
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

            if (file_exists($classes_dir . $class_file)) {
                require_once $classes_dir . $class_file;
            }
        }

        /**
         *
         * @return array Array of the Account data
         */
        public function get_nfsugarcrm_account_data() {

            $data = get_option('nfsugarcrm_account_data');

            return $data;
        }

        /**
         *
         * @return array Array of the communication data
         */
        public function get_nfsugarcrm_comm_data() {

            $data = get_option('nfsugarcrm_comm_data');

            return $data;
        }

        /**
         * Modify the comm data global
         *
         * This doesn't write to the database to minimize db calls.  Rather,
         * use update_nfsugarcrm_comm_data to write to the db.  If there
         * is a point where error can halt or branch; run an update to store
         * the last known data.
         *
         * @param string $key Key of the comm data to update
         * @param string $value Value to update in comm data
         * @param bool $append Add to nested array to preserve previous data
         */
        public function modify_nfsugarcrm_comm_data($key = '', $value = '', $append = false) {

            if (0 < strlen($key) || 0 < strlen($value)) {
//                return;
            }

            if ($append) {
                $count = count($this->nfsugarcrm_comm_data[$key]);

                if (3 < $count) {

                    array_shift($this->nfsugarcrm_comm_data[$key]);
                }

                $this->nfsugarcrm_comm_data[$key][] = $value;
            } else {

                $this->nfsugarcrm_comm_data[$key] = $value;
            }
        }

        /**
         * Write the current global comm data to the database
         */
        public function update_nfsugarcrm_comm_data() {

            update_option('nfsugarcrm_comm_data', $this->nfsugarcrm_comm_data);
        }

    }

    /**
     * The main function responsible for returning The Highlander Plugin
     * Instance to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     * @since 3.0
     * @return {class} Highlander Instance
     */
    function NF_SugarCRM() {
        return NF_SugarCRM::instance();
    }

    NF_SugarCRM();
}

/**
 *
 * @return array A lookup array keyed on the stored value to replace with the proper label
 */
function nfsugarcrm_build_field_lookup_array(){

    $lookup_array = array(); // initialize

    $field_list = nfsugarcrm_build_sugar_field_list();

    foreach($field_list as $field){

        $lookup_array[$field['value']]=$field['name'];
    }

    return $lookup_array;
}
