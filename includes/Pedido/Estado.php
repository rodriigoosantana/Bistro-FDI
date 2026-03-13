<?php

namespace es\ucm\fdi\aw\Pedido;

enum Estado: string
{
    case Nuevo         = "nuevo";
    case Recibido      = "recibido";
    case EnPreparacion = "en preparacion";
    case Cocinando     = "cocinando";
    case ListoCocina   = "listo cocina";
    case Terminado     = "terminado";
    case Entregado     = "entregado";
    case Cancelado     = "cancelado";
}