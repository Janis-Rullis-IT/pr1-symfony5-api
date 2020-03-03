<?php


namespace App\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener extends AbstractController
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $response =  new Response(null,Response::HTTP_NOT_FOUND);
        $event->setResponse($response);
    }
}