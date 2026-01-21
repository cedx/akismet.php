<?php declare(strict_types=1);
namespace Belin\Akismet;

use Uri\Rfc3986\Uri;

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
	public Uri $url;

	/**
	 * Creates a new blog.
	 * @param string|Uri $url The blog or site URL.
	 * @param string $charset The character encoding for the values included in comments.
	 * @param string[] $languages The languages in use on the blog or site, in ISO 639-1 format.
	 */
	function __construct(string|Uri $url, string $charset = "", array $languages = []) {
		$this->charset = $charset;
		$this->languages = $languages;
		$this->url = $url instanceof Uri ? $url : new Uri($url);
	}

	/**
	 * Returns a JSON representation of this object.
	 * @return \stdClass The JSON representation of this object.
	 */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;
		$map->blog = $this->url->toString();
		if ($this->charset) $map->blog_charset = $this->charset;
		if ($this->languages) $map->blog_lang = implode(",", $this->languages);
		return $map;
	}
}
