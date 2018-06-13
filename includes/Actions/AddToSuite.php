<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

if (!defined('ABSPATH') || !class_exists('NF_Abstracts_Action'))
    exit;

/**
 * Class NF_Action_InsightlyCRMExample
 */
final class NF_SuiteCRM_Actions_AddToSuite extends NF_Abstracts_Action {

    /**
     * @var string
     */
    protected $_name = 'addtosuite'; // child CRM

    /**
     * @var array
     */
    protected $_tags = array();

    /**
     * @var string
     */
    protected $_timing = 'normal';

    /**
     * @var int
     */
    protected $_priority = '10';

    /**
     * The availalble Suite fields for mapping
     * @var array
     */
    protected $field_map_array;

    /**
     * The field data from the form submission needed for building the request
     * @var array
     */
    protected $fields_to_extract;

    /**
     * The lookup array built in shared functions, used for dropdown array
     * @var array
     */
    protected $field_map_lookup;

    /**
     *
     * @var array Request array used to build the Suite Request Object
     */
    protected $request_array;

    /**
     * Store the Api Client
     * @var Client
     */
    protected $api_client;

    /**
     * Stores the session_id for the API Session
     * @var string
     */
    protected $session_id;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->_nicename = __('Add To Suite', 'ninja-forms');

        // build the dropdown array
        $this->field_map_array = nfsuitecrm_build_suite_field_list();

        add_action('admin_init', array($this, 'init_settings'));
        add_action('ninja_forms_builder_templates', array($this, 'builder_templates'));
    }

    /*
     * PUBLIC METHODS
     */

    public function save($action_settings) {

        return $action_settings;
    }

    public function process($action_settings, $form_id, $data) {

        $createObjects = [];

        $this->setup_api();

        foreach ($action_settings['suite_field_map'] as $field_array)
        {
            if (empty($field_array['field_map'])) {
                continue;
            }

            list($object, $field_name) = explode('.', $field_array['field_map']);

            $value = $field_array['form_field'];

            $createObjects[$object][$field_name] = $value;

            # Check for existing Object using this field
            if (isset($field_array['special_instructions']) && $field_array['special_instructions'] == 'DuplicateCheck') {
                $id = $this->look_for_id($object, $field_name, $value);
                if ($id) {
                    $createObjects[$object]['id'] = $id;
                }
            }
        }

        foreach ($createObjects as $objectType => $objectData) {
            try {
                $response = $this->api_client->request('POST', '/api/v8/modules/' . $objectType, [
                    'json' => [
                        'data' => [
                            'id' => '',
                            'type' => $objectType,
                            'attributes' => $objectData,
                        ],
                    ],
                ]);
                nfsuitecrm_update_comm_data(['status' => 'Success','debug' => 'Success',]);
            } catch (Exception $e) {
                nfsuitecrm_update_comm_data([
                    'status' => 'Error connecting to API:' .  $e->getMessage(),
                    'debug' => 'Error connecting to API:' .  $e->getMessage(),
                ]);

                return $data;
            }
        }

        return $data;
    }

    public function builder_templates() {
        NF_SuiteCRM::template('custom-field-map-row.html');
    }

    public function init_settings() {

        $settings = NF_SuiteCRM::config('ActionFieldMapSettings');
        $this->_settings = array_merge($this->_settings, $settings);

        $field_dropdown = $this->build_field_map_dropdown($this->field_map_array);

        $this->_settings['field_map']['columns']['field_map']['options'] = $field_dropdown;

        $special_instructions = NF_SuiteCRM::config('SpecialInstructions');
        $this->_settings['field_map']['columns']['special_instructions']['options'] = $special_instructions;

        $this->fields_to_extract = NF_SuiteCRM::config('FieldsToExtract');
    }

    protected function extract_field_data($action_settings) {

        $this->request_array = array();  // initialize

        $field_map_data = $action_settings['suite_field_map']; // matches option repeater 'name'

        if (!is_array($field_map_data)) {
            return; // stop if no array
        }

        $this->build_field_map_lookup();

        foreach ($field_map_data as $field_data) {// cycle through each mapped field
            $map_args = array();

            foreach ($this->fields_to_extract as $field_to_extract) { // cycle through each column in the repeater
                if (isset($field_data[$field_to_extract])) {
                    $value = $field_data[$field_to_extract];

                    // for the field map, replace the human readable version with the coded version
                    if ('field_map' == $field_to_extract) {

                        $value = $this->field_map_lookup[$value];
                    }

                    $map_args[$field_to_extract] = $value;
                }
            }

            $this->request_array[] = $map_args;
        }
    }

    /*
     * PROTECTED METHODS
     */

    /**
     * Build the array of each field to be sent
     *
     * Uses the reader-friendly name for both label and value.  Processing
     * can look up the programmatic value for mapping the request
     * @param array $field_map_array
     * @return array
     */
    protected function build_field_map_dropdown($field_map_array) {

        $dropdown_array = array();

        foreach ($field_map_array as $array) {

            $dropdown_array[] = array(
                'label' => $array['name'],
                'value' => $array['value'],
            );
        }

        return $dropdown_array;
    }

    /**
     * Builds the lookup array for processing.
     *
     * The dropdown has both label and value of the reader-friendly version.
     * The lookup is keyed on the reader-friendly to lookup the mapping value
     */
    protected function build_field_map_lookup() {

        $this->field_map_lookup = array(); // initialize

        foreach ($this->field_map_array as $array) {

            $this->field_map_lookup[$array['name']] = $array['value'];
        }
    }

    protected function setup_api()
    {
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
                'status' => 'Error connecting to API:' .  $e->getMessage(),
                'debug' => 'Error connecting to API:' .  $e->getMessage(),
            ]);

            return false;
        }

        function correctContentTypeHeader()
        {
            return function (callable $handler) {
                return function (
                    RequestInterface $request,
                    array $options
                ) use ($handler) {
                    $request = $request
                        // Have to do this, because using 'json' overrides our header to just application/json
                        ->withHeader('Content-Type', 'application/vnd.api+json')
                    ;
                    return $handler($request, $options);
                };
            };
        }

        $stack = HandlerStack::create();
        $stack->push(correctContentTypeHeader());

        $this->api_client = new Client([
            'handler' => $stack,
            'base_uri' => Ninja_Forms()->get_setting('nfsuitecrm_url'),
            'headers' => [
                'Content-Type' => 'application/vnd.api+json', # Note: Using 'json' overrides this with application/json
                'Accept' => 'application/vnd.api+json',
                'Authorization' => 'Bearer ' . $accessToken->access_token,
            ],
        ]);

        return true;
    }

    protected function look_for_id($object, $field_name, $value)
    {
        // Filter Field Name
        switch ($field_name) {
            case 'email1':
                $query = <<<EOQ
                    %s.id in (
                        SELECT bean_id FROM email_addr_bean_rel
                        INNER JOIN email_addresses
                        WHERE
                            email_addr_bean_rel.email_address_id = email_addresses.id
                            AND email_addr_bean_rel.deleted = 0
                            AND email_addresses.deleted = 0
                            AND bean_module = '%s'
                            AND email_addresses.email_address = '%s'
                        )
EOQ;
                $query = sprintf($query, strtolower($object), $object, $value);
                break;
            default:
                // @TODO This is untested
                $query = sprintf("s.%s = '%s'", $object, $field_name, $value);
        }



        $rest_data = [
            "session" => $this->session_id,
            'module_name' => 'Leads',
            'query' => $query,
            'order_by' => 'name',
            'offset' => '',
            'select_fields' => array('name','email1'),

        ];

        try {
            // Supressing notices until PR https://github.com/guzzle/oauth-subscriber/pull/49
            $response = @$this->api_client->post('/service/v4_1/rest.php', [
                'query' => [
                    'request_type' => 'JSON',
                    'response_type' => 'JSON',
                    'input_type' => 'JSON',
                    'method' => 'get_entry_list',
                    'rest_data' => json_encode($rest_data)
                ]]);
        } catch (Exception $e) {
            nfsuitecrm_update_comm_data([
                'status' => 'Error connecting to API:' . $e->getMessage(),
                'debug' => 'Error connecting to API:' . $e->getMessage(),
            ]);
        }

        if (!isset($response) || !$response) {
            return null;
        }

        $object = json_decode((string)$response->getBody());
        if ($object->result_count) {
            return $object->entry_list[0]->id;
        }

        return null;
    }
}
