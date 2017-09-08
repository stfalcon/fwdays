<?php

namespace Application\Bundle\DefaultBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\Translation\Translator;

/**
 * MenuBuilder Class
 */
class MenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    private $translator;
    private $locales;
    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param Translator $translator
     * @param $locales
     */
    public function __construct(FactoryInterface $factory, $translator, $locales)
    {
        $this->factory = $factory;
        $this->translator = $translator;
        $this->locales = $locales;
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

        $menu->setUri($request->getRequestUri());
        $menu->setAttribute('class', 'nav');

        $homepages = [];
        foreach ($this->locales as $locale) {
            $homepages[] = '/'.$locale.'/';
        }
        $homepages[] = '/';

        $menuManipulator = new MenuManipulator();
        if (!in_array($request->getPathInfo(), $homepages)) {
            $item = $menu->addChild($this->translator->trans('main.menu.go_head'), array('route' => 'homepage'));
            $menuManipulator->moveToFirstPosition($item);
        }
        $menu->addChild($this->translator->trans('main.menu.about'), array('route' => 'page_show', 'routeParameters' => array('slug' => 'about')));
        $menu->addChild($this->translator->trans('main.menu.events'), array('route' => 'events'));
        $menu->addChild($this->translator->trans('main.menu.contacts'), array('route' => 'page_show', 'routeParameters' => array('slug' => 'contacts')));
        $menu->addChild($this->translator->trans('main.menu.partners'), array('route' => 'partners_page'));

        return $menu;
    }

    /**
     * Event page submenu
     *
     * @param Request $request Request
     * @param Event   $event   Event
     *
     * @return \Knp\Menu\MenuItem
     */
    public function createEventSubMenu(Request $request, Event $event)
    {
        $menu = $this->factory->createItem('root');

        $menu->setUri($request->getRequestUri());

        $menu->addChild($this->translator->trans('main.menu.about_event'), array('route' => 'event_show', 'routeParameters' => array('event_slug' => $event->getSlug())));

        if ($event->getSpeakers()) {
            $menu->addChild($this->translator->trans('main.menu.speakers'), array('route' => 'event_speakers', 'routeParameters' => array('event_slug' => $event->getSlug())));
        }

        if ($event->getTickets()) {
            $menu->addChild($this->translator->trans('main.menu.participants'), array('route' => 'event_participants', 'routeParameters' => array('event_slug' => $event->getSlug())));
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
