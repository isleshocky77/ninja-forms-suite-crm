<?php

/*
 * Builds the drop-down list of Suite fields that can be mapped to
 * 
 */

function nfsuitecrm_build_suite_field_list() {

    global $nfsuitecrm_account_data;

    $default_fields_array = array(
        array(
            'name' => 'None',
            'value' => 'None'
        )
    );

    if ( !isset( $nfsuitecrm_account_data[ 'field_list' ] ) || !is_array( $nfsuitecrm_account_data[ 'field_list' ] ) ) {

        return $default_fields_array;
    }

    $iterating_array = array();

    foreach ( $nfsuitecrm_account_data[ 'field_list' ] as $object => $field_name_label_pair ) {

        foreach ( $field_name_label_pair as $name => $label ) {

            $iterating_array[] = array(
                'name' => $object . ' - ' . $label,
                'value' => $object . '.' . $name
            );
        }
    }

    if ( !empty( $iterating_array ) ) {

        $suite_fields_array = array_merge( $default_fields_array, $iterating_array );
    } else {

        $suite_fields_array = $default_fields_array;
    }

    return $suite_fields_array;
}
