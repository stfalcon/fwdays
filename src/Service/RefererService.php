<?php

namespace App\Service;

use App\Entity\Referer\Referer;
use App\Entity\User;
use App\Repository\Referer\RefererRepository;
use App\Service\User\UserService;
use App\Traits\EntityManagerTrait;
use App\Traits\LoggerTrait;
use App\Traits\ValidatorTrait;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * RefererService.
 */
class RefererService
{
    use EntityManagerTrait;
    use ValidatorTrait;
    use LoggerTrait;

    /** @var UserService */
    private $userService;

    /** @var RefererRepository */
    private $refererRepository;

    /**
     * @param UserService       $userService
     * @param RefererRepository $refererRepository
     */
    public function __construct(UserService $userService, RefererRepository $refererRepository)
    {
        $this->userService = $userService;
        $this->refererRepository = $refererRepository;
    }

    /**
     * @param string      $fromUrl
     * @param string      $toUrl
     * @param string|null $cookieId
     *
     * @return string|null
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addReferer(string $fromUrl, string $toUrl, ?string $cookieId): ?string
    {
        $cookieId = \htmlentities(\strip_tags(\trim($cookieId)));

        $referer = new Referer($fromUrl, $toUrl);

        $user = $this->userService->getCurrentUser(UserService::RESULT_RETURN_IF_NULL);

        if ($user instanceof User) {
            if (null !== $cookieId && !empty($cookieId)) {
                $this->checkUserCookieRelation($user, $cookieId);
            }
            $referer->setUser($user);
        } else {
            if (empty($cookieId)) {
                $cookieId = \uniqid('user_', true);
            }
            $referer->setCookieId($cookieId);
        }

        $errors = $this->validator->validate($referer);
        if (0 === \count($errors)) {
            $this->em->persist($referer);
        } else {
            $errorString = '';
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $errorString .= ' '.$error->getMessage();
            }
            $this->logger->addError('Save referer error', ['errors' => $errorString]);
        }

        $this->em->flush();

        return $cookieId;
    }

    /**
     * @param User   $user
     * @param string $cookieId
     */
    public function checkUserCookieRelation(User $user, string $cookieId): void
    {
        $referrers = $this->refererRepository->findAllWithCookieId($cookieId);
        foreach ($referrers as $referer) {
            $referer->setUser($user);
        }

        if (\count($referrers) > 0) {
            $this->em->flush();
        }
    }
}
