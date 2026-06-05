<?php

declare(strict_types=1);

use IdM\Http\Response;

test('Error response includes correlationId and errorCode', function (): void {
    $response = Response::error('NOT_FOUND', 'missing', 404, '11111111-1111-4111-8111-111111111111');
    if ($response->statusCode !== 404) {
        throw new RuntimeException('unexpected status code');
    }

    $body = $response->body;
    if (!is_array($body) || ($body['error']['errorCode'] ?? '') !== 'NOT_FOUND') {
        throw new RuntimeException('error payload invalid');
    }
    if (($body['error']['correlationId'] ?? '') !== '11111111-1111-4111-8111-111111111111') {
        throw new RuntimeException('correlation id missing from error payload');
    }
});
