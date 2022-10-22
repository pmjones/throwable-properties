<?php
namespace pmjones;

use JsonSerializable;
use ReflectionClass;
use Stringable;
use Throwable;

/**
 * @property-read string $class;
 * @property-read string $message;
 * @property-read string $string;
 * @property-read int $code;
 * @property-read string $file;
 * @property-read int $line;
 * @property-read array<string, mixed> $other;
 * @property-read array<int, mixed> $trace;
 * @property-read ?ThrowableProperties $previous;
 */
class ThrowableProperties implements JsonSerializable, Stringable
{
    protected string $class;

    protected string $message;

    protected string $string;

    protected int $code;

    protected string $file;

    protected int $line;

    /** @var array<string, mixed> */
    protected array $other;

    /** @var array<int, mixed> */
    protected array $trace;

    protected ?ThrowableProperties $previous;

    public function __construct(Throwable $e)
    {
        $this->class = get_class($e);
        $this->message = $e->getMessage();
        $this->string = (string) $e;
        $this->code = $e->getCode();
        $this->file = $e->getFile();
        $this->line = $e->getLine();
        $this->other = $this->getOther($e);
        $this->trace = $this->getTrace($e);
        $this->previous = $e->getPrevious() === null
            ? null
            : new self($e->getPrevious());
    }

    public function __get(string $prop) : mixed
    {
        return $this->$prop;
    }

    public function __toString() : string
    {
        return $this->string;
    }

    public function jsonSerialize() : mixed
    {
        return $this->asArray();
    }

    /** @return array<string, mixed> */
    public function asArray() : array
    {
        return get_object_vars($this);
    }

    /** @return array<string, mixed> */
    protected function getOther(Throwable $e) : array
    {
        $other = [];

        $skip = [
            'message',
            'string',
            'code',
            'file',
            'line',
            'trace',
            'previous',
        ];

        $rc = new ReflectionClass($e);

        foreach ($rc->getProperties() as $rp) {
            $prop = $rp->getName();

            if (in_array($prop, $skip)) {
                continue;
            }

            $rp->setAccessible(true);
            $other[$prop] = $rp->getValue($e);
        }

        return $other;
    }

    /** @return array<int, mixed> */
    protected function getTrace(Throwable $e) : array
    {
        $trace = [];

        foreach ($e->getTrace() as $info) {
            unset($info['args']);
            $trace[] = $info;
        }

        return $trace;
    }
}
