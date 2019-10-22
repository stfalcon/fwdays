<?php

namespace Application\Bundle\DefaultBundle\Exception;

/**
 * BadAutoRegistrationDataException.
 */
class BadAutoRegistrationDataException extends \Exception
{
    private $errorMap;

    /**
     * @param string          $message
     * @param array           $errorMap
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(string $message, array $errorMap = [], $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errorMap = $errorMap;
    }

    /**
     * @return array
     */
    public function getErrorMap(): array
    {
        return $this->errorMap;
    }
}
