<?php

use Illuminate\Support\Facades\Broadcast;

// Private channel per branch for POS updates
Broadcast::channel('branch.{branchId}', function ($user, $branchId) {
    return (int) $user->branch_id === (int) $branchId;
});

