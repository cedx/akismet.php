<?php declare(strict_types=1);
namespace Belin\Akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use function PHPUnit\Framework\{assertCount, assertEquals};

/**
 * Tests the features of the {@see Blog} class.
 */
#[TestDox("Blog")]
final class BlogTests extends TestCase {

	#[Test, TestDox("jsonSerialize()")]
	public function jsonSerialize(): void {
		// It should return only the blog URL with a newly created instance.
		$json = new Blog("https://github.com/cedx/akismet.php")->jsonSerialize();
		assertCount(1, get_object_vars($json));
		assertEquals("https://github.com/cedx/akismet.php", $json->blog);

		// It should return a non-empty map with a initialized instance.
		$json = new Blog(charset: "UTF-8", languages: ["en", "fr"], url: "https://github.com/cedx/akismet.php")->jsonSerialize();
		assertCount(3, get_object_vars($json));
		assertEquals("https://github.com/cedx/akismet.php", $json->blog);
		assertEquals("UTF-8", $json->blog_charset);
		assertEquals("en,fr", $json->blog_lang);
	}
}
