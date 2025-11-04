<?php 

require_once('Libraries/Core/VentaValidators.php');     // El nuevo validador.
require_once('Models/LogJsonVentaModel.php');

// Definición de la clase Documento que hereda de Controllers
class Venta extends Controllers {

    public $total = 0;
    public $procesadas = 0;  
    public $errores = 0; 
    public $resultados = []; 

    // Propiedades de la clase
    public $token;
    public $empresa;
    public $ventas;

    private $logId;

    // Constructor de la clase
    public function __construct() {

        try {

            // 1. Obtiene y valida el JSON inicial
            $this->initializeRequest();

            // 2. Llama al constructor padre para tener acceso al modelo
            parent::__construct($this->empresa);

            // 3. Inicia el proceso principal de validación y persistencia
            $this->processInvoiceBatch($this->ventas);

        } catch (Exception $e) {

             $code = http_response_code();
            if ($code === 200) {
                $code = 500; // Valor por defecto si no se asignó nada antes
                http_response_code($code);
            }

            echo json_encode([
                'status' => false,
                'error' => "Error en el procesamiento del lote: " . $e->getMessage()
            ]);
            exit;
        }
    }

    // Método para devolver la factura en formato JSON
    public function venta() {

        // 1. Construimos el array de la respuesta
        $data = [
            "status" => $this->errores === 0,
            "resumen" => [
                "total" => $this->total,
                "procesadas" => $this->procesadas,
                "errores" => $this->errores
            ],
            "ventas" => $this->resultados
        ];

        // 2. Convertimos la respuesta a formato JSON
        $jsonResponse = json_encode($data, JSON_PRETTY_PRINT);

        // 3. ACTUALIZAMOS EL LOG con la respuesta
        // Verificamos que tengamos un ID de log para actualizar
        if (!empty($this->logId)) {
            // Creamos una instancia del LogJsonModel para usar su método de actualización
            $logJsonModel = new LogJsonVentaModel();
            $logJsonModel->updateLogResponse($this->logId, $jsonResponse);
        }

        // 4. Imprimimos la respuesta final al cliente
        header('Content-Type: application/json');
        echo $jsonResponse;
    }

    // Extrae los datos del POST y valida el token
    private function initializeRequest() {
        $postdata = file_get_contents("php://input");
        if (!$postdata) {
            $this->sendError(400, "No se recibió un cuerpo de petición válido.");
        }

        $data = json_decode($postdata);
        if (!$data) {
            $this->sendError(400, "No se recibió un JSON válido.");
        }

        if (empty($data->empresa)) {
            $this->sendError(400, "El JSON debe contener 'empresa'.");
        }

        $bearerToken = $this->getBearerToken();
        if (!$bearerToken) {
            $this->sendError(401, "No se recibió un token Bearer en el header de autorización.");
        }

        $this->validateEmpresa($data->empresa);
        $this->validateToken($bearerToken, $data->empresa);

        $this->token = $bearerToken;
		$this->empresa = (isset($data->empresa) && $data->empresa !== "") ? $data->empresa : NULL;
		$this->ventas = (isset($data->data) && is_array($data->data)) ? $data->data : NULL;
		
        if (empty($this->ventas)) {
            $this->sendError(400, "El JSON debe contener un array de 'data'.");
        }

        $logJsonModel = new LogJsonVentaModel();
        $this->logId = $logJsonModel->insertLogRequest($postdata, 'VENTA', $this->empresa);
    }

    // El corazón de la nueva arquitectura.
    private function processInvoiceBatch(array $ventas) {
        
        $this->total = count($ventas);

        try {
            // =============================================================
            // FASE 1: VALIDACIÓN(Súper rápido)
            // =============================================================
             foreach ($ventas as $venta) {
                // 1. Validar la estructura completa de la venta. Si falla, lanza excepción.
                VentaValidators::validateVentaCompleta($venta);
            }

            // =================================================================
            // FASE 2: PROCESAMIENTO INDIVIDUAL DE VENTAS
            // =================================================================
            foreach ($ventas as $venta) {
                try {

                    // Cada factura se procesa en su propia transacción.
                    $this->model->procesarVentaIndividual($venta);

                    // Si no hay excepción, la venta fue exitosa.
                    $this->procesadas++;
                    $this->resultados[] = [
                        "status" => true,
                        "message" => "Venta procesada correctamente."
                    ];
                } catch (Exception $e) {
                    // Si procesarVentaIndividual falla, solo esta venta se marca como error.
                    $this->errores++;
                    $this->resultados[] = [
                        "status" => false,
                        "error" => $e->getMessage() // Mensaje de error específico de la venta
                    ];
                }
            }

        } catch (Exception $e) {
            // Este catch ahora solo captura errores catastróficos,
            // como un fallo en la inserción de maestros o un error de conexión.
            $this->errores = $this->total;
            $this->procesadas = 0;
            $this->resultados[] = ["status" => false, "error" => "Error crítico de la sincronización: " . $e->getMessage()];
        }
    }

    private function validateToken($token, $empresa) {

        $mysql2 = new Mysql2();
        $sqltoken = "SELECT * FROM api_enterprise WHERE ETR_IDENTIF = '$empresa' AND ETR_TOKEN = '$token'";
        $requesttoken = $mysql2->select($sqltoken);

        if (!$requesttoken) {
            http_response_code(403);
            throw new Exception("Acceso denegado: Token inválido.");
        }
    }

    private function validateEmpresa($empresa) {
        
        $mysql2 = new Mysql2();
        $sqlEnterprise = "SELECT * FROM api_enterprise WHERE ETR_IDENTIF = '$empresa'";
        $requestEnterprise = $mysql2->select($sqlEnterprise);

        if (!$requestEnterprise) {
            http_response_code(400);
            // Si la inserción falla, es mejor lanzar un error.
            throw new Exception("La empresa no existe o el identificativo es incorrecto.");
        }
    }

    private function getBearerToken() {
        $headers = null;

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }



        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function sendError($code, $message) {
        http_response_code($code);
        echo json_encode([
            'status' => false,
            'error' => $message
        ]);
        exit;
    }
}
?>