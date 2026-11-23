<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class FriendAdded
{
    use Dispatchable;

    public function __construct(
        public readonly int $userId,
        public readonly int $friendId,
    ) {}
}
