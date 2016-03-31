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
                default:
                    echo $this->usageHelp();
                    break;
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieves the program parameter and calls the update function
     */
    private function update_program_download_info()
    {
        if ($program = $this->getArg('program')) {
            switch($program) {
                case 'mozilla':
                    $this->update_mozilla();
                    break;
                case 'libreoffice':
                    $this->update_libreoffice();
                    break;
            }
        } else {
            echo $this->usageHelp();
        }
    }

    private function update_mozilla()
    {
        $moz_os = $this->get_moz_os();
        $products_list = array(
            array(
                'slug' => 'firefox',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/firefox_versions.json?view=co&revision=150465&content-type=text%2Fplain',
                'os' => $moz_os,
                'stable' => 'LATEST_FIREFOX_VERSION',
            ),
            array(
                'slug' => 'thunderbird',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/thunderbird_versions.json?view=co&revision=150514&content-type=text%2Fplain',
                'os' => $moz_os,
                'stable' => 'LATEST_FIREFOX_VERSION',
                'arquitectura' => array ( 'x86' => '', 'x86_64' => '64')
            )
        );

        foreach ($products_list as $product) {
            if ( $post = get_page_by_path( $product['slug'] , OBJECT, 'programa' ) ) {
                $version_info = $this->process_json_info($product);
                $field_key = $this->acf_get_field_key("baixada", $post->ID);

                update_field( $field_key, $version_info, $post->ID );
            }
        }
    }

    /**
     * Process the json information coming from an url
     */
    private function process_json_info($product)
    {
        $json = json_decode( do_json_api_call( $product['json_url'] ));
        $version = $json->{$product['stable']};

        foreach($product['os'] as $os_wp => $oses) {
            foreach( $oses as $arch_wp => $os ) {
                if( $arch_wp == 'android' or $arch_wp == 'ios') {
                    $arch_wp == 'x86';
                    $download_url = $os;
                } else {
                    $download_url = 'https://download.mozilla.org/?product='.$product['slug'].'-'.$version.'-SSL&os='.$os.'&lang=ca';
                }

                $version_info[$os_wp.$arch_wp]['download_url'] = $download_url;
                $version_info[$os_wp.$arch_wp]['download_version'] = $version;
                $version_info[$os_wp.$arch_wp]['download_size'] = '';
                $version_info[$os_wp.$arch_wp]['arquitectura'] = $arch_wp;
                $version_info[$os_wp.$arch_wp]['download_os'] = $os_wp;
            }
        }

        return $version_info;
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
     * Returns the array with all the mozilla available os
     *
     * @return array
     */
    private function get_moz_os()
    {
        $moz_os = array(
            'windows' => array(
                'x86' => 'win',
                'x86_64' => 'win64'
            ),
            'osx' => array (
                'x86' => 'osx',
            ),
            'linux' => array(
                'x86' => 'linux',
                'x86_64' => 'linux64'
            ),
            'android' => array (
                'android' => 'https://play.google.com/store/apps/details?id=org.mozilla.firefox'
            ),
            'ios' => array (
                'ios' => 'https://itunes.apple.com/app/apple-store/id989804926'
            )
        );

        return $moz_os;
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
