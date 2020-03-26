<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isNull, logicalNot};

/** @testdox Akismet\Comment */
class CommentTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    // It should return an empty instance with an empty map.
    $comment = Comment::fromJson(new \stdClass);
    assertThat($comment->getAuthor(), isNull());
    assertThat($comment->getContent(), isEmpty());
    assertThat($comment->getDate(), isNull());
    assertThat($comment->getReferrer(), isNull());
    assertThat($comment->getType(), isEmpty());

    // It should return an initialized instance with a non-empty map.
    $comment = Comment::fromJson((object) [
      'comment_author' => 'Cédric Belin',
      'comment_content' => 'A user comment.',
      'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
      'comment_type' => 'trackback',
      'referrer' => 'https://belin.io'
    ]);

    /** @var Author $author */
    $author = $comment->getAuthor();
    assertThat($author, logicalNot(isNull()));
    assertThat($author->getName(), equalTo('Cédric Belin'));

    /** @var \DateTimeInterface $date */
    $date = $comment->getDate();
    assertThat($date, logicalNot(isNull()));
    assertThat($date->format('Y'), equalTo(2000));

    assertThat($comment->getContent(), equalTo('A user comment.'));
    assertThat((string) $comment->getReferrer(), equalTo('https://belin.io'));
    assertThat($comment->getType(), equalTo(CommentType::trackback));
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    // It should return only the author info with a newly created instance.
    $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6')))->jsonSerialize();
    assertThat(get_object_vars($data), countOf(2));
    assertThat($data->user_agent, equalTo('Doom/6.6.6'));
    assertThat($data->user_ip, equalTo('127.0.0.1'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6', 'Cédric Belin'), 'A user comment.', CommentType::pingback))
      ->setDate(new \DateTimeImmutable('2000-01-01T00:00:00.000Z'))
      ->setReferrer(new Uri('https://belin.io'))
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
}
