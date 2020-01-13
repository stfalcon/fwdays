<?php

namespace App\Menu;

use App\Service\User\UserService;
use Knp\Menu\FactoryInterface;
use SunCat\MobileDetectBundle\DeviceDetector\MobileDetector;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;

/**
 * MenuBuilder Class.
 */
class MenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    private $translator;

    private $locales;

    private $userService;

    private $mobileDetector;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param Translator                 $translator
     * @param array                      $locales
     * @param UserService                $userService
     * @param MobileDetector             $mobileDetector
     */
    public function __construct(FactoryInterface $factory, $translator, $locales, UserService $userService, $mobileDetector)
    {
        $this->factory = $factory;
        $this->translator = $translator;
        $this->locales = $locales;
        $this->userService = $userService;
        $this->mobileDetector = $mobileDetector;
    }

    /**
     * @param RequestStack $requestStack
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenuRedesign(RequestStack $requestStack)
    {
        $menu = $this->factory->createItem('root');

        $request = $requestStack->getCurrentRequest();
        $menu->setUri($request->getRequestUri());
        $menu->setAttribute('class', 'header-nav');

        $menu->addChild($this->translator->trans('main.menu.events'), ['route' => 'events'])
            ->setAttribute('class', 'header-nav__item');
        $menu->addChild($this->translator->trans('main.menu.contacts'), ['route' => 'page', 'routeParameters' => ['slug' => 'contacts']])
            ->setAttribute('class', 'header-nav__item');
        $menu->addChild($this->translator->trans('main.menu.about'), ['route' => 'page', 'routeParameters' => ['slug' => 'about']])
            ->setAttribute('class', 'header-nav__item');

        if ($this->userService->isUserAccess()) {
            $menu->addChild($this->translator->trans('main.menu.cabinet'), ['route' => 'cabinet'])
                ->setAttribute('class', 'header-nav__item header-nav__item--mob');
        } else {
            if ($this->mobileDetector->isMobile() || $this->mobileDetector->isTablet()) {
                $menu->addChild($this->translator->trans('menu.login'), ['route' => 'fos_user_security_login'])
                    ->setAttributes(
                        [
                            'class' => 'header-nav__item header-nav__item--mob header-nav__item--sign-in',
                        ]
                    );
            } else {
                $menu->addChild($this->translator->trans('menu.login'), ['uri' => '#'])
                    ->setAttributes(
                        [
                            'class' => 'header-nav__item header-nav__item--mob header-nav__item--sign-in',
                            'data-remodal-target' => 'modal-signin',
                        ]
                    );
            }
        }

        return $menu;
    }

    /**
     * Login menu.
     *
     * @param RequestStack $requestStack
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoginMenu(RequestStack $requestStack)
    {
        $menu = $this->factory->createItem('root');

        $request = $requestStack->getCurrentRequest();
        $menu->setUri($request->getRequestUri());

        if ($this->userService->isUserAccess()) {
            $menu->addChild($this->translator->trans('main.menu.cabinet'), ['route' => 'cabinet']);
        } else {
            $menu->addChild($this->translator->trans('menu.login'), ['uri' => '#'])
                ->setAttributes(['data-remodal-target' => 'modal-signin']);
        }

        return $menu;
    }
}
