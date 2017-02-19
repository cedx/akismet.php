<?php
/**
 * Implementation of the `akismet\test\CommentTest` class.
 */
namespace akismet\test;

use akismet\{Author, Comment, CommentType};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \akismet\Comment
 */
class CommentTest extends TestCase {

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    // Should return a null reference with a non-object value.
    $this->assertNull(Comment::fromJSON('foo'));

    // Should return an empty instance with an empty map.
    $comment = Comment::fromJSON([]);
    $this->assertNull($comment->getAuthor());
    $this->assertEmpty($comment->getContent());
    $this->assertNull($comment->getDate());
    $this->assertEmpty($comment->getReferrer());
    $this->assertEmpty($comment->getType());

    // Should return an initialized instance with a non-empty map.
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
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return an empty map with a newly created instance.
    $data = (new Comment())->jsonSerialize();
    $this->assertEmpty(get_object_vars($data));

    // Should return a non-empty map with a initialized instance.
    $data = (new Comment((new Author())->setName('Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
      ->setReferrer('https://belin.io')
      ->jsonSerialize();

    $this->assertEquals('Cédric Belin', $data->comment_author);
    $this->assertEquals('A user comment.', $data->comment_content);
    $this->assertEquals('pingback', $data->comment_type);
    $this->assertEquals('https://belin.io', $data->referrer);
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $comment = (string) (new Comment((new Author())->setName('Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
      ->setReferrer('https://belin.io');

    // Should start with the class name.
    $this->assertStringStartsWith('akismet\Comment {', $comment);

    // Should contain the instance properties.
    $this->assertContains('"comment_author":"Cédric Belin"', $comment);
    $this->assertContains('"comment_content":"A user comment."', $comment);
    $this->assertContains('"comment_type":"pingback"', $comment);
    $this->assertContains('"referrer":"https://belin.io"', $comment);
  }
}
