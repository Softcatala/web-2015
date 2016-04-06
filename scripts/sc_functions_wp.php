<?php
/**
 * WordPress Shell Functions for Softcatalà project
 * Important: this script has to be placed in the WordPress base directory (where index.php is)
 */

require( 'wp/wp-blog-header.php' );

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
                case 'convert_projects_to_acf':
                    $this->convert_projects_to_acf();
                    break;
                case 'get_redirections':
                    $this->get_redirections();
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
     * Converts wp-fields into acf fields in projects
     */
    protected function convert_projects_to_acf() {
        global $wpdb;

        $posts_query = "SELECT * FROM $wpdb->posts
                WHERE $wpdb->posts.post_type = 'projecte'
                ";

        $result = $wpdb->get_results($posts_query);
        foreach ($result as $post) {
            $post_id = $post->ID;

            //Append new project values
            $values = array(
                "subtitle_projecte" => get_post_meta( $post_id, 'wpcf-subtitle_projecte', true ),
                "responsable" => get_post_meta( $post_id, 'wpcf-responsable', true ),
                "logotip" => $this->get_caption_from_media_url( get_post_meta( $post_id, 'wpcf-logotip', true ), true ),
                "lloc_web_projecte" => get_post_meta( $post_id, 'wpcf-lloc_web_projecte', true ),
                "llista_de_correu" => get_post_meta( $post_id, 'wpcf-llista_de_correu', true ),
                "url_rebost_pr" => get_post_meta( $post_id, 'wpcf-url_rebost_pr', true )
            );
            $this->save_values_acf( $values, $post_id );

            //Values for steps
            if( get_post_meta( $post_id, 'wpcf-lectures_recomanades', true ) && get_post_meta( $post_id, 'wpcf-project_requirements', true )) {
                $step_values = array (
                    "steps" =>
                    array(
                        array(
                            'step_title' => 'Lectures recomanades',
                            'step_content' => get_post_meta( $post_id, 'wpcf-lectures_recomanades', true )
                        ),
                        array(
                            'step_title' => 'Requeriments del projecte',
                            'step_content' => get_post_meta( $post_id, 'wpcf-project_requirements', true )
                        )
                    )
                );

                $this->save_values_acf( $step_values, $post_id );
            }

            //Arxivat
            $arxivat = get_post_meta( $post_id, 'wpcf-arxivat_pr', true );
            if($arxivat == '1') {
                //Set the operating system taxonomy for program
                $terms = array( $this->get_taxonomy_id( 'arxivat', 'classificacio' ) );
                $terms = array_map( 'intval', $terms );
                wp_set_object_terms( $post_id, $terms, 'classificacio', true );
            }
        }
    }

    /**
     * Saves a list of values in ACF
     */
    protected function save_values_acf( $values, $post_id ) {
        foreach ($values as $field_key => $value) {
            $field_key = $this->acf_get_field_key( $field_key, $post_id );
            update_field($field_key, $value, $post_id);
        }
    }

    /**
     * Generates a file with all the redirections
     */
    protected function get_redirections()
    {
        if ($section = $this->getArg('section')) {

            switch($section) {
                case 'rebost':
                    $section_data['post_type'] = 'programa';
                    $section_data['title'] = 'rebost';
                    $section_data['wpcf_field_name'] = 'wpcf-url_rebost';
                    break;
                case 'projectes':
                    $section_data['post_type'] = 'projecte';
                    $section_data['title'] = 'projectes';
                    $section_data['wpcf_field_name'] = 'wpcf-url_rebost_pr';
                    break;
            }

            $this->get_section_redirection($section_data);
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Generates a file with all the redirections
     */
    protected function get_section_redirection( $section_data )
    {
        global $wpdb;

        $redirects_file = '# '. $section_data['title'] . "\n\n";
        $sc_prod_url = 'https://www.softcatala.org';

        $posts_query = "SELECT * FROM $wpdb->posts
                WHERE $wpdb->posts.post_type = '" . $section_data['post_type'] . "'
                ";

        $result = $wpdb->get_results($posts_query);
        foreach ($result as $post) {
            $section_sc_url = get_post_meta( $post->ID, $section_data['wpcf_field_name'], true );
            if($section_sc_url) {
                $sc_uri = str_replace( $sc_prod_url, '^', $section_sc_url );

                $post_local_uri = get_permalink($post);
                $post_sc_url = str_replace(get_home_url(), '', $post_local_uri);

                $redirection = $sc_uri . ' ' . $post_sc_url;

                $redirects_file .= "rewrite $redirection permanent;\n";
            }
        }

        echo $redirects_file;
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

    /**
     * This function retrieves the media caption from
     * a given url. It is used because the «secondary image»
     * created from Types doesn't return the media caption
     * Author: https://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
     *
     * @param string $url
     * @return string $caption
     */
    protected function get_caption_from_media_url( $attachment_url = '', $return_id = false ) {

        global $wpdb;
        $attachment_id = false;

        // If there is no url, return.
        if ( '' == $attachment_url )
            return;

        // Get the upload directory paths and clean the attachment url
        $upload_dir_paths = wp_upload_dir();
        $attachment_url = str_replace( 'wp/../', '', $attachment_url );

        // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
        if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

            // If this is the URL of an auto-generated thumbnail, get the URL of the original image
            $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

            // Remove the upload path base directory from the attachment URL
            $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

            // Finally, run a custom database query to get the attachment ID from the modified attachment URL
            $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

        }

        //Not in the original function from the author
        $attachment_meta = get_post_field('post_excerpt', $attachment_id);

        if( $return_id ) {
            return $attachment_id;
        }

        return $attachment_meta;
    }
}

$shell = new WordPress_Shell_SC_Functions();
$shell->run();
