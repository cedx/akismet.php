<?php
declare(strict_types=1);
namespace Akismet;

use PHPUnit\Framework\{TestCase};
use Psr\Http\Message\{UriInterface};

/**
 * Tests the features of the `Akismet\Blog` class.
 */
class BlogTest extends TestCase {

  /**
   * @test Blog::fromJson
   */
  function testFromJson(): void {
    // It should return a null reference with a non-object value.
    assertThat(Blog::fromJson('foo'), isNull());

    // It should return an empty instance with an empty map.
    $blog = Blog::fromJson([]);
    assertThat($blog->getCharset(), isEmpty());
    assertThat($blog->getLanguages(), isEmpty());
    assertThat($blog->getUrl(), isNull());

    // It should return an initialized instance with a non-empty map.
    $blog = Blog::fromJson([
      'blog' => 'https://dev.belin.io/akismet.php',
      'blog_charset' => 'UTF-8',
      'blog_lang' => 'en, fr'
    ]);

    assertThat($blog->getCharset(), equalTo('UTF-8'));
    assertThat($blog->getLanguages()->getArrayCopy(), equalTo(['en', 'fr']));

    $url = $blog->getUrl();
    assertThat($url, isInstanceOf(UriInterface::class));
    assertThat((string) $url, equalTo('https://dev.belin.io/akismet.php'));
  }

  /**
   * @test Blog::jsonSerialize
   */
  function testJsonSerialize(): void {
    // It should return only the blog URL with a newly created instance.
    $data = (new Blog('https://dev.belin.io/akismet.php'))->jsonSerialize();
    assertThat(get_object_vars($data), countOf(1));
    assertThat($data->blog, equalTo('https://dev.belin.io/akismet.php'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Blog('https://dev.belin.io/akismet.php', 'UTF-8', ['en', 'fr']))->jsonSerialize();
    assertThat(get_object_vars($data), countOf(3));
    assertThat($data->blog, equalTo('https://dev.belin.io/akismet.php'));
    assertThat($data->blog_charset, equalTo('UTF-8'));
    assertThat($data->blog_lang, equalTo('en,fr'));
  }

  /**
   * @test Blog::__toString
   */
  function testToString(): void {
    $blog = (string) (new Blog('https://dev.belin.io/akismet.php', 'UTF-8', ['en', 'fr']));

    // It should start with the class name.
    assertThat($blog, stringStartsWith('Akismet\Blog {'));

    // It should contain the instance properties.
    assertThat($blog, logicalAnd(
      stringContains('"blog":"https://dev.belin.io/akismet.php"'),
      stringContains('"blog_charset":"UTF-8"'),
      stringContains('"blog_lang":"en,fr"')
    ));
  }
}
