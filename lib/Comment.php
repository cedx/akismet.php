<?php
/**
 * Implementation of the `akismet\Comment` class.
 */
namespace akismet;

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
  private $content = '';

  /**
   * @var \DateTime The UTC timestamp of the creation of the comment.
   */
  private $date;

  /**
   * @var string The permanent location of the entry the comment is submitted to.
   */
  private $permalink = '';

  /**
   * @var \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  private $postModified;

  /**
   * @var string The URL of the webpage that linked to the entry being requested.
   */
  private $referrer = '';

  /**
   * @var string The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   */
  private $type = '';

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
   * Creates a new comment from the specified JSON map.
   * @param mixed $map A JSON map representing a comment.
   * @return Comment The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    if (!is_array($map)) return null;

    $hasAuthor = count(array_filter(array_keys($map), function($key) {
      return preg_match('/^comment_author/', $key) || preg_match('/^user/', $key);
    })) > 0;

    return new static([
      'author' => $hasAuthor ? Author::fromJSON($map) : null,
      'content' => isset($map['comment_content']) && is_string($map['comment_content']) ? $map['comment_content'] : '',
      'date' => isset($map['comment_date_gmt']) && is_string($map['comment_date_gmt']) ? new \DateTime($map['comment_date_gmt']) : null,
      'permalink' => isset($map['permalink']) && is_string($map['permalink']) ? $map['permalink'] : '',
      'postModified' => isset($map['comment_post_modified_gmt']) && is_string($map['comment_post_modified_gmt']) ? new \DateTime($map['comment_post_modified_gmt']) : null,
      'referrer' => isset($map['referrer']) && is_string($map['referrer']) ? $map['referrer'] : '',
      'type' => isset($map['comment_type']) && is_string($map['comment_type']) ? $map['comment_type'] : ''
    ]);
  }

  /**
   * Gets the comment's author.
   * @return Author The comment's author.
   */
  public function getAuthor() {
    return $this->author;
  }

  /**
   * Gets the comment's content.
   * @return string The comment's content.
   */
  public function getContent(): string {
    return $this->content;
  }

  /**
   * Gets the UTC timestamp of the creation of the comment.
   * @return \DateTime The UTC timestamp of the creation of the comment.
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * Gets the permanent location of the entry the comment is submitted to.
   * @return string The permanent location of the entry the comment is submitted to.
   */
  public function getPermalink(): string {
    return $this->permalink;
  }

  /**
   * Gets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @return \DateTime The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   */
  public function getPostModified() {
    return $this->postModified;
  }

  /**
   * Gets the URL of the webpage that linked to the entry being requested.
   * @return string The URL of the webpage that linked to the entry being requested.
   */
  public function getReferrer(): string {
    return $this->referrer;
  }

  /**
   * Gets the comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   * @return string The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return array The map in JSON format corresponding to this object.
   */
  final public function jsonSerialize(): array {
    return $this->toJSON();
  }

  /**
   * Sets the comment's author.
   * @param Author $value The new author.
   */
  public function setAuthor(Author $value) {
    $this->author = $value;
  }

  /**
   * Sets the comment's content.
   * @param string $value The new content.
   */
  public function setContent(string $value) {
    $this->content = $value;
  }

  /**
   * Sets the UTC timestamp of the creation of the comment.
   * @param \DateTime $value The new UTC timestamp of the creation of the comment.
   */
  public function setDate(\DateTime $value) {
    $this->date = $value;
  }

  /**
   * Sets the permanent location of the entry the comment is submitted to.
   * @param string $value The new permanent location of the entry.
   */
  public function setPermalink(string $value) {
    $this->permalink = $value;
  }

  /**
   * Sets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
   * @param \DateTime $value The new UTC timestamp of the publication time.
   */
  public function setPostModified(\DateTime $value) {
    $this->postModified = $value;
  }

  /**
   * Sets the URL of the webpage that linked to the entry being requested.
   * @param string $value The new URL of the webpage that linked to the entry.
   */
  public function setReferrer(string $value) {
    $this->referrer = $value;
  }

  /**
   * Sets the comment's type.
   * @param string $value The new type.
   */
  public function setType(string $value) {
    $this->type = $value;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return array The map in JSON format corresponding to this object.
   */
  public function toJSON(): array {
    $map = ($author = $this->getAuthor()) ? $author->toJSON() : [];

    if (mb_strlen($content = $this->getContent())) $map['comment_content'] = $content;
    if ($date = $this->getDate()) $map['comment_date_gmt'] = $date->format('c');
    if ($postModified = $this->getPostModified()) $map['comment_post_modified_gmt'] = $postModified->format('c');
    if (mb_strlen($type = $this->getType())) $map['comment_type'] = $type;
    if (mb_strlen($permalink = $this->getPermalink())) $map['permalink'] = $permalink;
    if (mb_strlen($referrer = $this->getReferrer())) $map['referrer'] = $referrer;

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
