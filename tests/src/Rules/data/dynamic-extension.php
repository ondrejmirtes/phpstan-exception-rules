<?php declare(strict_types = 1);

namespace Pepakriz\PHPStanExceptionRules\Rules\DynamicExtension;

use Pepakriz\PHPStanExceptionRules\DynamicConstructorThrowTypeExtension;
use Pepakriz\PHPStanExceptionRules\DynamicFunctionThrowTypeExtension;
use Pepakriz\PHPStanExceptionRules\DynamicMethodThrowTypeExtension;
use Pepakriz\PHPStanExceptionRules\DynamicStaticMethodThrowTypeExtension;
use Pepakriz\PHPStanExceptionRules\UnsupportedClassException;
use Pepakriz\PHPStanExceptionRules\UnsupportedFunctionException;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use RuntimeException;

class DynamicExtension implements DynamicMethodThrowTypeExtension, DynamicStaticMethodThrowTypeExtension, DynamicConstructorThrowTypeExtension, DynamicFunctionThrowTypeExtension
{

	/**
	 * @throws UnsupportedClassException
	 * @throws UnsupportedFunctionException
	 */
	public function getThrowTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		if ($methodReflection->getDeclaringClass()->getName() !== TestClass::class) {
			throw new UnsupportedClassException();
		}

		if ($methodReflection->getName() === 'throwDynamicException') {
			return new ObjectType(RuntimeException::class);
		}

		throw new UnsupportedFunctionException();
	}

	/**
	 * @throws UnsupportedClassException
	 * @throws UnsupportedFunctionException
	 */
	public function getThrowTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
	{
		if ($methodReflection->getDeclaringClass()->getName() === TestClass::class) {
			if ($methodReflection->getName() === 'staticThrowDynamicException') {
				return new ObjectType(RuntimeException::class);
			}

			throw new UnsupportedFunctionException();
		}

		if ($methodReflection->getDeclaringClass()->getName() === BaseTestClass::class) {
			if ($methodReflection->getName() === 'test') {
				return new ObjectType(RuntimeException::class);
			}

			throw new UnsupportedFunctionException();
		}

		throw new UnsupportedClassException();
	}

	/**
	 * @throws UnsupportedClassException
	 */
	public function getThrowTypeFromConstructor(MethodReflection $methodReflection, New_ $newNode, Scope $scope): Type
	{
		if ($methodReflection->getDeclaringClass()->getName() === TestClass::class) {
			return new ObjectType(RuntimeException::class);
		}

		throw new UnsupportedClassException();
	}

	/**
	 * @throws UnsupportedFunctionException
	 */
	public function getThrowTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		if ($functionReflection->getName() !== __NAMESPACE__ . '\\throwDynamicException') {
			throw new UnsupportedFunctionException();
		}

		return new ObjectType(RuntimeException::class);
	}

}

function blankFunction(): void {

}

function throwDynamicException(): void {

}

abstract class BaseTestClass
{

	public function test()
	{

	}

}

class TestClass extends BaseTestClass
{

	public function __construct()
	{
	}

	public function test()
	{
		$this->blankMethod();
		$this->throwDynamicException(); // error: Missing @throws RuntimeException annotation

		self::blankStaticMethod();
		self::staticThrowDynamicException(); // error: Missing @throws RuntimeException annotation

		blankFunction();
		throwDynamicException(); // error: Missing @throws RuntimeException annotation

		new self(); // error: Missing @throws RuntimeException annotation

		parent::test(); // error: Missing @throws RuntimeException annotation
	}

	public function blankMethod(): void
	{

	}

	public function throwDynamicException(): void
	{

	}

	public static function blankStaticMethod(): void
	{

	}

	public static function staticThrowDynamicException(): void
	{

	}

}
