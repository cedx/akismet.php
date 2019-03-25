<?php declare(strict_types=1);
namespace Akismet;

/**
 * Specifies the type of a comment.
 */
abstract class CommentType {

  /**
   * @var string A blog post.
   */
  const BLOG_POST = 'blog-post';

  /**
   * @var string A blog comment.
   */
  const COMMENT = 'comment';

  /**
   * @var string A contact form or feedback form submission.
   */
  const CONTACT_FORM = 'contact-form';

  /**
   * @var string A top-level forum post.
   */
  const FORUM_POST = 'forum-post';

  /**
   * @var string A message sent between just a few users.
   */
  const MESSAGE = 'message';

  /**
   * @var string A [pingback](https://en.wikipedia.org/wiki/Pingback) post.
   */
  const PINGBACK = 'pingback';

  /**
   * @var string A reply to a top-level forum post.
   */
  const REPLY = 'reply';

  /**
   * @var string A new user account.
   */
  const SIGNUP = 'signup';

  /**
   * @var string A [trackback](https://en.wikipedia.org/wiki/Trackback) post.
   */
  const TRACKBACK = 'trackback';

  /**
   * @var string A [Twitter](https://twitter.com) message.
   */
  const TWEET = 'tweet';
}
