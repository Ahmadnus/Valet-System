<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class hostsServices
{
    /**
     * Create a new class instance.
     */
    public function getAgentsForHost()
    {



        $valetHost = Auth::user();

        $locations = Location::where('host_id', $valetHost->id)
            ->with(['agents', 'parkingSlots'])
            ->get();


        // Flatten all agents from all locations


        return $locations;
    }
}
