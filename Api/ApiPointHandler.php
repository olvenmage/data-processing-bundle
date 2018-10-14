<?php

namespace Olveneer\DataProcessorBundle\Api;

use Doctrine\ORM\EntityManagerInterface;
use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassInterface;
use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassParameters;
use Olveneer\DataProcessorBundle\Processing\AbstractProcessor;
use Olveneer\DataProcessorBundle\Processing\ProcessingResponse;
use Olveneer\DataProcessorBundle\Processing\ProcessorInterface;
use Olveneer\DataProcessorBundle\Processing\ProcessorParameters;
use Olveneer\DataProcessorBundle\Exception\InvalidDataClassException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class APIPointHandler
 * @package App\Service\API
 * @author Douwe
 */
class ApiPointHandler
{
    const STATUS_INVALID_CREDENTIALS = 'INVALID CREDENTIALS';
    const STATUS_MISSING_CREDENTIALS = 'NO CREDENTIALS';
    /**
     * @var EntityAccessor
     */
    private $entityAccessor;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * APIPointHandler constructor.
     * @param EntityAccessor $entityAccessor
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityAccessor $entityAccessor,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->entityAccessor = $entityAccessor;
        $this->em = $em;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    /**
     * @param $options
     * @return JsonResponse
     * @throws InvalidDataClassException
     */
    public function handle($options) {
        $this->configureOptions($options);

        /** @var DecoderInterface|EncoderInterface $encoder */
        $encoder = $options['encoder'];

        /** @var ApiUserAuthenticatorInterface $authenticator */
        $authenticator = $options['authenticator'];

        $dataClass = $options['dataClass'];

        /** @var ProcessorInterface $processor */
        $processor = $options['processor'];

        $request = $this->requestStack->getCurrentRequest();

        $content = $request->getContent();

        $jsonData = $encoder->decode($content,  $encoder::FORMAT);

        $user = null;

        if ($authenticator instanceof ApiUserAuthenticatorInterface) {
            if (!$authenticator->isDataPresent($jsonData)) {
                return new JsonResponse([
                    'status' => self::STATUS_MISSING_CREDENTIALS,
                    'messages' => ['credentials' => $this->translator->trans($authenticator->getMissingDataMessage())]
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = $authenticator->authenticate($jsonData);

            if (!$user instanceof UserInterface) {
                return new JsonResponse([
                    'status' => self::STATUS_INVALID_CREDENTIALS,
                    'messages' => ['credentials' => $this->translator->trans($authenticator->getInvalidDataMessage())]
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $params = new ProcessorDataClassParameters($this->em, $user);

        $dataClass = new $dataClass($params);

        if (!$dataClass instanceof ProcessorDataClassInterface) {
            throw new InvalidDataClassException('Data class must implement the APIDataClassInterface.');
        }

        $this->entityAccessor->write($dataClass, $jsonData);

        /** @var ConstraintViolationList $context */
        $context = $this->validator->validate($dataClass);

        $iterator = $context->getIterator();

        if (count($iterator)) {
            $errorMessages = [];

            /** @var ConstraintViolation $context */
            foreach ($iterator as $context) {
                $errorMessages[$context->getPropertyPath()] = $context->getMessage();
            }

            return new JsonResponse([
                'status' => ProcessingResponse::STATUS_INVALID_DATA,
                'messages' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$processor instanceof AbstractProcessor) {
            $processor->beforeProcess($dataClass, $params);
        }

        $params = new ProcessorParameters($dataClass, $user);

        $response = $processor->process($params);

        $jsonResponse = new JsonResponse(json_encode($response));

        if ($response->isOk()) {
            $jsonResponse->setStatusCode(Response::HTTP_OK);
        } else {
            $jsonResponse->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $jsonResponse;
    }

    /**
     * @param $options
     */
    private function configureOptions($options)
    {
        $resolver = new OptionsResolver();

        $resolver->setRequired(['data_class', 'processor']);

        $resolver->setDefaults([
            'data_class' => null,
            'processor' => null,
            'authenticator' => null,
            'encoder' => new JsonEncoder(),
            'decoding_format' => 'json'
        ]);

        $resolver->addAllowedTypes('data_class', 'string');

        $resolver->setNormalizer('processor', function(Options $options, $processor) {
            if (!$processor instanceof ProcessorInterface) {
                throw new InvalidOptionsException('The processor must implement the ProcessorInterface');
            }

            return $processor;
        });

        $resolver->setNormalizer('data_class', function(Options $options, $dataClass) {
            $params = new ProcessorDataClassParameters($this->em, null);

            if (!class_exists($dataClass)) {
                throw new InvalidOptionsException('The data class has to be a valid class name');
            }

            $dataClassInstance = new $dataClass($params);

            if (!$dataClassInstance instanceof ProcessorDataClassInterface) {
                throw new InvalidOptionsException('The data class must implement the ProcessorDataClassInterface');
            }

            return $dataClass;
        });

        $resolver->setNormalizer('authenticator', function(Options $options, $authenticator) {
            if ($authenticator && !$authenticator instanceof AuthenticatorInterface) {
                throw new InvalidOptionsException('The processor must implement the ProcessorInterface');
            }

            return $authenticator;
        });

        $resolver->setNormalizer('encoder', function(Options $options, $encoder) {
            if (!$encoder instanceof EncoderInterface || !$encoder instanceof DecoderInterface) {
                throw new InvalidOptionsException('The encoder must implement the EncoderInterface and the DecoderInterface');
            }

            if (!defined(get_class($encoder) . '::FORMAT')) {
                throw new InvalidOptionsException('The encoder needs a FORMAT constant.');
            }

            return $encoder;
        });

        $resolver->resolve($options);
    }
}
