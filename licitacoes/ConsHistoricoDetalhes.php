<?php
// -------------------------------------------------------------------------
// Portal da DGCO
// Programa: ConsHistoricoDetalhes.php
// Autor: Rossana Lira
// Data: 06/05/03
// Objetivo: Programa de Detalhamento (Historico) da Licitação
// OBS.: Tabulação 2 espaços
// -------------------------------------------------------------------------
// Alterado: Rossana
// Data: 24/05/2007 - Liberar Permissão Remunerada de Uso para Tomada de Preços
// -------------------------------------------------------------------------
// Alterado: Rodrigo Melo
// Data: 11/01/2008 - Correção do botão voltar.
// -------------------------------------------------------------------------
// Alterado: Rodrigo Melo
// Data: 21/11/2008 - Correção para permitir baixar os arquivos que estão na ATA DA FASE
// -------------------------------------------------------------------------
// Alterado: Ariston Cordeiro
// Data: 02/03/2011 - Mostrar Documentos e Atas marcados excluídos
// -------------------------------------------------------------------------
// Alterado: Ariston Cordeiro
// Data: 23/03/2011 - não mostrar responsáveis e observações de documentos alterados antes da data em que a melhoria foi colocada
// -------------------------------------------------------------------------
// Alterado: Heraldo Botelho
// Data: 23/04/2013 - O Valor Estimado (na variável=>$ValorEstimado) passa ser
// calculado pela função totalValorEstimado([params])
// -------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 19/06/2014 - CR123143]: REDMINE 19 (P6)
// -------------------------------------------------------------------------
// Alterado: Pitang
// Data: 26/08/2014 - [CR123143]: REDMINE 19 (P6)
// -------------------------------------------------------------------------
// Alterado: Pitang
// Data: 29/08/2014 - [CR123143]: REDMINE 19 (P6)
// -------------------------------------------------------------------------
// Alterado: Pitang
// Data: 17/09/2014 - [CR123143]: REDMINE 19 (P6)
// -------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 03/10/2014 - Correção de exibição de economicidade do processo.
// -------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 09/10/2014 - [CR123143]: REDMINE 19 (P6) - Ajusta economicidade do lote de serviço
// -------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 10/11/2014 - #3 [CR123143]: REDMINE 19 (P6) - Adiciona dados na sessão para realizar download de arquivos
// -------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 14/11/2014 - CR referente a coluna "Valor Estimado" (que foi retirada na tela de companhamento em produção)
// -------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 29/06/2015
// Objetivo: CR87475
// Versão: v1.22.0-2-gff75074
// -------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 27/04/2016
// Objetivo: Requisito 129503 - Histórico de Licitação Intranet
// Versão: v1.37.0
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 29/04/2016
// Objetivo: Requisito 129783 - Correção de exibição das fases de licitação
// @version GIT: v1.38.0
// -----------------------------------------------------------------------------
// Alterado: Lucas Baracho
// Data:     10/07/2018
// Objetivo: Tarefa Redmine 73631
//-----------------------------------------------------------------------------
// Alterado: Pitang Agile TI - Caio Coutinho
// Data:     11/07/2018
// Objetivo: Tarefa Redmine #106548
// -------------------------------------------------------------------------------
// Alterado: Ernesto Ferreira
// Data:     29/08/2018
// Objetivo: Tarefa Redmine 200463
// -------------------------------------------------------------------------------
// Alterado: Pitang Agile TI - Ernesto Ferreira
// Data:     19/09/2018
// Objetivo: [LICITAÇÕES - TRAMITAÇÃO] Entrada - Erros (Item 4 da lista da CR)
// -----------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/12/2018
# Objetivo: Tarefa Redmine 95906
#--------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     15/01/2019
# Objetivo: Tarefa Redmine 209297
#--------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     13/03/2019
# Objetivo: Tarefa Redmine 122413
#--------------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     08/04/2019
# Objetivo: Tarefa Redmine 214320
#--------------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 15/05/2023
# Objetivo: Cr 282613
# -----------------------------------------------------------------------------

session_start();
$pesquisa = $_SESSION['origemPesquisa'];

// Acesso ao arquivo de funções #
require_once '../funcoes.php';
require_once 'funcoesLicitacoes.php';
require_once '../compras/funcoesCompras.php';

$programaSelecao = "ConsAcompSolicitacaoCompra.php";

// Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso('/licitacoes/ConsHistoricoDownloadDoc.php');
AddMenuAcesso('/licitacoes/ConsHistoricoDownloadAtas.php');
AddMenuAcesso('/licitacoes/ConsHistoricoDetalhesDocumentosRelacionados.php');
AddMenuAcesso( '/compras/'.$programaSelecao );

class CR87475
{

    /**
     * Calcular a economicidade.
     *
     *
     * @param string $valorTotalEstimado
     * @param string $valorTotalHomologado
     */
    public function calcularEconomicidade($valorTotalEstimado, $valorTotalHomologado, $tipolicitacao = 'P')
    {
        if ((float) $valorTotalEstimado == 0 || (float) $valorTotalHomologado == 0) {
            return 0;
        }

        // Considera apenas duas casas décimais na hora do cálculo
        $valorTotalEstimado = $this->trataValor($valorTotalEstimado);
        $valorTotalHomologado = $this->trataValor($valorTotalHomologado);

        // Fórmula de cálculo da economicidade
        //Menor Preço -> Quanto menor for o valor homologado, quando comparado com o estimado, maior será a economicidade;
        //Maior Oferta -> Quanto maior for o valor homologado, quando comparado com o estimado, maior será a economicidade;
        if($tipolicitacao != 'O'){
            $diferencaEstimadoHomologado = ($valorTotalEstimado - $valorTotalHomologado);
            $economicidade = ($diferencaEstimadoHomologado * 100) / $valorTotalEstimado;
        }else{
            $diferencaEstimadoHomologado = ($valorTotalHomologado - $valorTotalEstimado);
            $economicidade = ($diferencaEstimadoHomologado * 100) / $valorTotalEstimado;

        }


        return $economicidade;
    }

    /**
     * [trataValor description]
     *
     * @param [type] $valor
     *            [description]
     * @return [type] [description]
     */
    public function trataValor($valor)
    {
        return round((float) $valor, 2);
    }



    public function consultarDCentroDeCustoUsuario($corglicodi)
    {
        $db = Conexao();
        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
             WHERE 1=1
        ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = ".$corglicodi;
        }
        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $dados = array();
            while ($Linha = $result->fetchRow()) {

                $dados[] = $Linha;


            }
        }
        //$db->disconnect();
        return $dados;
    }

}

$CR87475 = new CR87475();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
} else {
    $Selecao = $_GET['Selecao'];
    $GrupoCodigo = $_GET['GrupoCodigoDet'];
    $LicitacaoProcesso = $_GET['LicitacaoProcessoDet'];
    $LicitacaoAno = $_GET['LicitacaoAnoDet'];
    $ComissaoCodigo = $_GET['ComissaoCodigoDet'];
    $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigoDet'];

    $GrupoCodigoResultado = $_GET['GrupoCodigo'];
    $LicitacaoProcessoResultado = $_GET['LicitacaoProcesso'];
    $LicitacaoAnoResultado = $_GET['LicitacaoAno'];
    $ComissaoCodigoResultado = $_GET['ComissaoCodigo'];
    $OrgaoLicitanteCodigoResultado = $_GET['OrgaoLicitanteCodigo'];
    $ObjetoResultado = $_GET['Objeto'];

    $TipoItemLicitacao = $_GET['TipoItemLicitacao'];
    $Item = $_GET['Item'];

    $_SESSION['GrupoCodigoDet'] = $_GET['GrupoCodigoDet'];
    $_SESSION['ProcessoDet'] = $_GET['LicitacaoProcessoDet'];
    $_SESSION['ProcessoAnoDet'] = $_GET['LicitacaoAnoDet'];
    $_SESSION['ComissaoCodigoDet'] = $_GET['ComissaoCodigoDet'];
    $_SESSION['OrgaoLicitanteCodigoDet'] = $_GET['OrgaoLicitanteCodigoDet'];
}

function getComissaoLicitacao($ccomlicodi) {
    $db = Conexao();
    $sql = "  SELECT ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO ";
    $sql .= " WHERE  CCOMLICODI =  " . $ccomlicodi;
    $sql .= " ORDER BY ECOMLIDESC ASC ";
    $res  = $db->query($sql);
    $res->fetchInto($res, DB_FETCHMODE_OBJECT);

    if (!PEAR::isError($res)) {
        return $res;
    }
}

resetArquivoAcesso();

$_SESSION['PermitirAuditoria'] = 'N'; // Variável de sessão que permite fazer download de arquivos excluídos e armazenados.
                                      // Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Selecao == 1) {
    $Titulo = ' Anos Anteriores';
} elseif ($Selecao == 2) {
    $Titulo = ' Ano Atual';
}

$comissaoApenasHomologada = 46; // Exibir a coluna valor estimado se diferente de 46 ou se igual a 46 e licitação homologada
$fasesComResultado = array(
    13,
    15
); // Fases que podem ter resultado

// Resgata as informções da licitação #
$db = Conexao();
$sql = 'SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.CLICPOCODL, ';
$sql .= '       D.ALICPOANOL, D.XLICPOOBJE, E.EORGLIDESC, D.TLICPODHAB, ';
$sql .= '       D.VLICPOVALE, D.VLICPOVALH,	D.FLICPOREGP, B.CMODLICODI, ';
$sql .= '       D.VLICPOTGES, D.FLICPODEMC, D.flicporesu, D.FLICPOTIPO, ';
$sql .= '       D.FLICPOVFOR, D.CLICPOPRO2, D.ALICPOANO2, D.CCOMLICOD1, D.flicpolegi';
$sql .= '  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, ';
$sql .= '       SFPC.TBCOMISSAOLICITACAO C, SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ';
$sql .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $GrupoCodigo ";
$sql .= '   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ';
$sql .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.CLICPOPROC = $LicitacaoProcesso ";
$sql .= "   AND D.ALICPOANOP = $LicitacaoAno AND E.CORGLICODI = D.CORGLICODI ";
$sql .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        $GrupoDesc = $Linha[0];
        $ModalidadeDesc = $Linha[1];
        $ComissaoDesc = $Linha[2];
        $NLicitacao = substr($Linha[3] + 10000, 1);
        $AnoLicitacao = $Linha[4];
        $ObjetoLicitacao = $Linha[5];
        $OrgaoLicitacao = $Linha[6];
        $LicitacaoDtAbertura = substr($Linha[7], 8, 2) . '/' . substr($Linha[7], 5, 2) . '/' . substr($Linha[7], 0, 4);
        $LicitacaoHoraAbertura = substr($Linha[7], 11, 5);
        $fracassado = false;
        if(empty($Linha[20]) || $Linha[20]==''){
            $legislacao = '8666';
        }else{
            $legislacao = $Linha[20];
        }
        if(!empty($Linha[17]) && !empty($Linha[18]) && !empty($Linha[19])) {
            $fracassado = true;
            $comissaoFracassado = getComissaoLicitacao($Linha[19]);
            $processoFracassado = substr($Linha[17] + 10000, 1) . '/' . $Linha[18];
        }

        $ValorEstimado = "0,00";
        if ($Linha[8] != "") {
            $ValorEstimado = ($Linha[8]);
        }

        $ValorHomologado = $Linha[9];

        $flagResultadoLicitacao = $Linha[14];

        if ($Linha[10] == 'S') {
            $RegistroPreco = 'SIM';
        } else {
            $RegistroPreco = 'NÃO';
        }

        $ModalidadeCodigo = $Linha[11];
        $TotalGeralEstimado = $Linha[12];

        if ($Linha[13] == 'S') {
            $validacaoFornecedor = 'SIM';
        } else {
            $validacaoFornecedor = 'NÃO';
        }

        $licitacaoTipo = $Linha[14];
        $tratamentoDiferenciado = $Linha[16];
    }
}

// Obter Valor Estimado
$sql = "
    select
           lp.vlicpovale,
           lp.vlicpovalh,
           lp.vlicpotges
      from sfpc.tbsolicitacaolicitacaoportal slp
           inner join sfpc.tblicitacaoportal lp
                   on lp.clicpoproc = slp.clicpoproc
                   and lp.alicpoanop = slp.alicpoanop
                   and lp.cgrempcodi = slp.cgrempcodi
                   and lp.ccomlicodi = slp.ccomlicodi
                   and lp.corglicodi = slp.corglicodi
     where slp.alicpoanop = $LicitacaoAno
           and slp.clicpoproc = $LicitacaoProcesso
           and slp.ccomlicodi = $ComissaoCodigo
           and slp.corglicodi = $OrgaoLicitanteCodigo
           and slp.cgrempcodi =  $GrupoCodigo";
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        // // Calcular valor estimado
        $ValorEstimado = totalValorEstimado($db, $LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
        if (count($Linha) > 0) {
            $ValorEstimado = ($ValorEstimado);
        }
    }
}

$descricaoTratamentoDiferenciado = strtoupper(getDescricaoTratamentoDiferenciado($tratamentoDiferenciado));

?>
<html>
    <?php
    // Carrega o layout padrão #
    layout();
    ?>
    <script type="text/javascript">
        <!--
<?php MenuAcesso(); ?>
        //-->

    <?php
    $urlTramitacao = '';

    if(isset($pesquisa) && $_GET['origemTramitacao'] == 2){
        //Relatorio de Monitoramento
        unset($_SESSION['origemPesquisa']);

        $Url = "RelTramitacaoMonitoramento.php?";
        $Url .= "tramitacaoNumeroProtocolo=".$pesquisa['tramitacaoNumeroProtocolo'];
        $Url .= "&tramitacaoAnoProtocolo=".$pesquisa['tramitacaoAnoProtocolo'];
        $Url .= "&tramitacaoGrupo=".$pesquisa['tramitacaoGrupo'];
        $Url .= "&tramitacaoOrgao=".$pesquisa['tramitacaoOrgao'];
        $Url .= "&tramitacaoObjeto=".$pesquisa['tramitacaoObjeto'];
        $Url .= "&tramitacaoNumeroCI=".$pesquisa['tramitacaoNumeroCI'];
        $Url .= "&tramitacaoNumeroOficio=".$pesquisa['tramitacaoNumeroOficio'];
        $Url .= "&tramitacaoNumeroScc=".$pesquisa['tramitacaoNumeroScc'];
        $Url .= "&tramitacaoComissaoLicitacao=".$pesquisa['tramitacaoComissaoLicitacao'];
        $Url .= "&tramitacaoAcao=".$pesquisa['tramitacaoAcao'];
        $Url .= "&tramitacaoAgenteDestino=".$pesquisa['tramitacaoAgenteDestino'];
        $Url .= "&tramitacaoProcessoNumero=".$pesquisa['tramitacaoProcessoNumero'];
        $Url .= "&tramitacaoProcessoAno=".$pesquisa['tramitacaoProcessoAno'];
        $Url .= "&tramitacaoDataEntradaInicio=".$pesquisa['tramitacaoDataEntradaInicio'];
        $Url .= "&tramitacaoDataEntradaFim=".$pesquisa['tramitacaoDataEntradaFim'];
        $Url .= "&tramitacaoSituacao=".$pesquisa['tramitacaoSituacao'];
        $Url .= "&tramitacaoOrdem=".$pesquisa['tramitacaoOrdem'];
        $Url .= "&tramitacaoAtraso=".$pesquisa['tramitacaoAtraso'];

        $Url .= "&Botao=Pesquisar";//&Critica=1";
        $Url .= "&t=".mktime();
        
        $urlTramitacao  = $Url;
    }


    if(isset($pesquisa) && $_GET['origemTramitacao'] == 1){

        unset($_SESSION['origemPesquisa']);

        $Url = "CadTramitacao".$pesquisa['rotina'].".php?";
        $Url .= "numProtocolo=".$pesquisa['numProtocolo'];
        $Url .= "&anoProtocolo=".$pesquisa['anoProtocolo'];
        $Url .= "&orgao=".$pesquisa['orgao'];
        $Url .= "&objeto=".$pesquisa['objeto'];
        $Url .= "&numeroci=".$pesquisa['numeroci'];
        $Url .= "&numeroOficio=".$pesquisa['numeroOficio'];
        $Url .= "&numeroScc=".$pesquisa['numeroScc'];
        $Url .= "&proLicitatorio=".$pesquisa['proLicitatorio'];
        $Url .= "&acao=".$pesquisa['acao'];
        $Url .= "&origem=".$pesquisa['origem'];
        $Url .= "&Data".$pesquisa['rotina']."Ini=".$pesquisa['Data'.$pesquisa['rotina'].'Ini'];
        $Url .= "&Data".$pesquisa['rotina']."Fim=".$pesquisa['Data'.$pesquisa['rotina'].'Fim'];
        $Url .= "&botao=Pesquisar&Critica=1";
        $Url .= "&t=".mktime();
        
        $urlTramitacao  = $Url;
        }
    ?>
        function retornarTramitacao(){
            



            window.location = "<?php echo $urlTramitacao ?>";


        }

    </script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
 <script language="JavaScript" src="../menu.js"></script>
 <script language="JavaScript" src="./ConsHistoricoDetalhes.js"></script>
 <script language="JavaScript">Init();</script>
 <br>
 <br>
 <br>
 <br>
 <br>
 <table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
   <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
   <td align="left" class="textonormal" colspan="2"><font
    class="titulo2"
   >|</font> <a href="../index.php"><font color="#000000">Página
      Principal</font></a> > Licitações > Histórico</td>
  </tr>
  <!-- Fim do Caminho-->
  <!-- Erro -->
        <?php

        if ($Mens == 1) {
            ?>
            <tr>
   <td width="100"></td>
   <td align="left" colspan="2"><?php
            if ($Mens == 1) {
                ExibeMens($Mensagem, $Tipo, 1);
            }
            ?></td>
  </tr>
    <?php
        }
        ?>
    <!-- Fim do Erro -->
  <!-- Corpo -->
  <tr>
   <td width="100"></td>
   <td class="textonormal">
    <table border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
     <tr>
      <td class="textonormal">
       <table border="1" cellpadding="3" cellspacing="0"
        bordercolor="#75ADE6" summary="" class="textonormal"
       >
        <tr>
         <td align="center" bgcolor="#75ADE6" valign="middle"
          colspan="4" class="titulo3"
         >HISTÓRICO DE LICITAÇÕES - DETALHAMENTO</td>
        </tr>
        <tr>
         <td class="textonormal" colspan="4">
          <p align="justify">Para visualizar os documentos e Atas da
           Licitação, clique no item desejado. Para visualizar todas as
           Licitações Pesquisadas, clique no botão "Voltar".</p>
         </td>
        </tr>
        <tr>
         <td class="textonegrito" bgcolor="#DCEDF7" colspan="4">
                        <?php echo "$GrupoDesc <br><br> $ModalidadeDesc <br><br> $ComissaoDesc<br>"; ?>
                    </td>
        </tr>
                    <?php

                    $sqlSolicitacoesC = "SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi
            FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $LicitacaoProcesso AND SOL.ALICPOANOP = $LicitacaoAno
           AND SOL.CCOMLICODI = $ComissaoCodigo AND SOL.cgrempcodi =" . $GrupoCodigo;

                    $resultSolic = $db->query($sqlSolicitacoesC);
                    $ultimaFase = ultimaFase($LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);
                    if (PEAR::isError($resultSolic)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSolicitacoesC");
                    }
                    $Solicitacao = '';
                    $int = 0;
                    while ($Linha = $resultSolic->fetchRow()) {
                        if ($int > 0) {
                            $Solicitacao .= ' - ';
                        }
                        $Solicitacao .= 'SCC ' . getNumeroSolicitacaoCompra($db, $Linha[0]);
                        $SeqSolicitacao = $Linha[0];
                        ++ $int;
                    }

                    $LicitacaoProcesso = substr($LicitacaoProcesso + 10000, 1);
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">PROCESSO</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$LicitacaoProcesso/$LicitacaoAno</td>\n";
                    echo "			</tr>\n";
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">LEGISLAÇÃO DE COMPRA</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$legislacao</td>\n";
                    echo "			</tr>\n";
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">LICITAÇÃO</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$NLicitacao/$AnoLicitacao</td>\n";
                    echo "			</tr>\n";

                    if($fracassado) {
                        echo "			<tr>\n";
                        echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">PROCESSO LICITATÓRIO FRACASSADO</td>\n";
                        echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$processoFracassado - $comissaoFracassado->ecomlidesc</td>\n";
                        echo "			</tr>\n";
                    }

                    echo "			<tr>\n";
                    echo '				<td valign="top" bgcolor="#F7F7F7" class="textonegrito" colspan="2">REGISTRO DE PREÇO';

                    // Caso a modalidade seja concorrência ou tomada de preços apareça nome Permissão Remunerada de Uso
                    if ($ModalidadeCodigo == 3 or $ModalidadeCodigo == 2) {
                        echo '/PERMISSÃO REMUNERADA DE USO';
                    }                    

                    echo "				</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$RegistroPreco</td>\n";
                    echo "			</tr>\n";
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">OBJETO</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ObjetoLicitacao</td>\n";
                    echo "			</tr>\n";
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">DATA/HORA DE ABERTURA</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$LicitacaoDtAbertura $LicitacaoHoraAbertura h</b></td>\n";
                    echo "			</tr>\n";
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">ÓRGÃO LICITANTE</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$OrgaoLicitacao</td>\n";
                    echo "			</tr>\n";
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">TRATAMENTO DIFERENCIADO EPP/ME/MEI</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$descricaoTratamentoDiferenciado</td>\n";
                    echo "			</tr>\n";

                    $Url = "../compras/".$programaSelecao."?SeqSolicitacao=".$SeqSolicitacao."&programa=window";
                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">SOLICITAÇÃO DE COMPRA</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\"><a href=\"javascript:AbreJanela('$Url');\">$Solicitacao</a></td>\n";
                    echo "			</tr>\n";

                    echo "			<tr>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">NECESSIDADE DE APRESENTAÇÃO DE DEMONSTRAÇÕES CONTÁBEIS</td>\n";
                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$validacaoFornecedor</td>\n";
                    echo "			</tr>\n";

                    if ($ComissaoCodigo != $comissaoApenasHomologada || ($ComissaoCodigo == $comissaoApenasHomologada && $flagResultadoLicitacao && in_array($ultimaFase, $fasesComResultado))) {
                        echo "			<tr>\n";
                        echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR ESTIMADO</td>\n";
                        echo '				<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="2">' . converte_valor($ValorEstimado) . "</td>\n";
                        echo "			</tr>\n";
                    }

                    if ($flagResultadoLicitacao and in_array($ultimaFase, $fasesComResultado)) {
                        if ($TotalGeralEstimado != '') {
                            echo "			<tr>\n";
                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">TOTAL GERAL ESTIMADO<br>(Itens que Lograram Êxito)</td>\n";
                            echo '				<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="2">' . converte_valor($TotalGeralEstimado) . "</td>\n";
                            echo "			</tr>\n";
                        }

                        if ($ValorHomologado != '') {
                            echo "			<tr>\n";
                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR HOMOLOGADO<br>(Itens que Lograram Êxito)</td>\n";
                            echo '				<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="2"> ' . converte_valor($ValorHomologado) . "</td>\n";
                            echo "			</tr>\n";
                        }
                    }

                    if (in_array($ultimaFase, $fasesComResultado)) {
                        $economicidade = $CR87475->calcularEconomicidade($TotalGeralEstimado, $ValorHomologado, $licitacaoTipo);
                        echo "			<tr>\n";
                        echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">ECONOMICIDADE DO PROCESSO</td>\n";
                        echo '				<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="2">' . number_format($economicidade, 2, ',', '.') . " %</td>\n";
                        echo "			</tr>\n";
                    }

                    // Pega os Dados dos do Bloqueio #
                    $sql = 'SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU, ';
                    $sql .= '       CLICBLFUNC, CLICBLSUBF, CLICBLPROG, CLICBLTIPA, ';
                    $sql .= '       ALICBLORDT, CLICBLELE1, CLICBLELE2, CLICBLELE3, ';
                    $sql .= '       CLICBLELE4, CLICBLFONT ';
                    $sql .= '  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT';
                    $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
                    $sql .= "   AND CCOMLICODI = $ComissaoCodigo ";
                    $sql .= "   AND CGREMPCODI = $GrupoCodigo";
                    $sql .= ' ORDER BY ALICBLSEQU';
                    $result = $db->query($sql);

                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $Rows = $result->numRows();
                        for ($i = 0; $i < $Rows; ++ $i) {
                            $Linha = $result->fetchRow();
                            $ExercicioBloq[$i] = $Linha[0];
                            $Orgao[$i] = $Linha[1];
                            $Unidade[$i] = $Linha[2];
                            $Bloqueios[$i] = $Linha[3];
                            $Funcao[$i] = $Linha[4];
                            $Subfuncao[$i] = $Linha[5];
                            $Programa[$i] = $Linha[6];
                            $TipoProjAtiv[$i] = $Linha[7];
                            $ProjAtividade[$i] = $Linha[8];
                            $Elemento1[$i] = $Linha[9];
                            $Elemento2[$i] = $Linha[10];
                            $Elemento3[$i] = $Linha[11];
                            $Elemento4[$i] = $Linha[12];
                            $Fonte[$i] = $Linha[13];
                            $Dotacao[$i] = NumeroDotacao($Funcao[$i], $Subfuncao[$i], $Programa[$i], $Orgao[$i], $Unidade[$i], $TipoProjAtiv[$i], $ProjAtividade[$i], $Elemento1[$i], $Elemento2[$i], $Elemento3[$i], $Elemento4[$i], $Fonte[$i]);
                        }
                    }

                    // [Para listar os bloqueios da licitação]
                    $dbOracle = ConexaoOracle();
                    // Pega os Dados dos do Bloqueio de uma licitação com SCC #
                    $sql = "
                        select Distinct AITLBLNBLOQ, AITLBLANOB
                        from
                        sfpc.tbitemlicitacaobloqueio
                        WHERE
                        CLICPOPROC = $LicitacaoProcesso
                        AND ALICPOANOP = $LicitacaoAno
                        AND CCOMLICODI = $ComissaoCodigo
                        AND CGREMPCODI = $GrupoCodigo
                        ";
                    $result = executarSQL($db, $sql);
                    $i = 0;
                    while ($bloqueioChave = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                        $bloqueioAno = $bloqueioChave->aitlblanob; // AITLBLANOB;
                        $bloqueioSequencial = $bloqueioChave->aitlblnbloq; // AITLBLNBLOQ;
                        $bloqueioArray = getDadosBloqueioFromChave($dbOracle, $bloqueioAno, $bloqueioSequencial);

                        $ExercicioBloq[$i] = $bloqueioArray['ano'];
                        $Orgao[$i] = $bloqueioArray['orgao'];
                        $Unidade[$i] = $bloqueioArray['unidade'];
                        $Bloqueios[$i] = $bloqueioArray['sequencial'];
                        $Funcao[$i] = $bloqueioArray['funcao'];
                        $Subfuncao[$i] = $bloqueioArray['subfuncao'];
                        $Programa[$i] = $bloqueioArray['programa'];
                        $TipoProjAtiv[$i] = $bloqueioArray['tipoProjetoAtividade'];
                        $ProjAtividade[$i] = $bloqueioArray['projetoAtividade'];
                        $Elemento1[$i] = $bloqueioArray['elemento1'];
                        $Elemento2[$i] = $bloqueioArray['elemento2'];
                        $Elemento3[$i] = $bloqueioArray['elemento3'];
                        $Elemento4[$i] = $bloqueioArray['elemento4'];
                        $Fonte[$i] = $bloqueioArray['fonte'];
                        $Dotacao[$i] = NumeroDotacao($Funcao[$i], $Subfuncao[$i], $Programa[$i], $Orgao[$i], $Unidade[$i], $TipoProjAtiv[$i], $ProjAtividade[$i], $Elemento1[$i], $Elemento2[$i], $Elemento3[$i], $Elemento4[$i], $Fonte[$i]);
                        ++ $i;
                    }

                    // Pega os Dados de dotação de uma licitação com SCC #
                    $sql = "
                        select distinct
                        aitldounidoexer, citldounidoorga, citldounidocodi, citldotipa, aitldoordt,
                        citldoele1, citldoele2, citldoele3, citldoele4, citldofont
                        from
                        sfpc.tbitemlicitacaodotacao
                        WHERE
                        CLICPOPROC = $LicitacaoProcesso
                        AND ALICPOANOP = $LicitacaoAno
                        AND CCOMLICODI = $ComissaoCodigo
                        AND CGREMPCODI = $GrupoCodigo
                        ";
                    $result = executarSQL($db, $sql);
                    $i = 0;
                    while ($bloqueioChave = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                        $dotacaoAno = $bloqueioChave->aitldounidoexer;
                        $dotacaoOrgao = $bloqueioChave->citldounidoorga;
                        $dotacaoUnidade = $bloqueioChave->citldounidocodi;
                        $dotacaoTipoProjeto = $bloqueioChave->citldotipa;
                        $dotacaoProjeto = $bloqueioChave->aitldoordt;
                        $dotacaoE1 = $bloqueioChave->citldoele1;
                        $dotacaoE2 = $bloqueioChave->citldoele2;
                        $dotacaoE3 = $bloqueioChave->citldoele3;
                        $dotacaoE4 = $bloqueioChave->citldoele4;
                        $dotacaoFonte = $bloqueioChave->citldofont;

                        $bloqueioArray = getDadosDotacaoOrcamentariaFromChave($dbOracle, $dotacaoAno, $dotacaoOrgao, $dotacaoUnidade, $dotacaoTipoProjeto, $dotacaoProjeto, $dotacaoE1, $dotacaoE2, $dotacaoE3, $dotacaoE4, $dotacaoFonte);
                        ($dbOracle == false)? $oracleOff = true : $oracleOff = false; 
                        $ExercicioBloq[$i] = $dotacaoAno;
                        $Orgao[$i] = $dotacaoOrgao;
                        $Unidade[$i] = $dotacaoUnidade;
                        $Bloqueios[$i] = null;
                        $Funcao[$i] = null;
                        $Subfuncao[$i] = null;
                        $Programa[$i] = null;
                        $TipoProjAtiv[$i] = $dotacaoTipoProjeto;
                        $ProjAtividade[$i] = $dotacaoProjeto;
                        $Elemento1[$i] = $dotacaoE1;
                        $Elemento2[$i] = $dotacaoE2;
                        $Elemento3[$i] = $dotacaoE3;
                        $Elemento4[$i] = $dotacaoE4;
                        $Fonte[$i] = $dotacaoFonte;
                        $Dotacao[$i] = $bloqueioArray['dotacao'];
                        ++ $i;
                    }
                    // [/Para listar os bloqueios da licitação]

                    echo "<tr>\n";
                    echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">BLOQUEIOS</td>\n";
                    echo "</tr>\n";
                    if (count($Bloqueios) != 0) {
                        echo "			<tr>\n";
                        echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">EXERCÍCIO</td>\n";
                        echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">NÚMERO</td>\n";
                        echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">UNIDADE ORÇAMENTÁRIA</td>\n";
                        echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">DOTAÇÃO</td>\n";
                        echo "			</tr>\n";
                        for ($i = 0; $i < count($Bloqueios); ++ $i) {
                            // [Verifica para tratar como dotação]
                            $isDotacao = false;
                            if (is_null($Bloqueios[$i])) {
                                $isDotacao = true;
                            }
                            // [/Verifica para tratar como dotação]

                            echo "			<tr>\n";
                            echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">$ExercicioBloq[$i]</td>\n";
                            echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";

                            // [Verificação se é dotação]
                            if ($isDotacao) {
                                echo ' (dotação) ';
                            } else {
                                echo '					' . $Orgao[$i] . '.' . sprintf('%02d', $Unidade[$i]) . '.1.' . $Bloqueios[$i] . "\n";
                                echo "					<input type=\"hidden\" name=\"Bloqueios[$i]\" value=\"$Bloqueios[$i]\">\n";
                            }
                            // [/Verificação se é dotação]

                            echo "				</td>\n";
                            echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";

                            // Busca a descrição da Unidade Orçamentaria #
                            if ($_SERVER['SERVER_NAME'] != 'varzea.recife' and $_SERVER['SERVER_NAME'] != 'www.recife.pe.gov.br') {
                                if (empty($ExercicioBloq[$i])) {
                                    $ExercicioBloq[$i] = '9999/99/99';
                                }
                                if (empty($Orgao[$i])) {
                                    $Orgao[$i] = '9999';
                                }
                                if (empty($Unidade[$i])) {
                                    $Unidade[$i] = '9999';
                                }
                            }

                            $sql = 'SELECT EUNIDODESC FROM SFPC.TBUNIDADEORCAMENTPORTAL ';
                            $sql .= " WHERE TUNIDOEXER = $ExercicioBloq[$i] AND CUNIDOORGA = $Orgao[$i] ";
                            $sql .= "   AND CUNIDOCODI = $Unidade[$i]";
                            $result = $db->query($sql);
                            if (PEAR::isError($result)) {
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                            } else {
                                $Linha = $result->fetchRow();
                                $UnidadeOrcament[$i] = $Linha[0];
                            }
                            echo "					$UnidadeOrcament[$i]\n";
                            echo "				</td>\n";
                            echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";
                            echo "					$Dotacao[$i]\n";
                            echo "				</td>\n";
                            echo "			</tr>\n";
                        }
                    } else {
                        if($oracleOff == true){

                            echo "<tr>\n";
                            echo "	<td class=\"textonegrito\" colspan=\"4\">Banco Oracle Offline.</td>\n";
                            echo "</tr>\n";

                        }else{
                            echo "<tr>\n";
                            echo "	<td class=\"textonegrito\" colspan=\"4\">Nenhum Bloqueio Informado.</td>\n";
                            echo "</tr>\n";
                        }
                    }

                    // --------------------------------------------
                    // Verificar se Licitação tem resultado
                    // ---------------------------------------------
                    $sql = ' select flicporesu as resultado ';
                    $sql .= ' from sfpc.tblicitacaoportal ';
                    $sql .= ' where ';
                    $sql .= " clicpoproc = $LicitacaoProcesso";
                    $sql .= ' and alicpoanop = ' . $LicitacaoAno;
                    $sql .= ' and cgrempcodi = ' . $GrupoCodigo;
                    $sql .= ' and ccomlicodi = ' . $ComissaoCodigo;
                    $sql .= ' and corglicodi = ' . $OrgaoLicitanteCodigo;

                    $result = executarTransacao($db, $sql);
                    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

                    $licitacaoComResultado = false;
                    if ($row->resultado == 'S') {
                        $licitacaoComResultado = true;
                    }

                    // --------------------------------------------
                    // Verificar ultim afase da licitação
                    // ---------------------------------------------
                    $ultimaFase = ultimaFase($LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);
                    $arraySituacoesConcluidas = getIdFasesConcluidas($db);

                    // --------------------------------------------------------
                    // Inserido por Heraldo
                    // para exibir itens de materiais e de serviços
                    // ---------------------------------------------------------
                    // --------------------------------------------------------
                    // SQL para capturar os itens de material da licitação
                    // ---------------------------------------------------------
                    $sqlMaterial = <<<SQLMAT
SELECT
    a.aitelporde ,
    b.ematepdesc ,
    a.cmatepsequ ,
    c.eunidmdesc ,
    a.aitelpqtso ,
    a.citelpnuml ,
    d.aforcrsequ ,
    d.nforcrrazs ,
    d.nforcrfant ,
    d.aforcrccgc ,
    a.eitelpdescmat ,
    a.eitelpmarc ,
    a.eitelpmode ,
    a.vitelpunit ,
    a.vitelpvlog
FROM
    sfpc.tbitemlicitacaoportal a LEFT JOIN sfpc.tbfornecedorcredenciado d
        ON a.aforcrsequ = d.aforcrsequ ,
    sfpc.tbmaterialportal b ,
    sfpc.tbunidadedemedida c
WHERE
    a.cmatepsequ = b.cmatepsequ
    AND b.cunidmcodi = c.cunidmcodi
    AND a.clicpoproc = $LicitacaoProcesso
    AND a.alicpoanop = $LicitacaoAno
    AND a.cgrempcodi = $GrupoCodigo
    AND a.ccomlicodi = $ComissaoCodigo
    AND a.corglicodi = $OrgaoLicitanteCodigo
ORDER BY
    6 ,
    1
SQLMAT;

                    $resILTmp = $db->query($sqlMaterial); // echo $sqlMaterial; die;
                    $result = $db->query($sqlMaterial);

                    $Rows = $result->numRows();

                    // ------------------------------------------------------------
                    // - Se encontrar pelo menos uma linha exibir grade com Itens
                    // ------------------------------------------------------------
                    if ($Rows > 0) {
                        echo '<tr  class="textonegrito" bgcolor="#75ADE6"   > ';
                        echo '<td colspan=5 align="center"   valign="middle" >ITENS DE MATERIAIS DA LICITAÇÃO</td>';
                        echo '</tr>';

                        echo '<tr>';
                        echo '<td colspan=5>';

                        echo '<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;"  >';
                        echo '<tr class="textonegrito" bgcolor="#DCEDF7" >';
                        echo '</tr>';

                        $numLoteMatAntes = '999';

                        $exibeTd = false;

                        while ($arrI = $resILTmp->fetchRow()) {
                            if (! empty($arrI[10])) {
                                $exibeTd = true;
                                break;
                            }
                        }

                        $html = '';
                        $acumEconomicidade = 0;
                        $cont = 0;
                        $arrLoteItens = array();
                        while ($Linha = $result->fetchRow()) {
                            $intColSpan = 10;
                            $ordMaterial = $Linha[0];
                            $descMaterial = $Linha[1];
                            $seqMaterial = $Linha[2];
                            $unidMaterial = $Linha[3];
                            $qtdMaterial = $Linha[4];
                            $numLoteMat = $Linha[5];
                            $codForCredMat = $Linha[6];
                            $razaoSocForMat = $Linha[7];
                            $nomeFantForMat = $Linha[8];
                            $cgcForCredMat = $Linha[9];
                            $descDetalhadaMaterial = $Linha[10];

                            if (! empty($descDetalhadaMaterial)) {
                                ++ $intColSpan;
                            }

                            $marcaMaterial = $Linha[11];

                            if ($marcaMaterial == '') {
                                $marcaMaterial = '<center>-</center>';
                            }

                            $modeloMaterial = $Linha[12];

                            if ($modeloMaterial == '') {
                                $modeloMaterial = '<center>-</center>';
                            }

                            $valorEstimadoMaterial = $Linha[13];
                            $valorHomologadoMaterial = $Linha[14];

                            if (! empty($valorHomologadoMaterial)) {
                                ++ $intColSpan;
                            }

                            // [CUSTOMIZAÇÃO] - Ajusta economicidade do material
                            $valorEstimadoMaterialTotal = ((float) $qtdMaterial) * $CR87475->trataValor($valorEstimadoMaterial);
                            $valorHomologadoMaterialTotal = ((float) $qtdMaterial) * $CR87475->trataValor($valorHomologadoMaterial);

                            $arrLoteItens[$numLoteMat]['VEMT'] += $valorEstimadoMaterialTotal;
                            $arrLoteItens[$numLoteMat]['VHMT'] += $valorHomologadoMaterialTotal;
                            // [/CUSTOMIZAÇÃO]

                            if ($numLoteMat != $numLoteMatAntes) {
                                $numLoteMatAntes = $numLoteMat;

                                // if ($licitacaoComResultado and 13 == $ultimaFase and ! empty($razaoSocForMat)) {
                                if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and ! empty($razaoSocForMat)) {
                                    $soma = getTotalValorLogrado($db, $LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteMat);
                                    $html .= '<tr class="textonegrito" bgcolor="#75ADE6">';
                                    $html .= '<td valign=top colspan=' . $intColSpan . '> LOTE ' . ($numLoteMat) . ' FORNECEDOR VENCEDOR : ' . FormataCpfCnpj($cgcForCredMat) . ' - ' . ($razaoSocForMat) . ' - ' . 'R$ ' . (number_format((float) $soma, 2, ',', '.')) . "  {ECONOMICIDADE$numLoteMat}</td>";
                                    $html .= '</tr>';
                                } else {
                                    $html .= '<tr class="textonegrito" bgcolor="#75ADE6">';
                                    $html .= '<td valign=top colspan=' . $intColSpan . '> LOTE ' . ($numLoteMat) . ' </td>';
                                    $html .= '</tr>';
                                }

                                $html .= '<tr class="textonegrito" bgcolor="#DCEDF7" >';
                                $html .= '<td width=30px>ORD.</td><td >DESC. ITEM</td><td >CÓD</td><td>UNIDADE</td>';

                                if ($exibeTd) {
                                    $html .= '<td>DESC. DETALHADA ITEM</td>';
                                }

                                $html .= '<td>QUANTIDADE</td>';

                                if ($flagResultadoLicitacao and in_array($ultimaFase, $fasesComResultado)) {
                                    $html .= '<td>MARCA</td>';
                                    $html .= '<td>MODELO</td>';
                                }

                                if ($ComissaoCodigo != $comissaoApenasHomologada || ($ComissaoCodigo == $comissaoApenasHomologada && $flagResultadoLicitacao && in_array($ultimaFase, $fasesComResultado))) {
                                    $html .= '<td>VALOR ESTIMADO</td>';
                                }

                                if ($flagResultadoLicitacao and in_array($ultimaFase, $fasesComResultado)) {
                                    $html .= '<td>VALOR HOMOLOGADO</td>';
                                }

                                $html .= '</tr>';
                                ++ $cont;
                            }

                            $html .= '<tr>';
                            $html .= '<td valign=top>' . $ordMaterial . '</td>';
                            $html .= '<td valign=top>' . ($descMaterial) . '</td>';
                            $html .= '<td valign=top>' . ($seqMaterial) . '</td>';
                            $html .= '<td valign=top>' . $unidMaterial . '</td>';

                            if ($exibeTd) {
                                $descDetalhadaMaterial = $descDetalhadaMaterial == '' ? '---' : $descDetalhadaMaterial;
                                $html .= '<td align=center>' . $descDetalhadaMaterial . '</td>';
                            }

                            $html .= '<td valign=rigth   align="rigth" > ' . number_format($qtdMaterial, '4', ',', '.') . '</td>';

                            if ($flagResultadoLicitacao and in_array($ultimaFase, $fasesComResultado)) {
                                $html .= '<td valign=top>' . $marcaMaterial . '</td>';
                                $html .= '<td valign=top>' . $modeloMaterial . '</td>';
                            }

                            if ($ComissaoCodigo != $comissaoApenasHomologada || ($ComissaoCodigo == $comissaoApenasHomologada && $flagResultadoLicitacao && in_array($ultimaFase, $fasesComResultado))) {
                                $html .= '<td valign=top>R$ ' . number_format((float) $valorEstimadoMaterial, 2, ',', '.') . '</td>';
                            }

                            if ($flagResultadoLicitacao and in_array($ultimaFase, $fasesComResultado)) {
                                $html .= '<td valign=top>R$ ' . number_format((float) $valorHomologadoMaterial, 2, ',', '.') . '</td>';
                            }

                            $html .= '</tr>';
                        }

                        $obj = new ArrayObject($arrLoteItens);
                        $it = $obj->getIterator();
                        $keyPrev = 0;
                        $acumEconomicidade = 0;

                        foreach ($it as $key => $item) {
                            $acumEconomicidade = $CR87475->calcularEconomicidade($item['VEMT'], $item['VHMT'],$licitacaoTipo);
                            $html = str_replace("{ECONOMICIDADE$key}", 'ECONOMICIDADE LOTE: ' . number_format((float) $acumEconomicidade, 2, ',', '.') . '%', $html);
                            $acumEconomicidade = 0;
                        }
                        echo $html;
                        echo '</table>';
                        echo '</td>';
                        echo '</tr>';
                    }

                    // --------------------------------------------------------
                    // SQL para capturar os itens de serviço da licitação
                    // ---------------------------------------------------------
                    // [CUSTOMIZAÇÃO] - Ajusta economicidade do serviço
                    $arrLoteItens = array(); // Reinicia o array para adicionar apenas os serviços
                                             // [/CUSTOMIZAÇÃO]

                    $sqlServico = <<<SQLSERVICO
SELECT
    a.aitelporde ,
    b.eservpdesc ,
    a.cservpsequ ,
    a.citelpnuml ,
    c.aforcrsequ ,
    c.nforcrrazs ,
    c.nforcrfant ,
    c.aforcrccgc ,
    a.eitelpdescse ,
    a.vitelpunit ,
    a.vitelpvlog ,
	a.aitelpqtso
FROM
    sfpc.tbitemlicitacaoportal a LEFT JOIN sfpc.tbfornecedorcredenciado c
        ON a.aforcrsequ = c.aforcrsequ ,
    sfpc.tbservicoportal b
WHERE
    a.cservpsequ = b.cservpsequ
    AND a.clicpoproc = $LicitacaoProcesso
    AND a.alicpoanop = $LicitacaoAno
    AND a.cgrempcodi = $GrupoCodigo
    AND a.ccomlicodi = $ComissaoCodigo
    AND a.corglicodi = $OrgaoLicitanteCodigo
ORDER BY
    4 ,
    1
SQLSERVICO;

                    $resultTemp = $db->query($sqlServico);
                    $result = $db->query($sqlServico);

                    $exibeTd = true;

                    while ($arrI = $resultTemp->fetchRow()) {
                        if (! empty($arrI[8])) {
                            $exibeTd = true;
                            break;
                        }
                    }
                    $Rows = $result->numRows();

                    // ------------------------------------------------------------
                    // - Se encontrar pelo menos uma linha exibir grade com Itens
                    // ------------------------------------------------------------
                    if ($Rows > 0) {
                        echo '<tr  class="textonegrito" bgcolor="#75ADE6"   > ';
                        echo '<td colspan=5 align="center"   valign="middle" >ITENS DE SERVIÇO DA LICITAÇÃO</td>';
                        echo '</tr>';

                        echo '<tr>';
                        echo '<td colspan=5>';

                        echo '<table  border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">';
                        echo '<tr class="textonegrito" bgcolor="#DCEDF7" >';
                        // echo "<tr><td colspan=5> LOTE </td></tr>";
                        // echo "<td width=30px>ORD.</td><td >DESC. ITEM</td><td >CÓD</td> ";
                        echo '</tr>';

                        $numLoteServAntes = '999';

                        $cont = 0;
                        $html = '';
                        $acumEconomicidade = 0;
                        $intColSpan = 8;
                        $arrLoteItens = array();
                        while ($Linha = $result->fetchRow()) {
                            $ordServico = $Linha[0];
                            $descServico = $Linha[1];
                            $seqServico = $Linha[2];
                            $numLoteServico = $Linha[3];
                            $codForCredServ = $Linha[4];
                            $razaoSocForServ = $Linha[5];
                            $nomeFantFornServ = $Linha[6];
                            $cgcForCredServ = $Linha[7];

                            $descDetalhadaServico = $Linha[8];
                            $valorEstimadoItem = $Linha[9];
                            $valorHomologadoItem = $Linha[10];

                            // [CUSTOMIZAÇÃO] - Ajusta economicidade do serviço
                            $qtdItemServico = $Linha[11];

                            // [CUSTOMIZAÇÃO] - Ajusta economicidade do material
                            $valorTotalEstimadoServico = ((float) $qtdItemServico) * $CR87475->trataValor($valorEstimadoItem);
                            $valorTotalHomologadoServico = ((float) $qtdItemServico) * $CR87475->trataValor($valorHomologadoItem);

                            $arrLoteItens[$numLoteServico]['VEST'] += $valorTotalEstimadoServico;
                            $arrLoteItens[$numLoteServico]['VHST'] += $valorTotalHomologadoServico;

                            // [/CUSTOMIZAÇÃO]

                            if ($numLoteServico != $numLoteServAntes) {
                                $numLoteServAntes = $numLoteServico;

                                // if ($licitacaoComResultado and $ultimaFase == 13 and ! empty($razaoSocForServ)) {
                                if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and ! empty($razaoSocForServ)) {
                                    $soma = getTotalValorServico($db, $LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteServico);

                                    $html .= '<tr class="textonegrito" bgcolor="#75ADE6">';
                                    $html .= "<td valign=top colspan=$intColSpan> LOTE " . ($numLoteServico) . ' FORNECEDOR VENCEDOR: ' . FormataCpfCnpj($cgcForCredServ) . ' - ' . ($razaoSocForServ) . ' - ' . 'R$ ' . (number_format((float) $soma, 2, ',', '.')) . " {ECONOMICIDADE$numLoteServico}</td>";
                                    $html .= '</tr>';
                                } else {
                                    $html .= '<tr class="textonegrito" bgcolor="#75ADE6">';
                                    $html .= "<td valign=top colspan=$intColSpan> LOTE " . ($numLoteServico) . '</td>';
                                    $html .= '</tr>';
                                }

                                $html .= '<tr class="textonegrito" bgcolor="#DCEDF7" >';
                                $html .= '<td width=30px>ORD.</td>';
                                $html .= '<td>DESC. ITEM</td>';
                                $html .= '<td align="center">QUANTIDADE</td>';

                                if ($exibeTd) {
                                    $html .= '<td >DESC. DETALHADA ITEM</td>';
                                }

                                $html .= '<td>CÓD</td>';
                                if ($ComissaoCodigo != $comissaoApenasHomologada || ($ComissaoCodigo == $comissaoApenasHomologada && $licitacaoComResultado && in_array($ultimaFase, $fasesComResultado))) {
                                    $html .= '<td>VALOR ESTIMADO</td>';
                                }

                                if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado)) {
                                    $html .= '<td>VALOR HOMOLOGADO</td>';
                                }

                                $html .= '</tr>';
                                ++ $cont;
                            }

                            $html .= '<tr>';
                            $html .= '<td valign="top">' . ($ordServico) . '</td>';
                            $html .= '<td valign="top">' . ($descServico) . '</td>';
                            $html .= '<td valign="top" align="center">' . number_format($qtdItemServico, '4', ',', '.') . '</td>';

                            if ($exibeTd) {
                                $html .= '<td valign=top>' . ($descDetalhadaServico) . '</td>';
                            }

                            $html .= '<td valign="top" align="center">' . ($seqServico) . '</td>';
                            if ($ComissaoCodigo != $comissaoApenasHomologada || ($ComissaoCodigo == $comissaoApenasHomologada && $licitacaoComResultado && in_array($ultimaFase, $fasesComResultado))) {
                                $html .= '<td valign=top>R$ ' . number_format((float) $valorEstimadoItem, 2, ',', '.') . '</td>';
                            }

                            if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado)) {
                                $html .= '<td valign=top>R$ ' . number_format((float) $valorHomologadoItem, 2, ',', '.') . '</td>';
                            }

                            $html .= '</tr>';
                        }

                        $obj = new ArrayObject($arrLoteItens);
                        $it = $obj->getIterator();
                        $keyPrev = 0;
                        $acumEconomicidadeServico = 0;

                        // //($arrLoteItens);die;
                        foreach ($it as $key => $item) {
                            $acumEconomicidadeServico = $CR87475->calcularEconomicidade($item['VEST'], $item['VHST'],$licitacaoTipo);
                            $html = str_replace("{ECONOMICIDADE$key}", 'ECONOMICIDADE LOTE: ' . number_format((float) $acumEconomicidadeServico, 2, ',', '.') . '%', $html);
                            $acumEconomicidadeServico = 0;
                        }

                        echo $html;
                        echo '</table>';
                        echo '</td>';
                        echo '</tr>';
                    }

                    // --------------------------------------------------------
                    // Final Trecho de código inserido por Heraldo
                    // ---------------------------------------------------------

                    echo "<tr>\n";

                    echo '	<td class="textonegrito" bgcolor="#DCEDF7" colspan="4">';
                    $paramentrosConsultaDocumentos = "processo=$LicitacaoProcesso&ano=$LicitacaoAno&comissao=$ComissaoCodigo&grupo=$GrupoCodigo";
                    echo '<a href="#"
                             onclick="javascript:AbreJanelaItem(\'../licitacoes/ConsHistoricoDetalhesDocumentosRelacionados.php?' . $paramentrosConsultaDocumentos . '\', 900, 350);"
                           >DOCUMENTOS RELACIONADOS</a>';
                    echo '</td>';

                    echo "</tr>\n";
                    ?>

                    <?php
                    // Pega as Fases da Licitação #
                    $sql = 'SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ';
                    $sql .= '       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ';
                    $sql .= '       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP, C.TATASFULAT ';
                    $sql .= '  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ';
                    $sql .= '    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ';
                    $sql .= '   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ';
                    $sql .= '   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ';
                    $sql .= ' 	    LEFT OUTER JOIN SFPC.TBUSUARIOPORTAL U ON C.CUSUPOCODI = U.CUSUPOCODI';
                    $sql .= " WHERE B.CLICPOPROC = $LicitacaoProcesso AND B.ALICPOANOP = $LicitacaoAno ";
                    $sql .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
                    $sql .= '   AND B.CFASESCODI = A.CFASESCODI';
                    $sql .= ' ORDER BY B.TFASELDATA ASC, A.AFASESORDE ASC';
                    $result = $db->query($sql);

                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    }

                    $resultadoFases = $db->query($sql);
                    $totalLinhas = $resultadoFases->numRows();
                    $totalAtasNaHomologacao = 0; // Acumulador de total de atas na fase de homologação

                    if ($totalLinhas > 0) {
                        /*
                         * while ($linhaFase = $resultadoFases->fetchRow()) {
                         * if ($linhaFase[0] == "HOMOLOGAÇÃO") {
                         * $totalAtasNaHomologacao++;
                         * }
                         * }
                         */

                        // ATA DE REGISTRO (Apenas se for registro de preço)
                        if($RegistroPreco == 'SIM'){
                            echo "<tr>\n";
                            echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\" align=\"center\"> ATA DE REGISTRO DE PREÇO - DOCUMENTO(S)</td>\n";
                            echo "</tr>\n"; ?>
                                <tr>
                                    <td class="textonormal" colspan="4"><br>
                                        <?php
                                        if ($Mens2 == 1) {
                                            ExibeMens($Mensagem, $Tipo);
                                        }
                            # Pega a(s) ata(s) de registro de preços ANTIGA TABELA - documentos #
                            $sqlRegPreco  = "SELECT CATARPCODI, EATARPNOME ";
                            $sqlRegPreco .= "  FROM SFPC.TBATAREGISTROPRECO";
                            $sqlRegPreco .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
                            $sqlRegPreco .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $GrupoCodigo ";

                            $resultRegPreco = $db->query($sqlRegPreco);
                            if (PEAR::isError($resultRegPreco)) {
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlRegPreco");
                            } else {
                                $Rows = $resultRegPreco->numRows();
                                resetArquivoAcesso();
                                while ($cols = $resultRegPreco->fetchRow()) {
                                    $cont++;
                                    $dados[$cont-1] = "$cols[0];$cols[1];$cols[2]";
                                    $dadosFile[] = "$cols[0];$cols[1];$cols[2]";
                                }
                                # Mostra os Documentos relacionados com a Licitação #
                                if ($Rows > 0) {
                                    for ($Row = 0 ; $Row < $Rows ; $Row++) {
                                        $Linha = explode(";", $dadosFile[$Row]);
                                        $ArqUpload = "registropreco/ATAREGISTROPRECO".$GrupoCodigo."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$Linha[0];
                                        $Arq = $GLOBALS["CAMINHO_UPLOADS"].$ArqUpload;
                                        if (file_exists($Arq)) {
                                            $tamanho = filesize($Arq)/1024;
                                            addArquivoAcesso($ArqUpload);
                                            $Url = "../registropreco/ConsRegistroPrecoDownloadDoc.php?GrupoCodigo=$GrupoCodigo&Processo=$LicitacaoProcesso&ProcessoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&DocCodigo=$Linha[0]";
                                            if (!in_array($Url, $_SESSION['GetUrl'])) {
                                                $_SESSION['GetUrl'][] = $Url;
                                            }
                                            echo "<a href=\"$Url\" target=\"_blank\" class=\"textonormal\"><img src=\"../midia/disquete.gif\" border=\"0\"> $Linha[1]</a> - ";
                                            printf("%01.1f", $tamanho);
                                            echo " k <br>";
                                        } else {
                                            echo "<img src=\"../midia/disquete.gif\" border=\"0\"> $Linha[1] - <b>Arquivo não armazenado</b>";
                                        }
                                        if ($Linha[2] != "") {
                                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Obs.: $Linha[2]";
                                        }
                                        echo "<br>\n";
                                    }
                                } else {
                                    //echo "<font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n";
                                }
                            }      
                            
                            # NOVA TABELA - Pega a(s) ata(s) de registro de preços - documentos #
                            $sqlRegPrecoNova  = "SELECT CARPNOSEQU, EARPINOBJE, CARPINCODN, AARPINANON, CORGLICODI, CUSUPOCODI ";
                            $sqlRegPrecoNova .= "  FROM SFPC.TBATAREGISTROPRECOINTERNA";
                            $sqlRegPrecoNova .= " WHERE FARPINSITU = 'A' AND CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
                            $sqlRegPrecoNova .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $GrupoCodigo ";
                            $resultRegPrecoNova = $db->query($sqlRegPrecoNova);

                            ////($sqlRegPrecoNova." - Orgão: ".$OrgaoLicitanteCodigo);
                            //die();

                            if (PEAR::isError($resultRegPrecoNova)) {
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlRegPrecoNova");
                            } else {
                                $RowsInterna = $resultRegPrecoNova->numRows();
                                resetArquivoAcesso();
                                $cont = 0;
                                while ($cols = $resultRegPrecoNova->fetchRow()) {
                                    $cont++;
                                    $dados[$cont-1] = "$cols[0];$cols[1];$cols[2];$cols[3];$cols[4];$cols[5]";
                                }

                                
                                # Mostra os Documentos relacionados com a Licitação #
                                if ($RowsInterna > 0) {
                                    for ($Row = 0 ; $Row < $RowsInterna ; $Row++) {
                                        $Linha = explode(";", $dados[$Row]);

                                        $dto = CR87475::consultarDCentroDeCustoUsuario($OrgaoLicitanteCodigo);
                                        //ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi

                                        $numeroAtaFormatado = $dto[0][0] . str_pad($dto[0][1], 2, '0', STR_PAD_LEFT);
                                        $numeroAtaFormatado .= "." . str_pad($Linha[2], 4, "0", STR_PAD_LEFT) . "/" . $Linha[3];
                                        
                                        echo "<br>&nbsp;&nbsp;<a href='javascript:window.open(\"../registropreco/ConsAtaRegistroPrecoExtratoAtaDetalhe.php?carpnosequ=".$Linha[0]."&inativos=1&window=1\", \"_blank\", \"toolbar=no,scrollbars=yes,resizable=yes,top=100,left=100,width=1000,height=500\");'>".$numeroAtaFormatado."</a><br><br>";
                                        //echo "<br>&nbsp;&nbsp;<a href='../registropreco/ConsAtaRegistroPrecoExtratoAtaDetalhe.php?carpnosequ=".$Linha[0]."&inativos=1'> ".$numeroAtaFormatado."</a><br><br>";
                                    }

                                } else {
                                    //echo "<font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n";
                                }
                            }// fim Dados da nova tabela

                            if( ($RowsInterna + $Rows) < 1){
                                echo "<font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n";
                            }

                        }
                        //END

                        while ($linhaFase = $resultadoFases->fetchRow()) {
                            $descricaoFase = $linhaFase[0];
                            $tempCodigoAta = $linhaFase[7];
                            $tempNomeAta = $linhaFase[8];

                            if ($descricaoFase == 'HOMOLOGAÇÃO' && $tempCodigoAta != '' && $tempNomeAta != '') {
                                $codigoAta = $linhaFase[7];
                                $nomeAta = $linhaFase[8];
                                $faseCod = $linhaFase[4];
                                ++ $totalAtasNaHomologacao;
                            }
                        }

                        // Exibe link direto para o único arquivo
                        if ($totalAtasNaHomologacao == 1) {
                            $ArqUpload = 'licitacoes/' . 'ATASFASE' . $GrupoCodigo . '_' . $LicitacaoProcesso . '_' . $LicitacaoAno . '_' . $ComissaoCodigo . '_' . $OrgaoLicitanteCodigo . '_' . $faseCod . '_' . $codigoAta;
                            $Arquivo = $GLOBALS['CAMINHO_UPLOADS'] . $ArqUpload;
                            addArquivoAcesso($ArqUpload);

                            $Url = "ConsHistoricoDownloadAtas.php?GrupoCodigo=$GrupoCodigo&LicitacaoProcesso=$LicitacaoProcesso&LicitacaoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$faseCod&AtaCodigo=$codigoAta";

                            if (! in_array($Url, $_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }

                            echo "<tr>\n";
                            echo '    <td class="textonegrito" bgcolor="#DCEDF7" colspan="5">';
                            echo '        <a href="' . $Url . '">RESULTADO DO PROCESSO LICITATÓRIO</a>';
                            echo '    </td>';
                            echo "</tr>\n";
                        }

                        // Caso exista mais de uma ata na fase de homologação será exibido um link para um popup
                        if ($totalAtasNaHomologacao > 1) {
                            echo "<tr>\n";
                            echo '	<td class="textonegrito" bgcolor="#DCEDF7" colspan="5">';

                            $paramentrosConsultaDocumentos = "processo=$LicitacaoProcesso&ano=$LicitacaoAno&comissao=$ComissaoCodigo&grupo=$GrupoCodigo&orgaoLicitante=$OrgaoLicitanteCodigo";
                            echo '<a href="#"
										 onclick="javascript:AbreJanelaItem(\'../licitacoes/ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.php?' . $paramentrosConsultaDocumentos . '\', 900, 350);">
    									 RESULTADO DO PROCESSO LICITATÓRIO
    								  </a>';
                            echo '</td>';
                            echo "</tr>\n";
                        }
                    }
                    ?>

                    <tr>
         <td class="textonormal" colspan="4"
          style="padding: 0; border: 0px;"
         >
	                        <?php
                        if ($Mens2 == 1) {
                            ExibeMens($Mensagem, $Tipo);
                        }
                        ?>
	                    </td>
        </tr>

                    <?php
                    $Rows = $result->numRows();

                    if ($Rows > 0) {
                        echo "<tr>\n";
                        echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\"> HISTÓRICO </td>\n";
                        echo "</tr>\n";
                        echo "<tr>\n";
                        echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">FASE</td>\n";
                        echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">DATA</td>\n";
                        echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">DETALHE</td>\n";
                        echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">ATA(S) DA FASE</td>\n";
                        echo "</tr>\n";

                        while ($Linha = $result->fetchRow()) {
                            $FaseCodigo = $Linha[4];
                            $DataFase = substr($Linha[6], 8, 2) . '/' . substr($Linha[6], 5, 2) . '/' . substr($Linha[6], 0, 4);
                            $FaseDetalhamento = $Linha[5];
                            $nomeAta = $Linha[8];
                            $itemObservacao = ' - <b>Observação/ Justificativa:</b> "' . $Linha[9] . '"';
                            $itemExcluido = $Linha[10];
                            $itemAutor = ' - <b>Responsável:</b> "' . $Linha[11] . '"';
                            $itemDataAlteracao = $Linha[12];
                            if ($itemDataAlteracao < '2011-03-23') {
                                $itemObservacao = '';
                                $itemAutor = '';
                            }

                            if (($CodFaseAnterior != '') and ($Linha[4] != $CodFaseAnterior)) {
                                echo "</td>\n</tr>\n";
                            }

                            if ($Linha[4] == $CodFaseAnterior) {
                                $ArqUpload = 'licitacoes/ATASFASE' . $GrupoCodigo . '_' . $LicitacaoProcesso . '_' . $LicitacaoAno . '_' . $ComissaoCodigo . '_' . $OrgaoLicitanteCodigo . '_' . $FaseCodigo . '_' . $Linha[7];
                                $Arquivo = $GLOBALS['CAMINHO_UPLOADS'] . $ArqUpload;
                                addArquivoAcesso($ArqUpload);

                                if ($itemExcluido == 'S') {
                                    echo "<s><br><img src='../midia/disqueteInexistente.gif' border='0'/><font color=\"#000000\"> $nomeAta </font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                                } elseif (file_exists($Arquivo)) {
                                    $Url = "ConsHistoricoDownloadAtas.php?GrupoCodigo=$GrupoCodigo&LicitacaoProcesso=$LicitacaoProcesso&LicitacaoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";
                                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                                        $_SESSION['GetUrl'][] = $Url;
                                    }
                                    echo "<br><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
                                } else {
                                    echo "<br><img src='../midia/disqueteInexistente.gif' border='0'/><font color=\"#000000\"> $nomeAta </font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
                                }
                            } else {
                                echo "<tr>\n";
                                $DataFase = substr($Linha[6], 8, 2) . '/' . substr($Linha[6], 5, 2) . '/' . substr($Linha[6], 0, 4);
                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[0]</td>\n";
                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataFase</td>\n";
                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]&nbsp;</td>\n";

                                if ($Linha[7] != 0) {
                                    $ArqUpload = 'licitacoes/ATASFASE' . $GrupoCodigo . '_' . $LicitacaoProcesso . '_' . $LicitacaoAno . '_' . $ComissaoCodigo . '_' . $OrgaoLicitanteCodigo . '_' . $FaseCodigo . '_' . $Linha[7];
                                    $Arquivo = $GLOBALS['CAMINHO_UPLOADS'] . $ArqUpload;
                                    addArquivoAcesso($ArqUpload);
                                    ?>

                                    <?php
                                    //if ($itemExcluido == 'S') {
                                       // echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><img src='../midia/disqueteInexistente.gif' border='0'/><s><font color=\"#000000\"> $nomeAta</font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                                    //} elseif (file_exists($Arquivo)) {
                                    if (file_exists($Arquivo)) {
                                        $Url = "ConsHistoricoDownloadAtas.php?GrupoCodigo=$GrupoCodigo&LicitacaoProcesso=$LicitacaoProcesso&LicitacaoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";
                                        if (! in_array($Url, $_SESSION['GetUrl'])) {
                                            $_SESSION['GetUrl'][] = $Url;
                                        }
                                        ?><?php

                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=$Url><img src=../midia/disquete.gif border=0> <font color=\"#000000\"> $nomeAta </font></a> ";
                                        if ($itemExcluido == 'S') {
                                            echo " <span style='color: red; font-weight: bold'> (Excluido)</span>";
                                        }
                                        echo $itemAutor .' '. $itemObservacao . "<br/>";
                                    } else {
                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><img src='../midia/disqueteInexistente.gif' border='0'/><font color=\"#000000\"> $nomeAta</font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
                                    }
                                } else {
                                    echo '<td valign="top" bgcolor="#F7F7F7" class="textonormal">&nbsp;</td>';
                                }
                            }

                            $CodFaseAnterior = $Linha[4];
                        }
                        echo "\n</td>\n</tr>\n";
                    }
                    // Busca o(s) resultado(s) da Licitação #
                    $sql = " SELECT ERESLIHABI, ERESLIINAB, ERESLIJULG, ERESLIREVO, ERESLIANUL ";
                    $sql .= "  FROM SFPC.TBRESULTADOLICITACAO ";
                    $sql .= "  WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
                    $sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoLicitanteCodigo ";
                    $sql .= "   AND CGREMPCODI = $GrupoCodigo";

                    $result = $db->query($sql);
                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    }
                    $Rows = $result->numRows();
                    if ($Rows == 1) {
                        while ($Linha = $result->fetchRow()) {
                            $Resultados = 1;
                            $ResultadoHabi = $Linha[0];
                            $ResultadoInab = $Linha[1];
                            $ResultadoJulg = $Linha[2];
                            $ResultadoRevo = $Linha[3];
                            $ResultadoAnul = $Linha[4];
                        }
                    } else {
                        $Resultados = 0;
                    }
                    $db->disconnect();
                    if (($ResultadoHabi != '') or ($ResultadoInab != '') or ($ResultadoJulg != '') or ($ResultadoRevo != '') or ($ResultadoAnul != '')) {
                        echo "<tr>\n";
                        echo "<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">RESULTADOS</td>\n";
                        echo "</tr>\n";
                    }
                    if ($ResultadoHabi != '') {
                        echo "<tr>\n";
                        echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >EMPRESAS HABILITADAS </td>\n";
                        echo "  <tr>\n";
                        echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoHabi</td>\n";
                        echo "  </tr>\n";
                        echo "</tr>\n";
                    }
                    if ($ResultadoInab != '') {
                        echo "<tr>\n";
                        echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >EMPRESAS INABILITADAS </td>\n";
                        echo "  <tr>\n";
                        echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoInab</td>\n";
                        echo "  </tr>\n";
                        echo "</tr>\n";
                    }
                    if ($ResultadoJulg != '') {
                        echo "<tr>\n";
                        echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" > JULGAMENTO </td>\n";
                        echo "  <tr>\n";
                        echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoJulg</td>\n";
                        echo "  </tr>\n";
                        echo "</tr>\n";
                    }
                    if ($ResultadoRevo != '') {
                        echo "<tr>\n";
                        echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >REVOGAÇÃO </td>\n";
                        echo "  <tr>\n";
                        echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoRevo</td>\n";
                        echo "  </tr>\n";
                        echo "</tr>\n";
                    }
                    if ($ResultadoAnul != '') {
                        echo "<tr>\n";
                        echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >ANULAÇÃO </td>\n";
                        echo "  <tr>\n";
                        echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoAnul</td>\n";
                        echo "  </tr>\n";
                        echo "</tr>\n";
                    }
                    ?>
                    <tr>
         <td colspan="5">
          <table class="textonormal" cellspacing="0" border="0"
           align="right"
          >
           <tr>
            <td>
             <form method="post"
              action="ConsHistoricoResultadoGeral.php"
             >
              <input type="hidden" name="ComissaoCodigo"
               value="<?= $ComissaoCodigoResultado; ?>"
              /> <input type="hidden" name="ModalidadeCodigo"
               value="<?= $ModalidadeCodigo; ?>"
              /> <input type="hidden" name="OrgaoLicitanteCodigo"
               value="<?= $OrgaoLicitanteCodigoResultado; ?>"
              /> <input type="hidden" name="LicitacaoAno"
               value="<?= $LicitacaoAnoResultado; ?>"
              /> <input type="hidden" name="LicitacaoProcesso"
               value="<?= $LicitacaoProcessoResultado; ?>"
              /> <input type="hidden" name="GrupoCodigo"
               value="<?= $GrupoCodigoResultado; ?>"
              /> <input type="hidden" name="Selecao"
               value="<?= $Selecao; ?>"
              /> <input type="hidden" name="Objeto"
               value="<?= $ObjetoResultado; ?>"
              /> <input type="hidden" name="TipoItemLicitacao"
               value="<?= $TipoItemLicitacao; ?>"
              /> <input type="hidden" name="Item" value="<?= $Item; ?>">
              <?php if(isset($pesquisa) && $_GET['origemTramitacao'] == 1){ ?>
              <input type="button" name="Voltar" value="Voltar"
               class="botao" onclick="retornarTramitacao();" >
              <?php }else if(isset($pesquisa) && $_GET['origemTramitacao'] == 2){ ?>
                <input type="button" name="Voltar" value="Voltar"
               class="botao" onclick="retornarTramitacao();" >
              <?php }else{ ?>
                <input type="submit" name="Voltar" value="Voltar" class="botao" >
              <?php } ?>  
             </form>
            </td>
           </tr>
          </table>
         </td>
        </tr>
       </table>
      </td>
     </tr>
    </table>
   </td>
  </tr>
  <!-- Fim do Corpo -->
 </table>
</body>
</html>
<script language="javascript" type="text/javascript">
    function AbreJanelaItem(url,largura,altura){
	   window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
    }
</script>
