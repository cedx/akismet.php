<?php namespace akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Represents the author of a comment.
 */
class Author implements \JsonSerializable {

	/**
	 * The author's mail address.
	 */
	public string $email;

	/**
	 * The author's IP address.
	 */
	public string $ipAddress;

	/**
	 * The author's name. If you set it to `"viagra-test-123"`, Akismet will always return `true`.
	 */
	public string $name;

	/**
	 * The author's role. If you set it to `"administrator"`, Akismet will always return `false`.
	 */
	public string $role;

	/**
	 * The URL of the author's website.
	 */
	public ?UriInterface $url;

	/**
	 * The author's user agent, that is the string identifying the Web browser used to submit comments.
	 */
	public string $userAgent;

	/**
	 * Creates a new author.
	 * @param string $ipAddress The author's IP address.
	 * @param string $name The author's name. If you set it to `"viagra-test-123"`, Akismet will always return `true`.
	 * @param string $email The author's mail address.
	 * @param string $url The URL of the author's website.
	 * @param string $role The author's role. If you set it to `"administrator"`, Akismet will always return `false`.
	 * @param string $userAgent The author's user agent, that is the string identifying the Web browser used to submit comments.
	 */
	function __construct(string $ipAddress = "", string $name = "", string $email = "", string $url = "", string $role = "", string $userAgent = "") {
		$this->email = $email;
		$this->ipAddress = $ipAddress ?: ($_SERVER["REMOTE_ADDR"] ?? "");
		$this->name = $name;
		$this->role = $role;
		$this->url = $url ? new Uri($url) : null;
		$this->userAgent = $userAgent ?: ($_SERVER["HTTP_USER_AGENT"] ?? "");
	}

	/**
	 * Creates a new author from the specified JSON object.
	 * @param object $json A JSON object representing an author.
	 * @return self The instance corresponding to the specified JSON object.
	 */
	static function fromJson(object $json): self {
		return new self(
			email: isset($json->comment_author_email) && is_string($json->comment_author_email) ? $json->comment_author_email : "",
			ipAddress: isset($json->user_ip) && is_string($json->user_ip) ? $json->user_ip : "",
			name: isset($json->comment_author) && is_string($json->comment_author) ? $json->comment_author : "",
			role: isset($json->user_role) && is_string($json->user_role) ? $json->user_role : "",
			url: isset($json->comment_author_url) && is_string($json->comment_author_url) ? $json->comment_author_url : "",
			userAgent: isset($json->user_agent) && is_string($json->user_agent) ? $json->user_agent : ""
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
