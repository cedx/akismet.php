<?php
/**
 * Implementation of the `akismet\test\CommentTest` class.
 */
namespace akismet\test;

use akismet\{Author, Comment, CommentType};
use Codeception\{Specify};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \akismet\Comment
 */
class CommentTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(Comment::fromJSON('foo'));
    });

    $this->specify('should return an empty instance with an empty map', function() {
      $comment = Comment::fromJSON([]);
      $this->assertNull($comment->getAuthor());
      $this->assertEmpty($comment->getContent());
      $this->assertNull($comment->getDate());
      $this->assertEmpty($comment->getReferrer());
      $this->assertEmpty($comment->getType());
    });

    $this->specify('should return an initialized instance with a non-empty map', function() {
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
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return an empty map with a newly created instance', function() {
      $data = (new Comment())->jsonSerialize();
      $this->assertEmpty(get_object_vars($data));
    });

    $this->specify('should return a non-empty map with a initialized instance', function() {
      $data = (new Comment((new Author())->setName('Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
        ->setReferrer('https://belin.io')
        ->jsonSerialize();

      $this->assertEquals('Cédric Belin', $data->comment_author);
      $this->assertEquals('A user comment.', $data->comment_content);
      $this->assertEquals('pingback', $data->comment_type);
      $this->assertEquals('https://belin.io', $data->referrer);
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $comment = (string) (new Comment((new Author())->setName('Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
      ->setReferrer('https://belin.io');

    $this->specify('should start with the class name', function() use ($comment) {
      $this->assertStringStartsWith('akismet\Comment {', $comment);
    });

    $this->specify('should contain the instance properties', function() use ($comment) {
      $this->assertContains('"comment_author":"Cédric Belin"', $comment);
      $this->assertContains('"comment_content":"A user comment."', $comment);
      $this->assertContains('"comment_type":"pingback"', $comment);
      $this->assertContains('"referrer":"https://belin.io"', $comment);
    });
  }
}
