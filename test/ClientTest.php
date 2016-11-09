<?php
/**
 * Implementation of the `akismet\test\ClientTest` class.
 */
namespace akismet\test;
use akismet\{Author, Blog, Client, Comment, CommentType};

/**
 * Tests the features of the `akismet\Client` class.
 */
class ClientTest extends \PHPUnit_Framework_TestCase {

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
   * Tests the `Client` constructor.
   */
  public function testConstructor() {
    $client = new Client('0123456789ABCDEF', 'https://github.com/cedx/akismet.js');
    $this->assertEquals('0123456789ABCDEF', $client->getAPIKey());
    $this->assertInstanceOf(Blog::class , $client->getBlog());
  }

  /**
   * Tests the `Client::checkComment()` method.
   */
  public function testCheckComment() {
    $this->client->checkComment($this->ham)->subscribeCallback(
      function($response) { $this->assertFalse($response); },
      function(\Exception $e) { $this->fail($e->getMessage()); }
    );

    $this->client->checkComment($this->spam)->subscribeCallback(
      function($response) { $this->assertTrue($response); },
      function(\Exception $e) { $this->fail($e->getMessage()); }
    );
 }

  /**
   * Tests the `Client::submitHam()` method.
   */
  public function testSubmitHam() {
    $this->client->submitHam($this->ham)->subscribeCallback(
      function() { $this->assertTrue(true); },
      function(\Exception $e) { $this->fail($e->getMessage()); }
    );
  }

  /**
   * Tests the `Client::submitSpam()` method.
   */
  public function testSubmitSpam() {
    $this->client->submitSpam($this->spam)->subscribeCallback(
      function() { $this->assertTrue(true); },
      function(\Exception $e) { $this->fail($e->getMessage()); }
    );
  }

  /**
   * Tests the `Client::verifyKey()` method.
   */
  public function testVerifyKey() {
    $this->client->verifyKey()->subscribeCallback(
      function($response) { $this->assertTrue($response); },
      function(\Exception $e) { $this->fail($e->getMessage()); }
    );

    $client = new Client('viagra-test-123', $this->client->getBlog(), ['isTest' => $this->client->isTest()]);
    $client->verifyKey()->subscribeCallback(
      function($response) { $this->assertFalse($response); },
      function(\Exception $e) { $this->fail($e->getMessage()); }
    );
  }

  /**
   * Performs a common set of tasks just before each test method is called.
   */
  protected function setUp() {
    $this->client = new Client(getenv('AKISMET_API_KEY'), 'https://github.com/cedx/akismet.php', ['isTest' => true]);

    $this->ham = new Comment([
      'author' => new Author([
        'ipAddress' => '192.168.0.1',
        'name' => 'Akismet for PHP',
        'url' => 'https://github.com/cedx/akismet.php',
        'userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:42.0) Gecko/20100101 Firefox/42.0'
      ]),
      'content' => 'I\'m testing out the Service API.',
      'referrer' => 'https://packagist.org/packages/cedx/akismet',
      'type' => CommentType::COMMENT
    ]);

    $this->spam = new Comment([
      'author' => new Author([
        'ipAddress' => '127.0.0.1',
        'name' => 'viagra-test-123',
        'userAgent' => 'Spam Bot/6.6.6'
      ]),
      'content' => 'Spam!',
      'type' => CommentType::TRACKBACK
    ]);
  }
}
