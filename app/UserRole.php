<?php

namespace App;


enum UserRole: string
{
    case CUSTOMER = 'valet_customer';
    case AGENT = 'valet_agent';
}