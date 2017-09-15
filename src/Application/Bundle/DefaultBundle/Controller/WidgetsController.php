<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class WidgetsController extends Controller
{
    /**
     * @param Request $request
     * @param string $position
     * @Template("ApplicationDefaultBundle:Redesign:language_switcher.html.twig")
     *
     * @return array
     */
    public function languageSwitcherAction($request, $position = 'header')
    {
        $locales = $this->getParameter('locales');
        $localesArr = [];
        foreach ($locales as $locale) {
            $localesArr[$locale] = $this->localizeRoute($request, $locale);
        }

        return ['locales' => $localesArr, 'position' => $position];
    }

    /**
     * Like review
     *
     * @Route(path="/like/{review_slug}", name="like_review",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')"))
     * @ParamConverter("review", options={"mapping": {"review_slug": "slug"}})
     * @param Review  $review
     *
     * @return JsonResponse
     */
    public function likeAction(Review $review)
    {
        $user = $this->getUser();
        if ($review->isLikedByUser($user)) {
            $review->removeLikedUser($user);
        } else {
            $review->addLikedUser($user);
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new JsonResponse(['result'=> true, 'likesCount' => $review->getLikedUsers()->count()]);
    }

    /**
     * Localize current route
     *
     * @param Request $request
     * @param string $locale
     *
     * @return string
     */
    private function localizeRoute($request, $locale)
    {
        $locales = $this->getParameter('locales');
        $path = $request->getPathInfo();
        $currentLocal = $this->getInnerSubstring($path, '/');
        if (in_array($currentLocal, $locales)) {
            $path = str_replace('/'.$currentLocal, '',$path);
        }
        $params = $request->query->all();

        return $request->getBaseUrl().'/'.$locale.$path.($params ? '?'.http_build_query($params) : '');
    }

    private function getInnerSubstring($string, $delim, $KeyNumber = 1)
    {
        $string = explode($delim, $string, 3);

        return isset($string[$KeyNumber]) ? $string[$KeyNumber] : '';
    }
}