<?php
/**
 * WordPress Shell Functions for Softcatalà project
 * Important: this script has to be placed in the WordPress base directory (where index.php is)
 */

require "wp-config.php";
require_once('../plugins/types/wpcf.php');
require_once('../plugins/types/admin.php');

require_once WPCF_INC_ABSPATH . '/fields.php';
require_once WPCF_INC_ABSPATH . '/import-export.php';

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
                case 'export_fields':
                    $this->export_fields();
                    break;
                case 'import_fields':
                    $this->import_fields();
                    break;
                default:
                    echo $this->usageHelp();
                    break;
            }
        } else {
            echo $this->usageHelp();
        }
    }
    
    private function export_fields()
    {
        $fieldsxml = wpcf_admin_export_selected_data( array(), 'all', 'xml' );
        echo $fieldsxml;
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

