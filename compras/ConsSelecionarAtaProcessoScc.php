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
 * @version   Git: v1.8.0-97-g28abed4
 */

/**
 * HISTÓRICO DE ALTERAÇÕES NO PROGRAMA
 * -------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
 * Link:     http://redmine.recife.pe.gov.br/issues/81057
 * Versão:   v1.22.0-12-g99b595d
 * -------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     23/05/2019
 * Objetivo: Tarefa Redmine 210696
 * -------------------------------------------------------------------------------------------
 * Alterado: Lucas Vicente e Lucas André
 * Data:     20/04/2023
 * Objetivo: Tarefa Redmine 281919
 * -------------------------------------------------------------------------------------------
 */

require_once dirname(__FILE__) . '/../funcoes.php';
if (! @require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class Dados
{

    /**
     * [sqlOrgaoAtaGerada description]
     * step 3
     * 
     * @return [type] [description]
     */
    private function sqlAtasProcesso($dados)
    {
        $sql = "SELECT a.carpnosequ, a.aarpinpzvg, a.tarpinulat, a.farpinsitu, fc.nforcrrazs, a.aforcrsequ FROM sfpc.tbataregistroprecointerna a";
        $sql .= " LEFT JOIN sfpc.tbparticipanteatarp p";
        $sql .= "   ON a.carpnosequ = p.carpnosequ";
        $sql .= " INNER JOIN sfpc.tbfornecedorcredenciado fc";
        $sql .= "   ON a.aforcrsequ = fc.aforcrsequ ";
        $sql .= " WHERE a.clicpoproc =".$dados['processo'];
        $sql .= "   AND a.alicpoanop =".$dados['ano'];
        $sql .= "   AND a.cgrempcodi =".$dados['grupo'];
        $sql .= "   AND a.ccomlicodi =".$dados['comissao'];
        $sql .= "   AND a.corglicodi =".$dados['orgao'];
        $sql .= "   AND a.farpinsitu = 'A' ";
        
        // Participante
        $orgaoCentroCusto = $this->getOrgaoCentroCusto();
        if($_GET['tipoSarp'] == 'P') {
            $sql .= "   AND p.corglicodi = ".$orgaoCentroCusto;
        }

        $sql .= " group by a.carpnosequ, a.aarpinpzvg, a.tarpinulat, a.farpinsitu, fc.nforcrrazs, a.aforcrsequ, a.carpincodn ";
        $sql .= " order by a.carpincodn asc ";

        return $sql;
    }
    /**
     * [sqlProcessoRegistroPrecoEmHomologacao description]
     *
     * @param  [type] $orgao
     *                       [description]
     * @param  [type] $ano
     *                       [description]
     * @return [type] [description]
     */
    private function sqlProcessoLicitacao($processo, $ano, $orgao, $grupo, $comissao)
    {        
        $sql = "SELECT ";
        $sql .= "DISTINCT C.EGREMPDESC ,";
        $sql .= "E.EMODLIDESC ,";
        $sql .= "D.ECOMLIDESC ,";
        $sql .= "A.CLICPOPROC ,";
        $sql .= "A.ALICPOANOP ,";
        $sql .= "A.CLICPOCODL ,";
        $sql .= "A.ALICPOANOL ,";
        $sql .= "A.XLICPOOBJE ,";
        $sql .= "A.TLICPODHAB ,";
        $sql .= "B.EORGLIDESC ,";
        $sql .= "A.CGREMPCODI ,";
        $sql .= "A.CCOMLICODI ,";
        $sql .= "A.CORGLICODI ,";
        $sql .= "D.ECOMLILOCA ,";
        $sql .= "D.ACOMLIFONE ,";
        $sql .= "D.ACOMLINFAX, ";
        $sql .= "A.CLICPOCODL ";
        $sql .= "FROM ";
        $sql .= "SFPC.TBLICITACAOPORTAL A ,";
        $sql .= "SFPC.TBORGAOLICITANTE B ,";
        $sql .= "SFPC.TBGRUPOEMPRESA C ,";
        $sql .= "SFPC.TBCOMISSAOLICITACAO D ,";
        $sql .= "SFPC.TBMODALIDADELICITACAO E ,";
        $sql .= "SFPC.TBFASELICITACAO F ,";
        if ($_SESSION ['TipoAta'] == 'I') {
            $sql .= "SFPC.tbataregistroprecointerna ata ";
        } else {
            $sql .= "SFPC.tbataregistroprecoexterna ata ";
        }
        $sql .= "WHERE ";
        $sql .= "A.CORGLICODI = B.CORGLICODI ";
        $sql .= "AND A.FLICPOSTAT = 'A' ";
        $sql .= "AND A.CGREMPCODI = C.CGREMPCODI ";
        $sql .= "AND A.CCOMLICODI = D.CCOMLICODI ";
        $sql .= "AND A.CMODLICODI = E.CMODLICODI ";
        $sql .= "AND F.CLICPOPROC = A.CLICPOPROC ";
        $sql .= "AND A.CLICPOPROC =".$processo;
        $sql .= "AND F.ALICPOANOP = A.ALICPOANOP ";
        $sql .= "AND F.CGREMPCODI = A.CGREMPCODI ";
        $sql .= "AND F.CCOMLICODI = A.CCOMLICODI ";
        $sql .= "AND F.CORGLICODI = A.CORGLICODI ";
        $sql .= "AND A.CORGLICODI =".$orgao;
        $sql .= "AND A.FLICPOREGP = 'S' ";
        $sql .= "AND F.CFASESCODI = 13";
        $sql .= "AND A.ALICPOANOP =".$ano;
        $sql .= "AND A.CCOMLICODI =".$comissao;
        if ($_SESSION ['TipoAta'] == 'I') {
            $sql .= "AND A.clicpoproc = ata.clicpoproc ";
        } else {
            $sql .= "AND A.clicpoproc = ata.earpexproc ";
        }
        $sql .= "AND ata.farpinsitu = 'A'";
        $sql .= "ORDER BY ";
        $sql .= "C.EGREMPDESC ,";
        $sql .= "E.EMODLIDESC ,";
        $sql .= "D.ECOMLIDESC ,";
        $sql .= "A.ALICPOANOP ,";
        $sql .= "A.CLICPOPROC";

        return $sql;
    }
    /**
     * [consultarProcessoDoOrgaoNoAno description]
     *
     * @param  [type] $orgao
     *                       [description]
     * @param  [type] $ano
     *                       [description]
     * @return [type] [description]
     */
    public function consultarProcessoLicitacao($processo, $ano, $orgao, $grupo, $comissao)
    {
        $resultados = array();
        $processos = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlProcessoLicitacao($processo, $ano, $orgao, $grupo, $comissao));

        while ($resultado->fetchInto($processos, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $processos;
        }
        
        return $resultados;
    }
    /**
     * [consultarOrgaoComNumeracaoGerada description]
     * step 2
     * 
     * @return [type] [description]
     */
    public function consultarAtasProcesso($processo)
    {
        $resultados = array();
        $atas = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlAtasProcesso($processo));

        while ($resultado->fetchInto($atas, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $atas;
        }
        
        return $resultados;
    }    

    /**
     * [consultarValoresMaterial description]
     * @param  [type] $produtoCodigo [description]
     * @return [type]                [description]
     */
    public function consultarValoresMaterial($produtoCodigo)
    {
        $resultado = sqlConsultaItem($produtoCodigo);

        return $resultado;
    }
    /**
     * [consultarValoresServico description]
     * @param  [type] $produtoCodigo [description]
     * @return [type]                [description]
     */

    /**
     * [consultarItensDaAta description]
     * @param  [type] $ata [description]
     * @return [type]      [description]
     */

    function getOrgaoCentroCusto() {
        $resultados = '';
        $sql = "SELECT CCPORT.CCENPOSEQU, CCPORT.CORGLICODI ";
        $sql .= "   FROM SFPC.TBCENTROCUSTOPORTAL CCPORT ";
        $sql .= "   WHERE CCPORT.CCENPOSEQU = " . $_SESSION['centroCustoAnterior'];

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        while ($resultado->fetchInto($res, DB_FETCHMODE_OBJECT)) {
            $resultados = $res;
        }
        
        return $resultados->corglicodi; 
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados). 
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *
 */
class GUI extends BaseIntefaceGraficaUsuario
{
    public $geralTpl;

    public function __construct()
    {
        // $this->geralTpl = new TemplatePaginaPadrao("templates/DetalharProcessoAtasScc.html", "Planejamento > DFD > Encaminhar");
        $this->setAdaptacao(new Adaptacao());
        // $this->getAdaptacao()->setTemplate($this->getTemplate());
        
    }
    /**
     * [proccessPrincipal description]
     * @return [type] [description]
     */
    public function proccessPrincipal($tpl)
    {
        //Recebe a variavel global para organizar no código.

        $processo   = $_GET ['processo'];
        $ano        = $_GET ['ano'];
        $orgao      = $_GET ['orgao'];
        $grupo      = $_GET ['grupo'];
        $comissao   = $_GET ['comissao'];
        $tipoSarp   = $_SESSION['tipoSarpAnterior'];
        $tipoAta    = $_SESSION['tipoAtaAnterior'];

        $_SESSION['UsuOrgLogado'] = $orgao;

        $radio = $_POST['radioItem'];
        
        if ($radio != null) {
            
            $this->processSelecionar($radio[0], $tpl);
        }

        $processoLicitatorio = $this->getAdaptacao()->getNegocio()->getDados()->consultarProcessoLicitacao($processo, $ano, $orgao, $grupo, $comissao);
        $this->getAdaptacao()->plotarBlocoProcesso($processoLicitatorio, $tpl);
        
        $dados = array(
            'processo' => $processo,
            'ano' => $ano,
            'orgao' => $orgao,
            'grupo' => $grupo,
            'comissao' => $comissao
        );
        $atasDoProcesso = $this->getAdaptacao()->getNegocio()->getDados()->consultarAtasProcesso($dados); // step 1  
        
        Adaptacao::plotarBlocoAtas($atasDoProcesso, $tpl);
        
        $tpl->ACESSO_TITULO = "ATAS VIGENTES";
        $tpl->DESCRICAO = "Selecione a ata desejada.";
        $tpl->ACESSO_TITULO_ATA = "ATA(S) DE REGISTRO DE PREÇO";
        $tpl->NAME_BOTAO = "selecionar";
        $tpl->VALOR_BOTAO = "Selecionar";
        $tpl->CLICK_BOTAO = "$('form').submit()";
        $tpl->NAME_BOTAO_VOLTAR = "voltar";
        $tpl->VALOR_BOTAO_VOLTAR = "Voltar";
        $tpl->CLICK_BOTAO_VOLTAR = "location.href='ConsProcessoPesquisarScc.php?Programa=CadSolicitacaoCompraIncluir&CampoProcessoSARP=NumProcessoSARP&CampoAnoSARP=AnoProcessoSARP&CampoComissaoCodigoSARP=ComissaoCodigoSARP&CampoOrgaoLicitanteCodigoSARP=OrgaoLicitanteCodigoSARP&CampoGrupoEmpresaCodigoSARP=GrupoEmpresaCodigoSARP&CampoCarregaProcessoSARP=CarregaProcessoSARP&TipoAta=".$tipoAta."&TipoSarp=".$tipoSarp."';";
        $tpl->NOME_PROGRAMA = "ConsSelecionarItensAtaProcessoScc.php?processo=".$processo."&ano=".$ano."&orgao=".$orgao."&grupo=".$grupo."&tipoSarp=".$tipoSarp."&comissao=".$comissao;
        
    }
    
    /**
     * [processSelecionar description]
     *
     * @return [type] [description]
     */
    public function processSelecionar($ata)
    {
       
        $itensAta = consultarItensDaAta($ata);
        $pos = 0;
        
        foreach ($itensAta as $item) {
            if ($item->cmatepsequ != null) {
                $this->getAdaptacao()->getNegocio()->montarArrayMaterial($item, $pos);
            } elseif ($item->cservpsequ != null) {
                $this->getAdaptacao()->getNegocio()->montarArrayServico($item, $pos);
            }

            $pos ++;
        }

        $_SESSION ['ataCasoSARP'] = $ata;
        echo "<script>opener.document.CadSolicitacaoCompraIncluirManterExcluir.submit()</script>";
        echo "<script>self.close()</script>";
     
    }
}
/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 *
 */
function procurarAtaInterna($carpnosequ)
{   
    $resultado = array();
   
    $resultado = sqlConsultarProcurarAta($carpnosequ);
    
    return $resultado;         
}
function sqlConsultarProcurarAta($carpnosequ)
{
    $db = Conexao();
    $sql = "
        SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = $carpnosequ     
    ";
    $resultado = executarSQL($db, $sql);
    
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dados[] = $retorno;
    }

    return $dados;
}
function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
{   
    $resultado = array();

    $resultado = sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    
    return $resultado;               
}
function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
{
    $db = Conexao();
    $sql = "
        SELECT
            ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
        FROM sfpc.tbcentrocustoportal ccp
        WHERE 1=1 ";

    if ($corglicodi != null || $corglicodi != "") {
        $sql .= " AND ccp.corglicodi = $corglicodi ";
    }
    $resultado = executarSQL($db, $sql);
    
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dados[] = $retorno;
    }

    return $dados;
}
function listarTodosDocumentos($carpnosequ)
{
    $resultado = consultarTodosDocumentosAta($carpnosequ);
    return $resultado;
}
function consultarTodosDocumentosAta($carpnosequ)
{
    $db = Conexao();
    $sql = "
        SELECT encode(idocatarqu, 'base64') as arquivo, carpnosequ, cdocatsequ, edocatnome, cusupocodi
          FROM sfpc.tbdocumentoatarp
         WHERE carpnosequ = $carpnosequ
    ";
    $resultado = executarSQL($db, $sql);
    
    $documentos = array();
    $documento = null;
    while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
        $documentos[] = $documento;
    }
    if (PEAR::isError($resultado)) {
        ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: ");
    }

    return $documentos;
}
function consultarItensDaAta($ata)
{
    $resultados = array();

    $resultados = sqlItensDaAta($ata);

    return $resultados;
}
function sqlItensDaAta($ata)
{
    $db = Conexao();
    if ($_SESSION['TipoSarp'] == 'P') {
        $tabelaJoin = 'sfpc.tbparticipanteatarp pa';
    } else {
        $tabelaJoin = 'sfpc.tbcaronaorgaoexterno pa';
    }

    if ($_SESSION ['TipoAta'] == 'I') {
        $tabela = "sfpc.tbataregistroprecointerna ata";
    } else {
        $tabela = "sfpc.tbataregistroprecoexterna ata";
    }

    $sql = "SELECT *  ";
    $sql .= " FROM ";
    $sql .= " sfpc.tbitemataregistropreconova ata";
    $sql .= " inner join ".$tabelaJoin;
    $sql .= " on pa.carpnosequ = ata.carpnosequ";
    $sql .= " WHERE";
    $sql .= " ata.carpnosequ =".$ata;

    $resultado = executarSQL($db, $sql);
    
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dados[] = $retorno;
    }

    return $dados;
}
function consultarValoresMaterial($produtoCodigo)
{
    $resultado = sqlConsultaItem($produtoCodigo);

    return $resultado;
}
function sqlConsultaItem($codigoMaterial)
{
    $codigoMaterial = filter_var($codigoMaterial, FILTER_SANITIZE_NUMBER_INT);
    $db = Conexao();
    $sql = "
    SELECT
        DISTINCT M.EMATEPDESC ,
        U.EUNIDMSIGL ,
        I.EITESCDESCMAT
    FROM
        SFPC.TBMATERIALPORTAL M ,
        SFPC.TBUNIDADEDEMEDIDA U ,
        SFPC.TBITEMSOLICITACAOCOMPRA I
    WHERE
        M.CMATEPSEQU = $codigoMaterial
        AND U.CUNIDMCODI = M.CUNIDMCODI
        AND M.CMATEPSEQU = I.CMATEPSEQU
    ";

    $resultado = executarSQL($db, $sql);

    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dados[] = $retorno;
    }

    return $dados;
}
function sqlItemServico($itemCodigo)
{
    $db = Conexao();
    $sql = "
    SELECT
        m.eservpdesc
    FROM
        sfpc.tbservicoportal m
    WHERE
        m.cservpsequ = $itemCodigo
    ";

    $resultado = executarSQL($db, $sql);
    
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dados[] = $retorno;
    }

    return $dados;
}
function consultarValoresServico($produtoCodigo)
{
    $resultado = sqlItemServico($produtoCodigo);

    return $resultado;
}
class Adaptacao extends AbstractAdaptacao
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setNegocio(new Negocio());
    }

    // public function listarTodosDocumentos($carpnosequ)
    // {
    //     $resultado = consultarTodosDocumentosAta($carpnosequ);
    //     return $resultado;
    // }

    /**
     * [plotarBlocoOrgao description]
     *
     * @param  GUI    $gui
     *                        [description]
     * @param  [type] $orgaos
     *                        [description]
     * @return [type] [description]
     */
    public function plotarBlocoProcesso($processo, $tpl)
    {
        if ($processo == null) {
            return;
        }

        $processo = $processo[0];

        $tpl->CAMPO_COMISSAO = $processo->ecomlidesc;
        $tpl->CAMPO_PROCESSO = str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT);
        $tpl->CAMPO_ANO = $processo->alicpoanop;
        $tpl->CAMPO_MODALIDADE = $processo->emodlidesc;
        $tpl->CAMPO_LICITACAO = str_pad($processo->clicpocodl, 4, '0', STR_PAD_LEFT);;
        $tpl->CAMPO_ANO_LICITACAO = $processo->alicpoanol;
        $tpl->CAMPO_ORGAO_LICITANTE = $processo->eorglidesc;
    }
    /**
     * [plotarBlocoProcesso description]
     *
     * @param  GUI    $gui
     *                           [description]
     * @param  [type] $processos
     *                           [description]
     * @return [type] [description]
     */
    public function plotarBlocoAtas($atas, $tpl)
    {
        
        if ($atas == null) {
            return;
        }

        foreach ($atas as $ata) {            
            $codForn = $ata->aforcrsequ;

            $situacaoAtual = checarSituacaoAtualFornecedor($codForn);

            if ($situacaoAtual == 1) {
                $radioI = "radio";
                $msgForn = null;
            } else {
                $radioI = "hidden";
                $msgForn = "<font color='#ff0000'> - FORNECEDOR COM SANÇÕES NO SICREF</font>";
            }
           
            $ata_ = procurarAtaInterna($ata->carpnosequ);
            $ata_2 = current($ata_);
            $centro = consultarDCentroDeCustoUsuario($ata_2->cgrempcodi, $ata_2->cusupocodi, $ata_2->corglicodi);
            $objeto = current($centro);
        
            $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);        
            $numeroAtaFormatado .= "." . str_pad($ata_2->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata_2->aarpinanon;
           
            $tpl->TIPO = $radioI;
            $tpl->ID_ATA = $ata->carpnosequ;
            $tpl->NUMERO_ATA = $numeroAtaFormatado;
            $tpl->MSG_FORN = $msgForn;
            $tpl->ALTERACAO_ATA = date("d/m/Y", strtotime($ata->tarpinulat));
            $tpl->SITUACAO_ATA = ($ata->farpinsitu == 'A') ? 'ATIVA' : 'INATIVA';
            $tpl->MESES_ATA = $ata->aarpinpzvg;
            
            // Documentos
            $documentosAtas = listarTodosDocumentos($ata->carpnosequ);
            
            foreach ($documentosAtas as $key => $documento) {
                $_SESSION['documento'.$ata->carpnosequ.'arquivo'.$key] = $documento->idocatarqu;
                $tpl->VALOR_DOCUMENTO_KEY = 'documento'.$ata->carpnosequ.'arquivo'.$key;
                
                $documentoDecodificado = base64_decode($documento->arquivo);

                $tpl->VALOR_DOCUMENTO = $documento->edocatnome;
                
                $tpl->block("BLOCO_DOCUMENTOS");
                
            }

            $tpl->block("bloco_lista_ata");
        }
        
    }
}
/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 *
 */
class Negocio extends BaseNegocio
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setDados(new Dados());
    }
    /**
     * [montarArrayServico description]
     * @param  [type] $item [description]
     * @param  [type] $pos  [description]
     * @return [type]       [description]
     */
    public function montarArrayServico($item, $pos)
    {
        echo 'entrou';die;
        $servicoDTO = consultarValoresServico($item->cservpsequ);

        $servicos [$pos] = array();
        $servicos [$pos] ["tipo"] = TIPO_ITEM_SERVICO;
        $servicos [$pos] ["codigo"] = $item->cservpsequ;
        $servicos [$pos] ["descricao"] = $servicoDTO->eservpdesc;
        $servicos [$pos] ["check"] = false;
        $servicos [$pos] ["quantidade"] = "0,0000";
        $servicos [$pos] ["valorEstimado"] = "0,0000";

        // valores em float para uso em funções
        $servicos [$pos] ["quantidadeItem"] = 0;
        $servicos [$pos] ["valorItem"] = 0;
        $servicos [$pos] ["quantidadeExercicio"] = "0";
        $servicos [$pos] ["totalExercicio"] = '0,0000';
        $servicos [$pos] ["fornecedor"] = "";
        $servicos [$pos] ["posicao"] = $pos;
        $servicos [$pos] ["posicaoItem"] = $pos + 1; // posição mostrada na tela
        $servicos [$pos] ["isObras"] = isObras(Conexao(), $servicos [$pos] ["codigo"], TIPO_ITEM_SERVICO);

        $_SESSION ['servicoSarp'] [$pos] = $servicos [$pos];
      
    }
    /**
     * [montarArrayMaterial description]
     * @param  [type] $item [description]
     * @param  [type] $pos  [description]
     * @return [type]       [description]
     */
    public function montarArrayMaterial($item, $pos)
    {   echo 'entrou';die;
        $materialDTO = consultarValoresMaterial($item->cmatepsequ);

        $materiais [$pos] = array();
        $materiais [$pos] ["tipo"] = TIPO_ITEM_MATERIAL;
        $materiais [$pos] ["codigo"] = $item->cmatepsequ;
        $materiais [$pos] ["descricao"] = $materialDTO->ematepdesc;
        /**
         * adiciona descricao detalhada
         */
        $materiais [$pos] ["descricaoDetalhada"] = strtoupper2($materialDTO->eitescdescmat);
        // echo "<pre>";var_dump($MaterialDescricaoDetalhada);die();
        $materiais [$pos] ["unidade"] = $materialDTO->eunidmsigl;

        $materiais [$pos] ["check"] = false;
        $materiais [$pos] ["quantidade"] = "0,0000";
        $materiais [$pos] ["valorEstimado"] = "0,0000";

        // valores em float para uso em funções
        $materiais [$pos] ["quantidadeItem"] = 0;
        $materiais [$pos] ["valorItem"] = 0;
        $materiais [$pos] ["quantidadeExercicio"] = "0,0000";
        $materiais [$pos] ["totalExercicio"] = '0,0000';
        $materiais [$pos] ["marca"] = "";
        $materiais [$pos] ["modelo"] = "";
        $materiais [$pos] ["fornecedor"] = "";
        $materiais [$pos] ["posicao"] = $pos;
        $materiais [$pos] ["posicaoItem"] = $pos + 1; // posição mostrada na tela
        $materiais [$pos] ["reservas"] = array();
        $materiais [$pos] ["trp"] = calcularValorTrp(Conexao(), TIPO_COMPRA_SARP, $materiais [$pos] ["codigo"]);
        $materiais [$pos] ["isObras"] = isObras(Conexao(), $materiais [$pos] ["codigo"], TIPO_ITEM_MATERIAL);

        if (! is_null($materiais [$pos] ["trp"])) {
            $materiais [$pos] ["trp"] = converte_valor_estoques($materiais [$pos] ["trp"]);
        }

        $_SESSION ['materialSarp'] [$pos] = $materiais [$pos];
       
    }

    public function consultarTodosDocumentosAta($carpnosequ) {
        return consultarTodosDocumentosAta($carpnosequ);
       
    }
}

/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new GUI();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);
$tpl = new TemplatePaginaPadrao("templates/DetalharProcessoAtasScc.html", " ");
switch ($acao) {
    case 'Selecionar':
        $app->processSelecionar($tpl);
        break;
    case 'Principal':
    default:
    
        $app->proccessPrincipal($tpl);
        break;
}

$tpl->show();
