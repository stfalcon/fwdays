<?php

namespace Application\Bundle\UserBundle\Model;

use Application\Bundle\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Class UserManager.
 */
class UserManager extends \FOS\UserBundle\Doctrine\UserManager
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * Constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param CanonicalizerInterface  $usernameCanonicalizer
     * @param CanonicalizerInterface  $emailCanonicalizer
     * @param ObjectManager           $om
     * @param string                  $class
     * @param Container               $container
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class, Container $container)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);

        $this->container = $container;
    }

    /**
     * Automatic user registration.
     *
     * @param array $participant
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function autoRegistration($participant): User
    {
        /** @var User $user */
        $user = $this->createUser();
        $user->setEmail($participant['email']);
        $user->setName($participant['name']);
        $user->setSurname($participant['surname']);
        $user->setFullname($participant['surname'].' '.$participant['name']);

        //Generate a temporary password
        $plainPassword = substr(md5(uniqid(mt_rand(), true).time()), 0, 8);

        $user->setPlainPassword($plainPassword);
        $user->setEnabled(true);

        $errors = $this->container->get('validator')->validate($user);
        if ($errors->count() > 0) {
            throw new BadCredentialsException('Bad credentials!');
        }

        $this->updateUser($user);

        $body = $this->container->get('stfalcon_event.mailer_helper')->renderTwigTemplate(
            'ApplicationUserBundle:Registration:automatically.html.twig',
            [
                'user' => $user,
                'plainPassword' => $plainPassword,
            ]
        );

        $message = $this->container->get('stfalcon_event.mailer_helper')->createMessage(
            $this->container->get('translator')->trans('registration.email.subject'),
            $user->getEmail(),
            $body
        );

        $this->container->get('mailer')->send($message);

        return $user;
    }
}
