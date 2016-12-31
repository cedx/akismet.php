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
      'languages' => 'en, fr',
      'url' => 'https://github.com/cedx/akismet.php'
    ]);

    $this->assertEquals('UTF-8', $blog->getCharset());
    $this->assertEquals(['en', 'fr'], $blog->getLanguages());
    $this->assertEquals('https://github.com/cedx/akismet.php', $blog->getURL());
  }

  /**
   * Tests the `Blog::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(Blog::fromJSON('foo'));

    $blog = Blog::fromJSON([]);
    $this->assertEmpty($blog->getCharset());
    $this->assertEmpty($blog->getLanguages());
    $this->assertEmpty($blog->getURL());

    $blog = Blog::fromJSON([
      'blog' => 'https://github.com/cedx/akismet.php',
      'blog_charset' => 'UTF-8',
      'blog_lang' => 'en, fr'
    ]);

    $this->assertEquals('UTF-8', $blog->getCharset());
    $this->assertEquals(['en', 'fr'], $blog->getLanguages());
    $this->assertEquals('https://github.com/cedx/akismet.php', $blog->getURL());
  }

  /**
   * Tests the `Blog::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $data = (new Blog())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    $data = (new Blog([
      'charset' => 'UTF-8',
      'languages' => 'en, fr',
      'url' => 'https://github.com/cedx/akismet.php'
    ]))->jsonSerialize();

    $this->assertEquals('https://github.com/cedx/akismet.php', $data->blog);
    $this->assertEquals('UTF-8', $data->blog_charset);
    $this->assertEquals('en,fr', $data->blog_lang);
  }
}
