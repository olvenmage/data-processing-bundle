<?php

namespace Olveneer\DataProcessorBundle\Processing;

use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassParameters;

/**
 * Interface APIProcessorInterface
 * @package App\Service\API
 * @author Douwe
 *
 * De taak van de ApiProcessor is om de data af te handelen. Oftewel, er iets mee doen als alles klopt.
 */
interface ProcessorInterface
{
    /**
     * @param ProcessorParameters $params
     * @return ProcessingResponse
     *
     * Wordt aangeroepen als alle api data klopt.
     * Als deze false returnt is er iets misgegaan.
     */
    public function process(ProcessorParameters $params): ProcessingResponse;

    /**
     * @param $object
     * @param ProcessorDataClassParameters $params
     * @return mixed
     * Wordt aangeroepen voordat de dataclass door de processor heen gaat.
     */
    public static function beforeProcess(&$object, ProcessorDataClassParameters $params);
}
