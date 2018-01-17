<?php

namespace Stfalcon\Bundle\EventBundle\Command;

use Stfalcon\Bundle\EventBundle\Entity\Speaker;
use Stfalcon\Bundle\EventBundle\Entity\Translation\SpeakerTranslation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Stfalcon\Bundle\EventBundle\Entity\Mail;

/**
 * Class StfalconMailerCommand.
 */
class StfalconChangeTranslationCommand extends ContainerAwareCommand
{
    /**
     * Set options.
     */
    protected function configure()
    {
        $this
            ->setName('stfalcon:change_translation')
            ->setDescription('Change default translation to uk');
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $repositories = [];
        $repositories[] = $em->getRepository('StfalconEventBundle:Speaker')->findAll();
        $repositories[] = $em->getRepository('StfalconEventBundle:EventPage')->findAll();
        $repositories[] = $em->getRepository('StfalconEventBundle:Event')->findAll();
        $repositories[] = $em->getRepository('StfalconSponsorBundle:Category')->findAll();
        $repositories[] = $em->getRepository('StfalconSponsorBundle:Sponsor')->findAll();
        foreach ($repositories as $repository) {
            foreach ($repository as $entity) {
                $translations = $entity->getTranslations();
                foreach ($translations as $translation) {
                    if ('uk' === $translation->getLocale()) {
                        $field = $translation->getField();
                        $action = 'set'.ucfirst($field);
                        $content = $translation->getContent();
                        if (null !== $content && is_callable([$entity, $action])) {
                            $entity->$action($content);
                        }
                    }
                }
            }
        }

        $em->flush();
    }
}
