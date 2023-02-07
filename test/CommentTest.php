<?php namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use function phpunit\expect\{expect, it};

/**
 * Tests the features of the {@see Comment} class.
 */
#[TestDox('akismet\Comment')]
final class CommentTest extends TestCase {

	#[TestDox("::fromJson()")]
	function testFromJson(): void {
		it("should return an empty instance with an empty map", function() {
			$comment = Comment::fromJson(new \stdClass);
			expect($comment->author)->to->be->null;
			expect($comment->content)->to->be->empty;
			expect($comment->date)->to->be->null;
			expect($comment->referrer)->to->be->null;
			expect($comment->type)->to->be->empty;
		});

		it("should return an initialized instance with a non-empty map", function() {
			$comment = Comment::fromJson((object) [
				"comment_author" => "Cédric Belin",
				"comment_content" => "A user comment.",
				"comment_date_gmt" => "2000-01-01T00:00:00.000Z",
				"comment_type" => "blog-post",
				"referrer" => "https://belin.io"
			]);

			/** @var Author $author */
			$author = $comment->author;
			expect($author)->to->not->be->null;
			expect($author ->name)->to->equal("Cédric Belin");

			/** @var \DateTimeInterface $date */
			$date = $comment->date;
			expect($date)->to->not->be->null;
			expect($date->format("Y"))->to->equal(2_000);

			expect($comment->content)->to->equal("A user comment.");
			expect((string) $comment->referrer)->to->equal("https://belin.io");
			expect($comment->type)->to->equal(CommentType::blogPost->value);
		});
	}

	#[TestDox("->jsonSerialize()")]
	function testJsonSerialize(): void {
		it("should return only the author info with a newly created instance", function() {
			$data = (new Comment(author: new Author(ipAddress: "127.0.0.1")))->jsonSerialize();
			expect(get_object_vars($data))->to->have->lengthOf(1);
			expect($data->user_ip)->to->equal("127.0.0.1");
		});

		it("should return a non-empty map with a initialized instance", function() {
			$data = (new Comment(
				author: new Author(ipAddress: "127.0.0.1", name: "Cédric Belin", userAgent: "Doom/6.6.6"),
				content: "A user comment.",
				date: new \DateTimeImmutable("2000-01-01T00:00:00.000Z"),
				referrer: "https://belin.io",
				type: CommentType::blogPost->value
			))->jsonSerialize();

			expect(get_object_vars($data))->to->have->lengthOf(7);
			expect($data->comment_author)->to->equal("Cédric Belin");
			expect($data->comment_content)->to->equal("A user comment.");
			expect($data->comment_date_gmt)->to->equal("2000-01-01T00:00:00+00:00");
			expect($data->comment_type)->to->equal("blog-post");
			expect($data->referrer)->to->equal("https://belin.io");
			expect($data->user_agent)->to->equal("Doom/6.6.6");
			expect($data->user_ip)->to->equal("127.0.0.1");
		});
	}
}
