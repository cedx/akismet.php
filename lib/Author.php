<?php
declare(strict_types=1);
namespace Akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};

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
  private $ipAddress;

  /**
   * @var string The author's name.
   */
  private $name;

  /**
   * @var string The author's role.
   */
  private $role = '';

  /**
   * @var Uri The URL of the author's website.
   */
  private $url;

  /**
   * @var string The author's user agent, that is the string identifying the Web browser used to submit comments.
   */
  private $userAgent;

  /**
   * Creates a new author.
   * @param string $ipAddress The author's IP address.
   * @param string $userAgent The author's user agent.
   * @param string $name The author's name.
   */
  function __construct(string $ipAddress, string $userAgent, string $name = '') {
    $this->ipAddress = $ipAddress;
    $this->userAgent = $userAgent;
    $this->name = $name;
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class . " $json";
  }

  /**
   * Creates a new author from the specified JSON map.
   * @param object $map A JSON map representing an author.
   * @return self The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  static function fromJson(object $map): self {
    $author = new static(
      isset($map->user_ip) && is_string($map->user_ip) ? $map->user_ip : '',
      isset($map->user_agent) && is_string($map->user_agent) ? $map->user_agent : '',
      isset($map->comment_author) && is_string($map->comment_author) ? $map->comment_author : ''
    );

    return $author
      ->setEmail(isset($map->comment_author_email) && is_string($map->comment_author_email) ? $map->comment_author_email : '')
      ->setRole(isset($map->user_role) && is_string($map->user_role) ? $map->user_role : '')
      ->setUrl(isset($map->comment_author_url) && is_string($map->comment_author_url) ? $map->comment_author_url : null);
  }

  /**
   * Gets the author's mail address.
   * If you set it to `"akismet-guaranteed-spam@example.com"`, Akismet will always return `true`.
   * @return string The author's mail address.
   */
  function getEmail(): string {
    return $this->email;
  }

  /**
   * Gets the author's IP address.
   * @return string The author's IP address.
   */
  function getIPAddress(): string {
    return $this->ipAddress;
  }

  /**
   * Gets the author's name.
   * If you set it to `"viagra-test-123"`, Akismet will always return `true`.
   * @return string The author's name.
   */
  function getName(): string {
    return $this->name;
  }

  /**
   * Gets the author's role.
   * If you set it to `"administrator"`, Akismet will always return `false`.
   * @return string The author's role.
   */
  function getRole(): string {
    return $this->role;
  }

  /**
   * Gets the URL of the author's website.
   * @return UriInterface The URL of the author's website.
   */
  function getUrl(): ?UriInterface {
    return $this->url;
  }

  /**
   * Gets the author's user agent, that is the string identifying the Web browser used to submit comments.
   * @return string The author's user agent.
   */
  function getUserAgent(): string {
    return $this->userAgent;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    $map = new \stdClass;
    $map->user_agent = $this->getUserAgent();
    $map->user_ip = $this->getIPAddress();

    if (mb_strlen($name = $this->getName())) $map->comment_author = $name;
    if (mb_strlen($email = $this->getEmail())) $map->comment_author_email = $email;
    if ($url = $this->getUrl()) $map->comment_author_url = (string) $url;
    if (mb_strlen($role = $this->getRole())) $map->user_role = $role;
    return $map;
  }

  /**
   * Sets the author's mail address. If you set it to `"akismet-guaranteed-spam@example.com"`, Akismet will always return `true`.
   * @param string $value The new mail address.
   * @return self This instance.
   */
  function setEmail(string $value): self {
    $this->email = $value;
    return $this;
  }

  /**
   * Sets the author's role. If you set it to `"administrator"`, Akismet will always return `false`.
   * @param string $value The new role.
   * @return self This instance.
   */
  function setRole(string $value): self {
    $this->role = $value;
    return $this;
  }

  /**
   * Sets the URL of the author's website.
   * @param string|UriInterface $value The new website URL.
   * @return self This instance.
   */
  function setUrl($value): self {
    $this->url = is_string($value) ? new Uri($value) : $value;
    return $this;
  }
}
