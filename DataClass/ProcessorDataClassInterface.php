<?php

namespace Olveneer\DataProcessorBundle\DataClass;

/**
 * Interface APIDataClassInterface
 * @package App\Service\API
 * @author Douwe
 *
 * De taak van de data class is om de json data te valideren.
 */
interface ProcessorDataClassInterface
{
    /**
     * ProcessorDataClassInterface constructor.
     * @param ProcessorDataClassParameters $params
     */
    public function __construct(ProcessorDataClassParameters $params);
}
