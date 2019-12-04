<?php

declare(strict_types=1);

namespace Application\Bundle\DefaultBundle\Admin\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

/**
 * SonataCRUDWithLogController.
 */
class SonataCRUDWithLogController extends CRUDController
{
    private $savedObject;

    /**
     * {@inheritdoc}
     */
    public function editAction($id = null)
    {
        $result = parent::editAction($id);

        $this->postEdit($this->getRequest());

        return $result;
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     */
    protected function postEdit(Request $request): void
    {
        if (Request::METHOD_GET === $request->getMethod()) {
            return;
        }
        $object = $this->savedObject;

        $logDir = $this->getParameter('kernel.logs_dir');
        $now = new \DateTime('now');
        $filename = \sprintf('%s.txt', $now->format('Y-m-d-H-i'));
        $dir = \sprintf('%s/Sonata/%s', $logDir, $now->format('Y-m-d'));
        if (!\is_dir($dir)) {
            \mkdir($dir, 0777, true);
        }
        $fileNameWithPath = \sprintf('%s/Sonata/%s/%s', $logDir, $now->format('Y-m-d'), $filename);
        $timeString = \sprintf('[%s]:', $now->format('Y-m-d H:i:s'));

        $content = \sprintf('%s POST EDIT %s', $timeString, $object->getId()).PHP_EOL;
        $content .= \sprintf('CLASS %s', \get_class($object)).PHP_EOL;
        $content .= \sprintf('USER %s', $this->getUser()->getEmail()).PHP_EOL;

        $content = $this->addContentFromTranslations($content, $object);

        \file_put_contents($fileNameWithPath, $content, FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    protected function preEdit(Request $request, $object)
    {
        if (Request::METHOD_GET === $request->getMethod()) {
            return null;
        }
        $this->savedObject = $object;
        $logDir = $this->getParameter('kernel.logs_dir');
        $now = new \DateTime('now');
        $filename = \sprintf('%s.txt', $now->format('Y-m-d-H-i'));
        $dir = \sprintf('%s/Sonata/%s', $logDir, $now->format('Y-m-d'));
        if (!\is_dir($dir)) {
            \mkdir($dir, 0777, true);
        }
        $fileNameWithPath = \sprintf('%s/Sonata/%s/%s', $logDir, $now->format('Y-m-d'), $filename);
        $timeString = \sprintf('[%s]:', $now->format('Y-m-d H:i:s'));

        $uniqid = $request->query->get('uniqid');

        $content = \sprintf('%s EDIT %s', $timeString, $object->getId()).PHP_EOL;
        $content .= \sprintf('CLASS %s', \get_class($object)).PHP_EOL;
        $content .= \sprintf('USER %s', $this->getUser()->getEmail()).PHP_EOL;
        $content .= 'REQUEST'.PHP_EOL;

        $content .= \sprintf('uniqid %s', $uniqid).PHP_EOL;

        $uniqidArray = $request->get($uniqid);
        if (!\is_array($uniqidArray)) {
            $content .= 'ERROR not array'.PHP_EOL;
            \file_put_contents($fileNameWithPath, $content);

            return null;
        }

        if (isset($uniqidArray['translations'])) {
            $content .= 'TRANSLATES'.PHP_EOL.PHP_EOL;
            foreach ($uniqidArray['translations'] as $translation) {
                foreach ($translation as $locale => $fields) {
                    foreach ($fields as $field => $value) {
                        $content .= \sprintf('[%s][%s]', $locale, $field).PHP_EOL;
                        $content .= '[CONTENT]'.PHP_EOL;
                        $content .= $value.PHP_EOL;
                        $content .= '[END CONTENT]'.PHP_EOL.PHP_EOL;
                    }
                }
            }
        }

        $content = $this->addContentFromTranslations($content, $object);

        \file_put_contents($fileNameWithPath, $content, FILE_APPEND);

        return null;
    }

    /**
     * @param string $content
     * @param $object
     *
     * @return string
     */
    private function addContentFromTranslations(string $content, $object): string
    {
        $content .= 'OBJECT'.PHP_EOL;

        $translations = $object->getTranslations();
        foreach ($translations as $translation) {
            $content .= \sprintf('[%s][%s]', $translation->getLocale(), $translation->getField()).PHP_EOL;
            $content .= '[CONTENT]'.PHP_EOL;
            $content .= $translation->getContent().PHP_EOL;
            $content .= '[END CONTENT]'.PHP_EOL.PHP_EOL;
        }

        return $content;
    }
}
