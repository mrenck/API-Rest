<?php 
    class Produtos_desc {
        private $id;
        private $codBarras;
        private $descricao;
        private $quantidade;
        private $preco;
        private $local;
        private $validade;
        //private $updatedBy;
        //private $updatedOn;
        //private $createdBy;
        //private $createdOn;
        private $tableName = 'produtos_desc';
        private $dbConn;

        function setId($id) { $this->id = $id; }
        function getId() { return $this->id; }
        function setCodBarras($codBarras) { $this->codBarras = $codBarras; }
        function getCodBarras() { return $this->codBarras; }
        function setDescricao($descricao) { $this->descricao = $descricao; }
        function getDescricao() { return $this->descricao; }
        function setQuantidade($quantidade) { $this->quantidade = $quantidade; }
        function getQuantidade() { return $this->quantidade; }
        function setPreco($preco) { $this->preco = $preco; }
        function getPreco() { return $this->preco; }
        function setLocal($local) { $this->local = $local; }
        function getLocal() { return $this->local; }
        function setValidade($validade) { $this->validade = $validade; }
        function getValidade() { return $this->validade; }
        //function setUpdatedBy($updatedBy) { $this->updatedBy = $updatedBy; }
        //function getUpdatedBy() { return $this->updatedBy; }
        //function setUpdatedOn($updatedOn) { $this->updatedOn = $updatedOn; }
        //function getUpdatedOn() { return $this->updatedOn; }
        //function setCreatedBy($createdBy) { $this->createdBy = $createdBy; }
        //function getCreatedBy() { return $this->createdBy; }
        //function setCreatedOn($createdOn) { $this->createdOn = $createdOn; }
        //function getCreatedOn() { return $this->createdOn; }

        public function __construct() {
            $db = new DbConnect();
            $this->dbConn = $db->connect();
        }

        public function getProdutosDetailsByCodBarras() {
            $sql = "SELECT * FROM produtos_desc WHERE cod_barras = :codBarras";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindParam(':codBarras', $this->codBarras);
            $stmt->execute();
            $produtos_desc = $stmt->fetch(PDO::FETCH_ASSOC);
            return $produtos_desc;
        }

        public function getAllProdutos_desc() {
            $stmt = $this->dbConn->prepare("SELECT * FROM " . $this->tableName);
            $stmt->execute();
            $produtos_desc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $produtos_desc;
        }

        public function insert() {
            $sql = 'INSERT INTO ' . $this->tableName . '(id, cod_barras, descricao, quantidade, preco, local, validade) 
                                                        VALUES (null, :codBarras, :descricao, :quantidade, :preco, :local, :validade)';
            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindParam(':codBarras', $this->codBarras);
            $stmt->bindParam(':descricao', $this->descricao);
            $stmt->bindParam(':quantidade', $this->quantidade);
            $stmt->bindParam(':preco', $this->preco);
            $stmt->bindParam(':local', $this->local);
            $stmt->bindParam(':validade', $this->validade);
            //$stmt->bindParam(':createdBy', $this->createdBy);
            //$stmt->bindParam(':createdOn', $this->createdOn);

            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function update() {
            $sql = "UPDATE $this->tableName SET";

            if( null != $this->getLocal()) {
                $sql .= " local = '" . $this->getLocal() . "',";
            }

            if( null != $this->getValidade()) {
                $sql .= " validade = '" . $this->getValidade() . "',";
            }

            $sql .= "WHERE id = :userId";

            $stmt = $this->dbConn->prepare($sql);
            $stmt->bindParam(':userId', $this->id);
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function delete() {
            $stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE id = :userId');
            $stmt->bindParam(':userId', $this->id);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
            
        }

    }

?>