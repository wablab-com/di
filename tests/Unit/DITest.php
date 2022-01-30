<?php
namespace WabLab\DI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WabLab\DI\DI;
use WabLab\DI\Exceptions\AliasNameIsNotRegistered;
use WabLab\DI\Exceptions\LoopDependenciesCreationDetected;
use WabLab\DI\Exceptions\UndefinedClassConstructorArgumentName;
use WabLab\DI\Tests\Classes\ClassWithComplexDependencies;
use WabLab\DI\Tests\Classes\ClassWithLoopDependencies;
use WabLab\DI\Tests\Classes\ClassWithNullableDependencies;
use WabLab\DI\Tests\Classes\ClassWithSimpleDependencies;
use WabLab\DI\Tests\Classes\EmptyArgumentsClass;

class DITest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUnregisteredButValidClassCreation_NoArguments()
    {
        $di = new DI();
        $this->assertInstanceOf(\DateTime::class, $di->make(\DateTime::class));
    }

    public function testUnregisteredButValidClassCreation_Arguments()
    {
        $di = new DI();
        $obj = $di->make(\DateTime::class, ['datetime' => '2022-01-01']);
        $this->assertEquals('2022-01-01', $obj->format('Y-m-d'));
    }

    public function testExistsClassCreation_InvalidArgumentName()
    {
        $this->expectException(UndefinedClassConstructorArgumentName::class);
        $di = new DI();
        $di->make(\DateTime::class, ['invalid_arg_name' => '2022-01-01']);
    }

    public function testClassRegistration_String()
    {
        $di = new DI();
        $di->register('exception', \Exception::class);
        $this->assertInstanceOf(\Exception::class, $di->make('exception'));
    }

    public function testClassRegistration_Object()
    {
        $di = new DI();
        $di->register('exception', new \Exception('test message'));
        $this->assertInstanceOf(\Exception::class, $di->make('exception'));
    }

    public function testClassRegistration_Closure_NoArguments()
    {
        $di = new DI();
        $di->register('exception', function () {
            return new \Exception('test message');
        });
        $this->assertInstanceOf(\Exception::class, $di->make('exception'));
    }

    public function testClassRegistration_Closure_Argument()
    {
        $di = new DI();
        $di->register('datetime', function ($datetime) {
            return new \DateTime($datetime);
        });
        $dateObj = $di->make('datetime', ['2021-01-01']);
        $this->assertEquals('2021-01-01', $dateObj->format('Y-m-d'));
    }

    public function testUnregisteredAndInvalidClassCreation()
    {
        $this->expectException(AliasNameIsNotRegistered::class);
        $di = new DI();
        $di->make('any invalid class alias');
    }

    public function testMakeNewObjectWithSimpleDependencies()
    {
        $di = new DI();
        $obj = $di->make(ClassWithSimpleDependencies::class);
        $this->assertInstanceOf(\DateTime::class, $obj->getValue());
    }

    public function testMakeNewObjectWithNullableDependencies()
    {
        $di = new DI();
        $obj = $di->make(ClassWithNullableDependencies::class);
        $this->assertNull($obj->getArg1());
        $this->assertNull($obj->getArg2());

        $obj = $di->make(ClassWithNullableDependencies::class, ['arg2' => 'test value']);
        $this->assertNull($obj->getArg1());
        $this->assertEquals('test value', $obj->getArg2());
    }

    public function testMakeNewObjectWithComplexDependencies()
    {
        $di = new DI();
        $obj = $di->make(ClassWithComplexDependencies::class);
        $this->assertInstanceOf(ClassWithNullableDependencies::class, $obj->getArg1());
        $this->assertInstanceOf(ClassWithSimpleDependencies::class, $obj->getArg2());
    }

    public function testLoopDependencies()
    {
        $this->expectException(LoopDependenciesCreationDetected::class);

        $di = new DI();
        $di->make(ClassWithLoopDependencies::class);
    }

    public function testMakeSingletonObj()
    {
        $di = new DI();
        $di->register('testSingleton', function() {
            static $obj;
            if(!isset($obj)) {
                $obj = new \stdClass();
            }
            return $obj;
        });

        $di->make('testSingleton')->id = 1000;
        $this->assertEquals(1000, $di->make('testSingleton')->id);
    }

    public function testEmptyArgumentsClass()
    {
        $di = new DI();
        $this->assertInstanceOf(EmptyArgumentsClass::class, $di->make(EmptyArgumentsClass::class));
    }

}