<?php

namespace Olveneer\DataProcessorBundle\Tests;

use Olveneer\DataProcessorBundle\Processing\AbstractProcessor;
use Olveneer\DataProcessorBundle\Processing\ProcessingResponse;
use Olveneer\DataProcessorBundle\Processing\ProcessorParameters;

class TestProcessor extends AbstractProcessor
{
    public function process(ProcessorParameters $params): ProcessingResponse
    {
        parent::process($params);

        $data = $params->getDataClass();

        return new ProcessingResponse(ProcessingResponse::STATUS_OK);
    }
}