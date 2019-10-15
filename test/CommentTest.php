<?php declare(strict_types=1);
namespace Akismet;

use function PHPUnit\Expect\{expect, it};
use GuzzleHttp\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};

/** @testdox Akismet\Comment */
class CommentTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    it('should return an empty instance with an empty map', function() {
      $comment = Comment::fromJson(new \stdClass);
      expect($comment->getAuthor())->to->be->null;
      expect($comment->getContent())->to->be->empty;
      expect($comment->getDate())->to->be->null;
      expect($comment->getReferrer())->to->be->null;
      expect($comment->getType())->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $comment = Comment::fromJson((object) [
        'comment_author' => 'Cédric Belin',
        'comment_content' => 'A user comment.',
        'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
        'comment_type' => 'trackback',
        'referrer' => 'https://belin.io'
      ]);

      /** @var Author $author */
      $author = $comment->getAuthor();
      expect($author)->to->not->be->null;
      expect($author->getName())->to->equal('Cédric Belin');

      /** @var \DateTime $date */
      $date = $comment->getDate();
      expect($date)->to->not->be->null;
      expect($date->format('Y'))->to->equal(2000);

      expect($comment->getContent())->to->equal('A user comment.');
      expect((string) $comment->getReferrer())->to->equal('https://belin.io');
      expect($comment->getType())->to->equal(CommentType::trackback);
    });
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return only the author info with a newly created instance', function() {
      $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6')))->jsonSerialize();
      expect(get_object_vars($data))->to->have->lengthOf(2);
      expect($data->user_agent)->to->equal('Doom/6.6.6');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Comment(new Author('127.0.0.1', 'Doom/6.6.6', 'Cédric Belin'), 'A user comment.', CommentType::pingback))
        ->setDate(new \DateTime('2000-01-01T00:00:00.000Z'))
        ->setReferrer(new Uri('https://belin.io'))
        ->jsonSerialize();

      expect(get_object_vars($data))->to->have->lengthOf(7);
      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_content)->to->equal('A user comment.');
      expect($data->comment_date_gmt)->to->equal('2000-01-01T00:00:00+00:00');
      expect($data->comment_type)->to->equal('pingback');
      expect($data->referrer)->to->equal('https://belin.io');
      expect($data->user_agent)->to->equal('Doom/6.6.6');
      expect($data->user_ip)->to->equal('127.0.0.1');
    });
  }
}
