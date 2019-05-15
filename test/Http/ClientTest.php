<?php declare(strict_types=1);
namespace Akismet\Http;

use Akismet\{Author, Blog, Comment, CommentType};
use GuzzleHttp\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Akismet\Http\Client` class. */
class ClientTest extends TestCase {

  /** @var Client The client used to query the service database. */
  private $client;

  /** @var Comment A comment with content marked as ham. */
  private $ham;

  /** @var Comment A comment with content marked as spam. */
  private $spam;

  /** @test Client->checkComment() */
  function testCheckComment(): void {
    // It should return `false` for valid comment (e.g. ham).
    assertThat($this->client->checkComment($this->ham), isFalse());

    // It should return `true` for invalid comment (e.g. spam).
    assertThat($this->client->checkComment($this->spam), isTrue());
  }

  /** @test Client->submitHam() */
  function testSubmitHam(): void {
    // It should complete without error.
    try {
      $this->client->submitHam($this->ham);
      assertThat(true, isTrue());
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /** @test Client->submitSpam() */
  function testSubmitSpam(): void {
    // It should complete without error.
    try {
      $this->client->submitSpam($this->spam);
      assertThat(true, isTrue());
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /** @test Client->verifyKey() */
  function testVerifyKey(): void {
    // It should return `true` for a valid API key.
    assertThat($this->client->verifyKey(), isTrue());

    // It should return `false` for an invalid API key.
    $client = (new Client('0123456789-ABCDEF', $this->client->getBlog()))->setTest($this->client->isTest());
    assertThat($client->verifyKey(), isFalse());
  }

  /** @before This method is called before each test. */
  protected function setUp(): void {
    $this->client = (new Client((string) getenv('AKISMET_API_KEY'), new Blog(new Uri('https://dev.belin.io/akismet.php'))))->setTest(true);

    $author = (new Author('192.168.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36', 'Akismet'))
      ->setRole('administrator')
      ->setUrl(new Uri('https://dev.belin.io/akismet.php'));

    $this->ham = (new Comment($author, 'I\'m testing out the Service API.', CommentType::comment))
      ->setReferrer(new Uri('https://packagist.org/packages/cedx/akismet'));

    $author = (new Author('127.0.0.1', 'Spam Bot/6.6.6', 'viagra-test-123'))->setEmail('akismet-guaranteed-spam@example.com');
    $this->spam = new Comment($author, 'Spam!', CommentType::trackback);
  }
}
