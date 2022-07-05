<?php namespace Akismet;

use PHPUnit\Framework\{Assert, TestCase};
use function PHPUnit\Framework\{assertThat, equalTo, isFalse, isTrue, logicalOr};

/**
 * @testdox Akismet\Client
 */
class ClientTest extends TestCase {

	/**
	 * The client used to query the service database.
	 * @var Client
	 */
	private Client $client;

	/**
	 * A comment with content marked as ham.
	 * @var Comment
	 */
	private Comment $ham;

	/**
	 * A comment with content marked as spam.
	 * @var Comment
	 */
	private Comment $spam;

	/**
	 * @before This method is called before each test.
	 */
	protected function setUp(): void {
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

	/**
	 * @testdox ->checkComment()
	 */
	function testCheckComment(): void {
		// It should return `CheckResult::ham` for valid comment (e.g. ham).
		assertThat($this->client->checkComment($this->ham), equalTo(CheckResult::ham));

		// It should return `CheckResult::spam` for invalid comment (e.g. spam).
		assertThat($this->client->checkComment($this->spam), logicalOr(
			equalTo(CheckResult::spam),
			equalTo(CheckResult::pervasiveSpam)
		));
	}

	/**
	 * @testdox ->submitHam()
	 * @doesNotPerformAssertions
	 */
	function testSubmitHam(): void {
		// It should complete without error.
		try { $this->client->submitHam($this->ham); }
		catch (\Throwable $e) { Assert::fail($e->getMessage()); }
	}

	/**
	 * @testdox ->submitSpam()
	 * @doesNotPerformAssertions
	 */
	function testSubmitSpam(): void {
		// It should complete without error.
		try { $this->client->submitSpam($this->spam); }
		catch (\Throwable $e) { Assert::fail($e->getMessage()); }
	}

	/**
	 * @testdox ->verifyKey()
	 */
	function testVerifyKey(): void {
		// It should return `true` for a valid API key.
		assertThat($this->client->verifyKey(), isTrue());

		// It should return `false` for an invalid API key.
		$client = new Client(apiKey: "0123456789-ABCDEF", blog: $this->client->blog, isTest: true);
		assertThat($client->verifyKey(), isFalse());
	}
}
