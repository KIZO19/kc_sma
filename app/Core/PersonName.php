<?php

namespace App\Core;

final class PersonName
{
    public static function format(?array $person): string
    {
        $person ??= [];
        $parts = array_map(
            static fn (string $key): string => trim((string) ($person[$key] ?? '')),
            ['nom', 'postnom', 'prenom']
        );

        return implode(' ', array_filter($parts, static fn (string $part): bool => $part !== ''));
    }
}