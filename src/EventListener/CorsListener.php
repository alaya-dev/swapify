<?php


namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CorsListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Symfony\Component\HttpFoundation\Response();
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept');
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:*');
    }
}