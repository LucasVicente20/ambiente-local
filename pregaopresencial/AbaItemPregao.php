<?php
# -------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio coutinho
# Data:		22/01/2019
# Objetivo: Tarefa Redmine 208468
# -------------------------------------------------------------------------

# Exibe Aba Qualificação Técnica - Formulário E #
function ExibeAbaItemPregao(){
    ?>
    <html>
    <?php
    # Carrega o layout padrão #
    layout();
    ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" type="">
        <!--
        function Submete(Destino) {
            document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function enviar(valor){
            document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function AbreJanela(url,largura,altura) {
            window.open(url,'pagina','status=no,scrollbars=no,left=20,top=130,width='+largura+',height='+altura);
        }
        function enviarDestino(valor, Destino){
            document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
            document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
            document.CadPregaoPresencialSessaoPublica.submit();
        }

        <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadPregaoPresencialSessaoPublica.php" method="post" name="CadPregaoPresencialSessaoPublica">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Pregão Presencial > Sessão Pública
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="100"></td>
                <td align="left" colspan="2">
                    <?php
                        if( $_SESSION['Mens'] != 0 ){
                            ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']);
                        }
                        $_SESSION['Mens'] = null;
                        $_SESSION['Tipo'] = null;
                        $_SESSION['Mensagem'] = null
                    ?>
                </td>
            </tr>
            <!-- Fim do Erro -->

            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table  border="0" cellspacing="0" cellpadding="3" summary="" width="1024px">
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                            PREGÃO PRESENCIAL - SESSÃO PÚBLICA
                                            <?php
                                                if($_SESSION['CodLoteSelecionado'] <> null) {
                                                    $db                     = Conexao();
                                                    $Processo 				= $_SESSION['Processo'];
                                                    $ProcessoAno 			= $_SESSION['ProcessoAno'];
                                                    $ComissaoCodigo 		= $_SESSION['ComissaoCodigo'];
                                                    $OrgaoLicitanteCodigo 	= $_SESSION['OrgaoLicitanteCodigo'];
                                                    $NumeroLoteSelecionado 	= $_SESSION['NumeroLoteSelecionado'];
                                                    $CodLoteSelecionado 	= $_SESSION['CodLoteSelecionado'];

                                                    //Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
                                                    $sqlSolicitacoes = "SELECT	a.cmatepsequ, a.cservpsequ, eunidmdesc, replace(a.aitelpqtso,'.',',') as valor, ematepdesc, 
                                                                                eservpdesc, a.eitelpdescmat, a.eitelpdescse
                                                                        FROM sfpc.tbitemlicitacaoportal a 
                                                                        LEFT OUTER JOIN sfpc.tbmaterialportal b on a.cmatepsequ = b.cmatepsequ 
                                                                        LEFT OUTER JOIN sfpc.tbservicoportal c on a.cservpsequ = c.cservpsequ
                                                                        LEFT OUTER JOIN sfpc.tbunidadedemedida d on b.cunidmcodi = d.cunidmcodi
                                                                        WHERE a.clicpoproc = $Processo 
                                                                            AND   	a.alicpoanop = $ProcessoAno
                                                                            AND  	a.ccomlicodi = $ComissaoCodigo
                                                                            AND   	a.corglicodi = $OrgaoLicitanteCodigo
                                                                            AND   	a.cgrempcodi =".$_SESSION['_cgrempcodi_']."
                                                                            AND 	a.citelpnuml = $NumeroLoteSelecionado
                                                                        ORDER BY	a.aitelporde";

                                                    $resultItens = $db->query($sqlSolicitacoes);

                                                    if( PEAR::isError($resultItens) ){
                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
                                                    }

                                                    $LinhaItens = $resultItens->fetchRow();
                                                    $QuantidadeItens = 0;
                                                    $QuantidadeItens = $resultItens->numRows();
                                                    //Início - Controle Botões Lances, Histórico de Lances e Renegociar Preço
                                                    $UltimaRodadaLances = 0;
                                                    $PregaoFinalizado = 0;
                                                    $QuantidadeFornecedores = 0;

                                                    $sqlFornecedores = "SELECT COUNT(pi.cpregpsequ)
                                                                        FROM sfpc.tbpregaopresencialfornecedor fn,
                                                                            sfpc.tbpregaopresencialclassificacao cl,
                                                                            sfpc.tbpregaopresencialsituacaofornecedor sf,
                                                                            sfpc.tbpregaopresenciallote lt,
                                                                            sfpc.tbpregaopresencialprecoinicial pi
                                                                        WHERE lt.cpregtsequ  = $CodLoteSelecionado
                                                                               AND fn.cpregfsequ  = cl.cpregfsequ
                                                                               AND lt.cpregtsequ  = cl.cpregtsequ
                                                                               AND sf.cpresfsequ  = cl.cpresfsequ
                                                                               AND cl.cpregfsequ  = pi.cpregfsequ
                                                                               AND cl.cpregtsequ  = pi.cpregtsequ
                                                                               AND pi.fpregpalan  = 1";

                                                    $resultFornecedores = $db->query($sqlFornecedores);

                                                    if( PEAR::isError($resultFornecedores) ){
                                                        ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
                                                    }

                                                    $LinhaPrecoInicial = $resultFornecedores->fetchRow();
                                                    $QuantidadeFornecedores = $LinhaPrecoInicial[0];
                                                    if($QuantidadeFornecedores > 0 and $_SESSION['CodLoteSelecionado'] > 0) {
                                                        $CodLoteSelecionado = $_SESSION['CodLoteSelecionado'];
                                                        //Pegar a última rodada de lances - Se não houver o valor passado será zero
                                                        $sqlUltimaRodadaLances = "SELECT MAX(la.cpreglnumr)
                                                                                  FROM sfpc.tbpregaopresenciallance la,
                                                                                       sfpc.tbpregaopresenciallote lt,
                                                                                       sfpc.tbpregaopresencialprecoinicial pi,
                                                                                       sfpc.tbpregaopresencialfornecedor fn
                                                                                   WHERE lt.cpregtsequ  = $CodLoteSelecionado
                                                                                         AND lt.cpregtsequ  = la.cpregtsequ
                                                                                         AND lt.cpregtsequ  = pi.cpregtsequ
                                                                                         AND la.cpregpsequ  = pi.cpregpsequ
                                                                                         AND pi.cpregfsequ  = fn.cpregfsequ
                                                                                         AND la.fpreglrpfn  = 0
                                                                                         AND pi.fpregpalan  = 1";

                                                        $resultUltimaRodadaLances 	= $db->query($sqlUltimaRodadaLances);

                                                        if( PEAR::isError($resultUltimaRodadaLances) ){
                                                            ExibeErroBD("$ErroPrograma\nLinhaUltimaRodada: ".__LINE__."\nSql: $sqlUltimaRodadaLances");
                                                        }

                                                        $LinhaUltimaRodadaLances 	= $resultUltimaRodadaLances->fetchRow();
                                                        $UltimaRodadaLances 		= $LinhaUltimaRodadaLances[0];

                                                        if($UltimaRodadaLances == '' or $UltimaRodadaLances == null) {
                                                            $UltimaRodadaLances = 0;
                                                        } else if ($UltimaRodadaLances > 0) {
                                                            $sqlValUltimaRodadaLances = "SELECT COUNT(la.cpreglsequ)
                                                                                         FROM sfpc.tbpregaopresenciallance la,
                                                                                              sfpc.tbpregaopresenciallote lt,
                                                                                              sfpc.tbpregaopresencialprecoinicial pi,
                                                                                              sfpc.tbpregaopresencialfornecedor fn
                                                                                         WHERE lt.cpregtsequ  = $CodLoteSelecionado
                                                                                         AND lt.cpregtsequ  = la.cpregtsequ
                                                                                         AND lt.cpregtsequ  = pi.cpregtsequ
                                                                                         AND la.cpregpsequ  = pi.cpregpsequ
                                                                                         AND pi.cpregfsequ  = fn.cpregfsequ
                                                                                         AND pi.fpregpalan  = 1
                                                                                         AND la.fpreglurod  = 1
                                                                                         AND la.fpregllven  = 1
                                                                                         AND la.fpreglrpfn  = 0
                                                                                         AND la.cpreglnumr  = $UltimaRodadaLances";

                                                            $resultValUltimaRodadaLances = $db->query($sqlValUltimaRodadaLances);
                                                            if( PEAR::isError($resultValUltimaRodadaLances) ){
                                                                ExibeErroBD("$ErroPrograma\nLinhaPregaoFinalizado: ".__LINE__."\nSql: $sqlSolicitacoes");
                                                            }

                                                            $LinhaValUltimaRodadaLances 	= $resultValUltimaRodadaLances->fetchRow();
                                                            $PregaoFinalizado 				= $LinhaValUltimaRodadaLances[0];
                                                        }
                                                    }

                                                    $_SESSION['DecricaoSituacaoLote'] = "";
                                                    if($_SESSION['CodSituacaoLoteSelecionado'] == 5) {
                                                        $SQLMotivoFracasso	= "SELECT lt.epregtdess FROM sfpc.tbpregaopresenciallote lt WHERE lt.cpregtsequ = $CodLoteSelecionado";
                                                        $ResMotivoFracasso 	= $db->query($SQLMotivoFracasso);
                                                        if (PEAR::isError($ResMotivoFracasso)) {
                                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SQLMotivoFracasso");
                                                        }

                                                        $LinhaMotivoFracasso = $ResMotivoFracasso->fetchRow();
                                                        $_SESSION['DecricaoSituacaoLote'] = $LinhaMotivoFracasso[0];
                                                    }
                                                    $db->disconnect();
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" >
                                            <table border="0" width="100%" summary="">
                                                <tr>
                                                    <td width="20%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Comissão:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label style="width:500px;"><?php echo $_SESSION['NomeComissao'];?></label>
                                                        <input type="hidden" name="CodigoDaComissao" value="<?php echo $_SESSION['CodigoComissao'];?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Processo:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo substr($_SESSION['NumeroDoProcesso'] + 10000,1); ?></label>
                                                        <input type="hidden" name="NumeroDoProcesso" value="<?php echo substr($_SESSION['NumeroDoProcesso'] + 10000,1);?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Ano:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo $_SESSION['AnoDoExercicio']; ?></label>
                                                        <input type="hidden" name="AnoDoExercicio" value="<?php echo $_SESSION['AnoDoExercicio'];?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Modalidade:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo $_SESSION['Modalidade']; ?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Registro de Preço:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <input type="hidden" id="registroPreco" name="registroPreco" value="<?php echo $_SESSION['RegistroPreco'];?>"/>
                                                        <label>
                                                            <?php
                                                                if ($RegistroPreco) {
                                                                    echo "Sim";
                                                                } else {
                                                                    echo "Não";
                                                                }
                                                            ?>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Licitação:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo substr($_SESSION['Licitação'] + 10000,1); ?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Ano da Licitação:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo $_SESSION['AnoLicitação']; ?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Objeto:</td>
                                                    <td>
                                                        <label class="textonormal" style="word-wrap:break-word;" ><?php echo $_SESSION['Objeto'];?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Tratamento diferenciado EPP/MEI/ME:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <input type="hidden" id="tratamentoDiferenciado" name="tratamentoDiferenciado" value="<?php echo $_SESSION['TratamentoDiferenciado'];?>"/>
                                                        <label>
                                                            <?php
                                                                if	($_SESSION['TratamentoDiferenciado'] == 'N'){
                                                                    echo "NÃO";
                                                                } elseif ($_SESSION['TratamentoDiferenciado'] == 'E') {
                                                                    echo "EXCLUSIVO";
                                                                } elseif ($_SESSION['TratamentoDiferenciado'] == 'C') {
                                                                    echo "COTA RESERVADA";
                                                                } elseif ($_SESSION['TratamentoDiferenciado'] == 'S') {
                                                                    echo "SUBCONTRATAÇÃO";
                                                                } elseif ($_SESSION['TratamentoDiferenciado'] == 'M') {
                                                                    echo "COTA RESERVADA/EXCLUSIVA";
                                                                } else {
                                                                    echo "NÃO INFORMADO";
                                                                }
                                                            ?>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Órgão demandante:</td>
                                                    <td>
                                                        <label class="textonormal" style="word-wrap:break-word;" ><?php echo $_SESSION['OrgaoDemandante'];?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Tipo de Licitação:</td>
                                                    <td>
                                                        <select name="PregaoTipoSelecionado" class="textonormal">
                                                            <option value="N" <?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'selected' : '');?>>MENOR PREÇO</option>
                                                            <option value="M" <?php echo ($_SESSION['PregaoTipo'] == 'M' ? 'selected' : '');?>>MAIOR OFERTA</option>
                                                        </select>
                                                        <input	name="AlterarPregaoTipo" value="Alterar" class="botao" onclick="javascript:enviarDestino('AlterarPregaoTipo','E');" type="button">
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <?php echo NavegacaoAbas(off,off,off,off,on); ?>
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <td colspan="4">
                                                        <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                            <tbody>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="left" bgcolor="#DCEDF7" valign="left">
                                                                    Preços Iniciais por Fornecedor
                                                                    <input name="PrecosIniciais" value="Preços Iniciais" class="botao"
                                                                           onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialPrecoInicialPorFornecedor.php?ProgramaOrigem=CadPregaoPresencialPrecoInicialPorFornecedor', 1024, 768);"
                                                                           type="button">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="left" bgcolor="#DCEDF7" valign="left" style="font-weight: bold;">Seleção de Lote:
                                                                    <select name="CodLoteSelecionado" class="textonormal">
                                                                        <option value="">Selecione um Lote...</option>
                                                                        <!-- Mostra as licitações cadastradas -->
                                                                        <?php
                                                                            $db     = Conexao();
                                                                            $sql    = "SELECT DISTINCT 	pl.cpregtnuml, pl.cpregtsequ ";
                                                                            $sql   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl";
                                                                            $sql   .= "  WHERE 			pl.cpregasequ = ".$_SESSION['PregaoCod']." ";
                                                                            $sql   .= "  ORDER BY 		pl.cpregtnuml";

                                                                            $result = $db->query($sql);
                                                                            if( PEAR::isError($result) ){
                                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                                            } else {
                                                                                $ComissaoCodigoAnt = "";
                                                                                while( $Linha = $result->fetchRow() ){
                                                                                    echo "<option value=\"$Linha[1]\">Lote: $Linha[0]</option>\n" ;
                                                                                }
                                                                            }

                                                                            $db->disconnect();
                                                                        ?>
                                                                    </select>
                                                                    <input name="SelecionarLote" value="Selecionar" class="botao" onclick="javascript:enviarDestino('SelecionarLote', 'E');" type="button">
                                                                </td>
                                                            </tr>

                                                            <?php if($_SESSION['CodLoteSelecionado'] > 0) { ?>
                                                            <tr>
                                                                <td colspan="7">
                                                                    <!-- INÍCIO - DETALHES DO LOTE -->
                                                                    <table border="0" width="100%" summary="">
                                                                        <tr>
                                                                            <td width="20%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Nº Lote:</td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <label style="width:500px;"><?php echo $_SESSION['NumeroLoteSelecionado'];?></label>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Descrição Resumida:</td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <input type="text" size="110" maxlength="100" name="DescricaoLoteSelecionado" value="<?php echo $_SESSION['DescricaoLoteSelecionado'];?>" />
                                                                                <input name="SalvarDescricaoLote" value="<?=$_SESSION['DescricaoLoteSelecionado'] == '' ? "Salvar" : "Alterar" ?>" class="botao" onclick="javascript:enviarDestino('SalvarDescricaoLote', 'E');" type="button">
                                                                            </td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Situação: </td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <label><?php echo $_SESSION['SituacaoLoteSelecionado'].(($_SESSION['CodSituacaoLoteSelecionado'] == 5) ?
                                                                                            (" - ".$_SESSION['DecricaoSituacaoLote']) : (($_SESSION['CodSituacaoLoteSelecionado'] == 4) ? (" - NENHUM FORNECEDOR CREDENCIADO PARA O PREGÃO") : (""))); ?></label>
                                                                            </td>
                                                                        </tr>

                                                                        <?php if($_SESSION['ValorLoteSelecionado'] > 0){?>
                                                                        <tr>
                                                                            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Fornecedor Vencedor:</td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <label><?php echo $_SESSION['FornecedorVencedorLoteSelecionado']; ?></label>
                                                                            </td>
                                                                        </tr>
                                                                        <?php }?>

                                                                        <?php if($_SESSION['ValorLoteSelecionado'] > 0){?>
                                                                        <tr>
                                                                            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;"><?=(($_SESSION['PregaoTipo'] == 'N') ? ("Preço Vencedor (R$): ") : ("Oferta Vencedora (%): "))?></td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <input type="hidden" id="registroPreco" name="registroPreco" value="<?php echo $_SESSION['ValorLoteSelecionado'];?>"/>
                                                                                <label style= "<?=(($_SESSION['ValorRenegociadoLoteSelecionado'] > 0) ? ("color: red; text-decoration:line-through;") : (""))?>">
                                                                                    <?php echo number_format($_SESSION['ValorLoteSelecionado'], 4, ',', '.');?>
                                                                                </label>
                                                                            </td>
                                                                        </tr>
                                                                        <?php }?>

                                                                        <?php if($_SESSION['ValorRenegociadoLoteSelecionado'] > 0){?>
                                                                        <tr>
                                                                            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;"><?=(($_SESSION['PregaoTipo'] == 'N') ? ("Preço Renegociado (R$): ") : ("Oferta Renegociada (%): "))?> </td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <input type="hidden" id="registroPreco" name="registroPreco" value="<?php echo $_SESSION['ValorRenegociadoLoteSelecionado'];?>"/>
                                                                                <label style="color: blue;font-weight: bold;">
                                                                                    <?php echo number_format($_SESSION['ValorRenegociadoLoteSelecionado'], 4, ',', '.');?>
                                                                                </label>
                                                                            </td>
                                                                        </tr>
                                                                        <?php }?>
                                                                    </table>
                                                                    <!-- FIM - DETALHES DO LOTE -->
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">
                                                                    ITENS DO LOTE
                                                                </td>
                                                            </tr>
                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr class="head_principal">
                                                                <!--  Coluna 1 = ORD-->
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%"><br /> ORDEM</td>

                                                                <!--  Coluna 2 = CNPJ/CPF-->
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%"> <br /> TIPO </td>

                                                                <!--  Coluna 3 = CNPJ/CPF-->
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓD. REDUZIDO </td>

                                                                <!--  Coluna 4 = RAZÃO SOCIAL/NOME-->
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> UNID. </td>

                                                                <!--  Coluna 4 = RAZÃO SOCIAL/NOME-->
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> QUANTIDADE </td>

                                                                <!--  Coluna 5 = VALOR-->
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="30%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO RESUMIDA </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="30%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO DETALHADA </td>
                                                            </tr>

                                                            <?php for ($itr = 0; $itr < $QuantidadeItens; ++ $itr) { ?>
                                                            <!-- Dados MEMBRO DE COMISSÃO  -->
                                                            <tr>
                                                                <!--  Coluna 1 = Codido-->
                                                                <td class="textonormal" align="center" style="text-align: center">
                                                                    <?= ($itr + 1); ?>
                                                                </td>

                                                                <!--  Coluna 2  = Tipo -->
                                                                <td class="textonormal" align="center">
                                                                    <?= $LinhaItens[0] == null ? "CADUS" : "CADUM"?>
                                                                </td>

                                                                <!--  Coluna 2  = Código -->
                                                                <td class="textonormal" align="center">
                                                                    <?= $LinhaItens[0] == null ? $LinhaItens[1] : $LinhaItens[0]?>
                                                                </td>

                                                                <!--  Coluna 2  = Unidade -->
                                                                <td class="textonormal" align="center">
                                                                    <?= $LinhaItens[2] == null ? "S/ UNIDADE" : $LinhaItens[2]?>
                                                                </td>

                                                                <!--  Coluna 2  = Quantidade -->
                                                                <td class="textonormal" align="center">
                                                                    <?= $LinhaItens[3] ?>
                                                                </td>

                                                                <!--  Coluna 3 = Descricao Resumida-->
                                                                <td class="textonormal" align="center">
                                                                    <?php
                                                                        $Encoding = 'UTF-8';
                                                                        $CodRedMaterialServicoBanco = ($LinhaItens[0] == null ? $LinhaItens[1] : $LinhaItens[0]);
                                                                        $TipoGrupoBanco				= ($LinhaItens[0] == null ? "S" : "M") ;
                                                                        $UrlItem = "../estoques/CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco";
                                                                    ?>
                                                                    <a href="javascript:AbreJanela('<?= $UrlItem ?>',700,550);"><font color="#000000">
                                                                            <?= $LinhaItens[4] == null ? $LinhaItens[5] : $LinhaItens[4]?></font>
                                                                    </a>
                                                                </td>

                                                                <!--  Coluna 2  = Descrição Detalhada -->
                                                                <td class="textonormal" align="center">
                                                                    <?= (($LinhaItens[6] == null and $LinhaItens[7] == null) ? 'S/N' : ($LinhaItens[6] == null ? mb_strtoupper($LinhaItens[7], $Encoding) : mb_strtoupper($LinhaItens[6], $Encoding))) ?>
                                                                </td>

                                                                <?php
                                                                        $LinhaItens = $resultItens->fetchRow();
                                                                    }
                                                                ?>

                                                                <?php if ($QuantidadeItens <= 0) { ?>
                                                                <tr>
                                                                    <td class="textonormal itens_material" colspan="7">Nenhum Item para o Lote</td>
                                                                </tr>
                                                                <!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                                <?php } ?>

                                                            <tr>
                                                                <td colspan="6" class="titulo3 itens_material menosum" width="95%">TOTAL DE ITENS DO LOTE:</td>
                                                                <td class="textonormal" align="center" style="font-weight: bold;" width="5%">
                                                                    <div id="MaterialTotal"><?=$QuantidadeItens?></div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="textonormal" colspan="6" align="center">
                                                                    <!-- Carrega janela para preços iniciais -->
                                                                    <?php if ($_SESSION['CodSituacaoLoteSelecionado'] != 5) { ?>
                                                                        <input name="PrecosIniciais" value="Preços Iniciais" class="botao"
                                                                            onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencial<?=(($UltimaRodadaLances > 0 and $PregaoFinalizado >= 0 and $QuantidadeFornecedores > 0) ? ("Historico") : (""))?>PrecoInicial.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 1024, 768);"
                                                                            type="button">
                                                                    <?php } ?>

                                                                    <?php if ($UltimaRodadaLances >= 0 && $PregaoFinalizado == 0 && $QuantidadeFornecedores > 0) { ?>
                                                                        <!-- Início condição para haver lances - Ao menos 1 preços iniciais > 0 -->
                                                                        <!-- Carrega janela para Lances -->
                                                                        <?php if ($_SESSION['CodSituacaoLoteSelecionado'] != 5) { ?>
                                                                            <input name="Lances" value="Lances" class="botao"
                                                                                onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialLance.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 1024, 768);"
                                                                                type="button">
                                                                            <?php } ?>

                                                                        <!-- Fim condição para haver lances -->
                                                                        <?php } else if ($UltimaRodadaLances > 0 and $PregaoFinalizado == 1) { ?>
                                                                            <!-- Início condição para haver histórico de lances - Ao menos 1 rodada de lances para o  lote selecionado -->
                                                                            <!-- Carrega janela para Histórico de Lances (A mesma de Lances, porém, sem botões)   -->
                                                                            <?php if ($_SESSION['CodSituacaoLoteSelecionado'] != 5) { ?>
                                                                            <input name="HistoricoLances" value="Histórico Lances" class="botao"
                                                                                onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialHistoricoLance.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 1024, 768);"
                                                                                type="button">
                                                                            <?php } ?>
                                                                            <!-- Fim condição para haver histórico de lances -->
                                                                            <?php if($_SESSION['CodSituacaoLoteSelecionado'] <> 3) { ?>
                                                                            <!-- Início condição para haver renegociação - Deve haver um Fornecedor vencedor para o lote selecionado (Vencedor/Vencedor Provisário) -->
                                                                            <!-- Carrega janela para Renegociação de Preço vencedor  -->

                                                                            <?php if ($_SESSION['CodSituacaoLoteSelecionado'] != 5) { ?>
                                                                                <input name="Renegociar" value="Renegociar Preço" class="botao"
                                                                                    onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialRenegociarPreco.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 700, 600);"
                                                                                    type="button">
                                                                            <?php } ?>
                                                                            <!-- Fim condição para haver renegociação -->
                                                                            <?php
                                                                            }
                                                                        }
                                                                    ?>

                                                                    <?php if($_SESSION['CodSituacaoLoteSelecionado'] <> 3 and $_SESSION['CodSituacaoLoteSelecionado'] <> 4 and $_SESSION['CodSituacaoLoteSelecionado'] <> 5) { ?>

                                                                    <input name="Fracassar" value="Fracassar Lote" class="botao"
                                                                        onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialFracassarLote.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 700, 600);"
                                                                        type="button">
                                                                    </td>
                                                                    <?php } if($_SESSION['CodSituacaoLoteSelecionado'] == 5) { ?>

                                                                    <input name="Desfazer Fracasso" value="Desfazer Fracasso do Lote" class="botao"
                                                                        onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialFracassarLote.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 700, 600);"
                                                                        type="button">
                                                                    <?php } ?>
                                                                </td>

                                                                <td class="textonormal" colspan="1" align="right" style="font-weight: bold;">
                                                                    <?php if ($_SESSION['CodSituacaoLoteSelecionado'] != 5) { ?>
                                                                        Situação:
                                                                        <select name="SituacaoLote" class="textonormal">
                                                                            <option value="0">Selecione uma Situação...</option>
                                                                            <!-- Mostra as licitações cadastradas  -->
                                                                            <?php
                                                                            $db                 = Conexao();
                                                                            $CodLoteSelecionado = $_SESSION['CodLoteSelecionado'];
                                                                            $CondicaoSituacao   = "		WHERE 		sl.cpreslsequ IN (0)";
                                                                            $CodigoFornecedor   = 0;

                                                                            #Recebe o último código de Preço Inicial#
                                                                            $sqlLote = "SELECT cpregfsequ, cpreslsequ FROM sfpc.tbpregaopresenciallote WHERE cpregtsequ = $CodLoteSelecionado";
                                                                            $res = $db->query($sqlLote);

                                                                            if (PEAR::isError($res)) {
                                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                                            } else {
                                                                                $LinhaLote 				= $res->fetchRow();
                                                                                $CodigoFornecedor		= $LinhaLote[0];
                                                                                $SituacaoLote			= $LinhaLote[1];
                                                                            }

                                                                            if($CodigoFornecedor > 0) {
                                                                                $CondicaoSituacao = "		WHERE 		sl.cpreslsequ IN (3)";
                                                                            }

                                                                            #Recebe o último código de Preço Inicial#
                                                                            $sqlFornecedores = "SELECT COUNT(cpregfsequ) FROM sfpc.tbpregaopresencialprecoinicial WHERE cpregtsequ = $CodLoteSelecionado";
                                                                            $res = $db->query($sqlFornecedores);

                                                                            if (PEAR::isError($res)) {
                                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                                            }else{
                                                                                $LinhaFornecedores 		= $res->fetchRow();
                                                                                $TotalFornecedores		= $LinhaFornecedores[0];
                                                                            }

                                                                            if(($CodigoFornecedor == null or $CodigoFornecedor == '' or $CodigoFornecedor == 0) and $TotalFornecedores == 0 and $SituacaoLote == 1)
                                                                            {
                                                                                $CondicaoSituacao = "		WHERE 		sl.cpreslsequ IN (4)";
                                                                            }
                                                                            else if($SituacaoLote == 4)
                                                                            {
                                                                                $CondicaoSituacao = "		WHERE 		sl.cpreslsequ IN (1)";
                                                                            }
                                                                            else if($SituacaoLote == 3)
                                                                            {
                                                                                $CondicaoSituacao = "		WHERE 		sl.cpreslsequ IN (2)";
                                                                            }

                                                                            $sql    = "SELECT			sl.cpreslsequ, sl.epreslnome ";
                                                                            $sql   .= "		FROM 		sfpc.tbpregaopresencialsituacaolote sl";
                                                                            $sql   .= $CondicaoSituacao;
                                                                            $sql   .= "  	ORDER BY 	sl.epreslnome";


                                                                            $result = $db->query($sql);
                                                                            if( PEAR::isError($result) ){
                                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                                            }else{
                                                                                $ComissaoCodigoAnt = "";
                                                                                while( $Linha = $result->fetchRow() ){

                                                                                    echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n" ;
                                                                                }
                                                                            }
                                                                            $db->disconnect();
                                                                            ?>
                                                                        </select>

                                                                        <input name="AplicarSituacaoLote" value="Aplicar" class="botao"
                                                                            onclick="javascript:enviarDestino('AplicarSituacaoLote', 'E');"
                                                                            type="button">
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                    <?php }?>
                                    <tr>
                                        <td colspan="4" align="right">
                                            <input type="button" value="Primeira Aba" class="botao" onclick="javascript:enviar('E');">
                                            <input type="hidden" name="Botao" value="">
                                            <input type="hidden" name="Origem" value="E">
                                            <input type="hidden" name="Destino">
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
    </form>
    <script language="javascript" type="">
        <!--
        <?php if($_SESSION['TipoHabilitacao']=="L"){ ?>
            document.CadPregaoPresencialSessaoPublica.NomeEntidade.focus();
        <?php } if( $_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir" ){ ?>
        <?php
        $Url = "RotVerificaEmail.php?ProgramaOrigem=CadPregaoPresencialSessaoPublica";
        if (!in_array($Url,$_SESSION['GetUrl'])){
            $_SESSION['GetUrl'][] = $Url;
        }
        ?>
        window.open('<?php echo $Url; ?>','pagina','status=no,scrollbars=no,left=200,top=150,width=400,height=225');
        <?php } ?>
        //-->
    </script>
    </body>
    </html>
    <?php
    exit;
}