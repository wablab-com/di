<?php

namespace WabLab\DI\Tests\Classes;

class ClassWithComplexDependencies
{

    private ClassWithNullableDependencies $arg1;
    private ClassWithSimpleDependencies $arg2;

    public function __construct(ClassWithNullableDependencies $arg1, ClassWithSimpleDependencies $arg2)
    {

        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }

    /**
     * @return ClassWithNullableDependencies
     */
    public function getArg1(): ClassWithNullableDependencies
    {
        return $this->arg1;
    }

    /**
     * @return ClassWithSimpleDependencies
     */
    public function getArg2(): ClassWithSimpleDependencies
    {
        return $this->arg2;
    }

}