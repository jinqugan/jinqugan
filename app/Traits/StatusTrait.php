<?php

namespace App\Traits;

use App\Models\Status;

trait StatusTrait {
    /**
     * Generate phone international format (+60108888888).
     *
     * @return collection
     */
    public function getStatuses($type)
    {
        return Status::where('type', $type)->get();
    }
}
