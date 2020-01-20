<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Mail;
use App\Model\TranslatedMail;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * TranslatedMailService.
 */
class TranslatedMailService
{
    private $defaultLocale;

    /**
     * @param string $defaultLocale
     */
    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param Mail $mail
     *
     * @return TranslatedMail[]
     */
    public function getTranslatedMailArray(Mail $mail): array
    {
        $translations = $mail->getTranslations();
        $events = $mail->getEvents()->toArray();
        $mailId = $mail->getId();
        $translatedMails = [$this->defaultLocale => new TranslatedMail($mailId, $mail->getTitle(), $mail->getText(), $events)];
        $tmpArr = [];
        foreach ($translations as $translation) {
            $tmpArr[$translation->getLocale()][$translation->getField()] = $translation->getContent();
        }

        foreach ($tmpArr as $locale => $fields) {
            $this->assertKeyExists(['title', 'text'], $fields);
            $translatedMails[$locale] = new TranslatedMail($mailId, $fields['title'], $fields['text'], $events);
        }

        return $translatedMails;
    }

    /**
     * @param array $keysArray
     * @param array $checkArray
     */
    private function assertKeyExists(array $keysArray, array $checkArray)
    {
        foreach ($keysArray as $key) {
            if (!\array_key_exists($key, $checkArray)) {
                throw new BadRequestHttpException(\sprintf('data key %s not found', $key));
            }
        }
    }
}
