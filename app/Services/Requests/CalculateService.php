<?php

namespace App\Services\Requests;

use App\Http\Requests\Requests\ValetTimeRequest;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\Request as UserRequest;
use App\Models\ValetTime ;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalculateService
{
    /**
     * Create a new class instance.
     */


     public function parkingTime(array $data, $req)
     {

         $userId = Auth::id();

         // Get the user request
         $userRequest = UserRequest::where('user_id', $userId)->firstOrFail();

         // Get the specific request instance
         $request = UserRequest::findOrFail($req);

         // Ensure completed_time and in_progress_time are set
         if (!$request->completed_time || !$request->in_progress_time) {
             return response()->json([
                 'error' => 'Time calculation cannot be performed. Missing timestamps.'
             ], 400);
         }



         $locationid=UserRequest::where('user_id', $userId)->first();

$cost=Location::where('id',$locationid->location_id)->with("requests")->first();
         // Calculate the time difference (ensure it's always positive)

         $timeDifference =UserRequest::where('user_id',Auth::id())->first('total_hours');








         // Calculate the time difference (ensure it's always positive)
         $price=$timeDifference->total_hours* $cost->cost;


         // Create ValetTime record
         $valetTime = ValetTime::create([
             'request_id' => $req,
             'user_id' => $userId,
             'hours' => $timeDifference->total_hours,
             'price'=>$price
         ]);

return  $valetTime;
     }
     public function calCost(array $data, $req)
     {

         $userId = Auth::id();

         // Get the user request
         $userRequest = UserRequest::where('user_id', $userId)->firstOrFail();

         // Get the specific request instance
         $request = UserRequest::findOrFail($req);

         // Ensure completed_time and in_progress_time are set
         if (!$request->completed_time || !$request->in_progress_time) {
             return response()->json([
                 'error' => 'Time calculation cannot be performed. Missing timestamps.'
             ], 400);
         }

         // Create ValetTime record
         $valetTime = ValetTime::create([
             'request_id' => $req,
             'user_id' => $userId,
            // 'hours' => $timeDifference,
             //'price' => $price,
         ]);

return  $valetTime;
     }

     }

