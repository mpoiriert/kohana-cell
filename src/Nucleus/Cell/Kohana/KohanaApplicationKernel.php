<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nucleus\Cell\Kohana;

use Nucleus\Framework\SingletonApplicationKernel;

/**
 * Description of ApplicationKernel
 *
 * @author Martin
 */
abstract class KohanaApplicationKernel extends SingletonApplicationKernel
{
    abstract public function bootstrap();
}
