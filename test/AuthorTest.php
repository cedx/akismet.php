<?php namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use function phpunit\expect\{expect, it};

/**
 * Tests the features of the {@see Author} class.
 */
#[TestDox('akismet\Author')]
final class AuthorTest extends TestCase {

	#[TestDox("::fromJson()")]
	function testFromJson(): void {
		it("should return an empty instance with an empty map", function() {
			$author = Author::fromJson(new \stdClass);
			expect($author->email)->to->be->empty;
			expect($author->ipAddress)->to->be->empty;
			expect($author->url)->to->be->null;
			expect($author->userAgent)->to->be->empty;
		});

		it("should return an initialized instance with a non-empty map", function() {
			$author = Author::fromJson((object) [
				"comment_author_email" => "cedric@belin.io",
				"comment_author_url" => "https://belin.io",
				"user_agent" => "Mozilla/5.0",
				"user_ip" => "127.0.0.1"
			]);

			expect($author->email)->to->equal("cedric@belin.io");
			expect($author->ipAddress)->to->equal("127.0.0.1");
			expect((string) $author->url)->to->equal("https://belin.io");
			expect($author->userAgent)->to->equal("Mozilla/5.0");
		});
	}

	#[TestDox("->jsonSerialize()")]
	function testJsonSerialize(): void {
		it("should return only the IP address with a newly created instance", function() {
			$data = (new Author(ipAddress: "127.0.0.1"))->jsonSerialize();
			expect(get_object_vars($data))->to->have->lengthOf(1);
			expect($data->user_ip)->to->equal("127.0.0.1");
		});

		it("should return a non-empty map with a initialized instance", function() {
			$data = (new Author(
				email: "cedric@belin.io",
				ipAddress: "192.168.0.1",
				name: "Cédric Belin",
				url: "https://belin.io",
				userAgent: "Mozilla/5.0"
			))->jsonSerialize();

			expect(get_object_vars($data))->to->have->lengthOf(5);
			expect($data->comment_author)->to->equal("Cédric Belin");
			expect($data->comment_author_email)->to->equal("cedric@belin.io");
			expect($data->comment_author_url)->to->equal("https://belin.io");
			expect($data->user_agent)->to->equal("Mozilla/5.0");
			expect($data->user_ip)->to->equal("192.168.0.1");
		});
	}
}
