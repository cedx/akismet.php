<?php
/**
 * Implementation of the `akismet\BlogTest` class.
 */
namespace akismet;

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
    it('should return a null reference with a non-object value', function() {
      expect(Blog::fromJSON('foo'))->to->be->null;
    });

    it('should return an empty instance with an empty map', function() {
      $blog = Blog::fromJSON([]);
      expect($blog->getCharset())->to->be->empty;
      expect($blog->getLanguages())->to->be->empty;
      expect($blog->getURL())->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $blog = Blog::fromJSON([
        'blog' => 'https://github.com/cedx/akismet.php',
        'blog_charset' => 'UTF-8',
        'blog_lang' => 'en, fr'
      ]);

      expect($blog->getCharset())->to->equal('UTF-8');
      expect($blog->getLanguages()->getArrayCopy())->to->equal(['en', 'fr']);
      expect($blog->getURL())->to->equal('https://github.com/cedx/akismet.php');
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Blog())->jsonSerialize())->to->be->empty;
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Blog('https://github.com/cedx/akismet.php', 'UTF-8', ['en', 'fr']))->jsonSerialize();
      expect(get_object_vars($data))->to->have->lengthOf(3);
      expect($data->blog)->to->equal('https://github.com/cedx/akismet.php');
      expect($data->blog_charset)->to->equal('UTF-8');
      expect($data->blog_lang)->to->equal('en,fr');
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $blog = (string) (new Blog('https://github.com/cedx/akismet.php', 'UTF-8', ['en', 'fr']));

    it('should start with the class name', function() use ($blog) {
      expect($blog)->to->startWith('akismet\Blog {');
    });

    it('should contain the instance properties', function() use ($blog) {
      expect($blog)->to->contain('"blog":"https://github.com/cedx/akismet.php"')
        ->and->contain('"blog_charset":"UTF-8"')
        ->and->contain('"blog_lang":"en,fr"');
    });
  }
}
