<?php 

// Definición de la clase DocumentoModel que hereda de Mysql
class ActivacionModel extends Mysql
{	
    // Constructor de la clase
    public function __construct($conectEnterprise)
    {   
        parent::__construct($conectEnterprise);
    }

    // =============================================================
    // MÉTODO PARA PROCESAR UNA ÚNICA FACTURA CON SUS DOCUMENTOS
    // =============================================================
    public function procesarActivacionIndividual($activacion) {
        
        // 1. Preparamos los datos de los documentos para esta venta
        $preparedData = $this->prepararDatosTransaccionalesActivacion($activacion);
        $activaciones = $preparedData['activaciones'];

        // --- PASO 1: VALIDACIÓN DE EXISTENCIA ---
        // Antes de intentar cualquier inserción, validamos cada venta que se va a crear.
        foreach ($activaciones as $docArray) {
            // Extraemos los datos clave del array preparado
            $aliado_id = $docArray[0];
            $sucursal_id = $docArray[2];
            $sucursal = $docArray[3];
            $region_id = $docArray[4];
            $region = $docArray[5];
            $estado_id = $docArray[6];
            $estado = $docArray[7];
            $tipo = $docArray[8];
            $mes_id = $docArray[9];
            $mes = $docArray[10];
            $codigo = $docArray[11];
            $descripcion = $docArray[12];

            if ($this->_activacionExiste($aliado_id, $sucursal_id, $region_id, $estado_id, $tipo, $mes_id, $codigo)) {
                // Si la venta ya existe, lanzamos un error específico.
                // El controlador lo capturará y marcará esta venta como fallida.
                throw new Exception("La activacion -- Sucursal: {$sucursal} | Región: {$region} | Estado: {$estado} | Tipo: {$tipo} | Mes: {$mes} | Descripción: {$descripcion} ya existe en la base de datos.");
            }
        }

        // --- PASO 3: PERSISTENCIA (Si todas las validaciones pasaron) ---
        $this->beginTransaction();
        try {
            $this->_bulkInsertActivaciones($activaciones);
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
    private function _activacionExiste($aliado_id, $sucursal_id, $region_id, $estado_id, $tipo, $mes_id, $codigo) {
        
        // Sanitizar los valores para evitar SQL Injection
        $aliado_idSan = $this->conexion->quote($aliado_id);
        $sucursal_idSan = $this->conexion->quote($sucursal_id);
        $region_idSan = $this->conexion->quote($region_id);
        $estado_idSan = $this->conexion->quote($estado_id);
        $tipoSan = $this->conexion->quote($tipo);
        $mes_idSan = $this->conexion->quote($mes_id);
        $codigoSan = $this->conexion->quote($codigo);

        // Construir el SELECT
        $sql = "SELECT 1 FROM PANEL_HOMOLOGACIONACT_DELETE
                WHERE ALIADOID = $aliado_idSan
                AND SUCURSALID = $sucursal_idSan
                AND REGIONID = $region_idSan
                AND ESTADOID = $estado_idSan
                AND TIPO = $tipoSan
                AND MESID = $mes_idSan
                AND CODIGO = $codigoSan
                LIMIT 1";
        
       $this->strquery = $sql; // Guardar para depuración
        $result = $this->select($sql);

        return !empty($result); // true si existe, false si no
    }

    // =================================================================================
    // MÉTODOS "PREPARADORES" DE DATOS
    // =================================================================================
    public function prepararDatosTransaccionalesActivacion($activacion) {
        $activaciones = [];
        
        $activaciones[] = $this->_prepararArrayActivacion($activacion);

        return ['activaciones' => $activaciones];
    }

    // Método privado para preparar un array de un solo documento
    private function _prepararArrayActivacion($activacion) {

        return [
            $activacion->aliado_id, $activacion->aliado, $activacion->sucursal_id, $activacion->sucursal, $activacion->region_id, $activacion->region, 
            $activacion->estado_id, $activacion->estado, $activacion->tipo, $activacion->mes_id, $activacion->mes, $activacion->codigo,
            $activacion->descripcion, $activacion->activacion_anterior, $activacion->cartera_anterior, $activacion->activacion_actual, $activacion->cartera_actual,
            $activacion->fecha_sincronizacion
        ];
    }

    private function _bulkInsertActivaciones(array $data) {
        if (empty($data)) return;

        $columnas = "ALIADOID, ALIADO, SUCURSALID, SUCURSAL, REGIONID, REGION, ESTADOID, ESTADO, TIPO, MESID, MES, CODIGO,
                    DESCRI, A2024, C2024, A2025, C2025, UPD_SINCRO";

        $valueStrings = [];
        foreach ($data as $fila) {
            $sanitizedValues = array_map([$this->conexion, 'quote'], $fila);
            $valueStrings[] = "(" . implode(',', $sanitizedValues) . ")";
        }

        $sql = "INSERT INTO PANEL_HOMOLOGACIONACT_DELETE ($columnas) VALUES " . implode(", ", $valueStrings);
        $this->insert_massive($sql);
    }
}
?>