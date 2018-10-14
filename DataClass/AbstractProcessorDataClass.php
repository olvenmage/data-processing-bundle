<?php

namespace Olveneer\DataProcessorBundle\DataClass;

/**
 * Class AbstractProcessorDataClass
 * @package App\Service\API
 * @author Douwe
 */
abstract class AbstractProcessorDataClass implements ProcessorDataClassInterface
{
    /**
     * @var ProcessorDataClassParameters
     */
    protected $params;

    /**
     * RMAData constructor.
     * @param ProcessorDataClassParameters $params
     */
    public function __construct(ProcessorDataClassParameters $params)
    {
        $this->params = $params;
    }


    /**
     * @return ProcessorDataClassParameters
     */
    public function getParams()
    {
        return $this->params;
    }
}
