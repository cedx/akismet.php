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
  private $email = '';

  /**
   * @var string The author's IP address.
   */
  private $ipAddress = '';

  /**
   * @var string The author's name.
   */
  private $name = '';

  /**
   * @var string The author's role.
   */
  private $role = '';

  /**
   * @var string The URL of the author's website.
   */
  private $url = '';

  /**
   * @var string The author's user agent, that is the string identifying the Web browser used to submit comments.
   */
  private $userAgent = '';

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
   * Creates a new author from the specified JSON map.
   * @param mixed $map A JSON map representing an author.
   * @return Author The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    return !is_array($map) ? null : new static([
      'email' => isset($map['comment_author_email']) && is_string($map['comment_author_email']) ? $map['comment_author_email'] : '',
      'ipAddress' => isset($map['user_ip']) && is_string($map['user_ip']) ? $map['user_ip'] : '',
      'name' => isset($map['comment_author']) && is_string($map['comment_author']) ? $map['comment_author'] : '',
      'role' => isset($map['user_role']) && is_string($map['user_role']) ? $map['user_role'] : '',
      'url' => isset($map['comment_author_url']) && is_string($map['comment_author_url']) ? $map['comment_author_url'] : '',
      'userAgent' => isset($map['user_agent']) && is_string($map['user_agent']) ? $map['user_agent'] : ''
    ]);
  }

  /**
   * Gets the author's mail address.
   * @return string The author's mail address.
   */
  public function getEmail(): string {
    return $this->email;
  }

  /**
   * Gets the author's IP address.
   * @return string The author's IP address.
   */
  public function getIpAddress(): string {
    return $this->ipAddress;
  }

  /**
   * Gets the author's name.
   * @return string The author's name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Gets the author's role.
   * @return string The author's role.
   */
  public function getRole(): string {
    return $this->role;
  }

  /**
   * Gets the URL of the author's website.
   * @return string The URL of the author's website.
   */
  public function getURL(): string {
    return $this->url;
  }

  /**
   * Gets the author's user agent, that is the string identifying the Web browser used to submit comments.
   * @return string The author's user agent.
   */
  public function getUserAgent(): string {
    return $this->userAgent;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return array The map in JSON format corresponding to this object.
   */
  final public function jsonSerialize(): array {
    return $this->toJSON();
  }

  /**
   * Sets the author's mail address.
   * @param string $value The new mail address.
   * @return Author This instance.
   */
  public function setEmail(string $value) {
    $this->email = $value;
    return $this;
  }

  /**
   * Sets the the author's IP address.
   * @param string $value The new IP address.
   * @return Author This instance.
   */
  public function setIpAddress(string $value) {
    $this->ipAddress = $value;
    return $this;
  }

  /**
   * Sets the author's name.
   * @param string $value The new name.
   * @return Author This instance.
   */
  public function setName(string $value) {
    $this->name = $value;
    return $this;
  }

  /**
   * Sets the author's role. If you set it to `"administrator"`, Akismet will always return `false`.
   * @param string $value The new role.
   * @return Author This instance.
   */
  public function setRole(string $value) {
    $this->role = $value;
    return $this;
  }

  /**
   * Sets the URL of the author's website.
   * @param string $value The new website URL.
   * @return Author This instance.
   */
  public function setURL(string $value) {
    $this->url = $value;
    return $this;
  }

  /**
   * Sets the author's user agent, that is the string identifying the Web browser used to submit comments.
   * @param string $value The new user agent.
   * @return Author This instance.
   */
  public function setUserAgent(string $value) {
    $this->userAgent = $value;
    return $this;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return array The map in JSON format corresponding to this object.
   */
  public function toJSON(): array {
    $map = [];

    if (mb_strlen($name = $this->getName())) $map['comment_author'] = $name;
    if (mb_strlen($email = $this->getEmail())) $map['comment_author_email'] = $email;
    if (mb_strlen($url = $this->getURL())) $map['comment_author_url'] = $url;
    if (mb_strlen($userAgent = $this->getUserAgent())) $map['user_agent'] = $userAgent;
    if (mb_strlen($ipAddress = $this->getIpAddress())) $map['user_ip'] = $ipAddress;
    if (mb_strlen($role = $this->getRole())) $map['user_role'] = $role;

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
