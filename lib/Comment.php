<?php
declare(strict_types=1);
namespace Akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};

/**
 * Represents a comment submitted by an author.
 */
class Comment implements \JsonSerializable {

  /**
   * @var Author The comment's author.
   */
  private $author;

  /**
   * @var string The comment's content.
   */
  private $content;

  /**
   * @var \DateTime The UTC timestamp of the creation of the comment.
   */
  private $date;

  /**
   * @var Uri The permanent location of the entry the comment is submitted to.
   */
  private $permalink;

  /**
   * @var \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  private $postModified;

  /**
   * @var Uri The URL of the webpage that linked to the entry being requested.
   */
  private $referrer;

  /**
   * @var string The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   */
  private $type;

  /**
   * Creates a new comment.
   * @param Author $author The comment's author.
   * @param string $content The comment's content.
   * @param string $type The comment's type.
   */
  function __construct(?Author $author, string $content = '', string $type = '') {
    $this->author = $author;
    $this->content = $content;
    $this->type = $type;
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
   * Creates a new comment from the specified JSON map.
   * @param object $map A JSON map representing a comment.
   * @return self The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  static function fromJson(object $map): self {
    $keys = array_keys(get_object_vars($map));
    $hasAuthor = count(array_filter($keys, function($key) {
      return preg_match('/^comment_author/', $key) || preg_match('/^user/', $key);
    })) > 0;

    $comment = new static(
      $hasAuthor ? Author::fromJson($map) : null,
      isset($map->comment_content) && is_string($map->comment_content) ? $map->comment_content : '',
      isset($map->comment_type) && is_string($map->comment_type) ? $map->comment_type : ''
    );

    return $comment
      ->setDate(isset($map->comment_date_gmt) && is_string($map->comment_date_gmt) ? $map->comment_date_gmt : null)
      ->setPermalink(isset($map->permalink) && is_string($map->permalink) ? $map->permalink : null)
      ->setPostModified(isset($map->comment_post_modified_gmt) && is_string($map->comment_post_modified_gmt) ? $map->comment_post_modified_gmt : null)
      ->setReferrer(isset($map->referrer) && is_string($map->referrer) ? $map->referrer : null);
  }

  /**
   * Gets the comment's author.
   * @return Author The comment's author.
   */
  function getAuthor(): ?Author {
    return $this->author;
  }

  /**
   * Gets the comment's content.
   * @return string The comment's content.
   */
  function getContent(): string {
    return $this->content;
  }

  /**
   * Gets the UTC timestamp of the creation of the comment.
   * @return \DateTime The UTC timestamp of the creation of the comment.
   */
  function getDate(): ?\DateTime {
    return $this->date;
  }

  /**
   * Gets the permanent location of the entry the comment is submitted to.
   * @return UriInterface The permanent location of the entry the comment is submitted to.
   */
  function getPermalink(): ?UriInterface {
    return $this->permalink;
  }

  /**
   * Gets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @return \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  function getPostModified(): ?\DateTime {
    return $this->postModified;
  }

  /**
   * Gets the URL of the webpage that linked to the entry being requested.
   * @return UriInterface The URL of the webpage that linked to the entry being requested.
   */
  function getReferrer(): ?UriInterface {
    return $this->referrer;
  }

  /**
   * Gets the comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   * @return string The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   */
  function getType(): string {
    return $this->type;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    $map = $this->getAuthor()->jsonSerialize();
    if (mb_strlen($content = $this->getContent())) $map->comment_content = $content;
    if ($date = $this->getDate()) $map->comment_date_gmt = $date->format('c');
    if ($postModified = $this->getPostModified()) $map->comment_post_modified_gmt = $postModified->format('c');
    if (mb_strlen($type = $this->getType())) $map->comment_type = $type;
    if ($permalink = $this->getPermalink()) $map->permalink = (string) $permalink;
    if ($referrer = $this->getReferrer()) $map->referrer = (string) $referrer;
    return $map;
  }

  /**
   * Sets the UTC timestamp of the creation of the comment.
   * @param mixed $value The new UTC timestamp of the creation of the comment.
   * @return self This instance.
   */
  function setDate($value): self {
    if ($value instanceof \DateTime) $this->date = $value;
    else if (is_string($value)) $this->date = new \DateTime($value);
    else if (is_int($value)) $this->date = new \DateTime("@$value");
    else $this->date = null;

    return $this;
  }

  /**
   * Sets the permanent location of the entry the comment is submitted to.
   * @param string|UriInterface $value The new permanent location of the entry.
   * @return self This instance.
   */
  function setPermalink($value): self {
    $this->permalink = is_string($value) ? new Uri($value) : $value;
    return $this;
  }

  /**
   * Sets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @param mixed $value The new UTC timestamp of the publication time.
   * @return self This instance.
   */
  function setPostModified($value): self {
    if ($value instanceof \DateTime) $this->postModified = $value;
    else if (is_string($value)) $this->postModified = new \DateTime($value);
    else if (is_int($value)) $this->postModified = new \DateTime("@$value");
    else $this->postModified = null;

    return $this;
  }

  /**
   * Sets the URL of the webpage that linked to the entry being requested.
   * @param string|UriInterface $value The new URL of the webpage that linked to the entry.
   * @return self This instance.
   */
  function setReferrer($value): self {
    $this->referrer = is_string($value) ? new Uri($value) : $value;
    return $this;
  }
}
