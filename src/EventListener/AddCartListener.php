<?php

namespace App\EventListener;

use App\Entity\Product;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

final class AddCartListener
{
    #[AsEventListener(event: 'kernel.controller_arguments')]
    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        if ($event->getArguments() instanceof Product)
        {
            dd($event->getArguments());
        }
    }
}
