<?php
namespace akismet;

/**
 * Specifies the type of a comment.
 */
final class CommentType {

  /**
   * @var string A standard comment.
   */
  const COMMENT = 'comment';

  /**
   * @var string A [pingback](https://en.wikipedia.org/wiki/Pingback) comment.
   */
  const PINGBACK = 'pingback';

  /**
   * @var string A [trackback](https://en.wikipedia.org/wiki/Trackback) comment.
   */
  const TRACKBACK = 'trackback';
}
