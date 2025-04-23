<?php

namespace App\app\traits;

use App\Models\Request as userRequest;

trait CheckRequestStatus
{

        /**
         * Check if the request status is "in_progress".
         * Throws an exception if not.
         *
         * @param int $requestId
         * @throws \Exception
         */
        public function checkRequestStatusInPreparing(int $requestId)
        {
            $request = userRequest::findOrFail($requestId);


            if ($request->status === 'completed') {
                throw new \Exception('You have already paid. Fee waiver cannot be requested after payment is completed.');
            }


        if (
    $request->status !== 'delivering' &&
    !($request->status === 'in_progress' && $request->requested_by_user)
) {
    throw new \Exception('Fee waiver can only be requested when the status is "delivering" or "in progress with user-initiated request".');
}
        }

}
