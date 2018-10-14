<?php

namespace Olveneer\DataProcessorBundle\Tests;

use Olveneer\DataProcessorBundle\Api\ApiPointHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ControllerTest extends Controller
{
    /**
     * @Route(path="/test/api")
     * @param ApiPointHandler $apiPointHandler
     * @param TestProcessor $processor
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Olveneer\DataProcessorBundle\Exception\InvalidDataClassException
     */
    public function api(ApiPointHandler $apiPointHandler, TestProcessor $processor)
    {

        $options = [
          'data_class' => TestDataClass::class,
          'processor' => $processor
        ];

       return $apiPointHandler->handle($options);
    }
}