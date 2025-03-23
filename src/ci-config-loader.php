<?php

/**
 * CodeIgniter Configuration Loader
 *
 * A script to be run in isolation for loading CodeIgniter configuration files into one big area.
 * The script has three steps:
 * 1. Initialize the global environment - global constants, functions etc that config files may depend on.
 * 2. Load all config files in `%currentWorkingDirectory%/application/config`.
 * 3. Serialize and print  the config to STDOUT.
 *
 * Why do we do it in an anonymous class/object? Because config files may also depend on `$this`, and since it is a
 * one-time job, there isn't any benefit in a normal class.
 *
 * By the way, to be safe(r) and avoid conflicts, we also use long and verbose property and variables names (with the
 * exception of `$config`).
 *
 * PS: You can prepare the environment for loading the config by passing a path to a bootstrap PHP script as a CLI
 * argument.
 */

if (!isset($argv)) {
	die("This script must be called from the command line\n");
}

echo serialize(
	(new class($argv[1] ?? null) {
		/**
		 * A list of file(name)s found in the config directory but are not usable from {@see CI_Config}.
		 * @var list<string>
		 */
		private array $ciConfigLoaderExcludedConfigFiles = [
			'autoload.php',
			'constants.php',
			'database.php',
			'doctypes.php',
			'foreign_chars.php',
			'hooks.php',
			'mimes.php',
			'routes.php',
			'smileys.php',
			'user_agents.php',
		];

		/**
		 * @var array<array-key, mixed>
		 */
		private array $config = [];

		public function __construct(
			private readonly null|string $ciConfigLoaderBootstrap,
		) {
		}

		/**
		 * @return array<array-key, mixed>
		 */
		public function load(): array
		{
			if ($this->ciConfigLoaderBootstrap) {
				require_once($this->ciConfigLoaderBootstrap);
			}

			if (!defined('BASEPATH')) {
				define('BASEPATH', getcwd() ?: '');
			}
			assert(is_string(BASEPATH));
			if (!defined('APPPATH')) {
				define('APPPATH', BASEPATH . '/application');
			}

			if (!is_dir(APPPATH . '/config')) {
				throw new RuntimeException('Configuration directory not found: ' . APPPATH . '/config');
			}

			if (($ciConfigLoaderConfigFiles = glob(APPPATH . '/config/*.php')) !== false) {
				foreach ($ciConfigLoaderConfigFiles as $ciConfigLoaderConfigFile) {
					if (in_array(basename($ciConfigLoaderConfigFile), $this->ciConfigLoaderExcludedConfigFiles, true)) {
						continue;
					}

					$config = [];
					include($ciConfigLoaderConfigFile);
					$this->config = array_merge($this->config, $config);
				}
			}

			return $this->config;
		}
	})->load()
);
