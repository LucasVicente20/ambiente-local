<?php
# ------------------------------------------------------------------
# Portal de Compras
# Programa: CadTramitacaoConfirmacao.php
# Autor:    Ernesto Ferreira
# Data:     13/08/2018
# Objetivo: Tarefa Redmine 199436
# ------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Acesso ao arquivo de funções #
require_once '../licitacoes/funcoesTramitacao.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica          = $_POST['Critica'];
    $botao            = $_POST['botao'];
    $idProtocolo      = $_POST['protsequ'];
    $numprotocolo     = $_POST['numprotocolo'];
    $anoprotocolo     = $_POST['anoprotocolo'];
    $usuarioResponsavel = $_POST['usuarioResponsavel'];
    $idTramitacao     = $_POST['idTramitacao'];

    //Dados para retornar a tela de pesquisa
    $numprotocoloRetorno   = $_POST['numprotocoloRetorno'];
    $anoprotocoloRetorno   = $_POST['anoprotocoloRetorno'];
    $orgaoRetorno          = $_POST['orgaoRetorno'];
    $objetoRetorno         = $_POST['objetoRetorno'];
    $numerociRetorno       = $_POST['numerociRetorno'];
    $numeroOficioRetorno   = $_POST['numeroOficioRetorno'];
    $numeroSccRetorno      = $_POST['numeroSccRetorno'];
    $proLicitatorioRetorno = $_POST['proLicitatorioRetorno'];
    $acaoRetorno           = $_POST['acaoRetorno'];
    $origemRetorno         = $_POST['origemRetorno'];
    $DataIniRetorno        = $_POST['DataIniRetorno'];
    $DataFimRetorno        = $_POST['DataFimRetorno'];
    $retornoEntrada        = $_POST['retornoEntrada'];
    $retornoSaida          = $_POST['retornoSaida'];

}else{
    $Critica            = $_GET['Critica'];
    $botao              = $_GET['botao'];
    $idProtocolo        = $_GET['protsequ'];
    $numprotocolo       = $_GET['numprotocolo'];
    $anoprotocolo       = $_GET['anoprotocolo'];

    // dados de retorno
    $numprotocoloRetorno   = $_GET['numprotocoloRetorno'];
    $anoprotocoloRetorno   = $_GET['anoprotocoloRetorno'];
	$orgaoRetorno          = $_GET['orgao'];
	$objetoRetorno         = $_GET['objeto'];
	$numerociRetorno       = $_GET['numeroci'];
	$numeroOficioRetorno   = $_GET['numeroOficio'];
	$numeroSccRetorno      = $_GET['numeroScc'];
	$proLicitatorioRetorno = $_GET['proLicitatorio'];
	$acaoRetorno           = $_GET['acao'];
	$origemRetorno         = $_GET['origem'];
	$DataIniRetorno        = $_GET['DataIni'];
    $DataFimRetorno        = $_GET['DataFim'];
    $retornoEntrada        = $_GET['retornoEntrada'];
    $retornoSaida          = $_GET['retornoSaida'];
    $usuarioAlterado       = $_GET['usuarioAlterado'];
    $tramitacaoExcluida    = $_GET['tramitacaoExcluida'];

}   

if($botao == 'Alterar') {
    $tituloPagina = 'ALTERAR';
    $tituloBotao = 'Alterar';
}

if($botao == 'Excluir') {
    $tituloPagina = 'EXCLUIR';
    $tituloBotao = 'Excluir';
}


if($botao == 'Voltar') {
    $params = '?protsequ=' . $idProtocolo;
    $params .= '&numprotocolo=' . $numprotocolo;
    $params .= '&anoprotocolo=' . $anoprotocolo;
    $params .= '&numprotocoloRetorno=' . $numprotocoloRetorno;
    $params .= '&anoprotocoloRetorno=' . $anoprotocoloRetorno;
    $params .= "&orgao=".$orgaoRetorno;
    $params .= "&objeto=".$objetoRetorno;
    $params .= "&numeroci=".$numerociRetorno;
    $params .= "&numeroOficio=".$numeroOficioRetorno;
    $params .= "&numeroScc=".$numeroSccRetorno;
    $params .= "&proLicitatorio=".$proLicitatorioRetorno;
    $params .= "&acao=".$acaoRetorno;
    $params .= "&origem=".$origemRetorno;
    $params .= "&DataIni=".$DataIniRetorno;
    $params .= "&DataFim=".$DataFimRetorno;
    $params .= "&retornoEntrada=".$retornoEntrada;
    $params .= "&retornoSaida=".$retornoSaida;
    header('Location: CadTramitacaoDetalhe.php' . $params);
    exit();
}


if ($Critica == 1) {
    # Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    
    //Verifica se o usuario responsavel
    if ($usuarioResponsavel <= 0 && $botao == 'Alterar') {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.Confirmacao.usuarioResponsavel.focus();\" class=\"titulo2\">Usuário Responsável</a>";
    }


    if($Mens == 0){
        if($botao == 'Alterar'){
            // atualiza a tramitação anterior
            $sqlUpdate = "UPDATE SFPC.TBTRAMITACAOLICITACAO
                            SET cusupocodi = ".$usuarioResponsavel.", ttramlulat = NOW()
                            WHERE cprotcsequ = ".$idProtocolo." AND ctramlsequ = ". $idTramitacao;

            $resUpdate = executarSQL($db, $sqlUpdate);
            if (PEAR::isError($resUpdate)) {
                cancelarTransacao($db);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                $Botao = $tituloBotao;
            }else{
                $params = '?protsequ=' . $idProtocolo;
                $params .= '&numprotocolo=' . $numprotocolo;
                $params .= '&anoprotocolo=' . $anoprotocolo;

                $params .= '&numprotocoloRetorno=' . $numprotocoloRetorno;
                $params .= '&anoprotocoloRetorno=' . $anoprotocoloRetorno;
                $params .= "&orgao=".$orgaoRetorno;
                $params .= "&objeto=".$objetoRetorno;
                $params .= "&numeroci=".$numerociRetorno;
                $params .= "&numeroOficio=".$numeroOficioRetorno;
                $params .= "&numeroScc=".$numeroSccRetorno;
                $params .= "&proLicitatorio=".$proLicitatorioRetorno;
                $params .= "&acao=".$acaoRetorno;
                $params .= "&origem=".$origemRetorno;
                $params .= "&DataIni=".$DataIniRetorno;
                $params .= "&DataFim=".$DataFimRetorno;
                $params .= "&retornoEntrada=".$retornoEntrada;
                $params .= "&retornoSaida=".$retornoSaida;
                $params .= '&usuarioAlterado=1';
                $params .= '&t='.mktime();
                header('Location: CadTramitacaoDetalhe.php' . $params);
                exit();
            }
        }

        if($botao == 'Excluir'){
            

            // Exclui os anexos da tramitacao
            $sqlDeleteAnexo = "DELETE FROM SFPC.TBTRAMITACAOLICITACAOANEXO
                            WHERE cprotcsequ = ".$idProtocolo." AND ctramlsequ = ". $idTramitacao;

            $resDeleteAnexo = executarSQL($db, $sqlDeleteAnexo);
            if (PEAR::isError($resDelete)) {
                cancelarTransacao($db);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            }else{

                // Exclui a tramitação
                $sqlDelete = "DELETE FROM SFPC.TBTRAMITACAOLICITACAO
                                WHERE cprotcsequ = ".$idProtocolo." AND ctramlsequ = ". $idTramitacao;

                $resDelete = executarSQL($db, $sqlDelete);
                if (PEAR::isError($resDelete)) {
                    cancelarTransacao($db);
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }else{
                    $novoUltimoPasso   = getTramitacaoUltimoPasso($idProtocolo);

                    $idTramitacao = $novoUltimoPasso[11];


                    // atualiza a tramitação anterior
                    $sqlUpdate = "UPDATE sfpc.tbtramitacaolicitacao
                                    SET ttramlsaid = NULL, ttramlulat = NOW()
                                    WHERE cprotcsequ = ".$idProtocolo." AND ctramlsequ = ". $idTramitacao;

                    $resUpdate = executarSQL($db, $sqlUpdate);
                    if (PEAR::isError($resUpdate)) {
                        cancelarTransacao($db);
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                        $Botao = "";
                    }else{

                        $params = '?protsequ=' . $idProtocolo;
                        $params .= '&numprotocolo=' . $numprotocolo;
                        $params .= '&anoprotocolo=' . $anoprotocolo;
                        
                        $params .= '&numprotocoloRetorno=' . $numprotocoloRetorno;
                        $params .= '&anoprotocoloRetorno=' . $anoprotocoloRetorno;
                        $params .= "&orgao=".$orgaoRetorno;
                        $params .= "&objeto=".$objetoRetorno;
                        $params .= "&numeroci=".$numerociRetorno;
                        $params .= "&numeroOficio=".$numeroOficioRetorno;
                        $params .= "&numeroScc=".$numeroSccRetorno;
                        $params .= "&proLicitatorio=".$proLicitatorioRetorno;
                        $params .= "&acao=".$acaoRetorno;
                        $params .= "&origem=".$origemRetorno;
                        $params .= "&DataIni=".$DataIniRetorno;
                        $params .= "&DataFim=".$DataFimRetorno;
                        $params .= "&retornoEntrada=".$retornoEntrada;
                        $params .= "&retornoSaida=".$retornoSaida;
                        $params .= '&tramitacaoExcluida=1';
                        $params .= '&t='.mktime();
                        header('Location: CadTramitacaoDetalhe.php' . $params);
                        exit();
                    }


                }
            }
        }        

    }

}

  
// Dados do ultimo passo!

$ultimoPasso   = getTramitacaoUltimoPasso($idProtocolo);

$agenteOrigem = $ultimoPasso[0];
$usuarioResponsavel = $ultimoPasso[1];
$acao = $ultimoPasso[4];
$arrEntrada = explode("-",substr($ultimoPasso[5],0,10));
$dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];
//calculo do prazo
$previsto = calcularTramitacaoSaida($dataHoraEntrada, $ultimoPasso[6]);

$realizado = $ultimoPasso[7];

$now = date('Y-m-d');
$arrPrevisto = explode("/",$previsto);
$dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];


$tipoAgente = $ultimoPasso[10];
$idTramitacao = $ultimoPasso[11];


$htmlUsuarioResponsavel = '';

$sql = "select distinct usu.cusupocodi, usu.eusuporesp from sfpc.tbtramitacaolicitacao tram
join sfpc.tbtramitacaoagenteusuario agusu on agusu.ctagensequ = tram.ctagensequ
join sfpc.tbusuarioportal usu on usu.cusupocodi = agusu.cusupocodi
where ".$idProtocolo." = tram.cprotcsequ and agusu.ctagensequ = ".$ultimoPasso[3]."
and usu.cusupocodi <> ".$ultimoPasso[12]." 
order by usu.eusuporesp asc";


$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        if ($Linha[0] == $usuarioResponsavel) {
            $htmlUsuarioResponsavel.= "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
        } else {
            $htmlUsuarioResponsavel.= "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
        }
    }
}

?>
<html>
<style>

.titulo_resultado{
    background-color: #DCEDF7
}

.tamanho_campo{
    width:100%;
}
</style>    
<?php
# Carrega o layout padrão @
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">//Init();</script>
    <form action="CadTramitacaoConfirmacao.php" method="post" name="Confirmacao">
        <input type="hidden" name="botao" value="">
        <br> <br> <br> <br> <br><br>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <td width="100">
                    <img border="0" src="../midia/linha.gif" alt="">
                </td>
                <td align="left" class="textonormal">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a>
                    > Licitações > Tramitação > Saída
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	        <?php if ( $Mens == 1 ) {?>
	        <tr>
                <td width="100"></td>
                <td align="left" colspan="2">
                    <?php ExibeMens($Mensagem,$Tipo,1); ?>
                </td>
            </tr>
	        <?php } ?>
	        <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3" >
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width: 100%;">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">TRAMITAÇÃO - <?php echo $tituloPagina ?></td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Preencha os dados abaixo para efetuar a pesquisa e clique no número do Protocolo do Processo Licitatório desejado. </p>
                                        </td>
                                    </tr>
                                   
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7"> Agente Destino</td>
                                                    <td class="textonormal">
                                                        <?php echo $agenteOrigem ?>
                                                    </td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Usuário Responsável Atual &nbsp;</td>
                                                    <td class="textonormal">
                                                        <?php echo $usuarioResponsavel ?>
                                                    </td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ação</td>
                                                    <td class="textonormal">
                                                        <?php echo $acao ?>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Data Hora da Entrada</td>
                                                    <td class="textonormal">
                                                        <?php echo $dataHoraEntrada ?>
                                                        
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Previsto</td>
                                                    <td class="textonormal">
                                                        <?php echo $previsto ?>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Realizado</td>
                                                    <td class="textonormal">
                                                        <?php
                                                        if($realizado){
                                                         $arrRealizado = explode("-",substr($realizado,0,10));
                                                         $dataRealizado = $arrRealizado[2]."/".$arrRealizado[1]."/".$arrRealizado[0];
                                                         echo $dataRealizado;
                                                        }else{
                                                            echo '';
                                                        }
                                                         ?>
                                                    </td>
                                                    
                                                </tr>
                                                <?php 
                                                    if($realizado){   
                                                        if(strtotime($realizado) > strtotime($dataPrevista)) { ?>
                                                        <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7">Atraso</td>
                                                            <td class="textonormal">
                                                                ATRASADO
                                                            </td>
                                                            <td class="textonormal"></td>
                                                        </tr>
                                                    <?php 
                                                        } 
                                                    }else{
                                                        $now = date('Y-m-d');
                                                        if(strtotime($now) > strtotime($dataPrevista)) { ?>
                                                            <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7">Atraso</td>
                                                                <td class="textonormal">
                                                                    ATRASADO
                                                                </td>
                                                                <td class="textonormal"></td>
                                                            </tr>
                                                        <?php 
                                                        } 
                                                    }
                                                
                                                if($tituloBotao == 'Alterar'){
                                                    if($tipoAgente == 'I'){
                                                ?>
                                                        <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7">Novo Usuário Responsável* &nbsp;</td>
                                                            <td class="textonormal">
                                                            <select name="usuarioResponsavel" class="tamanho_campo">
                                                                    <option value="0">Selecione um Usuário...</option>
                                                                    <?php echo $htmlUsuarioResponsavel ?>
                                                            </select>
                                                            </td>
                                                            
                                                        </tr>
                                                <?php  } // se for do tipo interno
                                                    
                                                }// se for botao == alterar
                                                ?>

                                                <input type="hidden" name="Critica" value="1"> 
                                                <input type="hidden" name="protsequ" value="<?php echo $idProtocolo ?>">
                                                <input type="hidden" name="idTramitacao" value="<?php echo $idTramitacao ?>">  
                                                <input type="hidden" name="numprotocolo" value="<?php echo $numprotocolo ?>"> 
                                                <input type="hidden" name="anoprotocolo" value="<?php echo $anoprotocolo ?>"> 
                                                
                                                <input type="hidden" name="numprotocoloRetorno" value="<?php echo $numprotocoloRetorno ?>"> 
                                                <input type="hidden" name="anoprotocoloRetorno" value="<?php echo $anoprotocoloRetorno ?>"> 
                                                <input type="hidden" name="orgaoRetorno" value="<?php echo $orgaoRetorno ?>">
                                                <input type="hidden" name="objetoRetorno" value="<?php echo $objetoRetorno ?>">
                                                <input type="hidden" name="numerociRetorno" value="<?php echo $numerociRetorno ?>">
                                                <input type="hidden" name="numeroOficioRetorno" value="<?php echo $numeroOficioRetorno ?>">
                                                <input type="hidden" name="numeroSccRetorno" value="<?php echo $numeroSccRetorno ?>">
                                                <input type="hidden" name="proLicitatorioRetorno" value="<?php echo $proLicitatorioRetorno ?>">
                                                <input type="hidden" name="acaoRetorno" value="<?php echo $acaoRetorno ?>">
                                                <input type="hidden" name="origemRetorno" value="<?php echo $origemRetorno ?>">
                                                <input type="hidden" name="DataIniRetorno" value="<?php echo $DataIniRetorno ?>">
                                                <input type="hidden" name="DataFimRetorno" value="<?php echo $DataFimRetorno ?>">  
                                                <input type="hidden" name="retornoEntrada" value="<?php echo $retornoEntrada ?>">
                                                <input type="hidden" name="retornoSaida" value="<?php echo $retornoSaida ?>">   
                                            </table>
                                        </td>
                                    </tr>
                                    <tr colspan='13'>
                                        <td class="textonormal" align="right">
                                            <input type="submit" name="Executar" value="<?php echo $tituloBotao ?>" class="botao" onclick="document.Confirmacao.botao.value = '<?php echo $tituloBotao ?>'">
                                            <input type="submit" name="Voltar" value="Voltar" class="botao" onclick="document.Confirmacao.botao.value = 'Voltar'">
                                        </td>
                                            
                                    </tr>
  
                                </table>
                                
                            </td>
                        </tr>
                        <tr>
                            <td>
                              
                            </td>
                    </tr>
                    </table>      
                    


                </td>
            </tr>
            <!-- Fim do Corpo -->
        </table>
    </form>




</body>
</html>
