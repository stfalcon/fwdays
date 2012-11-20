<?php
/**
 * Adding HTTP_HOST to the log messages
 * @param array $record
 *
 * @return array
 */
namespace Application\Bundle\DefaultBundle;

class GreyLogHostProcessor
{
    public function processRecord(array $record)
    {
        $record['extra']['host'] = $_SERVER['HTTP_HOST'];

        return $record;
    }
}