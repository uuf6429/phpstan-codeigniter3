<?php

namespace CodeIgniter3\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;

/**
 * @api
 *
 * @todo Work in progress
 */
class ModelLoaderTypeResolver implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
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
		$expr = $node->getArgs()[0]->value;
		$typeBefore = $scope->getType($expr);

		return new SpecifiedTypes();
	}
}
