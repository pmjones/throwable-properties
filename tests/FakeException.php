<?php
namespace pmjones;

class FakeException extends \Exception
{
	protected string $foo = 'bar';
	protected string $baz = 'dib';
}
