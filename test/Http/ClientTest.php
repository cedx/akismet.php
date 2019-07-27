<?php declare(strict_types=1);
namespace Akismet\Http;

use function PHPUnit\Expect\{expect, it};
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
    it('should return `false` for valid comment (e.g. ham)', function() {
      expect($this->client->checkComment($this->ham))->to->be->false;
    });

    it('should return `true` for invalid comment (e.g. spam)', function() {
      expect($this->client->checkComment($this->spam))->to->be->true;
    });
  }

  /** @test Client->submitHam() */
  function testSubmitHam(): void {
    it('should complete without error', function() {
      expect(function() { $this->client->submitHam($this->ham); })->to->not->throw;
    });
  }

  /** @test Client->submitSpam() */
  function testSubmitSpam(): void {
    it('should complete without error', function() {
      expect(function() { $this->client->submitSpam($this->spam); })->to->not->throw;
    });
  }

  /** @test Client->verifyKey() */
  function testVerifyKey(): void {
    it('should return `true` for a valid API key', function() {
      expect($this->client->verifyKey())->to->be->true;
    });

    it('should return `false` for an invalid API key', function() {
      $client = (new Client('0123456789-ABCDEF', $this->client->getBlog()))->setTest($this->client->isTest());
      expect($client->verifyKey())->to->be->false;
    });
  }

  /** @before This method is called before each test. */
  protected function setUp(): void {
    $this->client = (new Client((string) getenv('AKISMET_API_KEY'), new Blog(new Uri('https://dev.belin.io/akismet.php'))))->setTest(true);

    $author = (new Author('192.168.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3851.0 Safari/537.36 Edg/77.0.223.0', 'Akismet'))
      ->setRole('administrator')
      ->setUrl(new Uri('https://dev.belin.io/akismet.php'));

    $this->ham = (new Comment($author, 'I\'m testing out the Service API.', CommentType::comment))
      ->setReferrer(new Uri('https://packagist.org/packages/cedx/akismet'));

    $author = (new Author('127.0.0.1', 'Spam Bot/6.6.6', 'viagra-test-123'))->setEmail('akismet-guaranteed-spam@example.com');
    $this->spam = new Comment($author, 'Spam!', CommentType::trackback);
  }
}
