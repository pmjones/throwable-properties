<?php
namespace pmjones;

use Throwable;
use Exception;

/**
 * @phpstan-import-type TraceArrayWithoutArgs from ThrowableProperties
 * @phpstan-import-type ThrowablePropertiesAsObject from ThrowableProperties
 */
class ThrowablePropertiesTest extends \PHPUnit\Framework\TestCase
{
    public function testBasic() : void
    {
        try {
            $prev = new Exception('prev message');
            $line = __LINE__ + 1;
            throw new FakeException('fake message', 88, $prev);
        } catch (Throwable $e) {
            $t = new ThrowableProperties($e);
            $this->assertInstanceOf(ThrowableProperties::CLASS, $t);
            $this->assertSame(FakeException::CLASS, $t->class);
            $this->assertSame('fake message', $t->message);
            $this->assertSame(88, $t->code);
            $this->assertSame(__FILE__, $t->file);
            $this->assertSame($line, $t->line);
            $this->assertSame(['foo' => 'bar', 'baz' => 'dib'], $t->other);
            $this->assertNotEmpty($t->trace);
            foreach ($t->trace as $info) {
                // @phpstan-ignore-next-line
                $this->assertFalse(array_key_exists('args', $info));
            }
            $this->assertInstanceOf(ThrowableProperties::CLASS, $t->previous);
            $this->assertSame((string) $e, (string) $t);
        }
    }

    public function testJsonEncode() : void
    {
        try {
            $prev = new Exception('prev message');
            $line = __LINE__ + 1;
            throw new FakeException('fake message', 88, $prev);
        } catch (Throwable $e) {
            $this->assertSame('{}', json_encode($e));
            $t = new ThrowableProperties($e);

            /** @var ThrowablePropertiesAsObject */
            $j = json_decode((string) json_encode($t));
            $this->assertSame(FakeException::CLASS, $j->class);
            $this->assertSame('fake message', $j->message);
            $this->assertSame(88, $j->code);
            $this->assertSame(__FILE__, $j->file);
            $this->assertSame($line, $j->line);
            $this->assertEquals((object) ['foo' => 'bar', 'baz' => 'dib'], $j->other);
            $this->assertNotEmpty($j->trace);

            /** @var TraceArrayWithoutArgs $info */
            foreach ($j->trace as $info) {
                // @phpstan-ignore-next-line
                $this->assertFalse(property_exists($info, 'args'));
            }

            $this->assertNotEmpty($j->previous);
        }
    }
}
