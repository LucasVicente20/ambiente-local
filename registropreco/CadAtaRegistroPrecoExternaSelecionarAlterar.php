<?php
/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * ----------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     20/08/2018
 * Objetivo: Tarefa Redmine 201672
 * ----------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     03/04/2019
 * Objetivo: Tarefa Redmine 214262
 * ----------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     17/04/2019
 * Objetivo: Tarefa Redmine 215117
 * ----------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/08/2019
 * Objetivo: Tarefa Redmine 222001
 * ----------------------------------------------------------------------------------------  
 * Alterado: Eliakim Ramos
 * Data:     22/11/2019
 * Objetivo: Tarefa Redmine 226849
 * ----------------------------------------------------------------------------------------  
 * Alterado: João Madson
 * Data:     06/07/2020
 * Objetivo: CR #226853
 * ----------------------------------------------------------------------------------------  
 * Alterado: João Madson
 * Data:     03/02/2021
 * Objetivo: CR #243518
 * ----------------------------------------------------------------------------------------  
 * Alterado: Marcello Albuquerque
 * Data:     08/05/2021
 * Objetivo: CR #248031
 * ----------------------------------------------------------------------------------------  
 */

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');

/**
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoExternaSelecionarAlterar extends Dados_Abstrata
{

    /**
     *
     * @param integer $numeroAta
     * @param string $situacao
     *            "A" ou "I"
     */
    public function sqlAlteraSituacao($numeroAta, $situacao)
    {
        $sql = 'UPDATE sfpc.tbataregistroprecoexterna';
        $sql .= " SET farpexsitu='%s', cusupocodi= %d, tarpexnulat='now()'";
        $sql .= ' WHERE carpnosequ=' . $numeroAta;

        $sql = sprintf($sql, $situacao, $_SESSION['_cusupocodi_']);

        return $sql;
    }
    
    

    /**
     *
     * @param unknown $documentoFornecedor
     */
    public function sqlSelectFornecedorPorDocumento($documentoFornecedor)
    {
        $sql = 'select f.aforcrccgc, f.aforcrsequ,  f.aforcrccpf, f.nforcrrazs, f.eforcrlogr';
        $sql .= " from sfpc.tbfornecedorcredenciado f where (f.aforcrccpf = '%s'";
        $sql .= " or f.aforcrccgc= '%s')";

        $sql = sprintf($sql, $documentoFornecedor, $documentoFornecedor);

        return $sql;
    }

    /**
     *
     * @param unknown $documento
     * @return string
     */
    public function sqlInsereDocumento($documento)
    {
        $codigoUsuario = $this->getCodigoUsuarioLogado();
        $sql = "INSERT INTO sfpc.tbdocumentoatarp";
        $sql .= " (carpnosequ, cdocatsequ, edocatnome, idocatarqu, tdocatcada, cusupocodi, tdocatulat)";
        $sql .= " VALUES($documento->carpnosequ, $documento->cdocatsequ, '$documento->edocatnome', $documento->idocatarqu, clock_timestamp(), $codigoUsuario, clock_timestamp())";

        return $sql;
    }

    public function sqlConsultarDocumento(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = "
                SELECT 
                  carpnosequ,
                  cdocatsequ,
                  edocatnome,
                  encode(idocatarqu, 'base64') as idocatarqu,                
                  tdocatcada,
                  cusupocodi,
                  tdocatulat 
                FROM sfpc.tbdocumentoatarp darp 
                WHERE darp.carpnosequ = %d";
        return sprintf($sql, $carpnosequ->getValor());
    }

    /**
     *
     * @param int $aarpexanon
     *            Ano da numeração da ata
     * @param int $carpnosequ
     *            Código sequencial da ata de registro de preço
     */
    public function consultarProcessoExterno($aarpexanon, $carpnosequ)
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoExterna();
        return $repositorio->consultarAtaRegistroPrecoAtiva(new Negocio_ValorObjeto_Carpnosequ($carpnosequ), new Negocio_ValorObjeto_Aarpexanon($aarpexanon));
    }

    /**
     */
    public function consultarModalidade()
    {
        $sql = Dados_Sql_ModalidadeLicitacao::getInstancia()->sqlSelecionaTodas();
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param int $codigo
     *            Código do Fornecedor
     */
    public function consultarFornecedor($codigo)
    {
        $sql = Dados_Sql_FornecedorCredenciado::getInstancia()->selecionarFornecedorPorCodigo($codigo);

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param intefer $carpnosequ
     */
    public function consultarItemAtaInterna($carpnosequ)
    {
        $sql = Dados_Sql_ItemAtaRegistroPrecoNova::sqlFind(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    public function itemUtilizadoEmSCC($carpnosequ, $citarpsequ)
    {
        $sql = "select count(*) 
        from sfpc.tbitemataregistropreconova iarpn
            inner join sfpc.tbitemsolicitacaocompra isc on iarpn.carpnosequ = isc.carpnosequ and iarpn.citarpsequ = isc.citarpsequ
        where iarpn.carpnosequ = %d and iarpn.citarpsequ = %d";

        $sql = sprintf($sql, $carpnosequ, $citarpsequ);

        $resultado =  ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        return $resultado[0]->count < 1;
    }

}

class RegistroPreco_Negocio_CadAtaRegistroPrecoExternaSelecionarAlterar extends Negocio_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoExternaSelecionarAlterar());

        return parent::getDados();
    }

    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem'].
     */
    public function collectorSessionItem()
    {
        if (isset($_SESSION['item'])) {
            $countItem = count($_SESSION['item']);
            for ($i = 0; $i < $countItem; ++ $i) {
                $newItem = $_SESSION['item'][$i];
                $_SESSION['ataExternaItem'][] = $newItem;
            }
        }
        // cleaning for news itens
        unset($_SESSION['item']);
    }

    public function validarItem($entidade) 
    {
        $mensagem = '';
        $valido = true;

        $br = '<br />';
        $adicionarBr = !empty($_SESSION['mensagemFeedback']);

        if (empty($entidade->aitarpqtor) || moeda2float($entidade->aitarpqtor, 4) == 0.0000){
            if ($adicionarBr || !$valido) $mensagem .= $br;
            $mensagem .= 'Campo Quantidade Original o item é de Preencimento Obrigatório';
            $valido = false;
        }

        if (empty($entidade->vitarpvori) || moeda2float($entidade->vitarpvori, 4) == 0.0000){
            if ($adicionarBr || !$valido) $mensagem .= $br;
            $mensagem .= 'Campo Valor Original o item é de Preencimento Obrigatório';
            $valido = false;
        }

        if (empty($entidade->aitarpqtat) || moeda2float($entidade->aitarpqtat, 4) == 0.0000){
            $entidade->aitarpqtat = "0.0000";
            // if ($adicionarBr || !$valido) $mensagem .= $br;
            // $mensagem .= 'Campo Quantidade Atual o item é de Preencimento Obrigatório';
            // $valido = false;
        }

        if (empty($entidade->vitarpvatu) || moeda2float($entidade->vitarpvatu, 4) == 0.0000){
            $entidade->aitarpqtat = "0.0000";
            // if ($adicionarBr || !$valido) $mensagem .= $br;
            // $mensagem .= 'Campo Valor Atual o item é de Preencimento Obrigatório';
            // $valido = false;
        }

        if (empty($entidade->eitarpmarc)){
            if ($adicionarBr || !$valido) $mensagem .= $br;
            $mensagem .= 'Campo Marca do item é de Preencimento Obrigatório.';
            $valido = false;
        }

        if (empty($entidade->eitarpmode)){
            if ($adicionarBr || !$valido) $mensagem .= $br;
            $mensagem .= 'Campo Modelo do item é de Preencimento Obrigatório.';
            $valido = false;
        }

        if (isset($entidade->eitarpdescmat) && empty($entidade->eitarpdescmat)){ 
            if ($adicionarBr || !$valido) $mensagem .= $br;
            $mensagem .= 'Campo Descrição Detalhada do item é de Preencimento Obrigatório.';
            $valido = false;
        }
        
        if (isset($entidade->eitarpdescse) && empty($entidade->eitarpdescse)){
            if ($adicionarBr || !$valido) $mensagem .= $br;
            $mensagem .= 'Campo Descrição Detalhada do item é de Preencimento Obrigatório.';
            $valido = false;
        }

        if (empty($entidade->citarpnuml)){
            if ($adicionarBr || !$valido) $mensagem .= $br;
            $mensagem .= 'Campo Lote do item é de Preencimento Obrigatório';
            $valido = false;
        }

        if (!$valido) {
            $_SESSION['mensagemFeedback'] .= $mensagem;
        }

        return $valido;
    }

    /**
     *
     * @param stdClass $entidade
     * @return boolean
     */
    public function validarInsercao($entidade)
    {
        unset($_SESSION['mensagemFeedback']);
        $mensagem = '';
        $valido = true;

        $br = '<br />';

        if (empty($entidade->carpexcodn)) {
            $mensagem .= 'Campo Número da Ata é de Preencimento Obrigatório';
            $valido = false;
        }
        if (empty($entidade->aarpexanon)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Ano da Ata é de Preencimento Obrigatório';
            $valido = false;
        }
        if (empty($entidade->earpexproc)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Processo da Ata é de Preencimento Obrigatório';
            $valido = false;
        }
        if (empty($entidade->cmodlicodi)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Modalidade é de Preencimento Obrigatório';
            $valido = false;
        }
        if (empty($entidade->earpexorgg)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Orgão da Ata é de Preencimento Obrigatório';
            $valido = false;
        }

        if (empty($entidade->earpexobje)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Objeto é de Preencimento Obrigatório';
            $valido = false;
        }
        if (empty($entidade->tarpexdini)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Data Inicial é de Preencimento Obrigatório';
            $valido = false;
        }
        if (empty($entidade->aarpexpzvg)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Vigência é de Preencimento Obrigatório';
            $valido = false;
        }
        if (empty($entidade->aforcrsequ)) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'Campo Fornecedor Original é de Preencimento Obrigatório';
            $valido = false;
        }

        if (count($_SESSION['Arquivos_Upload']['nome']) == 0) {
            if (!$valido) $mensagem .= $br;
            $mensagem .= 'É preciso Informar um Arquivo';
            $valido = false;
        }

        if (! $valido) {
            $_SESSION['mensagemFeedback'] = $mensagem;
        }

        return $valido;
    }

    public function validarDelecaoDosItens()
    {
        $valido = true;

        if (!empty($_SESSION['itens_deletados'])) {
            unset($_SESSION['mensagemFeedback']);

            $carpnosequ = $_SESSION['processoExterno'];

            foreach ($_SESSION['itens_deletados'] as $item) {
                $citarpsequ = explode("-", $item);

                $itemPodeSerDeletado = $this->getDados()->itemUtilizadoEmSCC($carpnosequ, $citarpsequ[1]);

                if (!$itemPodeSerDeletado) {
                    $valido = false;
                    
                    $_SESSION['mensagemFeedback'] =  'O Item de ordem ' . $citarpsequ[0] . ' não pode ser removido pois já tem uma solicitação de compra para o mesmo. Inative o item ou solicite que o mesmo seja removido do banco de dados, junto com a referência dele na SCC';

                    break;
                }
            }
        }

        return $valido;
    }

    /**
     * Coletar a lista de itens
     *
     * @return array $resultados lista com os itens adicionandos pelo o botão "Incluir Item"
     */
    public function coletarItensAdicionadoAtaExterna($ordemAtual)
    {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

        $resultados = array();
        $countItem = count($_SESSION['ataExternaItem']);
        
        if ($countItem > 0) {
            for ($i = 0; $i < $countItem; ++$i) {
                ++$ordemAtual;
                $dados = explode($SimboloConcatenacaoArray, $_SESSION['ataExternaItem'][$i]);

                $descricao = explode($SimboloConcatenacaoDesc, $dados[0]);
                $item = array();
                $valorEstimado = isset($_POST['ValorUnitarioEstimado'][$i]) ? $_POST['ValorUnitarioEstimado'][$i] : '0.0000';

                $item['aitarporde'] = $ordemAtual;
                $item['cmatepsequ'] = $dados[3] == 'S' ? null : $dados[1];
                $item['cservpsequ'] = $dados[3] == 'S' ? $dados[1] : null;
                $item['aitarpqtor'] = '';
                $item['vitarpvori'] = converte_valor_estoques($valorEstimado / 10000);
                $item['citarpnuml'] = '';
                $item['aitarpqtat'] = '';
                $item['vitarpvatu'] = '';
                $item['fitarpsitu'] = '';
                $item['eservpdesc'] = $dados[3] == 'S' ? $descricao[0] : null;
                $item['ematepdesc'] = $dados[3] == 'S' ? null : $descricao[0];
                $item['eunidmsigl'] = $dados[2];
                $item['citarpsequ'] = '';
                $item['fmatepgene'] = $dados[4];

                array_push($resultados, (object) $item);
            }

            unset($_SESSION['ataExternaItem']);
        }

        return $resultados;
    }

    public function alterarSituacaoAtaExterna($numeroAta, $situacao)
    {
        $db = ClaDatabasePostgresql::getConexao();
        $sql = $this->getDados()->sqlAlteraSituacao($numeroAta, $situacao);

        return $db->query($sql);
    }
    
    public function consultarFornecedorPorDocumento($documentoFornecedor)
    {
        $db = ClaDatabasePostgresql::getConexao();
        $sql = $this->getDados()->sqlSelectFornecedorPorDocumento($documentoFornecedor);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
        return $fornecedor;
    }

    /**
     *
     * @param DB $database
     * @param stdClass $entidade
     */
    public function alterarAtaExterna(&$database, $entidade)
    {
        $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
        $entidade->tarpexnulat = "NOW()";

        $campos = (array) $entidade;
        $campos['aarpexpzvg'] = (int) $entidade->aarpexpzvg;
        $campos['aforcrsequ'] = (int) $entidade->aforcrsequ;
        $campos['farpexsitu'] = $entidade->farpexsitu;
        $campos['aarpexanon'] = $entidade->aarpexanon;
        $campos['tarpexnulat'] = $entidade->tarpexnulat;

        unset($campos['carpnosequ']);

        $condicao = 'carpnosequ=' . $_SESSION['processoExterno'] . ' AND aarpexanon=' . $_SESSION['anoProcesso'];
        $result = $database->autoExecute('sfpc.tbataregistroprecoexterna', $campos, DB_AUTOQUERY_UPDATE, $condicao);

        ClaDatabasePostgresql::hasError($result);

        return $result;
    }

    public function deletarItens(&$database) { // abaco
        if (!empty($_SESSION['itens_deletados'])) {
            $carpnosequ = $_SESSION['processoExterno'];
            $o = 0;
            foreach ($_SESSION['itens_deletados'] as $item) {
                $o++;
                $citarpsequ = explode("-", $item);
                $database->query(" DELETE FROM SFPC.TBITEMATAREGISTROPRECONOVA WHERE CARPNOSEQU = " . $carpnosequ . " AND CITARPSEQU = " . $citarpsequ[1]);
            }
        }
    }

    /*** Apagar se funcionar
     * Função cria por Eliakim Ramos
     * Ela trata o valor do campo unitario 
     * evitando que o memso fique com varios zeros
     */

    public function TrataValorUnitario($valorUnitario){
            if(strlen($valorUnitario) >= 10){
                if(strstr($valorUnitario,'.') && strstr($valorUnitario,',')){
                        $aux            = str_replace ('.','',$valorUnitario);
                        $valorUnitario  = str_replace(',','.', $aux);
                        return $valorUnitario;
                        exit; 
                }
            }else{
                if(strstr($valorUnitario,',') ){
                    $valorUnitario  = str_replace(',','.', $valorUnitario);
                    return $valorUnitario;
                    exit;
                }
            }
          
    }

    /**
     *
     * @param unknown $database
     * @param unknown $valoresAta
     * @param unknown $numeroAta
     * @param unknown $numeroItem
     * @return unknown
     */
    public function alterarItemAtaExterna(&$database, $valoresAta, $numeroAta, $numeroItem, $flagInsert)
    {
        $entidade = new stdClass();
 
        $entidade->carpnosequ = (int) $numeroAta;
        $entidade->citarpsequ = (int) $numeroItem;
        $entidade->aitarporde = isset($valoresAta->aitarporde) ? (int) $valoresAta->aitarporde : null;
        $entidade->cmatepsequ = isset($valoresAta->cmatepsequ) ? (int) $valoresAta->cmatepsequ : null;
        $entidade->cservpsequ = isset($valoresAta->cservpsequ) ? (int) $valoresAta->cservpsequ : null;
        $entidade->aitarpqtor = isset($valoresAta->aitarpqtor) ? moeda2float($valoresAta->aitarpqtor, 4) : null;
        $entidade->aitarpqtat = isset($valoresAta->aitarpqtat) ? moeda2float($valoresAta->aitarpqtat, 4) : null;
        $entidade->vitarpvori = isset($valoresAta->vitarpvori) ? moeda2float($valoresAta->vitarpvori) : null;
        $entidade->vitarpvatu = isset($valoresAta->vitarpvatu) ? $this->TrataValorUnitario($valoresAta->vitarpvatu) : null;
        $entidade->citarpnuml = isset($valoresAta->citarpnuml) ? (int) $valoresAta->citarpnuml : null;
        $entidade->eitarpmarc = isset($valoresAta->eitarpmarc) ? strtoupper2($valoresAta->eitarpmarc) : null;
        $entidade->eitarpmode = isset($valoresAta->eitarpmode) ? strtoupper2($valoresAta->eitarpmode) : null;
        $entidade->eitarpdescmat = isset($valoresAta->eitarpdescmat) ? strtoupper2($valoresAta->eitarpdescmat) : null;
        $entidade->eitarpdescse = isset($valoresAta->eitarpdescse) ? strtoupper2($valoresAta->eitarpdescse) : null;
        $entidade->fitarpsitu = isset($valoresAta->fitarpsitu) ? $valoresAta->fitarpsitu : "A";
        $entidade->fitarpincl = isset($valoresAta->fitarpincl) ? $valoresAta->fitarpincl : "S";
        $entidade->fitarpexcl = isset($valoresAta->fitarpexcl) ? $valoresAta->fitarpexcl : 'S';
        $entidade->titarpincl = isset($valoresAta->titarpincl) ? $valoresAta->titarpincl : "NOW()";
        $entidade->cusupocodi = isset($valoresAta->cusupocodi) ? $valoresAta->cusupocodi : (int) $_SESSION['_cusupocodi_'];
        $entidade->titarpulat = isset($valoresAta->titarpulat) ? $valoresAta->titarpulat : "NOW()";
        if ($flagInsert){
            $res = $database->autoExecute('sfpc.tbitemataregistropreconova', (array) $entidade, DB_AUTOQUERY_INSERT);
        } else {
            $condicao  = 'carpnosequ = ' . $entidade->carpnosequ . ' ';
            $condicao .= 'AND citarpsequ = ' . $entidade->citarpsequ;
            $res       = $database->autoExecute('sfpc.tbitemataregistropreconova', (array)$entidade, DB_AUTOQUERY_UPDATE, $condicao);
        }

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    public function alterarTipoControle(&$database, $ata, $tipo = 0) {
        $entidade = new stdClass();
        $entidade->farpnotsal = $tipo;
        $condicao  = 'carpnosequ = ' . $ata;
        $res = $database->autoExecute('sfpc.tbataregistropreconova', (array)$entidade, DB_AUTOQUERY_UPDATE, $condicao);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    public function consultarDocumento($carpnosequ)
    {
        $sql = $this->getDados()->sqlConsultarDocumento(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return $res;
    }

    public function atualizarDocumentosAta(&$database, $idAta)
    {
        $this->inserirDocumentoAta($database, $idAta);
    }

    private function inserirDocumentoAta($conexao, $carpnosequ)
    {
        $conexao->query(sprintf("DELETE FROM sfpc.tbdocumentoatarp WHERE carpnosequ = %d", $carpnosequ));

        $documento = $conexao->getRow('SELECT MAX(cdocatsequ) FROM sfpc.tbdocumentoatarp WHERE carpnosequ = ?', array(
            (int) $carpnosequ
        ), DB_FETCHMODE_OBJECT);
        $valorMax = (int) $documento->max + 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);

        $nomeTabela = 'sfpc.tbdocumentoatarp';
        $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);
        for ($i = 0; $i < $tamanho; $i ++) {
            $entidade->carpnosequ = (int) $carpnosequ;
            $entidade->cdocatsequ = (int) $valorMax;
            $entidade->edocatnome = $_SESSION['Arquivos_Upload']['nome'][$i];
            $entidade->idocatarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            $entidade->tdocatcada = 'NOW()';
            $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidade->tdocatulat = 'NOW()';
            $conexao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);
            $valorMax ++;
        }
    }
}

class RegistroPreco_Adaptacao_CadAtaRegistroPrecoExternaSelecionarAlterar extends Adaptacao_Abstrata
{

    public function recuperarValorItens()
    {
        $itens = array();
        
        $arrayOriginal          = $_POST['orginalItem'];
        $arrayvalorOrginalItem  = $_POST['valororginalItem'];
        $arrayloteItem          = $_POST['loteItem'];
        $arrayQuantidade        = $_POST['quantidadeItem'];
        $arrayValorUnitario     = $_POST['valorUnitarioItem'];
        $arraySituacao          = $_POST['situacaoAta'];
        $arrayOrdem             = $_POST['ordem'];
        $arrayTipo              = $_POST['tipo'];
        $arrayFlagTipo          = $_POST['valorTipo'];
        $arraySeq               = $_POST['seq'];
        $arrayMarc              = $_POST['Marca'];
        $arrayMode              = $_POST['Modelo'];
        $arrayDesc              = $_POST['descricao']; 
        $arrayDescDet           = $_POST['descricaoDetalhada'];  
        $arrayGene              = $_POST['generico'];    
        $arrayUnid              = $_POST['unidade'];

        for ($i = 0; $i < sizeof($arrayOriginal); ++ $i) {
            $item = array();
            $item['aitarpqtor'] = $arrayOriginal[$i];
            $item['vitarpvori'] = $arrayvalorOrginalItem[$i];
            $item['citarpnuml'] = $arrayloteItem[$i];
            $item['aitarpqtat'] = $arrayQuantidade[$i];
            $item['vitarpvatu'] = !empty($arrayValorUnitario[$i])?$arrayValorUnitario[$i]:"0,0000";
            $item['fitarpsitu'] = $arraySituacao[$i];
            // $item['aitarpqtat'] = $arrayQuantidade[$i];
            // $item['vitarpvatu'] = !empty($arrayValorUnitario[$i])?$arrayValorUnitario[$i]:"0,0000";
            // $item['fitarpsitu'] = $arraySituacao[$i];
            $item['aitarporde'] = $arrayOrdem[$i];
            $item['cmatepsequ'] = $arrayFlagTipo[$i] == 'M' ? $arrayTipo[$i] : null;
            $item['cservpsequ'] = $arrayFlagTipo[$i] == 'S' ? $arrayTipo[$i] : null;
            $item['citarpsequ'] = $arraySeq[$i];
            $item['eitarpmarc'] = $arrayMarc[$i];
            $item['eitarpmode'] = $arrayMode[$i];
            $item['fmatepgene'] = $arrayGene[$i];

            if($arrayFlagTipo[$i] == 'M') { 
                $item['ematepdesc'] = $arrayDesc[$i]; //
                $item['eunidmsigl'] = $arrayUnid[$i];
                if($arrayGene[$i] == 'S') {
                    $item['eitarpdescmat'] = $arrayDescDet[$i];
                }
            } else {
                $item['eservpdesc'] = $arrayDesc[$i];
                $item['eitarpdescse'] = $arrayDescDet[$i];
            }
           
            array_push($itens, (object) $item);
        }

        return $itens;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoExternaSelecionarAlterar());

        return parent::getNegocio();
    }

    public function atualizarAtaRegistroPrecoExterna(&$database)
    {
        $entidade = ClaDatabasePostgresql::getEntidade('sfpc.tbataregistroprecoexterna');

        $entidade->carpexcodn = $_POST['NumeroAta'];
        $entidade->aarpexanon = (int) filter_var($_POST['anoAta'], FILTER_SANITIZE_NUMBER_INT);
        $entidade->earpexproc = strtoupper2($_POST['processoAta']);
        $entidade->cmodlicodi = (int) filter_var($_POST['modalidadeAta'], FILTER_SANITIZE_NUMBER_INT);
        $entidade->earpexorgg = strtoupper2($_POST['orgaoAta']);
        $entidade->earpexobje = strtoupper2($_POST['objetoAta']);
        $dataInicial = empty($_POST['dataInicialAta']) ? null : new DataHora($_POST['dataInicialAta']);
        $entidade->tarpexdini = $dataInicial == null ? null : $dataInicial->formata('Y-m-d h:i:s');
        $entidade->aarpexpzvg = $_POST['vigenciaAta'];
        $entidade->aforcrsequ = $_POST['codigoFornecedor'];
        $entidade->aforcrseq1 = isset($_POST['codigoFornecedorAtual']) && ($_POST['codigoFornecedorAtual'] > 0) ? $_POST['codigoFornecedorAtual'] : null;
        $entidade->farpexsitu = $_POST['situacaoAtaExterna'];

        $validado = $this->getNegocio()->validarInsercao($entidade);
        if (! $validado) {
            return false;
        }

        return $this->getNegocio()->alterarAtaExterna($database, $entidade);
    }
    

    public function atualizaValoresAtaTela()
    {
        $valoresAta = array();

        $valoresAta['carpexcodn'] = $_POST['NumeroAta'];
        $valoresAta['aarpexanon'] = $_POST['anoAta'];
        $valoresAta['earpexproc'] = $_POST['processoAta'];
        $valoresAta['cmodlicodi'] = $_POST['modalidadeAta'];
        $valoresAta['earpexorgg'] = $_POST['orgaoAta'];
        $valoresAta['earpexobje'] = $_POST['objetoAta'];
        $valoresAta['tarpexdini'] = $_POST['dataInicialAta'];
        $valoresAta['aarpexpzvg'] = $_POST['vigenciaAta'];
        $valoresAta['aforcrsequ'] = $_POST['codigoFornecedor'];
        $valoresAta['aforcrseq1'] = $_POST['codigoFornecedorAtual'];
        $valoresAta['farpexsitu'] = $_POST['SituacaoAta'];
        $valoresAta['carpnosequ'] = $_POST['carpnosequ'];

        return $valoresAta;
    }

    public function atualizaValoresItemTela()
    {
        $valoresAta = array();

        $valoresAta['qtdOriginal'] = $_POST['NumeroAta'];
        $valoresAta['qtdAtual'] = $_POST['anoAta'];
        $valoresAta['valorOriginal'] = $_POST['processoAta'];
        $valoresAta['valorAtual'] = $_POST['modalidadeAta'];
        $valoresAta['lote'] = $_POST['orgaoAta'];
        $valoresAta['situacao'] = $_POST['objetoAta'];
        $valoresAta['dataInicial'] = $_POST['dataInicialAta'];

        return $valoresAta;
    }


    public function atualizarItemAtaRegistroPrecoExterna(&$database)
    {
        $valoresAta = $this->recuperarValorItens();

        foreach ($valoresAta as $item) {
           
            $validado = $this->getNegocio()->validarItem($item);
            
            if (!$validado){
                return false;
            }
        }

        $ata = $_POST['carpnosequ'];
        $citarpsequ = 1;
        foreach ($valoresAta as $item) {
            $seqItem = $item->citarpsequ;
            
            if (empty($seqItem)) {
                $seqItem = $citarpsequ;
                $flagInsert = true;
            } else {
                $flagInsert = false;
            }
            
            $retornoBanco = $this->getNegocio()->alterarItemAtaExterna($database, $item, $ata, $seqItem, $flagInsert);
            if(!empty($retornoBanco->message)){
                ClaDatabasePostgresql::hasError($retornoBanco);
                return false;
            }
            $citarpsequ++;
        }
        return true;
    }

    public function atualizarTipoControle(&$database) {
        $ata  = $_POST['carpnosequ'];
        $tipo = $_POST['TipoControle'];

        return $this->getNegocio()->alterarTipoControle($database, $ata, $tipo);
    }

    /**
     *
     * @param unknown $carpnosequ
     * @return NULL[]
     */
    public function consultarFornecedoresAtaExterna($carpnosequ)
    {
        $fornecedores = array();
        $fornecedores['original'] = current(FornecedorService::getFornecedorOriginalAtaExterna($carpnosequ));
        $fornecedores['atual'] = current(FornecedorService::getFornecedorAtualAtaExterna($carpnosequ));

        return $fornecedores;
    }

    public function consultarDocumento($carpnosequ)
    {
        return $this->getNegocio()->consultarDocumento($carpnosequ);
    }
}

class RegistroPreco_UI_CadAtaRegistroPrecoExternaSelecionarAlterar extends UI_Abstrata
{
    const QUANTIDADE_ANOS = 3;

    /**
     *
     * @param unknown $fornecedorOrigin
     */
    private function plotarBlocoFornecedorOriginal($fornecedorOrigin, $flagPesquisarFornecedor = false)
    {
        if (empty($fornecedorOrigin) && $flagPesquisarFornecedor){
            $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ORIGINAL = 'Fornecedor não cadastrado no SICREF';
            $this->getTemplate()->CODIGO_FORNECEDOR_ORIGINAL = '';
        } else {
            $numeroCnpjOrCpfOriginal = '';
            $aforcrsequ = '';
    
            $fornecedorOrigDoc = isset($_POST['fornecedorOrigDoc']) ? filter_var($_POST['fornecedorOrigDoc']) : null;
            $fornecedorOriginalProcesso = isset($_POST['fornecedorOriginalProcesso']) ? filter_var($_POST['fornecedorOriginalProcesso']) : null;
            $aforcrsequ = $fornecedorOrigin->aforcrsequ;        
            $numeroCnpjOrCpfOriginal = $fornecedorOrigin->aforcrccgc != null ? $fornecedorOrigin->aforcrccgc : $fornecedorOrigin->aforcrccpf;
            
            if ($fornecedorOrigDoc && $fornecedorOriginalProcesso) {
                $fornecedorOrigDoc = ($fornecedorOrigDoc == 'CNPJ') ? 1 : 2;
                $fornecedorOrigin = current(FornecedorService::verificarFornecedorCredenciado($fornecedorOrigDoc, $fornecedorOriginalProcesso));
                $numeroCnpjOrCpfOriginal = preg_replace('/[^0-9]/', '', $fornecedorOriginalProcesso);
            }
    
            if (!empty($numeroCnpjOrCpfOriginal)) {
                $this->getTemplate()->FORNECEDOR_ORIGINAL_ATA_EXTERNA = $numeroCnpjOrCpfOriginal;
                $this->getTemplate()->CODIGO_FORNECEDOR_ORIGINAL = $aforcrsequ;
                $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ORIGINAL = FormataCpfCnpj($numeroCnpjOrCpfOriginal) . ' - ' . $fornecedorOrigin->nforcrrazs;
                $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_LOGRADOURO = $fornecedorOrigin->eforcrlogr;
            }
        }
    }

    /**
     *
     * @param unknown $fornecedorAtual
     */
    private function plotarBlocoFornecedorAtual($fornecedorAtual, $flagPesquisarFornecedor = false)
    {
        if (empty($fornecedorAtual) && $flagPesquisarFornecedor){
            $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ATUAL = 'Fornecedor não cadastrado no SICREF';
            $this->getTemplate()->CODIGO_FORNECEDOR_ATUAL = '';
        } else {
            $numeroCnpjOrCpfAtual = '';
            $aforcrsequ = '';

            $fornecedorAtualdoc = isset($_POST['fornecedorAtualdoc']) ? filter_var($_POST['fornecedorAtualdoc']) : null;
            $fornecedorAtualProcesso = isset($_POST['fornecedorAtualProcesso']) ? filter_var($_POST['fornecedorAtualProcesso']) : null;
            $aforcrsequ = $fornecedorAtual->aforcrsequ;
            $numeroCnpjOrCpfAtual = $fornecedorAtual->aforcrccgc != null ? $fornecedorAtual->aforcrccgc : $fornecedorAtual->aforcrccpf;

            if ($fornecedorAtualdoc && $fornecedorAtualProcesso) {
                $fornecedorAtualdoc = ($fornecedorAtualdoc == 'CNPJ') ? 1 : 2;
                $fornecedorAtual = current(FornecedorService::verificarFornecedorCredenciado($fornecedorAtualdoc, $fornecedorAtualProcesso));
                $numeroCnpjOrCpfAtual = preg_replace('/[^0-9]/', '', $fornecedorAtualProcesso);
            }

            if (!empty($numeroCnpjOrCpfAtual)) {
                $this->getTemplate()->FORNECEDOR_ATUAL_ATA_EXTERNA = $numeroCnpjOrCpfAtual;
                $this->getTemplate()->CODIGO_FORNECEDOR_ATUAL = $aforcrsequ;
                $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ATUAL = FormataCpfCnpj($numeroCnpjOrCpfAtual) . ' - ' . $fornecedorAtual->nforcrrazs;
                $this->getTemplate()->VALOR_DESCRITIVO_FORNECEDOR_ATUAL_LOGRADOURO = $fornecedorAtual->eforcrlogr;
            }
        }
    }

    /**
     *
     * @param array $anoProcesso
     */
     public function plotarBlocoAno($anoProcesso)
     {
         date_default_timezone_set('America/Recife');
         $anoAtual = (int) date('Y');
         $anos = array();
         for ($i = 0; $i < RegistroPreco_UI_CadAtaRegistroPrecoExternaSelecionarAlterar::QUANTIDADE_ANOS; ++ $i) {
             array_push($anos, strval($anoAtual - $i));
         }
 
         foreach ($anos as $text) {

             $this->getTemplate()->ANO_VALUE = $text;
             $this->getTemplate()->ANO_TEXT = $text;
 
             if ($anoProcesso == $text) {
                 $this->getTemplate()->ANO_SELECTED = 'selected';
             } else {
                $this->getTemplate()->clear('ANO_SELECTED');
            }
             $this->getTemplate()->block('BLOCO_ANO');
         }
     }

    /**
     */
    public function coletarDocumentosAdicionados()
    {
        if (isset($_SESSION['Arquivos_Upload']['nome'])) {
            $lista = '';
            $qtdeDocumentos = sizeof($_SESSION['Arquivos_Upload']['nome']);
            
            for ($i = 0; $i < $qtdeDocumentos; $i ++) {
                $nomeDocumento = $_SESSION['Arquivos_Upload']['nome'][$i];
                $lista .= '<li>' . $nomeDocumento . '<input type="button" name="remover[]" value="Remover" class="botao removerDocumento" doc="' . $i . '" /></li>';
            }

            $this->getTemplate()->VALOR_DOCUMENTOS_ATA = $lista;
        }
    }

    /**
     *
     * @param unknown $carpnosequ
     */
    private function plotarBlocoDocumentos($carpnosequ)
    {
        $this->getTemplate()->VALOR_DOCUMENTOS_ATA = '';

        if (empty($_SESSION['Arquivos_Upload'])) {
            $documentos = $this->getAdaptacao()->consultarDocumento($carpnosequ);

            if (!empty($documentos)) {
                foreach ($documentos as $documento) {
                    $documentoHexDecodificado = base64_decode($documento->idocatarqu);
                    $documentoToBin = $this->hextobin($documentoHexDecodificado);

                    $_SESSION['Arquivos_Upload']['nome'][] = $documento->edocatnome;
                    $_SESSION['Arquivos_Upload']['conteudo'][] = $documentoToBin;
                }
            }
        }

        $this->coletarDocumentosAdicionados();
        $this->getTemplate()->block('BLOCO_FILE');
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

    /**
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao('templates/CadAtaRegistroPrecoExternaSelecionarAlterar.html', 'Registro de Preço > Ata Externa > Manter');
        $template->NOMEPROGRAMA = 'CadAtaRegistroPrecoExternaSelecionarAlterar';
        $this->setTemplate($template);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoExternaSelecionarAlterar());

        return parent::getAdaptacao();
    }

    /**
     *
     * @param array $processo
     */
    public function plotarBlocoProcesso($processo, $fornecedor = null, $tipoFornecedor = '')
    {

        if (! is_array($processo) && ! $processo instanceof stdClass) {
            return;
        }

        if (is_array($processo)) {
            $processo = current($processo);
        }

        $numeroAta = $processo->carpexcodn;
        if (isset($_POST['NumeroAta'])) {
            $numeroAta = $_POST['NumeroAta'];
        }

        $anoAta =  $processo->aarpexanon;
        if (isset($_POST['anoAta'])) {
            $anoAta =  $_POST['anoAta'];
        }

        $processoAta =  $processo->earpexproc;
        if (isset($_POST['processoAta'])) {
            $processoAta = $_POST['processoAta'];
        }

        $modalidadeAta = $processo->cmodlicodi;
        if (isset($_POST['modalidadeAta'])) {
            $modalidadeAta = $_POST['modalidadeAta'];
        }

        $orgaoAta = $processo->earpexorgg;
        if (isset($_POST['orgaoAta'])) {
            $orgaoAta = $_POST['orgaoAta'];
        }

        $vigenciaAta = $processo->aarpexpzvg;;
        if (isset($_POST['vigenciaAta'])) {
            $vigenciaAta = $_POST['vigenciaAta'];
        }

        $objetoAta = $processo->earpexobje;
        if (isset($_POST['objetoAta'])) {
            $objetoAta = $_POST['objetoAta'];
        }

        $situacaoAta = $processo->farpexsitu;
        if (isset($_POST['situacaoAtaExterna'])) {
            $situacaoAta = $_POST['situacaoAtaExterna'];
        }

        if (isset($_POST['dataInicialAta'])) {
            $dataInicial = $_POST['dataInicialAta'];
        } else {
            $timeDataInicial = strtotime($processo->tarpexdini);
            $dataInicial = date('d/m/Y', $timeDataInicial);
        }

        $this->getTemplate()->DATA_ATA_EXTERNA = $dataInicial;

        $this->getTemplate()->NUMERO_ATA_EXTERNA = str_pad($numeroAta, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->ANO_ATA_EXTERNA = $anoAta;
        $this->plotarBlocoAno($anoAta);
        $this->getTemplate()->PROCESSO_ATA_EXTERNA = strtoupper2($processoAta);
        $this->getTemplate()->SEQ_PROCESSO = $processo->carpnosequ;
        $this->getTemplate()->SITUACAO_ATA_EXTERNA = $situacaoAta;

        $modalidades = $this->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarModalidade();
        $this->plotarBlocoModalidade($modalidades, $modalidadeAta);

        $this->getTemplate()->ORGAO_ATA_EXTERNA = strtoupper2($orgaoAta);
        $this->getTemplate()->OBJETO_ATA_EXTERNA = strtoupper2($objetoAta);

        if (isset($processo->carpnosequ)) {
            $this->getTemplate()->VALOR_CARPNOSEQU = $processo->carpnosequ;
            $this->plotarBlocoDocumentos($processo->carpnosequ);
            
            if ($processo->aforcrsequ) {
                $fornecedores['original'] = current(FornecedorService::getFornecedorPorId($processo->aforcrsequ));
            }
            
            if ($processo->aforcrseq1) {
                $fornecedores['atual'] = current(FornecedorService::getFornecedorPorId($processo->aforcrseq1));
            }


            if ($tipoFornecedor == 'original') {
                $flagPesquisarFornecedorOriginal = true;
                $flagPesquisarFornecedorAtual = false;
            } else if ($tipoFornecedor == 'atual'){
                $flagPesquisarFornecedorOriginal = false;
                $flagPesquisarFornecedorAtual = true;
            } else {
                $flagPesquisarFornecedorOriginal = false;
                $flagPesquisarFornecedorAtual = false;
            }

            $this->plotarBlocoFornecedorOriginal($fornecedores['original'], $flagPesquisarFornecedorOriginal);
            $this->plotarBlocoFornecedorAtual($fornecedores['atual'], $flagPesquisarFornecedorAtual);
        }

        $this->getTemplate()->VIGENCIA_ATA_EXTERNA = $vigenciaAta;
        
        $this->plotarBlocoSituacaoAta($situacaoAta);
    }
        /***
         * Função cria por Eliakim Ramos
         * Ela Cria umna mascara para o valor do campo unitario 
         * evitando que o memso fique com varios zeros
         */

        public function MascaraValorUnitario($valorUnitario){
            $maskared  = '';
            $k         = 0;
            $arraytira = array('.',',');
            $valorSemPontuacao = str_replace($arraytira,'',$valorUnitario);
            strlen($valorSemPontuacao);
            switch(strlen($valorSemPontuacao)){
                case "5":
                    $mascara = "#,####";
                break;
                case "6":
                    $mascara = "##,####";
                break;
                case "7":
                    $mascara = "###,####";
                break;
                case "8":
                    $mascara = "#.###,####";
                break;
                case "9":
                    $mascara = "##.###,####";
                break;
                case "10":
                    $mascara = "###.###,####";
                break;
                case "11":
                    $mascara = "#.###.###,####";
                break;
                case "12":
                    $mascara = "##.###.###,####";
                break;
                case "13":
                    $mascara = "###.###.###,####";
                break;
                case "14":
                    $mascara = "#.###.###.###,####";
                break;
                case "15":
                    $mascara = "##.###.###.###,####";
                break;
                case "16":
                    $mascara = "###.###.###.###,####";
                break;
                case "17":
                    $mascara = "#.###.###.###.###,####";
                break;
            }
            
            for($i =0; $i <= strlen($mascara)-1; $i++){
                if($mascara[$i] == "#"){
                    if(isset($valorSemPontuacao[$k])){
                       $maskared .= $valorSemPontuacao[$k++];
                    }
                }else{
                    $maskared .= $mascara[$i];
                }
            }
            return $maskared;
        }

    public function plotarBlocoUnicoItem($item)
    {
        $descricaoDetalhada = ' - <textarea style="display:none" required name="descricaoDetalhada[]"></textarea>';
        // CADUM = material e CADUS = serviço
        $tipo = 'CADUM';
        if (is_null($item->cmatepsequ) == true) {
            $descricaoDetalhada = '<textarea required name="descricaoDetalhada[]">'.$item->eitarpdescse.'</textarea>';
            $tipo = 'CADUS';
        } else {
            if($item->fmatepgene == 'S') {
                $descricaoDetalhada = '<textarea required name="descricaoDetalhada[]">'.$item->eitarpdescmat.'</textarea>';
            }
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
        $item->vitarpvatu = !empty($item->vitarpvatu)?$item->vitarpvatu:"0,0000";
        $this->getTemplate()->ITESEQ                = $item->citarpsequ;
        $this->getTemplate()->ORD_ITEM              = $item->aitarporde;       
        $this->getTemplate()->DESC_TIPO             = $tipo;
        $this->getTemplate()->VALOR_TIPO            = $item->cmatepsequ == null ? 'S' : 'M';
        $this->getTemplate()->CADUS_ITEM            = $valorCodigo;  // Código Sequencial do Material OU Código sequencial do serviço
        $this->getTemplate()->DESCRICAO_ITEM        = $valorDescricao;
        $this->getTemplate()->UND_ITEM              = $item->cmatepsequ != null ? $item->eunidmsigl : '';
        $this->getTemplate()->ORIGINAL_ITEM         = converte_valor_licitacao($item->aitarpqtor);
        $this->getTemplate()->VALOR_ORGINAL_ITEM    = converte_valor_licitacao($item->vitarpvori);
        $this->getTemplate()->TOTAL_ITEM            = converte_valor_licitacao($item->aitarpqtor * $item->vitarpvori);
        $this->getTemplate()->LOTE_ITEM             = $item->citarpnuml;
        $this->getTemplate()->DESCRICAO_DETALHADA   = $descricaoDetalhada;
        $this->getTemplate()->VALOR_MARCA           = $item->eitarpmarc;
        $this->getTemplate()->VALOR_MODELO          = $item->eitarpmode;
        $this->getTemplate()->VALOR_GENERICO        = $item->fmatepgene;
        $this->getTemplate()->QTD_ATUAL_ITEM        = converte_valor_licitacao($item->aitarpqtat);
        $this->getTemplate()->VALOR_UNITARIO_ITEM   = $this->MascaraValorUnitario($item->vitarpvatu);
        $this->getTemplate()->VALOR_TOTAL_ITEM      = converte_valor_licitacao($item->aitarpqtat * $item->vitarpvatu);
        
        $this->plotarBlocoSituacao($item->fitarpsitu);
    }

    
    public function plotarBlocoSituacaoAta($valorAtual)
    {
        $situacoesAta = array();
        $situacoesAta['A'] = 'ATIVO';
        $situacoesAta['I'] = 'INATIVO';

        foreach ($situacoesAta as $VALUE => $TEXT) {
            $this->getTemplate()->SITUACAO_ATA_EXTERNA = $VALUE;
            $this->getTemplate()->SITUACAO_ATA_TEXT = $TEXT;

            if ($valorAtual == $VALUE) {

                //var_dump($_SESSION['_fperficorp_']);
                //var_dump($_SESSION['_cperficodi_']);
                $perfil = $_SESSION['_cperficodi_'];
                //var_dump("teste1");
               
                if($perfil != "2" || !$perfil == "6"){
                 //var_dump("teste");

                    $this->getTemplate()->HIDDEN = 'hidden';
        
                        if($valorAtual == 'A'){
                        $this->getTemplate()->TEXTO = 'ATIVO';
                        }else{
                        $this->getTemplate()->TEXTO = 'INATIVO';
                        $this->getTemplate()->DISABLED = 'disabled';
                        }                
                  }
                  

                $this->getTemplate()->SITUACAO_ATA_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('SITUACAO_ATA_SELECTED');
            }

            
            $this->getTemplate()->block('BLOCO_SITUACAO_ATA_EXTERNA');
        }
    }

    /**
     *
     * @param array $modalidades
     * @param unknown $valorAtual
     */
    public function plotarBlocoModalidade(array $modalidades, $valorAtual)
    {
        foreach ($modalidades as $modalidade) {
            $this->getTemplate()->MODALIDADE_VALUE = $modalidade->cmodlicodi;
            $this->getTemplate()->MODALIDADE_TEXT = $modalidade->emodlidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($valorAtual == $modalidade->cmodlicodi) {
                $this->getTemplate()->MODALIDADE_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('MODALIDADE_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_MODALIDADE');
        }
    }

    public function plotarBlocoItem(array $itens)
    {
        $valorTotal = 0;
        $ord = 1;
        
        foreach ($itens as $item) {
            $item->aitarporde = $ord;
            $this->plotarBlocoUnicoItem($item);
            $valorTotal += $item->aitarpqtat * $item->vitarpvatu;
            $this->getTemplate()->block('BLOCO_ITEM');
            $ord ++;
        }
        $this->getTemplate()->TOTAL_ATA = converte_valor_estoques($valorTotal);
    }

    public function plotarBlocoSituacao($valorAtual)
    {
        $situacoes = array();
        $situacoes['A'] = 'ATIVO';
        $situacoes['I'] = 'INATIVO';
        foreach ($situacoes as $VALUE => $TEXT) {
            $this->getTemplate()->SITUACAO_VALUE = $VALUE;
            $this->getTemplate()->SITUACAO_TEXT = $TEXT;

            // Vendo se a opção atual deve ter o atributo "selected"

            if ($valorAtual == $VALUE) {
                $this->getTemplate()->SITUACAO_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('SITUACAO_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_SITUACAO');
        }
    }
}

/**
 * CadRegistroPrecoIntencaoIncluir.
 *
 * Class application
 */
class CadAtaRegistroPrecoExternaSelecionarAlterar extends ProgramaAbstrato
{

    /**
     */
    private function proccessPrincipal()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getUI()->garbageCollection();
        }

        if (! isset($_SESSION['anoProcesso']) && ! filter_var($_REQUEST['ano'], FILTER_VALIDATE_INT)) {
            $this->getUI()->mensagemSistema('Ano deve ser selecionado', 0);
            $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
            header('Location: ' . $uri);
            exit();
        }

        if (! isset($_SESSION['processoExterno']) && ! filter_var($_REQUEST['processo'], FILTER_VALIDATE_INT)) {     
            $this->getUI()->mensagemSistema('Processo deve ser selecionado', 0);
            $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
            header('Location: ' . $uri);
            exit();
        }

        $carpnosequ = isset($_REQUEST['processo']) ? (int)filter_var($_REQUEST['processo'], FILTER_SANITIZE_NUMBER_INT) : (int)$_SESSION['processoExterno'];
        $aarpexanon = !empty($_REQUEST['ano']) ? (int)filter_var($_REQUEST['ano'], FILTER_SANITIZE_NUMBER_INT) : (int)$_SESSION['anoProcesso'];

        $_SESSION['anoProcesso'] = $aarpexanon;
        $_SESSION['processoExterno'] = $carpnosequ;

        $processos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarProcessoExterno($aarpexanon, $carpnosequ);


        $this->getUI()->plotarBlocoProcesso($processos);

        $entidade = current($processos);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $itensAta = $this->getUI()->getAdaptacao()->recuperarValorItens();
        } else {
            $itensAta = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarItemAtaInterna($entidade->carpnosequ);
        }

        // Adicionar item na sessão da lista caso seja do incluir
        $this->getUI()->getAdaptacao()->getNegocio()->collectorSessionItem('ataExternaItem');
            
        $tamanho = sizeof($itensAta);

        $itensAdicionadoAtaExterna = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
                ->coletarItensAdicionadoAtaExterna($tamanho);

        if (count($itensAdicionadoAtaExterna) > 0) {
            $itensAta = array_merge($itensAta, $itensAdicionadoAtaExterna);
        }

        $this->getUI()->getTemplate()->ATA = $entidade->carpnosequ;        
        $this->buildSelectTipoControle($entidade->farpnotsal);

        if (isset($_SESSION['itens_deletados'])) {
            $itensValidosParaDelecao = $this->getUI()->getAdaptacao()->getNegocio()->validarDelecaoDosItens(); // abaco

            if (!$itensValidosParaDelecao) {
                unset($_SESSION['itens_deletados']);
                $Mens = 0;
                $Tipo = 1;
                $Mensagem = $_SESSION['mensagemFeedback'];
            } else {
                $db = Conexao();

                $this->getUI()->getAdaptacao()->getNegocio()->deletarItens($db);

                $itensParaDeletar = array_unique($_SESSION['itens_deletados']);

                foreach ($itensParaDeletar as $valor) {
                    $posicaoItem = (int) $valor - 1;

                    unset($itensAta[$posicaoItem]);
                }

                unset($_SESSION['itens_deletados']);
            }
        }

        $this->getUI()->plotarBlocoItem($itensAta);
    }

    /**
     * Select tipo de controle
     * 
     * @param $tipo null
     * @return void
     */
    private function buildSelectTipoControle($tipo = null) {
        $controle = selectTipoControle();
        $tipo = !empty($_REQUEST['TipoControle']) ? (int)filter_var($_REQUEST['TipoControle'], FILTER_SANITIZE_NUMBER_INT) : $tipo;

        foreach ($controle as $key => $value) {
            $this->getUI()->getTemplate()->VALOR_CONTROLE = $key;
            $this->getUI()->getTemplate()->DESCRICAO_CONTROLE = $value;
            
            $this->getUI()->getTemplate()->clear("VALOR_CONTROLE_SELECIONADO");
            if ($tipo == $key) {
                $this->getUI()->getTemplate()->VALOR_CONTROLE_SELECIONADO = "selected";
            }
            $this->getUI()->getTemplate()->block("BLOCO_TIPOCONTROLE");
        }
    }

    /**
     */
    private function processVoltar()
    {
        unset($_SESSION['itens_deletados']);
        $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
        header('Location: ' . $uri);
        exit();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoExternaSelecionarAlterar());
    }

    /**
     * Front Controller.
     */
    protected function frontController()
    {
   
        $botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';
        
        switch ($botao) {

            case 'Salvar':
                
                if (!$this->salvarDados()){           
                    $this->getUI()->getTemplate()->block("BLOCO_TIPOCONTROLE");        
                    $this->getUI()->mensagemSistema($_SESSION['mensagemFeedback'], 0, 1);
                    $this->proccessPrincipal();    
                }
                break;
            case 'InserirDocumento':
                $this->inserirDocumento();
                $this->proccessPrincipal();
                break;
            case 'RemoverDocumento':
                $this->removerDocumento();
                break;
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Imprimir':
                $this->processImprimir();
                break;
            case 'Ativar':
                $this->ativarAtaExterna();
                break;
            case 'Inativar':
                $this->inativarAtaExterna();
                break;
            case 'original':
                $this->pesquisarFornecedor('original');
                 break;
            case 'atual':
                $this->pesquisarFornecedor('atual');
                break;
            case 'RemoverItem':
                $this->removerItem();
                if (!$this->getUI()->getAdaptacao()->getNegocio()->validarDelecaoDosItens()) {
                    $this->getUI()->mensagemSistema($_SESSION['mensagemFeedback'], 0, 1);
                }
                $this->proccessPrincipal();
                break;  
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
            case 'ExcluirAta': //madson
                $resultadoFunc = $this->checaSeTipoSARP();
                    if($resultadoFunc == false){
                        $this->excluirAtaNaoSARP();
                    }else{
                        $this->getUI()->mensagemSistema('Ata Externa não pode ser excluída, pois possui SCC SARP relacionada', 0);
                        $this->proccessPrincipal();
                    }
                
                break;
        }
    }

    /**
     * Carregar Ano.
     */
    private function carregarAno()
    {
        $anoAtual = (int) date('Y');
        $anos = array();
        for ($i = 0; $i < 3; ++ $i) {
            array_push($anos, strval($anoAtual - $i));
        }

        return $anos;
    }

    private function reiniciarTela()
    {
        $itens = $this->getUI()
            ->getAdaptacao()
            ->atualizaValoresItemTela();
        $ata = $this->getUI()
            ->getAdaptacao()
            ->atualizaValoresAtaTela();
        $ata = $this->arrayParaObject($ata);

        $this->getUI()->plotarBlocoProcesso($ata);

        $itens = $this->getUI()
            ->getAdaptacao()
            ->recuperarValorItens();
        $this->getUI()->getTemplate()->ATA = $_REQUEST['ATA'];
        $this->getUI()->plotarBlocoItem($itens);
    }

    private function arrayParaObject($d)
    {
        return (object) $d;
    }

    private function processImprimir()
    {
        $pdf = new PdfAtaRegistroPrecoExternaDetalhamento();
        $pdf->setAno($_SESSION['anoProcesso']);
        $pdf->setProcesso($_SESSION['processoExterno']);
        $pdf->gerarRelatorio();
    }

    private function salvarDados()
    {
        $db = Conexao();
        $db->autoCommit(false);
        $db->query("BEGIN TRANSACTION");

        try {
            $itensValidosParaDelecao = $this->getUI()->getAdaptacao()->getNegocio()->validarDelecaoDosItens();

            if (!$itensValidosParaDelecao) {
                unset($_SESSION['itens_deletados']);
                throw new DomainException($_SESSION['mensagemFeedback']);
            } else {
                $this->getUI()->getAdaptacao()->getNegocio()->deletarItens($db);
            }
            $ataAlterada = $this->getUI()
                ->getAdaptacao()
                ->atualizarAtaRegistroPrecoExterna($db);
            
            $atualiarTipoControle = $this->getUI()
                ->getAdaptacao()
                ->atualizarTipoControle($db);

            $itensAlterados = $this->getUI()
            ->getAdaptacao()
            ->atualizarItemAtaRegistroPrecoExterna($db);

            if (! $ataAlterada || ! $itensAlterados || !$atualiarTipoControle) {
                
                throw new DomainException($_SESSION['mensagemFeedback']);
            }

            $this->getUI()
                ->getAdaptacao()
                ->getNegocio()
                ->atualizarDocumentosAta($db, $_SESSION['processoExterno']);

            $db->query("COMMIT");
            $db->query("END TRANSACTION");
            unset($_SESSION['Arquivos_Upload']);
            unset($_SESSION['itens_deletados']);
            $this->getUI()->setMensagemFeedBack('Alterado com sucesso', 1);
            $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
            header('Location: ' . $uri);
            exit();
        } catch (Exception $e) {            
            $db->query("ROLLBACK");  
            return false;
        }

        $db->disconnect();
    }

    private function ativarAtaExterna()
    {
        $numeroAta = $_REQUEST['ATA'];
        $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->alterarSituacaoAtaExterna($numeroAta, 'A');
        $this->reiniciarTela();

        $this->getUI()->getTemplate()->SITUACAO_ATA_EXTERNA = 'A';

        $this->getUI()->setMensagemFeedBack('Ativado com sucesso', 1);
        $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
        header('Location: ' . $uri);
        exit();
    }

    private function inativarAtaExterna()
    {
        $numeroAta = $_REQUEST['ATA'];
        $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->alterarSituacaoAtaExterna($numeroAta, 'I');
        $this->reiniciarTela();

        $this->getUI()->getTemplate()->SITUACAO_ATA_EXTERNA = 'I';

        $this->getUI()->setMensagemFeedBack('Inativado com sucesso', 1);
        $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
        header('Location: ' . $uri);
        exit();
    }

    private function consultarModalidade()
    {
        $resultados = array();

        $db = Conexao();
        $sql = $this->sqlModalidade();
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($modalidade, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $modalidade);
        }

        return $resultados;
    }

    private function inserirDocumento()
    {
        $arquivoInformado = $_FILES['fileArquivo'];

        if ($arquivoInformado['size'] == 0) {
            $this->getUI()->mensagemSistema('É preciso Informar um Arquivo', 0);
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

    private function removerDocumento()
    {
        $idDocumento = filter_var($_POST['documentoExcluir'], FILTER_VALIDATE_INT);

        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);
        $this->buildSelectTipoControle();

        $this->reiniciarTela();
    }

    private function removerItem()
    {
        if ($_POST['Botao'] == 'RemoverItem' && !isset($_POST['CheckItem'])) {
            $this->getUI()->mensagemSistema('É preciso selecionar um item da ata para remover', 0);
        } else {
            $itensDeletados = array();
            if (!empty($_SESSION['itens_deletados'])) {
                $itensDeletados = $_SESSION['itens_deletados'];
            }

            if (isset($_POST['CheckItem'])) {
                foreach($_POST['CheckItem'] as $item){
                    array_push($itensDeletados, $item);
                }

                $_SESSION['itens_deletados'] = $itensDeletados;
            }
        }

        $this->buildSelectTipoControle();
    }


    private function pesquisarFornecedor($fornecedorTipo)
    {
        if ($fornecedorTipo == 'original') {
            $documento = $_POST['fornecedorOriginalProcesso'];
        } else {
            $documento =  $_POST['fornecedorAtualProcesso'];
        }

        $documento = preg_replace('/[^0-9]/', '', $documento);
        $fornecedor = $this->getUI()->getAdaptacao()->getNegocio()->consultarFornecedorPorDocumento($documento);
        $itens =$this->getUI()->getAdaptacao()->atualizaValoresItemTela();
        $ata = $this->getUI()->getAdaptacao()->atualizaValoresAtaTela();

        if (!empty($fornecedor)){
            if ($fornecedorTipo == 'original') {
                $ata['aforcrsequ'] = $fornecedor->aforcrsequ;
            }else {
                $ata['aforcrseq1'] = $fornecedor->aforcrsequ;
            }
        } else {
            if ($fornecedorTipo == 'original') {
                $ata['aforcrsequ'] = null;
            }else {
                $ata['aforcrseq1'] = null;
            }
        }
        $ata = $this->arrayParaObject($ata);

        $this->getUI()->plotarBlocoProcesso($ata, $fornecedor, $fornecedorTipo);
        $itens = $this->getUI()->getAdaptacao()->recuperarValorItens();
        $this->getUI()->plotarBlocoItem($itens);
        $this->buildSelectTipoControle();
    }


    // Madson
    //Função que checa se a ATA está vinculada a uma SCC de tipo SARP; 
    public function checaSeTipoSARP(){
        $db = Conexao();
        $carpnosequ = $_REQUEST['carpnosequ'];

        //Condicional para checar se é SARP
        
        $contTipocompra = 0;
        $sql = "select count(*) from sfpc.tbsolicitacaocompra
                                where ctpcomcodi = 5 and carpnosequ = $carpnosequ";
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($contTipocompra);
        $checagem = intval($contTipocompra[0]);

        if($checagem == 0){//Caso seja SARP, retorna para que a função delete seja permitida;
            return false; 
        }else{//caso não seja, retorna para que a mensagem seja mostrada;
            return true;
        }
    }

    // Função criada para excluir Atas de Registro de Preço de SCCs que não são do tipo SARP;
    public function excluirAtaNaoSARP(){  
        $db = Conexao();
        $carpnosequ = $_REQUEST['carpnosequ'];
        
        $SqlDel1 = "Delete from sfpc.tbataregistroprecoexterna where carpnosequ = $carpnosequ";
        $resultado = executarSQL($db, $SqlDel1);        
        ClaDatabasePostgresql::hasError($resultado);

        $SqlDel2 = "Delete from sfpc.tbitemataregistropreconova where carpnosequ = $carpnosequ";
        $resultado = executarSQL($db, $SqlDel2);        
        ClaDatabasePostgresql::hasError($resultado);

        $SqlDel3 = "Delete from sfpc.tbdocumentoatarp where carpnosequ = $carpnosequ";
        $resultado = executarSQL($db, $SqlDel3);        
        ClaDatabasePostgresql::hasError($resultado);

        $SqlDel4 = "Delete from sfpc.tbataregistropreconova where carpnosequ = $carpnosequ";
        $resultado = executarSQL($db, $SqlDel4);        
        ClaDatabasePostgresql::hasError($resultado);

        $this->getUI()->setMensagemFeedBack('Ata excluida com sucesso', 1);
        $uri = 'CadAtaRegistroPrecoExternaSelecionarNovo.php';
        header('Location: ' . $uri);
            
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoExternaSelecionarAlterar());
