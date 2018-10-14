<?php

namespace Olveneer\DataProcessorBundle\Util;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Olveneer\DataProcessorBundle\DataClass\ProcessorDataClassParameters;

/**
 * Util ControllerProcessingTrait
 * @package App\Service\API
 * @author Douwe
 */
trait ControllerProcessingTrait
{
    /**
     * @return ManagerRegistry
     */
    abstract public function getDoctrine(): ManagerRegistry;

    /**
     * Get a user from the Security Token Storage.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     *
     */
    abstract protected function getUser();

    /**
     * @param $dataClass
     * @return mixed
     */
    public function createApiData($dataClass)
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->getDoctrine()->getManager();

        $params = new ProcessorDataClassParameters($manager, $this->getUser());

        $data = new $dataClass($params);

        return $data;
    }
}
