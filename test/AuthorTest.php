<?php
declare(strict_types=1);
namespace Akismet;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Akismet\Author` class.
 */
class AuthorTest extends TestCase {

  /**
   * @test Author::fromJson
   */
  public function testFromJson(): void {
    // It should return a null reference with a non-object value.
    assertThat(Author::fromJson('foo'), isNull());

    // It should return an empty instance with an empty map.
    $author = Author::fromJson([]);
    assertThat($author->getEmail(), isEmpty());
    assertThat($author->getIPAddress(), isEmpty());

    // It should return an initialized instance with a non-empty map.
    $author = Author::fromJson([
      'comment_author_email' => 'cedric@belin.io',
      'comment_author_url' => 'https://belin.io'
    ]);

    assertThat($author->getEmail(), equalTo('cedric@belin.io'));
    assertThat((string) $author->getUrl(), equalTo('https://belin.io'));
  }

  /**
   * @test Author::jsonSerialize
   */
  public function testJsonSerialize(): void {
    // It should return only the IP address and user agent with a newly created instance.
    $data = (new Author('127.0.0.1', 'Doom/6.6.6'))->jsonSerialize();
    assertThat(get_object_vars($data), countOf(2));
    assertThat($data->user_agent, equalTo('Doom/6.6.6'));
    assertThat($data->user_ip, equalTo('127.0.0.1'));

    // It should return a non-empty map with a initialized instance.
    $data = (new Author('192.168.0.1', 'Mozilla/5.0', 'Cédric Belin'))
      ->setEmail('cedric@belin.io')
      ->setUrl('https://belin.io')
      ->jsonSerialize();

    assertThat(get_object_vars($data), countOf(5));
    assertThat($data->comment_author, equalTo('Cédric Belin'));
    assertThat($data->comment_author_email, equalTo('cedric@belin.io'));
    assertThat($data->comment_author_url, equalTo('https://belin.io'));
    assertThat($data->user_agent, equalTo('Mozilla/5.0'));
    assertThat($data->user_ip, equalTo('192.168.0.1'));
  }

  /**
   * @test Author::__toString
   */
  public function testToString(): void {
    $author = (string) (new Author('127.0.0.1', 'Doom/6.6.6', 'Cédric Belin'))
      ->setEmail('cedric@belin.io')
      ->setUrl('https://belin.io');

    // It should start with the class name.
    assertThat($author, stringStartsWith('Akismet\Author {'));

    // It should contain the instance properties.
    assertThat($author, logicalAnd(
      stringContains('"comment_author":"Cédric Belin"'),
      stringContains('"comment_author_email":"cedric@belin.io"'),
      stringContains('"comment_author_url":"https://belin.io"'),
      stringContains('"user_agent":"Doom/6.6.6"'),
      stringContains('"user_ip":"127.0.0.1"')
    ));
  }
}
