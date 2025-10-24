<?php

namespace App\Enum;

enum OrderStatus: string
{
    case cart = "Panier";
    case validated = "Validée";
}
