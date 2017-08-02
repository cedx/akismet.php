<?php
declare(strict_types=1);
namespace Akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};
use Psr\Http\Message\{UriInterface};

/**
 * Tests the features of the `Akismet\Author` class.
 */
class AuthorTest extends TestCase {

  /**
   * @test Author::fromJson
   */
  public function testFromJson() {
    it('should return a null reference with a non-object value', function() {
      expect(Author::fromJson('foo'))->to->be->null;
    });

    it('should return an empty instance with an empty map', function() {
      $author = Author::fromJson([]);
      expect($author->getEmail())->to->be->empty;
      expect($author->getUrl())->to->be->null;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $author = Author::fromJson([
        'comment_author_email' => 'cedric@belin.io',
        'comment_author_url' => 'https://belin.io'
      ]);

      expect($author->getEmail())->to->equal('cedric@belin.io');
      expect((string) $author->getUrl())->to->equal('https://belin.io');
    });
  }

  /**
   * @test Author::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Author)->jsonSerialize())->to->be->empty;
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Author('127.0.0.1'))
        ->setEmail('cedric@belin.io')
        ->setName('Cédric Belin')
        ->setUrl('https://belin.io')
        ->jsonSerialize();

      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_author_email)->to->equal('cedric@belin.io');
      expect($data->comment_author_url)->to->equal('https://belin.io');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });
  }

  /**
   * @test Author::setUrl
   */
  public function testSetUrl() {
    it('should return an instance of `UriInterface` for strings', function() {
      $url = (new Author)->setUrl('https://github.com/cedx/akismet.php')->getUrl();
      expect($url)->to->be->instanceOf(UriInterface::class);
      expect((string) $url)->to->equal('https://github.com/cedx/akismet.php');
    });

    it('should return a `null` reference for unsupported values', function() {
      expect((new Author)->setUrl(123)->getUrl())->to->be->null;
    });
  }

  /**
   * @test Author::__toString
   */
  public function testToString() {
    $author = (string) (new Author('127.0.0.1'))
      ->setEmail('cedric@belin.io')
      ->setName('Cédric Belin')
      ->setUrl('https://belin.io');

    it('should start with the class name', function() use ($author) {
      expect($author)->to->startWith('Akismet\Author {');
    });

    it('should contain the instance properties', function() use ($author) {
      expect($author)->to->contain('"comment_author":"Cédric Belin"')
        ->and->contain('"comment_author_email":"cedric@belin.io"')
        ->and->contain('"comment_author_url":"https://belin.io"')
        ->and->contain('"user_ip":"127.0.0.1"');
    });
  }
}
