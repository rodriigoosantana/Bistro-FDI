<?php

namespace es\ucm\fdi\aw\Pedido;

enum Tipo: string
{
    case ParaTomar  = "local";
    case ParaLlevar = "llevar";
}