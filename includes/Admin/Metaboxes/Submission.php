<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_SugarCRM_Admin_Metaboxes_Submission extends NF_Abstracts_SubmissionMetabox
{
    public function __construct()
    {
        parent::__construct();

        $this->_title = __( 'Sugar Crm', 'ninja-forms' );

        if( $this->sub && ! $this->sub->get_extra_value( 'nfsugarcrm' ) ){
            remove_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        }
    }

    public function render_metabox( $post, $metabox )
    {
        echo "<dl>";

        foreach ($this->sub->get_extra_value( 'nfsugarcrm' ) as $object => $id) {

            echo "<dt>";
            echo __( $object, "ninja-forms-sugar-crm" );
            echo "</dt>";

            echo "<dd>";
            printf( '<a href="%s" target="_blank">%s</a>',
                sprintf('%s?module=%s&action=DetailView&record=%s',
                    Ninja_Forms()->get_setting('nfsugarcrm_url'),
                    $object,
                    $id
                ),
                $id
                );
            echo "</dd>";

        }

        echo "</dl>";
    }
}
