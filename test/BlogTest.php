<?php
/**
 * Implementation of the `akismet\test\BlogTest` class.
 */
namespace akismet\test;
use akismet\{Blog};

/**
 * Tests the features of the `akismet\Blog` class.
 */
class BlogTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `Blog` constructor.
   */
  public function testConstructor() {
    $blog = new Blog([
      'charset' => 'UTF-8',
      'language' => 'en',
      'url' => 'https://github.com/cedx/akismet.php'
    ]);

    $this->assertEquals('UTF-8', $blog->getCharset());
    $this->assertEquals('en', $blog->getLanguage());
    $this->assertEquals('https://github.com/cedx/akismet.php', $blog->getURL());
  }

  /**
   * Tests the `Blog::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(Blog::fromJSON('foo'));

    $blog = Blog::fromJSON([]);
    $this->assertTrue(!mb_strlen($blog->getCharset()));
    $this->assertTrue(!mb_strlen($blog->getLanguage()));
    $this->assertTrue(!mb_strlen($blog->getURL()));

    $blog = Blog::fromJSON([
      'blog' => 'https://github.com/cedx/akismet.php',
      'blog_charset' => 'UTF-8',
      'blog_lang' => 'en'
    ]);

    $this->assertEquals('UTF-8', $blog->getCharset());
    $this->assertEquals('en', $blog->getLanguage());
    $this->assertEquals('https://github.com/cedx/akismet.php', $blog->getURL());
  }

  /**
   * Tests the `Blog::toJSON()` method.
   */
  public function testToJSON() {
    $data = (new Blog())->toJSON();
    $this->assertTrue(!count($data));

    $data = (new Blog([
      'charset' => 'UTF-8',
      'language' => 'en',
      'url' => 'https://github.com/cedx/akismet.php'
    ]))->toJSON();

    $this->assertEquals('https://github.com/cedx/akismet.php', $data['blog']);
    $this->assertEquals('UTF-8', $data['blog_charset']);
    $this->assertEquals('en', $data['blog_lang']);
  }
}
