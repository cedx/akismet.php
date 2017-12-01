<?php
declare(strict_types=1);
namespace Akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};

/**
 * Represents the front page or home URL transmitted when making requests.
 */
class Blog implements \JsonSerializable {

  /**
   * @var string The character encoding for the values included in comments.
   */
  private $charset;

  /**
   * @var \ArrayObject The languages in use on the blog or site, in ISO 639-1 format.
   */
  private $languages;

  /**
   * @var Uri The blog or site URL.
   */
  private $url;

  /**
   * Initializes a new instance of the class.
   * @param string|UriInterface $url The blog or site URL.
   * @param string $charset The character encoding for the values included in comments.
   * @param string[] $languages The languages in use on the blog or site.
   */
  public function __construct($url, string $charset = '', array $languages = []) {
    $this->url = is_string($url) ? new Uri($url) : $url;
    $this->charset = $charset;
    $this->languages = new \ArrayObject($languages);
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }

  /**
   * Creates a new blog from the specified JSON map.
   * @param mixed $map A JSON map representing a blog.
   * @return Blog The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJson($map): ?self {
    if (is_array($map)) $map = (object) $map;
    else if (!is_object($map)) return null;

    $transform = function($languages) {
      return array_values(array_filter(array_map('trim', explode(',', $languages)), function($language) {
        return mb_strlen($language) > 0;
      }));
    };

    return new static(
      isset($map->blog) && is_string($map->blog) ? $map->blog : null,
      isset($map->blog_charset) && is_string($map->blog_charset) ? $map->blog_charset : '',
      isset($map->blog_lang) && is_string($map->blog_lang) ? $transform($map->blog_lang) : []
    );
  }

  /**
   * Gets the character encoding for the values included in comments.
   * @return string The character encoding for the values included in comments.
   */
  public function getCharset(): string {
    return $this->charset;
  }

  /**
   * Gets the languages in use on the blog or site, in ISO 639-1 format.
   * @return \ArrayObject The languages in use on the blog or site.
   */
  public function getLanguages(): \ArrayObject {
    return $this->languages;
  }

  /**
   * Gets the blog or site URL.
   * @return UriInterface The blog or site URL.
   */
  public function getUrl(): ?UriInterface {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    $map->blog = (string) $this->getUrl();

    if (mb_strlen($charset = $this->getCharset())) $map->blog_charset = $charset;
    if (count($languages = $this->getLanguages())) $map->blog_lang = implode(',', $languages->getArrayCopy());
    return $map;
  }
}
