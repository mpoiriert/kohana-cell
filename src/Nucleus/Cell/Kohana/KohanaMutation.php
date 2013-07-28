<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nucleus\Cell\Kohana;

use Go\Aop\Intercept\MethodInvocation;
use Nucleus\DependencyInjection\BaseAspect;
use Nucleus\Routing\Router;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Route;
use Symfony\Component\HttpFoundation\Request as NucleusRequest;
use Request as KohanaRequest;
use Nucleus\IService\EventDispatcher\IEventDispatcherService;
use ArrayObject;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Description of KohanaAspect
 *
 * @author Martin
 * 
 * @Aspect
 */
class KohanaMutation extends BaseAspect
{
    /**
     * @param MethodInvocation $invocation Invocation
     *
     * @Go\Lang\Annotation\Around("execution(public Kohana_Request::process_uri(*))")
     */
    public function aroundKohanaRequestProcessUri(MethodInvocation $invocation)
    {
        $arguments = $invocation->getArguments();
        try {
            $result = $this->getRouter()->match('/' . $arguments[0]);
            $route = new Route($arguments[0]);
            $route->defaults($result);
            $params = new ArrayObject(
                array(
                    'params' => array('controller'=>'nucleus','action'=>'execute','_nucleus'=>$result),
                    'route' => $route
                )
            );

            $this->getEventDispatcher()->dispatch('KohanaMutation.processUri',$params);
    
            return $params->getArrayCopy();
        } catch (ResourceNotFoundException $e) {
            return $invocation->proceed();
        }
    }
    
    /**
     * @param MethodInvocation $invocation Invocation
     *
     * @Go\Lang\Annotation\Around("execution(public Nucleus\Routing\Router->addRoute(*))")
     */
    public function beforeAddRoute(MethodInvocation $invocation)
    {
        list($name, $path, $defaults, $requirements, $options, $host, $schemes, $methods) = $invocation->getArguments();
       
        if(isset($defaults['_culture']) && $defaults['_culture']) {
            $name = $defaults['_culture'] . ':i18n:' . $name;
        }
        
        Route::set($name, $path)->defaults($defaults);
        
        $invocation->proceed();
    }
    
    /**
     * @param MethodInvocation $invocation Invocation
     *
     * @Go\Lang\Annotation\Around("execution(public Kohana_Route->uri(*))")
     */
    public function aroundKohanaRouteGet(MethodInvocation $invocation)
    {
        $arguments = $invocation->getArguments();
        $route = $invocation->getThis();
        $name = Route::name($route);
        
        $parameters = $arguments[0];
        if(empty($parameters)) {
            $parameters = array();
        }
        
        try {
            return $this->getRouter()->generate($name, $parameters);
        } catch (RouteNotFoundException $e) {
            return $invocation->proceed();
        }
    }
    
     /**
     * @param MethodInvocation $invocation Invocation
     *
     * @Go\Lang\Annotation\Around("execution(public Kohana_Request_Client_Internal->execute_request(*))")
     */
    public function aroundKohanaRequestClientInternalExecuteRequest(MethodInvocation $invocation)
    {
        $arguments = $invocation->getArguments();
        $kohanaRequest = $arguments[0];
        /* @var $kohanaRequest \Request */
        $kohanaParameters = $kohanaRequest->param();
        
        if(!isset($kohanaParameters['_nucleus'])) {
            return $invocation->proceed();
        }

        $previousKohanaRequest = KohanaRequest::$current;
        
        KohanaRequest::$current = $kohanaRequest;
        
        $request = new NucleusRequest(
            $kohanaRequest->query(),
            $kohanaRequest->post(),
            $kohanaParameters['_nucleus'],
            $kohanaRequest->cookie(),
            $_FILES,
            $_SERVER
            //Body might be needed here
        );
        
        $request->request->add($kohanaParameters['_nucleus']);
        
        $service = $request->request->get('_service');
           
        $response = $this->getFrontController()
            ->execute($service['name'], $service['method'], $request);

        $kohanaResponse = $kohanaRequest->create_response();
        $kohanaResponse->body($response->getContent());
        $kohanaResponse->headers($response->headers->all());
        KohanaRequest::$current = $previousKohanaRequest;
        return $kohanaResponse;
    }
    
    /**
     * @return IEventDispatcherService
     */
    private function getEventDispatcher()
    {
        return $this->getServiceContainer()->getServiceByName(IEventDispatcherService::NUCLEUS_SERVICE_NAME);
    }
   
    /**
     * @return \Nucleus\FrontController\FrontController
     */
    private function getFrontController()
    {
        return $this->getServiceContainer()->getServiceByName('frontController');
    }
    
    /**
     * @return Router
     */
    private function getRouter()
    {
        return $this->getServiceContainer()->getServiceByName('routing');
    }
}
