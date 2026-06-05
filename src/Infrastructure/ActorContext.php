<?php

declare(strict_types=1);

namespace IdM\Infrastructure;

final class ActorContext
{
    public function __construct(
        public readonly string $actorType,
        public readonly ?string $actorId
    ) {
    }

    public static function fromHeaders(?string $actorType, ?string $actorId): self
    {
        $normalizedType = strtoupper(trim((string) $actorType));
        if (!in_array($normalizedType, ['USER', 'SERVICE_ACCOUNT', 'SYSTEM'], true)) {
            $normalizedType = 'SYSTEM';
        }

        $normalizedId = null;
        if ($actorId !== null && Uuid::isValid($actorId)) {
            $normalizedId = strtolower($actorId);
        }

        if ($normalizedType !== 'SYSTEM' && $normalizedId === null) {
            $normalizedType = 'SYSTEM';
        }

        return new self($normalizedType, $normalizedId);
    }
}
