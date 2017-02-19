<?php
/**
 * Implementation of the `akismet\test\AuthorTest` class.
 */
namespace akismet\test;

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
    // Should return a null reference with a non-object value.
    $this->assertNull(Author::fromJSON('foo'));

    // Should return an empty instance with an empty map.
    $author = Author::fromJSON([]);
    $this->assertEmpty($author->getEmail());
    $this->assertEmpty($author->getURL());

    // Should return an initialized instance with a non-empty map.
    $author = Author::fromJSON([
      'comment_author_email' => 'cedric@belin.io',
      'comment_author_url' => 'https://belin.io'
    ]);

    $this->assertEquals('cedric@belin.io', $author->getEmail());
    $this->assertEquals('https://belin.io', $author->getURL());
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return an empty map with a newly created instance.
    $data = (new Author())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    // Should return a non-empty map with a initialized instance.
    $data = (new Author('127.0.0.1'))
      ->setEmail('cedric@belin.io')
      ->setName('Cédric Belin')
      ->setURL('https://belin.io')
      ->jsonSerialize();

    $this->assertEquals('Cédric Belin', $data->comment_author);
    $this->assertEquals('cedric@belin.io', $data->comment_author_email);
    $this->assertEquals('https://belin.io', $data->comment_author_url);
    $this->assertEquals('127.0.0.1', $data->user_ip);
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $author = (string) (new Author('127.0.0.1'))
      ->setEmail('cedric@belin.io')
      ->setName('Cédric Belin')
      ->setURL('https://belin.io');

    // Should start with the class name.
    $this->assertStringStartsWith('akismet\Author {', $author);

    // Should contain the instance properties.
    $this->assertContains('"comment_author":"Cédric Belin"', $author);
    $this->assertContains('"comment_author_email":"cedric@belin.io"', $author);
    $this->assertContains('"comment_author_url":"https://belin.io"', $author);
    $this->assertContains('"user_ip":"127.0.0.1"', $author);
  }
}
