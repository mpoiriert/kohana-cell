<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nucleus\Cell\Kohana;

use Nucleus\Framework\SingletonApplicationKernel;
use Request;
use Cookie;

/**
 * Description of ApplicationKernel
 *
 * @author Martin
 */
abstract class KohanaApplicationKernel extends SingletonApplicationKernel
{
    /**
     * The directory in which your application specific resources are located.
     * The application directory must contain the bootstrap.php file.
     *
     * @link http://kohanaframework.org/guide/about.install#application
     */
    protected $application = 'application';

    /**
     * The directory in which your modules are located.
     *
     * @link http://kohanaframework.org/guide/about.install#modules
     */
    protected $modules = 'modules';

    /**
     * The directory in which the Kohana resources are located. The system
     * directory must contain the classes/kohana.php file.
     *
     * @link http://kohanaframework.org/guide/about.install#system
     */
    protected $system = 'system';

    protected $cookieSalt = 'default';
    
    protected function initiliazeKohanaVariables()
    {
        /**
         * The default extension of resource files. If you change this, all resources
         * must be renamed to use the new extension.
         *
         * @link http://kohanaframework.org/guide/about.install#ext
         */
        define('EXT', '.php');

        /**
         * Set the PHP error reporting level. If you set this in php.ini, you remove this.
         * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
         *
         * When developing your application, it is highly recommended to enable notices
         * and strict warnings. Enable them by using: E_ALL | E_STRICT
         *
         * In a production environment, it is safe to ignore notices and strict warnings.
         * Disable them by using: E_ALL ^ E_NOTICE
         *
         * When using a legacy application with PHP >= 5.3, it is recommended to disable
         * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
         */
        error_reporting(E_ALL | E_STRICT);

        /**
         * End of standard configuration! Changing any of the code below should only be
         * attempted by those with a working knowledge of Kohana internals.
         *
         * @link http://kohanaframework.org/guide/using.configuration
         */
        // Set the full path to the docroot
        define('DOCROOT', $this->getDocRoot());
    }
    
    /**
     * Return the the document root of Kohana to initialize the DOCROOT constant
     */
    abstract protected function getDocRoot();
    
    protected function checkInstallation()
    {
        if (file_exists(DOCROOT . 'install' . EXT)) {
            // Load the installation check
            ob_start();
            require DOCROOT . 'install' . EXT;
            $result = str_replace(
                '<body>', 
                '<body><strong>You are using Kohana with Nucleus, override the [checkInstallation] method with a empty content</strong>', 
                ob_get_clean()
            );
            echo $result;
            exit;
        }
    }
    
    protected function preCreation()
    {
        $this->initiliazeKohanaVariables();
        
        $application = $this->application;
        $modules = $this->modules;
        $system = $this->system;
        
        // Make the application relative to the docroot, for symlink'd index.php
        if (!is_dir($application) AND is_dir(DOCROOT . $application)) $application = DOCROOT . $application;

        // Make the modules relative to the docroot, for symlink'd index.php
        if (!is_dir($modules) AND is_dir(DOCROOT . $modules)) $modules = DOCROOT . $modules;

        // Make the system relative to the docroot, for symlink'd index.php
        if (!is_dir($system) AND is_dir(DOCROOT . $system)) $system = DOCROOT . $system;

        // Define the absolute paths for configured directories
        define('APPPATH', realpath($application) . DIRECTORY_SEPARATOR);
        define('MODPATH', realpath($modules) . DIRECTORY_SEPARATOR);
        define('SYSPATH', realpath($system) . DIRECTORY_SEPARATOR);

        // Clean up the configuration vars
        unset($application, $modules, $system);

        $this->checkInstallation();

        /**
         * Define the start time of the application, used for profiling.
         */
        if (!defined('KOHANA_START_TIME')) {
            define('KOHANA_START_TIME', microtime(TRUE));
        }

        /**
         * Define the memory usage at the start of the application, used for profiling.
         */
        if (!defined('KOHANA_START_MEMORY')) {
            define('KOHANA_START_MEMORY', memory_get_usage());
        }
    }
    
    protected function postCreation()
    {
        require APPPATH . 'bootstrap' . EXT;
        if(!Cookie::$salt) {
            Cookie::$salt = $this->cookieSalt;
        }
    }

    public function handleRequest()
    {
        echo Request::factory()
            ->execute()
            ->send_headers(TRUE)
            ->body();
    }
}
