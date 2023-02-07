<?php namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Before;
use function phpunit\expect\{expect, it};

/**
 * Tests the features of the {@see Client} class.
 */
#[TestDox('akismet\Client')]
class ClientTest extends TestCase {

	/**
	 * The client used to query the service database.
	 */
	private Client $client;

	/**
	 * A comment with content marked as ham.
	 */
	private Comment $ham;

	/**
	 * A comment with content marked as spam.
	 */
	private Comment $spam;

	/**
	 * This method is called before each test.
	 */
	#[Before]
	protected function before(): void {
		$this->client = new Client(
			apiKey: getenv("AKISMET_API_KEY") ?: "",
			blog: new Blog("https://github.com/cedx/akismet.php"),
			isTest: true
		);

		$this->ham = new Comment(
			author: new Author(
				ipAddress: "192.168.0.1",
				name: "Akismet",
				role: AuthorRole::administrator->value,
				url: "https://belin.io",
				userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36"
			),
			content: "I'm testing out the Service API.",
			referrer: "https://www.npmjs.com/package/@cedx/akismet",
			type: CommentType::comment->value
		);

		$this->spam = new Comment(
			author: new Author(
				email: "akismet-guaranteed-spam@example.com",
				ipAddress: "127.0.0.1",
				name: "viagra-test-123",
				userAgent: "Spam Bot/6.6.6"
			),
			content: "Spam!",
			type: CommentType::blogPost->value
		);
	}

	#[TestDox("->checkComment()")]
	function testCheckComment(): void {
		it("should return `CheckResult::ham` for valid comment (e.g. ham)", function() {
			expect($this->client->checkComment($this->ham))->to->equal(CheckResult::ham);
		});

		it("should return `CheckResult::spam` for invalid comment (e.g. spam)", function() {
			expect($this->client->checkComment($this->spam))->to->be->oneOf([CheckResult::spam, CheckResult::pervasiveSpam]);
		});
	}

	#[TestDox("->submitHam()")]
	function testSubmitHam(): void {
		it("should complete without error", function() {
			expect(fn() => $this->client->submitHam($this->ham))->to->not->throw;
		});
	}

	#[TestDox("->submitSpam()")]
	function testSubmitSpam(): void {
		it("should complete without error", function() {
			expect(fn() => $this->client->submitSpam($this->spam))->to->not->throw;
		});
	}

	#[TestDox("->verifyKey()")]
	function testVerifyKey(): void {
		it("should return `true` for a valid API key", function() {
			expect($this->client->verifyKey())->to->be->true;
		});

		it("should return `false` for an invalid API key", function() {
			$client = new Client(apiKey: "0123456789-ABCDEF", blog: $this->client->blog, isTest: true);
			expect($client->verifyKey())->to->be->false;
		});
	}
}
