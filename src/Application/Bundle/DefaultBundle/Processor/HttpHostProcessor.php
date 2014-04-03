<?php
namespace Application\Bundle\DefaultBundle\Processor;

use Monolog\Processor\WebProcessor;

/**
 * Class HttpHostProcessor added HTTP_HOST field
 */
class HttpHostProcessor extends WebProcessor
{
    /**
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        $record = parent::__invoke($record);

        if (!isset($record['extra']['http_host'])) {
            $record['extra']['http_host'] = isset($this->serverData['SERVER_NAME']) ? $this->serverData['SERVER_NAME'] : null;
        }

        return $record;
    }
} 