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
class SC_Cron
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
                case 'update_program_download_info':
                    $this->update_program_download_info();
                    break;
                case 'generate_aparells_stats':
                    $this->generate_aparells_stats();
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
     * Generates a json file with the stats of aparells
     */
    private function generate_aparells_stats()
    {
        $args = array( 'post_type' => 'aparell', 'posts_per_page' => -1 );

        $posts = Timber::get_posts( $args );

        $aparells_data['total'] = 0;
        $aparells_data['conf_cat'] = 0;
        $aparells_data['correccio_cat'] = 0;
        foreach ($posts as $post) {
            if($post->conf_cat) {
                $aparells_data['conf_cat'] = $aparells_data['conf_cat'] + 1;
            }

            if($post->correccio_cat) {
                $aparells_data['correccio_cat'] = $aparells_data['correccio_cat'] + 1;
            }

            $aparells_data['total'] = $aparells_data['total'] + 1;
        }

        echo json_encode($aparells_data);
    }

    /**
     * Retrieves the program parameter and calls the update function
     */
    private function update_program_download_info()
    {
        if ($program = $this->getArg('program')) {
            switch($program) {
                case 'ubuntu':
                    $this->update_ubuntu();
                    break;
                case 'mozilla':
                    $this->update_mozilla();
                    break;
                case 'libreoffice':
                    $this->update_libreoffice();
                    break;
                case 'osmand':
                    $this->update_osmad();
                    break;
                case 'calibre':
                    $this->update_calibre();
                    break;
                case 'gimp':
                    $this->update_gimp();
                    break;
                case 'inkscape':
                    $this->update_inkscape();
                    break;
                case 'all':
                    $this->update_mozilla();
                    $this->update_libreoffice();
                    $this->update_osmad();
                    $this->update_ubuntu();
                    $this->update_calibre();
                    $this->update_gimp();
                    $this->update_inkscape();
                    break;
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Updates OSMAnd maps
     */
    private function update_osmad()
    {
        $url = 'https://api.softcatala.org/rebost-releases/v1/osmand';
        $this->generic_update('mapa-catala-per-a-losmand', $url);
    }


    private function update_ubuntu() {
        $ubuntu_flavours = array('ubuntu-mate', 'xubuntu', 'ubuntu', 'kubuntu');

        $base_url = 'https://api.softcatala.org/rebost-releases/v1/ubuntu';
        foreach ( $ubuntu_flavours as $post_slug ) {
            $this->generic_update($post_slug, $base_url . '/' . $post_slug);
        }
    }

    private function generic_update($slug, $url) {
        $result = do_json_api_call( $url );
        if ( $result == 'error' ) {
            return;
        }

        $versions = json_decode( $result, true);

        $post = get_page_by_path( $slug , OBJECT, 'programa' );

        $field_key = $this->acf_get_field_key("baixada", $post->ID);
        update_field($field_key, $versions, $post->ID);
    }

    /**
     * Updates LibreOffice programs
     */
    private function update_libreoffice()
    {
        $packages = array(
            'libreoffice' => 'libreoffice',
            'helppack-ca' => 'paquet-dajuda-en-catala-del-libreoffice',
            'helppack-ca-valencia' => 'paquet-dajuda-en-catala-valencia-del-libreoffice',
            'langpack-ca' => 'paquet-catala-per-al-libreoffice',
            'langpack-ca-valencia' => 'paquet-catala-valencia-per-al-libreoffice'
        );

        $base_url = 'https://api.softcatala.org/rebost-releases/v1/libreoffice';
        foreach ( $packages as $path => $post_slug ) {
            $this->generic_update($post_slug, $base_url . '/' . $path);
        }
    }

    /**
     * Updates Calibre
     */
    private function update_calibre() {
        $url = 'https://api.softcatala.org/rebost-releases/v1/calibre';
        $this->generic_update('calibre', $url);
    }

    /**
     * Updates Inkscape
     */
    private function update_inkscape() {
        $url = 'https://api.softcatala.org/rebost-releases/v1/inkscape';
        $this->generic_update('inkscape', $url);
    }

    /**
     * Updates GIMP
     */
    private function update_gimp()
    {
        $url = 'https://api.softcatala.org/rebost-releases/v1/gimp';
        $this->generic_update('inkscape', $url);
    }

    /**
     * Updates Mozilla programs
     */
    private function update_mozilla()
    {
        $packages = array(
            'firefox' => 'firefox',
            'firefox-valencia' => 'firefox-en-valencia',
            'firefox-langpack-ca' => 'paquet-catala-per-al-firefox',
            'firefox-langpack-ca-valencia' => 'paquet-catala-valencia-per-al-firefox',
            'dict-ca' => 'diccionari-catala-firefox',
            'dict-ca-valencia' => 'diccionari-valencia-firefox',
            'thunderbird' => 'thunderbird',
            'thunderbird-langpack-ca' => 'paquet-catala-per-al-thunderbird',
            'thunderbird-langpack-ca-valencia' => 'paquet-catala-valencia-per-al-thunderbird'
        );

        $base_url = 'https://api.softcatala.org/rebost-releases/v1/mozilla';
        foreach ( $packages as $path => $post_slug ) {
            $this->generic_update($post_slug, $base_url . '/' . $path);
        }
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
  --program <program-slug>     Program to be auto-updated, needs the wordpress program slug

  <action>     Possible actions: update_program_download_info (throws a xml), import_fields (requires --file argument with xml path)

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

$shell = new SC_Cron();
$shell->run();
