<?php declare(strict_types=1);
namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull};

/**
 * Tests the features of the {@see Blog} class.
 */
#[TestDox("Blog")]
final class BlogTest extends TestCase {

	#[Test]
	#[TestDox("fromJson()")]
	function fromJson(): void {
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

	#[Test]
	#[TestDox("jsonSerialize()")]
	function jsonSerialize(): void {
		// It should return only the blog URL with a newly created instance.
		$json = new Blog("https://github.com/cedx/akismet.php")->jsonSerialize();
		assertThat(get_object_vars($json), countOf(1));
		assertThat($json->blog, equalTo("https://github.com/cedx/akismet.php"));

		// It should return a non-empty map with a initialized instance.
		$json = new Blog(charset: "UTF-8", languages: ["en", "fr"], url: "https://github.com/cedx/akismet.php")->jsonSerialize();
		assertThat(get_object_vars($json), countOf(3));
		assertThat($json->blog, equalTo("https://github.com/cedx/akismet.php"));
		assertThat($json->blog_charset, equalTo("UTF-8"));
		assertThat($json->blog_lang, equalTo("en,fr"));
	}
}
