<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Represents the front page or home URL transmitted when making requests.
 */
class Blog implements \JsonSerializable {

	/**
	 * The character encoding for the values included in comments.
	 * @var string
	 */
	public string $charset;

	/**
	 * The languages in use on the blog or site, in ISO 639-1 format.
	 * @var string[]
	 */
	public array $languages;

	/**
	 * The blog or site URL.
	 * @var UriInterface
	 */
	public UriInterface $url;

	/**
	 * Creates a new blog.
	 * @param string $url The blog or site URL.
	 * @param string $charset The character encoding for the values included in comments.
	 * @param string[] $languages The languages in use on the blog or site, in ISO 639-1 format.
	 */
	function __construct(string $url, string $charset = "", array $languages = []) {
		$this->charset = $charset;
		$this->languages = $languages;
		$this->url = new Uri($url);
	}

	/**
	 * Creates a new blog from the specified JSON object.
	 * @param object $map A JSON object representing a blog.
	 * @return self The instance corresponding to the specified JSON object.
	 */
	static function fromJson(object $map): self {
		return new self(
			charset: isset($map->blog_charset) && is_string($map->blog_charset) ? $map->blog_charset : "",
			languages: isset($map->blog_lang) && is_string($map->blog_lang) ? array_map(trim(...), explode(",", $map->blog_lang)) : [],
			url: isset($map->blog) && is_string($map->blog) ? $map->blog : ""
		);
	}

	/**
	 * Converts this object to a map in JSON format.
	 * @return \stdClass The map in JSON format corresponding to this object.
	 */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;
		$map->blog = (string) $this->url;
		if ($this->charset) $map->blog_charset = $this->charset;
		if ($this->languages) $map->blog_lang = implode(",", $this->languages);
		return $map;
	}
}
