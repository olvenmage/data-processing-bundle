<?php

namespace Olveneer\DataProcessorBundle\DataClass;

use Olveneer\DataProcessorBundle\DataClass\AbstractProcessorDataClass;
use Olveneer\DataProcessorBundle\Exception\InvalidDataClassException;

/**
 * Class AbstractDataClassValidator
 * @package Olveneer\TwigComponentsBundle\DataClass
 */
abstract class AbstractDataClassValidator
{
    /**
     * @param null|AbstractProcessorDataClass $dataClass The data class to check
     *
     * @throws InvalidDataClassException
     */
    public static function validate(?AbstractProcessorDataClass $dataClass)
    {
        if (!$dataClass instanceof AbstractProcessorDataClass) {
            $msg = 'To use the Abstract Processor, you have to use AbstractProcessorDataClass as well.';

            throw new InvalidDataClassException($msg);
        }
    }
}
