<?php

namespace App;


enum NotificationType: string
{
    case REQUEST_PARKING_SERVICE = 'Noif-1';
    case REQUEST_CAR = 'Noif-2';
    case NEW_VP_RATE = 'Noif-3';
    case NEW_PAYMENT = 'Noif-4';
    case ASK_FOR_WAIVE = 'Noif-5';
    case ANSWER_WAIVE = 'Noif-6';
    case CLOSE_PARKING_EVENT = 'Noif-7';
    case NEW_VC_RATE = 'Noif-8';
    case CAR_PREPARATION_REQUESTED = 'Noif-9';
    case ACCEPT_REQUESTED = 'Noif-10';
    case ACCEPT_PREPARING = 'Noif-11';
    case CANCEL_REQUEST = 'Noif-12';

    public function defaultTitle(): string
    {
        return match($this) {
            self::REQUEST_PARKING_SERVICE       => trans('New Parking Request'),
            self::REQUEST_CAR                   => trans('Car Retrieval Request'),
            self::NEW_VP_RATE                   => trans('New Customer Rating'),
            self::NEW_PAYMENT                   => trans('New Payment Made'),
            self::ASK_FOR_WAIVE                 => trans('Fee Waive Request'),
            self::ANSWER_WAIVE                  => trans('Waive Request Response'),
            self::CLOSE_PARKING_EVENT           => trans('Parking Event Closed'),
            self::NEW_VC_RATE                   => trans('New Agent Rating'),
            self::CAR_PREPARATION_REQUESTED     => trans('Car Preparation Requested'),
            self::ACCEPT_REQUESTED              => trans('Parking Request Accepted'),
            self::ACCEPT_PREPARING              => trans('Agent Arrived and Preparing'),
            self::CANCEL_REQUEST                => trans('Parking Request Cancelled'),
        };
    }


}
