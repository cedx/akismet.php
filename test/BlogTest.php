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
  public function testFromJson() {
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
        'blog' => 'https://github.com/cedx/akismet.php',
        'blog_charset' => 'UTF-8',
        'blog_lang' => 'en, fr'
      ]);

      expect($blog->getCharset())->to->equal('UTF-8');
      expect($blog->getLanguages()->getArrayCopy())->to->equal(['en', 'fr']);
      expect((string) $blog->getUrl())->to->equal('https://github.com/cedx/akismet.php');
    });
  }

  /**
   * @test Blog::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Blog)->jsonSerialize())->to->be->empty;
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
   * @test Blog::setUrl
   */
  public function testSetUrl() {
    it('should return an instance of `UriInterface` for strings', function() {
      $url = (new Blog)->setUrl('https://github.com/cedx/akismet.php')->getUrl();
      expect($url)->to->be->instanceOf(UriInterface::class);
      expect((string) $url)->to->equal('https://github.com/cedx/akismet.php');
    });

    it('should return a `null` reference for unsupported values', function() {
      expect((new Blog)->setUrl(123)->getUrl())->to->be->null;
    });
  }

  /**
   * @test Blog::__toString
   */
  public function testToString() {
    $blog = (string) (new Blog('https://github.com/cedx/akismet.php', 'UTF-8', ['en', 'fr']));

    it('should start with the class name', function() use ($blog) {
      expect($blog)->to->startWith('Akismet\Blog {');
    });

    it('should contain the instance properties', function() use ($blog) {
      expect($blog)->to->contain('"blog":"https://github.com/cedx/akismet.php"')
        ->and->contain('"blog_charset":"UTF-8"')
        ->and->contain('"blog_lang":"en,fr"');
    });
  }
}
