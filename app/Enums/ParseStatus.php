<?php

namespace App\Enums;

enum ParseStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
