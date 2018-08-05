<?php
declare(strict_types=1);
namespace Akismet;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Akismet\Comment` class.
 */
class CommentTest extends TestCase {

  /**
   * @test Comment::fromJson
   */
  public function testFromJson(): void {
    // It should return a null reference with a non-object value.
    assertThat(Comment::fromJson('foo'), isNull());

    // It should return an empty instance with an empty map.
    $comment = Comment::fromJson([]);
    assertThat($comment->getAuthor(), isNull());
    assertThat($comment->getContent(), isEmpty());
    assertThat($comment->getDate(), isNull());
    assertThat($comment->getReferrer(), isNull());
    assertThat($comment->getType(), isEmpty());

    // It should return an initialized instance with a non-empty map.
    $comment = Comment::fromJson([
      'comment_author' => 'Cédric Belin',
      'comment_content' => 'A user comment.',
      'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
      'comment_type' => 'trackback',
      'referrer' => 'https://belin.io'
    ]);

    $author = $comment->getAuthor();
    assertThat($author, isInstanceOf(Author::class));
    assertThat($author->getName(), equalTo('Cédric Belin'));

    $date = $comment->getDate();
    assertThat($date, isInstanceOf(\DateTime::class));
    assertThat($date->format('Y'), equalTo(2000));

    assertThat($comment->getContent(), equalTo('A user comment.'));
    assertThat((string) $comment->getReferrer(), equalTo('https://belin.io'));
    assertThat($comment->getType(), equalTo(CommentType::TRACKBACK));
  }

  /**
   * @test Comment::jsonSerialize
   */
  public function testJsonSerialize(): void {
    // It should return only the author info with a newly created instance.
    $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6')))->jsonSerialize();
    assertThat(get_object_vars($data), countOf(2));
    assertThat($data->user_agent, equalTo('Doom/6.6.6'));
    assertThat($data->user_ip, equalTo('127.0.0.1'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6', 'Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
      ->setDate('2000-01-01T00:00:00.000Z')
      ->setReferrer('https://belin.io')
      ->jsonSerialize();

    assertThat(get_object_vars($data), countOf(7));
    assertThat($data->comment_author, equalTo('Cédric Belin'));
    assertThat($data->comment_content, equalTo('A user comment.'));
    assertThat($data->comment_date_gmt, equalTo('2000-01-01T00:00:00+00:00'));
    assertThat($data->comment_type, equalTo('pingback'));
    assertThat($data->referrer, equalTo('https://belin.io'));
    assertThat($data->user_agent, equalTo('Doom/6.6.6'));
    assertThat($data->user_ip, equalTo('127.0.0.1'));
  }

  /**
   * @test Comment::__toString
   */
  public function testToString(): void {
    $comment = (string) (new Comment(new Author('127.0.0.1', 'Doom/6.6.6', 'Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
      ->setDate('2000-01-01T00:00:00.000Z')
      ->setReferrer('https://belin.io');

    // It should start with the class name.
    assertThat($comment, stringStartsWith('Akismet\Comment {'));

    // It should contain the instance properties.
    assertThat($comment, logicalAnd(
      stringContains('"comment_author":"Cédric Belin"'),
      stringContains('"comment_content":"A user comment."'),
      stringContains('"comment_type":"pingback"'),
      stringContains('"comment_date_gmt":"2000-01-01T00:00:00+00:00"'),
      stringContains('"referrer":"https://belin.io"'),
      stringContains('"user_agent":"Doom/6.6.6"'),
      stringContains('"user_ip":"127.0.0.1"')
    ));
  }
}
