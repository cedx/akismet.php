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
    $this->assertEquals('192.168.0.1', $author->getIpAddress());
    $this->assertEquals('Cédric Belin', $author->getName());
  }

  /**
   * Tests the `Author::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(Author::fromJSON('foo'));

    $author = Author::fromJSON([]);
    $this->assertTrue(!mb_strlen($author->getEmail()));
    $this->assertTrue(!mb_strlen($author->getURL()));

    $author = Author::fromJSON([
      'comment_author_email' => 'cedric@belin.io',
      'comment_author_url' => 'https://www.belin.io'
    ]);

    $this->assertEquals('cedric@belin.io', $author->getEmail());
    $this->assertEquals('https://www.belin.io', $author->getURL());
  }

  /**
   * Tests the `Author::toJSON()` method.
   */
  public function testToJSON() {
    $data = (new Author())->toJSON();
    $this->assertTrue(!count($data));

    $data = (new Author([
      'email' => 'cedric@belin.io',
      'ipAddress' => '127.0.0.1',
      'name' => 'Cédric Belin',
      'url' => 'https://www.belin.io'
    ]))->toJSON();

    $this->assertEquals('Cédric Belin', $data['comment_author']);
    $this->assertEquals('cedric@belin.io', $data['comment_author_email']);
    $this->assertEquals('https://www.belin.io', $data['comment_author_url']);
    $this->assertEquals('127.0.0.1', $data['user_ip']);
  }
}
