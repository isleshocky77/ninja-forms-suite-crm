<?php
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

if (!defined('ABSPATH'))
    exit;

/**
 * Retrieve db options as global variables to minimize db calls
 *
 * @var array $nfsuitecrm_settings Client ID, Secret, Authorization Code, and Objects to Refresh
 * @var array $nfsuitecrm_comm_data Communication data for support
 * @var array $nfsuitecrm_account_data Objects and Fields available for field mapping
 *
 */
function nfsuite_load_globals() {

    /**
     * Array of Suite settings entered by user including consumer key,
     * consumer secret, authorization code, and objects to be made available
     *
     * @var array
     */
    global $nfsuitecrm_settings;

    /**
     * @var array Communication data stored for support
     */
    global $nfsuitecrm_comm_data;

    /**
     * Data retrieved from the account, including the Suite version, a list
     * of the objects and the fields within those objects
     *
     * @var array
     */
    global $nfsuitecrm_account_data;

        $nfsuitecrm_comm_data = get_option('nfsuitecrm_comm_data');

    $nfsuitecrm_account_data = get_option('nfsuitecrm_account_data');



    /*
     * in 3.0 this is stored in db option ninja_forms_settings
     *
     * available objects is a user entered commma delimited array of the objects
     * to be used to build the field map list.
     */
    $keys_to_extract = array(
        'nfsuitecrm_url',
        'nfsuitecrm_consumer_key',
        'nfsuitecrm_consumer_secret',
        'nfsuitecrm_authorization_code',
        'nfsuitecrm_refresh_token', // not manually entered; stored in nfsuitecrm_settings
        'nfsuitecrm_available_objects', // objects TO BE MADE available in the field list
    );


    /*
     * The site-wide settings
     *
     * In NF3, the refresh token isn't stored with the other settings because
     * it is not manually entered so it is stored in nfsuitecrm_settings
     *
     */
    $bypassed_settings = get_option('nfsuitecrm_settings');

    if ('2.9x' == NFSUITECRM_MODE) {

        $temp_array = $bypassed_settings;
    } else {

        // In a NF 3.0 setup, the settings are all stored in option ninja_forms_settings
        $nf_settings_array = get_option(' ninja_forms_settings');

        foreach ($keys_to_extract as $key) {

            if (isset($nf_settings_array[$key])) {

                $temp_array[$key] = $nf_settings_array[$key];
            } elseif (isset($bypassed_settings[$key])) {

                // If NF3 key isn't set, grab the NF2.9 version
                $temp_array[$key] = $bypassed_settings[$key];
            } else {

                // ensure it is at least set
                $temp_array[$key] = '';
            }
        }
    }
    // set the global
    $nfsuitecrm_settings = $temp_array;
}

function nfsuitecrm_extract_advanced_codes() {

    $settings_key = 'nfsuitecrm_advanced_codes';

    $advanced_codes_array = array(); //initialize
    $nf_settings_array = Ninja_Forms()->get_settings();

    if (isset($nf_settings_array[$settings_key])) {

        $advanced_codes_setting = $nf_settings_array[$settings_key];

        $advanced_codes_array = array_map('trim', explode(',', $advanced_codes_setting));
    }

    return $advanced_codes_array;
}

/**
 * Create HTML for account data
 *
 * @global array $nfsuitecrm_settings
 * @global array $nfsuitecrm_account_data
 * @return type
 *
 */
function nfsuitecrm_output_account_data() {

//    global $nfsuitecrm_settings;
    global $nfsuitecrm_account_data;


    if (isset($nfsuitecrm_account_data['version'])) {

        $version = $nfsuitecrm_account_data['version'];
    } else {
        $version = false;
    }

    if (isset($nfsuitecrm_account_data['object_list'])) {
        $object_list = implode(' , ', $nfsuitecrm_account_data['object_list']);
    } else {
        $object_list = __('No Suite object list currently available', 'ninja-forms-suite-crm');
    }

    if (isset($nfsuitecrm_account_data['field_list']) && is_array($nfsuitecrm_account_data['field_list'])) {
        $field_list = $nfsuitecrm_account_data['field_list'];
    } else {
        $field_list = false;
    }

    ob_start();
    ?>
    <table class="form-table">
        <tbody>
            <?php if ($version) { ?>
                <tr valign="top">
                    <th scope="row"><?php _e('Suite Version', 'ninja-forms-suite-crm'); ?></th>
                    <td>

                        <?php echo($version); ?>

                    </td>
                </tr>
                <?php
            }
            if ($field_list) {
                foreach ($field_list as $object => $list) {
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php echo $object; ?></th>
                        <td><?php echo implode(', ', array_keys($list)); ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            <tr valign="top">
                <th scope="row"><?php _e('Suite Object List', 'ninja-forms-suite-crm'); ?></th>
                <td>

                    <?php echo($object_list); ?>

                </td>
            </tr>

        </tbody>
    </table>
    <?php
    $account_data_html = ob_get_clean();

    return $account_data_html;
}

/**
 * Update communication data as a nested array for support
 *
 * 'debug'=>array(
 *      *class* => array(
 *          array(
 *              'heading' => string,
 *              'value' => string
 *          )
 *          . . .
 *      )
 *      . . .
 *  )
 *  'status'=>array(
 *      (string)
 *      . . .
 *  )
 * @param array $comm_data_array
 *
 */
function nfsuitecrm_update_comm_data($comm_data_array) {

    update_option('nfsuitecrm_comm_data', $comm_data_array);
}

function nfsuitecrm_update_account_data($account_data_array) {

    update_option('nfsuitecrm_account_data', $account_data_array);
}

function nfsuitecrm_update_settings($nfsalesformcrm_settings) {

    update_option('nfsuitecrm_settings', $nfsalesformcrm_settings);
}

add_action('init', 'nfsuitecrm_listener');

/**
 * Listens for POST or GET requests with specific commands
 *
 * Calls specific functions based on the request made; uses if statements with
 * a switch/case so that only vetted functions are called instead of allowing
 * for unvetted function calls
 */
function nfsuitecrm_listener() {

    /*
     * Trigger added in 3.0 to enable activation both from 2.9's form button,
     * which uses a form button with action
     * and 3.0's refresh token listener
     */
    $trigger = false; // initialize

    if (isset($_POST['action']) && $_POST['action'] == 'nfsuitecrm_generate_refresh_token_listener') {
        $trigger = 'refresh_token';
    }

    /*
     * Ensure this GET listener matches the URL in Settings.php
     */
    if (isset($_GET['nfsuitecrm_instructions']) && 'refresh_token' == $_GET['nfsuitecrm_instructions']) {
        $trigger = 'refresh_token';
    }

    /*
     * Ensure this GET listener matches the URL in Settings.php
     */
    if (isset($_GET['nfsuitecrm_instructions']) && 'generate_code' == $_GET['nfsuitecrm_instructions']) {
        $trigger = 'generate_code';
    }

    /*
     * Ensure this GET listener matches the URL in Settings.php
     */
    if (isset($_GET['nfsuitecrm_instructions']) && 'refresh_objects' == $_GET['nfsuitecrm_instructions']) {
        $trigger = 'refresh_objects';
    }

    switch($trigger){

        case 'refresh_token':
            nfsuitecrm_refresh_token();
            break;
        case 'refresh_objects':
            nfsuitecrm_refresh_suite_objects();
            break;
        case 'generate_code':
            nfsuitecrm_generate_code();
            break;
        default:
            break;
    }
}

function nfsuitecrm_generate_code() {

    if (empty(Ninja_Forms()->get_setting('nfsuitecrm_url'))) {
        $this->auth_code_url_link = 'https://example.localhost';
        return;
    }

    $stack = HandlerStack::create();

    $middleware = new Oauth1([
        'request_method' => Oauth1::REQUEST_METHOD_QUERY,
        'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC,
        'consumer_key'    => Ninja_Forms()->get_setting('nfsuitecrm_consumer_key'),
        'consumer_secret' => Ninja_Forms()->get_setting('nfsuitecrm_consumer_secret'),
    ]);
    $stack->push($middleware);

    $client = new Client([
        'base_uri' => Ninja_Forms()->get_setting('nfsuitecrm_url'),
        'handler' => $stack,
        'auth' => 'oauth',
        'query' => [ 'method' => 'oauth_request_token'],

    ]);

    try {
        // Supressing notices until PR https://github.com/guzzle/oauth-subscriber/pull/49
        $response = @$client->get('/service/v4_1/rest.php');
    } catch (Exception $e) {
        nfsuitecrm_update_comm_data([
            'status' => 'Error connecting to API:' .  $e->getMessage(),
            'debug' => 'Error connecting to API:' .  $e->getMessage(),
        ]);

        echo "Error connecting to API. Close tab and return to settings.";
        exit;
    }

    $request_token_info = [];
    parse_str((string) $response->getBody(), $request_token_info);

    $oauth_token = $request_token_info['oauth_token'];
    $oauth_token_secret = $request_token_info['oauth_token_secret'];
    $authorize_url = $request_token_info['authorize_url'];

    Ninja_Forms()->update_setting('nfsuitecrm_request_token', $oauth_token, true);
    Ninja_Forms()->update_setting('nfsuitecrm_request_token_secret', $oauth_token_secret);

    $auth_code_url_link = sprintf('%s&token=%s', $authorize_url, $oauth_token);

    header('Location: ' . $auth_code_url_link);
    exit;
}

/**
 * Attempts to generate refresh token from key, secret, and auth code
 *
 * @global array $nfsuitecrm_settings Suite settings array in db
 *
 */
function nfsuitecrm_refresh_token(){

    $stack = HandlerStack::create();

    $middleware = new Oauth1([
        'request_method'    => Oauth1::REQUEST_METHOD_QUERY,
        'signature_method'  => Oauth1::SIGNATURE_METHOD_HMAC,
        'consumer_key'      => Ninja_Forms()->get_setting('nfsuitecrm_consumer_key'),
        'consumer_secret'   => Ninja_Forms()->get_setting('nfsuitecrm_consumer_secret'),
        'token'             => Ninja_Forms()->get_setting('nfsuitecrm_request_token'),
        'token_secret'      => Ninja_Forms()->get_setting('nfsuitecrm_request_token_secret'),
    ]);
    $stack->push($middleware);

    $client = new Client([
        'base_uri' => Ninja_Forms()->get_setting('nfsuitecrm_url'),
        'handler' => $stack,
        'auth' => 'oauth',
        'query' => [
            'method'            => 'oauth_access_token',
            'oauth_verifier'    => Ninja_Forms()->get_setting('nfsuitecrm_authorization_code'),
        ],
    ]);

    try {
        // Supressing notices until PR https://github.com/guzzle/oauth-subscriber/pull/49
        $response = @$client->get('/service/v4_1/rest.php');
    } catch (Exception $e) {
        nfsuitecrm_update_comm_data([
            'status' => 'Error connecting to API:' .  $e->getMessage(),
            'debug' => 'Error connecting to API:' .  $e->getMessage(),
        ]);

        wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
        exit;
    }

    $access_token_info = [];
    parse_str((string) $response->getBody(), $access_token_info);

    Ninja_Forms()->update_setting('nfsuitecrm_access_token', $access_token_info['oauth_token'], true);
    Ninja_Forms()->update_setting('nfsuitecrm_access_token_secret', $access_token_info['oauth_token_secret']);

    nfsuitecrm_update_comm_data([
        'status' => 'Success getting token',
        'debug' => 'Success getting token',
    ]);

    wp_redirect(admin_url().'admin.php?page=nf-settings#'.NF_SuiteCRM::BOOKMARK);
    exit;
}

/**
 * Adds characters removed by NF settings
 * @param string $incoming_authcode
 * @return string Authorization code with trailing %3D added back in
 */
function nfsuitecrm_filter_authcode($incoming_authcode) {

    $wip_authcode = $incoming_authcode; // initial wip

    /*
     * Strip out URL if present - makes it easier for instructions
     */
    $wip_authcode = str_replace('https://login.suite.com/services/oauth2/success?code=','', $wip_authcode);

    /*
     * Add the stripped out characters when saving using Ninja_Forms class
     */
    if ('POST3' === NFSUITECRM_MODE) {

        $wip_authcode = $wip_authcode . '%3D%3D';
    }

    $authcode = $wip_authcode;
    return $authcode;
}

/**
 * Iterates the array of objects and inserts them into Suite
 * @param type $object_request_list
 * @param type $request_object
 * @param type $api_parameter_array
 * @return boolean
 */
function nfsuitecrm_process_object_list( $object_request_list, $request_object, $api_parameter_array){

    /*
     * Cycle through the object request list and add each new object to Suite
     *
     */
    foreach ( $object_request_list as $suite_object ) {

        $object_field_array = $request_object->get_object_field_list( $suite_object );

        $new_record_parameter_array = $api_parameter_array;
        $new_record_parameter_array[ 'object_name' ] = $suite_object;
        $new_record_parameter_array[ 'field_array' ] = $object_field_array;

        $new_object_record = new SuitePostNewRecordObject( $new_record_parameter_array );

        $new_record_id = $new_object_record->get_new_record_id();

        if ( $new_record_id ) {
            $request_object->link_child_objects( $suite_object, $new_record_id );
        }

        $temp_array = $new_object_record->get_comm_data();
        $new_object_array[ 'debug' ][] = $temp_array[ 'debug' ];
        $new_object_array[ 'status' ][] = $temp_array[ 'status' ]; // accumulate the statuses
    }


    /* add for duplicate field test */

    $duplicate_check_array = $request_object->get_duplicate_check_array();

    if ( !$duplicate_check_array ) {
        nfsuitecrm_update_comm_data( $new_object_array );
        return false;
    }

    /*
     * Cycle through the duplicate check array and check if the given
     * Suite object and field have more than one of the same user value
     * If true, create a task with description identifying the object,
     * field, and value that is duplicated
     *
     */
    foreach ( $duplicate_check_array as $suite_object => $field_check_array ) {

        $duplicate_check_parameter_array = $api_parameter_array;
        $duplicate_check_parameter_array[ 'object_name' ] = $suite_object;

        /*
         * NOTE: duplicate check is built as an array of arrays so that
         * multiple matches could be added in the future if needed;
         * currently checking only first array
         *
         */
        $duplicate_check_parameter_array[ 'field_name' ] = $field_check_array[ 0 ][ 'suite_field' ];
        $duplicate_check_parameter_array[ 'field_value' ] = $field_check_array[ 0 ][ 'user_value' ];

        $duplicate_check_object = new SuiteDuplicateCheck( $duplicate_check_parameter_array );

        $temp_array = $duplicate_check_object->get_comm_data();
        $new_object_array[ 'debug' ][] = $temp_array[ 'debug' ];
        $new_object_array[ 'status' ][] = $temp_array[ 'status' ]; // accumulate the statuses

        $response = $duplicate_check_object->get_duplicate_check_response();

        /*
         * If more than one entry is returned, there is a duplicate
         * Create a task
         */

        if ( isset( $response[ 'totalSize' ] ) && 1 < $response[ 'totalSize' ] ) {

            $task_to_review_duplicate_request_array = $api_parameter_array;
            $task_to_review_duplicate_request_array[ 'object_name' ] = 'Task';
            $task_to_review_duplicate_request_array[ 'field_array' ] = nfsuitecrm_build_duplicate_check_task_array( $duplicate_check_parameter_array );

            $new_duplicate_task_record = new SuitePostNewRecordObject( $task_to_review_duplicate_request_array );

            $temp_array = $new_duplicate_task_record->get_comm_data();
            $new_object_array[ 'debug' ][] = $temp_array[ 'debug' ];
            $new_object_array[ 'status' ][] = $temp_array[ 'status' ]; // accumulate the statuses
        }

    }

    nfsuitecrm_update_comm_data( $new_object_array );

}



/*
 * Build the Task fields for reviewing the duplicate object, field, and value
 * found during the duplicate field check
 *
 * Currently uses Task Subject and Description
 */
function nfsuitecrm_build_duplicate_check_task_array( $parameter_array ) {

    /*
     * Build the Task Description based on the parameters that are duplicated
     *
     */
    $description_intro = __( 'A recent form submission has a possible duplication in the following Object: ', 'ninja-forms-suite-crm' );

    $description_text = $description_intro
            . $parameter_array[ 'object_name' ] . '.  '
            . __( 'Please check this field: ', 'ninja-forms-suite-crm' )
            . $parameter_array[ 'field_name' ] . ' '
            .  __( 'for a duplicate value: ', 'ninja-forms-suite-crm' )
            . $parameter_array[ 'field_value' ];

    $description_text = apply_filters( 'nfsuitecrm-duplicate-found-task-description', $description_text , $parameter_array);


    /*
     * Set the Task Due Date
     */

    $date = new DateTime(); // get a timestamp
    $date_format = apply_filters( 'nfsuitecrm_filter_date_interval_format', 'Y-m-d' ); // set the format for Suite
    $date_interval = apply_filters('nfsuitecrm_filter_duplicate_check_task_due_date','0'); // give developer option to set delay to task date
    date_add( $date, date_interval_create_from_date_string( $date_interval) ); // delay task by interval amount

    $formatted_date = $date->format( $date_format ); // format the date for Suite



    $field_array = array(
        'Subject' => apply_filters( 'nfsuitecrm-duplicate-found-task-subject', 'Duplicate found from web form submission' ),
        'Description' => $description_text,
        'ActivityDate'=>$formatted_date
    );

    return $field_array;
}




/**
 * Gets the contents of an href link
 *
 * If unable to retrieve the contents of the link, returns FALSE
 *
 *
 * @param string $link Link sent in anchor href format
 * @return string $contents The contents of the link
 */
function nfsuitecrm_extract_upload_contents($link) {

    $contents = FALSE; // set default

    /*
     * Check for false or null link
     */
    if(!$link){

        return $contents;
    }

    $dom = new DOMDocument();

    /*
     * Attempt to parse link as html
     */
    libxml_use_internal_errors(true);
    $dom->loadHTML($link);
    $error_catch = libxml_get_last_error();
    libxml_clear_errors();
    libxml_use_internal_errors(false);

    if ($error_catch) {

        return $contents;
    }

    /*
     * Attempt to extract first anchor tag
     */
    $anchors = $dom->getElementsByTagName('a');

    if (!isset($anchors[0])) {

        return $contents;
    } else {

        $href = $anchors[0]->getAttribute('href');
    }

    /*
     * Attempt to retrieve contents of anchor href
     */
    $get_contents = @file_get_contents($href);

    if(!$get_contents){

        return $contents;
    }

    /*
     * Successful getting contents
     */
    $contents = $get_contents;

    return $contents;
}
