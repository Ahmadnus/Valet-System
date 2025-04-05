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

    public function defaultTitle(): string
    {
        return match($this) {
            self::REQUEST_PARKING_SERVICE => 'New Parking Request',
            self::REQUEST_CAR => 'Car Retrieval Request',
            self::NEW_VP_RATE => 'New Customer Rating',
            self::NEW_PAYMENT => 'New Payment Made',
            self::ASK_FOR_WAIVE => 'Fee Waive Request',
            self::ANSWER_WAIVE => 'Waive Request Response',
            self::CLOSE_PARKING_EVENT => 'Parking Event Closed',
            self::NEW_VC_RATE => 'New Agent Rating',
        };
    }


}
