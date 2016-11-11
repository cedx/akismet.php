<?php
/**
 * Implementation of the `akismet\Blog` class.
 */
namespace akismet;

/**
 * Represents the front page or home URL transmitted when making requests.
 */
class Blog implements \JsonSerializable {

  /**
   * @var string The character encoding for the values included in comments.
   */
  private $charset = '';

  /**
   * @var string The language(s) in use on the blog or site, in ISO 639-1 format, comma-separated.
   */
  private $language = '';

  /**
   * @var string The blog or site URL.
   */
  private $url = '';

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    foreach ($config as $property => $value) {
      $setter = "set$property";
      if(method_exists($this, $setter)) $this->$setter($value);
    }
  }

  /**
   * Creates a new blog from the specified JSON map.
   * @param mixed $map A JSON map representing a blog.
   * @return static The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    return !is_array($map) ? null : new static([
      'charset' => isset($map['blog_charset']) && is_string($map['blog_charset']) ? $map['blog_charset'] : '',
      'language' => isset($map['blog_lang']) && is_string($map['blog_lang']) ? $map['blog_lang'] : '',
      'url' => isset($map['blog']) && is_string($map['blog']) ? $map['blog'] : ''
    ]);
  }

  /**
   * Gets the character encoding for the values included in comments.
   * @return string The character encoding for the values included in comments.
   */
  public function getCharset(): string {
    return $this->charset;
  }

  /**
   * Gets the language(s) in use on the blog or site, in ISO 639-1 format, comma-separated.
   * @return string The language(s) in use on the blog or site.
   */
  public function getLanguage(): string {
    return $this->language;
  }

  /**
   * Gets the blog or site URL.
   * @return string The blog or site URL.
   */
  public function getURL(): string {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return array The map in JSON format corresponding to this object.
   */
  final public function jsonSerialize(): array {
    return $this->toJSON();
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
   * Sets the language(s) in use on the blog or site, in ISO 639-1 format, comma-separated.
   * @param string $value The new language(s).
   * @return Blog This instance.
   */
  public function setLanguage(string $value): self {
    $this->language = $value;
    return $this;
  }

  /**
   * Sets the blog or site URL.
   * @param string $value The new URL.
   * @return Blog This instance.
   */
  public function setURL(string $value): self {
    $this->url = $value;
    return $this;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return array The map in JSON format corresponding to this object.
   */
  public function toJSON(): array {
    $map = [];
    if (mb_strlen($url = $this->getURL())) $map['blog'] = $url;
    if (mb_strlen($charset = $this->getCharset())) $map['blog_charset'] = $charset;
    if (mb_strlen($language = $this->getLanguage())) $map['blog_lang'] = $language;
    return $map;
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class . " $json";
  }
}
