<?php declare(strict_types=1);
namespace Belin\Akismet;

/**
 * Specifies the type of a comment.
 */
enum CommentType: string {

	/**
	 * A blog post.
	 */
	case BlogPost = "blog-post";

	/**
	 * A blog comment.
	 */
	case Comment = "comment";

	/**
	 * A contact form or feedback form submission.
	 */
	case ContactForm = "contact-form";

	/**
	 * A top-level forum post.
	 */
	case ForumPost = "forum-post";

	/**
	 * A message sent between just a few users.
	 */
	case Message = "message";

	/**
	 * A reply to a top-level forum post.
	 */
	case Reply = "reply";

	/**
	 * A new user account.
	 */
	case Signup = "signup";
}
