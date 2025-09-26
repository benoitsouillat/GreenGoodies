<?php

namespace App\Enum;

enum OrderStatus: string
{
    case basket = "Panier";
    case validated = "Validée";
}
