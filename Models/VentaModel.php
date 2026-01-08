<?php 

// Definición de la clase DocumentoModel que hereda de Mysql
class VentaModel extends Mysql
{	
    // Constructor de la clase
    public function __construct($conectEnterprise)
    {   
        parent::__construct($conectEnterprise);
    }

    // =============================================================
    // MÉTODO PARA PROCESAR UNA ÚNICA FACTURA CON SUS DOCUMENTOS
    // =============================================================
    public function procesarVentaIndividual($venta) {
        
        // 1. Preparamos los datos de los documentos para esta venta
        $preparedData = $this->prepararDatosTransaccionalesVenta($venta);
        $ventas = $preparedData['ventas'];

        // --- PASO 1: VALIDACIÓN DE EXISTENCIA ---
        // Antes de intentar cualquier inserción, validamos cada venta que se va a crear.
        /*foreach ($ventas as $docArray) {
            // Extraemos los datos clave del array preparado
            $mes_id = $docArray[0];
            $mes = $docArray[1];
            $estado_id = $docArray[2];
            $estado = $docArray[3];
            $aliado_id = $docArray[4];
            $sucursal_id = $docArray[6];
            $sucursal = $docArray[7];
            $region_id = $docArray[8];
            $region = $docArray[9];
            $tipo_cliente_id = $docArray[10];
            $tipo_cliente = $docArray[11];
            $sku_id = $docArray[12];
            $sku = $docArray[13];

            if ($this->_ventaExiste($mes_id, $estado_id, $aliado_id, $sucursal_id, $region_id, $tipo_cliente_id, $sku_id, $sku)) {
                // Si la venta ya existe, lanzamos un error específico.
                // El controlador lo capturará y marcará esta venta como fallida.
                throw new Exception("La venta -- Mes: {$mes} | Estado: {$estado} | Sucursal: {$sucursal} | Region: {$region} | Tipo de cliente: {$tipo_cliente} | Sku: {$sku} ya existe en la base de datos.");
            }
        }*/

        // --- PASO 3: PERSISTENCIA (Si todas las validaciones pasaron) ---
        $this->beginTransaction();
        try {
            $this->_bulkInsertVentas($ventas);
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e; 
        }
    }

    /**
     * Helper privado que ejecuta el SELECT COUNT(*) para verificar si un documento existe.
     * Replica tu lógica de validación original.
     */
    private function _ventaExiste($mes_id, $estado_id, $aliado_id, $sucursal_id, $region_id, $tipo_cliente_id, $sku_id, $sku) {
        
        // Sanitizar los valores para evitar SQL Injection
        $mes_idSan = $this->conexion->quote($mes_id);
        $estado_idSan = $this->conexion->quote($estado_id);
        $aliado_idSan = $this->conexion->quote($aliado_id);
        $sucursal_idSan = $this->conexion->quote($sucursal_id);
        $region_idSan = $this->conexion->quote($region_id);
        $tipo_cliente_idSan = $this->conexion->quote($tipo_cliente_id);
        $sku_idSan = $this->conexion->quote($sku_id);
        $skuSan = $this->conexion->quote($sku);

        // Construir el SELECT
        $sql = "SELECT 1 FROM PANEL_HOMOLOGACIONVTA_DELETE
                WHERE MESID = $mes_idSan
                AND ESTADOID = $estado_idSan
                AND ALIADOID = $aliado_idSan
                AND SUCURSALID = $sucursal_idSan
                AND REGIONID = $region_idSan
                AND TIPOCLIENTEID = $tipo_cliente_idSan
                AND SKUID = $sku_idSan
                AND SKU = $skuSan
                LIMIT 1";
        
       $this->strquery = $sql; // Guardar para depuración
        $result = $this->select($sql);

        return !empty($result); // true si existe, false si no
    }

    // =================================================================================
    // MÉTODOS "PREPARADORES" DE DATOS
    // =================================================================================
    public function prepararDatosTransaccionalesVenta($venta) {
        $ventas = [];
        
        $ventas[] = $this->_prepararArrayVenta($venta);

        return ['ventas' => $ventas];
    }

    // Método privado para preparar un array de un solo documento
    private function _prepararArrayVenta($venta) {

        return [
            $venta->mes_id, $venta->mes, $venta->estado_id, $venta->estado, $venta->aliado_id, $venta->aliado, $venta->sucursal_id, $venta->sucursal, 
            $venta->region_id, $venta->region, $venta->tipo_cliente_id, $venta->tipo_cliente, $venta->sku_id, $venta->sku, $venta->departamento_id, 
            $venta->departamento, $venta->marca_id, $venta->marca, $venta->grupo_id, $venta->grupo, $venta->categoria_id, $venta->categoria, 
            $venta->version_id, $venta->version, $venta->peso_anterior, $venta->peso_actual, $venta->caja_anterior, $venta->caja_actual, 
            $venta->diferencia_peso, $venta->diferencia_caja, $venta->presentacion, $venta->fecha_sincronizacion
        ];
    }

    private function _bulkInsertVentas(array $data) {
        if (empty($data)) return;

        $columnas = "MESID, MES, ESTADOID, ESTADO, ALIADOID, ALIADO, SUCURSALID, SUCURSAL, REGIONID, REGION, TIPOCLIENTEID, TIPOCLIENTE,
                SKUID, SKU, DEPID, DEP, MARCAID, MARCA, GPOID, GPO, CATID, CAT, VERID, VER, PESOANTERIOR, PESOACTUAL, CAJASANTERIOR,
                CAJASACTUAL, DIFFPESO, DIFFCAJA, PRESENTACION, UPD";

        $valueStrings = [];
        foreach ($data as $fila) {
            $sanitizedValues = array_map([$this->conexion, 'quote'], $fila);
            $valueStrings[] = "(" . implode(',', $sanitizedValues) . ")";
        }

        $sql = "REPLACE INTO PANEL_HOMOLOGACIONVTA_DELETE ($columnas) VALUES " . implode(", ", $valueStrings);
        $this->insert_massive($sql);
    }
}
?>