<?php

namespace Olveneer\DataProcessorBundle\DataClass;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ProcessorDataClassParameters
 * @package App\Service\API
 * @author Douwe
 */
class ProcessorDataClassParameters
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * ProcessorDataClassParameters constructor.
     * @param EntityManagerInterface $em
     * @param UserInterface $user
     */
    public function __construct(EntityManagerInterface $em, ?UserInterface $user)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}
