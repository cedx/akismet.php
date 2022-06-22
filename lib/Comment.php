<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Represents a comment submitted by an author.
 */
class Comment implements \JsonSerializable {

	/**
	 * The comment's author.
	 * @var Author|null
	 */
	public ?Author $author;

	/**
	 * The comment's content.
	 * @var string
	 */
	public string $content;

	/**
	 * The UTC timestamp of the creation of the comment.
	 * @var \DateTimeInterface|null
	 */
	public ?\DateTimeInterface $date;

	/**
	 * The permanent location of the entry the comment is submitted to.
	 * @var UriInterface|null
	 */
	public ?UriInterface $permalink;

	/**
	 * The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
	 * @var \DateTimeInterface|null
	 */
	public ?\DateTimeInterface $postModified;

	/**
	 * A string describing why the content is being rechecked.
	 * @var string
	 */
	public string $recheckReason;

	/**
	 * The URL of the webpage that linked to the entry being requested.
	 * @var UriInterface|null
	 */
	public ?UriInterface $referrer;

	/**
	 * The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
	 * @var string
	 */
	public string $type = "";

	/**
	 * Creates a new comment.
	 * @param Author|null $author The comment's author.
	 * @param string $content The comment's content.
	 * @param \DateTimeInterface|null $date The UTC timestamp of the creation of the comment.
	 * @param string $permalink The permanent location of the entry the comment is submitted to.
	 * @param \DateTimeInterface|null $postModified The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
	 * @param string $recheckReason A string describing why the content is being rechecked.
	 * @param string $referrer The URL of the webpage that linked to the entry being requested.
	 * @param string $type The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`.
	 */
	function __construct(
		?Author $author, string $content = "", ?\DateTimeInterface $date = null, string $permalink = "",
		?\DateTimeInterface $postModified = null, string $recheckReason = "", string $referrer = "", string $type = ""
	) {
		$this->author = $author;
		$this->content = $content;
		$this->date = $date;
		$this->permalink = $permalink ? new Uri($permalink) : null;
		$this->postModified = $postModified;
		$this->recheckReason = $recheckReason;
		$this->referrer = $referrer ? new Uri($referrer) : null;
		$this->type = $type;
	}

	/**
	 * Creates a new comment from the specified JSON object.
	 * @param object $map A JSON object representing a comment.
	 * @return self The instance corresponding to the specified JSON object.
	 */
	static function fromJson(object $map): self {
		$keys = array_keys(get_object_vars($map));
		$hasAuthor = count(array_filter($keys, fn(string $key) => str_starts_with($key, "comment_author") || str_starts_with($key, "user"))) > 0;
		return new self(
			author: $hasAuthor ? Author::fromJson($map) : null,
			content: isset($map->comment_content) && is_string($map->comment_content) ? $map->comment_content : "",
			date: isset($map->comment_date_gmt) && is_string($map->comment_date_gmt) ? new \DateTimeImmutable($map->comment_date_gmt) : null,
			permalink: isset($map->permalink) && is_string($map->permalink) ? $map->permalink : "",
			postModified: isset($map->comment_post_modified_gmt) && is_string($map->comment_post_modified_gmt) ? new \DateTimeImmutable($map->comment_post_modified_gmt) : null,
			recheckReason: isset($map->recheck_reason) && is_string($map->recheck_reason) ? $map->recheck_reason : "",
			referrer: isset($map->referrer) && is_string($map->referrer) ? $map->referrer : "",
			type: isset($map->comment_type) && is_string($map->comment_type) ? $map->comment_type : ""
		);
	}

	/**
	 * Converts this object to a map in JSON format.
	 * @return \stdClass The map in JSON format corresponding to this object.
	 */
	function jsonSerialize(): \stdClass {
		$map = $this->author ? $this->author->jsonSerialize() : new \stdClass;
		if ($this->content) $map->comment_content = $this->content;
		if ($this->date) $map->comment_date_gmt = $this->date->format("c");
		if ($this->permalink) $map->permalink = (string) $this->permalink;
		if ($this->postModified) $map->comment_post_modified_gmt = $this->postModified->format("c");
		if ($this->recheckReason) $map->recheck_reason = $this->recheckReason;
		if ($this->referrer) $map->referrer = (string) $this->referrer;
		if ($this->type) $map->comment_type = $this->type;
		return $map;
	}
}
