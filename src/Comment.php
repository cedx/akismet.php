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
	 * The context in which this comment was posted.
	 * @var string[]
	 */
	public array $context;

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
	 * @param string $type The comment's type.
	 * @param \DateTimeInterface|null $date The UTC timestamp of the creation of the comment.
	 * @param \DateTimeInterface|null $postModified The UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
	 * @param string|UriInterface $permalink The permanent location of the entry the comment is submitted to.
	 * @param string|UriInterface $referrer The URL of the webpage that linked to the entry being requested.
	 * @param string $recheckReason A string describing why the content is being rechecked.
	 * @param string[] $context The context in which this comment was posted.
	 */
	function __construct(
		?Author $author, string $content = "", string $type = "", ?\DateTimeInterface $date = null, ?\DateTimeInterface $postModified = null,
		string|UriInterface $permalink = "", string|UriInterface $referrer = "", string $recheckReason = "", array $context = []
	) {
		$this->author = $author;
		$this->content = $content;
		$this->context = $context;
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
		$hasAuthor = count(array_filter($keys, fn($key) => str_starts_with($key, "comment_author") || str_starts_with($key, "user"))) > 0;
		return new self(
			author: $hasAuthor ? Author::fromJson($json) : null,
			content: (string) ($json->comment_content ?? ""),
			context: array_map(strval(...), (array) ($json->comment_context ?? [])),
			date: ($date = (string) ($json->comment_date_gmt ?? "")) ? new \DateTime($date) : null,
			permalink: (string) ($json->permalink ?? ""),
			postModified: ($date = (string) ($json->comment_post_modified_gmt ?? "")) ? new \DateTime($date) : null,
			recheckReason: (string) ($json->recheck_reason ?? ""),
			referrer: (string) ($json->referrer ?? ""),
			type: (string) ($json->comment_type ?? "")
		);
	}

	/**
	 * Converts this object to a map in JSON format.
	 * @return \stdClass The map in JSON format corresponding to this object.
	 */
	function jsonSerialize(): \stdClass {
		$map = $this->author ? $this->author->jsonSerialize() : new \stdClass;
		if ($this->content) $map->comment_content = $this->content;
		if ($this->context) $map->comment_context = $this->context;
		if ($this->date) $map->comment_date_gmt = $this->date->format("c");
		if ($this->permalink) $map->permalink = (string) $this->permalink;
		if ($this->postModified) $map->comment_post_modified_gmt = $this->postModified->format("c");
		if ($this->recheckReason) $map->recheck_reason = $this->recheckReason;
		if ($this->referrer) $map->referrer = (string) $this->referrer;
		if ($this->type) $map->comment_type = $this->type;
		return $map;
	}
}
