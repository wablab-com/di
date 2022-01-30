<?php

namespace WabLab\DI;

use WabLab\DI\Contracts\IDI;
use WabLab\DI\Exceptions\AliasNameIsNotRegistered;
use WabLab\DI\Exceptions\LoopDependenciesCreationDetected;
use WabLab\DI\Exceptions\UndefinedClassConstructorArgumentName;

class DI implements IDI
{

    protected $map = [];
    protected $loopDetection = [];

    /**
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $alias
     * @psalm-return RealInstanceType
     */
    public function make(string $alias, array $arguments = [])
    {
        $obj = $this->getObjectByAlias($alias, $arguments);
        if($obj) {
            return $obj;
        }

        $className = $this->getClassNameByAlias($alias);
        $this->traceDependenciesLoop($className);
        $obj = $this->createObjectByClassName($className, $arguments);
        unset($this->loopDetection[$className]);
        return $obj;
    }

    public function register(string $alias, mixed $mapper)
    {
        $this->map[$alias] = $mapper;
    }

    protected function createObjectByClassName(string $className, array $arguments): object
    {
        try {
            $class = new \ReflectionClass($className);
            $newObjArguments = $this->prepareConstructorArguments($class, $arguments);
            $newObj = $class->newInstanceArgs($newObjArguments);
            return $newObj;
        } catch (\ReflectionException $exception) {
            throw new AliasNameIsNotRegistered("Alias Name: [".$className."] - Original Error Message: {$exception->getMessage()}");
        }
    }

    protected function getClassNameByAlias(string $alias): mixed
    {
        $className = $alias;
        if (!empty($this->map[$alias])) {
            $className = $this->map[$alias];
        }
        return $className;
    }

    protected function getObjectByAlias(string $alias, array $arguments): mixed
    {
        if (!empty($this->map[$alias])) {
            if($this->map[$alias] instanceof \Closure) {
                return call_user_func_array($this->map[$alias], $arguments);
            } elseif(is_object($this->map[$alias])) {
                return $this->map[$alias];
            }
        }
        return null;
    }

    protected function prepareConstructorArguments(\ReflectionClass $class, array $arguments): array
    {
        $newObjArguments = [];
        $constructor = $class->getConstructor();
        if ($constructor) {
            $parameters = $constructor->getParameters();
            /**@var $parameter \ReflectionParameter */
            foreach ($parameters as $parameter) {
                if (isset($arguments[$parameter->getName()])) {
                    $newObjArguments[$parameter->getName()] = $arguments[$parameter->getName()];
                    unset($arguments[$parameter->getName()]);
                } else {
                    $newObjArguments[$parameter->getName()] = $this->createConstructorArgumentIfNotProvided($parameter);
                }
            }
            $this->assertAllConstructorPassedArgumentsMatched($arguments);
        }
        return $newObjArguments;
    }

    protected function createConstructorArgumentIfNotProvided(\ReflectionParameter $parameter): mixed
    {
        $parameterClass = $parameter->getType();
        $objArgument = null;
        if (class_exists($parameterClass)) {
            if (!$parameter->allowsNull()) {
                $objArgument = $this->make('\\' . $parameterClass->getName());
            }
        }
        return $objArgument;
    }

    protected function assertAllConstructorPassedArgumentsMatched(array $arguments): void
    {
        if (count($arguments)) {
            throw new UndefinedClassConstructorArgumentName(json_encode(array_keys($arguments)));
        }
    }

    protected function traceDependenciesLoop($className)
    {
        if(isset($this->loopDetection[$className])) {
            $loop = array_values($this->loopDetection);
            $loop[] = $className;

            $this->loopDetection = [];
            throw new LoopDependenciesCreationDetected('Trace: '.implode(' >> ', $loop));
        }

        $this->loopDetection[$className] = $className;
    }

}