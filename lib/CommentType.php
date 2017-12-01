<?php
declare(strict_types=1);
namespace Akismet;

/**
 * Specifies the type of a comment.
 */
abstract class CommentType {

  /**
   * @var string A blog post.
   */
  public const BLOG_POST = 'blog-post';

  /**
   * @var string A blog comment.
   */
  public const COMMENT = 'comment';

  /**
   * @var string A contact form or feedback form submission.
   */
  public const CONTACT_FORM = 'contact-form';

  /**
   * @var string A top-level forum post.
   */
  public const FORUM_POST = 'forum-post';

  /**
   * @var string A message sent between just a few users.
   */
  public const MESSAGE = 'message';

  /**
   * @var string A [pingback](https://en.wikipedia.org/wiki/Pingback) post.
   */
  public const PINGBACK = 'pingback';

  /**
   * @var string A reply to a top-level forum post.
   */
  public const REPLY = 'reply';

  /**
   * @var string A new user account.
   */
  public const SIGNUP = 'signup';

  /**
   * @var string A [trackback](https://en.wikipedia.org/wiki/Trackback) post.
   */
  public const TRACKBACK = 'trackback';

  /**
   * @var string A [Twitter](https://twitter.com) message.
   */
  public const TWEET = 'tweet';
}
