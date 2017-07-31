<?php
declare(strict_types=1);
namespace Akismet;

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
   * @var string The blog or site URL.
   */
  private $url;

  /**
   * Initializes a new instance of the class.
   * @param string $url The blog or site URL.
   * @param string $charset The character encoding for the values included in comments.
   * @param array $languages The languages in use on the blog or site.
   */
  public function __construct(string $url = '', string $charset = '', array $languages = []) {
    $this->languages = new \ArrayObject($languages);
    $this->setCharset($charset);
    $this->setUrl($url);
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
  public static function fromJson($map) {
    if (is_array($map)) $map = (object) $map;
    else if (!is_object($map)) return null;

    $transform = function($languages) {
      return array_values(array_filter(array_map('trim', explode(',', $languages))));
    };

    return (new static(isset($map->blog) && is_string($map->blog) ? $map->blog : ''))
      ->setCharset(isset($map->blog_charset) && is_string($map->blog_charset) ? $map->blog_charset : '')
      ->setLanguages(isset($map->blog_lang) && is_string($map->blog_lang) ? $transform($map->blog_lang) : []);
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
   * @return string The blog or site URL.
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    if (mb_strlen($url = $this->getUrl())) $map->blog = $url;
    if (mb_strlen($charset = $this->getCharset())) $map->blog_charset = $charset;
    if (count($languages = $this->getLanguages())) $map->blog_lang = implode(',', $languages->getArrayCopy());
    return $map;
  }

  /**
   * Sets the character encoding for the values included in comments.
   * @param string $value The new character encoding.
   * @return Blog This instance.
   */
  public function setCharset(string $value): self {
    $this->charset = $value;
    return $this;
  }

  /**
   * Sets the languages in use on the blog or site, in ISO 639-1 format.
   * @param array $values The new languages.
   * @return Blog This instance.
   */
  public function setLanguages(array $values): self {
    $this->languages->exchangeArray($values);
    return $this;
  }

  /**
   * Sets the blog or site URL.
   * @param string $value The new URL.
   * @return Blog This instance.
   */
  public function setUrl(string $value): self {
    $this->url = $value;
    return $this;
  }
}
