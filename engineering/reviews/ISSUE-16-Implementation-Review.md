# SIF #16 — generated HTTP bootstrap

The generated application now enables a Foundation-owned `HttpRuntimePlan`. Bootstrap composes HTTP with its logger and error handler, with default production-safe error handling when no custom plan is supplied. Explicit runtime injection and non-HTTP applications retain their behavior. Supplying both composition modes fails immediately.

The template accepts deployment-provided environment variables without requiring a `.env` file. Consumers register routes, handlers and middleware through the existing contracts. No consumer-level runtime reconstruction is required.

Validation on PHP 8.2.32: `composer quality` passes (1,422 tests, 3,761 assertions, two existing skips; PHPStan level 8 zero errors; configured style check passes). The style configuration excludes Foundation; targeted formatting must also be checked for changed Foundation files. Integration tests materialize the generated application, require its bootstrap and execute its native front controller in a separate PHP process. Additional tests cover consumer routing, 405, safe 500, explicit injection compatibility and conflicting composition inputs.

This review covers WP-222 template and WP-223 runtime integration repair; it does not claim deployment verification for any consumer.
