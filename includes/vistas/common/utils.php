<?php

function generaErroresGlobalesFormulario($errores)
{
    $html = '';
    
    $keys = array_keys($errores);
    
    if (count($keys) > 0) 
    {
        $html = '<ul class="errores">';
        
        foreach($keys as $key) 
        {
            $html .= "<li>{$errores[$key]}</li>";
        }
        
        $html .= '</ul>';
    }

    return $html;
}
    ?>
