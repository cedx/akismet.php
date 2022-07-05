<?php namespace Akismet;

/**
 * Specifies the type of a comment.
 */
enum CommentType: string {

	/** A blog post. */
	case blogPost = "blog-post";

	/** A blog comment. */
	case comment = "comment";

	/** A contact form or feedback form submission. */
	case contactForm = "contact-form";

	/** A top-level forum post. */
	case forumPost = "forum-post";

	/** A message sent between just a few users. */
	case message = "message";

	/** A reply to a top-level forum post. */
	case reply = "reply";

	/** A new user account. */
	case signup = "signup";
}
