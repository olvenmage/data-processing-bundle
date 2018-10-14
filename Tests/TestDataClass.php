<?php

namespace Olveneer\DataProcessorBundle\Tests;


use Olveneer\DataProcessorBundle\DataClass\AbstractProcessorDataClass;
use Symfony\Component\Validator\Constraints as Assert;

class TestDataClass extends AbstractProcessorDataClass
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $lastName;
}