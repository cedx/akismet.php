<?php
/**
 * Implementation of the `akismet\test\BlogTest` class.
 */
namespace akismet\test;

use akismet\{Blog};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \akismet\Blog
 */
class BlogTest extends TestCase {

  /**
   * @test ::fromJSON
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
    $this->assertEquals(['en', 'fr'], $blog->getLanguages()->getArrayCopy());
    $this->assertEquals('https://github.com/cedx/akismet.php', $blog->getURL());
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $data = (new Blog())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    $data = (new Blog('https://github.com/cedx/akismet.php', ['en', 'fr']))
      ->setCharset('UTF-8')
      ->jsonSerialize();

    $this->assertEquals('https://github.com/cedx/akismet.php', $data->blog);
    $this->assertEquals('UTF-8', $data->blog_charset);
    $this->assertEquals('en,fr', $data->blog_lang);
  }
}
