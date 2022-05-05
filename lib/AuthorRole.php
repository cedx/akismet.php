<?php declare(strict_types=1);
namespace Akismet;

/**
 * Specifies the role of an author.
 */
enum AuthorRole: string {

	/** The author is an administrator. */
	case administrator = "administrator";
}
