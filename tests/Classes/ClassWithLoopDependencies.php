<?php

namespace WabLab\DI\Tests\Classes;

class ClassWithLoopDependencies
{
    public function __construct(ClassWithLoopDependenciesStage1 $arg)
    {
    }
}

class ClassWithLoopDependenciesStage1
{
    public function __construct(ClassWithLoopDependenciesStage2 $arg)
    {
    }
}

class ClassWithLoopDependenciesStage2
{
    public function __construct(ClassWithLoopDependenciesStage3 $arg)
    {
    }
}


class ClassWithLoopDependenciesStage3
{
    public function __construct(ClassWithLoopDependencies $arg)
    {
    }
}