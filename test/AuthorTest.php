<?php
/**
 * Implementation of the `akismet\test\AuthorTest` class.
 */
namespace akismet\test;

use akismet\{Author};
use Codeception\{Specify};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \akismet\Author
 */
class AuthorTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(Author::fromJSON('foo'));
    });

    $this->specify('should return an empty instance with an empty map', function() {
      $author = Author::fromJSON([]);
      $this->assertEmpty($author->getEmail());
      $this->assertEmpty($author->getURL());
    });

    $this->specify('should return an initialized instance with a non-empty map', function() {
      $author = Author::fromJSON([
        'comment_author_email' => 'cedric@belin.io',
        'comment_author_url' => 'https://belin.io'
      ]);

      $this->assertEquals('cedric@belin.io', $author->getEmail());
      $this->assertEquals('https://belin.io', $author->getURL());
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return an empty map with a newly created instance', function() {
      $data = (new Author())->jsonSerialize();
      $this->assertEmpty(get_object_vars($data));
    });

    $this->specify('should return a non-empty map with a initialized instance', function() {
      $data = (new Author('127.0.0.1'))
        ->setEmail('cedric@belin.io')
        ->setName('Cédric Belin')
        ->setURL('https://belin.io')
        ->jsonSerialize();

      $this->assertEquals('Cédric Belin', $data->comment_author);
      $this->assertEquals('cedric@belin.io', $data->comment_author_email);
      $this->assertEquals('https://belin.io', $data->comment_author_url);
      $this->assertEquals('127.0.0.1', $data->user_ip);
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

    $this->specify('should start with the class name', function() use ($author) {
      $this->assertStringStartsWith('akismet\Author {', $author);
    });

    $this->specify('should contain the instance properties', function() use ($author) {
      $this->assertContains('"comment_author":"Cédric Belin"', $author);
      $this->assertContains('"comment_author_email":"cedric@belin.io"', $author);
      $this->assertContains('"comment_author_url":"https://belin.io"', $author);
      $this->assertContains('"user_ip":"127.0.0.1"', $author);
    });
  }
}
