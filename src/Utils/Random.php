<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
namespace FapiMember\Utils;


use InvalidArgumentException;

/**
 * Secure random string generator.
 */
final class Random
{

	/**
	 * Generates a random string of given length from characters specified in second argument.
	 * Supports intervals, such as `0-9` or `A-Z`.
	 *
	 * @param int $length
	 * @param string $charlist
	 * @return string
	 */
	public static function generate($length = 10, $charlist = '0-9a-z')
	{
		$charlist = count_chars(preg_replace_callback('#.-.#', static function (array $m) {
			return implode('', range($m[0][0], $m[0][2]));
		}, $charlist), 3);
		$chLen = strlen($charlist);

		if ($length < 1) {
			throw new InvalidArgumentException('Length must be greater than zero.');
		}

		if ($chLen < 2) {
			throw new InvalidArgumentException('Character list must contain at least two chars.');
		}

		$res = '';
		for ($i = 0; $i < $length; $i++) {
			$res .= $charlist[random_int(0, $chLen - 1)];
		}
		return $res;
	}
}
