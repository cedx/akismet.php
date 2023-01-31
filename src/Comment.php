<?php namespace akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Represents a comment submitted by an author.
 */
class Comment implements \JsonSerializable {

	/**
	 * The comment's author.
	 */
	public ?Author $author;

	/**
	 * The comment's content.
	 */
	public string $content;

	/**
	 * The UTC timestamp of the creation of the comment.
	 */
	public ?\DateTimeInterface $date;

	/**
	 * The permanent location of the entry the comment is submitted to.
	 */
	public ?UriInterface $permalink;

	/**
	 * The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
	 */
	public ?\DateTimeInterface $postModified;

	/**
	 * A string describing why the content is being rechecked.
	 */
	public string $recheckReason;

	/**
	 * The URL of the webpage that linked to the entry being requested.
	 */
	public ?UriInterface $referrer;

	/**
	 * The comment's type.
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
	 * @param string $type The comment's type.
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
	 * @param object $json A JSON object representing a comment.
	 * @return self The instance corresponding to the specified JSON object.
	 */
	static function fromJson(object $json): self {
		$keys = array_keys(get_object_vars($json));
		$hasAuthor = count(array_filter($keys, fn(string $key) => str_starts_with($key, "comment_author") || str_starts_with($key, "user"))) > 0;
		return new self(
			author: $hasAuthor ? Author::fromJson($json) : null,
			content: isset($json->comment_content) && is_string($json->comment_content) ? $json->comment_content : "",
			date: isset($json->comment_date_gmt) && is_string($json->comment_date_gmt) ? new \DateTimeImmutable($json->comment_date_gmt) : null,
			permalink: isset($json->permalink) && is_string($json->permalink) ? $json->permalink : "",
			postModified: isset($json->comment_post_modified_gmt) && is_string($json->comment_post_modified_gmt) ? new \DateTimeImmutable($json->comment_post_modified_gmt) : null,
			recheckReason: isset($json->recheck_reason) && is_string($json->recheck_reason) ? $json->recheck_reason : "",
			referrer: isset($json->referrer) && is_string($json->referrer) ? $json->referrer : "",
			type: isset($json->comment_type) && is_string($json->comment_type) ? $json->comment_type : ""
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
