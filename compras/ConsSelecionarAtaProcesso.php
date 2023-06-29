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
 * ---------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
 * Link:     http://redmine.recife.pe.gov.br/issues/81057
 * Versão:   v1.22.0-12-g99b595d
 */
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
     * [sqlConsultaItem description]
     * @param  integer $codigoMaterial [description]
     * @return string                 [description]
     */
    private function sqlConsultaItem($codigoMaterial)
    {
        $codigoMaterial = filter_var($codigoMaterial, FILTER_SANITIZE_NUMBER_INT);

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
            M.CMATEPSEQU = %d
            AND U.CUNIDMCODI = M.CUNIDMCODI
            AND M.CMATEPSEQU = I.CMATEPSEQU
        ";

        return sprintf($sql, $codigoMaterial);
    }

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
     */
    private function sqlItensDaAta($ata)
    {
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

        $sql = "SELECT ";
        $sql .= "* ";
        $sql .= "FROM";
        $sql .= " sfpc.tbitemataregistropreconova ata";
        $sql .= " inner join ".$tabelaJoin;
        $sql .= " on pa.carpnosequ = ata.carpnosequ";
        $sql .= " WHERE";
        $sql .= " ata.carpnosequ =".$ata;

        return $sql;
    }
    /**
     * [sqlItemServico description]
     * @param  [type] $itemCodigo [description]
     * @return [type]             [description]
     */
    private function sqlItemServico($itemCodigo)
    {
        $sql = "
        SELECT
            m.eservpdesc
        FROM
            sfpc.tbservicoportal m
        WHERE
            m.cservpsequ = %d
        ";

        return sprintf($sql, $itemCodigo);
    }

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
     */
    private function sqlAtasProcesso($processo)
    {
        if ($_SESSION ['TipoAta'] == 'I') {
            $sql = "select a.carpnosequ,fc.nforcrrazs from sfpc.tbataregistroprecointerna a";
        } else {
            $sql = "select a.carpnosequ,fc.nforcrrazs from sfpc.tbataregistroprecoexterna a";
        }
        $sql .= " inner join sfpc.tbparticipanteatarp p";
        $sql .= " on a.carpnosequ = p.carpnosequ";
        $sql .= " inner join sfpc.tbfornecedorcredenciado fc";
        $sql .= " on a.aforcrsequ = fc.aforcrsequ";

        if ($_SESSION ['TipoAta'] == 'I') {
            $sql .= " where a.clicpoproc =".$processo;
        } else {
            $sql .= " where a.earpexproc =".$processo;
        }

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
    private function sqlProcessoLicitacao($processo, $ano, $orgao, $grupo)
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
        $sql .= "D.ACOMLINFAX ";
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
    public function consultarProcessoLicitacao($processo, $ano, $orgao, $grupo)
    {
        $resultados = array();
        $processos = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlProcessoLicitacao($processo, $ano, $orgao, $grupo));

        while ($resultado->fetchInto($processos, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $processos;
        }

        return $resultados;
    }
    /**
     * [consultarOrgaoComNumeracaoGerada description]
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
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultaItem($produtoCodigo));

        $resultado->fetchInto($produto, DB_FETCHMODE_OBJECT);

        return $produto;
    }
    /**
     * [consultarValoresServico description]
     * @param  [type] $produtoCodigo [description]
     * @return [type]                [description]
     */
    public function consultarValoresServico($produtoCodigo)
    {
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlItemServico($produtoCodigo));

        $resultado->fetchInto($produto, DB_FETCHMODE_OBJECT);

        return $produto;
    }
    /**
     * [consultarItensDaAta description]
     * @param  [type] $ata [description]
     * @return [type]      [description]
     */
    public function consultarItensDaAta($ata)
    {
        $resultados = array();
        $processos = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlItensDaAta($ata));

        while ($resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $itemAta;
        }

        return $resultados;
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
class GUI extends BaseIntefaceGraficaUsuario
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePortal("templates/DetalharProcessoAtas.html"));
        $this->setAdaptacao(new Adaptacao());
        $this->getAdaptacao()->setTemplate($this->getTemplate());
    }
    /**
     * [proccessPrincipal description]
     * @return [type] [description]
     */
    public function proccessPrincipal()
    {
        $processo = $_GET ['processo'];
        $ano = $_GET ['ano'];
        $orgao = $_GET ['orgao'];
        $grupo = $_GET ['grupo'];

        $_SESSION ['UsuOrgLogado'] = $orgao;

        $radio = $_POST ['radioItem'];

        if ($radio != null) {
            $this->processSelecionar($radio [0]);
        }

        $processoLicitatorio = $this->getAdaptacao()->getNegocio()->getDados()->consultarProcessoLicitacao($processo, $ano, $orgao, $grupo);

        $this->getAdaptacao()->plotarBlocoProcesso($processoLicitatorio);

        $atasDoProcesso = $this->getAdaptacao()->getNegocio()->getDados()->consultarAtasProcesso($processo);
        Adaptacao::plotarBlocoAtas($atasDoProcesso);

        $this->getTemplate()->ACESSO_TITULO = "ATAS VIGENTES";
        $this->getTemplate()->DESCRICAO = "Selecione a ata desejada.";
        $this->getTemplate()->ACESSO_TITULO_ATA = "ATA(S) DE REGISTRO DE PREÇO";
        $this->getTemplate()->NAME_BOTAO = "selecionar";
        $this->getTemplate()->VALOR_BOTAO = "Selecionar";
        $this->getTemplate()->CLICK_BOTAO = "$('form').submit()";
        $this->getTemplate()->NAME_BOTAO_VOLTAR = "voltar";
        $this->getTemplate()->VALOR_BOTAO_VOLTAR = "Voltar";
        $this->getTemplate()->CLICK_BOTAO_VOLTAR = "location.href='ConsProcessoPesquisar.php';";
        $this->getTemplate()->NOME_PROGRAMA = "ConsSelecionarAtaProcesso";
    }
    /**
     * [processSelecionar description]
     *
     * @return [type] [description]
     */
    public function processSelecionar($ata)
    {
        $itensAta = $this->getAdaptacao()->getNegocio()->getDados()->consultarItensDaAta($ata);
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
class Adaptacao extends AbstractAdaptacao
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setNegocio(new Negocio());
    }

    /**
     * [plotarBlocoOrgao description]
     *
     * @param  GUI    $gui
     *                        [description]
     * @param  [type] $orgaos
     *                        [description]
     * @return [type] [description]
     */
    public function plotarBlocoProcesso($processo)
    {
        if ($processo == null) {
            return;
        }
        $processo = $processo [0];

        $this->getTemplate()->CAMPO_COMISSAO = $processo->corglicodi;
        $this->getTemplate()->CAMPO_PROCESSO = $processo->CLICPOPROC;
        $this->getTemplate()->CAMPO_ANO = $processo->aarpinanon;
        $this->getTemplate()->CAMPO_MODALIDADE = $processo->emodlidesc;
        $this->getTemplate()->CAMPO_LICITACAO = "--";
        $this->getTemplate()->CAMPO_ANO_LICITACAO = $processo->ALICPOANOP;
        $this->getTemplate()->CAMPO_ORGAO_LICITANTE = $processo->eorglidesc;
        $this->getTemplate()->CAMPO_OBSERVACAO = $processo->earpinobje;
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
    public function plotarBlocoAtas($atas)
    {
        if ($atas == null) {
            return;
        }
        foreach ($atas as $ata) {
            $this->getTemplate()->NUMERO_ATA = $ata->carpnosequ;

            $this->getTemplate()->FORNECEDOR_ATA = $ata->nforcrrazs;

            $this->getTemplate()->block("bloco_lista_ata");
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
        $servicoDTO = $this->getDados()->consultarValoresServico($item->cservpsequ);

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
        $servicos [$pos] ["isObras"] = isObras(ClaDatabasePostgresql::getConexao(), $servicos [$pos] ["codigo"], TIPO_ITEM_SERVICO);

        $_SESSION ['servicoSarp'] [$pos] = $servicos [$pos];
    }
    /**
     * [montarArrayMaterial description]
     * @param  [type] $item [description]
     * @param  [type] $pos  [description]
     * @return [type]       [description]
     */
    public function montarArrayMaterial($item, $pos)
    {
        $materialDTO = $this->getDados()->consultarValoresMaterial($item->cmatepsequ);

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
}

/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new GUI();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

switch ($acao) {
    case 'Selecionar':
        $app->processSelecionar();
        break;
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();
