<?php
declare(strict_types=1);
namespace Akismet;

use function PHPUnit\Expect\{expect, fail, it};
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
    it('should return `false` for valid comment (e.g. ham)', function() {
      expect($this->client->checkComment($this->ham))->to->be->false;
    });

    it('should return `true` for invalid comment (e.g. spam)', function() {
      expect($this->client->checkComment($this->spam))->to->be->true;
    });
  }

  /**
   * @test Client::submitHam
   */
  public function testSubmitHam(): void {
    it('should complete without error', function() {
      try {
        $this->client->submitHam($this->ham);
        expect(true)->to->be->true;
      }
      catch (\Throwable $e) {
        fail($e->getMessage());
      }
    });
  }

  /**
   * @test Client::submitSpam
   */
  public function testSubmitSpam(): void {
    it('should complete without error', function() {
      try {
        $this->client->submitSpam($this->spam);
        expect(true)->to->be->true;
      }
      catch (\Throwable $e) {
        fail($e->getMessage());
      }
    });
  }

  /**
   * @test Client::verifyKey
   */
  public function testVerifyKey(): void {
    it('should return `true` for a valid API key', function() {
      expect($this->client->verifyKey())->to->be->true;
    });

    it('should return `false` for an invalid API key', function() {
      $client = (new Client('0123456789-ABCDEF', $this->client->getBlog()))->setIsTest($this->client->isTest());
      expect($client->verifyKey())->to->be->false;
    });
  }

  /**
   * Performs a common set of tasks just before each test method is called.
   */
  protected function setUp(): void {
    $this->client = (new Client(getenv('AKISMET_API_KEY'), 'https://github.com/cedx/akismet.php'))->setIsTest(true);

    $author = (new Author('192.168.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:42.0) Gecko/20100101 Firefox/42.0', 'Akismet'))
      ->setRole('administrator')
      ->setUrl('https://github.com/cedx/akismet.php');

    $this->ham = (new Comment($author, 'I\'m testing out the Service API.', CommentType::COMMENT))
      ->setReferrer('https://packagist.org/packages/cedx/akismet');

    $author = new Author('127.0.0.1', 'Spam Bot/6.6.6', 'viagra-test-123');
    $this->spam = new Comment($author, 'Spam!', CommentType::TRACKBACK);
  }
}
