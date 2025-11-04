<?php
// ARCHIVO: Libraries/Core/Validators.php

class ActivacionValidators {
    
    public static function validateActivacionCompleta($activacion) {
        $missing = [];

        if (empty($activacion->aliado_id)) $missing[] = 'aliado_id'; 
        if (empty($activacion->aliado)) $missing[] = 'aliado'; 
        if (empty($activacion->sucursal_id)) $missing[] = 'sucursal_id'; 
        if (empty($activacion->sucursal)) $missing[] = 'sucursal'; 
        if (empty($activacion->region_id)) $missing[] = 'region_id'; 
        if (empty($activacion->region)) $missing[] = 'region'; 
        if (empty($activacion->estado_id)) $missing[] = 'estado_id'; 
        if (empty($activacion->estado)) $missing[] = 'estado'; 
        if (empty($activacion->tipo)) $missing[] = 'tipo'; 
        if (empty($activacion->mes_id)) $missing[] = 'mes_id'; 
        if (empty($activacion->mes)) $missing[] = 'mes'; 
        if (empty($activacion->codigo)) $missing[] = 'codigo'; 
        if (empty($activacion->descripcion)) $missing[] = 'descripcion'; 
        if (!isset($activacion->activacion_anterior)) $missing[] = 'activacion_anterior'; 
        if (!isset($activacion->cartera_anterior)) $missing[] = 'cartera_anterior'; 
        if (!isset($activacion->activacion_actual)) $missing[] = 'activacion_actual'; 
        if (!isset($activacion->cartera_actual)) $missing[] = 'cartera_actual';
        if (empty($activacion->fecha_sincronizacion)) $missing[] = 'fecha_sincronizacion'; 

        if (!empty($missing)) {
            throw new Exception("Activación -- Faltan campos en el json de la activación: " . implode(', ', $missing));
        }
    }
}
?>