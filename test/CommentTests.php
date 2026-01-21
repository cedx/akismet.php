<?php declare(strict_types=1);
namespace Belin\Akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull, logicalNot};

/**
 * Tests the features of the {@see Comment} class.
 */
#[TestDox("Comment")]
final class CommentTests extends TestCase {

	#[Test]
	#[TestDox("jsonSerialize()")]
	function jsonSerialize(): void {
		// It should return only the author info with a newly created instance.
		$json = new Comment(author: new Author(ipAddress: "127.0.0.1"))->jsonSerialize();
		assertThat(get_object_vars($json), countOf(1));
		assertThat($json->user_ip, equalTo("127.0.0.1"));

		// It should return a non-empty map with a initialized instance.
		$json = new Comment(
			author: new Author(ipAddress: "127.0.0.1", name: "Cédric Belin", userAgent: "Doom/6.6.6"),
			content: "A user comment.",
			date: new \DateTime("2000-01-01T00:00:00.000Z"),
			referrer: "https://cedric-belin.fr",
			type: CommentType::BlogPost->value
		)->jsonSerialize();

		assertThat(get_object_vars($json), countOf(7));
		assertThat($json->comment_author, equalTo("Cédric Belin"));
		assertThat($json->comment_content, equalTo("A user comment."));
		assertThat($json->comment_date_gmt, equalTo("2000-01-01T00:00:00+00:00"));
		assertThat($json->comment_type, equalTo("blog-post"));
		assertThat($json->referrer, equalTo("https://cedric-belin.fr"));
		assertThat($json->user_agent, equalTo("Doom/6.6.6"));
		assertThat($json->user_ip, equalTo("127.0.0.1"));
	}
}
