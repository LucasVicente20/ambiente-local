<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaItemContratoIncluir.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato$_SESSION['dadosSalvar']["origemScc"]
#-------------------------------------------------------------------------
    require_once dirname(__FILE__) . '/../funcoes.php';
    require_once "./ClassContratos.php";
# Exibe Aba Item Contrato Incluir - Formulário B #
    function ExibeAbaItemContratoIncluir(){
    $ObjContrato = new Contrato();
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if(!empty($_SESSION['dadosSalvar']['csolcosequ']) && !empty($_SESSION['dadosSalvar']['aforcrsequ']) && !empty($_SESSION['dadosSalvar']['origemScc'])){
            $dadosItens = $ObjContrato->PesqusiaItensSCC($_SESSION['dadosSalvar']['csolcosequ'], $_SESSION['dadosSalvar']['aforcrsequ'], $_SESSION['dadosSalvar']['origemScc'], $_SESSION['dadosSalvar']['citelpnuml']);
        //   var_dump($dadosItens);
            $db = conexao();
            for($i=0; $i<count($dadosItens); $i++){
                if(!is_null($dadosItens[$i]->cmatepsequ)){
                    $sql = 'SELECT  U.EUNIDMSIGL as unidade
                            FROM    SFPC.TBMATERIALPORTAL M, SFPC.TBUNIDADEDEMEDIDA U
                            WHERE   M.CMATEPSEQU = ' . $dadosItens[$i]->cmatepsequ . '
                                AND U.CUNIDMCODI = M.CUNIDMCODI ';

                    $res = executarSQL($db, $sql);
                    $res->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                    $dadosItens[$i]->eunidmsigl = $retorno->unidade;
                }
            }
            
        }
    }
    if($_SESSION['csolcosequ']){
        $_SESSION['visitouItem'] = true; //Esta sessão serve para nçao limpar os dados de fornecedor caso seja alternado para item.
    }
    ?>
    <html>
    <?php
    # Carrega o layout padrão #
    layout();
    ?>
    <script language="JavaScript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" type="">
        function Submete(Destino) {
            document.CadContratoIncluir.Destino.value = Destino;
            document.CadContratoIncluir.submit();
        }
        function AbreJanela(url,largura,altura) {
            window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
        }
        <?php MenuAcesso(); ?>
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadContratoIncluir.php" method="post" name="CadContratoIncluir">
        <input type="hidden" name="numcontrato" value="<?php echo !empty($_POST['numcontrato'])?$_POST['numcontrato']:'';?>">
        <input type="hidden" name="objeto" value="<?php echo !empty($_POST['objeto'])?$_POST['objeto']:'';?>">
        <input type="hidden" name="fieldConsorcio" value="<?php echo !empty($_POST['fieldConsorcio'])?$_POST['fieldConsorcio']:'';?>">
        <input type="hidden" name="fieldContinuo" value="<?php echo !empty($_POST['fieldContinuo'])?$_POST['fieldContinuo']:'';?>">
        <input type="hidden" name="obra" value="<?php echo !empty($_POST['obra'])?$_POST['obra']:'';?>">
        <input type="hidden" name="cmb_regimeExecucaoModoFornecimento1" value="<?php echo !empty($_POST['cmb_regimeExecucaoModoFornecimento1'])?$_POST['cmb_regimeExecucaoModoFornecimento1']:'';?>">
        <input type="hidden" name="opcaoExecucaoContrato" value="<?php echo !empty($_POST['opcaoExecucaoContrato'])?$_POST['opcaoExecucaoContrato']:'';?>">
        <input type="hidden" name="prazo" value="<?php echo !empty($_POST['prazo'])?$_POST['prazo']:'';?>">
        <input type="hidden" name="dataPublicacaoDom" value="<?php echo !empty($_POST['dataPublicacaoDom'])?$_POST['dataPublicacaoDom']:'';?>">
        <input type="hidden" name="vigenciaDataInicio" value="<?php echo !empty($_POST['vigenciaDataInicio'])?$_POST['vigenciaDataInicio']:'';?>">
        <input type="hidden" name="vigenciaDataTermino" value="<?php echo !empty($_POST['vigenciaDataTermino'])?$_POST['vigenciaDataTermino']:'';?>">
        <input type="hidden" name="execucaoDataInicio" value="<?php echo !empty($_POST['execucaoDataInicio'])?$_POST['execucaoDataInicio']:'';?>">
        <input type="hidden" name="execucaoDataTermino" value="<?php echo !empty($_POST['execucaoDataTermino'])?$_POST['execucaoDataTermino']:'';?>">
        <input type="hidden" name="comboGarantia" value="<?php echo !empty($_POST['comboGarantia'])?$_POST['comboGarantia']:'';?>">
        <input type="hidden" name="repNome" value="<?php echo !empty($_POST['repNome'])?$_POST['repNome']:'';?>">
        <input type="hidden" name="repCPF" value="<?php echo !empty($_POST['repCPF'])?$_POST['repCPF']:'';?>">
        <input type="hidden" name="repCargo" value="<?php echo !empty($_POST['repCargo'])?$_POST['repCargo']:'';?>">
        <input type="hidden" name="repRG" value="<?php echo !empty($_POST['repRG'])?$_POST['repRG']:'';?>">
        <input type="hidden" name="repRgOrgao" value="<?php echo !empty($_POST['repRgOrgao'])?$_POST['repRgOrgao']:'';?>">
        <input type="hidden" name="repRgUF" value="<?php echo !empty($_POST['repRgUF'])?$_POST['repRgUF']:'';?>">
        <input type="hidden" name="repCidade" value="<?php echo !empty($_POST['repCidade'])?$_POST['repCidade']:'';?>">
        <input type="hidden" name="repEstado" value="<?php echo !empty($_POST['repEstado'])?$_POST['repEstado']:'';?>">
        <input type="hidden" name="repNacionalidade" value="<?php echo !empty($_POST['repNacionalidade'])?$_POST['repNacionalidade']:'';?>">
        <input type="hidden" name="repEstCiv" value="<?php echo !empty($_POST['repEstCiv'])?$_POST['repEstCiv']:'';?>">
        <input type="hidden" name="repProfissao" value="<?php echo !empty($_POST['repProfissao'])?$_POST['repProfissao']:'';?>">
        <input type="hidden" name="repEmail" value="<?php echo !empty($_POST['repEmail'])?$_POST['repEmail']:'';?>">
        <input type="hidden" name="repTelefone" value="<?php echo !empty($_POST['repTelefone'])?$_POST['repTelefone']:'';?>">
        <input type="hidden" name="gestorNome" value="<?php echo !empty($_POST['gestorNome'])?$_POST['gestorNome']:'';?>">
        <input type="hidden" name="gestorMatricula" value="<?php echo !empty($_POST['gestorMatricula'])?$_POST['gestorMatricula']:'';?>">
        <input type="hidden" name="gestorCPF" value="<?php echo !empty($_POST['gestorCPF'])?$_POST['gestorCPF']:'';?>">
        <input type="hidden" name="gestorEmail" value="<?php echo !empty($_POST['gestorEmail'])?$_POST['gestorEmail']:'';?>">
        <input type="hidden" name="gestorTelefone" value="<?php echo !empty($_POST['gestorTelefone'])?$_POST['gestorTelefone']:'';?>">
        <br><br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Incluir
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="100"></td>
                <td align="left" colspan="2">
                    <?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }

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
                    <table  border="1px" bordercolor="#75ADE6" cellspacing="0" cellpadding="3" summary="" width="1024px">
                        <thead>
                            <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>INCLUIR CONTRATO</b>
                            </td>
                        </thead>
                        <tr>
                            <td class="textonormal">
                                <table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="left">
                                            <?php echo NavegacaoAbasIncluir(off,on); ?>

                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <td colspan="4">
                                                        <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                            <tbody>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DO DOCUMENTO</td>
                                                            </tr>
                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr class="head_principal">

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="35%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO ITEM </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO<BR>REDUZIDO </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> UND </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> QTD. </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR UNITARIO </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR TOTAL </td>

                                                                <!-- <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MARCA </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MODELO </td> -->

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> TIPO </td>

                                                            </tr>
                                                            <?php
                                                                $valorFullTotal = 0;
                                                                if($dadosItens){
                                                                    if($_SESSION['dadosSalvar']['origemScc'] == "LICITACAO"){
                                                                        foreach($dadosItens as $itens){ 
                                                                            $codItens = !empty($itens->cmatepsequ)?$itens->cmatepsequ:$itens->cservpsequ;
                                                                            $tipoGrupo = !empty($itens->cmatepsequ)?'M':"S";
                                                            ?>
                                                            <tr>
                                                                <td class="textonormal" align="center" style="text-align: center">
                                                                    <?php echo $itens->aitelporde;?>
                                                                </td>
                                                                <td class="textonormal">
                                                                        <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo $codItens;?>&amp;TipoGrupo=<?php echo  $tipoGrupo;?>&amp;ProgramaOrigem=',800,470);">
                                                                            <?php 
                                                                                if(!empty($itens->cmatepsequ)){
                                                                                    $descricaoMaterial = $ObjContrato->GetDescricaoMaterial($itens->cmatepsequ);
                                                                                    echo $descricaoMaterial[0]->ematepdesc;
                                                                                }
                                                                                if(!empty($itens->cservpsequ)){
                                                                                    $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->cservpsequ);
                                                                                    echo $descricaoServico[0]->eservpdesc;
                                                                                }

                                                                            ?>
                                                                        </a>
                                                                </td>

                                                                <td class="textonormal" align="center">
                                                                <?php echo empty($itens->cmatepsequ)?$itens->cservpsequ:$itens->cmatepsequ;?>
                                                                </td>

                                                                <td class="textonormal" align="center"  style="cursor: help">
                                                                    <?php echo !empty($itens->eunidmsigl)?$itens->eunidmsigl:"";?>
                                                                </td>
                                                                <td class="textonormal" align="center">
                                                                    <?php echo number_format($itens->aitelpqtso,4,',','.');?>
                                                                </td>
                                                                <td class="textonormal" align="center">
                                                                    <?php echo number_format($itens->vitelpvlog,4,',','.');?>

                                                                </td>
                                                                <!--  Coluna 7 = Situação-->
                                                                <td class="textonormal" style="text-align: center !important;">
                                                                    <?php  $ValorItem = $itens->aitelpqtso * $itens->vitelpvlog;
                                                                            echo number_format($ValorItem,4,',','.');
                                                                            $valorFullTotal += $ValorItem;
                                                                    ?>
                                                                </td>
                                                                <!-- <td class="textonormal" style="text-align: center !important;">
                                                                    <?php //echo $itens->eitelpmarc;?>
                                                                </td>
                                                                <td class="textonormal" style="text-align: center !important;">
                                                                    <?php //echo $itens->eitelpmode;?>
                                                                </td> -->
                                                                <td class="textonormal" style="text-align: center !important;">
                                                                <?php echo !empty($itens->cservpsequ)?"Serviço":"Material";?>
                                                                </td>
                                                            </tr>
                                                            <?php            }
                                                                    }else{
                                                                        foreach($dadosItens as $itens){ 
                                                                            $codItens = !empty($itens->cmatepsequ)?$itens->cmatepsequ:$itens->cservpsequ;
                                                                            $tipoGrupo = !empty($itens->cmatepsequ)?'M':"S";
                                                            ?>
                                                            <tr>
                                                                <td class="textonormal" align="center" style="text-align: center">
                                                                    <?php echo $itens->aitescorde;?>
                                                                </td>
                                                                <td class="textonormal">
                                                                     <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo $codItens;?>&amp;TipoGrupo=<?php echo  $tipoGrupo;?>&amp;ProgramaOrigem=',800,470);">
                                                                     <?php 
                                                                                if(!empty($itens->cmatepsequ)){
                                                                                    $descricaoMaterial = $ObjContrato->GetDescricaoMaterial($itens->cmatepsequ);
                                                                                    echo $descricaoMaterial[0]->ematepdesc;
                                                                                }
                                                                                if(!empty($itens->cservpsequ)){
                                                                                    $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->cservpsequ);
                                                                                    echo $descricaoServico[0]->eservpdesc;
                                                                                }

                                                                            ?>
                                                                    </a>
                                                                </td>  

                                                                <td class="textonormal" align="center">
                                                                    <?php echo empty($itens->cmatepsequ)?$itens->cservpsequ:$itens->cmatepsequ;?>
                                                                </td>

                                                                <td class="textonormal" align="center"  style="cursor: help">
                                                                <?php echo !empty($itens->eunidmsigl)?$itens->eunidmsigl:"";?>
                                                                </td>
                                                                <td class="textonormal" align="center">
                                                                    <?php echo number_format($itens->aitescqtso,4,',','.');?>
                                                                </td>
                                                                <td class="textonormal" align="center">
                                                                    <?php echo number_format($itens->vitescunit,4,',','.');?>

                                                                </td>
                                                                <!--  Coluna 7 = Situação-->
                                                                <td class="textonormal" style="text-align: center !important;">
                                                                <?php  $ValorItem = $itens->aitescqtso * $itens->vitescunit;
                                                                            echo number_format($ValorItem,4,',','.');
                                                                            $valorFullTotal += $ValorItem;
                                                                    ?> 
                                                                </td>
                                                                <!-- <td class="textonormal" style="text-align: center !important;">
                                                                    <?php //echo $itens->eitescmarc;?>
                                                                </td>
                                                                <td class="textonormal" style="text-align: center !important;">
                                                                    <?php //echo $itens->eitescmode;?>
                                                                </td> -->
                                                                <td class="textonormal" style="text-align: center !important;">
                                                                <?php echo !empty($itens->cservpsequ)?"Serviço":"Material";?>
                                                                </td>
                                                            </tr>
                                                            <?php            }
                                                                    }
                                                                }else{?>
                                                            <tr>
                                                                <td class="textonormal itens_material" colspan="7" style="color: red">
                                                                    Não há item associado ao contrato.
                                                                </td>
                                                            </tr>
                                                             <?php } ?>
                                                            <!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr>
                                                                <td class="textonormal" colspan="6" align="left">
                                                                    <b>VALOR TOTAL DOS ITENS DO CONTRATO</b>
                                                                </td>
                                                                <td class="textonormal" colspan="2" align="center">
                                                                    <?php echo number_format($valorFullTotal,4,',','.'); ?>
                                                                </td>
                                                            </tr>
                                                    
                                                          </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <input type="hidden" id="Destino" name="Destino">
                                </table>
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
    <?php
    exit;
}
?>