<?php declare(strict_types=1);
namespace Akismet;

/** Specifies the type of a comment. */
abstract class CommentType {

	/** @var string A blog post. */
	const blogPost = "blog-post";

	/** @var string A blog comment. */
	const comment = "comment";

	/** @var string A contact form or feedback form submission. */
	const contactForm = "contact-form";

	/** @var string A top-level forum post. */
	const forumPost = "forum-post";

	/** @var string A [pingback](https://en.wikipedia.org/wiki/Pingback) post. */
	const pingback = "pingback";

	/** @var string A [trackback](https://en.wikipedia.org/wiki/Trackback) post. */
	const trackback = "trackback";

	/** @var string A [Twitter](https://twitter.com) message. */
	const tweet = "tweet";
}
