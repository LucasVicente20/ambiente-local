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
 * ------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     28/06/2018
 * Objetivo: Tarefa Redmine 197622
 * ------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     04/09/2018
 * Objetivo: Tarefa Redmine 201677
 * ------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     04/09/2018
 * Objetivo: Tarefa Redmine 204375
 * ------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     19/12/2018
 * Objetivo: Tarefa Redmine 206574
 * -----------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     11/03/2019
 * Objetivo: Tarefa Redmine 212502
 * ------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     22/05/2019
 * Objetivo: Tarefa Redmine 217300
 * ------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Vicente e Lucas André
 * Data:     20/04/2023
 * Objetivo: Tarefa Redmine 281919
 * ------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

if (! @require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */


function sqlItensDaAta($nAta, $orgao = null)
{   
    /*if ($_SESSION['TipoSarp'] == 'P') {
        $tabelaJoin = 'sfpc.tbparticipanteatarp pa';
    } else {
        $tabelaJoin = 'sfpc.tbcaronaorgaoexterno pa';
    }

    if ($_SESSION ['TipoAta'] == 'I') {
        $tabela = "sfpc.tbataregistroprecointerna ata";
    } else {
        $tabela = "sfpc.tbataregistroprecoexterna ata";
    }*/
    $db = Conexao();
    $sql = "SELECT ITEMA.CARPNOSEQU, ITEMA.CITARPSEQU, ITEMA.AITARPQTOR, ITEMA.AITARPQTAT, ";
    $sql .= "       ITEMA.AITARPORDE, ITEMA.CMATEPSEQU, ITEMA.CSERVPSEQU, ITEMA.EITARPDESCMAT, ";
    $sql .= "       ITEMA.EITARPDESCSE, MA.EMATEPDESC, SE.ESERVPDESC, ITEMA.VITARPVATU, ";
    $sql .= "       ITEMA.VITARPVORI, ITEMA.EITARPMARC, ITEMA.EITARPMODE, UM.EUNIDMSIGL ";
    if ($_GET['tipoSarp'] == 'P') {
        $sql .= "       , PA.APIARPQTAT, PA.APIARPQTUT, PA.VPIARPVATU ";
    }
    $sql .= "   FROM SFPC.TBITEMATAREGISTROPRECONOVA ITEMA ";
    if ($_GET['tipoSarp'] == 'P') {
        $sql .= 'LEFT JOIN SFPC.TBPARTICIPANTEITEMATARP PA ON ITEMA.CITARPSEQU = PA.CITARPSEQU';
        $sql .= "       AND ITEMA.CARPNOSEQU = PA.CARPNOSEQU ";
    }
    $sql .= "   LEFT JOIN SFPC.TBMATERIALPORTAL MA "; // TBMATERIALPORTAL
    $sql .= "       ON ITEMA.CMATEPSEQU = MA.CMATEPSEQU ";
    $sql .= "   LEFT JOIN SFPC.TBSERVICOPORTAL SE "; // TBSERVICOPORTAL
    $sql .= "       ON ITEMA.CSERVPSEQU = SE.CSERVPSEQU ";
    $sql .= "   LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UM "; // TBUNIDADEDEMEDIDA
    $sql .= "       ON MA.CUNIDMCODI = UM.CUNIDMCODI ";
    $sql .= "   WHERE 1=1 AND ITEMA.CARPNOSEQU = " . $nAta;
    if ($_GET['tipoSarp'] == 'P') {
        $sql .= "   AND PA.CORGLICODI = " . $orgao;
    }
    $sql .= "   AND ITEMA.FITARPSITU = 'A' ";
    $sql .= "   GROUP BY ITEMA.CARPNOSEQU, ITEMA.CITARPSEQU, MA.EMATEPDESC, SE.ESERVPDESC, UM.EUNIDMSIGL, ITEMA.AITARPQTOR, ITEMA.AITARPQTAT, 
    ITEMA.AITARPORDE, ITEMA.CMATEPSEQU, ITEMA.CSERVPSEQU, ITEMA.EITARPDESCMAT, ITEMA.EITARPDESCSE, ITEMA.VITARPVATU, ITEMA.EITARPMARC, ITEMA.EITARPMODE, ITEMA.VITARPVATU, ITEMA.VITARPVORI"; // TODO Verificar esse group by pq estava repetindo os registros
    if ($_GET['tipoSarp'] == 'P') {
        $sql .= "       , PA.APIARPQTAT, PA.APIARPQTUT, PA.VPIARPVATU ";
    }
    $sql .= "   ORDER BY ITEMA.aitarporde ASC ";
    $resultado = executarSQL($db, $sql);
    
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dados[] = $retorno;
    }

    return $dados;
}
function consultarItensDaAta($ata)
{  
    $resultados = array();
    
    $resultados = sqlItensDaAta($ata);

    return $resultados;
}
function verificarTipoControle($ata) {
   
    $db = Conexao();
    $sql = "
        SELECT arpn.farpnotsal 
        FROM sfpc.tbataregistropreconova arpn
        WHERE arpn. carpnosequ = $ata ";

    $resultado = executarSQL($db, $sql);

    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dados[] = $retorno;
    }
    
    return $dados;
}
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

        $sql = " SELECT
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
                    AND M.CMATEPSEQU = I.CMATEPSEQU ";

        return sprintf($sql, $codigoMaterial);
    }

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
     */

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


    function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
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

    function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $resultados = array();
        $centros = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi));

        while ($resultado->fetchInto($centros, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $centros;
        }
        
        return $resultados;               
    }

    function sqlConsultarFornecedorAta($ata, $tipo = 'E')
    {
        $sql  = " SELECT tbata.aforcrsequ, tbfc.aforcrccgc, tbfc.aforcrccpf, tbfc.nforcrrazs  ";
        if($tipo == 'I') {
            $sql .= " FROM sfpc.tbataregistroprecointerna as tbata ";
        } else {
            $sql .= " ,tbata.carpexcodn, tbata.aarpexanon FROM sfpc.tbataregistroprecoexterna as tbata ";
        }
        
        $sql .= "    left join sfpc.tbfornecedorcredenciado as tbfc on tbata.aforcrsequ = tbfc.aforcrsequ
             WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $ata);
    }

    function sqlConsultarProcurarAta($carpnosequ)
    {
        $sql = "
            SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $carpnosequ);
    }

    function procurarAtaInterna($carpnosequ)
    {   
        $resultados = array();
        $ata = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultarProcurarAta($carpnosequ));

        while ($resultado->fetchInto($atas, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $atas;
        }
        
        return $resultados;         
    }

    function procurarFornecedorAta($ata, $tipoAta)
    {   
        $resultados = '';
        $res = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultarFornecedorAta($ata, $tipoAta));

        while ($resultado->fetchInto($res, DB_FETCHMODE_OBJECT)) {
            $resultados = $res;
        }
        
        return $resultados;         
    }

    // Sql para retornar o tipo de controle da ata

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
        // $this->setTemplate(new TemplatePortal("templates/DetalharItensProcessoAtasScc.html"));
        $this->setAdaptacao(new Adaptacao());
        // $this->getAdaptacao()->setTemplate($tpl);
    }
    /**
     * [proccessPrincipal description]
     * 
     * TODO exibir o nº correto da ata
     * 
     * @return [type] [description]
     */
    public function proccessPrincipal($tpl)
    {       
        $radio      = isset($_POST['radioItem']) ? $_POST['radioItem'] : $_GET['ata'];
        $tipoAta    = isset($_GET['TipoAta']) ? $_GET['TipoAta'] : 'I';
        $close      = isset($_GET['close']) ? $_GET['close'] : 0;

        if(isset($_POST['radioItem'])) {
            $_SESSION['numeroAtaCasoSARP'] = $_POST['numeroAta'][$radio];
        }
    
        $processo               = $_GET ['processo'];
        $ano                    = $_GET ['ano'];
        $orgao                  = $_GET ['orgao'];
        $grupo                  = $_GET ['grupo'];
        $comissao               = (isset($_GET ['comissao'])) ? $_GET['comissao'] : $_SESSION['ataSarp']['comissao'];
        $tipoSarp               = (isset($_GET ['tipoSarp'])) ? $_GET['tipoSarp'] : $_SESSION['tipoSarp'];
        $_SESSION['ataSarp']    = array('ata'=>$radio,'processo'=>$processo,'ano'=>$ano,'orgao'=>$orgao,'grupo'=>$grupo,'comissao'=>$comissao);
        $_SESSION ['ataCasoSARP'] = $radio;
        
        // Remover os itens caso trocar a ata
        if(isset($_SESSION['ataCasoSARP']) && $_SESSION['ataCasoSARP'] != $radio) {
            unset($_SESSION['item']);
        }

        // salvar o carpnosequ na sessão
        // caseo seja externa o campo foi salvo na tela anterior
        $_SESSION['ataCasoSARP'] = $radio;      

        $orgaoAtual = getOrgaoCentroCusto(ClaDatabasePostgresql::getConexao(), $_SESSION['centroCustoAnterior']); 
    
        $itensAtasDoProcesso = consultarItensDaAta($radio, $orgaoAtual); // step 1  
        Adaptacao::plotarBlocoAtas($itensAtasDoProcesso, $tipoAta, $tipoSarp, $tpl);
        
        // Verificar fornecedor
        $fornecedor = $this->getAdaptacao()->getNegocio()->getDados()->procurarFornecedorAta($radio, $tipoAta);
        $fornecedor_text = '<input type="hidden" name="fornecedor" value="';
        $fornecedor_text .= (!empty($fornecedor->aforcrccgc)) ? $fornecedor->aforcrccgc : $fornecedor->aforcrccpf;
        $fornecedor_text .= '">';

        // Nº da ata
        if($tipoAta == 'I') {
            $ata_ = $this->getAdaptacao()->getNegocio()->getDados()->procurarAtaInterna($radio);
            $ata = current($ata_);
            $centro = $this->getAdaptacao()->getNegocio()->getDados()->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
            $objeto = current($centro);
                        
            $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);        
            $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;
        } else {
            $numeroAtaFormatado = $fornecedor->carpexcodn.'/'.$fornecedor->aarpexanon;
        }

        //$tpl->ID_ATA = $radio;
        //$tpl->ID_ITEM_ATA = $radio;

        $tpl->ACESSO_TITULO_ATA = "ITENS ATA(S) Nº " . $numeroAtaFormatado . " DE REGISTRO DE PREÇO";
        $tpl->FORNECEDOR = $fornecedor_text;
        $tpl->NAME_BOTAO = "incluir";
        $tpl->VALOR_BOTAO = "Incluir";
        $tpl->CLICK_BOTAO = "$('form').submit()";
        $tpl->NAME_BOTAO_VOLTAR = "voltar";
        $tpl->VALOR_BOTAO_VOLTAR = "Voltar";
        
        // Botão Voltar
        if($close) {
            $tpl->CLICK_BOTAO_VOLTAR = "window.close();";
        } else {
            if($tipoAta == 'E') {
                $tpl->CLICK_BOTAO_VOLTAR = "location.href='ConsProcessoPesquisarScc.php?Programa=CadSolicitacaoCompraIncluir&CampoProcessoSARP=NumProcessoSARP&CampoAnoSARP=AnoProcessoSARP&CampoComissaoCodigoSARP=ComissaoCodigoSARP&CampoOrgaoLicitanteCodigoSARP=OrgaoLicitanteCodigoSARP&CampoGrupoEmpresaCodigoSARP=GrupoEmpresaCodigoSARP&CampoCarregaProcessoSARP=CarregaProcessoSARP&TipoAta=E&TipoSarp=C';"; //tipoSarp
            } else {
                $tpl->CLICK_BOTAO_VOLTAR = "location.href='ConsSelecionarAtaProcessoScc.php?processo=".$processo."&ano=".$ano."&orgao=".$orgao."&grupo=".$grupo."&tipoSarp=".$tipoSarp."&comissao=".$comissao."';";
            }
        }
        
        $tpl->NOME_PROGRAMA = "ConsSelecionarItensAtaProcessoScc";
    }
    /**
     * [processSelecionar description]
     *
     * @return [type] [description]
     */
    public function processIncluir($itens)
    {
        if(!empty($itens['item'])) {            
            $sessionString = '';
            foreach ($itens['item'] as $key => $value) {
                $sessionString = $itens['descricao'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['codigo'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['unidade'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['tipo'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['unitario'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['marca'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['modelo'][$key].$GLOBALS['SimboloConcatenacaoArray']; 
                
                if($_SESSION['tipoControle'] == 2) {
                    $sessionString .= $itens['quantidadeSol'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['descricaoDetalhada'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['fornecedor'].$GLOBALS['SimboloConcatenacaoArray'].$itens['vitescunit'][$key];
                }else if($_SESSION['tipoControle'] == 1) {
                    $sessionString .= $itens['apiar'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['descricaoDetalhada'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['fornecedor'].$GLOBALS['SimboloConcatenacaoArray'].$itens['vitescunit'][$key];
                } else {
                    $sessionString .=$itens['quantidade'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['descricaoDetalhada'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['fornecedor'].$GLOBALS['SimboloConcatenacaoArray'].$itens['vitescunit'][$key];
                }

                $_SESSION['item'][$key]  = $sessionString;
            }

        }
        echo "<script>opener.document.forms[0].submit();</script>";
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
     * [plotarBlocoProcesso description]
     *
     * @param  GUI    $gui
     *                           [description]
     * @param  [type] $processos
     *                           [description]
     * @return [type] [description]
     */
    public function plotarBlocoAtas($itens, $tipoAta = 'I', $tipoSarp = 'C', $tpl) //lucas
    { 
        if ($itens == null) {
            return;
        }
        
        $tipoControle = verificarTipoControle($itens[0]->carpnosequ);
        $ataCorporativa = verificarAtaCorporativa(Conexao(), $itens[0]->carpnosequ);
        $_SESSION['tipoControle'] = $tipoControle[0];
        
        if($tipoControle[0] == 0) {
            $exibirTipo1  = 'display:none;';
            $exibirTipo2  = 'display:none;';
        } else if($tipoControle[0] == 1) {
            $exibirTipo2  = 'display:none;';
            $exibirTipo0  = 'display:none;';
        } else if($tipoControle[0] == 2) {
            $exibirTipo1  = 'display:none;';
        }

        foreach ($itens as $key => $item) {
            $apiar = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
            if(($tipoControle[0] == 1)) {
                $qtdItensAta = ($item->vitarpvatu != 0) ? $item->vitarpvatu : $item->vitarpvori;
            } else {
                $qtdItensAta = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
            }


            /*$fiels_utilizado = 'apiarpqtut'; // Campo para somar valor utilizado
            $field_scc = 'acoeitqtat';
            $fiels_carona_scc      = 'aitescqtso';
            $field_inclusao_direta = 'AITCRPQTUT';
            $field_carona_externa = 'acoeitqtat';
            if($tipoControle[0]->farpnotsal == 1) {
                $fiels_carona_scc = 'vitescunit';
                $field_scc = 'vcoeitvuti';
                $fiels_utilizado = 'vitcrpvuti'; // Campo para somar valor utilizado
                $field_inclusao_direta = 'VITCRPVUTI';
                $field_carona_externa = 'vcoeitvuti';
                //$fiels_solicitado = 'vpiarpvatu';
                $saldoQuantidadeTotal = valorItemAta(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, 'vitarpvatu', 'vitarpvori');
                $valorMaximoCarona = valorMaximoCarona(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ, 'vitarpvatu', 'vitarpvori', $ataCorporativa);
            }*/

            if($tipoAta == 'I') {
                if($tipoSarp == 'C') {
                    // Adesão
                    $adesao = getPercentualAdesao(Conexao(), $ataCorporativa);

                    // Quantidade do total dos caronas internos
                    $field_total_interna =  ($tipoControle[0] == 1) ? 'vitescunit' : 'aitescqtso';
                    $qtdOrgaoCaronaInterna = getQtdTotalOrgaoCaronaInterna(Conexao(), null, $item->carpnosequ, $item->citarpsequ, $field_total_interna);            

                    // Quantidade do total dos caronas externos
                    $field_externa =  ($tipoControle[0] == 1) ? 'vcoeitvuti' : 'acoeitqtat';
                    $qtdOrgaoCaronaExterna = getQtdTotalOrgaoCaronaExterna(Conexao(), $item->carpnosequ, $item->citarpsequ, $field_externa);

                    // Quantidade total carona interna por inclusão direta
                    $field_total_direta = ($tipoControle[0] == 1) ? 'vitcrpvuti' : 'aitcrpqtut';
                    $qtdOrgaoCaronaInternaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(Conexao(), $item->carpnosequ, $item->citarpsequ, null, $field_total_direta);            

                    // Valor max carona
                    if($tipoControle[0] == 1) {
                        $valorMaximoCarona = valorMaximoCarona(Conexao(), $item->carpnosequ, $item->citarpsequ, 'vitarpvatu', 'vitarpvori', $ataCorporativa);
                    } else {
                        $valorMaximoCarona = valorMaximoCarona(Conexao(), $item->carpnosequ, $item->citarpsequ, 'AITARPQTAT', 'AITARPQTOR', $ataCorporativa);
                    }

                    $saldoGeralCaronaAta = ($valorMaximoCarona) - ($qtdOrgaoCaronaInterna + $qtdOrgaoCaronaExterna + $qtdOrgaoCaronaInternaIncDir);
                    
                    // Quantidade total para orgão que vai ser carona
                    $field_interna =  ($tipoControle[0] == 1) ? 'vitescunit' : 'aitescqtso';
                    $qtdOrgaoSelecionadoCaronaInterna = getQtdTotalOrgaoCaronaInterna(Conexao(), $_SESSION['centroCustoAnterior'], $item->carpnosequ, $item->citarpsequ, $field_interna);            
                    
                    // aitcrpqtut
                    $field_direta = ($tipoControle[0] == 1) ? 'vitcrpvuti' : 'aitcrpqtut';
                    $qtdOrgaoCaronaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(Conexao(), $item->carpnosequ, $item->citarpsequ, $_SESSION['centroCustoAnterior'], $field_direta);            
                    
                    $qtdLimiteOrgaoCarona = ($qtdItensAta * $adesao) - ($qtdOrgaoSelecionadoCaronaInterna + $qtdOrgaoCaronaIncDir);
                    
                    if($qtdLimiteOrgaoCarona < $saldoGeralCaronaAta) {
                        $saldoGeralCaronaAta = $qtdLimiteOrgaoCarona;
                    }

                } else {
                    $qtdItensAta = ($tipoControle[0] == 1) ? $item->vpiarpvatu : $item->apiarpqtat;
                    $field_1 = ($tipoControle[0] == 1) ? 'vpiarpvuti' : 'apiarpqtut';
                    $field_2 = ($tipoControle[0] == 1) ? 'vitescunit' : 'aitescqtso';
                    $qtdOrgaoParticipanteInterna = getQtdTotalOrgaoParticipanteInterna(Conexao(), $_SESSION['centroCustoAnterior'], $item->carpnosequ, $item->citarpsequ, $field_1, $field_2);
                    $saldoGeralCaronaAta = $qtdItensAta - $qtdOrgaoParticipanteInterna;
                }
            } else if($tipoAta == 'E') {
                $field_externa = ($tipoControle[0] == 1) ? 'vitescunit' : 'aitescqtso';
                $qtdOrgaoCaronaInternaAtaExterna = getTotalQtdCaronaInternaAtaExterna(Conexao(), $_SESSION['centroCustoAnterior'], $item->carpnosequ, $item->citarpsequ, $field_externa);            
                $saldoGeralCaronaAta = $qtdItensAta - $qtdOrgaoCaronaInternaAtaExterna;        
            }
            
            // Verificar quantidade carona para habilitar a seleção do item
            $checkboxItem = $item->aitarporde;
            $checkboxQtd = '---';
            if($saldoGeralCaronaAta > 0) {
                $checkboxItem = '<input type="checkbox" name="item['.$key.']" value="'.$item->citarpsequ.'">' .$item->aitarporde;
                $checkboxQtd = '<input size="10" type="text" data-pos="'.$key.'" class="money verificarQuantidade" name="quantidade['.$key.']" value="0">';
            } else {
                $saldoGeralCaronaAta = 0;
            }
            
            $checkboxValorSlc = '<input size="10" type="hidden" data-pos="'.$key.'" class="money verificarValorSolicitado" name="valor_solicitado['.$key.']" value="0">';
            if($tipoControle[0] != 0) {
                $checkboxValorSlc = '<input size="10" type="text" data-pos="'.$key.'" class="money verificarValorSolicitado" name="valor_solicitado['.$key.']" value="0">';
                $checkboxValorSlc .= '<input size="10" type="hidden" data-pos="'.$key.'" class="money" name="quantidade['.$key.']" value="'.converte_valor_estoques($qtdItensAta).'">';
                
                $checkboxValorSlc2 = '<input size="10" type="text" data-pos="'.$key.'" class="money verificarValorSolicitado" name="valor_solicitado_2['.$key.']" value="0">';
            }

            // Tipo
            $itn = (!empty($item->cmatepsequ)) ? "M" : "S";
            $input_tipo = '<input name="tipo['.$key.']" value="'.$itn.'" type="hidden"/>';
            // Descrição
            $des = (!empty($item->cmatepsequ)) ? $item->ematepdesc : $item->eservpdesc;
            $input_desc = '<input name="descricao['.$key.']" value="'.$des.'" type="hidden"/>';
            // Descrição detalhada
            $desc_det = (!empty($item->cmatepsequ)) ? $item->eitarpdescmat : $item->eitarpdescse;
            $input_desc_det = '<textarea style="display:none" name="descricaoDetalhada['.$key.']">' .$desc_det. '</textarea>';
            // Unidade
            $input_und = '<input name="unidade['.$key.']" value="'.$item->eunidmsigl.'" type="hidden"/>';
            // Código
            $cod = (!empty($item->cmatepsequ)) ? $item->cmatepsequ : $item->cservpsequ;
            $cod_2 = $cod . '#' . $item->citarpsequ;
            $input_cod = '<input name="codigo['.$key.']" value="'.$cod_2.'" type="hidden"/>';
            // Valor unitário
            $valor_unitario = ($item->vitarpvatu > 0) ? $item->vitarpvatu : $item->vitarpvori;
            $input_uni = '<input name="unitario['.$key.']" value="'.$valor_unitario.'" type="hidden"/>';
            // Marca
            $marc = (empty($item->eitarpmarc) || $item->eitarpmarc == 'null') ? '' : $item->eitarpmarc;
            $input_marc = '<input name="marca['.$key.']" value="'.$marc.'" type="hidden"/>';
            // Modelo
            $mod = (empty($item->eitarpmode) || $item->eitarpmode == 'null') ? '' : $item->eitarpmode;
            $input_mod = '<input name="modelo['.$key.']" value="'.$mod.'" type="hidden"/>';
            // Carona
            $input_carona = '<input name="carona['.$key.']" value="'.converte_valor_estoques($saldoGeralCaronaAta).'" type="hidden"/>';
            // Apiar
            $input_apiar = '<input name="apiar['.$key.']" value="'.converte_valor_estoques($apiar).'" type="hidden"/>';
           
            $tpl->CHECKBOX_ITEM        = $checkboxItem;
            $tpl->TIPO_ATA             = (!empty($item->cmatepsequ)) ? "CADUM" : "CADUS";
            $tpl->CODIGO_ATA           = (!empty($item->cmatepsequ)) ? $item->cmatepsequ : $item->cservpsequ;
            $tpl->DESCRICAO_ATA        = (!empty($item->cmatepsequ)) ? $item->ematepdesc : $item->eservpdesc;
            $tpl->DESCRICAO_DET_ATA    = (!empty($item->cmatepsequ)) ? $item->eitarpdescmat : $item->eitarpdescse;
            $tpl->UNIDADE_ATA          = $item->eunidmsigl; 
            $tpl->QUANTIDADE_ITEM_ATA  = converte_valor_estoques($qtdItensAta); 
            $tpl->SALDO_CARONA_ATA     = ($saldoGeralCaronaAta <= 0) ? ' --- ' : converte_valor_estoques($saldoGeralCaronaAta);
            $tpl->DISPLAY_TIPO_0       = $exibirTipo0;
            $tpl->DISPLAY_TIPO_1       = $exibirTipo1;
            $tpl->DISPLAY_TIPO_2       = $exibirTipo2;
            $tpl->INPUT_TIPO           = $input_tipo;
            $tpl->INPUT_DESC           = $input_desc;
            $tpl->INPUT_DESC_DET       = $input_desc_det;
            $tpl->INPUT_UND            = $input_und;
            $tpl->INPUT_COD            = $input_cod;
            $tpl->INPUT_UNI            = $input_uni;
            $tpl->INPUT_MARC           = $input_marc;
            $tpl->INPUT_MOD            = $input_mod;
            $tpl->INPUT_CARONA         = $input_carona;
            $tpl->INPUT_APIAR          = $input_apiar;
            $tpl->COLSPAN              = ($tipoControle[0] == 2) ? 10 : 9;
             
            if($tipoControle[0] != 0) {
                $tpl->CHECKBOX_VALOR_SLCT_2  = $checkboxValorSlc2;
                $tpl->CHECKBOX_VALOR_SLCT    = $checkboxValorSlc;
                $tpl->block("bloco_tipo");
            } else {
                $tpl->CHECKBOX_QTD = $checkboxQtd;
                $tpl->block("bloco_quantidade");
            }
            
            $tpl->block("bloco_lista_ata");
            
        }

        $tpl->JS_1 = ($tipoControle[0] == 1) ? 'true' : 'false';
        $tpl->JS_2 = ($tipoControle[0] == 2) ? 'true' : 'false';
        $tpl->block("bloco_js");
        
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
}

/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new GUI();
$tpl = new TemplatePaginaPadrao("templates/DetalharItensProcessoAtasScc.html", " ");
$acao = filter_input(INPUT_POST, 'incluir', FILTER_SANITIZE_STRING);
switch ($acao) {
    case 'Incluir':
        $itens = array(
            'item'               => $_POST['item'],
            'tipo'               => $_POST['tipo'],
            'descricao'          => $_POST['descricao'],
            'descricaoDetalhada' => $_POST['descricaoDetalhada'],
            'unidade'            => $_POST['unidade'],
            'value'              => $_POST['value'],
            'codigo'             => $_POST['codigo'],
            'marca'              => $_POST['marca'],
            'modelo'             => $_POST['modelo'],
            'quantidade'         => $_POST['quantidade'],
            'unitario'           => $_POST['unitario'],
            'id_ata'             => $_POST['id_ata'],
            'fornecedor'         => $_POST['fornecedor'],
            'vitescunit'         => $_POST['valor_solicitado'],
            'quantidadeSol'      => $_POST['valor_solicitado_2'],
            'apiar'              => $_POST['apiar']
        );
        $app->processIncluir($itens);
        break;
    case 'Principal':
    default:
        $app->proccessPrincipal($tpl);
        break;
}

$tpl->show();