<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\EventPage;
use App\Entity\Page;
use App\Entity\Speaker;
use App\Entity\Sponsor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $repositories = [];
        $repositories[] = $em->getRepository(Speaker::class)->findAll();
        $repositories[] = $em->getRepository(EventPage::class)->findAll();
        $repositories[] = $em->getRepository(Event::class)->findAll();
        $repositories[] = $em->getRepository(Category::class)->findAll();
        $repositories[] = $em->getRepository(Sponsor::class)->findAll();
        $repositories[] = $em->getRepository(Page::class)->findAll();
        foreach ($repositories as $repository) {
            foreach ($repository as $entity) {
                $translations = $entity->getTranslations();
                foreach ($translations as $translation) {
                    if ('uk' === $translation->getLocale()) {
                        $field = $translation->getField();
                        $action = 'set'.ucfirst($field);
                        $content = $translation->getContent();
                        if (null !== $content && \is_callable([$entity, $action])) {
                            $entity->$action($content);
                        }
                    }
                }
            }
        }

        $em->flush();
    }
}
