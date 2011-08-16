<?php

namespace Application\DefaultBundle\Menu;

use Knp\Bundle\MenuBundle\Menu;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Bundle\DoctrineBundle\Registry;

class EventSubMenu extends Menu
{
    /**
     * @param Request $request
     * @param Router $router
     */
    public function __construct(Request $request, Router $router, $event)
    {
        parent::__construct();
        
        $this->setCurrentUri($request->getRequestUri());
        
        if ($event->getSpeakers()) {
            $this->addChild(
                    "Докладчики",
                    $router->generate('event_speakers', array('event_slug' => $event->getSlug())));
        }
        
        // Ссылки на страницы ивента
        // @todo можно добавить для страниц свойство "отображать в меню"
        foreach($event->getPages() as $page) {
            $url = $router->generate(
                    'event_page_show', 
                    array('event_slug' => $event->getSlug(), 'page_slug' => $page->getSlug()));
            $this->addChild($page->getTitle(), $url);
        }
    }
}