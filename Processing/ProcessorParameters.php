<?php

namespace Olveneer\DataProcessorBundle\Processing;

use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ProcessorParameters
 * @package App\Service\API
 * @author Douwe
 */
class ProcessorParameters
{
    /**
     * @var ProcessorDataClassInterface
     */
    private $dataClass;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * ProcessorParameters constructor.
     * @param ProcessorDataClassInterface $dataClass
     * @param UserInterface $user
     */
    public function __construct(ProcessorDataClassInterface $dataClass, ? UserInterface $user)
    {
        $this->dataClass = $dataClass;
        $this->user = $user;
    }

    /**
     * @return ProcessorDataClassInterface
     */
    public function getDataClass(): ProcessorDataClassInterface
    {
        return $this->dataClass;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}
