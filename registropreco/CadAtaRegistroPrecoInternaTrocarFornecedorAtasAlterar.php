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
 * @version   GIT: v1.8.0-278-g503dc67
 */
#------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 28/06/2018
# Objtivo: Tarefa Redine #194536
#------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
session_start();
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar extends Dados_Abstrata
{

    /**
     *
     * @param unknown $entidade
     * @param unknown $nomeTabela
     * @param unknown $conexao
     * @throws Exception
     * @return void|unknown
     */
    public function factoryInsere($entidade, $nomeTabela, $conexao = null, $condicao = null)
    {
        if (is_null($conexao)) {
            $conexao = ClaDatabasePostgresql::getConexao();
        }

        if (! $conexao instanceof DB_pgsql) {
            throw new Exception("Error Processing Request", 1);
            return;
        }

        $tabela = $conexao->tableInfo($nomeTabela);

        if (!is_array($tabela)) {
            throw new Exception("Error Processing Request", 1);
            return;
        }

        if (is_null($condicao)){
            $res = $conexao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);
        } else {
            $res = $conexao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_UPDATE, $condicao);
        }


        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function carregarTodosDocumentosAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
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

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function numeroAnoAtaExistente($orgao, $numeroAta, $anoAta)
    {
        $sql = "
        SELECT COUNT(*)
        FROM SFPC.TBATAREGISTROPRECOINTERNA
        WHERE CORGLICODI = %d
        AND CARPINCODN = %d
        AND AARPINANON = %d
        ";
        
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), sprintf($sql, $orgao, $numeroAta, $anoAta));
        ClaDatabasePostgresql::hasError($resultado);
        $resultado->fetchInto($resultado, DB_FETCHMODE_OBJECT);

        return $resultado->count;
    }

    public function qtdSolicitacaoCompraAta($seqAta)
    {
        $sql = "
        SELECT COUNT(*) 
        FROM SFPC.TBSOLICITACAOCOMPRA
        WHERE CARPNOSEQU = %d
        ";
        
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), sprintf($sql, $seqAta));
        ClaDatabasePostgresql::hasError($resultado);
        $resultado->fetchInto($resultado, DB_FETCHMODE_OBJECT);

        return $resultado->count;
    }

    public function getSeqAtaPorCodigoDaNumeracao($carpincodn)
    {
        $sql = sprintf("
        SELECT 
            carpnosequ
        FROM sfpc.tbataregistroprecointerna
        WHERE carpincodn = %d", $carpincodn);

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado[0]->carpnosequ;
    }

    /**
     *
     * @param unknown $codigo
     * @return unknown
     */
    public function consultarFornecedorPorCodigo($codigo)
    {
        $sql = "
            SELECT f.aforcrsequ,f.nforcrrazs ,f.eforcrlogr,f.aforcrccgc
            FROM sfpc.tbfornecedorcredenciado f
            WHERE f.aforcrsequ = $codigo
        ";

        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param unknown $codigo
     * @return unknown
     */
    public function consultarFornecedorPorAta($seqAta)
    {
        $sql = "
            SELECT * 
            FROM sfpc.tbfornecedorcredenciado f 
                INNER JOIN sfpc.tbataregistroprecointerna a ON f.aforcrsequ = a.aforcrsequ
            WHERE a.carpnosequ = $seqAta
        ";

        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
    //madson
    /**
     *
     * @param unknown $codigo
     * @return unknown
     */
    public function consultarTodosDocumentosAta($carpnosequ)
    {
        $db = Conexao();
        $sql = "
            SELECT edocatnome FROM sfpc.tbdocumentoatarp WHERE carpnosequ = ".$carpnosequ;
       
        $resultado = executarSQL($db, $sql);
        $documentos = array();
        $documento = null;
        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }
        ClaDatabasePostgresql::hasError($resultado);
        $resultado->fetchInto($resultado, DB_FETCHMODE_OBJECT);
        $db->disconnect();
        return $documentos;
    }

    /**
     * [consultarFornecedorPorCdigo description]
     *
     * @param [type] $codigo
     *            [description]
     * @return [type] [description]
     */
    public function consultarFornecedorPorCpnjOrCpf($codigo)
    {
        $sql = "
            SELECT *
            FROM sfpc.tbfornecedorcredenciado f
        ";

        if (strlen($codigo) > 11) {
            $sql .= "WHERE f.aforcrccgc LIKE '$codigo'";
        } else {
            $sql .= "WHERE f.aforcrccpf  LIKE '$codigo'";
        }

        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param integer $processo
     * @param integer $orgao
     * @param integer $ano
     * @param integer $numeroAta
     * @return NULL
     */
    public function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoNova();
        return $repositorio->consultarAtaPorChave($processo, $orgao, $ano, $numeroAta);
    }

    /**
     *
     * @param integer $ano
     * @param integer $processo
     * @param integer $orgaoUsuario
     * @return unknown
     */
    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param integer $carpnosequ
     * @return NULL
     */
    public function consultarItensAtaNova($carpnosequ)
    {
        $db = Conexao();
        $sql = Dados_Sql_ItemAtaRegistroPrecoNova::sqlFind(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
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
     *
     * @param unknown $processo
     * @param unknown $orgao
     * @param unknown $ano
     * @param unknown $grupo
     * @return NULL
     */
    public function consultarValorMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $documento = null;
        $sql = "
            SELECT MAX(d.cdocatsequ)
            FROM sfpc.tbdocumentoatarp d
            WHERE d.carpnosequ = %d
        ";

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), sprintf($sql, $ata));

        ClaDatabasePostgresql::hasError($resultado);

        $resultado->fetchInto($documento, DB_FETCHMODE_OBJECT);

        return $documento;
    }

    /**
     *
     * @return number
     */
    public function getUltimoIdAtaNova()
    {
        $resultado = current(ClaDatabasePostgresql::executarSQL(Dados_Sql_AtaRegistroPrecoNova::sqlMaximoId()));

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado->carpnosequ + 1;
    }

    /**
     *
     * @param aarray $ata
     */
    public function consultarParticipantesAta($ata)
    {
        $sql = "select ol.eorglidesc from sfpc.tbparticipanteatarp parp";
        $sql .= " inner join sfpc.tborgaolicitante ol";
        $sql .= " on ol.corglicodi = parp.corglicodi";
        $sql .= " where parp.carpnosequ=" . $ata;

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
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
     public static function consultarOrgaosParticipantesAta($processo, $ano, $orgaoGestor, $seqAta = null)
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
     * Executa o código dos órgãos participantes de uma ata.
     *
     * @param integer $processo da ata
     * @param integer $ano da ata
     * @param integer $orgaoGestor da ata
     *
     * @return NULL|stdClass
     */
     public static function consultarParticipantesAtaInterna($processo, $ano, $orgaoGestor, $carpnosequ)
     {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlParticipantesAta($processo, $ano, $orgaoGestor, $carpnosequ);
        return ClaDatabasePostgresql::executarSQL($sql);
     }
     
     public static function consultarParticipantesItensPorSeqAta($carpnosequ)
     {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlParticipantesItensAta($carpnosequ);
        return ClaDatabasePostgresql::executarSQL($sql);
     }

    public function consultarSCCDoProcesso($ata, $orgao, $codigoItem, $tipoItem, $seqItem)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlConsultarSCCDoProcesso($ata, $orgao, $codigoItem, $tipoItem, $seqItem);
        return ClaDatabasePostgresql::executarSQL($sql);

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

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar extends Negocio_Abstrata
{

    private $orgao;

    private $ano;

    private $ata;

    private $fornecedorAtual;

    private $tipoFornecedor;

    private $fornecedorOriginal;

    private $carpnosequ;

    const TAMANHO_MAXIMO = 2000000;

    const TIPO_ARQUIVO_VALIDO = 'doc, odt, pdf';

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar());
        return parent::getDados();
    }

    /**
     * [_especificacaoCamposObrigatorio description]
     *
     * @return [type] [description]
     */
    private function _especificacaoCamposObrigatorio()
    {
        return array(
            array(
                'campo' => 'fornecedorSelecionado',
                'text' => 'Fornecedor Atual',
                'href' => 'javascript:document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.fornecedorSelecionado.focus()'
            ),
            array(
                'campo' => 'novoNumAta',
                'text' => 'Novo Nº da Ata Interna (Número Ata)',
                'href' => 'javascript:document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.novoNumAta.focus()'
            ),
            array(
                'campo' => 'novoAnoAta',
                'text' => 'Novo Nº da Ata Interna (Ano Ata)',
                'href' => 'javascript:document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.novoAnoAta.focus()'
            ),
            array(
                'campo' => 'fileArquivo',
                'text' => 'Documento(s)',
                'href' => ''
            )
        );
    }

    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function carregarTodosDocumentosAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        return $this->getDados()->carregarTodosDocumentosAta($carpnosequ);
    }

    private function numeroAnoAtaExistente($orgao, $numeroAta, $anoAta)
    {
        $numeroAnoAtaExistente = $this->getDados()->numeroAnoAtaExistente($orgao, $numeroAta, $anoAta) > 0;
        
        return $numeroAnoAtaExistente;
    }
    

    /**
     * Valida
     *
     * @return boolean true se não houve erro ou false caso contrário
     */
    public function validacao()
    {
        if (isset($_SESSION['mensagemFeedback'])) {
            unset($_SESSION['mensagemFeedback']);
        }

        $mensagem = array();
        $retorno = true;
        $listaCampos = array();

        $elem = new Element('a');
        $elem->set('class', 'titulo2');

        if ($_POST['efetuarDesfazerTroca'] == 'Desfazer Troca'){
            if($this->existeSolicitacaoCompra()){
                $_SESSION['mensagemFeedback'] = "A Troca de Fornecedor não pode ser desfeita, pois já houve uma solicitação de compra associada à nova ata";
		$mensagem = "A Troca de Fornecedor não pode ser desfeita, pois já houve uma solicitação de compra associada à nova ata";
                $retorno = false;
            }
        } else {
            $qtdArquivosInseridos = $_POST['qtdArquivosInseridos'];
            $camposObrigatorios = $this->_especificacaoCamposObrigatorio();
            $listaCampos = array();

            if (empty($_POST['fornecedorSelecionado']) === true){
                array_push($listaCampos,$camposObrigatorios[0]);
            }

            if (empty($_POST['novoNumAta']) === true ){
                array_push($listaCampos,$camposObrigatorios[1]);
            }

            if (empty($_POST['novoAnoAta']) === true) {
                array_push($listaCampos,$camposObrigatorios[2]);
            }

            if (empty($_SESSION['Arquivos_Upload']['nome'])) {
                array_push($listaCampos,$camposObrigatorios[3]);
            }

            foreach ($listaCampos as $value) {
                $campo = isset($value['filter']) ? filter_input(INPUT_POST, $value['campo'], $value['filter']) : filter_input(INPUT_POST, $value['campo']);
                if (empty($campo)) {
                    $elem->set('text', $value['text']);

                    if (!empty($value['href'])){
                        $elem->set('href', $value['href']);
                    }

                    if ($value === end($listaCampos)) {
                        $mensagem[] = $elem->build() . ' deve ser informado';
                    } else {
                        $mensagem[] = $elem->build() . ' deve ser informado <br />';
                    }

                    $retorno = false;
                }
            }

            $existeNumeroAnoAta = $this->numeroAnoAtaExistente($_REQUEST['orgao'], $_POST['novoNumAta'], $_POST['novoAnoAta']);
            if ($existeNumeroAnoAta){
                $elem->set('text', 'Número da Nova Ata Interna já Existe');
                $elem->set('href', '');
                $mensagem[] = $elem->build();
                $retorno = false;
            }

            $fornecedorAtual = preg_replace("/[^0-9]/", "", filter_var($_REQUEST['fornecedorSelecionado'], FILTER_SANITIZE_STRING));
            $fornecedorOriginal = preg_replace("/[^0-9]/", "", filter_var($_REQUEST['fornecedorOriginal'], FILTER_SANITIZE_STRING));

            $tipoFornecedor = filter_var($_REQUEST['tipoFornecedor'], FILTER_SANITIZE_NUMBER_INT);

            $fornecedorAtual = preg_replace("/[^0-9]/", "", $fornecedorAtual);
            $fornecedorOriginal = preg_replace("/[^0-9]/", "", $fornecedorOriginal);

            if (1 == $this->tipoFornecedor && ! valida_CNPJ($fornecedorAtual)) {
                $elem->set('text', 'CNPJ Válido');
                $elem->set('href', 'javascript:document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.fornecedorSelecionado.focus()');
                $mensagem[] = $elem->build() . ' deve ser informado';
                $retorno = false;
            } elseif (2 == $tipoFornecedor && ! valida_CPF($fornecedorAtual)) {
                $elem->set('text', 'CPF Válido');
                $elem->set('href', 'javascript:document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.fornecedorSelecionado.focus()');
                $mensagem[] = $elem->build() . ' deve ser informado';
                $retorno = false;
            }

            if ($fornecedorOriginal == $fornecedorAtual) {
                $elem->set('text', 'Fornecedor Atual não pode igual ao Fornecedor Original');
                $elem->set('href', 'javascript:document.CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.fornecedorSelecionado.focus()');
                $mensagem[] = $elem->build();
                $retorno = false;
            }
        }
	
	
        if (! $retorno) {
            $_SESSION['mensagemFeedback'] = $mensagem;
        }

        return $retorno;
    }

    private function existeSolicitacaoCompra()
    {
        return $this->getDados()->qtdSolicitacaoCompraAta((int)$_POST['seqNovaAta']) > 0;
    }

    private function inserirAtaNova($conexao, $tipoControle)
    {   

        $nomeTabela = 'sfpc.tbataregistropreconova';
        $entidadeAtaNova = ClaDatabasePostgresql::getEntidade($nomeTabela);
        $entidadeAtaNova->carpnosequ = $this->getDados()->getUltimoIdAtaNova();
        $entidadeAtaNova->carpnotiat = 'I';
        $entidadeAtaNova->tarpnoincl = 'NOW()';
        $entidadeAtaNova->cusupocodi = (int) $_SESSION['_cusupocodi_'];
        $entidadeAtaNova->tarpnoulat = 'NOW()';
        $entidadeAtaNova->farpnotsal = $tipoControle;

        $this->getDados()->factoryInsere($entidadeAtaNova, $nomeTabela, $conexao);
    }

    private function inserirAtaNovaInterna($conexao, $carpnosequ)
    {
        $nomeTabela = 'sfpc.tbataregistroprecointerna';
        $entidadeAtaInternaNova = ClaDatabasePostgresql::getEntidade($nomeTabela);
        $entidadeAtaInternaAntiga = $conexao->getRow('SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = ?', array($_REQUEST['ata']), DB_FETCHMODE_OBJECT);

        $fornecedorAtual = preg_replace("/[^0-9]/", "", $_REQUEST['fornecedorSelecionado']);
        $novoAno = $_POST['novoAnoAta'];
        $novoNumero = $_POST['novoNumAta'];

        $entidadeAtaInternaNova->carpnosequ = $carpnosequ;
        $entidadeAtaInternaNova->clicpoproc = (int) $entidadeAtaInternaAntiga->clicpoproc;
        $entidadeAtaInternaNova->alicpoanop = $entidadeAtaInternaAntiga->alicpoanop;
        $entidadeAtaInternaNova->cgrempcodi = (int) $entidadeAtaInternaAntiga->cgrempcodi;
        $entidadeAtaInternaNova->ccomlicodi = (int) $entidadeAtaInternaAntiga->ccomlicodi;
        $entidadeAtaInternaNova->corglicodi = (int) $entidadeAtaInternaAntiga->corglicodi;
        $entidadeAtaInternaNova->earpinobje = $entidadeAtaInternaAntiga->earpinobje;
        $entidadeAtaInternaNova->aarpinanon = $novoAno;
        $entidadeAtaInternaNova->carpincodn = $novoNumero;
        $fornecedor = current($this->getDados()->consultarFornecedorPorCpnjOrCpf($fornecedorAtual));
        $entidadeAtaInternaNova->aforcrsequ = (int) $fornecedor->aforcrsequ;
        $entidadeAtaInternaNova->tarpindini = 'NOW()';
        $entidadeAtaInternaNova->aarpinpzvg = $entidadeAtaInternaAntiga->aarpinpzvg;
        $entidadeAtaInternaNova->farpinsitu = 'I';
        $entidadeAtaInternaNova->carpnoseq1 = null;
        $entidadeAtaInternaNova->cusupocodi = (int) $_SESSION['_cusupocodi_'];
        $entidadeAtaInternaNova->tarpinulat = 'NOW()';

        $this->getDados()->factoryInsere($entidadeAtaInternaNova, $nomeTabela, $conexao);

        $this->atualizarAta($conexao, $entidadeAtaInternaAntiga, $carpnosequ);

        return $this->inserirParticipanteAta($conexao, $entidadeAtaInternaAntiga, $carpnosequ);
    }

    private function atualizarAta($conexao, $ataAntiga, $novoSeqAta){
        $nomeTabela = 'sfpc.tbataregistroprecointerna';

        $ataAntiga->farpinsitu = 'I';
        $ataAntiga->carpnoseq1 = $novoSeqAta;
        $ataAntiga->cusupocodi = (int) $_SESSION['_cusupocodi_'];
        $ataAntiga->tarpinulat = 'NOW()';
        $condicao = "carpnosequ  = " . (int)$_REQUEST['ata'];

        $this->getDados()->factoryInsere($ataAntiga, $nomeTabela, $conexao, $condicao);
    }

    private function inserirParticipanteAta($conexao, $entidadeAtaInternaAntiga, $carpnosequ)
    {
        $nomeTabela = 'sfpc.tbparticipanteatarp';
        $entidadeParticipanteAta = ClaDatabasePostgresql::getEntidade($nomeTabela);
        $entidadeParticipantesAntigos = $this->getDados()->consultarParticipantesAtaInterna((int) $entidadeAtaInternaAntiga->clicpoproc, $entidadeAtaInternaAntiga->alicpoanop, (int) $entidadeAtaInternaAntiga->corglicodi, $entidadeAtaInternaAntiga->carpnosequ);

        foreach ($entidadeParticipantesAntigos as $participanteAntigo) {
            $entidadeParticipanteAta->carpnosequ = $carpnosequ;
            $entidadeParticipanteAta->corglicodi = $participanteAntigo->corglicodi;
            $entidadeParticipanteAta->fpatrpsitu = $participanteAntigo->fpatrpsitu;
            $entidadeParticipanteAta->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeParticipanteAta->tpatrpulat = 'NOW()';
            
            $this->getDados()->factoryInsere($entidadeParticipanteAta, $nomeTabela, $conexao);
        }
    }

    /**
     *
     * @param unknown $conexao
     * @param unknown $carpnosequ
     */
    private function inserirDocumentoAta($conexao, $carpnosequ)
    {
        $documento = $conexao->getRow('SELECT MAX(cdocatsequ) FROM sfpc.tbdocumentoatarp WHERE carpnosequ = ?', array((int)$_REQUEST['ata']), DB_FETCHMODE_OBJECT);
        $valorMax = (int) $documento->max + 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);

        $nomeTabela = 'sfpc.tbdocumentoatarp';
        $entidadeNova = ClaDatabasePostgresql::getEntidade($nomeTabela);
        
        for ($i = 0; $i < $tamanho; $i ++) {
            $entidadeNova->carpnosequ = $carpnosequ;
            $entidadeNova->cdocatsequ = (int)$valorMax++;
            $entidadeNova->edocatnome = $_SESSION['Arquivos_Upload']['nome'][$i];
            $entidadeNova->idocatarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            $entidadeNova->tdocatcada = 'NOW()';
            $entidadeNova->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeNova->tdocatulat = 'NOW()';
            $this->getDados()->factoryInsere($entidadeNova, $nomeTabela, $conexao);
        }
    }

    /**
     * [inserirItemAta description]
     *
     * @param [type] $conexao
     *            [description]
     * @param [type] $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    private function inserirItemAta($conexao, $carpnosequ)
    {
        $nomeTabela = 'sfpc.tbitemataregistropreconova';
        $entidadeItemAtaNova = ClaDatabasePostgresql::getEntidade($nomeTabela);
        
        $entidadeItensAtaAntiga = $conexao->getAll('SELECT * FROM sfpc.tbitemataregistropreconova WHERE carpnosequ = ?', array($_REQUEST['ata']), DB_FETCHMODE_OBJECT);
        foreach ($entidadeItensAtaAntiga as $key) {
            $entidadeItemAtaNova->carpnosequ = $carpnosequ;
            $entidadeItemAtaNova->citarpsequ = $key->citarpsequ;
            $entidadeItemAtaNova->aitarporde = $key->aitarporde;
            $entidadeItemAtaNova->cmatepsequ = $key->cmatepsequ;
            $entidadeItemAtaNova->cservpsequ = $key->cservpsequ;
            $entidadeItemAtaNova->aitarpqtor = $key->aitarpqtor;
            $entidadeItemAtaNova->aitarpqtat = $key->aitarpqtat;
            $entidadeItemAtaNova->vitarpvori = $key->vitarpvori;
            $entidadeItemAtaNova->vitarpvatu = $key->vitarpvatu;
            $entidadeItemAtaNova->citarpnuml = $key->citarpnuml;
            $entidadeItemAtaNova->eitarpmarc = $key->eitarpmarc;
            $entidadeItemAtaNova->eitarpmode = $key->eitarpmode;
            $entidadeItemAtaNova->eitarpdescmat = $key->eitarpdescmat;
            $entidadeItemAtaNova->eitarpdescse = $key->eitarpdescse;
            $entidadeItemAtaNova->fitarpsitu = $key->fitarpsitu;
            $entidadeItemAtaNova->fitarpincl = $key->fitarpincl;
            $entidadeItemAtaNova->fitarpexcl = $key->fitarpexcl;
            $entidadeItemAtaNova->titarpincl = 'NOW()';
            $entidadeItemAtaNova->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeItemAtaNova->titarpulat = 'NOW()';
            $entidadeItemAtaNova->citarpitel = $key->citarpitel;

            $this->getDados()->factoryInsere($entidadeItemAtaNova, $nomeTabela, $conexao);
        }
    }

    private function inserirParticipanteItemAta($conexao, $carpnosequ) 
    {
        $nomeTabela = 'sfpc.tbparticipanteitematarp';
        $entidadeParticipanteItemAta = ClaDatabasePostgresql::getEntidade($nomeTabela);
        $entidadesItensParticipantesAta = $this->getDados()->consultarParticipantesItensPorSeqAta($_REQUEST['ata']);
        $entidadesItensParticipantesAta = $this->atualizarParticipantesItensPorSeqAta($entidadesItensParticipantesAta);
        
        foreach ($entidadesItensParticipantesAta as $itemParticipante){
            $entidadeParticipanteItemAta->carpnosequ = $carpnosequ;
            $entidadeParticipanteItemAta->corglicodi = $itemParticipante->corglicodi;
            $entidadeParticipanteItemAta->citarpsequ = $itemParticipante->citarpsequ;
            $entidadeParticipanteItemAta->apiarpqtat = $itemParticipante->apiarpqtat;
            $entidadeParticipanteItemAta->fpiarpsitu = $itemParticipante->fpiarpsitu;
            $entidadeParticipanteItemAta->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeParticipanteItemAta->tpiarpulat = 'NOW()';
            $entidadeParticipanteItemAta->apiarpqtut = 0;
            $entidadeParticipanteItemAta->vpiarpvatu = $itemParticipante->vpiarpvatu;
            $entidadeParticipanteItemAta->vpiarpvuti = 0;
            $this->getDados()->factoryInsere($entidadeParticipanteItemAta, $nomeTabela, $conexao);
        }
    }

    private function atualizarParticipantesItensPorSeqAta($itens) {
        if(!empty($itens)){
            foreach ($itens as $key => $value) {
                $quantidade = 0;
                $codigoItem = $value->cmatepsequ;
                $tipoItem = 'M';
                if(!is_null($value->cservpsequ)) {
                    $codigoItem = $value->cservpsequ;
                    $tipoItem = 'S';
                }

                $dados = $this->getDados()->consultarSCCDoProcesso($_REQUEST['ata'],$value->corglicodi,  $codigoItem, $tipoItem, $value->citarpsequ);
                
                if(!empty($dados)) {
                    foreach($dados as $key_ => $value_) {
                        $quantidade += $value_->aitescqtso;
                    }
                }

                $itens[$key]->apiarpqtat = $value->apiarpqtat - ($value->apiarpqtut + $quantidade);
            }
        }

        return $itens;
    }

    private function inserirCaronaExterna($conexao, $carpnosequ) 
    {
        $nomeTabela = 'sfpc.tbcaronaorgaoexterno';
        $entidadeCaronaAta = ClaDatabasePostgresql::getEntidade($nomeTabela);

        $caronasAta = $conexao->getAll('SELECT * FROM sfpc.tbcaronaorgaoexterno WHERE carpnosequ = ?', array((int)$_REQUEST['ata']), DB_FETCHMODE_OBJECT);
        
        foreach ($caronasAta as $carona){
            $entidadeCaronaAta->carpnosequ = $carpnosequ;
            $entidadeCaronaAta->ccaroesequ = $carona->ccaroesequ;
            $entidadeCaronaAta->ecaroeorgg = $carona->ecaroeorgg;
            $entidadeCaronaAta->tcaroeincl = $carona->tcaroeincl;
            $entidadeCaronaAta->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeCaronaAta->tcaroeulat = 'NOW()';
            
            $this->getDados()->factoryInsere($entidadeCaronaAta, $nomeTabela, $conexao);
        }
    }

    private function inserirItemCaronaExterna($conexao, $carpnosequ) 
    {
        $nomeTabela = 'sfpc.tbcaronaorgaoexternoitem';
        $entidadeItemCarona = ClaDatabasePostgresql::getEntidade($nomeTabela);

        $itensCarona = $conexao->getAll('SELECT * FROM sfpc.tbcaronaorgaoexternoitem WHERE carpnosequ = ?', array((int)$_REQUEST['ata']), DB_FETCHMODE_OBJECT);

        foreach ($itensCarona as $itemCarona){
            $entidadeItemCarona->carpnosequ = $carpnosequ;
            $entidadeItemCarona->ccaroesequ = $itemCarona->ccaroesequ;
            $entidadeItemCarona->citarpsequ = $itemCarona->citarpsequ;
            $entidadeItemCarona->acoeitqtat = $itemCarona->acoeitqtat;
            $entidadeItemCarona->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeItemCarona->tcoeitulat = 'NOW()';

            $this->getDados()->factoryInsere($entidadeItemCarona, $nomeTabela, $conexao);
        }
    }

    private function inserirCaronaInterna($conexao, $carpnosequ) 
    {
        $nomeTabela = 'sfpc.tbcaronainternaatarp';
        $entidadeCaronaInterna = ClaDatabasePostgresql::getEntidade($nomeTabela);

        $caronasAta = $conexao->getAll('SELECT * FROM sfpc.tbcaronainternaatarp WHERE carpnosequ = ?', array((int)$_REQUEST['ata']), DB_FETCHMODE_OBJECT);
        
        foreach ($caronasAta as $carona){
            $entidadeCaronaInterna->carpnosequ = $carpnosequ;
            $entidadeCaronaInterna->corglicodi = $carona->corglicodi;
            $entidadeCaronaInterna->fcarrpsitu = $carona->fcarrpsitu;
            $entidadeCaronaInterna->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeCaronaInterna->tcarrpulat = 'NOW()';

            $this->getDados()->factoryInsere($entidadeCaronaInterna, $nomeTabela, $conexao);
        }
    }

    private function inserirItemCaronaInterna($conexao, $carpnosequ) 
    {
        $nomeTabela = 'sfpc.tbitemcaronainternaatarp';
        $entidadeItemCarona = ClaDatabasePostgresql::getEntidade($nomeTabela);

        $itensCarona = $conexao->getAll('SELECT * FROM sfpc.tbitemcaronainternaatarp WHERE carpnosequ = ?', array((int)$_REQUEST['ata']), DB_FETCHMODE_OBJECT);
        
        foreach ($itensCarona as $itemCarona){
            $entidadeItemCarona->carpnosequ = $carpnosequ;
            $entidadeItemCarona->corglicodi = $itemCarona->corglicodi;
            $entidadeItemCarona->citarpsequ = $itemCarona->citarpsequ;
            $entidadeItemCarona->aitcrpqtat = $itemCarona->aitcrpqtat;
            $entidadeItemCarona->fitcrpsitu = $itemCarona->fitcrpsitu;
            $entidadeItemCarona->aitcrpqtut = $itemCarona->aitcrpqtut;
            $entidadeItemCarona->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidadeItemCarona->titcrpulat = 'NOW()';

            $this->getDados()->factoryInsere($entidadeItemCarona, $nomeTabela, $conexao);
        }
    }

    /**
     * [insereDocumento description]
     *
     * @return [type] [description]
     */
    public function insereDocumento()
    {
        $arquivoInformado = $_FILES['fileArquivo'];

        if (empty($arquivoInformado['name'])) {
            $elem = new Element('a');
            $elem->set('text', 'É preciso Informar um Arquivo');

            $_SESSION['mensagemFeedback'] = $elem->build();
            return false;
        } 
        $arquivo = new Arquivo();
        $arquivo->setExtensoes('pdf');
        $arquivo->setTamanhoMaximo(2000000);
        $arquivo->configurarArquivo();
        
        return true;
    }

    /**
     * [removeDocumento description]
     *
     * @return [type] [description]
     */
    public function removeDocumento()
    {
        $idDocumento = filter_input(INPUT_POST, 'documentoExcluir', FILTER_VALIDATE_INT);

        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);
    }

    /**
     */
    public function salvarTrocaFornecedor()
    {
        $conexao = Conexao();
        $conexao->autoCommit(false);
        $conexao->query("BEGIN TRANSACTION");	

        try {
            if ($_REQUEST['efetuarDesfazerTroca'] === 'Efetuar Troca'){
                $this->efetuarTroca($conexao);
            } else {
                $this->desfazerTroca($conexao);
            }

            $conexao->query("COMMIT");
            $conexao->query("END TRANSACTION");
        } catch (Exception $e) {
            $semerror = false;
            $conexao->query("ROLLBACK");            
            ExibeErroBD(self::$erroPrograma . "\nLinha: ".__LINE__."\nSql: " . $e->getMessage());
            
            return false;
        }

        $conexao->disconnect();

        unset($_SESSION['Arquivos_Upload']);
        return true;
    }

    private function efetuarTroca($conexao)
    {   
        $tipoControle = $_POST['tipoControle'];
        $this->inserirAtaNova($conexao, $tipoControle);
        
        $ataNovainserida = $conexao->getRow('
            SELECT MAX(carpnosequ) AS atual FROM sfpc.tbataregistropreconova'
        , array(), DB_FETCHMODE_OBJECT);

        $this->inserirAtaNovaInterna($conexao, $ataNovainserida->atual);
        $this->inserirItemAta($conexao, $ataNovainserida->atual);
        $this->inserirParticipanteItemAta($conexao, $ataNovainserida->atual);
        $this->inserirDocumentoAta($conexao, $ataNovainserida->atual);
        /*$this->inserirCaronaExterna($conexao, $ataNovainserida->atual);
        $this->inserirItemCaronaExterna($conexao, $ataNovainserida->atual);
        $this->inserirCaronaInterna($conexao, $ataNovainserida->atual);
        $this->inserirItemCaronaInterna($conexao, $ataNovainserida->atual);*/
    }

    private function desfazerTroca($conexao)
    {
        $ataAntiga = $conexao->getRow('SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = ?', array($_REQUEST['ata']), DB_FETCHMODE_OBJECT);

        $this->atualizarAta($conexao, $ataAntiga, null);

        $tabelas = array(
                "sfpc.tbparticipanteitematarp", 
                "sfpc.tbparticipanteatarp", 
                "sfpc.tbitemcaronainternaatarp", 
                "sfpc.tbcaronainternaatarp", 
                "sfpc.tbcaronaorgaoexternoitem",
                "sfpc.tbcaronaorgaoexterno",
                "sfpc.tbitemataregistropreconova",
                "sfpc.tbataregistroprecointerna",
                "sfpc.tbdocumentoatarp",
                "sfpc.tbataregistropreconova"
            );

        
        $this->removerRegistrosPorSeqAta((int)$_POST['seqNovaAta'], $conexao, $tabelas);
    }

    private function removerRegistrosPorSeqAta($seqAta, $conexao, $tabelas)
    {
        foreach ($tabelas as $tabela) {
            $conexao->query(sprintf("DELETE FROM " . $tabela . " WHERE carpnosequ = %d ;", (int)$_POST['seqNovaAta']));
        }
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
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
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar extends Adaptacao_Abstrata
{
    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar());
        return parent::getNegocio();
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
        return $this->getNegocio()->carregarTodosDocumentosAta(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
    }

    /**
     *
     * @param unknown $template
     */
    public function coletarDadosPostado(RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar $template)
    {
        $tipoFornecedor = filter_var($_POST['tipoFornecedor'], FILTER_SANITIZE_NUMBER_INT);

        $template->getTemplate()->clear('VALOR_CHECKED_CNPJ');
        $template->getTemplate()->clear('VALOR_CHECKED_CPF');

        if (1 == $tipoFornecedor) {
            $template->getTemplate()->VALOR_CHECKED_CNPJ = 'checked="checked';
        } else {
            $template->getTemplate()->VALOR_CHECKED_CPF = 'checked="checked';
        }

        $template->getTemplate()->VALOR_FORNECEDOR_ATUAL = filter_var($_POST['fornecedorSelecionado'], FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     *
     * @param unknown $dadosFornecedor
     * @param unknown $gui
     */
    public function montaDetalhesFornecedor($dadosFornecedor, RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar $gui)
    {
        $numCpnjCpf = $_SESSION['Fornecedor']['fornecedorSelecionado'] ;

        if (empty($dadosFornecedor) === true && isset($_SESSION['Fornecedor']) === true) {
            $dadosFornecedor[] = $_SESSION['Fornecedor']['dados'];
            $gui->getTemplate()->VALOR_FORNECEDOR_ATUAL = filter_var($cnpjOrCpf, FILTER_SANITIZE_NUMBER_INT);
        }

        if (empty($dadosFornecedor) === false) {
            $elem = new Element('p');
            if (null == $dadosFornecedor) {
                $elem->set('text', 'Fornecedor não cadastrado no SICREF');
                $gui->getTemplate()->VALORES_AUXILIARES_FORNECEDOR = $elem->build();
                return;
            }
        

            if (is_array($dadosFornecedor)){
                if (empty($dadosFornecedor[0])) {
                    $elem->set('text', "Fornecedor não cadastrado no SICREF");
                    $gui->getTemplate()->VALORES_AUXILIARES_FORNECEDOR = $elem->build();
                } else {
                    $gui->getTemplate()->VALOR_FORNECEDOR_ATUAL = filter_var($numCpnjCpf, FILTER_SANITIZE_NUMBER_INT);
                    
                    foreach ($dadosFornecedor as $key => $value) {
                        $stringHTML = $value->nforcrrazs . ' <br />' . $value->eforcrlogr . ', ' . $value->aforcrnume . ' - ' . $value->eforcrbair . ' - ' . $value->nforcrcida . '/' . $value->cforcresta;
                        $elem->set('text', $stringHTML);
                        $gui->getTemplate()->VALORES_AUXILIARES_FORNECEDOR = $elem->build();
                    }
                }
            } else {
                $stringHTML = $dadosFornecedor->nforcrrazs . ' <br />' . $dadosFornecedor->eforcrlogr . ', ' . $dadosFornecedor->aforcrnume . ' - ' . $dadosFornecedor->eforcrbair . ' - ' . $dadosFornecedor->nforcrcida . '/' . $dadosFornecedor->cforcresta;
                $numCpnjCpf = isset($dadosFornecedor->aforcrccpf) ? $dadosFornecedor->aforcrccpf : $dadosFornecedor->aforcrccgc;
                $elem->set('text', $stringHTML);
                $gui->getTemplate()->VALORES_AUXILIARES_FORNECEDOR = $elem->build();
                $gui->getTemplate()->VALOR_FORNECEDOR_ATUAL = filter_var($numCpnjCpf, FILTER_SANITIZE_NUMBER_INT);
            }

            $_SESSION['Fornecedor']['fornecedorSelecionado'] = $numCpnjCpf;
        }
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar extends UI_Abstrata
{

    /**
     *
     * @param unknown $val
     * @return number|string
     */
    private function maskNumber($val)
    {
        $result = 0;

        if ($val < 1000) {
            $result = $val . ",00";
        } elseif ($val > 1000 && $val < 10000) {
            $result = self::mask($val, '#.###') . ",00";
        } elseif ($val > 10000 && $val < 100000) {
            $result = self::mask($val, '##.###') . ",00";
        } elseif ($val > 100000 && $val < 1000000) {
            $result = self::mask($val, '###.###') . ",00";
        } elseif ($val > 1000000 && $val < 10000000) {
            $result = self::mask($val, '#.###.###') . ",00";
        }

        return $result;
    }

    /**
     * [mask description]
     *
     * @param [type] $val
     *            [description]
     * @param [type] $mask
     *            [description]
     * @return [type] [description]
     */
    private function mask($val, $mask)
    {
        $maskared = '';
        $iteratorTwo = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i ++) {
            if ($mask[$i] == '#') {
                if (isset($val[$iteratorTwo])) {
                    $maskared .= $val[$iteratorTwo ++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }

        return $maskared;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar());
        return parent::getAdaptacao();
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.html", "Registro de Preço > Ata Interna > Trocar Fornecedor");
        $this->setTemplate($template);
    }


    /**
     * Coletar Documentos adicionado via SESSION ou via Banco de Dados
     *
     * @return void [description]
     */
    private function coletarDocumentosAdicionados($ata)
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar());
        
        $qtdeDocumentos = sizeof($_SESSION['Arquivos_Upload']['nome']);

        if ($qtdeDocumentos > 0 && isset($_SESSION['Arquivos_Upload']['nome'])) {
            for ($i = 0; $i < $qtdeDocumentos; $i ++) {
                $this->getTemplate()->ID_DOCUMENTO = $i;
                $this->getTemplate()->NOME_DOCUMENTO = $_SESSION['Arquivos_Upload']['nome'][$i];
                $this->getTemplate()->block('BLOCO_DOCUMENTO');
            }
        }
    }

    /**
     *
     * @param unknown $ano
     * @param unknown $orgao
     * @param unknown $processo
     * @param unknown $ata
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
     * @param unknown $licitacao
     * @param unknown $ata
     * @param unknown $participantes
     */
    public function plotarBlocoLicitacao($licitacao, $ata, $participantes = null)
    {
        $licitacao = current($licitacao);
        
        $ata = current($ata);

        $this->getTemplate()->SEQ_NOVA_ATA = $ata->carpnoseq1;
        $this->getTemplate()->VALOR_ATA_INTERNA = $this->getNumeroAtaInterna($ata);
        $this->getTemplate()->VALOR_COMISSAO = $licitacao->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO = str_pad($licitacao->clicpoproc, 4, "0", STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO = $licitacao->alicpoanop != null ? $licitacao->alicpoanop : '&nbsp;';
        $this->getTemplate()->VALOR_MODALIDADE = $licitacao->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO = str_pad($licitacao->clicpocodl, 4, "0", STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO = $licitacao->alicpoanol != null ? $licitacao->alicpoanol : '&nbsp;';
        $this->getTemplate()->VALOR_ORG_LIMITE = $licitacao->eorglidesc;
        $this->getTemplate()->VALOR_OBJETO = (empty($ata->earpinobje)) ? $licitacao->xlicpoobje : $ata->earpinobje;
        $this->getTemplate()->VALOR_NOVA_ATA_INTERNA = $this->getNumeroAtaInterna($ata, true);
        $this->getTemplate()->VALOR_DOCUMENTO = $ata->xlicpoobje;
        $this->getTemplate()->VALOR_TIPO_CONTROLE = $ata->farpnotsal;
        $this->getTemplate()->VALOR_PARTICIPANTES = RegistroPreco_Dados_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar::
            consultarOrgaosParticipantesAta($licitacao->clicpoproc, $licitacao->alicpoanop, $licitacao->corglicodi, $ata->carpnosequ);

        if (! empty($ata->aforcrccpf)) {
            $numeroCnpjOrCpf = $ata->aforcrccpf;
            $mascaraCnpjOrCpf = '##.###.###-##';
        }
        if (! empty($ata->aforcrccgc)) {
            $numeroCnpjOrCpf = $ata->aforcrccgc;
            $mascaraCnpjOrCpf = '##.###.###/####-##';
        }

        $this->coletarDocumentosAdicionados($ata);

        $this->getTemplate()->VALOR_FORNECEDOR = $this->mask($numeroCnpjOrCpf, $mascaraCnpjOrCpf) . " - " . $ata->nforcrrazs . "<br />" . $ata->eforcrlogr;

        $this->getTemplate()->FORNECEDORORIGINAL = $numeroCnpjOrCpf;
        $this->getTemplate()->block("BLOCO_FILE");
        $this->getTemplate()->block("BLOCO_LICITACAO");
        $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
    }

    private function getNumeroAtaInterna($ata, $isNovoNum = false)
    {

        $dto = $this->getAdaptacao()->getNegocio()->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objeto = current($dto);
        $ataInterna = current($this->getAdaptacao()->getNegocio()->procurar((int)$ata->carpnosequ));
        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        
        if (!is_null($ata->carpnoseq1)){
            $novaAtaInterna = current($this->getAdaptacao()->getNegocio()->procurar((int)$ata->carpnoseq1));
            
            $this->getTemplate()->VALOR_NOVO_NUM_ATA = str_pad($novaAtaInterna->carpincodn, 4, "0", STR_PAD_LEFT);
            $this->getTemplate()->VALOR_NOVO_ANO_ATA = $novaAtaInterna->alicpoanop;
        } else {
            $this->getTemplate()->VALOR_NOVO_NUM_ATA = $_REQUEST['novoNumAta'];
            $this->getTemplate()->VALOR_NOVO_ANO_ATA = $_REQUEST['novoAnoAta'];
        }
        
        if (!$isNovoNum){
            $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT);
            $numeroAtaFormatado .= "/" . $ataInterna->alicpoanop;
        }

        return $numeroAtaFormatado;
    }

    /**
     *
     * @param unknown $itens
     */
    public function plotarBlocoResultadoAtas($itens)
    {
        if ($itens == null) {
            return;
        }

        foreach ($itens as $item) {
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
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $valorCodigo; 
            $this->getTemplate()->VALOR_DESCRICAO = $valorDescricao;
            $this->getTemplate()->VALOR_UND = $item->eunidmsigl;
            $this->getTemplate()->QTD_ORIGINAL = converte_valor_estoques($item->aitarpqtor);
            $this->getTemplate()->VALOR_QTD_ATUAL = converte_valor_estoques($item->vitarpvori);
            $this->getTemplate()->VALOR_TOTAL = converte_valor_licitacao($item->aitarpqtor * $item->vitarpvori);
            $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;
            $this->getTemplate()->QTD_ATUAL = converte_valor_licitacao($item->aitarpqtat);
            $this->getTemplate()->VALOR_UNIT_ATUAL = converte_valor_licitacao($item->vitarpvatu);
            $this->getTemplate()->VALOR_TOTAL_ATUAL = converte_valor_licitacao($item->aitarpqtat * $item->vitarpvatu);
            $this->getTemplate()->VALOR_SITUACAO = $item->fitarpsitu == 'A' ? strtoupper('Ativo') : strtoupper('Inativo');

            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }
    }
}

class CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar extends ProgramaAbstrato
{

    /**
     */
    private function consultarFornecedor()
    {
        $_SESSION['Fornecedor'] = null;

        $tipoFornecedor     = filter_var($_REQUEST['tipoFornecedor'], FILTER_SANITIZE_NUMBER_INT);
        $fornecedorAtual    = preg_replace("/[^0-9]/", "", filter_var($_REQUEST['fornecedorSelecionado'], FILTER_SANITIZE_STRING));

        $resultado = FornecedorService::verificarFornecedorCredenciado($tipoFornecedor, $fornecedorAtual);

        $_SESSION['Fornecedor']['fornecedorSelecionado'] = $fornecedorAtual;
        $_SESSION['Fornecedor']['dados'] = $resultado[0];
        $this->getUI()
        ->getAdaptacao()
        ->montaDetalhesFornecedor($resultado, $this->getUI());
    }
  
    /**
     * Alterar o Fornecedor da Ata
     *
     * @return [type] [description]
     */
     private function salvarTrocaFornecedor()
    {
	
        if (! $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->validacao()) {			

		if($_SESSION['mensagemFeedback'] == "A Troca de Fornecedor não pode ser desfeita, pois já houve uma solicitação de compra associada à nova ata"){
		    $this->getUI()->mensagemSistema($_SESSION['mensagemFeedback'], 0, 1);
		}else{
	            $this->getUI()->mensagemSistema(implode("", $_SESSION['mensagemFeedback']), 0, 1);
		}

            return;
        }

        if (! $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->salvarTrocaFornecedor()) {
            $this->getUI()->mensagemSistema(implode("", $_SESSION['mensagemFeedback']), 0, 1);
            return;
        }

        $_SESSION['Fornecedor'] = null;

        $msg = 'Desfazer Troca executada com sucesso';
        if ($_REQUEST['efetuarDesfazerTroca'] === 'Efetuar Troca'){
            $msg = 'Troca executada com sucesso';
        }

        $this->getUI()->setMensagemFeedBack($msg, 1, 0);
        header('Location: CadAtaRegistroPrecoInternaTrocarFornecedorAtas.php?ano='.$_REQUEST['ano'].'&processo='.$_REQUEST['processo'].'&orgao='.$_REQUEST['orgao']);
        exit();
    }

    /**
     * [insereDocumento description]
     *
     * @return [type] [description]
     */
    public function insereDocumento()
    {
        if (!$this->getUI()->getAdaptacao()->getNegocio()->insereDocumento()){
            $this->getUI()->mensagemSistema($_SESSION['mensagemFeedback'], 0, 1);
            return;
        }
    }

    /**
     * [removeDocumento description]
     *
     * @return [type] [description]
     */
    public function removeDocumento()
    {
        $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->removeDocumento($this->getUI());
    }

    /**
     */
    private function processVoltar()
    {
        $orgao = $_SESSION['orgao'];
        $ano = $_SESSION['ano'];
        $processo = $_SESSION['processo'];

        $url = 'CadAtaRegistroPrecoInternaTrocarFornecedorAtas.php?ano=' . $ano . '&processo=' . $processo . "&orgao=" . $orgao;
        header('Location: ' . $url);
    }
    
    /**
     */
    private function proccessPrincipal($montarFornecedor = true)
    {
        $orgao      = filter_var($_REQUEST['orgao'], FILTER_SANITIZE_NUMBER_INT);
        $ano        = filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT);
        $processo   = filter_var($_REQUEST['processo'], FILTER_SANITIZE_NUMBER_INT);
        $ata        = filter_var($_REQUEST['ata'], FILTER_SANITIZE_NUMBER_INT);

        if (strlen($ata) > 5) {
            $ata = (int) str_replace(0, '', substr($ata, 5, - 5));
        }

        $atas = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarAtaPorChave((int) $ano, (int) $processo, (int) $orgao, (int) $ata);
        $ataTmp = current($atas);

        if($montarFornecedor) {
            $numeroCnpjCpf = preg_replace("/[^0-9]/", "", filter_var($_POST['fornecedorSelecionado'], FILTER_SANITIZE_STRING));

            if (!is_null($ataTmp->carpnoseq1)){
                $numeroCnpjCpf = $ataTmp->carpnoseq1;
            }

            if (!empty($numeroCnpjCpf)){
                $fornecedor = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarFornecedorPorAta($numeroCnpjCpf);
                $this->montaDetalhesFornecedor(current($fornecedor));
            }
        }
        $this->getUI()->plotarBlocoBotao($ano, $orgao, $processo, $ata);

        $licitacao = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarLicitacaoAtaInterna($ano, $processo, $orgao);

        $this->getUI()->plotarBlocoLicitacao($licitacao, $atas, $participantes);

        $itens = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarItensAtaNova((int)$ata);

        $this->getUI()->plotarBlocoResultadoAtas($itens);
        
//madson
        $desabilitado = "disabled";
        if(empty($ataTmp->carpnoseq1)){
            $textoBotao = "Efetuar Troca";
            $desabilitado = '';
        } else {
            $textoBotao = "Desfazer Troca";
            //madson
        //    var_dump($atas[0]->carpnosequ);exit;
            $documentosAtas =  $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarTodosDocumentosAta($atas[0]->carpnosequ);
            $contNome = count($documentosAtas);
            for($i = 0; $i < $contNome; $i++) {
                
                 if($i==0){
                    $concatena = $documentosAtas[$i]->edocatnome."<br>";
                }else{
                    $concatena .= $documentosAtas[$i]->edocatnome."<br>";
                }
            }
            $this->getUI()->getTemplate()->VALOR_NOME_DOCUMENTO = $concatena;
        }
        $this->getUI()->getTemplate()->VALOR_BOTAO_TROCAR = $textoBotao;
        $this->getUI()->getTemplate()->DISABLED_CAMPO_ATA = $desabilitado;
    }

    private function montaDetalhesFornecedor($dadosFornecedor) {
        $resultado = $dadosFornecedor;
        
        if(!is_object($dadosFornecedor)){
            $resultado = current($this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarFornecedorPorCpnjOrCpf($dadosFornecedor));
        }

        $this->getUI()
            ->getAdaptacao()
            ->montaDetalhesFornecedor($resultado, $this->getUI());
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar());
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        $acao = filter_var($_REQUEST['Botao'], FILTER_SANITIZE_STRING);
        switch ($acao) {
            case 'procuraFornecedor':
                $this->consultarFornecedor();
                $this->proccessPrincipal(false);
                break;
            case 'Salvar':
                if (!$this->salvarTrocaFornecedor()){
                    $this->proccessPrincipal();    
                }
                break;
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Remover':
                $this->removeDocumento();
                $this->proccessPrincipal();
            break;
            case 'Inserir':
                $this->insereDocumento();
                $this->proccessPrincipal();
            break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar());