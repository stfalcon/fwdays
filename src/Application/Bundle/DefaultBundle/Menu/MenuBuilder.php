<?php

namespace Application\Bundle\DefaultBundle\Menu;

use Application\Bundle\UserBundle\Entity\User;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @param $locales
     * @param $tokenService
     * @param $mobileDetector
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
     * @param Request $request
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenuRedesign(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setUri($request->getRequestUri());
        $menu->setAttribute('class', 'header-nav');

        $menu->addChild($this->translator->trans('main.menu.events'), ['route' => 'events'])
            ->setAttribute('class', 'header-nav__item');
        $menu->addChild($this->translator->trans('main.menu.contacts'), ['route' => 'contacts'])
            ->setAttribute('class', 'header-nav__item');
        $menu->addChild($this->translator->trans('main.menu.about'), ['route' => 'about'])
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
                            'class' => 'header-nav__item header-nav__item--mob',
                        ]);
            } else {
                $menu->addChild($this->translator->trans('menu.login'), ['uri' => '#'])
                    ->setAttributes(
                        [
                            'class' => 'header-nav__item header-nav__item--mob',
                            'data-remodal-target' => 'modal-signin',
                        ]);
            }
        }

        return $menu;
    }

    /**
     * Login menu.
     *
     * @param Request $request
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createLoginMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

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
