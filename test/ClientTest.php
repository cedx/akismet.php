<?php
declare(strict_types=1);
namespace Akismet;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Akismet\Client` class.
 */
class ClientTest extends TestCase {

  /**
   * @var Client The client used to query the service database.
   */
  private $client;

  /**
   * @var Comment A comment with content marked as ham.
   */
  private $ham;

  /**
   * @var Comment A comment with content marked as spam.
   */
  private $spam;

  /**
   * @test Client::checkComment
   */
  public function testCheckComment(): void {
    // It should return `false` for valid comment (e.g. ham).
    assertThat($this->client->checkComment($this->ham), isFalse());

    // It should return `true` for invalid comment (e.g. spam).
    assertThat($this->client->checkComment($this->spam), isTrue());
  }

  /**
   * @test Client::submitHam
   */
  public function testSubmitHam(): void {
    // It should complete without error.
    try {
      $this->client->submitHam($this->ham);
      assertThat(true, isTrue());
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @test Client::submitSpam
   */
  public function testSubmitSpam(): void {
    // It should complete without error.
    try {
      $this->client->submitSpam($this->spam);
      assertThat(true, isTrue());
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @test Client::verifyKey
   */
  public function testVerifyKey(): void {
    // It should return `true` for a valid API key.
    assertThat($this->client->verifyKey(), isTrue());

    // It should return `false` for an invalid API key.
    $client = (new Client('0123456789-ABCDEF', $this->client->getBlog()))->setIsTest($this->client->isTest());
    assertThat($client->verifyKey(), isFalse());
  }

  /**
   * Performs a common set of tasks just before each test method is called.
   */
  protected function setUp(): void {
    $this->client = (new Client(getenv('AKISMET_API_KEY'), 'https://dev.belin.io/akismet.php'))->setIsTest(true);

    $author = (new Author('192.168.0.1', 'Mozilla/5.0 (X11; Linux x86_64) Chrome/66.0.3359.1390', 'Akismet'))
      ->setRole('administrator')
      ->setUrl('https://dev.belin.io/akismet.php');

    $this->ham = (new Comment($author, 'I\'m testing out the Service API.', CommentType::COMMENT))
      ->setReferrer('https://packagist.org/packages/cedx/akismet');

    $author = (new Author('127.0.0.1', 'Spam Bot/6.6.6', 'viagra-test-123'))->setEmail('akismet-guaranteed-spam@example.com');
    $this->spam = new Comment($author, 'Spam!', CommentType::TRACKBACK);
  }
}
