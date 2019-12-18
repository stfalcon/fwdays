<?php

namespace App\Service\SonataBlock;

use App\Entity\User;
use App\Service\EmailHashValidationService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * EmailSubscribeBlockService.
 */
class EmailSubscribeBlockService extends AbstractBlockService
{
    /** @var EmailHashValidationService */
    private $emailHashValidationService;

    /**
     * @param EmailHashValidationService $emailHashValidationService
     */
    public function setEmailHashValidationService(EmailHashValidationService $emailHashValidationService)
    {
        $this->emailHashValidationService = $emailHashValidationService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $user = $blockContext->getSetting('user');
        $type = $blockContext->getSetting('email_type');
        $mailId = $blockContext->getSetting('mailId');

        if (!$user instanceof User) {
            throw new BadRequestHttpException();
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'email_type' => $type,
            'user' => $user,
            'mailId' => $mailId,
            'hash' => $this->emailHashValidationService->generateHash($user, $mailId),
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'AppBundle:Email:_email_subscribe.html.twig',
            'email_type' => 'unsubscribe',
            'user' => null,
            'mailId' => null,
        ]);
    }
}
