<?php declare(strict_types=1);

namespace CodeIgniter3\PHPStanTests;

use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TypeEnrichmentTest extends TypeInferenceTestCase
{
	/**
	 * @return iterable<mixed>
	 */
	public static function typeEnrichmentDataProvider(): iterable
	{
		yield from self::gatherAssertTypes(__DIR__ . '/fixtures/config-return-types.php');
		//yield from self::gatherAssertTypes(__DIR__ . '/fixtures/model-loader.php');
	}

	#[DataProvider('typeEnrichmentDataProvider')]
	public function testTypeEnrichmentAssertions(string $assertType, string $file, mixed ...$args): void
	{
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [__DIR__ . '/codeigniter-test.neon'];
	}
}
