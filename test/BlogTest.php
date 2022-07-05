<?php namespace Akismet;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull};

/**
 * @testdox Akismet\Blog
 */
class BlogTest extends TestCase {

	/**
	 * @testdox ::fromJson()
	 */
	function testFromJson(): void {
		// It should return an empty instance with an empty map.
		$blog = Blog::fromJson(new \stdClass);
		assertThat($blog->charset, isEmpty());
		assertThat($blog->languages, isEmpty());
		assertThat($blog->url, isNull());

		// It should return an initialized instance with a non-empty map.
		$blog = Blog::fromJson((object) [
			"blog" => "https://github.com/cedx/akismet.php",
			"blog_charset" => "UTF-8",
			"blog_lang" => "en, fr"
		]);

		assertThat($blog->charset, equalTo("UTF-8"));
		assertThat($blog->languages, equalTo(["en", "fr"]));
		assertThat((string) $blog->url, equalTo("https://github.com/cedx/akismet.php"));
	}

	/**
	 * @testdox ->jsonSerialize()
	 */
	function testJsonSerialize(): void {
		// It should return only the blog URL with a newly created instance.
		$data = (new Blog("https://github.com/cedx/akismet.php"))->jsonSerialize();
		assertThat(get_object_vars($data), countOf(1));
		assertThat($data->blog, equalTo("https://github.com/cedx/akismet.php"));

		// It should return a non-empty map with a initialized instance.
		$data = (new Blog(charset: "UTF-8", languages: ["en", "fr"], url: "https://github.com/cedx/akismet.php"))->jsonSerialize();
		assertThat(get_object_vars($data), countOf(3));
		assertThat($data->blog, equalTo("https://github.com/cedx/akismet.php"));
		assertThat($data->blog_charset, equalTo("UTF-8"));
		assertThat($data->blog_lang, equalTo("en,fr"));
	}
}
