<?php

namespace Application\Bundle\DefaultBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * MenuBuilder Class
 */
class MenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
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
     *
     * @return \Knp\Menu\MenuItem
     */
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setCurrentUri($request->getRequestUri());
        $menu->setAttribute('class', 'nav');

        $menu->addChild('О Frameworks Days', array('route' => 'page_show', 'routeParameters' => array('slug' => 'about')));
        $menu->addChild('События', array('route' => 'events'));
        $menu->addChild('Контактная информация', array('route' => 'page_show', 'routeParameters' => array('slug' => 'contacts')));
        $menu->addChild('Спонсоры и партнеры', array('route' => 'partners_page'));

        return $menu;
    }

    /**
     * Event page top menu
     *
     * @param Request $request
     *
     * @return \Knp\Menu\MenuItem
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
     * @param Request                                   $request Request
     * @param \Stfalcon\Bundle\EventBundle\Entity\Event $event   Event
     *
     * @return \Knp\Menu\MenuItem
     */
    public function createEventSubMenu(Request $request, $event)
    {
        $menu = $this->factory->createItem('root');

        $menu->setCurrentUri($request->getRequestUri());

        $menu->addChild("О событии", array('route' => 'event_show', 'routeParameters' => array('event_slug' => $event->getSlug())));

        /** @var $event \Stfalcon\Bundle\EventBundle\Entity\Event */
        if ($event->getSpeakers()) {
            $menu->addChild("Докладчики", array('route' => 'event_speakers', 'routeParameters' => array('event_slug' => $event->getSlug())));
        }

        // ссылки на страницы ивента
        foreach ($event->getPages() as $page) {
            if ($page->isShowInMenu()) {
                $menu->addChild($page->getTitle(), array('route' => 'event_page_show',
                        'routeParameters' => array('event_slug' => $event->getSlug(), 'page_slug' => $page->getSlug())));
            }
        }

        return $menu;
    }
}
