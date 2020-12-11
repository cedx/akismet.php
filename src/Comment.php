<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/** Represents a comment submitted by an author. */
class Comment implements \JsonSerializable {

	/** The comment's content. */
	private string $content = "";

	/** The UTC timestamp of the creation of the comment. */
	private ?\DateTimeImmutable $date = null;

	/** The permanent location of the entry the comment is submitted to. */
	private ?UriInterface $permalink = null;

	/** The UTC timestamp of the publication time for the post, page or thread on which the comment was posted. */
	private ?\DateTimeImmutable $postModified = null;

	/** A string describing why the content is being rechecked. */
	private string $recheckReason = "";

	/** The URL of the webpage that linked to the entry being requested. */
	private ?UriInterface $referrer = null;

	/** The comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`. */
	private string $type = "";

	/** Creates a new comment. */
	function __construct(private ?Author $author) {}

	/** Creates a new comment from the specified JSON object. */
	static function fromJson(object $map): self {
		$keys = array_keys(get_object_vars($map));
		$hasAuthor = count(array_filter($keys, fn($key) => (bool) preg_match('/^(comment_author|user)/', $key))) > 0;
		return (new self($hasAuthor ? Author::fromJson($map) : null))
			->setContent(isset($map->comment_content) && is_string($map->comment_content) ? $map->comment_content : "")
			->setDate(isset($map->comment_date_gmt) && is_string($map->comment_date_gmt) ? new \DateTimeImmutable($map->comment_date_gmt) : null)
			->setPermalink(isset($map->permalink) && is_string($map->permalink) ? new Uri($map->permalink) : null)
			->setPostModified(isset($map->comment_post_modified_gmt) && is_string($map->comment_post_modified_gmt) ? new \DateTimeImmutable($map->comment_post_modified_gmt) : null)
			->setRecheckReason(isset($map->recheck_reason) && is_string($map->recheck_reason) ? $map->recheck_reason : "")
			->setReferrer(isset($map->referrer) && is_string($map->referrer) ? new Uri($map->referrer) : null)
			->setType(isset($map->comment_type) && is_string($map->comment_type) ? $map->comment_type : "");
	}

	/** Gets the comment's author. */
	function getAuthor(): ?Author {
		return $this->author;
	}

	/** Gets the comment's content. */
	function getContent(): string {
		return $this->content;
	}

	/** Gets the UTC timestamp of the creation of the comment. */
	function getDate(): ?\DateTimeImmutable {
		return $this->date;
	}

	/** Gets the permanent location of the entry the comment is submitted to. */
	function getPermalink(): ?UriInterface {
		return $this->permalink;
	}

	/** Gets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted. */
	function getPostModified(): ?\DateTimeImmutable {
		return $this->postModified;
	}

	/** Gets the string describing why the content is being rechecked. */
	function getRecheckReason(): string {
		return $this->recheckReason;
	}

	/** Gets the URL of the webpage that linked to the entry being requested. */
	function getReferrer(): ?UriInterface {
		return $this->referrer;
	}

	/** Gets the comment's type. This string value specifies a `CommentType` constant or a made up value like `"registration"`. */
	function getType(): string {
		return $this->type;
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		$map = ($author = $this->getAuthor()) ? $author->jsonSerialize() : new \stdClass;
		if (mb_strlen($content = $this->getContent())) $map->comment_content = $content;
		if ($date = $this->getDate()) $map->comment_date_gmt = $date->format("c");
		if ($postModified = $this->getPostModified()) $map->comment_post_modified_gmt = $postModified->format("c");
		if (mb_strlen($type = $this->getType())) $map->comment_type = $type;
		if ($permalink = $this->getPermalink()) $map->permalink = (string) $permalink;
		if (mb_strlen($recheckReason = $this->getRecheckReason())) $map->recheck_reason = $recheckReason;
		if ($referrer = $this->getReferrer()) $map->referrer = (string) $referrer;
		return $map;
	}

	/**
	 * Sets the comment's content.
	 * @param string $value The new content.
	 * @return $this This instance.
	 */
	function setContent(string $value): self {
		$this->content = $value;
		return $this;
	}

	/**
	 * Sets the UTC timestamp of the creation of the comment.
	 * @param \DateTimeInterface|null $value The new UTC timestamp of the creation of the comment.
	 * @return $this This instance.
	 */
	function setDate(?\DateTimeInterface $value): self {
		$this->date = $value ? \DateTimeImmutable::createFromInterface($value) : null;
		return $this;
	}

	/**
	 * Sets the permanent location of the entry the comment is submitted to.
	 * @param UriInterface|null $value The new permanent location of the entry.
	 * @return $this This instance.
	 */
	function setPermalink(?UriInterface $value): self {
		$this->permalink = $value;
		return $this;
	}

	/**
	 * Sets the UTC timestamp of the publication time for the post, page or thread on which the comment was posted.
	 * @param \DateTimeInterface|null $value The new UTC timestamp of the publication time.
	 * @return $this This instance.
	 */
	function setPostModified(?\DateTimeInterface $value): self {
		$this->postModified = $value ? \DateTimeImmutable::createFromInterface($value) : null;
		return $this;
	}

	/**
	 * Sets the string describing why the content is being rechecked.
	 * @param string $value A string describing why the content is being rechecked.
	 * @return $this This instance.
	 */
	function setRecheckReason(string $value): self {
		$this->recheckReason = $value;
		return $this;
	}

	/**
	 * Sets the URL of the webpage that linked to the entry being requested.
	 * @param UriInterface|null $value The new URL of the webpage that linked to the entry.
	 * @return $this This instance.
	 */
	function setReferrer(?UriInterface $value): self {
		$this->referrer = $value;
		return $this;
	}

	/**
	 * Sets the comment's type.
	 * @param string $value The new type.
	 * @return $this This instance.
	 */
	function setType(string $value): self {
		$this->type = $value;
		return $this;
	}
}
