<?php

use function PHPStan\Testing\assertType;

class CI_Config
{
	/**
	 * @return mixed
	 */
	public function item(string $name, string $index = '')
	{
		return 123;
	}
}

/**
 * @internal
 */
function testConfig(CI_Config $config): void
{
	assertType('string', $config->item('base_url'));
	assertType('false', $config->item('enable_hooks'));
	assertType('*ERROR*', $config->item('inexistent_config'));
}
