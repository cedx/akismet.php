<?php declare(strict_types=1);
namespace Akismet;

use GuzzleHttp\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Akismet\Blog` class. */
class BlogTest extends TestCase {

  /** @test Blog::fromJson() */
  function testFromJson(): void {
    // It should return an empty instance with an empty map.
    $blog = Blog::fromJson(new \stdClass);
    assertThat($blog->getCharset(), isEmpty());
    assertThat($blog->getLanguages(), isEmpty());
    assertThat($blog->getUrl(), isNull());

    // It should return an initialized instance with a non-empty map.
    $blog = Blog::fromJson((object) [
      'blog' => 'https://dev.belin.io/akismet.php',
      'blog_charset' => 'UTF-8',
      'blog_lang' => 'en, fr'
    ]);

    assertThat($blog->getCharset(), equalTo('UTF-8'));
    assertThat($blog->getLanguages()->getArrayCopy(), equalTo(['en', 'fr']));
    assertThat((string) $blog->getUrl(), equalTo('https://dev.belin.io/akismet.php'));
  }

  /** @test Blog->jsonSerialize() */
  function testJsonSerialize(): void {
    // It should return only the blog URL with a newly created instance.
    $data = (new Blog(new Uri('https://dev.belin.io/akismet.php')))->jsonSerialize();
    assertThat(get_object_vars($data), countOf(1));
    assertThat($data->blog, equalTo('https://dev.belin.io/akismet.php'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Blog(new Uri('https://dev.belin.io/akismet.php'), 'UTF-8', ['en', 'fr']))->jsonSerialize();
    assertThat(get_object_vars($data), countOf(3));
    assertThat($data->blog, equalTo('https://dev.belin.io/akismet.php'));
    assertThat($data->blog_charset, equalTo('UTF-8'));
    assertThat($data->blog_lang, equalTo('en,fr'));
  }
}
