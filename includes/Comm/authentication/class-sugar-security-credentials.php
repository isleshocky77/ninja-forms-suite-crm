<?php

/**
 * Structure of returned array
 *  'result' 
 *  'comm_data'
 *      //-comm_data_array structure-//
 *      )
 *  'data'=>array(
 *      'refresh_token'=>,
 *      'consumer_key,
 *      'consumer_secret'=>,
 *      

 *   )
 * 
 */
class SugarSecurityCredentials {

    private $nfsugarcrm_settings;
    private $credentials_array;

    function __construct( $nfsugarcrm_settings ) {

        $this->nfsugarcrm_settings = $nfsugarcrm_settings;

        $this->credentials_array = array(
            'result' => 'success',
            'comm_data' => array(),
            'data' => array()
        );

        $this->extract_consumer_key();
        $this->extract_consumer_secret();
        $this->extract_refresh_token();

        $this->get_credentials_array(); // automatically calls the method to return the array
    }

    private function extract_consumer_key() {

        if ( isset( $this->nfsugarcrm_settings[ 'nfsugarcrm_consumer_key' ] ) && strlen( $this->nfsugarcrm_settings[ 'nfsugarcrm_consumer_key' ] ) > 0 ) {

            $this->credentials_array[ 'data' ][ 'consumer_key' ] = $this->nfsugarcrm_settings[ 'nfsugarcrm_consumer_key' ];
        } else {

            $this->credentials_array[ 'result' ] = 'failure';
            $this->credentials_array[ 'comm_data' ][ 'status' ] = __( 'Some of the needed Sugar login credentials is missing.  Please see raw data for details.', 'ninja-forms-sugar-crm' );
            $this->credentials_array[ 'comm_data' ][ 'debug' ][] = array(
                'heading' => 'Missing Consumer Key',
                'value' => __( 'Please enter your consumer key', 'ninja-forms-sugar-crm' )
            );
        }
    }

    private function extract_consumer_secret() {
        if ( isset( $this->nfsugarcrm_settings[ 'nfsugarcrm_consumer_secret' ] ) && strlen( $this->nfsugarcrm_settings[ 'nfsugarcrm_consumer_secret' ] ) > 0 ) {

            $this->credentials_array[ 'data' ][ 'consumer_secret' ] = $this->nfsugarcrm_settings[ 'nfsugarcrm_consumer_secret' ];
        } else {

            $this->credentials_array[ 'result' ] = 'failure';
            $this->credentials_array[ 'comm_data' ][ 'status' ] = __( 'Some of the needed Sugar login credentials is missing.  Please see raw data for details.', 'ninja-forms-sugar-crm' );
            $this->credentials_array[ 'comm_data' ][ 'debug' ][] = array(
                'heading' => 'Missing Consumer Secret',
                'value' => __( 'Please enter your consumer secret', 'ninja-forms-sugar-crm' )
            );
        }
    }

    private function extract_refresh_token() {
        if ( isset( $this->nfsugarcrm_settings[ 'nfsugarcrm_refresh_token' ] ) && strlen( $this->nfsugarcrm_settings[ 'nfsugarcrm_refresh_token' ] ) > 0 ) {

            $this->credentials_array[ 'data' ][ 'refresh_token' ] = $this->nfsugarcrm_settings[ 'nfsugarcrm_refresh_token' ];
        } else {

            $this->credentials_array[ 'result' ] = 'failure';
            $this->credentials_array[ 'comm_data' ][ 'status' ] = __( 'Some of the needed Sugar login credentials is missing.  Please see raw data for details.', 'ninja-forms-sugar-crm' );
            $this->credentials_array[ 'comm_data' ][ 'debug' ][] = array(
                'heading' => 'Missing Refresh Token',
                'value' => __( 'Please generate an authorization code and a refresh token', 'ninja-forms-sugar-crm' )
            );
        }
    }

    private function extract_password() {
        if ( isset( $this->nfsugarcrm_settings[ 'nfsugarcrm_password' ] ) && strlen( $this->nfsugarcrm_settings[ 'nfsugarcrm_password' ] ) > 0 ) {

            $this->credentials_array[ 'data' ][ 'password' ] = $this->nfsugarcrm_settings[ 'nfsugarcrm_password' ];
        } else {

            $this->credentials_array[ 'result' ] = 'failure';
            $this->credentials_array[ 'comm_data' ][ 'status' ] = __( 'Some of the needed Sugar login credentials is missing.  Please see raw data for details.', 'ninja-forms-sugar-crm' );
            $this->credentials_array[ 'comm_data' ][ 'debug' ][] = array(
                'heading' => 'Missing Password',
                'value' => __( 'Please enter your password', 'ninja-forms-sugar-crm' )
            );
        }
    }

// Sets and Gets

    public function get_credentials_array() {

        if ( empty( $this->credentials_array ) ) {

            return false;
        } else {
            return $this->credentials_array;
        }
    }

    public function get_comm_data() {

        if ( empty( $this->comm_data ) ) {

            return false;
        } else {
            return $this->comm_data;
        }
    }

}
