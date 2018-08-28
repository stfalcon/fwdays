<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stfalcon\Bundle\EventBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WidgetsController.
 */
class WidgetsController extends Controller
{
    /**
     * @param Request $request
     * @param string  $position
     *
     * @return Response
     */
    public function languageSwitcherAction($request, $position = 'header')
    {
        $locales = $this->getParameter('locales');
        $localesArr = [];
        foreach ($locales as $locale) {
            $localesArr[$locale] = $this->localizeRoute($request, $locale);
        }

        return $this->render(
            'ApplicationDefaultBundle:Redesign:language_switcher.html.twig',
            [
                'locales' => $localesArr,
                'position' => $position,
            ]
        );
    }

    /**
     * Like review.
     *
     * @Route(path="/like/{reviewSlug}", name="like_review",
     *     methods={"POST"},
     *     options = {"expose"=true},
     *     condition="request.isXmlHttpRequest()")
     * @Security("has_role('ROLE_USER')"))
     *
     * @ParamConverter("review", options={"mapping": {"reviewSlug": "slug"}})
     *
     * @param Review $review
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

        return new JsonResponse(['result' => true, 'likesCount' => $review->getLikedUsers()->count()]);
    }

    /**
     * Localize current route.
     *
     * @param Request $request
     * @param string  $locale
     *
     * @return string
     */
    private function localizeRoute($request, $locale)
    {
        $locales = $this->getParameter('locales');
        $path = $request->getPathInfo();
        $currentLocal = $this->getInnerSubstring($path, '/');
        if (in_array($currentLocal, $locales)) {
            $path = preg_replace('/^\/'.$currentLocal.'\//', '/', $path);
        }
        $params = $request->query->all();

        return $request->getBaseUrl().'/'.$locale.$path.($params ? '?'.http_build_query($params) : '');
    }

    /**
     * Get inner sub string in position number.
     *
     * @param string $string
     * @param string $delim
     * @param int    $keyNumber
     *
     * @return string
     */
    private function getInnerSubstring($string, $delim, $keyNumber = 1)
    {
        $string = explode($delim, $string, 3);

        return isset($string[$keyNumber]) ? $string[$keyNumber] : '';
    }
}
