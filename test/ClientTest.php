<?php
/**
 * Implementation of the `akismet\test\ClientTest` class.
 */
namespace akismet\test;

use akismet\{Author, Blog, Client, Comment, CommentType};
use PHPUnit\Framework\{TestCase};
use Rx\{Observable};
use Rx\Subject\{Subject};

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
    // Should return `false` for valid comment (e.g. ham).
    $this->assertFalse($this->client->checkComment($this->ham));

    // Should return `true` for invalid comment (e.g. spam).
    $this->assertTrue($this->client->checkComment($this->spam));
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return the right values for an incorrectly configured client.
    $map = (new Client('0123456789-ABCDEF'))
      ->setEndPoint('http://localhost')
      ->setUserAgent('FooBar/6.6.6')
      ->jsonSerialize();

    $this->assertCount(5, get_object_vars($map));
    $this->assertEquals('0123456789-ABCDEF', $map->apiKey);
    $this->assertNull($map->blog);
    $this->assertEquals('http://localhost', $map->endPoint);
    $this->assertFalse($map->isTest);
    $this->assertEquals('FooBar/6.6.6', $map->userAgent);

    // Should return the right values for a properly configured client.
    $map = $this->client->jsonSerialize();
    $this->assertCount(5, get_object_vars($map));
    $this->assertEquals(getenv('AKISMET_API_KEY'), $map->apiKey);
    $this->assertEquals(Blog::class, $map->blog);
    $this->assertEquals(Client::DEFAULT_ENDPOINT, $map->endPoint);
    $this->assertTrue($map->isTest);
    $this->assertStringStartsWith('PHP/', $map->userAgent);
  }

  /**
   * @test ::onRequest
   */
  public function testOnRequest() {
    // Should return an `Observable` instead of the underlying `Subject`.
    $stream = $this->client->onRequest();
    $this->assertInstanceOf(Observable::class, $stream);
    $this->assertFalse($stream instanceof Subject);
  }

  /**
   * @test ::onRequest
   */
  public function testOnResponse() {
    // Should return an `Observable` instead of the underlying `Subject`.
    $stream = $this->client->onResponse();
    $this->assertInstanceOf(Observable::class, $stream);
    $this->assertFalse($stream instanceof Subject);
  }

  /**
   * @test ::submitHam
   */
  public function testSubmitHam() {
    // Should complete without error.
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
    // Should complete without error.
    try {
      $this->client->submitSpam($this->spam);
      $this->assertTrue(true);
    }

    catch (\Throwable $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $value = (string) $this->client;

    // Should start with the class name.
    $this->assertStringStartsWith('akismet\Client {', $value);

    // Should contain the instance properties.
    $this->assertContains(sprintf('"apiKey":"%s"', getenv('AKISMET_API_KEY')), $value);
    $this->assertContains(sprintf('"blog":"%s"', str_replace('\\', '\\\\', Blog::class)), $value);
    $this->assertContains(sprintf('"endPoint":"%s"', Client::DEFAULT_ENDPOINT), $value);
    $this->assertContains('"isTest":true', $value);
    $this->assertContains('"userAgent":"PHP/', $value);
  }

  /**
   * @test ::verifyKey
   */
  public function testVerifyKey() {
    // Should return `true` for a valid API key.
    $this->assertTrue($this->client->verifyKey());

    // Should return `false` for an invalid API key.
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
