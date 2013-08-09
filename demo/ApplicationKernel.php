<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Nucleus\Cell\Kohana\KohanaApplicationKernel;

/**
 * Description of ApplicationKernel
 *
 * @author Martin
 */
class ApplicationKernel extends KohanaApplicationKernel
{
    protected function getDnaConfiguration()
    {
        return parent::getDnaConfiguration()
            ->setConfiguration(__DIR__)
            ->setCachePath(dirname(__DIR__) . '/cache')
            ->setDebug(true);
    }
    
    protected function checkInstallation()
    {
        //parent::checkInstallation();
    }
    
    
    protected function getDocRoot()
    {
        return realpath(__DIR__ . '/../vendor/kohana/kohana/') . DIRECTORY_SEPARATOR;
    }
}
