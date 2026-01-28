<?php declare(strict_types=1);
namespace Belin\Akismet;

use PHPUnit\Framework\{Assert, TestCase};
use PHPUnit\Framework\Attributes\{Before, Test, TestDox};
use function PHPUnit\Framework\{assertEquals, assertFalse, assertTrue};

/**
 * Tests the features of the {@see Client} class.
 */
#[TestDox("Client")]
final class ClientTests extends TestCase {

	/**
	 * The client used to query the remote API.
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

	#[Test, TestDox("checkComment()")]
	public function checkComment(): void {
		// It should return `CheckResult::ham` for valid comment (e.g. ham).
		assertEquals(CheckResult::Ham, $this->client->checkComment($this->ham));

		// It should return `CheckResult::spam` for invalid comment (e.g. spam).
		$result = $this->client->checkComment($this->spam);
		assertTrue($result == CheckResult::Spam || $result == CheckResult::PervasiveSpam);
	}

	#[Test, TestDox("submitHam()")]
	public function submitHam(): void {
		try {
			$this->client->submitHam($this->ham);
			assertTrue(true); // @phpstan-ignore function.alreadyNarrowedType
		}
		catch (\Throwable $e) {
			Assert::fail($e->getMessage());
		}
	}

	#[Test, TestDox("submitSpam()")]
	public function submitSpam(): void {
		try {
			$this->client->submitSpam($this->spam);
			assertTrue(true); // @phpstan-ignore function.alreadyNarrowedType
		}
		catch (\Throwable $e) {
			Assert::fail($e->getMessage());
		}
	}

	#[Test, TestDox("verifyKey()")]
	public function verifyKey(): void {
		// It should return `true` for a valid API key.
		assertTrue($this->client->verifyKey());

		// It should return `false` for an invalid API key.
		$client = new Client(apiKey: "0123456789-ABCDEF", blog: $this->client->blog, isTest: true);
		assertFalse($client->verifyKey());
	}

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
				role: AuthorRole::Administrator->value,
				url: "https://cedric-belin.fr",
				userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0"
			),
			content: "I'm testing out the Service API.",
			referrer: "https://www.npmjs.com/package/@cedx/akismet",
			type: CommentType::Comment->value
		);

		$this->spam = new Comment(
			author: new Author(
				email: "akismet-guaranteed-spam@example.com",
				ipAddress: "127.0.0.1",
				name: "viagra-test-123",
				userAgent: "Spam Bot/6.6.6"
			),
			content: "Spam!",
			type: CommentType::BlogPost->value
		);
	}
}
