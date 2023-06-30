# Change Log

## 2.0.0

Initial release.

This release is motivated by
<https://www.reddit.com/r/PHP/comments/yar3ip/how_to_json_encode_a_throwable/itvlcs8/>.
It differs from 1.0.0 in that:

- ThrowableProperties::jsonSerialize() is now typehinted as `array` instead of
  `mixed`

- Magic `__get()` is removed in favor of `public readonly` properties

- Local custom type aliases have been addded for static analysis

Although this is technically a backwards-compatibility breaking release
because of the typehint change from `mixed` to `array`, code using 1.0.0
should not require any modifications to use 2.0.0 (aside from possibly
changing typhints).
