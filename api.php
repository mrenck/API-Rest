<?php 
    class Api extends Rest {

        public function __construct() {
            parent::__construct();
        }

        public function generateToken() {
            $email = $this->validateParameter('email', $this->param['email'], STRING);
            $pass = $this->validateParameter('pass', $this->param['pass'], STRING);

            try {
                $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":pass", $pass);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if(!is_array($user)) {
                    $this->returnResponse(INVALID_USER_PASS, "Email ou senha está incorreto.");
                }

                if($user['active'] == 0) {
                    $this->returnResponse(USER_NOT_ACTIVE, "Usuario nao está ativo. Entre em contato com o administrador.");
                }

                $payload = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (60*5),
                    'userId' => $user['id']
                ];

                $token = JWT::encode($payload, SECRET_KEY);

                $data = ['token' => $token];
                $this->returnResponse(SUCCESS_RESPONSE, $data);
            } catch (Exception $e) {
                $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
            }
            
        }

        public function addProdutos_desc() {
            $codBarras = $this->validateParameter('codBarras', $this->param['codBarras'], INTEGER);
            $descricao = $this->validateParameter('descricao', $this->param['descricao'], STRING);
            $quantidade = $this->validateParameter('quantidade', $this->param['quantidade'], INTEGER);
            $preco = $this->validateParameter('preco', $this->param['preco'], FLOAT);
            $local = $this->validateParameter('local', $this->param['local'], INTEGER, false);
            $validade = $this->validateParameter('validade', $this->param['validade'], STRING, false);

            $prod = new Produtos_desc;
            $prod->setCodBarras($codBarras);
            $prod->setDescricao($descricao);
            $prod->setQuantidade($quantidade);
            $prod->setPreco($preco);
            $prod->setLocal($local);
            $prod->setValidade($validade);

            if(!$prod->insert()) {
                $message = 'Falha no insert.';
            } else {
                $message = "Insert bem sucedido.";
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function getProdutosDetails() {
            $codBarras = $this->validateParameter('codBarras', $this->param['codBarras'], INTEGER);

            $prod = new Produtos_desc;
            $prod->setCodBarras($codBarras);
            $produtos_desc = $prod->getProdutosDetailsByCodBarras();
            if(!is_array($produtos_desc)) {
                $this->returnResponse(SUCCESS_RESPONSE, ['message' => 'Descricao do produto nao encontrada.']);
            }

            $response['prodCodBarras']  = $produtos_desc['cod_barras'];
            $response['prodDescricao']  = $produtos_desc['descricao'];
            $response['prodQuantidade'] = $produtos_desc['quantidade'];
            $response['prodPreco']      = $produtos_desc['preco'];
            $response['prodLocal']      = $produtos_desc['local'];
            $response['prodValidade']   = $produtos_desc['validade'];
            $this->returnResponse(SUCCESS_RESPONSE, $response);
        }

        /*
        public function updateProdutos_desc() {
            $descricao = $this->validateParameter('descricao', $this->param['descricao'], STRING);
            $quantidade = $this->validateParameter('quantidade', $this->param['quantidade'], INTEGER);
            $preco = $this->validateParameter('preco', $this->param['preco'], FLOAT);
            $local = $this->validateParameter('local', $this->param['local'], INTEGER, false);
            $validade = $this->validateParameter('validade', $this->param['validade'], STRING, false);

            $prod = new Produtos_desc;
            $prod->setCodBarras($codBarras);
            $prod->setDescricao($descricao);
            $prod->setQuantidade($quantidade);
            $prod->setPreco($preco);
            $prod->setLocal($local);
            $prod->setValidade($validade);

            if(!$prod->update()) {
                $message = 'Falha no update.';
            } else {
                $message = "Update bem sucedido.";
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }
        */

        /*
        public function deleteProdutos_desc() {
            $codBarras = $this->validateParameter('codBarras', $this->param['codBarras'], INTEGER);

            $prod = new Produtos_desc;
            $prod->setCodBarras($codBarras);

            if(!$prod->delete()) {
                $message = 'Falha no delete.';
            } else {
                $message = "Delete bem sucedido.";
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }
        */
    }

?>