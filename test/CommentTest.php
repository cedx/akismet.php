<?php
namespace akismet;
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `akismet\Comment` class.
 */
class CommentTest extends TestCase {

  /**
   * @test Comment::fromJSON
   */
  public function testFromJSON() {
    it('should return a null reference with a non-object value', function() {
      expect(Comment::fromJSON('foo'))->to->be->null;
    });

    it('should return an empty instance with an empty map', function() {
      $comment = Comment::fromJSON([]);
      expect($comment->getAuthor())->to->be->null;
      expect($comment->getContent())->to->be->empty;
      expect($comment->getDate())->to->be->null;
      expect($comment->getReferrer())->to->be->empty;
      expect($comment->getType())->to->be->empty;
    });

    it('should return an initialized instance with a non-empty map', function() {
      $comment = Comment::fromJSON([
        'comment_author' => 'Cédric Belin',
        'comment_content' => 'A user comment.',
        'comment_date_gmt' => '2000-01-01T00:00:00.000Z',
        'comment_type' => 'trackback',
        'referrer' => 'https://belin.io'
      ]);

      $author = $comment->getAuthor();
      expect($author)->to->be->instanceOf(Author::class);
      expect($author->getName())->to->equal('Cédric Belin');

      $date = $comment->getDate();
      expect($date)->to->be->instanceOf(\DateTime::class);
      expect($date->format('Y'))->to->equal(2000);

      expect($comment->getContent())->to->equal('A user comment.');
      expect($comment->getReferrer())->to->equal('https://belin.io');
      expect($comment->getType())->to->equal(CommentType::TRACKBACK);
    });
  }

  /**
   * @test Comment::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map with a newly created instance', function() {
      expect((new Comment())->jsonSerialize())->to->be->empty;
    });

    it('should return a non-empty map with a initialized instance', function() {
      $data = (new Comment((new Author())->setName('Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
        ->setDate(new \DateTime('2000-01-01T00:00:00.000Z'))
        ->setReferrer('https://belin.io')
        ->jsonSerialize();

      expect($data->comment_author)->to->equal('Cédric Belin');
      expect($data->comment_content)->to->equal('A user comment.');
      expect($data->comment_date_gmt)->to->equal('2000-01-01T00:00:00+00:00');
      expect($data->comment_type)->to->equal('pingback');
      expect($data->referrer)->to->equal('https://belin.io');
    });
  }

  /**
   * @test Comment::__toString
   */
  public function testToString() {
    $comment = (string) (new Comment((new Author())->setName('Cédric Belin'), 'A user comment.', CommentType::PINGBACK))
      ->setDate(new \DateTime('2000-01-01T00:00:00.000Z'))
      ->setReferrer('https://belin.io');

    it('should start with the class name', function() use ($comment) {
      expect($comment)->to->startWith('akismet\Comment {');
    });

    it('should contain the instance properties', function() use ($comment) {
      expect($comment)->to->contain('"comment_author":"Cédric Belin"')
        ->and->contain('"comment_content":"A user comment."')
        ->and->contain('"comment_type":"pingback"')
        ->and->contain('"comment_date_gmt":"2000-01-01T00:00:00+00:00"')
        ->and->contain('"referrer":"https://belin.io"');
    });
  }
}
