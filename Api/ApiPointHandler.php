<?php

namespace Olveneer\DataProcessorBundle\Api;

use Doctrine\ORM\EntityManagerInterface;
use Olveneer\DataProcessorBundle\DataClass\Normalizer;
use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassInterface;
use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassParameters;
use Olveneer\DataProcessorBundle\Exception\InvalidDataClassException;
use Olveneer\DataProcessorBundle\Processing\AbstractProcessor;
use Olveneer\DataProcessorBundle\Processing\ProcessingResponse;
use Olveneer\DataProcessorBundle\Processing\ProcessorInterface;
use Olveneer\DataProcessorBundle\Processing\ProcessorParameters;
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
    const STATUS_INVALID_METHOD = 'INVALID METHOD';
    const STATUS_INVALID_BODY = 'INVALID BODY';

    /**
     * @var Normalizer
     */
    private $normalizer;

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
     * @var
     */
    private $cachedOptions;

    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * APIPointHandler constructor.
     * @param Normalizer $normalizer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Normalizer $normalizer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        TranslatorInterface $translator
    )
    {
        $this->normalizer = $normalizer;
        $this->em = $em;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->translator = $translator;

        $this->defaultOptions = [
            'authenticator' => null,
            'encoders' => [new JsonEncoder()],
            'decoding_format' => 'json',
            'method' => 'POST'
        ];
    }

    /**
     * @param $options
     * @return JsonResponse
     * @throws InvalidDataClassException
     */
    public function handle($options)
    {
        $resolver = $this->getOptionResolver();

        if ($this->cachedOptions) {
            $options = $this->cachedOptions;
            $this->cachedOptions = null;
        } else {
            $options = $resolver->resolve($options);
        }

        $request = $this->requestStack->getCurrentRequest();

        /** @var DecoderInterface|EncoderInterface[] $encoder */
        $encoders = $options['encoders'];

        /** @var ApiUserAuthenticatorInterface $authenticator */
        $authenticator = $options['authenticator'];

        $dataClass = $options['data_class'];

        /** @var ProcessorInterface $processor */
        $processor = $options['processor'];

        $content = $request->getContent();

        $method = $request->getMethod();

        if ($method !== $options['method']) {
            return new JsonResponse([
                'status' => self::STATUS_INVALID_METHOD,
                'messages' => ['method' => "The request was a $method request while only a {$options['method']} is allowed."]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $error = false;
        $jsonData = null;

        foreach ($encoders as $encoder) {
            $caught = false;

            try {
                $data = $encoder->decode($content, $encoder::FORMAT);
            } catch (\UnexpectedValueException $e) {
                $error = $e;
                $caught = true;
            }

            if (!$caught) {
                $error = false;
                $jsonData = $data;
                break;
            }
        }

        if (!is_array($jsonData) || $error) {
            return new JsonResponse([
                'status' => self::STATUS_INVALID_BODY,
                'messages' => [
                    'body' => "The body cannot be decoded. Did you supply the right structure?",
                    'error' => $error ?: 'Decoding does not result in a workable array.'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

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

        try {
            $this->normalizer->normalize($dataClass, $jsonData);
        } catch(\Exception $e) {
            return new JsonResponse([
                'status' => ProcessingResponse::STATUS_INVALID_DATA,
                'messages' => ['data' => 'ERROR']
            ], Response::HTTP_BAD_REQUEST);
        }

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

        $jsonResponse = new JsonResponse($response->toArray());

        if ($response->isOk()) {
            $jsonResponse->setStatusCode(Response::HTTP_OK);
        } else {
            $jsonResponse->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $jsonResponse;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $resolver = $this->getOptionResolver();

        return [
            'defined' => $resolver->getDefinedOptions(),
            'required' => $resolver->getRequiredOptions(),
            'defaults' => $this->defaultOptions
        ];
    }


    /**
     * @param $options
     * @return bool
     */
    public function areOptionsValid($options)
    {
        try {
            $this->getOptionResolver()->resolve($options);
        } catch (\Exception $e) {
            return false;
        }

        $defaultOptions = $this->defaultOptions;

        $options = array_merge($defaultOptions, $options);

        $this->cachedOptions = $options;

        return true;
    }

    /**
     * @param $options
     */
    private function getOptionResolver()
    {
        $request = $this->requestStack->getCurrentRequest();
        $resolver = new OptionsResolver();

        $resolver->setRequired(['data_class', 'processor']);

        $resolver->setDefaults($this->defaultOptions);

        $resolver->addAllowedTypes('data_class', 'string');
        $resolver->addAllowedTypes('encoders', 'array');

        $resolver->setNormalizer('processor', function (Options $options, $processor) {
            if (!$processor instanceof ProcessorInterface) {
                throw new InvalidOptionsException('The processor must implement the ProcessorInterface');
            }

            return $processor;
        });

        $resolver->setNormalizer('data_class', function (Options $options, $dataClass) {
            if (!class_exists($dataClass)) {
                throw new InvalidOptionsException('The data class has to be a valid class name');
            }

            $params = new ProcessorDataClassParameters($this->em, null);

            $dataClassInstance = new $dataClass($params);

            if (!$dataClassInstance instanceof ProcessorDataClassInterface) {
                throw new InvalidOptionsException('The data class must implement the ProcessorDataClassInterface');
            }

            return $dataClass;
        });

        $resolver->setNormalizer('authenticator', function (Options $options, $authenticator) {
            if ($authenticator && !$authenticator instanceof AuthenticatorInterface) {
                throw new InvalidOptionsException('The processor must implement the ProcessorInterface');
            }

            return $authenticator;
        });

        $resolver->setNormalizer('encoders', function (Options $options, $encoders) {
            foreach ($encoders as $encoder) {
                if (!$encoder instanceof EncoderInterface || !$encoder instanceof DecoderInterface) {
                    throw new InvalidOptionsException('The encoder must implement the EncoderInterface and the DecoderInterface');
                }

                if (!defined(get_class($encoder) . '::FORMAT')) {
                    throw new InvalidOptionsException('The encoder needs a FORMAT constant.');
                }

            }

            return $encoders;
        });

        $resolver->setNormalizer('method', function (Options $options, $method) {
            $methods = ['POST', 'GET'];

            if (!in_array($method, $methods)) {
                throw new InvalidOptionsException('The method must either be "POST" or "GET"');
            }

            return $method;
        });

        return $resolver;
    }
}
