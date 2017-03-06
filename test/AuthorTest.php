<?php
/**
 * Implementation of the `akismet\AuthorTest` class.
 */
namespace akismet;

use akismet\{Author};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \akismet\Author
 */
class AuthorTest extends TestCase {

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    it('should return a null reference with a non-object value', function() {
      expect(Author::fromJSON('foo'))->to->be->null;
    });

    it('should return an empty instance with an empty map', function() {
      $author = Author::fromJSON([]);
      expect($author->getEmail())->to->be->empty;
      expect($author->getURL())->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $author = Author::fromJSON([
        'comment_author_email' => 'cedric@belin.io',
        'comment_author_url' => 'https://belin.io'
      ]);

      expect($author->getEmail())->to->equal('cedric@belin.io');
      expect($author->getURL())->to->equal('https://belin.io');
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Author())->jsonSerialize())->to->be->empty;
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Author('127.0.0.1'))
        ->setEmail('cedric@belin.io')
        ->setName('Cédric Belin')
        ->setURL('https://belin.io')
        ->jsonSerialize();

      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_author_email)->to->equal('cedric@belin.io');
      expect($data->comment_author_url)->to->equal('https://belin.io');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $author = (string) (new Author('127.0.0.1'))
      ->setEmail('cedric@belin.io')
      ->setName('Cédric Belin')
      ->setURL('https://belin.io');

    it('should start with the class name', function() use ($author) {
      expect($author)->to->startWith('akismet\Author {');
    });

    it('should contain the instance properties', function() use ($author) {
      expect($author)->to->contain('"comment_author":"Cédric Belin"')
        ->and->contain('"comment_author_email":"cedric@belin.io"')
        ->and->contain('"comment_author_url":"https://belin.io"')
        ->and->contain('"user_ip":"127.0.0.1"');
    });
  }
}
