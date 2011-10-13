<?php

namespace Application\Bundle\DefaultBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class MenuBuilder
{
    private $factory;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Main page top menu
     *
     * @param Request $request
     */
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setCurrentUri($request->getRequestUri());
        $menu->setAttribute('class', 'nav');

        $menu->addChild('О Frameworks Days', array('route' => 'page_show', 'routeParameters' => array('slug' => 'about')));
        $menu->addChild('События', array('route' => 'events'));
        $menu->addChild('Контактная информация', array('route' => 'page_show', 'routeParameters' => array('slug' => 'contacts')));
        $menu->addChild('Партнеры', array('route' => 'page_show', 'routeParameters' => array('slug' => 'partners')));

        return $menu;
    }

    /**
     * Event page top menu
     *
     * @param Request $request
     */
    public function createEventMainMenu(Request $request)
    {
        $menu = $this->createMainMenu($request);

        $menu->addChild('← На главную', array('route' => 'homepage'))
             ->moveToFirstPosition();

        return $menu;
    }

    /**
     * Event page submenu
     *
     * @param Request $request
     */
    public function createEventSubMenu(Request $request, $event)
    {
        $menu = $this->factory->createItem('root');

        $menu->setCurrentUri($request->getRequestUri());

        $menu->addChild("О событии", array('route' => 'event_show', 'routeParameters' => array('event_slug' => $event->getSlug())));

        if ($event->getSpeakers()) {
            $menu->addChild("Докладчики", array('route' => 'event_speakers', 'routeParameters' => array('event_slug' => $event->getSlug())));
        }

        // Ссылки на страницы ивента
        // @todo можно добавить для страниц свойство "отображать в меню"
        foreach($event->getPages() as $page) {
            $menu->addChild($page->getTitle(), array('route' => 'event_page_show',
                    'routeParameters' => array('event_slug' => $event->getSlug(), 'page_slug' => $page->getSlug())));
        }

        return $menu;
    }
}