<?php

/**
 * Uses the user settings to build an array of parameters needed to access
 * the Sugar API
 * 
 * Creates an instance of SugarSecurityCredentials to build security credentials
 * Creates an instance of SugarAccessToken from the security credentials
 * Creates an instance of SugarVersion from the access token
 * 
 * @global array $nfsugarcrm_settings
 * @return array
 *      api_parameter_array
 *        'access_token' =>
 *        'instance' =>
 *        'version_url' =>
 * 
 */
function nfsugarcrm_retrieve_api_parameters() {

    global $nfsugarcrm_settings;

    $comm_data = array();
    
    /*
     * Create a new instance of credentials
     * It holds and array of the Sugar account values that are needed to
     * generate an access token
     * 
     * If the needed account values are not available from the settings option,
     * the comm data status is updated to describe the missing information
     *      
     */
    $credentials = new SugarSecurityCredentials( $nfsugarcrm_settings );
    $credentials_array = $credentials->get_credentials_array();
    $credentials_comm_data = $credentials->get_comm_data();


    if ( 'success' != $credentials_array[ 'result' ] ) {
        $comm_data[ 'status' ][] = $credentials_comm_data[ 'comm_data' ][ 'status' ];
        $comm_data[ 'debug' ][] = $credentials_comm_data[ 'comm_data' ][ 'debug' ];
        nfsugarcrm_update_comm_data( $comm_data );
        return false;
    }

    /*
     * Use the credentials array to generate an access token and instance that
     * will be needed for all future requests made to Sugar during the
     * session.
     * 
     * If unsucessful in generating the token and instance, update the comm
     * data with the failure message
     * 
     */
    $access_token_array = array(
        'credentials_array' => $credentials_array[ 'data' ]
    );

    $access_token_object = new SugarAccessToken( $access_token_array );
    $token_result = $access_token_object->get_result();
    $temp_token_comm_data = $access_token_object->get_comm_data();

    $comm_data[ 'status' ][] = $temp_token_comm_data[ 'status' ];
    $comm_data[ 'debug' ][] = $temp_token_comm_data[ 'debug' ];

    if ( 'success' != $token_result ) {

        nfsugarcrm_update_comm_data( $comm_data );
        return false;
    }

    $api_parameter_array[ 'access_token' ] = $access_token_object->get_access_token();
    $api_parameter_array[ 'instance' ] = $access_token_object->get_instance_url();

    /*
     * Create the version object
     * 
     * Extract the latest version
     * 
     */
    $version_object = new SugarVersion( $api_parameter_array );
    $version_result = $version_object->get_result();
    $temp_version_comm_data = $version_object->get_comm_data();

    $comm_data[ 'status' ][] = $temp_version_comm_data[ 'status' ];
    $comm_data[ 'debug' ][] = $temp_version_comm_data[ 'debug' ];
    if ( 'success' != $version_result ) {

        nfsugarcrm_update_comm_data( $comm_data );
        return false;
    }

    $api_parameter_array[ 'version_url' ] = $version_object->get_version_url();


    return $api_parameter_array;
}
