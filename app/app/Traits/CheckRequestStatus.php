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

            // Check if the status is "completed"
            if ($request->status === 'completed') {
                throw new \Exception('You have already paid. Fee waiver cannot be requested after payment is completed.');
            }

            // Check if the status is not "in_preparing"
            if ($request->status !== 'preparing') {
                throw new \Exception('Fee waiver can only be requested when the status is "in preparing".');
            }
        }

}
