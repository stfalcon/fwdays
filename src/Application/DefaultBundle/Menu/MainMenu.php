<?php

namespace Application\DefaultBundle\Menu;

use Knp\Bundle\MenuBundle\Menu;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class MainMenu extends Menu
{
    /**
     * @param Request $request
     * @param Router $router
     */
    public function __construct(Request $request, Router $router)
    {
        parent::__construct();

        $this->setCurrentUri($request->getRequestUri());
        
        $this->setAttribute('class', 'nav');
        
        $this->addChild('О Frameworks Days', $router->generate('page_show', array('slug' => 'about')));
        $this->addChild('События', $router->generate('events'));
        $this->addChild('Контактная информация', $router->generate('page_show', array('slug' => 'contacts')));
        $this->addChild('Партнеры', $router->generate('page_show', array('slug' => 'partners')));
    }
}