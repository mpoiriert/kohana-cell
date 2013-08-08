<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nucleus\Cell\Kohana;

use Route;

/**
 * Description of Documentation
 *
 * @author Martin
 */
class TestController
{
    /**
     * @\Nucleus\IService\Routing\Route(name="kohana-cell-documentation", path="/nucleus/kohana-cell/documentation")
     * @\Nucleus\IService\FrontController\ViewDefinition(template="/kohana-cell/documentation.twig")
     */
    public function documentation()
    {
        return array(
            'documentation_url' => Route::url('kohana-cell-documentation'),
            'default_url' => Route::url('default')
        );
    }
}
