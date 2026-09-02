<?php

namespace App\Support\Authorization;

final class PermissionRegistry
{
    public const PANEL_ACCESS = 'admin.access';

    /** @var list<string> */
    public const RESOURCES = [
        'users',
        'products',
        'categories',
        'articles',
        'settings',
        'orders',
        'payment_intentions',
        'payment_transactions',
        'payment_refunds',
        'payment_webhooks',
    ];

    /** @var list<string> */
    public const ACTIONS = [
        'view_any',
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'force_delete',
        'replicate',
        'reorder',
    ];

    /** @return list<string> */
    public static function all(): array
    {
        $permissions = [self::PANEL_ACCESS];

        foreach (self::RESOURCES as $resource) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = self::for($resource, $action);
            }
        }

        return $permissions;
    }

    public static function for(string $resource, string $action): string
    {
        return "{$resource}.{$action}";
    }
}
