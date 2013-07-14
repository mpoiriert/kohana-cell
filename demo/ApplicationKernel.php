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
            ->setCachePath(dirname(__DIR__) . '/cache')
            ->setDebug(true);
    }
    
    public function bootstrap()
    {
        require_once __DIR__ . '/../vendor/kohana/kohana/index.php';
    }
}
