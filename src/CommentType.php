<?php declare(strict_types=1);
namespace Akismet;

/** Specifies the type of a comment. */
abstract class CommentType {

	/** A blog post. */
	const blogPost = "blog-post";

	/** A blog comment. */
	const comment = "comment";

	/** A contact form or feedback form submission. */
	const contactForm = "contact-form";

	/** A top-level forum post. */
	const forumPost = "forum-post";

	/** A [pingback](https://en.wikipedia.org/wiki/Pingback) post. */
	const pingback = "pingback";

	/** A [trackback](https://en.wikipedia.org/wiki/Trackback) post. */
	const trackback = "trackback";

	/** A [Twitter](https://twitter.com) message. */
	const tweet = "tweet";
}
