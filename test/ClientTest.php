<?php namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Before, TestDox};
use function PHPUnit\Framework\{assertThat, equalTo, isFalse, isNull, isTrue, logicalOr};

/**
 * Tests the features of the {@see Client} class.
 */
#[TestDox("Client")]
final class ClientTest extends TestCase {

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

	#[TestDox("checkComment()")]
	function testCheckComment(): void {
		// It should return `CheckResult::ham` for valid comment (e.g. ham).
		assertThat($this->client->checkComment($this->ham), equalTo(CheckResult::ham));

		// It should return `CheckResult::spam` for invalid comment (e.g. spam).
		assertThat($this->client->checkComment($this->spam), logicalOr(
			equalTo(CheckResult::spam),
			equalTo(CheckResult::pervasiveSpam)
		));
	}

	#[TestDox("submitHam()")]
	function testSubmitHam(): void {
		// It should complete without error.
		assertThat($this->client->submitHam($this->ham), isNull()); // @phpstan-ignore-line
	}

	#[TestDox("submitSpam()")]
	function testSubmitSpam(): void {
		// It should complete without error.
		assertThat($this->client->submitSpam($this->spam), isNull()); // @phpstan-ignore-line
	}

	#[TestDox("verifyKey()")]
	function testVerifyKey(): void {
		// It should return `true` for a valid API key.
		assertThat($this->client->verifyKey(), isTrue());

		// It should return `false` for an invalid API key.
		$client = new Client(apiKey: "0123456789-ABCDEF", blog: $this->client->blog, isTest: true);
		assertThat($client->verifyKey(), isFalse());
	}
}
