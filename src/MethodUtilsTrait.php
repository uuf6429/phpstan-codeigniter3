<?php

namespace CodeIgniter3\PHPStan;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;

/**
 * @internal
 */
trait MethodUtilsTrait
{
	private function assertArgCount(string $class, MethodCall $method, int $minCount, int $maxCount): void
	{
		$count = count($method->args);
		if ($count < $minCount || $count > $maxCount) {
			throw new \RuntimeException(sprintf(
				'`%s::%s()` requires %s argument(s); but got %s',
				$class,
				$method->name,
				$minCount === $maxCount ? $minCount : "$minCount-$maxCount",
				$count
			));
		}
	}

	private function getArgAsString(string $class, MethodCall $method, int $index): string
	{
		if (!(($arg = $method->args[$index] ?? null) instanceof Arg)) {
			throw new \RuntimeException(sprintf(
				'Argument #%d of `%s::%s()` must be defined as a simple string literal or constant',
				$index + 1,
				$class,
				$method->name
			));
		}

		if ($arg->value instanceof String_) {
			return $arg->value->value;
		}

		if ($arg->value instanceof ClassConstFetch) {
			if ($arg->value->name instanceof Identifier && $arg->value->name->name === 'class') {
				if ($arg->value->class instanceof FullyQualified) {
					return $arg->value->class->name;
				}
				throw new \RuntimeException(sprintf(
					'Unexpected class node type encountered while resolving class constant: %s',
					get_debug_type($arg->value->class),
				));
			}
			throw new \RuntimeException(sprintf(
				'Unexpected constant node type encountered while resolving class constant: %s',
				get_debug_type($arg->value->name),
			));
		}

		if ($arg->value instanceof ConstFetch) {
			throw new \RuntimeException('Resolving constant values is currently not supported');
		}

		throw new \RuntimeException(sprintf(
			'Argument #%d of `%s::%s()` must be a string literal or constant, but %s was given',
			$index + 1,
			$class,
			$method->name,
			get_debug_type($arg->value),
		));
	}
}
