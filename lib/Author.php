<?php
/**
 * Implementation of the `akismet\Author` class.
 */
namespace akismet;

/**
 * Represents the author of a comment.
 */
class Author implements \JsonSerializable {

  /**
   * @var string The author's mail address.
   */
  public $email = '';

  /**
   * @var string The author's IP address.
   */
  public $ipAddress = '';

  /**
   * @var string The author's name.
   */
  public $name = '';

  /**
   * @var string The role of the author. If you set it to `"administrator"`, Akismet will always return `false`.
   */
  public $role = '';

  /**
   * @var string The URL of the author's website.
   */
  public $url = '';

  /**
   * @var string The author's user agent, that is the string identifying the Web browser used to submit comments.
   */
  public $userAgent = '';

  /**
   * Initializes a new instance of the class.
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct($config = []) {
    foreach ($config as $property => $value) $this->$property = $value;
  }

  /**
   * Creates a new author from the specified JSON map.
   * @param mixed $map A JSON map representing an author.
   * @return Author The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    return !is_array($map) || !$map ? null : new static([
      'email' => isset($map['comment_author_email']) && is_string($map['comment_author_email']) ? $map['comment_author_email'] : '',
      'ipAddress' => isset($map['user_ip']) && is_string($map['comment_author_email']) ? $map['user_ip'] : '',
      'name' => isset($map['comment_author']) && is_string($map['comment_author_email']) ? $map['comment_author'] : '',
      'role' => isset($map['user_role']) && is_string($map['comment_author_email']) ? $map['user_role'] : '',
      'url' => isset($map['comment_author_url']) && is_string($map['comment_author_email']) ? $map['comment_author_url'] : '',
      'userAgent' => isset($map['user_agent']) && is_string($map['comment_author_email']) ? $map['user_agent'] : ''
    ]);
  }

  /**
   * Converts this object to a map in JSON format.
   * @return array The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): array {
    $map = [];

    if (mb_strlen($this->name)) $map['comment_author'] = $this->name;
    if (mb_strlen($this->email)) $map['comment_author_email'] = $this->email;
    if (mb_strlen($this->url)) $map['comment_author_url'] = $this->url;
    if (mb_strlen($this->userAgent)) $map['user_agent'] = $this->userAgent;
    if (mb_strlen($this->ipAddress)) $map['user_ip'] = $this->ipAddress;
    if (mb_strlen($this->role)) $map['user_role'] = $this->role;

    return $map;
  }
}
