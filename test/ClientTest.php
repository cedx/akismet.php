<?php
/**
 * Implementation of the `akismet\test\ClientTest` class.
 */
namespace akismet\test;

use akismet\{Author, Blog, Client, Comment, CommentType};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \akismet\Client
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
   * @test ::checkComment
   */
  public function testCheckComment() {
    $this->assertFalse($this->client->checkComment($this->ham));
    $this->assertTrue($this->client->checkComment($this->spam));
  }

  /**
   * @test ::submitHam
   */
  public function testSubmitHam() {
    try {
      $this->client->submitHam($this->ham);
      $this->assertTrue(true);
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @test ::submitSpam
   */
  public function testSubmitSpam() {
    try {
      $this->client->submitSpam($this->spam);
      $this->assertTrue(true);
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @test ::verifyKey
   */
  public function testVerifyKey() {
    $this->assertTrue($this->client->verifyKey());

    $client = (new Client('0123456789-ABCDEF', $this->client->getBlog()))
      ->setIsTest($this->client->isTest());

    $this->assertFalse($client->verifyKey());
  }

  /**
   * Performs a common set of tasks just before each test method is called.
   */
  protected function setUp() {
    $this->client = (new Client(getenv('AKISMET_API_KEY'), 'https://github.com/cedx/akismet.php'))
      ->setIsTest(true);

    $author = (new Author('192.168.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:42.0) Gecko/20100101 Firefox/42.0'))
      ->setName('Akismet for PHP')
      ->setRole('administrator')
      ->setURL('https://github.com/cedx/akismet.php');

    $this->ham = (new Comment($author, 'I\'m testing out the Service API.', CommentType::COMMENT))
      ->setReferrer('https://packagist.org/packages/cedx/akismet');

    $author = (new Author('127.0.0.1', 'Spam Bot/6.6.6'))->setName('viagra-test-123');
    $this->spam = new Comment($author, 'Spam!', CommentType::TRACKBACK);
  }
}
