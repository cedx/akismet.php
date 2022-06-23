<?php declare(strict_types=1);
namespace Akismet;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull};

/**
 * @testdox Akismet\Author
 */
class AuthorTest extends TestCase {

	/**
	 * @testdox ::fromJson()
	 */
	function testFromJson(): void {
		// It should return an empty instance with an empty map.
		$author = Author::fromJson(new \stdClass);
		assertThat($author->email, isEmpty());
		assertThat($author->ipAddress, isEmpty());
		assertThat($author->url, isNull());
		assertThat($author->userAgent, isEmpty());

		// It should return an initialized instance with a non-empty map.
		$author = Author::fromJson((object) [
			"comment_author_email" => "cedric@belin.io",
			"comment_author_url" => "https://belin.io",
			"user_agent" => "Mozilla/5.0",
			"user_ip" => "127.0.0.1"
		]);

		assertThat($author->email, equalTo("cedric@belin.io"));
		assertThat($author->ipAddress, equalTo("127.0.0.1"));
		assertThat((string) $author->url, equalTo("https://belin.io"));
		assertThat($author->userAgent, equalTo("Mozilla/5.0"));
	}

	/**
	 * @testdox ->jsonSerialize()
	 */
	function testJsonSerialize(): void {
		// It should return only the IP address with a newly created instance.
		$data = (new Author(ipAddress: "127.0.0.1"))->jsonSerialize();
		assertThat(get_object_vars($data), countOf(1));
		assertThat($data->user_ip, equalTo("127.0.0.1"));

		// It should return a non-empty map with a initialized instance.
		$data = (new Author(
			email: "cedric@belin.io",
			ipAddress: "192.168.0.1",
			name: "Cédric Belin",
			url: "https://belin.io",
			userAgent: "Mozilla/5.0"
		))->jsonSerialize();

		assertThat(get_object_vars($data), countOf(5));
		assertThat($data->comment_author, equalTo("Cédric Belin"));
		assertThat($data->comment_author_email, equalTo("cedric@belin.io"));
		assertThat($data->comment_author_url, equalTo("https://belin.io"));
		assertThat($data->user_agent, equalTo("Mozilla/5.0"));
		assertThat($data->user_ip, equalTo("192.168.0.1"));
	}
}
