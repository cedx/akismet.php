<?php
/**
 * Implementation of the `akismet\test\BlogTest` class.
 */
namespace akismet\test;

use akismet\{Blog};
use Codeception\{Specify};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \akismet\Blog
 */
class BlogTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(Blog::fromJSON('foo'));
    });

    $this->specify('should return an empty instance with an empty map', function() {
      $blog = Blog::fromJSON([]);
      $this->assertEmpty($blog->getCharset());
      $this->assertEmpty($blog->getLanguages());
      $this->assertEmpty($blog->getURL());
    });

    $this->specify('should return an initialized instance with a non-empty map', function() {
      $blog = Blog::fromJSON([
        'blog' => 'https://github.com/cedx/akismet.php',
        'blog_charset' => 'UTF-8',
        'blog_lang' => 'en, fr'
      ]);

      $this->assertEquals('UTF-8', $blog->getCharset());
      $this->assertEquals(['en', 'fr'], $blog->getLanguages()->getArrayCopy());
      $this->assertEquals('https://github.com/cedx/akismet.php', $blog->getURL());
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return an empty map with a newly created instance', function() {
      $data = (new Blog())->jsonSerialize();
      $this->assertEmpty(get_object_vars($data));
    });

    $this->specify('should return a non-empty map with a initialized instance', function() {
      $data = (new Blog('https://github.com/cedx/akismet.php', 'UTF-8', ['en', 'fr']))->jsonSerialize();
      $this->assertEquals('https://github.com/cedx/akismet.php', $data->blog);
      $this->assertEquals('UTF-8', $data->blog_charset);
      $this->assertEquals('en,fr', $data->blog_lang);
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $blog = (string) (new Blog('https://github.com/cedx/akismet.php', 'UTF-8', ['en', 'fr']));

    $this->specify('should start with the class name', function() use ($blog) {
      $this->assertStringStartsWith('akismet\Blog {', $blog);
    });

    $this->specify('should contain the instance properties', function() use ($blog) {
      $this->assertContains('"blog":"https://github.com/cedx/akismet.php"', $blog);
      $this->assertContains('"blog_charset":"UTF-8"', $blog);
      $this->assertContains('"blog_lang":"en,fr"', $blog);
    });
  }
}
