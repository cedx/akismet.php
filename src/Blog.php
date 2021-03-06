<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/** Represents the front page or home URL transmitted when making requests. */
class Blog implements \JsonSerializable {

	/** The character encoding for the values included in comments. */
	private string $charset = "";

	/** The languages in use on the blog or site, in ISO 639-1 format. */
	private \ArrayObject $languages;

	/** Creates a new blog. */
	function __construct(private ?UriInterface $url) {
		$this->languages = new \ArrayObject;
	}

	/** Creates a new blog from the specified JSON object. */
	static function fromJson(object $map): self {
		return (new self(isset($map->blog) && is_string($map->blog) ? new Uri($map->blog) : null))
			->setCharset(isset($map->blog_charset) && is_string($map->blog_charset) ? $map->blog_charset : "")
			->setLanguages(isset($map->blog_lang) && is_string($map->blog_lang) ? array_map("trim", explode(",", $map->blog_lang)) : []);
	}

	/** Gets the character encoding for the values included in comments. */
	function getCharset(): string {
		return $this->charset;
	}

	/** Gets the languages in use on the blog or site, in ISO 639-1 format. */
	function getLanguages(): \ArrayObject {
		return $this->languages;
	}

	/** Gets the blog or site URL. */
	function getUrl(): ?UriInterface {
		return $this->url;
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;
		$map->blog = (string) $this->getUrl();
		if (mb_strlen($charset = $this->getCharset())) $map->blog_charset = $charset;
		if (count($languages = $this->getLanguages())) $map->blog_lang = implode(",", (array) $languages);
		return $map;
	}

	/** Sets the character encoding for the values included in comments. */
	function setCharset(string $value): static {
		$this->charset = $value;
		return $this;
	}

	/** Sets the languages in use on the blog or site, in ISO 639-1 format. */
	function setLanguages(array $values): static {
		$this->getLanguages()->exchangeArray($values);
		return $this;
	}
}
