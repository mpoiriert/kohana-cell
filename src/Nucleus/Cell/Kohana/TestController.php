<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nucleus\Cell\Kohana;

use Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

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
    public function documentation(Response $response, Request $request)
    {
        $response->headers->setCookie(new Cookie('time',time()));
        $time = $request->cookies->get('time');
        if($time) {
            $time = date('Y-m-d H:i:s', $time);
        }
        
        return array(
            'documentation_url' => Route::url('kohana-cell-documentation'),
            'default_url' => Route::url('default'),
            'time' => $time
        );
    }
}
