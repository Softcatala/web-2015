<?php
/**
 * WordPress Shell Functions for Softcatalà project
 * Important: this script has to be placed in the WordPress base directory (where index.php is)
 */

require( 'wp-blog-header.php' );

/**
 * WordPress Shell Functions for Softcatalà project
 *
 * @package     wp-softcatala
 * @author      Softcatalà Team <web@softcatala.org>
 */
class WordPress_Shell_SC_Functions
{
    /**
     * Input arguments
     *
     * @var array
     */
    protected $_args        = array();
    
    /**
     * Initialize application and parse input parameters
     *
     */
    public function init()
    {
        $this->_parseArgs();
    }
    
    /**
     * Run main function
     *
     * @return WordPress_Shell_SC_Functions
     */
    public function run()
    {
        $this->init();
        if ($action = $this->getArg('action')) {
            switch ($action) {
                case 'remove_orphan_images':
                    $this->remove_orphan_images();
                    break;
                case 'convert_downloads_to_acf':
                    $this->convert_downloads_to_acf();
                    break;
                default:
                    echo $this->usageHelp();
                    break;
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Removes images with a parent post id which doesn't exist anymore
     */
    protected function remove_orphan_images()
    {
        global $wpdb;

        $imagesquery = "SELECT * FROM $wpdb->posts
                WHERE $wpdb->posts.post_type = 'attachment'
                AND $wpdb->posts.post_mime_type LIKE 'image%'
                ";

        $result = $wpdb->get_results($imagesquery);
        foreach ($result as $post) {
            setup_postdata($post);
            $attachmentid = $post->ID;
            $parentid = $post->post_parent;

            $idquery = "SELECT ID FROM $wpdb->posts WHERE ID = $parentid";
            $result2 = $wpdb->get_results($idquery);

            if( ! isset( $result2[0]->ID ) && $parentid == '0') {
                $delete_result = wp_delete_attachment( $attachmentid, true );

                if(! is_wp_error($delete_result)) {
                    echo 'Removed Attachment ID: '. $attachmentid. ' || Post ID: ' . $parentid . " || Existeix: " . $result2[0]->ID ."\n";
                } else {
                    echo 'Couldn\'t removed attachment ID: '. $attachmentid."\n";
                }
            }
        }
    }

    /**
     * Converts all download post_types to a ACF field
     */
    private function convert_downloads_to_acf()
    {
        global $wpdb;

        $baixadesquery = "SELECT * FROM $wpdb->posts
                WHERE $wpdb->posts.post_type = 'baixada'
                ";

        $result = $wpdb->get_results($baixadesquery);
        foreach ($result as $post) {
            $baixada_id = $post->ID;
            $parent_id = wpcf_pr_post_get_belongs($baixada_id, 'programa');
            $field_key = $this->acf_get_field_key("baixada", $parent_id);

            //Get OS information from baixada
            $term_list = wp_get_post_terms($baixada_id, 'sistema-operatiu-programa', array("fields" => "all"));
            if ( $term_list ) {
                $os = $term_list[0]->slug;
            } else {
                $os = '';
            }

            $valoracio = get_post_meta( $parent_id, 'wpcf-vots', true );
            if (strpos($valoracio, ',') !== false) {
                update_post_meta($parent_id, 'wpcf-vots', str_replace(',', '.', $valoracio));
            }

            //Get original values
            $value = get_field($field_key, $parent_id);

            //Append new values
            $value[] = array(
                "download_url" => get_post_meta( $baixada_id, 'wpcf-url_baixada', true ),
                "download_version" => get_post_meta( $baixada_id, 'wpcf-versio_baixada', true ),
                "download_size" => get_post_meta( $baixada_id, 'wpcf-mida_baixada', true ),
                "arquitectura" => $this->get_arch(get_post_meta( $baixada_id, 'wpcf-arquitectura_baixada', true )),
                "download_os" => $os
            );
            update_field( $field_key, $value, $parent_id );

            //Set the operating system taxonomy for program
            $terms = array( $this->get_taxonomy_id( $os, 'sistema-operatiu-programa' ) );
            $terms = array_map( 'intval', $terms );
            wp_set_object_terms( $parent_id, $terms, 'sistema-operatiu-programa', true );
        }
    }

    /**
     * Maps the old arquitectura version
     */
    private function get_arch($id)
    {
        if($id == '1') {
            return 'x86';
        } else {
            return 'x86_64';
        }
    }

    /**
     * Gets the taxonomy id from a os name
     */
    private function get_taxonomy_id( $taxonomy_name, $taxonomy )
    {
        $id = term_exists($taxonomy_name, $taxonomy);
        return $id['term_id'];
    }

    /**
     * Gets the field key from a field_name
     */
    function acf_get_field_key( $field_name, $post_id ) {
        global $wpdb;
        $acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_parent,post_name FROM $wpdb->posts WHERE post_excerpt=%s AND post_type=%s" , $field_name , 'acf-field' ) );
        // get all fields with that name.
        switch ( count( $acf_fields ) ) {
            case 0: // no such field
                return false;
            case 1: // just one result.
                return $acf_fields[0]->post_name;
        }
        // result is ambiguous
        // get IDs of all field groups for this post
        $field_groups_ids = array();
        $field_groups = acf_get_field_groups( array(
            'post_id' => $post_id,
        ) );
        foreach ( $field_groups as $field_group )
            $field_groups_ids[] = $field_group['ID'];

        // Check if field is part of one of the field groups
        // Return the first one.
        foreach ( $acf_fields as $acf_field ) {
            if ( in_array($acf_field->post_parent,$field_groups_ids) )
                return $acf_fields[0]->post_name;
        }
        return false;
    }
    
    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php sc_functions.php -- [options]

  --action <action>            Executes one of the predefined actions

  <action>     Possible actions: export_fields (throws a xml), import_fields (requires --file argument with xml path)

USAGE;
    }
    
    /**
     * Retrieve argument value by name or false
     *
     * @param string $name the argument name
     * @return mixed
     */
    public function getArg($name)
    {
        if (isset($this->_args[$name])) {
            return $this->_args[$name];
        }
        return false;
    }
    
    /**
     * Parse input arguments
     *
     * @return Mage_Shell_Abstract
     */
    protected function _parseArgs()
    {
        $current = null;
        foreach ($_SERVER['argv'] as $arg) {
            $match = array();
            if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                $current = $match[1];
                $this->_args[$current] = true;
            } else {
                if ($current) {
                    $this->_args[$current] = $arg;
                } else if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                    $this->_args[$match[1]] = true;
                }
            }
        }
        return $this;
    }
}

$shell = new WordPress_Shell_SC_Functions();
$shell->run();
