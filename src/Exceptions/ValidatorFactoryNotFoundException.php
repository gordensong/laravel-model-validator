<?php


namespace GordenSong\Exceptions;


use Throwable;

class ValidatorFactoryNotFoundException extends \Exception
{
	public function __construct($message = "Validator Factory Not Found", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}