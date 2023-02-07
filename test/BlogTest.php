<?php namespace akismet;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use function phpunit\expect\{expect, it};

/**
 * Tests the features of the {@see Blog} class.
 */
#[TestDox('akismet\Blog')]
class BlogTest extends TestCase {

	#[TestDox("::fromJson()")]
	function testFromJson(): void {
		it("should return an empty instance with an empty map", function() {
			$blog = Blog::fromJson(new \stdClass);
			expect($blog->charset)->to->be->empty;
			expect($blog->languages)->to->be->empty;
			expect($blog->url)->to->be->null;
		});

		it("should return an initialized instance with a non-empty map", function() {
			$blog = Blog::fromJson((object) [
				"blog" => "https://github.com/cedx/akismet.php",
				"blog_charset" => "UTF-8",
				"blog_lang" => "en, fr"
			]);

			expect($blog->charset)->to->equal("UTF-8");
			expect($blog->languages)->to->equal(["en", "fr"]);
			expect((string) $blog->url)->to->equal("https://github.com/cedx/akismet.php");
		});
	}

	#[TestDox("->jsonSerialize()")]
	function testJsonSerialize(): void {
		it("should return only the blog URL with a newly created instance", function() {
			$data = (new Blog("https://github.com/cedx/akismet.php"))->jsonSerialize();
			expect(get_object_vars($data))->to->have->lengthOf(1);
			expect($data->blog)->to->equal("https://github.com/cedx/akismet.php");
		});

		it("should return a non-empty map with a initialized instance", function() {
			$data = (new Blog(charset: "UTF-8", languages: ["en", "fr"], url: "https://github.com/cedx/akismet.php"))->jsonSerialize();
			expect(get_object_vars($data))->to->have->lengthOf(3);
			expect($data->blog)->to->equal("https://github.com/cedx/akismet.php");
			expect($data->blog_charset)->to->equal("UTF-8");
			expect($data->blog_lang)->to->equal("en,fr");
		});
	}
}
