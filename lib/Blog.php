<?php declare(strict_types=1);
namespace Akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};

/** Represents the front page or home URL transmitted when making requests. */
class Blog implements \JsonSerializable {

  /** @var string The character encoding for the values included in comments. */
  private string $charset;

  /** @var \ArrayObject The languages in use on the blog or site, in ISO 639-1 format. */
  private \ArrayObject  $languages;

  /** @var UriInterface|null The blog or site URL. */
  private ?UriInterface $url;

  /**
   * Creates a new blog.
   * @param UriInterface|null $url The blog or site URL.
   * @param string $charset The character encoding for the values included in comments.
   * @param string[] $languages The languages in use on the blog or site.
   */
  function __construct(?UriInterface $url, string $charset = '', array $languages = []) {
    $this->url = $url;
    $this->charset = $charset;
    $this->languages = new \ArrayObject($languages);
  }

  /**
   * Creates a new blog from the specified JSON object.
   * @param object $map A JSON object representing a blog.
   * @return self The instance corresponding to the specified JSON object.
   */
  static function fromJson(object $map): self {
    return new self(
      isset($map->blog) && is_string($map->blog) ? new Uri($map->blog) : null,
      isset($map->blog_charset) && is_string($map->blog_charset) ? $map->blog_charset : '',
      isset($map->blog_lang) && is_string($map->blog_lang) ? array_map('trim', explode(',', $map->blog_lang)) : []
    );
  }

  /**
   * Gets the character encoding for the values included in comments.
   * @return string The character encoding for the values included in comments.
   */
  function getCharset(): string {
    return $this->charset;
  }

  /**
   * Gets the languages in use on the blog or site, in ISO 639-1 format.
   * @return \ArrayObject The languages in use on the blog or site.
   */
  function getLanguages(): \ArrayObject {
    return $this->languages;
  }

  /**
   * Gets the blog or site URL.
   * @return UriInterface|null The blog or site URL.
   */
  function getUrl(): ?UriInterface {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    $map->blog = (string) $this->getUrl();
    if (mb_strlen($charset = $this->getCharset())) $map->blog_charset = $charset;
    if (count($languages = $this->getLanguages())) $map->blog_lang = implode(',', $languages->getArrayCopy());
    return $map;
  }
}
