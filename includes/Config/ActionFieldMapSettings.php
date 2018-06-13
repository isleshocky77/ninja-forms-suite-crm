<?php

if ( !defined( 'ABSPATH' ) )
    exit;

return array(
    /*
      |--------------------------------------------------------------------------
      | Suite Field Map
      |--------------------------------------------------------------------------
     */
    'field_map' => array(
        'name' => 'suite_field_map',
        'type' => 'option-repeater',
        'label' => __( 'Suite Field Map', 'ninja-forms-suite-crm' ) . ' <a href="#" class="nf-add-new">' . __( 'Add New' ) . '</a>',
        'width' => 'full',
        'group' => 'primary',
        'tmpl_row' => 'nf-tmpl-suite-custom-field-map-row',
        'value' => array(),
        'columns' => array(
            'form_field' => array(
                'header' => __( 'Form Field', 'ninja-forms-suite-crm' ),
                'default' => '',
                'options' => array()
            ),
            'field_map' => array(
                'header' => __( 'Suite Field', 'ninja-forms-suite-crm' ),
                'default' => '',
                'options' => array(), // created on constuction
            ),
            'special_instructions' => array(
                'header' => __( 'Data Handling Instructions', 'ninja-forms-suite-crm' ),
                'default' => '',
                'options' => array() // created on construction
            ),
        ),
    ),
);


