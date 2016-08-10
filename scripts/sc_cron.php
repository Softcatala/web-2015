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
                case 'all':
                    $this->update_mozilla();
                    $this->update_libreoffice();
                    $this->update_osmad();
                    $this->update_ubuntu();
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
        $info_url = 'https://gent.softcatala.org/albert/mapa/';
        $downloads_info_text = do_json_api_call( $info_url );

        //General download info
        $regexp = "/href='' class='name'>(.*).obf</siU";
        if( preg_match_all($regexp, $downloads_info_text, $matches )) {
            $downloads = array_unique ($matches[1] );
            foreach ($downloads as $key => $download) {
                $version_info[$key]['download_url'] = $info_url.$download. '.obf';
                $version_info[$key]['arquitectura'] = 'x86';
                $version_info[$key]['download_os'] = 'multiplataforma';
                $version_info[$key]['download_version'] = str_replace('Territori-catala-', '', $download);
            }
        }

        //Download size
        $regexp = "/>(.* MB)</siU";
        if( preg_match_all($regexp, $downloads_info_text, $matches1 )) {
            $sizes = array_unique ($matches1[1] );

            foreach ($sizes as $key => $size) {
                $version_info[$key]['download_size'] = substr($size, -8);
            }
        }

        $post = get_page_by_path( 'mapa-catala-per-a-losmand' , OBJECT, 'programa' );
        $field_key = $this->acf_get_field_key( "baixada", $post->ID );
        update_field($field_key, $version_info, $post->ID);
    }

    private function update_ubuntu() {
        $ubuntu_flavours = array('ubuntu-mate', 'ubuntu-gnome', 'xubuntu', 'ubuntu', 'kubuntu');

        $info_url = 'https://gent.softcatala.org/jmontane/check-version/ubuntu/latest_files.txt';
        $downloads_info_csv = do_json_api_call( $info_url );
        $downloads_info = explode( PHP_EOL, $downloads_info_csv );

        $ubuntu_downloads = array();

        foreach ($downloads_info as $key => $download_info) {
            $download = explode('|', $download_info);

            if (  in_array( $download[0], $ubuntu_flavours, true)) {

                $offset = ( $download[0] === 'ubuntu' ) ? 1 : 0;

                $ubuntu_version = $this->get_ubuntu_version($download[1], $download[2+$offset], $download[3+$offset*2], $download[4+$offset*2]);
                $ubuntu_downloads[$download[0]][] = $ubuntu_version;
            }
        }

        foreach ( $ubuntu_flavours as $post_slug ) {
            $ubuntu_post = get_page_by_path( $post_slug , OBJECT, 'programa' );

            update_field('baixada', $ubuntu_downloads[$post_slug], $ubuntu_post->ID);
        }
    }

    private function get_ubuntu_version($arch, $version, $size, $url) {

        $version_info = array();

        $version_info['download_version'] = $version;
        $version_info['download_url'] = $url;
        $version_info['download_size'] = $this->from_bytes_to_kb( floatval($size) );
        $version_info['arquitectura'] = (strpos($arch, 'amd64') !== false) ? 'x86_64' : 'x86';

        return $version_info;
    }

    /**
     * Updates LibreOffice programs
     */
    private function update_libreoffice()
    {
        $libreoffice_posts = array (
            'libreoffice'  => 'libreoffice',
            'ajuda'         => 'paquet-dajuda-en-catala-del-libreoffice',
            'ajuda_val'     => 'paquet-dajuda-en-catala-valencia-del-libreoffice',
            'langpack'      => 'paquet-catala-per-al-libreoffice',
            'langpack_val'  => 'paquet-dajuda-en-catala-valencia-del-libreoffice'
        );

        $info_url = 'https://gent.softcatala.org/jmontane/libo/latest_files.txt';
        $downloads_info_csv = do_json_api_call( $info_url );
        $downloads_info = explode( PHP_EOL, $downloads_info_csv );

        //Posts initialization
        foreach ( $libreoffice_posts as $post_key => $post_slug ) {
            $post[$post_key] = get_page_by_path( $post_slug , OBJECT, 'programa' );
        }

        foreach ($downloads_info as $key => $download_info) {
            $download = explode(' ', $download_info);
            $os = $this->get_libreoffice_donwload_os_from_url( $download[2] );
            if( $os != '' ) {
                if(strpos($download[2], 'helppack_ca-valencia') !== false) {
                    $version_info['ajuda_val'][] = $this->process_libreoffice_info($download, $os);
                } else if(strpos($download[2], 'helppack_ca') !== false) {
                    $version_info['ajuda'][] = $this->process_libreoffice_info($download, $os);
                } else if(strpos($download[2], 'langpack_ca-valencia') !== false) {
                    $version_info['langpack_val'][] = $this->process_libreoffice_info($download, $os);
                } else if(strpos($download[2], 'langpack_ca') !== false) {
                    $version_info['langpack'][] = $this->process_libreoffice_info($download, $os);
                } else {
                    $version_info['libreoffice'][] = $this->process_libreoffice_info($download, $os);
                }
            }
        }

        //Linux downloads and fields update
        foreach ( $libreoffice_posts as $post_key => $post_slug ) {
            $version_info[$post_key][] = $this->process_libreoffice_info($version_info[$post_key][0]['download_version'], 'linux');

            $field_key = $this->acf_get_field_key( "baixada", $post[$post_key]->ID );
            update_field($field_key, $version_info[$post_key], $post[$post_key]->ID);
        }
    }

    /**
     * Processes the LibreOffice unsorted info
     */
    private function process_libreoffice_info($download, $os)
    {
        $version_info = array();

        $version_info['download_os'] = $os;

        if ($os == 'linux' ) {
            $version_info['download_version'] = $download;
            $version_info['download_url'] = 'http://ca.libreoffice.org/baixada/?nodetect';
            $version_info['arquitectura'] = 'x86_64';
            $version_info['download_size'] = '';
        } else {
            $version_info['download_version'] = $download[1];
            $version_info['download_url'] = $download[2];
            $version_info['download_size'] = $this->from_bytes_to_kb( floatval($download[0]) );
            $version_info['arquitectura'] = $this->is_libo_64bits($download[2]) ? 'x86_64' : 'x86';
        }

        return $version_info;
    }

    private function is_libo_64bits($arch) {
        return (strpos($arch, 'x86-64') !== false) || (strpos($arch, 'x64') !== false);
    }

    /**
     * Returns the os related to a libreoffice download
     */
    private function get_libreoffice_donwload_os_from_url( $url )
    {
        if (strpos($url, '/win/') !== false) {
            $os = 'windows';
        } elseif (strpos($url, '/mac/') !== false) {
            $os = 'osx';
        } else {
            $os = false;
        }

        return $os;
    }

    /**
     * Returns the Bytes size value in KB
     */
    private function from_bytes_to_kb($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'MB', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }


    /**
     * Updates Mozilla programs
     */
    private function update_mozilla()
    {
        $products_list = array(
            array(
                'slug' => 'firefox',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/firefox_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'firefox' ),
                'stable' => 'LATEST_FIREFOX_VERSION',
            ),
            array(
                'slug' => 'paquet-catala-per-al-firefox',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/firefox_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'paquet-catala-per-al-firefox' ),
                'stable' => 'LATEST_FIREFOX_VERSION',
            ),
            array(
                'slug' => 'paquet-catala-valencia-per-al-firefox',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/firefox_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'paquet-catala-valencia-per-al-firefox' ),
                'stable' => 'LATEST_FIREFOX_VERSION',
            ),
            array(
                'slug' => 'diccionari-catala-firefox',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/firefox_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'diccionari-catala-firefox' ),
                'stable' => 'LATEST_FIREFOX_VERSION',
            ),
            array(
                'slug' => 'diccionari-valencia-firefox',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/firefox_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'diccionari-valencia-firefox' ),
                'stable' => 'LATEST_FIREFOX_VERSION',
            ),
            array(
                'slug' => 'thunderbird',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/thunderbird_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'thunderbird' ),
                'stable' => 'LATEST_THUNDERBIRD_VERSION',
                'arquitectura' => array ( 'x86' => '', 'x86_64' => '64')
            ),
            array(
                'slug' => 'paquet-catala-per-al-thunderbird',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/thunderbird_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'paquet-catala-per-al-thunderbird' ),
                'stable' => 'LATEST_THUNDERBIRD_VERSION',
                'arquitectura' => array ( 'x86' => '', 'x86_64' => '64')
            ),
            array(
                'slug' => 'paquet-catala-valencia-per-al-thunderbird',
                'type' => 'json',
                'json_url' => 'http://viewvc.svn.mozilla.org/vc/libs/product-details/json/thunderbird_versions.json?view=co&content-type=text%2Fplain',
                'os' => $this->get_moz_os( 'paquet-catala-valencia-per-al-thunderbird' ),
                'stable' => 'LATEST_THUNDERBIRD_VERSION',
                'arquitectura' => array ( 'x86' => '', 'x86_64' => '64')
            )
        );

        foreach ($products_list as $product) {
            if ( $post = get_page_by_path( $product['slug'] , OBJECT, 'programa' ) ) {
                $version_info = $this->process_mozilla_json_info($product);
                $field_key = $this->acf_get_field_key("baixada", $post->ID);

                update_field( $field_key, $version_info, $post->ID );
            }
        }
    }

    /**
     * Process the json information coming from an url
     */
    private function process_mozilla_json_info($product)
    {
        $json = json_decode( do_json_api_call( $product['json_url'] ));
        $version = $json->{$product['stable']};

        foreach($product['os'] as $os_wp => $oses) {
            foreach( $oses as $arch_wp => $os ) {
                if( $arch_wp == 'android' or $arch_wp == 'ios' or $arch_wp == 'multiplataforma') {
                    $arch_wp = 'x86';
                    $download_url = $os;
                    ($arch_wp == 'multiplataforma') ? $version = '' : '';
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
    private function get_moz_os( $program )
    {
        switch( $program ) {
            case 'firefox':
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
                break;
            case 'thunderbird':
                $moz_os = array(
                    'windows' => array(
                        'x86' => 'win',
                    ),
                    'osx' => array (
                        'x86' => 'osx',
                    ),
                    'linux' => array(
                        'x86' => 'linux',
                        'x86_64' => 'linux64'
                    )
                );
                break;
            case 'paquet-catala-per-al-firefox':
                $moz_os = array(
                    'multiplataforma' => array (
                        'multiplataforma' => 'https://addons.mozilla.org/firefox/downloads/latest/5019/addon-5019-latest.xpi'
                    )
                );
                break;
            case 'paquet-catala-valencia-per-al-firefox':
                $moz_os = array(
                    'multiplataforma' => array (
                        'multiplataforma' => 'https://addons.mozilla.org/firefox/downloads/latest/9702/addon-9702-latest.xpi'
                    )
                );
                break;
            case 'diccionari-valencia-firefox':
                $moz_os = array(
                    'multiplataforma' => array (
                        'multiplataforma' => 'https://addons.mozilla.org/firefox/downloads/latest/9192/addon-9192-latest.xpi'
                    )
                );
                break;
            case 'diccionari-catala-firefox':
                $moz_os = array(
                    'multiplataforma' => array (
                        'multiplataforma' => 'https://addons.mozilla.org/firefox/downloads/latest/3369/addon-3369-latest.xpi'
                    )
                );
                break;
            case 'paquet-catala-per-al-thunderbird':
                $moz_os = array(
                    'multiplataforma' => array (
                        'multiplataforma' => 'https://addons.mozilla.org/thunderbird/downloads/latest/640732/addon-640732-latest.xpi'
                    )
                );
                break;
            case 'paquet-catala-valencia-per-al-thunderbird':
                $moz_os = array(
                    'multiplataforma' => array (
                        'multiplataforma' => 'https://addons.mozilla.org/thunderbird/downloads/latest/9730/addon-9730-latest.xpi'
                    )
                );
                break;

        }

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
