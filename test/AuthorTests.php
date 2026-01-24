<?php declare(strict_types=1);
namespace Belin\Akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull};

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
		assertThat(get_object_vars($json), countOf(1));
		assertThat($json->user_ip, equalTo("127.0.0.1"));

		// It should return a non-empty map with a initialized instance.
		$json = new Author(
			email: "contact@cedric-belin.fr",
			ipAddress: "192.168.0.1",
			name: "Cédric Belin",
			url: "https://cedric-belin.fr",
			userAgent: "Mozilla/5.0"
		)->jsonSerialize();

		assertThat(get_object_vars($json), countOf(5));
		assertThat($json->comment_author, equalTo("Cédric Belin"));
		assertThat($json->comment_author_email, equalTo("contact@cedric-belin.fr"));
		assertThat($json->comment_author_url, equalTo("https://cedric-belin.fr"));
		assertThat($json->user_agent, equalTo("Mozilla/5.0"));
		assertThat($json->user_ip, equalTo("192.168.0.1"));
	}
}
