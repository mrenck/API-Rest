<?php
    require_once('constants.php');
    class Rest {
        protected $request;
        protected $serviceName;
        protected $param;
        protected $dbConn;
        protected $userId;

        public function __construct() {
            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->throwError(REQUEST_METHOD_NOT_VALID, 'Metodo requisitado e invalido.');
            }
            $handler = fopen('php://input', 'r');
            $this->request = stream_get_contents($handler);
            $this->validateRequest();

            $db = new DbConnect;
            $this->dbConn = $db->connect();

            if('generatetoken' != strtolower($this->serviceName)) {
                $this->validateToken();
            }
        }

        public function validateRequest() {
            if($_SERVER['CONTENT_TYPE'] !== 'application/json; charset=utf-8') {
                $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content type nao e valido.');
            }

            $data = json_decode($this->request, true);
            
            if(!isset($data['name']) || $data['name'] == "" ) {
                $this->throwError(API_NAME_REQUIRED, 'Nome da API e necessario.');
            }
            $this->serviceName = $data['name'];

            if(!is_array($data['param'])) {
                $this->throwError(API_PARAM_REQUIRED, 'Parametro da API e necessario.');
            }
            $this->param = $data['param'];
        }

        public function processApi() {
            $api = new API;
            $rMethod = new reflectionMethod('API', $this->serviceName);
            if(!method_exists($api, $this->serviceName)) {
                $this->throwError(API_DOES_NOT_EXIST, "API nao existe.");
            }
            $rMethod->invoke($api);
        }

        public function validateParameter($fieldName, $value, $dataType, $required = true) {
            if($required == true && empty($value) == true) {
                $this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldName . " parameter is required.");
            }

            switch ($dataType) {
                case BOOLEAN:
                    if(!is_bool($value)) {
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Tipo de dado nao e valido para " . $fieldName . ". Deveria ser boolean.");
                    }
                    break;
                case INTEGER:
                    if(!is_numeric($value)) {
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Tipo de dado nao e valido para " . $fieldName . ". Deveria ser numerico.");
                    }
                    break;
                case STRING:
                    if(!is_string($value)) {
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Tipo de dado nao e valido para " . $fieldName . ". Deveria ser string.");
                    }
                    break;                    
                
                default:
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Tipo de dado nao e valido para " . $fieldName);
                    break;
            }

            return $value;
        }

        public function validateToken() {
            try {
                    $token = $this->getBearerToken();
                    $payload = JWT::decode($token, SECRET_KEY, ['HS256']);

                    $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE id = :userId");
                    $stmt->bindParam(":userId", $payload->userId);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if(!is_array($user)) {
                        $this->returnResponse(INVALID_USER_PASS, "Este usuario nao foi encontrado no banco de dados.");
                    }

                    if($user['active'] == 0) {
                        $this->returnResponse(USER_NOT_ACTIVE, "Este usuario pode estar desativado. Entre em contato com o administrador.");
                    }
                    $this->userId = $payload->userId;
            } catch (Exception $e) {
                $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
            }
        }

        public function throwError($code, $message) {
            header("content-type: application/json");
            $errorMsg = json_encode(['error' => ['status'=>$code, 'message'=>$message]]);
            echo $errorMsg; exit;
        }

        public function returnResponse($code, $data) {
            header("content-type: application/json");
            $response = json_encode(['response' => ['status' => $code, "result" => $data]]);
            echo $response; exit;
        }

        /**
         * Get header Authorization
         */
        public function getAuthorizationHeader() {
            $headers = null;
            if (isset($_SERVER['Authorization'])) {
                $headers = trim($_SERVER["Authorization"]);
            } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                //Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                if(isset($requestHeaders['Authorization'])) {
                    $headers = trim($requestHeaders['Authorization']);
                }
            }
            return $headers;
        }

        /**
         * Get access token from header
         */
        public function getBearerToken() {
            $headers = $this->getAuthorizationHeader();
            //HEADER: Get the access token from the header
            if (!empty($headers)) {
                if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                    return $matches[1];
                }
            }
            $this->throwError(AUTHORIZATION_HEADER_NOT_FOUND, 'Token de acesso nao encontrado.');
        }
    }

?>