<?php

namespace CodeIgniter3\PHPStan;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\ObjectShapeType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;

/**
 * @api
 */
class ModelLoaderTypeResolver implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
	use MethodUtilsTrait;

	private const array MODEL_LOADER = [\CI_Loader::class, 'model'];

	private TypeSpecifier $typeSpecifier;

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function getClass(): string
	{
		return self::MODEL_LOADER[0];
	}

	public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
	{
		return $methodReflection->getName() === self::MODEL_LOADER[1];
	}

	public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$modelName = $this->getArgAsString(\CI_Loader::class, $node, 0);
		$propertyName = count($node->args) > 1
			? $this->getArgAsString(\CI_Loader::class, $node, 1)
			: $modelName;

		// $this of current scope
		$original = $scope->getVariableType('this');

		// anonymous object with property named $propertyName of type $modelName
		$extended = new ObjectShapeType([$propertyName => new ObjectType($modelName)], []);

		$intersected = TypeCombinator::intersect($original, $extended);

		return $this->typeSpecifier->create($node->var, $intersected, $context, $scope);
	}
}
