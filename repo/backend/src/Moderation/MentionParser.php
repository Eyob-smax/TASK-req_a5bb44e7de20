<?php

declare(strict_types=1);

namespace CampusLearn\Moderation;

/**
 * Extracts @handle tokens from a body and resolves them into known user ids.
 *
 * A handle is the local-part of the account's email (characters before the @).
 * The resolver callable receives the list of distinct, lower-cased handles
 * observed in the body and returns a handle => user_id map. This keeps
 * MentionParser framework-agnostic (no DB dependency).
 */
final class MentionParser
{
    /** @var non-empty-string */
    private const HANDLE_PATTERN = '/(?<![A-Za-z0-9._-])@([A-Za-z0-9][A-Za-z0-9._-]{0,63})/u';

    /**
     * @param callable(array<int, string>): array<string, int> $resolver
     *        Given a list of unique lower-cased handles, return a handle => user_id map.
     *
     * @return array{user_ids: int[], handles: string[], unknown: string[]}
     */
    public function parse(string $body, callable $resolver): array
    {
        if ($body === '' || strpos($body, '@') === false) {
            return ['user_ids' => [], 'handles' => [], 'unknown' => []];
        }

        preg_match_all(self::HANDLE_PATTERN, $body, $matches);
        $handles = [];
        foreach ($matches[1] ?? [] as $raw) {
            $handle = mb_strtolower(trim((string) $raw));
            if ($handle === '') {
                continue;
            }
            $handles[$handle] = true;
        }
        if ($handles === []) {
            return ['user_ids' => [], 'handles' => [], 'unknown' => []];
        }

        $distinct = array_keys($handles);
        $resolved = $resolver($distinct);
        if (! is_array($resolved)) {
            $resolved = [];
        }

        $userIds = [];
        $unknown = [];
        foreach ($distinct as $handle) {
            if (isset($resolved[$handle]) && is_int($resolved[$handle])) {
                $userIds[$handle] = $resolved[$handle];
            } else {
                $unknown[] = $handle;
            }
        }

        return [
            'user_ids' => array_values(array_unique(array_values($userIds), SORT_NUMERIC)),
            'handles'  => array_keys($userIds),
            'unknown'  => $unknown,
        ];
    }
}
