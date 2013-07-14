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
use Nucleus\IService\Invoker\IInvokerService;
use Symfony\Component\HttpFoundation\Request as NucleusRequest;
use Symfony\Component\HttpFoundation\Response as NucleusResponse;

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
            return array(
                'params' => array('controller'=>'nucleus','action'=>'execute','_nucleus'=>$result),
                'route' => $route
            );
        } catch (ResourceNotFoundException $e) {
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
        
        $parameters = $kohanaParameters['_nucleus'];
        $serviceName = $parameters['_service']['name'];
        $methodName = $parameters['_service']['method'];
        
        $response = new NucleusResponse();
        $service = $this->getServiceContainer()->getServiceByName($serviceName);
        $executionResult = $this->getInvoker()->invoke(
            array($service, $methodName), 
            array_merge($request->query->all(), $request->request->all()),
            array($request, $response)
        );
        
        $result = array('result' => $executionResult);
        
        $this->completeResponse($request, $response, $result);
        $response->prepare($request);
        
        $kohanaResponse = $kohanaRequest->create_response();
        $kohanaResponse->body($response->getContent());
        $kohanaResponse->headers($response->headers->all());
        return $kohanaResponse;
    }
    
    private function completeResponse(NucleusRequest $request, NucleusResponse $response, $result)
    {
        foreach ($request->getAcceptableContentTypes() as $contentType) {
            foreach ($this->getServiceContainer()->getServicesByTag('responseAdapter') as $adapter) {
                if ($adapter->adaptResponse($contentType, $request, $response, $result)) {
                    $response->headers->set('Content-Type', $contentType);
                    return;
                }
            }
        }
    }
    
    /**
     * @return IInvokerService
     */
    private function getInvoker()
    {
        return $this->getServiceContainer()->getServiceByName(IInvokerService::NUCLEUS_SERVICE_NAME);
    }
    
    /**
     * @return Router
     */
    private function getRouter()
    {
        return $this->getServiceContainer()->getServiceByName('routing');
    }
}
