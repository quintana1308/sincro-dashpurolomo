<?php
// ARCHIVO: Libraries/Core/Validators.php

class VentaValidators {
    
    public static function validateVentaCompleta($venta) {
        $missing = [];

        if (empty($venta->mes_id)) $missing[] = 'mes_id';
        if (empty($venta->mes)) $missing[] = 'mes';
        if (empty($venta->estado_id)) $missing[] = 'estado_id';
        if (empty($venta->estado)) $missing[] = 'estado';
        if (empty($venta->aliado_id)) $missing[] = 'aliado_id';
        if (empty($venta->aliado)) $missing[] = 'aliado';
        if (empty($venta->sucursal_id)) $missing[] = 'sucursal_id';
        if (empty($venta->sucursal)) $missing[] = 'sucursal';
        if (empty($venta->region_id)) $missing[] = 'region_id';
        if (empty($venta->region)) $missing[] = 'region';
        if (empty($venta->tipo_cliente_id)) $missing[] = 'tipo_cliente_id';
        if (empty($venta->tipo_cliente)) $missing[] = 'tipo_cliente';
        if (empty($venta->sku_id)) $missing[] = 'sku_id';
        if (empty($venta->sku)) $missing[] = 'sku';
        if (empty($venta->departamento_id)) $missing[] = 'departamento_id';
        if (empty($venta->departamento)) $missing[] = 'departamento';
        if (empty($venta->marca_id)) $missing[] = 'marca_id';
        if (empty($venta->marca)) $missing[] = 'marca';
        if (empty($venta->grupo_id)) $missing[] = 'grupo_id';
        if (empty($venta->grupo)) $missing[] = 'grupo';
        if (empty($venta->categoria_id)) $missing[] = 'categoria_id';
        if (empty($venta->categoria)) $missing[] = 'categoria';
        if (empty($venta->version_id)) $missing[] = 'version_id';
        if (empty($venta->version)) $missing[] = 'version';
        if (!isset($venta->peso_anterior)) $missing[] = 'peso_anterior';
        if (!isset($venta->peso_actual)) $missing[] = 'peso_actual';
        if (!isset($venta->caja_anterior)) $missing[] = 'caja_anterior';
        if (!isset($venta->caja_actual)) $missing[] = 'caja_actual';
        if (!isset($venta->diferencia_peso)) $missing[] = 'diferencia_peso';
        if (!isset($venta->diferencia_caja)) $missing[] = 'diferencia_caja';
        //if (empty($venta->presentacion)) $missing[] = 'presentacion';
		if (!isset($venta->presentacion) || $venta->presentacion === '') {
			$missing[] = 'presentacion';
		}
        if (empty($venta->fecha_sincronizacion)) $missing[] = 'fecha_sincronizacion';

        if (!empty($missing)) {
            throw new Exception("Venta -- Faltan campos en el json de la venta: " . implode(', ', $missing));
        }
    }
}
?>