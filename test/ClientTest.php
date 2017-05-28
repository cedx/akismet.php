<?php
namespace akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `akismet\Client` class.
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
  public function testCheckComment() {
    it('should return `false` for valid comment (e.g. ham)', function() {
      expect($this->client->checkComment($this->ham))->to->be->false;
    });

    it('should return `true` for invalid comment (e.g. spam)', function() {
      expect($this->client->checkComment($this->spam))->to->be->true;
    });
  }

  /**
   * @test Client::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return the right values for an incorrectly configured client', function() {
      $map = (new Client('0123456789-ABCDEF'))
        ->setEndPoint('http://localhost')
        ->setUserAgent('FooBar/6.6.6')
        ->jsonSerialize();

      expect(get_object_vars($map))->to->have->lengthOf(5);
      expect($map->apiKey)->to->equal('0123456789-ABCDEF');
      expect($map->blog)->to->be->null;
      expect($map->endPoint)->to->equal('http://localhost');
      expect($map->isTest)->to->be->false;
      expect($map->userAgent)->to->equal('FooBar/6.6.6');
    });

    it('should return the right values for a properly configured client', function() {
      $map = $this->client->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(5);
      expect($map->apiKey)->to->equal(getenv('AKISMET_API_KEY'));
      expect($map->blog)->to->equal(Blog::class);
      expect($map->endPoint)->to->equal(Client::DEFAULT_ENDPOINT);
      expect($map->isTest)->to->be->true;
      expect($map->userAgent)->to->startWith('PHP/');
    });
  }

  /**
   * @test Client::submitHam
   */
  public function testSubmitHam() {
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
  public function testSubmitSpam() {
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
   * @test Client::__toString
   */
  public function testToString() {
    $value = (string) $this->client;

    it('should start with the class name', function() use ($value) {
      expect($value)->to->startWith('akismet\Client {');
    });

    it('should contain the instance properties', function() use ($value) {
      expect($value)->to->contain(sprintf('"apiKey":"%s"', getenv('AKISMET_API_KEY')))
        ->and->contain(sprintf('"blog":"%s"', str_replace('\\', '\\\\', Blog::class)))
        ->and->contain(sprintf('"endPoint":"%s"', Client::DEFAULT_ENDPOINT))
        ->and->contain('"isTest":true')
        ->and->contain('"userAgent":"PHP/');
    });
  }

  /**
   * @test Client::verifyKey
   */
  public function testVerifyKey() {
    it('should return `true` for a valid API key', function() {
      expect($this->client->verifyKey())->to->be->true;
    });

    it('should return `false` for an invalid API key', function() {
      $client = (new Client('0123456789-ABCDEF', $this->client->getBlog()))
        ->setIsTest($this->client->isTest());

      expect($client->verifyKey())->to->be->false;
    });
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
