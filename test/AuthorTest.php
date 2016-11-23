<?php
/**
 * Implementation of the `akismet\test\AuthorTest` class.
 */
namespace akismet\test;
use akismet\{Author};

/**
 * Tests the features of the `akismet\Author` class.
 */
class AuthorTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `Author` constructor.
   */
  public function testConstructor() {
    $author = new Author([
      'email' => 'cedric@belin.io',
      'ipAddress' => '192.168.0.1',
      'name' => 'Cédric Belin'
    ]);

    $this->assertEquals('cedric@belin.io', $author->getEmail());
    $this->assertEquals('192.168.0.1', $author->getIPAddress());
    $this->assertEquals('Cédric Belin', $author->getName());
  }

  /**
   * Tests the `Author::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(Author::fromJSON('foo'));

    $author = Author::fromJSON([]);
    $this->assertEquals(0, mb_strlen($author->getEmail()));
    $this->assertEquals(0, mb_strlen($author->getURL()));

    $author = Author::fromJSON([
      'comment_author_email' => 'cedric@belin.io',
      'comment_author_url' => 'https://belin.io'
    ]);

    $this->assertEquals('cedric@belin.io', $author->getEmail());
    $this->assertEquals('https://belin.io', $author->getURL());
  }

  /**
   * Tests the `Author::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $data = (new Author())->jsonSerialize();
    $this->assertEquals(0, count((array) $data));

    $data = (new Author([
      'email' => 'cedric@belin.io',
      'ipAddress' => '127.0.0.1',
      'name' => 'Cédric Belin',
      'url' => 'https://belin.io'
    ]))->jsonSerialize();

    $this->assertEquals('Cédric Belin', $data->comment_author);
    $this->assertEquals('cedric@belin.io', $data->comment_author_email);
    $this->assertEquals('https://belin.io', $data->comment_author_url);
    $this->assertEquals('127.0.0.1', $data->user_ip);
  }
}
