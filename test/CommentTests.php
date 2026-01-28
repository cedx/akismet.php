<?php declare(strict_types=1);
namespace Belin\Akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use function PHPUnit\Framework\{assertCount, assertEquals};

/**
 * Tests the features of the {@see Comment} class.
 */
#[TestDox("Comment")]
final class CommentTests extends TestCase {

	#[Test, TestDox("jsonSerialize()")]
	public function jsonSerialize(): void {
		// It should return only the author info with a newly created instance.
		$json = new Comment(author: new Author(ipAddress: "127.0.0.1"))->jsonSerialize();
		assertCount(1, get_object_vars($json));
		assertEquals("127.0.0.1", $json->user_ip);

		// It should return a non-empty map with a initialized instance.
		$json = new Comment(
			author: new Author(ipAddress: "127.0.0.1", name: "Cédric Belin", userAgent: "Doom/6.6.6"),
			content: "A user comment.",
			date: new \DateTime("2000-01-01T00:00:00.000Z"),
			referrer: "https://cedric-belin.fr",
			type: CommentType::BlogPost->value
		)->jsonSerialize();

		assertCount(7, get_object_vars($json));
		assertEquals("Cédric Belin", $json->comment_author);
		assertEquals("A user comment.", $json->comment_content);
		assertEquals("2000-01-01T00:00:00+00:00", $json->comment_date_gmt);
		assertEquals("blog-post", $json->comment_type);
		assertEquals("https://cedric-belin.fr", $json->referrer);
		assertEquals("Doom/6.6.6", $json->user_agent);
		assertEquals("127.0.0.1", $json->user_ip);
	}
}
