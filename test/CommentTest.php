<?php namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull, logicalNot};

/**
 * Tests the features of the {@see Comment} class.
 */
#[TestDox("Comment")]
final class CommentTest extends TestCase {

	#[Test]
	#[TestDox("fromJson()")]
	function fromJson(): void {
		// It should return an empty instance with an empty map.
		$comment = Comment::fromJson(new \stdClass);
		assertThat($comment->author, isNull());
		assertThat($comment->content, isEmpty());
		assertThat($comment->date, isNull());
		assertThat($comment->permalink, isNull());
		assertThat($comment->postModified, isNull());
		assertThat($comment->recheckReason, isEmpty());
		assertThat($comment->referrer, isNull());
		assertThat($comment->type, isEmpty());

		// It should return an initialized instance with a non-empty map.
		$comment = Comment::fromJson((object) [
			"comment_author" => "Cédric Belin",
			"comment_content" => "A user comment.",
			"comment_date_gmt" => "2000-01-01T00:00:00.000Z",
			"comment_type" => "blog-post",
			"recheck_reason" => "The comment has been changed.",
			"referrer" => "https://belin.io",
			"user_ip" => "127.0.0.1"
		]);

		/** @var Author $author */
		$author = $comment->author;
		assertThat($author, logicalNot(isNull()));
		assertThat($author->ipAddress, equalTo("127.0.0.1"));
		assertThat($author->name, equalTo("Cédric Belin"));

		/** @var \DateTimeInterface $date */
		$date = $comment->date;
		assertThat($date, logicalNot(isNull()));
		assertThat($date->format("c"), equalTo("2000-01-01T00:00:00+00:00"));

		assertThat($comment->content, equalTo("A user comment."));
		assertThat((string) $comment->referrer, equalTo("https://belin.io"));
		assertThat($comment->recheckReason, equalTo("The comment has been changed."));
		assertThat($comment->type, equalTo(CommentType::blogPost->value));
	}

	#[Test]
	#[TestDox("jsonSerialize()")]
	function jsonSerialize(): void {
		// It should return only the author info with a newly created instance.
		$data = (new Comment(author: new Author(ipAddress: "127.0.0.1")))->jsonSerialize();
		assertThat(get_object_vars($data), countOf(1));
		assertThat($data->user_ip, equalTo("127.0.0.1"));

		// It should return a non-empty map with a initialized instance.
		$data = (new Comment(
			author: new Author(ipAddress: "127.0.0.1", name: "Cédric Belin", userAgent: "Doom/6.6.6"),
			content: "A user comment.",
			date: new \DateTimeImmutable("2000-01-01T00:00:00.000Z"),
			referrer: "https://belin.io",
			type: CommentType::blogPost->value
		))->jsonSerialize();

		assertThat(get_object_vars($data), countOf(7));
		assertThat($data->comment_author, equalTo("Cédric Belin"));
		assertThat($data->comment_content, equalTo("A user comment."));
		assertThat($data->comment_date_gmt, equalTo("2000-01-01T00:00:00+00:00"));
		assertThat($data->comment_type, equalTo("blog-post"));
		assertThat($data->referrer, equalTo("https://belin.io"));
		assertThat($data->user_agent, equalTo("Doom/6.6.6"));
		assertThat($data->user_ip, equalTo("127.0.0.1"));
	}
}
