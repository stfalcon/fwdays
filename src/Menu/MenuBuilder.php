<?php

namespace App\Menu;

use App\Entity\User;
use App\Service\User\UserService;
use App\Traits\TranslatorTrait;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use SunCat\MobileDetectBundle\DeviceDetector\MobileDetector;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * MenuBuilder.
 */
class MenuBuilder
{
    use TranslatorTrait;

    private $factory;
    private $locales;
    private $mobileDetector;
    private $userService;

    /**
     * @param FactoryInterface $factory
     * @param array            $locales
     * @param MobileDetector   $mobileDetector
     * @param UserService      $userService
     */
    public function __construct(FactoryInterface $factory, array $locales, MobileDetector $mobileDetector, UserService $userService)
    {
        $this->factory = $factory;
        $this->locales = $locales;
        $this->mobileDetector = $mobileDetector;
        $this->userService = $userService;
    }

    /**
     * @param RequestStack $requestStack
     *
     * @return ItemInterface
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

        if ($this->userService->getCurrentUser(UserService::RESULT_RETURN_IF_NULL) instanceof User) {
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
     * @return ItemInterface
     */
    public function createLoginMenu(RequestStack $requestStack)
    {
        $menu = $this->factory->createItem('root');

        $request = $requestStack->getCurrentRequest();
        $menu->setUri($request->getRequestUri());

        if ($this->userService->getCurrentUser(UserService::RESULT_RETURN_IF_NULL) instanceof User) {
            $menu->addChild($this->translator->trans('main.menu.cabinet'), ['route' => 'cabinet']);
        } else {
            $menu->addChild($this->translator->trans('menu.login'), ['uri' => '#'])
                ->setAttributes(['data-remodal-target' => 'modal-signin']);
        }

        return $menu;
    }
}
