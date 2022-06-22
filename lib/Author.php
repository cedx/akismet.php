<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Represents the author of a comment.
 */
class Author implements \JsonSerializable {

	/**
	 * The author's mail address.
	 * @var string
	 */
	public string $email;

	/**
	 * The author's IP address.
	 * @var string
	 */
	public string $ipAddress;

	/**
	 * The author's name. If you set it to `"viagra-test-123"`, Akismet will always return `true`.
	 * @var string
	 */
	public string $name;

	/**
	 * The author's role. If you set it to `"administrator"`, Akismet will always return `false`.
	 * @var string
	 */
	public string $role;

	/**
	 * The URL of the author's website.
	 * @var UriInterface|null
	 */
	public ?UriInterface $url;

	/**
	 * The author's user agent, that is the string identifying the Web browser used to submit comments.
	 * @var string
	 */
	public string $userAgent;

	/**
	 * Creates a new author.
	 * @param string $ipAddress The author's IP address.
	 * @param string $email The author's mail address.
	 * @param string $name The author's name. If you set it to `"viagra-test-123"`, Akismet will always return `true`.
	 * @param string $role The author's role. If you set it to `"administrator"`, Akismet will always return `false`.
	 * @param string $url The URL of the author's website.
	 * @param string $userAgent The author's user agent, that is the string identifying the Web browser used to submit comments.
	 */
	function __construct(string $ipAddress, string $email = "", string $name = "", string $role = "", string $url = "", string $userAgent = "") {
		$this->email = $email;
		$this->ipAddress = $ipAddress ?: ($_SERVER["REMOTE_ADDR"] ?? "");
		$this->name = $name;
		$this->role = $role;
		$this->url = $url ? new Uri($url) : null;
		$this->userAgent = $userAgent ?: ($_SERVER["HTTP_USER_AGENT"] ?? "");
	}

	/**
	 * Creates a new author from the specified JSON object.
	 * @param object $map A JSON object representing an author.
	 * @return self The instance corresponding to the specified JSON object.
	 */
	static function fromJson(object $map): self {
		return new self(
			email: isset($map->comment_author_email) && is_string($map->comment_author_email) ? $map->comment_author_email : "",
			ipAddress: isset($map->user_ip) && is_string($map->user_ip) ? $map->user_ip : "",
			name: isset($map->comment_author) && is_string($map->comment_author) ? $map->comment_author : "",
			role: isset($map->user_role) && is_string($map->user_role) ? $map->user_role : "",
			url: isset($map->comment_author_url) && is_string($map->comment_author_url) ? $map->comment_author_url : "",
			userAgent: isset($map->user_agent) && is_string($map->user_agent) ? $map->user_agent : ""
		);
	}

	/**
	 * Converts this object to a map in JSON format.
	 * @return \stdClass The map in JSON format corresponding to this object.
	 */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;
		$map->user_ip = $this->ipAddress;
		if ($this->email) $map->comment_author_email = $this->email;
		if ($this->name) $map->comment_author = $this->name;
		if ($this->role) $map->user_role = $this->role;
		if ($this->url) $map->comment_author_url = (string) $this->url;
		if ($this->userAgent) $map->user_agent = $this->userAgent;
		return $map;
	}
}
