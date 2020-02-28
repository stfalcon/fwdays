<?php

namespace App\Validator\Constraints\Slug;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Slug extends Regex
{
    private const SLUG_PATTERN = "/^[a-z0-9\.\-\+]+$/i";
    private const SLUG_MESSAGE = 'Поле може містити тільки eng букви, цифры, знаки -+.';

    /**
     * Slug constructor.
     */
    public function __construct()
    {
        parent::__construct(['pattern' => self::SLUG_PATTERN, 'message' => self::SLUG_MESSAGE]);
    }
}
