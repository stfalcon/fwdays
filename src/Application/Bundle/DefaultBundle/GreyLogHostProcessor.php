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
        $record['extra']['host'] = isset($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST']:'console';

        return $record;
    }
}