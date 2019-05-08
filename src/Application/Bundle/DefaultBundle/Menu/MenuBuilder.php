<?php

namespace Application\Bundle\DefaultBundle\Menu;

use Application\Bundle\DefaultBundle\Entity\User;
use Knp\Menu\FactoryInterface;
use SunCat\MobileDetectBundle\DeviceDetector\MobileDetector;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
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

    private $tokenService;

    private $mobileDetector;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param Translator                 $translator
     * @param array                      $locales
     * @param TokenStorageInterface      $tokenService
     * @param MobileDetector             $mobileDetector
     */
    public function __construct(FactoryInterface $factory, $translator, $locales, $tokenService, $mobileDetector)
    {
        $this->factory = $factory;
        $this->translator = $translator;
        $this->locales = $locales;
        $this->tokenService = $tokenService;
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
        $token = $this->tokenService->getToken();
        $user = $token ? $token->getUser() : null;
        if ($user instanceof User) {
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
     * @param RequestStack $request
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoginMenu(RequestStack $requestStack)
    {
        $menu = $this->factory->createItem('root');

        $request = $requestStack->getCurrentRequest();
        $menu->setUri($request->getRequestUri());

        $token = $this->tokenService->getToken();
        $user = $token ? $token->getUser() : null;
        if ($user instanceof User) {
            $menu->addChild($this->translator->trans('main.menu.cabinet'), ['route' => 'cabinet']);
        } else {
            $menu->addChild($this->translator->trans('menu.login'), ['uri' => '#'])
                ->setAttributes(['data-remodal-target' => 'modal-signin']);
        }

        return $menu;
    }
}
