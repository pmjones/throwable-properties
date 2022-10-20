# ThrowableProperties

When using `json_encode()` with _Throwable_ objects, such as _Error_ and
_Exception_, the result is an empty JSON object.

```php
try {
    // ...
} catch (Throwable $e) {
    echo json_encode($e); // '{}'
}
```

To convert a _Throwable_ into a form suitable for `json_encode()`, instantiate a
new _ThrowableProperties_ with it:

```php
use pmjones\ThrowableProperties;

try {
    // ...
} catch (Throwable $e) {
    $t = new ThrowableProperties($e);
    echo json_encode($t); // '{"class": ... }'
}
```

_ThrowableProperties_ is essentially a Data Transfer Object composed of these
properties:

- `string $class`: The _Throwable_ class.

- `string $message`: The _Throwable_ message.

- `string $string`: A string representation of the _Throwable_.

- `int $code`: The _Throwable_ code.

- `string $file`: The filename where the _Throwable_ was
  created.

- `int $line`: The line where the _Throwable_ was created.

- `array $other`: All other properties of the _Throwable_ (if
  any).

- `array $trace`: The stack trace array, with all `'args'`
  elements removed.

- `?ThrowableProperties $previous`: The previously thrown
  exception, if any, represented as a _ThrowableProperties_ instance.

_ThrowableProperties_ is _Stringable_ to the string form of the original
_Throwable_.

```php
try {
    // ...
} catch (Throwable $e) {
    $t = new ThrowableProperties($e);
    assert((string) $e === (string) $t));
}
```

If you just want the _ThrowableProperties_ values, you can call `asArray()`:

```php
try {
    // ...
} catch (Throwable $e) {
    $t = new ThrowableProperties($e);
    $a = $t->asArray(); // do something with the array
}
```

Finally, you can use a _ThrowableProperty_ inside your own _Throwable_
`jsonSerialize()` methods:

```php
use pmjones\ThrowableProperties;

class MyException extends \Exception implements JsonSerializable
{
    public function jsonSerialize() : mixed
    {
        return new ThrowableProperties($this);
    }
}
```


## Comparable Libraries

Cees-Jan Kiewet has a comparable offering called
[php-json-throwable](https://github.com/WyriHaximus/php-json-throwable),
using functions to encode a _Throwable_ instead of a standalone DTO. It works
on PHP 7.4 and later, whereas this library works only on PHP 8.0 and later.
