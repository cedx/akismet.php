<?php declare(strict_types=1);
namespace Akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/** Represents the author of a comment. */
class Author implements \JsonSerializable {

	/** The author's mail address. */
	private string $email = "";

	/** The author's IP address. */
	private string $ipAddress;

	/** The author's name. */
	private string $name = "";

	/** The author's role. */
	private string $role = "";

	/** The URL of the author's website. */
	private ?UriInterface $url = null;

	/** The author's user agent, that is the string identifying the Web browser used to submit comments. */
	private string $userAgent;

	/** Creates a new author. */
	function __construct(?string $ipAddress = null, ?string $userAgent = null) {
		$this->ipAddress = $ipAddress ?? ($_SERVER["REMOTE_ADDR"] ?? "");
		$this->userAgent = $userAgent ?? ($_SERVER["HTTP_USER_AGENT"] ?? "");
	}

	/** Creates a new author from the specified JSON object. */
	static function fromJson(object $map): self {
		$author = new self(
			isset($map->user_ip) && is_string($map->user_ip) ? $map->user_ip : "",
			isset($map->user_agent) && is_string($map->user_agent) ? $map->user_agent : ""
		);

		return $author
			->setEmail(isset($map->comment_author_email) && is_string($map->comment_author_email) ? $map->comment_author_email : "")
			->setName(isset($map->comment_author) && is_string($map->comment_author) ? $map->comment_author : "")
			->setRole(isset($map->user_role) && is_string($map->user_role) ? $map->user_role : "")
			->setUrl(isset($map->comment_author_url) && is_string($map->comment_author_url) ? new Uri($map->comment_author_url) : null);
	}

	/** Gets the author's mail address. */
	function getEmail(): string {
		return $this->email;
	}

	/** Gets the author's IP address. */
	function getIPAddress(): string {
		return $this->ipAddress;
	}

	/** Gets the author's name. */
	function getName(): string {
		return $this->name;
	}

	/** Gets the author's role. */
	function getRole(): string {
		return $this->role;
	}

	/** Gets the URL of the author's website. */
	function getUrl(): ?UriInterface {
		return $this->url;
	}

	/** Gets the author's user agent, that is the string identifying the Web browser used to submit comments. */
	function getUserAgent(): string {
		return $this->userAgent;
	}

	/** Converts this object to a map in JSON format. */
	function jsonSerialize(): \stdClass {
		$map = new \stdClass;
		$map->user_agent = $this->getUserAgent();
		$map->user_ip = $this->getIPAddress();

		if (mb_strlen($name = $this->getName())) $map->comment_author = $name;
		if (mb_strlen($email = $this->getEmail())) $map->comment_author_email = $email;
		if ($url = $this->getUrl()) $map->comment_author_url = (string) $url;
		if (mb_strlen($role = $this->getRole())) $map->user_role = $role;
		return $map;
	}

	/** Sets the author's mail address. If you set it to `"akismet-guaranteed-spam@example.com"`, Akismet will always return `true`. */
	function setEmail(string $value): static {
		$this->email = $value;
		return $this;
	}

	/** Sets the author's name. If you set it to `"viagra-test-123"`, Akismet will always return `true`. */
	function setName(string $value): static {
		$this->name = $value;
		return $this;
	}

	/** Sets the author's role. If you set it to `"administrator"`, Akismet will always return `false`. */
	function setRole(string $value): static {
		$this->role = $value;
		return $this;
	}

	/** Sets the URL of the author's website. */
	function setUrl(?UriInterface $value): static {
		$this->url = $value;
		return $this;
	}
}
