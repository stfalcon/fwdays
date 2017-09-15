<?php

namespace Application\Bundle\DefaultBundle\Menu;

use Application\Bundle\UserBundle\Entity\User;
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

    private $tokenService;
    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param Translator $translator
     * @param $locales
     * @param $tokenService
     */
    public function __construct(FactoryInterface $factory, $translator, $locales, $tokenService)
    {
        $this->factory = $factory;
        $this->translator = $translator;
        $this->locales = $locales;
        $this->tokenService = $tokenService;

    }

    /**
     * @param Request $request
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenuRedesign(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setUri($request->getRequestUri());
        $menu->setAttribute('class', 'header-nav');

        $menu->addChild($this->translator->trans('main.menu.events'), ['route' => 'events', ])
            ->setAttribute('class','header-nav__item');
        $menu->addChild($this->translator->trans('main.menu.contacts'), ['route' => 'contacts'])
            ->setAttribute('class','header-nav__item');
        $menu->addChild($this->translator->trans('main.menu.about'), ['route' => 'show_page', 'routeParameters' => ['slug' => 'about']])
            ->setAttribute('class','header-nav__item');

        return $menu;
    }

    /**
     * Login menu
     *
     * @param Request $request
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoginMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setUri($request->getRequestUri());

        $token = $this->tokenService->getToken();
        $user = $token ? $token->getUser() : null;
        if ($user instanceof User) {
            $menu->addChild($this->translator->trans( 'main.menu.cabinet'), ['route' => 'cabinet']);
        } else {
            $menu->addChild($this->translator->trans( 'menu.login'), ['uri' => '#modal-signin']);
        }

        return $menu;
    }
}
