<?php

namespace WabLab\DI\Contracts;

interface IDI
{

    /**
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $alias
     * @psalm-return RealInstanceType
     */
    public function make(string $alias, array $arguments = []);

    public function register(string $alias, mixed $mapper);

}