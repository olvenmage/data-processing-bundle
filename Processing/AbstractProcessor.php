<?php

namespace Olveneer\DataProcessorBundle\Processing;

use Olveneer\DataProcessorBundle\DataClass\AbstractProcessorDataClass;
use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassParameters;
use Olveneer\DataProcessorBundle\Exception\InvalidDataClassException;
use Olveneer\DataProcessorBundle\DataClass\AbstractDataClassValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractProcessor
 * @package App\Service\API
 * @author Douwe
 */
abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorParameters $params
     * @return ProcessingResponse
     *
     * Wordt aangeroepen als alle api data klopt.
     * Returnt een ProcessingResponse die alle benodigde data bevat.
     * @throws InvalidDataClassException
     */
    public function process(ProcessorParameters $params): ProcessingResponse
    {
        /** @var AbstractProcessorDataClass $dataClass */
        $dataClass = $params->getDataClass();

        AbstractDataClassValidator::validate($dataClass);

        self::beforeProcess($dataClass, $dataClass->getParams());

        return new ProcessingResponse(ProcessingResponse::STATUS_OK);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return ProcessingResponse
     * @throws InvalidDataClassException
     */
    public function processForm(FormInterface $form, Request $request): ProcessingResponse
    {
        /** @var AbstractProcessorDataClass $data */
        $data = $form->getData();

        AbstractDataClassValidator::validate($data);

        $params = new ProcessorParameters($data, $data->getParams()->getUser());

        self::beforeProcess($data, $data->getParams());

        return $this->process($params);
    }

    /**
     * @param mixed $object                             The object to mutate, usually the dataclass.
     * @param ProcessorDataClassParameters $params      The parameters that contain vital parts for the data class.
     * @return mixed
     * Wordt aangeroepen voordat de dataclass door de processor heen gaat.
     */
    public static function beforeProcess(&$object, ProcessorDataClassParameters $params)
    {
        return;
    }
}
