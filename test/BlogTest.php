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
    // Should return a null reference with a non-object value.
    $this->assertNull(Blog::fromJSON('foo'));

    // Should return an empty instance with an empty map.
    $blog = Blog::fromJSON([]);
    $this->assertEmpty($blog->getCharset());
    $this->assertEmpty($blog->getLanguages());
    $this->assertEmpty($blog->getURL());

    // Should return an initialized instance with a non-empty map.
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
    // Should return an empty map with a newly created instance.
    $data = (new Blog())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    // Should return a non-empty map with a initialized instance.
    $data = (new Blog('https://github.com/cedx/akismet.php', 'UTF-8', ['en', 'fr']))->jsonSerialize();
    $this->assertEquals('https://github.com/cedx/akismet.php', $data->blog);
    $this->assertEquals('UTF-8', $data->blog_charset);
    $this->assertEquals('en,fr', $data->blog_lang);
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $blog = (string) (new Blog('https://github.com/cedx/akismet.php', 'UTF-8', ['en', 'fr']));

    // Should start with the class name.
    $this->assertStringStartsWith('akismet\Blog {', $blog);

    // Should contain the instance properties.
    $this->assertContains('"blog":"https://github.com/cedx/akismet.php"', $blog);
    $this->assertContains('"blog_charset":"UTF-8"', $blog);
    $this->assertContains('"blog_lang":"en,fr"', $blog);
  }
}
