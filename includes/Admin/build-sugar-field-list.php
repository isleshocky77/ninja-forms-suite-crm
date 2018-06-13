<?php

/*
 * Builds the drop-down list of Sugar fields that can be mapped to
 * 
 */

function nfsugarcrm_build_sugar_field_list() {

    global $nfsugarcrm_account_data;

    $default_fields_array = array(
        array(
            'name' => 'None',
            'value' => 'None'
        )
    );

    if ( !isset( $nfsugarcrm_account_data[ 'field_list' ] ) || !is_array( $nfsugarcrm_account_data[ 'field_list' ] ) ) {

        return $default_fields_array;
    }

    $iterating_array = array();

    foreach ( $nfsugarcrm_account_data[ 'field_list' ] as $object => $field_name_label_pair ) {

        foreach ( $field_name_label_pair as $name => $label ) {

            $iterating_array[] = array(
                'name' => $object . ' - ' . $label,
                'value' => $object . '.' . $name
            );
        }
    }

    if ( !empty( $iterating_array ) ) {

        $sugar_fields_array = array_merge( $default_fields_array, $iterating_array );
    } else {

        $sugar_fields_array = $default_fields_array;
    }

    return $sugar_fields_array;
}
