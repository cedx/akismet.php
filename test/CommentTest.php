<?php
/**
 * Implementation of the `akismet\test\CommentTest` class.
 */
namespace akismet\test;
use akismet\{Author, Comment, CommentType};

/**
 * Tests the features of the `akismet\Comment` class.
 */
class CommentTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `Comment` constructor.
   */
  public function testConstructor() {
    $comment = new Comment([
      'content' => 'Hello World!',
      'date' => time(),
      'referrer' => 'https://github.com/cedx/akismet.php'
    ]);

    $this->assertEquals('Hello World!', $comment->getContent());
    $this->assertInstanceOf(\DateTime::class, $comment->getDate());
    $this->assertEquals('https://github.com/cedx/akismet.php', $comment->getReferrer());
  }

  /**
   * Tests the `Comment::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(Comment::fromJSON('foo'));

    $comment = Comment::fromJSON([]);
    $this->assertNull($comment->getAuthor());
    $this->assertEquals(0, mb_strlen($comment->getContent()));
    $this->assertNull($comment->getDate());
    $this->assertEquals(0, mb_strlen($comment->getReferrer()));
    $this->assertEquals(0, mb_strlen($comment->getType()));

    $comment = Comment::fromJSON([
      'comment_author' => 'Cédric Belin',
      'comment_content' => 'A user comment.',
      'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
      'comment_type' => 'trackback',
      'referrer' => 'https://belin.io'
    ]);

    $author = $comment->getAuthor();
    $this->assertInstanceOf(Author::class, $author);
    $this->assertEquals('Cédric Belin', $author->getName());

    $date = $comment->getDate();
    $this->assertInstanceOf(\DateTime::class, $date);
    $this->assertEquals(2000, $date->format('Y'));

    $this->assertEquals('A user comment.', $comment->getContent());
    $this->assertEquals('https://belin.io', $comment->getReferrer());
    $this->assertEquals(CommentType::TRACKBACK, $comment->getType());
  }

  /**
   * Tests the `Comment::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $data = (new Comment())->jsonSerialize();
    $this->assertEquals(0, count((array) $data));

    $data = (new Comment([
      'author' => new Author(['name' => 'Cédric Belin']),
      'content' => 'A user comment.',
      'referrer' => 'https://belin.io',
      'type' => CommentType::PINGBACK
    ]))->jsonSerialize();

    $this->assertEquals('Cédric Belin', $data->comment_author);
    $this->assertEquals('A user comment.', $data->comment_content);
    $this->assertEquals('pingback', $data->comment_type);
    $this->assertEquals('https://belin.io', $data->referrer);
  }
}
