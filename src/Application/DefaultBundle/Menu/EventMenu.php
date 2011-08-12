<?php

namespace Application\DefaultBundle\Menu;

use Knp\Bundle\MenuBundle\Menu;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class EventMenu extends MainMenu
{
    /**
     * @param Request $request
     * @param Router $router
     */
    public function __construct(Request $request, Router $router)
    {
        parent::__construct($request, $router);

        $this->addChild('← На главную', $router->generate('homepage'))
             ->moveToPosition(0);
    }
}