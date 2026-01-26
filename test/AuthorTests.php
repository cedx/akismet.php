<?php declare(strict_types=1);
namespace Belin\Akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use function PHPUnit\Framework\{assertCount, assertEquals};

/**
 * Tests the features of the {@see Author} class.
 */
#[TestDox("Author")]
final class AuthorTests extends TestCase {

	#[Test]
	#[TestDox("jsonSerialize()")]
	public function jsonSerialize(): void {
		// It should return only the IP address with a newly created instance.
		$json = new Author(ipAddress: "127.0.0.1")->jsonSerialize();
		assertCount(1, get_object_vars($json));
		assertEquals("127.0.0.1", $json->user_ip);

		// It should return a non-empty map with a initialized instance.
		$json = new Author(
			email: "contact@cedric-belin.fr",
			ipAddress: "192.168.0.1",
			name: "Cédric Belin",
			url: "https://cedric-belin.fr",
			userAgent: "Mozilla/5.0"
		)->jsonSerialize();

		assertCount(5, get_object_vars($json));
		assertEquals("Cédric Belin", $json->comment_author);
		assertEquals("contact@cedric-belin.fr", $json->comment_author_email);
		assertEquals("https://cedric-belin.fr", $json->comment_author_url);
		assertEquals("Mozilla/5.0", $json->user_agent);
		assertEquals("192.168.0.1", $json->user_ip);
	}
}
