<?php

namespace Olveneer\DataProcessorBundle\DataClass;


use Symfony\Component\PropertyAccess\PropertyAccessor;

class Normalizer
{
    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * Normalizer constructor.
     * @param PropertyAccessor $accessor
     */
    public function __construct(PropertyAccessor $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * Calls all getters for the properties array
     * array key: Property
     * array value: value to pass to setter
     *
     * @param $object
     * @param array|object $readFrom
     */
    public function normalize($object, $readFrom)
    {
        foreach ($readFrom as $property => $value) {
            if ($this->accessor->isWritable($object, $property)) {
                $this->accessor->setValue($object, $property, $value);
            }
        }
    }
}
