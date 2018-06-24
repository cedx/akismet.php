<?php
declare(strict_types=1);
namespace Akismet;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};
use Psr\Http\Message\{UriInterface};

/**
 * Tests the features of the `Akismet\Blog` class.
 */
class BlogTest extends TestCase {

  /**
   * @test Blog::fromJson
   */
  public function testFromJson(): void {
    it('should return a null reference with a non-object value', function() {
      expect(Blog::fromJson('foo'))->to->be->null;
    });

    it('should return an empty instance with an empty map', function() {
      $blog = Blog::fromJson([]);
      expect($blog->getCharset())->to->be->empty;
      expect($blog->getLanguages())->to->be->empty;
      expect($blog->getUrl())->to->be->null;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $blog = Blog::fromJson([
        'blog' => 'https://dev.belin.io/akismet.php',
        'blog_charset' => 'UTF-8',
        'blog_lang' => 'en, fr'
      ]);

      expect($blog->getCharset())->to->equal('UTF-8');
      expect($blog->getLanguages()->getArrayCopy())->to->equal(['en', 'fr']);

      $url = $blog->getUrl();
      expect($url)->to->be->instanceOf(UriInterface::class);
      expect((string) $url)->to->equal('https://dev.belin.io/akismet.php');
    });
  }

  /**
   * @test Blog::jsonSerialize
   */
  public function testJsonSerialize(): void {
    it('should return only the blog URL with a newly created instance', function() {
      $data = (new Blog('https://dev.belin.io/akismet.php'))->jsonSerialize();
      expect(get_object_vars($data))->to->have->lengthOf(1);
      expect($data->blog)->to->equal('https://dev.belin.io/akismet.php');
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Blog('https://dev.belin.io/akismet.php', 'UTF-8', ['en', 'fr']))->jsonSerialize();
      expect(get_object_vars($data))->to->have->lengthOf(3);
      expect($data->blog)->to->equal('https://dev.belin.io/akismet.php');
      expect($data->blog_charset)->to->equal('UTF-8');
      expect($data->blog_lang)->to->equal('en,fr');
    });
  }

  /**
   * @test Blog::__toString
   */
  public function testToString(): void {
    $blog = (string) (new Blog('https://dev.belin.io/akismet.php', 'UTF-8', ['en', 'fr']));

    it('should start with the class name', function() use ($blog) {
      expect($blog)->to->startWith('Akismet\Blog {');
    });

    it('should contain the instance properties', function() use ($blog) {
      expect($blog)->to->contain('"blog":"https://dev.belin.io/akismet.php"')
        ->and->contain('"blog_charset":"UTF-8"')
        ->and->contain('"blog_lang":"en,fr"');
    });
  }
}
