<?php

namespace App;
enum TopicType: string
{
    case VALET_CUSTOMER = 'valet_customer';
     case VALET_AGENT = 'valet_agent';
    case VALET_PROVIDER = 'valet_provider';
    case VALET_HOST = 'valet_host';
    case GENERAL = 'general';

    public function description(): string
    {
        return match($this) {
            self::VALET_CUSTOMER => 'Valet Customer Topic',
            self::VALET_AGENT => 'Valet Agent Topic',
            self::VALET_PROVIDER => 'Valet Provider Topic',
            self::VALET_HOST => 'Valet Host Topic',
            self::GENERAL => 'General Broadcast Topic',
        };
    }
}