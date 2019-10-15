<?php declare(strict_types=1);
namespace Akismet;

use function PHPUnit\Expect\{expect, it};
use GuzzleHttp\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};

/** @testdox Akismet\Blog */
class BlogTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    it('should return an empty instance with an empty map', function() {
      $blog = Blog::fromJson(new \stdClass);
      expect($blog->getCharset())->to->be->empty;
      expect($blog->getLanguages())->to->be->empty;
      expect($blog->getUrl())->to->be->null;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $blog = Blog::fromJson((object) [
        'blog' => 'https://dev.belin.io/akismet.php',
        'blog_charset' => 'UTF-8',
        'blog_lang' => 'en, fr'
      ]);

      expect($blog->getCharset())->to->equal('UTF-8');
      expect($blog->getLanguages()->getArrayCopy())->to->equal(['en', 'fr']);
      expect((string) $blog->getUrl())->to->equal('https://dev.belin.io/akismet.php');
    });
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return only the blog URL with a newly created instance', function() {
      $data = (new Blog(new Uri('https://dev.belin.io/akismet.php')))->jsonSerialize();
      expect(get_object_vars($data))->to->have->lengthOf(1);
      expect($data->blog)->to->equal('https://dev.belin.io/akismet.php');
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Blog(new Uri('https://dev.belin.io/akismet.php'), 'UTF-8', ['en', 'fr']))->jsonSerialize();
      expect(get_object_vars($data))->to->have->lengthOf(3);
      expect($data->blog)->to->equal('https://dev.belin.io/akismet.php');
      expect($data->blog_charset)->to->equal('UTF-8');
      expect($data->blog_lang)->to->equal('en,fr');
    });
  }
}
