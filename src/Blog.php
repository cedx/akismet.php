<?php namespace akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Represents the front page or home URL transmitted when making requests.
 */
class Blog implements \JsonSerializable {

	/**
	 * The character encoding for the values included in comments.
	 */
	public string $charset;

	/**
	 * The languages in use on the blog or site, in ISO 639-1 format.
	 * @var string[]
	 */
	public array $languages;

	/**
	 * The blog or site URL.
	 */
	public ?UriInterface $url;

	/**
	 * Creates a new blog.
	 * @param string|UriInterface $url The blog or site URL.
	 * @param string $charset The character encoding for the values included in comments.
	 * @param string[] $languages The languages in use on the blog or site, in ISO 639-1 format.
	 */
	function __construct(string|UriInterface $url, string $charset = "", array $languages = []) {
		$this->charset = $charset;
		$this->languages = $languages;
		$this->url = $url ? new Uri($url) : null;
	}

	/**
	 * Creates a new blog from the specified JSON object.
	 * @param object $json A JSON object representing a blog.
	 * @return self The instance corresponding to the specified JSON object.
	 */
	static function fromJson(object $json): self {
		return new self(
			charset: isset($json->blog_charset) && is_string($json->blog_charset) ? $json->blog_charset : "",
			languages: isset($json->blog_lang) && is_string($json->blog_lang) ? array_map(trim(...), explode(",", $json->blog_lang)) : [],
			url: isset($json->blog) && is_string($json->blog) ? $json->blog : ""
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
