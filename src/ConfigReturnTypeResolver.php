<?php

namespace CodeIgniter3\PHPStan;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Provides type information of values returned from {@see \CI_Config::item()}. Note that some restrictions apply:
 * 1. Excessive (conditional) logic in config files will likely cause problems.
 * 2. It is assumed that config sections are not used at all (and therefore all configs are merged as one).
 * 3. Configs are merged in the same order as they are loaded from the file system. Any overwritten/conflicting entries
 *    will likely not match or apply in all situations, so avoid conflicting config keys.
 *
 * @api
 */
class ConfigReturnTypeResolver implements DynamicMethodReturnTypeExtension
{
	private const array CONFIG_GETTER = [\CI_Config::class, 'item'];
	private const string CONFIG_LOADER_SCRIPT = __DIR__ . '/ci-config-loader.php';

	/**
	 * @var array<array-key, mixed>|null
	 */
	private array|null $ciConfig = null;

	public function getClass(): string
	{
		return self::CONFIG_GETTER[0];
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === self::CONFIG_GETTER[1];
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
	{
		return $scope->getTypeFromValue($this->getCodeIgniterConfigAtKeyPath($this->getConfigKeyPath($methodCall)));
	}

	/**
	 * @return list<string>
	 */
	private function getConfigKeyPath(MethodCall $methodCall): array
	{
		$argc = count($methodCall->args);
		if ($argc !== 1 && $argc !== 2) {
			throw new \RuntimeException("`CI_Config::item()` requires at least one parameter, and at most two; but got $argc");
		}

		$path = [];
		foreach ($methodCall->args as $index => $arg) {
			if (!$arg instanceof Arg || !$arg->value instanceof String_) {
				throw new \RuntimeException("Parameter #$index of `CI_Config::item()` must be string literals");
			}

			if ($arg->value->value === '') {
				continue;
			}

			$path[] = $arg->value->value;
		}

		return $path;
	}

	/**
	 * @return array<array-key, mixed>
	 */
	private function loadCodeIgniterConfig(): array
	{
		ob_start();
		passthru('php ' . escapeshellarg(self::CONFIG_LOADER_SCRIPT));
		if (($serialized = ob_get_clean()) === false) {
			throw new \RuntimeException('Failed to load config: output buffering failed');
		}
		if (!is_array($deserialized = unserialize($serialized, ['allowed_classes' => []]))) {
			throw new \RuntimeException("Failed to load config: deserialization of `$serialized` failed");
		}
		return $deserialized;
	}

	/**
	 * @return array<array-key, mixed>
	 */
	private function getCodeIgniterConfig(): array
	{
		return $this->ciConfig ?? ($this->ciConfig = $this->loadCodeIgniterConfig());
	}

	/**
	 * @param list<string> $keyPath
	 * @return mixed
	 */
	private function getCodeIgniterConfigAtKeyPath(array $keyPath)
	{
		$config = $this->getCodeIgniterConfig();

		foreach ($keyPath as $key) {
			if (!is_array($config) || !array_key_exists($key, $config)) {
				$keyPathStr = implode('.', $keyPath);
				throw new \RuntimeException("Config with key `$keyPathStr` does not exist");
			}
			$config = $config[$key];
		}

		return $config;
	}
}
