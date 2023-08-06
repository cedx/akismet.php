<?php namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{BeforeClass, Test, TestDox};
use function PHPUnit\Framework\{assertThat, equalTo, isFalse, isNull, isTrue, logicalOr};

/**
 * Tests the features of the {@see Client} class.
 */
#[TestDox("Client")]
final class ClientTest extends TestCase {

	/**
	 * The client used to query the remote API.
	 */
	private static Client $client;

	/**
	 * A comment with content marked as ham.
	 */
	private static Comment $ham;

	/**
	 * A comment with content marked as spam.
	 */
	private static Comment $spam;

	#[BeforeClass]
	static function beforeClass(): void {
		self::$client = new Client(
			apiKey: getenv("AKISMET_API_KEY") ?: "",
			blog: new Blog("https://github.com/cedx/akismet.php"),
			isTest: true
		);

		self::$ham = new Comment(
			author: new Author(
				ipAddress: "192.168.0.1",
				name: "Akismet",
				role: AuthorRole::administrator->value,
				url: "https://belin.io",
				userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0"
			),
			content: "I'm testing out the Service API.",
			referrer: "https://www.npmjs.com/package/@cedx/akismet",
			type: CommentType::comment->value
		);

		self::$spam = new Comment(
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

	#[Test]
	#[TestDox("checkComment()")]
	function checkComment(): void {
		// It should return `CheckResult::ham` for valid comment (e.g. ham).
		assertThat(self::$client->checkComment(self::$ham), equalTo(CheckResult::ham));

		// It should return `CheckResult::spam` for invalid comment (e.g. spam).
		assertThat(self::$client->checkComment(self::$spam), logicalOr(
			equalTo(CheckResult::spam),
			equalTo(CheckResult::pervasiveSpam)
		));
	}

	#[Test]
	#[TestDox("submitHam()")]
	function submitHam(): void {
		assertThat(self::$client->submitHam(self::$ham), isNull()); // @phpstan-ignore-line
	}

	#[Test]
	#[TestDox("submitSpam()")]
	function submitSpam(): void {
		assertThat(self::$client->submitSpam(self::$spam), isNull()); // @phpstan-ignore-line
	}

	#[Test]
	#[TestDox("verifyKey()")]
	function verifyKey(): void {
		// It should return `true` for a valid API key.
		assertThat(self::$client->verifyKey(), isTrue());

		// It should return `false` for an invalid API key.
		$client = new Client(apiKey: "0123456789-ABCDEF", blog: self::$client->blog, isTest: true);
		assertThat($client->verifyKey(), isFalse());
	}
}
