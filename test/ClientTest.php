<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\{Uri};
use PHPUnit\Framework\{Assert, TestCase};
use function PHPUnit\Framework\{assertThat, equalTo, isFalse, isTrue, logicalOr};

/** @testdox Akismet\Client */
class ClientTest extends TestCase {

  /** @var Client The client used to query the service database. */
  private Client $client;

  /** @var Comment A comment with content marked as ham. */
  private Comment $ham;

  /** @var Comment A comment with content marked as spam. */
  private Comment $spam;

  /** @testdox ->checkComment() */
  function testCheckComment(): void {
    // It should return `CheckResult::isHam` for valid comment (e.g. ham).
    assertThat($this->client->checkComment($this->ham), equalTo(CheckResult::isHam));

    // It should return `CheckResult::isSpam` for invalid comment (e.g. spam).
    assertThat($this->client->checkComment($this->spam), logicalOr(
      equalTo(CheckResult::isSpam),
      equalTo(CheckResult::isPervasiveSpam)
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

  /** @testdox ->verifyKey() */
  function testVerifyKey(): void {
    // It should return `true` for a valid API key.
    assertThat($this->client->verifyKey(), isTrue());

    // It should return `false` for an invalid API key.
    $client = (new Client('0123456789-ABCDEF', $this->client->getBlog()))->setTest(true);
    assertThat($client->verifyKey(), isFalse());
  }

  /** @before This method is called before each test. */
  protected function setUp(): void {
    $blog = new Blog(new Uri('https://dev.belin.io/akismet.php'));
    $this->client = (new Client((string) getenv('AKISMET_API_KEY'), $blog))->setTest(true);

    $author = (new Author('192.168.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:74.0) Gecko/20100101 Firefox/74.0'))
      ->setName('Akismet')
      ->setRole('administrator')
      ->setUrl(new Uri('https://dev.belin.io/akismet.php'));

    $this->ham = (new Comment($author))
      ->setContent('I\'m testing out the Service API.')
      ->setReferrer(new Uri('https://packagist.org/packages/cedx/akismet'))
      ->setType(CommentType::comment);

    $author = (new Author('127.0.0.1', 'Spam Bot/6.6.6'))
      ->setEmail('akismet-guaranteed-spam@example.com')
      ->setName('viagra-test-123');

    $this->spam = (new Comment($author))
      ->setContent('Spam!')
      ->setType(CommentType::trackback);
  }
}
