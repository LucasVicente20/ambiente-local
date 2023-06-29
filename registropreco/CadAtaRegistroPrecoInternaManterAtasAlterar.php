<?php

/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

// 220038--

if (!@require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

class RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar extends Dados_Abstrata
{

    /**
     *
     * @param integer $orgao
     * @param integer $ano
     * @param integer $processo
     * @param integer $ata
     */
    public function procurar($orgao, $ano, $processo, $ata)
    {
        $sql = "
        SELECT
            arpi.carpnosequ,
            arpi.clicpoproc,
            arpi.alicpoanop,
            arpi.cgrempcodi,
            arpi.ccomlicodi,
            arpi.corglicodi,
            arpi.tarpindini,
            arpi.aarpinpzvg
        FROM
            sfpc.tbataregistroprecointerna arpi
        WHERE
            arpi.clicpoproc = $processo
            AND arpi.alicpoanop = $ano
            AND arpi.corglicodi = $orgao
            AND arpi.carpnosequ = $ata
        ";

        return end(ClaDatabasePostgresql::executarSQL($sql));
    }

    /**
     *
     * @param integer $ano
     * @param integer $processo
     * @param integer $orgaoUsuario
     * @throws InvalidArgumentException
     */
    public function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {

        if (!filter_var($ano, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$ano deve ser inteiro válido');
        }

        if (!filter_var($processo, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$processo deve ser inteiro válido');
        }

        if (!filter_var($orgaoUsuario, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$orgaoUsuario deve ser inteiro válido');
        }

        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgaoUsuario = filter_var($orgaoUsuario, FILTER_SANITIZE_NUMBER_INT);

        $sql = "SELECT distinct sc.csolcocodi, sc.asolcoanos, l.clicpoproc,";
        $sql .= " l.alicpoanop,";
        $sql .= " l.xlicpoobje,";
        $sql .= " l.ccomlicodi,";
        $sql .= " c.ecomlidesc,";
        $sql .= " o.corglicodi,";
        $sql .= " o.eorglidesc,";
        $sql .= " m.emodlidesc,";
        $sql .= " l.clicpocodl,";
        $sql .= " l.alicpoanol,";
        $sql .= " sc.csolcosequ";
        $sql .= " from sfpc.tblicitacaoportal l";
        $sql .= " inner join sfpc.tborgaolicitante o";
        $sql .= " on o.corglicodi= %d";
        $sql .= " and l.corglicodi = o.corglicodi";
        $sql .= " inner join sfpc.tbcomissaolicitacao c";
        $sql .= " on l.ccomlicodi = c.ccomlicodi";
        $sql .= " inner join sfpc.tbmodalidadelicitacao m";
        $sql .= " on l.cmodlicodi = m.cmodlicodi";
        $sql .= " INNER JOIN sfpc.tbsolicitacaolicitacaoportal slp
        ON slp.clicpoproc = l.clicpoproc
    AND slp.alicpoanop = l.alicpoanop INNER JOIN sfpc.tbsolicitacaocompra sc
        ON sc.csolcosequ = slp.csolcosequ";
        $sql .= " where l.alicpoanop = %d";
        $sql .= " and l.clicpoproc = %d";

        return sprintf($sql, $orgaoUsuario, $ano, $processo);
    }

    /**
     *
     * @param unknown $processo
     * @param unknown $orgao
     * @param unknown $ano
     * @param unknown $chaveAta
     * @throws InvalidArgumentException
     */
    public function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        if (!filter_var($processo, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$processo deve ser inteiro válido');
        }

        if (!filter_var($orgao, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$orgaoUsuario deve ser inteiro válido');
        }

        if (!filter_var($ano, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$ano deve ser inteiro válido');
        }

        if (!filter_var($chaveAta, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$chaveAta deve ser inteiro válido');
        }

        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgao = filter_var($orgao, FILTER_SANITIZE_NUMBER_INT);
        $chaveAta = filter_var($chaveAta, FILTER_SANITIZE_NUMBER_INT);

        $sql = "select a.carpincodn, a.aarpinpzvg, a.tarpindini, f.nforcrrazs, d.edoclinome,";
        $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, a.cgrempcodi, a.cusupocodi, a.earpinobje, f.aforcrsequ,";

        $sql .= " f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, ";
        $sql .= " f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta, ";

        $sql .= " fa.nforcrrazs as razaoFornecedorAtual, fa.aforcrccgc as cgcFornecedorAtual, fa.aforcrccpf as cpfFornecedorAtual, fa.eforcrlogr as logradouroFornecedorAtual, ";
        $sql .= " fa.aforcrnume as numeroEnderecoFornecedorAtual, fa.eforcrbair as bairroFornecedorAtual, fa.nforcrcida as cidadeFornecedorAtual, fa.cforcresta as estadoFornecedorAtual ";

        $sql .= " from sfpc.tbataregistroprecointerna a";

        $sql .= " left outer join sfpc.tbsolicitacaolicitacaoportal s";
        $sql .= " on (s.clicpoproc = a.clicpoproc";
        $sql .= " and s.alicpoanop = a.alicpoanop";
        $sql .= " and s.ccomlicodi = a.ccomlicodi";
        $sql .= " and s.corglicodi = a.corglicodi)";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado f";
        $sql .= " on f.aforcrsequ = a.aforcrsequ";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado fa";
        $sql .= " on fa.aforcrsequ = (select afa.aforcrsequ from sfpc.tbataregistroprecointerna afa where afa.carpnosequ = a.carpnoseq1)";

        $sql .= " left outer join sfpc.tbdocumentolicitacao d";
        $sql .= " on d.clicpoproc =a.clicpoproc";
        $sql .= " and d.clicpoproc = %d";
        $sql .= " and d.corglicodi = %d";
        $sql .= " and d.alicpoanop = %d";
        $sql .= ' where a.carpnosequ = %d';

        return sprintf($sql, $processo, $orgao, $ano, $chaveAta);
    }

    public function sqlExisteCaronaExterna($ataCod)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbcaronaorgaoexterno car ";

        $sql .= " WHERE 1=1 ";
        $sql .= " AND car.carpnosequ = ".$ataCod;

        return $sql;
    }

    public function sqlExisteScc($ataCod)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbsolicitacaocompra sol ";

        $sql .= " WHERE 1=1 ";
        $sql .= " and sol. carpnosequ = ".$ataCod;
        $sql .= "   and sol.csitsocodi <> 10 ";

        return $sql;
    }

    public function sqlExisteAtaInternaAnoNumeracaoOrgao($ataCod, $orgao, $ano, $numeracao)
    {
        $sql  = " SELECT count(*) ";
        $sql .= " FROM sfpc.tbataregistroprecointerna atai ";

        $sql .= " WHERE 1=1 ";
        $sql .= " AND atai.aarpinanon = ".$ano;
        $sql .= " AND atai.carpincodn = ".$numeracao;
        $sql .= " AND atai.corglicodi = ".$orgao;

        $sql .= " AND atai.carpnosequ <> ".$ataCod;

        return $sql;
    }

    /**
     * Seleciona os órgãos participantes de uma ata.
     *
     * @param integer $processo da ata
     * @param integer $ano da ata
     * @param integer $orgaoGestor da ata
     *
     * @return string
     */
    public static function sqlOrgaosParticipantesAta($processo, $ano, $orgaoGestor)
    {
        if (!filter_var($processo, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$processo deve ser inteiro válido');
        }

        if (!filter_var($ano, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$ano deve ser inteiro válido');
        }

        if (!filter_var($orgaoGestor, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$orgaoUsuario deve ser inteiro válido');
        }

        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $orgaoGestor = filter_var($orgaoGestor, FILTER_SANITIZE_NUMBER_INT);

        $sql = "
            SELECT distinct o.eorglidesc
            FROM sfpc.tbparticipanteatarp p 
            INNER JOIN sfpc.tborgaolicitante o ON o.corglicodi = p.corglicodi
            WHERE p.carpnosequ IN 
                (SELECT  a.carpnosequ
                FROM sfpc.tbataregistroprecointerna a
                LEFT OUTER JOIN sfpc.tbsolicitacaolicitacaoportal s ON
                    (s.clicpoproc = a.clicpoproc
                    AND s.alicpoanop = a.alicpoanop
                    AND s.ccomlicodi = a.ccomlicodi
                    AND s.corglicodi = a.corglicodi)
                WHERE a.clicpoproc   = %d
                    AND a.alicpoanop = %d
                    AND a.corglicodi = %d 
                ORDER BY a.carpnosequ)";
        return sprintf($sql, $processo, $ano, $orgaoGestor);
    }

    public function sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $sql = "select max(d.cdoclicodi) from sfpc.tbdocumentolicitacao d";
        $sql .= "where d.clicpoproc =" . $processo;
        $sql .= "and d.cgrempcodi =" . $grupo;
        $sql .= "and d.corglicodi =" . $orgao;
        $sql .= "and d.alicpoanop =" . $ano;

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    public function sqlInsereDocumento($valores)
    {
        $sql = "INSERT INTO sfpc.tbdocumentolicitacao (clicpoproc,alicpoanop,cgrempcodi,ccomlicodi,corglicodi,";
        $sql .= "cdoclicodi,edoclinome,tdoclidata,cusupocodi,tdocliulat)";
        $sql .= " VALUES (" . $valores . ")";

        return $sql;
    }

    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function Dados_carregarTodosDocumentosAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $db = Conexao();
        $sql = sprintf("
                SELECT 
                carpnosequ,
                cdocatsequ,
                edocatnome,
                encode(idocatarqu, 'base64') as idocatarqu,                
                tdocatcada,
                cusupocodi,
                tdocatulat

                  FROM sfpc.tbdocumentoatarp
                 WHERE carpnosequ = %d
            ", $carpnosequ->getValor());

            
        $resultado = executarSQL($db, $sql);

        $documentos = array();
        $documento = null;
        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }
        if (PEAR::isError($resultado)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();

        return $documentos;
    }

    public function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
             WHERE 1=1
        ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    public function sqlConsultarProcurarAta($carpnosequ)
    {
        $sql = "
            SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $carpnosequ);
    }   
    
}

class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasAlterar extends Negocio_Abstrata
{

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar());
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    public function procurar($carpnosequ)
    {   
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar());
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarProcurarAta($carpnosequ);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    /**
     * [inserirDocumentoAta description]
     *
     * @param [type] $conexao
     *            [description]
     * @param [type] $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    private function inserirDocumentoAta($conexao, $carpnosequ)
    {
        $conexao->query(sprintf("DELETE FROM sfpc.tbdocumentoatarp WHERE carpnosequ = %d", $carpnosequ));

        $documento = $conexao->getRow('SELECT MAX(cdocatsequ) FROM sfpc.tbdocumentoatarp WHERE carpnosequ = ?', array(
            (int)$carpnosequ
        ), DB_FETCHMODE_OBJECT);
        $valorMax = (int)$documento->max + 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);


        

        

        $nomeTabela = 'sfpc.tbdocumentoatarp';
        $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);
       
        for ($i = 0; $i < $tamanho; $i++) {
            $entidade->carpnosequ = (int)$carpnosequ;
            $entidade->cdocatsequ = (int)$valorMax;
            $entidade->edocatnome = $_SESSION['Arquivos_Upload']['nome'][$i];  
     
            $entidade->idocatarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            
            // echo "<pre>";
            // echo "QUEBRANDOOOOOOOOOOO";
            // echo "<br />";
            // echo "<br />";
            // echo "<br />";
            // echo "<br />";

            // var_dump(ctype_xdigit($_SESSION['Arquivos_Upload']['conteudo'][$i]));

            // echo "<br />";
            // echo "<br />";
            // echo "<br />";
            // echo "<br />";
            // echo "<br />";

            // print_r($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            // echo "<br />";




            $entidade->tdocatcada = 'NOW()';
            $entidade->cusupocodi = (int)$_SESSION['_cusupocodi_'];
            $entidade->tdocatulat = 'NOW()';

            $conexao->autoExecute($nomeTabela, (array)$entidade, DB_AUTOQUERY_INSERT);

            if (ClaDatabasePostgresql::hasError($resultado)) {
                $conexao->rollback();
                return;
            }
            $valorMax++;
        }

        // die;
    }




    /**
     * [inserir description]
     *
     * @param [type] $entidade
     *            [description]
     * @return [type] [description]
     */
    public function inserir($entidade)
    {
        $conexao = ClaDatabasePostgresql::getConexao();
        $conexao->autoCommit(false);
        $aEntidade = (array)$entidade;
        unset($aEntidade['carpnosequ']);
        unset($aEntidade['ItemAtaRegistroPrecoNova']);
        

        if (isset($entidade->ItemAtaRegistroPrecoNova) === true) {
            $entidadeItemAta = array();
            foreach ($entidade->ItemAtaRegistroPrecoNova as $key => $value) {

                $codigoAta = $value['codigo_ata'];
                $codigoItem = $value['codigo_item'];

                $codigoAta = $value['codigo_ata'];
                $entidadeItemAta['fitarpsitu'] = $value['situacao'];

                $valorUnitario = moeda2float($value['valor_unitario']);
                if (empty($value['valor_unitario'])){
                    $valorUnitario = 0;
                }
                
                $quantidadeAtual = moeda2float($value['qtd_atual']);
                if (empty($value['qtd_atual'])){
                    $quantidadeAtual = 0;
                } 

                $entidadeItemAta['vitarpvatu'] = $valorUnitario;
                $entidadeItemAta['aitarpqtat'] = $quantidadeAtual;
                $conexao->autoExecute('sfpc.tbitemataregistropreconova', $entidadeItemAta, DB_AUTOQUERY_UPDATE, "carpnosequ = $codigoAta AND citarpsequ = $codigoItem");
            }
        }

        $resultado = $conexao->autoExecute('sfpc.tbataregistroprecointerna', $aEntidade, DB_AUTOQUERY_UPDATE, "carpnosequ =    $entidade->carpnosequ AND clicpoproc =     $entidade->clicpoproc AND alicpoanop =     $entidade->alicpoanop  AND corglicodi =     $entidade->corglicodi");

        if (ClaDatabasePostgresql::hasError($resultado)) {
            $conexao->rollback();
            return $resultado->getMessage();
        }

        $this->inserirDocumentoAta($conexao, $entidade->carpnosequ);
        $commited = $conexao->commit();

        if ($commited instanceof DB_error) {
            $conexao->rollback();

            return false;
        }

        unset($_SESSION['Arquivos_Upload']);

        return $resultado;
    }

    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function Negocio_carregarTodosDocumentosAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar());
        return $this->getDados()->Dados_carregarTodosDocumentosAta($carpnosequ);
    }
}

/**
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasAlterar extends Adaptacao_Abstrata
{

    /**
     * [consultarAtaPorChave description]
     *
     * @param [type] $processo
     *            [description]
     * @param [type] $orgao
     *            [description]
     * @param [type] $ano
     *            [description]
     * @param [type] $numeroAta
     *            [description]
     * @return [type] [description]
     */
    public function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $db     = Conexao();
        $dados  = new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar();
        $sql    = $dados->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
        return ClaDatabasePostgresql::executarSQL($sql, $db);
    }

    public function consultarExisteSccOuCaronaExterna($chaveAtaCod)
    {
        $retorno = false;
        $dados   = new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar();
        $db      = Conexao();
        
        $sql     = $dados->sqlExisteCaronaExterna($chaveAtaCod);

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($resultadoCountCarona, DB_FETCHMODE_OBJECT);
        
        $db->disconnect();
        
        $db = Conexao();

        $sql = $dados->sqlExisteScc($chaveAtaCod);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($resultadoCountScc, DB_FETCHMODE_OBJECT);

        $db->disconnect();
   
        if(($resultadoCountCarona->count != 0) || ( $resultadoCountScc->count != 0)){
            $retorno = true;
        }

        return $retorno;
    }

    public function consultarExisteAtaInternaAnoNumeracaoOrgao($ataCod, $orgao, $ano, $numeracao)
    {

        $dados   = new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar();
        $retorno = false;
        $db = Conexao();
        $sql = $dados->sqlExisteAtaInternaAnoNumeracaoOrgao($ataCod, $orgao, $ano, $numeracao);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($resultadoCount, DB_FETCHMODE_OBJECT);

        $db->disconnect();
       
        if($resultadoCount->count != 0){
            $retorno = true;
        }

        return $retorno;
    }

    /**
     * Executa a consulta dos órgãos participantes de uma ata.
     *
     * @param integer $processo da ata
     * @param integer $ano da ata
     * @param integer $orgaoGestor da ata
     *
     * @return NULL|stdClass
     */
    public static function consultarOrgaosParticipantesAta($processo, $ano, $orgaoGestor)
    {
        
        $db = Conexao();
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlOrgaosParticipantesAta($processo, $ano, $orgaoGestor, $seqAta);
        $res = executarSQL($db, $sql);

        $orgaos = array();
        $orgao = null;
        while ($res->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            $orgaos[] = $orgao;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();


         foreach($orgaos as $orgao) {
             $strOrgaos .= $orgao->eorglidesc . "<br />";
         }
 
         return $strOrgaos;
    }

    /**
     * [consultarItensAtaRegitroPreco description]
     *
     * @param [type] $ata
     *            [description]
     * @return [type] [description]
     */
     public function consultarItensAtaRegitroPreco($ata)
     {
        $db = Conexao();
        $sql = Dados_Sql_ItemAtaRegistroPrecoNova::sqlFind(new Negocio_ValorObjeto_Carpnosequ($ata[0]->carpnosequ));
        $res = executarSQL($db, $sql);
         
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        
        $db->disconnect();
        return $itens;
     }

    /**
     * [consultarLicitacaoAtaInterna description]
     *
     * @param [type] $ano
     *            [description]
     * @param [type] $processo
     *            [description]
     * @param [type] $orgaoUsuario
     *            [description]
     * @return [type] [description]
     */
    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     * [consultarValorMaximoDocumento description]
     *
     * @param [type] $processo
     *            [description]
     * @param [type] $orgao
     *            [description]
     * @param [type] $ano
     *            [description]
     * @param [type] $grupo
     *            [description]
     * @return [type] [description]
     */
    public function consultarValorMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $dados = new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar();
        $sql = $dados->sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo);
        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     * [procurar description]
     *
     * @param [type] $orgao
     *            [description]
     * @param [type] $ano
     *            [description]
     * @param [type] $processo
     *            [description]
     * @return [type] [description]
     */
    public function procurar($orgao, $ano, $processo, $ata)
    {
        $dados = new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasAlterar();
        return $dados->procurar($orgao, $ano, $processo, $ata);
    }

    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param [type] $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function carregarTodosDocumentosAta($carpnosequ)
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasAlterar());
        return $this->getNegocio()->Negocio_carregarTodosDocumentosAta(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
    }

    public function procurarAtaInterna($carpnosequ)
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasAlterar());
        return $this->getNegocio()->procurar($carpnosequ);
    }

    public function consultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi) {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasAlterar());
        return $this->getNegocio()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }

    public function sqlConsultarAtaNumeracaoAno($numeracao, $ano)
    {
        $sql = "  SELECT x.* FROM sfpc.tbataregistroprecointerna x
                    WHERE carpincodn = %d and aarpinanon = %d ";

        return sprintf($sql, $numeracao, $ano);
    }

    public function consultarAtaIterna($clicpoproc, $alicpoanop, $corglicodi, $aforcrsequ, $ccomlicodi)
    {
        $voClicpoproc = new Negocio_ValorObjeto_Clicpoproc($clicpoproc);
        $voAlicpoanop = new Negocio_ValorObjeto_Alicpoanop($alicpoanop);
        $voCorglicodi = new Negocio_ValorObjeto_Corglicodi($corglicodi);
        $voAforcrsequ = new Negocio_ValorObjeto_Aforcrsequ($aforcrsequ);
        $ccomlicodi = (int)$ccomlicodi;

        return $this->consultarAtaInterna($voClicpoproc, $voAlicpoanop, $voCorglicodi, $voAforcrsequ, $ccomlicodi);
    }

    public function consultarAtaInterna(
        Negocio_ValorObjeto_Clicpoproc $clicpoproc,
        Negocio_ValorObjeto_Alicpoanop $alicpoanop,
        Negocio_ValorObjeto_Corglicodi $corglicodi,
        Negocio_ValorObjeto_Aforcrsequ $aforcrsequ,
        $ccomlicodi
    )
    {
        $resultadoAta = array();
        $db = Conexao();

        $sql  = "SELECT *";
        $sql .= " FROM sfpc.tbataregistroprecointerna";
        $sql .= " WHERE";
        $sql .= " clicpoproc = %d";
        $sql .= " and alicpoanop = %d";
        $sql .= " and corglicodi = %d";
        $sql .= " and aforcrsequ = %d";
        $sql .= " and ccomlicodi = %d";
        /*$ata = executarSQL($db,
            sprintf(
                $sql,
                $clicpoproc->getValor(),
                2016,
                7,
                4712
            )
        );*/
        $resultadoAta = null;        
        $ata = executarSQL($db,
            sprintf(
                $sql,
                $clicpoproc->getValor(),
                $alicpoanop->getValor(),
                $corglicodi->getValor(),
                $aforcrsequ->getValor(),
                $ccomlicodi
            )
        );
        $ata->fetchInto($resultadoAta, DB_FETCHMODE_OBJECT);
        
        ClaDatabasePostgresql::hasError($resultado);
        
        if (empty($resultadoAta) == true) {
            $_SESSION['mensagemFeedback'] = 'A ata não existe';
            return false;
        }

        return $resultadoAta;
    }

    public function consultarNumeroAtaInterna($processo, $alicpoanop, $aarpinanon, $corglicodi, $carpincodn, $cgrempcodi, $ccomlicodi) {
        $resultadoAta = array();
        $db = Conexao();

        $sql  = "SELECT *";
        $sql .= " FROM sfpc.tbataregistroprecointerna";
        $sql .= " WHERE ";
        $sql .= " aarpinanon = %d and corglicodi = %d and carpincodn = %d ";       

        $resultadoAta = null;
        $ata = executarSQL($db, sprintf($sql,$aarpinanon, $corglicodi, $carpincodn));

        $ata->fetchInto($resultadoAta, DB_FETCHMODE_OBJECT);
        ClaDatabasePostgresql::hasError($resultado);
        
        if (empty($resultadoAta) == true) {
            return false;
        }

        return $resultadoAta;
    }

}

class RegistroPreco_UI_CadAtaRegistroPrecoInternaManterAtasAlterar extends UI_Abstrata
{

    /**
     * Coletar Documentos adicionado via SESSION ou via Banco de Dados
     *
     * @return void [description]
     */
    private function coletarDocumentosAdicionados($ata)
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasAlterar());
        $documentosAta = $this->getAdaptacao()->carregarTodosDocumentosAta($ata->carpnosequ);
        
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            unset($_SESSION['Arquivos_Upload']);
            $i = 0;
            foreach ($documentosAta as $documento) {
                $i++;

                $documentoHexDecodificado = base64_decode($documento->idocatarqu);
                $documentoToBin = $this->hextobin($documentoHexDecodificado);

                $_SESSION['Arquivos_Upload']['conteudo'][] = $documentoToBin;
                $_SESSION['Arquivos_Upload']['nome'][] = $documento->edocatnome;
            }
        }

        
        $qtdeDocumentos = sizeof($_SESSION['Arquivos_Upload']['nome']);

        if ($qtdeDocumentos > 0 && isset($_SESSION['Arquivos_Upload']['nome'])) {
            for ($i = 0; $i < $qtdeDocumentos; $i++) {
                $this->getTemplate()->ID_DOCUMENTO = $i;
                $this->getTemplate()->NOME_DOCUMENTO = $_SESSION['Arquivos_Upload']['nome'][$i];
                $this->getTemplate()->block('BLOCO_DOCUMENTO');
            }
        }
    }


    function hextobin($hexstr) 
    { 
        $n = strlen($hexstr); 
        $sbin="";   
        $i=0; 
        while($i<$n) 
        {       
            $a =substr($hexstr,$i,2);           
            $c = pack("H*",$a); 
            if ($i==0){$sbin=$c;} 
            else {$sbin.=$c;} 
            $i+=2; 
        } 
        return $sbin; 
    } 

    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaManterAtasAlterar.html", "Registro de Preço > Ata Interna > Manter");
        $this->setTemplate($template);
    }

    /**
     *
     * @param integer $ano
     * @param integer $orgao
     * @param integer $processo
     * @param integer $ata
     */
    public function plotarBlocoBotao($ano, $orgao, $processo, $ata)
    {
        $this->getTemplate()->VALOR_ANO_SESSAO = $ano;
        $this->getTemplate()->VALOR_ORGAO_SESSAO = $orgao;
        $this->getTemplate()->VALOR_PROCESSO_SESSAO = $processo;
        $this->getTemplate()->VALOR_ATA_SESSAO = $ata;
        $this->getTemplate()->block("BLOCO_BOTAO");
    }

    /**
     *
     * @param integer $licitacao
     * @param integer $ata
     * @param integer $dataInformada
     * @param integer $vigenciaInformada
     */
    public function plotarBlocoLicitacao($licitacao, $ata)
    {
        $licitacao  = current($licitacao);
        $ata        = current($ata);
        $adaptacao  = new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasAlterar();
        
        $numeroAta  = explode('.', $this->getNumeroAtaInterna($ata, $adaptacao));

        if(!isset($_POST['valor_ata_numero'])) {
            $valorNumeroAta = explode('/',$numeroAta[1]);
        } else {
            $valorNumeroAta[0] = $_POST['valor_ata_numero'];
            $valorNumeroAta[1] = $_POST['valor_ata_ano'];
        }

        $this->getTemplate()->VALOR_COMISSAO = $licitacao->ecomlidesc;
        $this->getTemplate()->VALOR_CODIGO_COMISSAO = $licitacao->ccomlicodi;
        $this->getTemplate()->VALOR_CODIGO_GRUPO = $licitacao->cgrempcodi;
        $this->getTemplate()->VALOR_PROCESSO = str_pad($licitacao->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO = $licitacao->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE = $licitacao->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO = str_pad($licitacao->clicpocodl, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO = $licitacao->alicpoanol;
        $this->getTemplate()->VALOR_ORG_LIMITE = $licitacao->eorglidesc;
        $this->getTemplate()->VALOR_PARTICIPANTES = $adaptacao->
            consultarOrgaosParticipantesAta($licitacao->clicpoproc, $licitacao->alicpoanop, $licitacao->corglicodi);
        $this->getTemplate()->VALOR_OBJETO = (empty($ata->earpinobje)) ? $licitacao->xlicpoobje : $ata->earpinobje;
        $this->getTemplate()->VALOR_ATA         = $numeroAta[0];
        $this->getTemplate()->VALOR_ATA_NUMERO  = $valorNumeroAta[0];
        $this->getTemplate()->VALOR_ATA_ANO     = $valorNumeroAta[1];

        if (isset($_POST['data']) === true) {
            $dataInicial = $_POST['data'];
        } else {
            $timeDataInicial = strtotime($ata->tarpindini);
            $dataInicial = date('d/m/Y', $timeDataInicial);
        }
        $this->getTemplate()->VALOR_DATA = $dataInicial;

        if (isset($_POST['vigencia']) === true) {
            $vigencia = $_POST['vigencia'];
        } else {
            $vigencia = $ata->aarpinpzvg;
        }
        $this->getTemplate()->VALOR_VIGENCIA = $vigencia;
        $this->getTemplate()->VALOR_DOCUMENTO = $ata->xlicpoobje;
        $this->getTemplate()->VALOR_FORNECEDOR = $ata->nforcrrazs;
        $this->getTemplate()->VALOR_CODIGO_FORNECEDOR = $ata->aforcrsequ;

        $this->coletarDocumentosAdicionados($ata);
        $this->getTemplate()->block("BLOCO_FILE");
        $this->getTemplate()->block("BLOCO_LICITACAO");
    }

    private function getNumeroAtaInterna($ata, $adaptacao)
    {
        
        $dto = $adaptacao->consultarCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objeto = current($dto);
        $ataInterna = current($adaptacao->procurarAtaInterna((int)$ata->carpnosequ));

        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

        return $numeroAtaFormatado;
    }

    /**
     *
     * @param array $itensAta
     */
    public function plotarBlocoItemAta($itensAta)
    {
        if ($itensAta == null) {
            return;
        }

        $formData = $_POST;
        $count = 0;
        $valorTotalAta = 0;

        foreach ($itensAta as $item) {
            // CADUM = material e CADUS = serviço
            $tipo = 'CADUM';
            if (is_null($item->cmatepsequ) == true) {
                $tipo = 'CADUS';
            }

            // Código do item
            $valorCodigo = $item->cmatepsequ;
            if ($tipo == 'CADUS') {
                $valorCodigo = $item->cservpsequ;
            }

            // Descrição do item
            $valorDescricao = $item->ematepdesc;
            if ($tipo === 'CADUS') {
                $valorDescricao = $item->eservpdesc;
            }

            $this->getTemplate()->VALOR_MARCA = $item->eitarpmarc;
            $this->getTemplate()->VALOR_MODELO = $item->eitarpmode;

            $this->getTemplate()->VALOR_ORDEM = $item->aitarporde;
            $this->getTemplate()->VALOR_TIPO = $tipo;
            $this->getTemplate()->VALOR_CADUS = $valorCodigo;  // Código Sequencial do Material OU Código sequencial do serviço
            $this->getTemplate()->VALOR_DESCRICAO = $valorDescricao;
            $this->getTemplate()->VALOR_UND = $item->eunidmsigl;
            $this->getTemplate()->VALOR_QTD_ORIGINAL = converte_valor_estoques($item->aitarpqtor);
            $this->getTemplate()->VALOR_UNITARIO_ORIGINAL = converte_valor_estoques($item->vitarpvori);
            $this->getTemplate()->VALOR_TOTAL_ORIGINAL = converte_valor_licitacao($item->aitarpqtor * $item->vitarpvori);
            $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;
            $this->getTemplate()->VALOR_QTD_ATUAL = converte_valor_licitacao($item->aitarpqtat);
            $this->getTemplate()->VALOR_VALOR_ATUAL = converte_valor_licitacao($item->vitarpvatu);
            $this->getTemplate()->VALOR_TOTAL_ATUAL = converte_valor_licitacao($item->aitarpqtat * $item->vitarpvatu);
            $this->getTemplate()->CODIGO_ITEM = $item->citarpsequ;
            $this->getTemplate()->CODIGO_ATA = $item->carpnosequ;

            $valorTotal += $item->aitarpqtat * $item->vitarpvatu;

            if ($item->fitarpsitu == 'A') {
                $this->getTemplate()->VALOR_SITUACAO_ATIVO = 'selected';
                $this->getTemplate()->VALOR_SITUACAO_INATIVO = '';
            }
            else {
                $this->getTemplate()->VALOR_SITUACAO_INATIVO = 'selected';
                $this->getTemplate()->VALOR_SITUACAO_ATIVO = '';
            }
            $this->getTemplate()->CONTADOR = $count;
            $count++;

            if (isset($formData['ItemAtaRegistroPrecoNova']) == true && is_array($formData['ItemAtaRegistroPrecoNova']) == true) {
                foreach ($formData['ItemAtaRegistroPrecoNova'] as $key => $value) {
                    $this->getTemplate()->VALOR_QTD_ATUAL = converte_valor_licitacao($value['qtd_atual']);
                    $this->getTemplate()->VALOR_VALOR_ATUAL = converte_valor_licitacao($value['valor_unitario']);
                    
                    if ($value['codigo_ata'] == $item->citarpsequ) {
                        // $this->getTemplate()->VALOR_VALOR_ATUAL = converte_valor_licitacao($value['valor_unitario']);
                        if ($value['situacao'] == 'A') {
                            $this->getTemplate()->VALOR_SITUACAO_ATIVO = 'selected';
                            $this->getTemplate()->VALOR_SITUACAO_INATIVO = '';
                        }
                        else {
                            $this->getTemplate()->VALOR_SITUACAO_INATIVO = 'selected';
                            $this->getTemplate()->VALOR_SITUACAO_ATIVO = '';
                        }
                    }
                }
            }
            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }

        $this->getTemplate()->TOTAL_ATA = converte_valor_licitacao($valorTotal);

    }

    /**
     *
     * @param unknown $processo
     * @param unknown $orgao
     * @param unknown $ano
     * @param unknown $grupo
     * @param unknown $comissao
     */
    public function montaValoresInsercaoDocumento($processo, $orgao, $ano, $grupo, $comissao)
    {
        $timestamp = date('U');
        $swatch = date('B');

        $now = $timestamp . $swatch;

        $docCodMAx = 1;
        $docNome = "Documento.txt";
        $valores = $processo . "," . $ano . "," . $grupo . "," . $comissao . "," . $orgao . "," . $docCodMAx . "," . $docNome . "," . $timestamp . "," . $_SESSION['_cusupocodi_'] . "," . $now;
    }
}

class CadAtaRegistroPrecoInternaManterAtasAlterar
{

    /**
     *
     * @var UI_Interface
     */
    private $gui;

    /**
     *
     * @param UI_Interface $gui
     */
    private function setUI(UI_Interface $gui)
    {
        $this->gui = $gui;
    }

    /**
     *
     * @return UI_Interface
     */
    private function getUI()
    {
        return $this->gui;
    }

    /**
     */
    private function proccessPrincipal()
    {
        $orgao      = isset($_REQUEST['orgao']) ? filter_var($_REQUEST['orgao'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['orgao'];
        $ano        = isset($_REQUEST['ano']) ? filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['ano'];
        $processo   = isset($_REQUEST['processo']) ? $_REQUEST['processo'] : $_SESSION['processo'];
        $ata        = isset($_REQUEST['ata']) ? filter_var($_REQUEST['ata'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['ata'];

        $_SESSION["orgao"] = $orgao;
        $_SESSION["ano"] = $ano;
        $_SESSION["processo"] = $processo;
        $_SESSION["ata"] = $ata;
        
        $codProcesso = explode("-", $processo);
        
        $this->getUI()->plotarBlocoBotao($ano, $orgao, $codProcesso[0], $ata);
        
        $adaptacao  = new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasAlterar();    
        $atas       = $adaptacao->consultarAtaPorChave($ano, $codProcesso[0], $orgao, $ata);
        $licitacao  = $adaptacao->consultarLicitacaoAtaInterna($ano, $codProcesso[0], $orgao);
        $itens      = $adaptacao->consultarItensAtaRegitroPreco($atas);

        $this->getUI()->plotarBlocoLicitacao($licitacao, $atas);
        $this->getUI()->plotarBlocoItemAta($itens);
    }

    /**
     */
    private function insereDocumento()
    {
        $arquivoInformado = $_FILES['fileArquivo'];

        if ($arquivoInformado['size'] == 0) {
            $this->getUI()->blockErro("É preciso Informar um Arquivo");
            return;
        }

        $arquivo = new Arquivo();
        $arquivo->setExtensoes('pdf');
        $arquivo->setTamanhoMaximo(2000000);

        $arquivo->configurarArquivo();

        if (isset($_SESSION['mensagemFeedback'])){
            $this->getUI()->mensagemSistema($_SESSION['mensagemFeedback'], 0);
        }
    }

    /**
     */
    private function removeDocumento()
    {
        $idDocumento = filter_input(INPUT_POST, 'documentoExcluir', FILTER_VALIDATE_INT);

        if (!is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);
    }

    /**
     */
    private function processVoltar()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];

        $uri = 'CadAtaRegistroPrecoInternaManterAtas.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao;
        header('location: ' . $uri);
    }

    private function validarAtaAlterarNumeracao($ano, $codProcesso, $codOrgao, $ata, $postForm, $flagExclusao){ 
       
        $adaptacao              = new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasAlterar();
        $retorno                = true;
        $erros                  = false;
        $valores                = '';
        $mensagemPassada        = "";
        $ata                    = $adaptacao->consultarAtaPorChave($ano, $codProcesso, $codOrgao, $ata);
        $numeroAtaConsultada    = str_pad($ata[0]->carpincodn, 4, "0", STR_PAD_LEFT);
        $anoAtaConsultada       = $ata[0]->aarpinanon;        

        if($postForm['valor_ata_numero'] == ""){
            $erros = true;
            $mensagemPassada .= "<a href=\"javascript:document.getElementById('valorAta').focus();\" class='titulo2'>O campo de número da ata é obrigatório; </a> ";
        }

        if($postForm['valor_ata_ano'] == ""){
            $erros = true;
            $mensagemPassada .= "<a href=\"javascript:document.getElementById('anoAta').focus();\" class='titulo2'>O campo de ano da ata é obrigatório; </a> ";
        }

        if(!$erros){
            if(( ($anoAtaConsultada != $postForm['valor_ata_ano']) || ($numeroAtaConsultada != $postForm['valor_ata_numero']) ) || $flagExclusao ){
                $contemCaronaExternaOuScc = $adaptacao->consultarExisteSccOuCaronaExterna($ata[0]->carpnosequ);
                if($contemCaronaExternaOuScc){
                    $erros = true;
                    if($flagExclusao){
                        $mensagemPassada = 'Não é possível excluir a ata, pois esta ata já está relacionada com uma Solicitação de Compra do tipo SARP ou Carona Externa';
                    }else{
                        $mensagemPassada = 'Não é possível alterar a numeração, pois esta ata já está relacionada com uma Solicitação de Compra do tipo SARP ou Carona Externa';
                    }
                }            
            }
            if(!$flagExclusao){
                if($adaptacao->consultarExisteAtaInternaAnoNumeracaoOrgao($ata[0]->carpnosequ, $ata[0]->corglicodi, $postForm['valor_ata_ano'], $postForm['valor_ata_numero'])){
                    $erros = true;
                    $mensagemPassada = 'Não é possível alterar a numeração, pois esta nova numeração já foi cadastrada para este órgão';
                }
            }
        }

        if($erros){
            $_SESSION['mensagemFeedback'] = $mensagemPassada;
            unset($_SESSION['mensagemFeedback']);
            $retorno = false;
        }

        return $retorno;
    }

    /**
     * Verificar ata com mesma numeração
     * 
     * @param $numeracao
     * @param $ano
     * 
     * @return bool
     */
    public function contemAtaComMesmaNumeracaoEAno($numeracao, $ano)
    {
        $adaptacao              = new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasAlterar();
        $database = Conexao();
        $sql = $adaptacao->sqlConsultarAtaNumeracaoAno($numeracao, $ano);
        $resultado = executarSQL($database, $sql);
        $documentos = array();
        $documento = null;
        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }    
        
        $ataC = $adaptacao->consultarAtaIterna(intval($_GET['processo']), intval($_REQUEST['valor_ata_ano']), intval($_GET['orgao']), intval($_REQUEST['fornecedor']), $_REQUEST['codigocomissao']);
        
        
        $retorno = false;        
        
        if(count($documentos->carpnosequ) > 1){
            $retorno = true;
        }else{
            if(!empty($documentos) && !empty($ataC)){                
                foreach ($documentos as $key => $value) {
                    if(
                        !is_null($ataC->carpnosequ) && 
                        $value->clicpoproc == $ataC->clicpoproc &&
                        //$value->aforcrsequ == $ataC ->aforcrsequ &&
                        $value->alicpoanop == $ataC->alicpoanop &&
                        $value->aarpinanon == $ataC->aarpinanon &&
                        $value->corglicodi == $ataC->corglicodi &&
                        $value->ccomlicodi == $ataC->ccomlicodi &&
                        $value->cgrempcodi == $ataC->cgrempcodi &&
                        $value->tarpinulat != $ataC->tarpinulat
                    ){
                        $retorno = true;
                    }
                }
            }else{
                if(empty($ataC)) {                    
                    $_ataC = $adaptacao->consultarNumeroAtaInterna(
                        intval($_GET['processo']),
                        intval($_REQUEST['ano']), 
                        intval($_REQUEST['valor_ata_ano']),                         
                        intval($_GET['orgao']),                         
                        intval($_REQUEST['valor_ata_numero']),
                        intval($_REQUEST['codigoGrupo']),
                        intval($_REQUEST['codigocomissao'])
                    ); 
                    if(!empty($_ataC) && count($_ataC) > 1) {
                        if(
                            $_GET['processo']           != $_ataC->clicpoproc ||
                            $_REQUEST['fornecedor']     != $_ataC->aforcrsequ ||
                            $_REQUEST['ano']            != $_ataC->alicpoanop ||
                            $_REQUEST['valor_ata_ano']  != $_ataC->aarpinanon ||
                            $_GET['orgao']              != $_ataC->corglicodi ||
                            $_REQUEST['codigocomissao'] != $_ataC->ccomlicodi ||
                            $_REQUEST['codigoGrupo']    != $_ataC->cgrempcodi
                        ) {
                            return true;
                        }
                    }                

                }
                //if(count($_SESSION['Arquivos_Upload']['nome']) <= 0) {
                  //  $retorno = true;
                //} 
            }
        }

        return ($retorno);
    }

    private function salvar()
    {

        try {
            $mensagem = '';
            $orgao          = $_SESSION["orgao"];
            $ano            = $_SESSION["ano"];
            $processo       = $_SESSION["processo"];
            $ata            = $_SESSION["ata"];
            $contMensagem   = 0;
            if (empty($_POST['data'])) {
                $mensagem = 'Data não foi informada<br>';
            }
            
            if (empty($_POST['vigencia'])) {
                $mensagem .= 'Vigência não foi informada<br>' ;
            }
            
            if (empty($_POST['vigencia'])) {
                $mensagem .= 'Vigência não foi informada<br>' ;
            }   
            
            if($this->contemAtaComMesmaNumeracaoEAno($_REQUEST['valor_ata_numero'] ,$_REQUEST['valor_ata_ano'])){
                $mensagem .= 'Já existe uma ata cadastrada com o mesmo número e ano informado<br>' ;
            }
            
            /*if(!$this->validarAtaAlterarNumeracao($ano, $processo, $orgao, $ata, $_POST, false)){
                $mensagem .= 'Já existe uma ata cadastrada com o mesmo número e ano informado<br>' ;
            }*/

            // descomentar para validar o arquivo
            /*$tamanho = count($_SESSION['Arquivos_Upload']['nome']);
            if ($tamanho == 0) { 
                $mensagem .= 'É preciso Informar um Arquivo<br>';
            }*/

            if (!empty($mensagem)) {
                throw new DomainException($msgFinal);
            }           

            $codProceso = explode('-', $processo);
            $this->getUI()->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasAlterar());
            $entidade = $this->getUI()
                ->getAdaptacao()
                ->procurar($orgao, $ano, $codProceso[0], $ata);

            $dataBr = explode('/', $_POST['data']);
            $data = $dataBr[2] . '-' . $dataBr[1] . '-' . $dataBr[0];
            date_default_timezone_set('America/Recife');
            $dataLimite = new DateTime($data);

            $entidade->tarpindini = (string)$dataLimite->format('Y-m-d H:i:s');
            $entidade->aarpinpzvg = filter_var($_POST['vigencia'], FILTER_SANITIZE_NUMBER_INT);
            $entidade->ItemAtaRegistroPrecoNova = $_POST['ItemAtaRegistroPrecoNova'];
            $entidade->tarpinulat = date('Y-m-d H:i:s');
            $entidade->carpincodn =  $_REQUEST['valor_ata_numero'];
            $entidade->aarpinanon =  $_REQUEST['valor_ata_ano'];

            $this->getUI()
                ->getAdaptacao()
                ->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasAlterar());

            $this->getUI()
                ->getAdaptacao()
                ->getNegocio()
                ->inserir($entidade);

            $_SESSION['mensagemFeedback'] = 'Ata de Registro de Preço alterada com sucesso';

            header('Location: CadAtaRegistroPrecoInternaManter.php');
            exit();
            /*
             * Libera espaço na session após os documentos serem adicionados
             */
            unset($_SESSION['Arquivos_Upload']);
        } catch (Exception $e) {
            $mensagem = substr_replace($mensagem, "<br>", -1);
            $this->getUI()->blockErro($mensagem, 0);
        }
    }

    /**
     */
    private function frontController()
    {
        $botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

        switch ($botao) {
            case 'Voltar' :
                $this->processVoltar();
                break;
            case 'Salvar' :
                $this->salvar();
                $this->proccessPrincipal();
                break;
            case 'Inserir' :
                $this->insereDocumento();
                $this->proccessPrincipal();
                break;
            case 'Remover' :
                $this->removeDocumento();
                $this->proccessPrincipal();
                break;
            case 'Principal' :
            default :
                $this->proccessPrincipal();
                break;
        }
    }

    /**
     */
    public function __construct()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoInternaManterAtasAlterar());
        /**
         * Front Controller for action
         */
        $this->frontController();
    }

    /**
     * Running the application
     */
    public function run()
    {
        /**
         * Rendering the application
         */
        return $this->getUI()
            ->getTemplate()
            ->show();
    }
}
 
$programa = new CadAtaRegistroPrecoInternaManterAtasAlterar();
echo $programa->run();
unset($programa);
