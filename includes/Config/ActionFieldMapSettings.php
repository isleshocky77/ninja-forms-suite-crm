<?php

if ( !defined( 'ABSPATH' ) )
    exit;

return array(
    /*
      |--------------------------------------------------------------------------
      | Sugar Field Map
      |--------------------------------------------------------------------------
     */
    'field_map' => array(
        'name' => 'sugar_field_map',
        'type' => 'option-repeater',
        'label' => __( 'Sugar Field Map', 'ninja-forms-sugar-crm' ) . ' <a href="#" class="nf-add-new">' . __( 'Add New' ) . '</a>',
        'width' => 'full',
        'group' => 'primary',
        'tmpl_row' => 'nf-tmpl-sugar-custom-field-map-row',
        'value' => array(),
        'columns' => array(
            'form_field' => array(
                'header' => __( 'Form Field', 'ninja-forms-sugar-crm' ),
                'default' => '',
                'options' => array()
            ),
            'field_map' => array(
                'header' => __( 'Sugar Field', 'ninja-forms-sugar-crm' ),
                'default' => '',
                'options' => array(), // created on constuction
            ),
            'special_instructions' => array(
                'header' => __( 'Data Handling Instructions', 'ninja-forms-sugar-crm' ),
                'default' => '',
                'options' => array() // created on construction
            ),
        ),
    ),
);


