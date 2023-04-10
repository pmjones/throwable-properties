<?php
namespace pmjones;

use JsonSerializable;
use ReflectionClass;
use Stringable;
use Throwable;

class ThrowableProperties implements JsonSerializable, Stringable
{
    /**
     * @var class-string
     */
    public readonly string $class;

    public readonly string $message;

    public readonly string $string;

    public readonly int $code;

    public readonly string $file;

    public readonly int $line;

    /**
     * @var array<string, mixed>
     */
    public readonly array $other;

    /**
     * @var array<
     *  int,
     *  array{
     *      file: string,
     *      line: int,
     *      function: string,
     *      class: string,
     *      type: string
     *  }
     * >
     */
    public readonly array $trace;

    public readonly ?ThrowableProperties $previous;

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

    /**
     * @return array{
     *  class: string,
     *  message: string,
     *  string: string,
     *  code: int,
     *  file: string,
     *  line: int,
     *  other: mixed[],
     *  trace: string[],
     *  previous: ThrowableProperties|null
     * }
     */
    public function jsonSerialize() : array
    {
        return $this->asArray();
    }

    /**
     * @return array{
     *  class: string,
     *  message: string,
     *  string: string,
     *  code: int,
     *  file: string,
     *  line: int,
     *  other: mixed[],
     *  trace: string[],
     *  previous: ThrowableProperties|null
     * }
     */
    public function asArray() : array
    {
        /**
         * @var array{
         *  class: string,
         *  message: string,
         *  string: string,
         *  code: int,
         *  file: string,
         *  line: int,
         *  other: mixed[],
         *  trace: string[],
         *  previous: ThrowableProperties|null
         * }
         */
        return get_object_vars($this);
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<
     *  int,
     *  array{
     *      file: string,
     *      line: int,
     *      function: string,
     *      class: string,
     *      type: string
     *  }
     * >
     */
    protected function getTrace(Throwable $e) : array
    {
        $trace = [];

        /**
         * @var array{
         *  file: string,
         *  line: int,
         *  function: string,
         *  class: string,
         *  type: string,
         *  args: mixed[]
         * } $info
         */
        foreach ($e->getTrace() as $info) {
            unset($info['args']);
            $trace[] = $info;
        }

        return $trace;
    }
}
