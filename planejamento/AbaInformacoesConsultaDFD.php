<?php
/**
 * Portal de Compras
 * Programa: AbaInformacoesConsultaDFD.php
 * Autor: João Madson
 * Data:  07/03/2023
 * Objetivo: Aba de amostragem das informações da DFD Selecioanada
 */

include "ClassPlanejamento.php";

//inicio da sessão para a página
session_start();

// A função abaixo é a montagem da tela na aba informações de ConsDFD.php
function ExibeAbaInformacoesDFD() {
    //monta variável do objeto que recebe a ClassPlanejamento
    $objPlanejamento = new Planejamento();

    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        $DFD = $_GET['dfdSelected'];
    } else if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $DFD = $_POST['dfdSelected'];
    }
    $compraCorporativa = $objPlanejamento->consultaCompraCorporativa();
    if (!empty($DFD) && empty($_SESSION['DFD'])) {
        $_SESSION['DFD'] = $dadosDFD = $objPlanejamento->consultaDFD($DFD);
        $_SESSION['agrupado'] = $agrupamento = $objPlanejamento->consultaDFDAgrupamento($dadosDFD->cpldfdsequ);
        if($dadosDFD->cpldfdsequ){
           
        }
        if($dadosDFD->cplvincodi){
            $DFDSVinculadas = $objPlanejamento->consultaDFDcodigoVinculo(null,$dadosDFD->cplvincodi);     
        }
        
        $_SESSION['vinculoDFDS'] =  $DFDSVinculadas;
        //pega o histórico da sessão sempre que recarregar a página
        $_SESSION['historico'] = $objPlanejamento->consultaHistorico($DFD);
        $_SESSION['ultimoResp'] = $ultimoResp = $objPlanejamento->consultaUltimoHistUsuResp($DFD);
        $_SESSION['itens'] = $itens = $objPlanejamento->consultaItens($DFD);

    } else if (!empty($_SESSION['DFD'])) {
        $agrupamento = $_SESSION['agrupado'];
        $ultimoResp = $_SESSION['ultimoResp'];
        $dadosDFD = $_SESSION['DFD'];
        $itens = $_SESSION['itens'];
    }

    $matOuServ =  !empty($itens[0]->cmatepsequ)? "M":"S";

 ?>
    <html>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" type="">

    function Submete(Destino) {
        document.ConsDFD.Destino.value = Destino;
        document.ConsDFD.submit();
    }
       
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">

    <style>
        #labels{
            width: 250px;
            background-color:#DCEDF7;
        }
    </style>

    <!-- <body background="../midia/bg.gif" marginwidth="0" marginheight="0"> -->
    <!-- <script language="JavaScript" src="../menu.js"></script> -->
    <script language="JavaScript">Init();</script>
    <form action="ConsultaDFD.php" method="post" id="formAbaInformacoes" name="ConsDFD">
    <input type="hidden" name="op" id="op" value="">   
    <input type="hidden" name="Destino" id="Destino" value="">
    <input type="hidden" name="dfdSelected" id="dfdSelected" value="<?php echo $DFD;?>">
    <!-- <br><br><br><br> -->
    <table cellpadding="0" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <!-- <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td> -->
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Consultar
            </td>
        </tr>
        <!-- Fim do Caminho-->
        
        <!-- Erro -->
        <tr>
            <td width="150"></td>
            <td align="left" colspan="2" id="tdmensagem">
                <div class="mensagem">
                    <div class="error">
                    Erro
                    </div>
                    <span class="mensagem-texto">
                    </span>
                </div>
            </td>
        </tr>
        <!-- Fim do Erro -->

        <!-- loading -->
        <tr>
            <td width="150"></td>
            <td align="left" colspan="2" id="tdload" style="display:none;">
                <div class="load" id="load"> 
                    <div class="load-content" >
                    <img src="../midia/loading.gif" alt="Carregando">
                    <spam>Carregando...</spam>
                    </div>
                </div> 
            </td>
        </tr>
        <!-- Fim do loading -->

        <!-- Corpo -->
        <tr>
            <!-- <td width="100" display="none"></td> -->
            <td class="textonormal">
                <table   border="1" bordercolor="#75ADE6" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                    <tr>
                        <td class="textonormal">
                            <table cellpadding="3" cellspacing="0" summary="" class="textonormal" bgcolor="#FFFFFF" width="1024px">
                                <thead colspan="3" >
                                    <?php echo NavegacaoAbasConsultaDFD(on,off); ?>
                                </thead>
                                <tr>
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>CONSULTA - DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA (DFD)</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                        <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                            <tr bgcolor="#bfdaf2">                                                    
                                                <!-- <td colspan="4"> -->    
                                                <table class="textonormal" id="scc_material" summary="" width="100%">
                                                    <!-- style="border: 1px solid #75ade6; border-radius: 4px;" -->
                                                    <tbody>                                                               
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Ano do PCA
                                                            </td>
                                                            <td>
                                                                <span><?php echo $dadosDFD->apldfdanod;?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Número do DFD
                                                            </td>
                                                            <td>
                                                                <span><?php echo $dadosDFD->cpldfdnumf;?></span>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        if(!empty($agrupamento->cplagdsequ)){
                                                            echo '<tr>
                                                                    <td class="textonormal" id="labels">
                                                                        DFD Agrupado
                                                                    </td>
                                                                    <td>
                                                                        <span>
                                                                            SIM
                                                                        </span>
                                                                    </td>
                                                                </tr>';
                                                            echo '<tr>
                                                                    <td class="textonormal" id="labels">
                                                                        Motivo do Agrupamento
                                                                    </td>
                                                                    <td>
                                                                        <span style="text-transform: uppercase;">'.$agrupamento->eplagdmoti.'</span>
                                                                    </td>
                                                                </tr>';
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Área Requisitante
                                                            </td>
                                                            <td>
                                                                <span id="areaReq"><?php echo $dadosDFD->descorgao;?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                CNPJ
                                                            </td>
                                                            <td>
                                                                <span name="cnpjAreaReq" id="cnpjAreaReq">
                                                                <?php echo $objPlanejamento->MascarasCPFCNPJ($dadosDFD->cnpjorgao);?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Classe
                                                            </td>
                                                            <td>
                                                                <span><?php echo "$dadosDFD->descclasse"; ?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                            Descrição Sucinta da Demanda
                                                            </td>
                                                            <td style="text-transform: uppercase;">
                                                                <span style="text-transform: uppercase;"><?php echo $dadosDFD->epldfddesc;?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Justificativa da Necessidade de Contratação
                                                            </td>
                                                            <td>
                                                                <span style="text-transform: uppercase;"><?php echo $dadosDFD->epldfdjust;?></span>    
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Estimativa Preliminar do Valor da Contratação
                                                            </td>
                                                            <td>
                                                                <span> <?php echo number_format($dadosDFD->cpldfdvest, 4, ',', '.');?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Tipo de Processo de Contratação
                                                            </td>
                                                            <td>
                                                                <span><?php if($dadosDFD->fpldfdtpct){ echo ($dadosDFD->fpldfdtpct == "D")? "CONTRATAÇÃO DIRETA": "LICITAÇÃO";}?></span>  
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Data Estimada para Conclusão
                                                            </td>
                                                            <td>
                                                                <span><?php if(date('d/m/Y', strtotime($dadosDFD->dpldfdpret)) != '01/01/1970'){echo date('d/m/Y', strtotime($dadosDFD->dpldfdpret));}?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Grau de Prioridade
                                                            </td>
                                                            <td>
                                                                <span><?php 
                                                                    if($dadosDFD->fpldfdgrau == 1){
                                                                        echo "ALTO";
                                                                    }else if($dadosDFD->fpldfdgrau == 2){
                                                                        echo "MÉDIO";
                                                                    }else if($dadosDFD->fpldfdgrau == 3){
                                                                        echo "BAIXO";
                                                                    }
                                                                ?></span>
                                                            </td>
                                                        </tr>
                                                        <!-- PRIORIDADE ALTA -->
                                                        <!-- ESSE CAMPO APARECERÁ SE O GRAU DE PRIORIDADE FOR ALTO -->
                                                        <tr id="trJustPrioridade" style="<?php echo ($dadosDFD->fpldfdgrau == "1")? "":"display:none;";?>">
                                                            <td class="textonormal" id="labels" >
                                                                Justificativa para prioridade alta
                                                            </td>
                                                            <td>
                                                                <span style="text-transform: uppercase;"><?php echo $dadosDFD->epldfdjusp;?></span>    
                                                            </td>
                                                        </tr>
                                                        <!-- FIM PRIORIDADE ALTA -->
                                                        <!-- <tr>
                                                            <td class="textonormal" id="labels">
                                                            DFDs vinculados
                                                            </td>
                                                            <td>
                                                                <span> <?php// echo $dadosDFD;?></span> 
                                                            </td>
                                                        </tr> -->
                                                        <?php if($dadosDFD->corglicodi==$compraCorporativa->corglicodi){?>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Compra Corporativa
                                                            </td>
                                                            <td>
                                                                <span><?php echo ($dadosDFD->fpldfdcorp == "S")? "SIM" : "NÃO";?></span> 
                                                            </td>
                                                        </tr>
                                                        <?php }?>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Situação do DFD
                                                            </td>
                                                            <td>
                                                                <span><?php echo $dadosDFD->eplsitnome;?></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="textonormal" id="labels">
                                                                Usuário responsável pela última alteração
                                                            </td>
                                                            <td>
                                                                <span><?php echo $ultimoResp[0]->eusuporesp ;?></span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <?php if($_SESSION['vinculoDFDS']){ ?>
                                                    <table id="vincularDFD" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                        <tbody>
                                                        <tr>
                                                            <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle"> DFDS VINCULADOS</td>
                                                        </tr>
                                                        <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                        <tr class="head_principal">
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="12%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> NÚMERO DO DFD</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ANO</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CLASSE</td>
                                                                <td class="textonormal" align="center" bgcolor="#DCEDF7" width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> SITUAÇÃO</td>
                                                         
                                                        </tr>
                                                        <?php foreach($_SESSION['vinculoDFDS'] as $dfd){
                                                            if($dfd->cpldfdsequ != $dadosDFD->cpldfdsequ){
                                                            echo '<tr class="head_principal">  
                                                                        <td class="textonormal" align="center"  width="12%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->cpldfdnumf.'</td>
                                                                        <td class="textonormal" align="center"  width="5%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->apldfdanod.'</td>
                                                                        <td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->descclasse.'</td>
                                                                        <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" bgcolor="#DCEDF7" border="0" height="1px"/> <br />'.$dfd->eplsitnome.'</td>                                                                    
                                                                    </tr>';
                                                            }
                                                        }
                                                                ?>
                                                        </tbody>
                                                    </table>
                                                    <?php }?>
                                                    <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DE MATERIAS DA CLASSE</td>
                                                            </tr>
                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr class="head_principal">

                                                                <!-- <td class="textoabason" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td> -->
                                                                
                                                                <?php if($matOuServ=="M"){
                                                                echo '
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ORD. </td>
                                                                
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO DO MATERIAL </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO MATERIAL </td>';
                                                                }else{
                                                                echo '
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> ORD. </td>
                                                                
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="35%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESCRIÇÃO DO SERVIÇO </td>

                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> CÓDIGO SERVIÇO </td>';
                                                                }?>
                                                            </tr>
                                                            <?php 
                                                            if(!empty($itens)){
                                                                for($i=0; $i<count($itens); $i++){
                                                                    $html = '<tr class="head_principal">

                                                                        <td class="textonormal" align="center" style="width:5%;" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /><spam>'.$itens[$i]->cplitecodi.'</spam></td>';
                                                                    if(!empty($itens[$i]->ematepdesc)){
                                                                        $html .= '<td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br />'.$itens[$i]->ematepdesc.'</td>

                                                                                  <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br />'.$itens[$i]->cmatepsequ.'</td>';                                                                   
                                                                    }else{
                                                                        $html .= '<td class="textonormal" align="center"  width="35%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br />'.$itens[$i]->eservpdesc.'</td>

                                                                                  <td class="textonormal" align="center"  width="10%" ><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br />'.$itens[$i]->cservpsequ.'</td>';                                                                    
                                                                    }    
                                                                       
                                                                    $html .= ' </tr>';
                                                                    echo $html;
                                                                }
                                                                
                                                            }else{
                                                            echo '<tr>
                                                                    <td class="textonormal" colspan="7" align="left">
                                                                        Nenhum item de material informado
                                                                    </td>
                                                                </tr>';
                                                            }?>
                                                        </tbody>
                                                    </table>
                                                    <tr>
                                                        <!-- <td colspan="8"> -->
                                                            <!-- <a type="button" class="botao" style="float:right; width:50px; text-align:center" href="ConsPesquisarDFD.php">Voltar</a> -->
                                                        <!-- </td> -->
                                                    </tr>
                                                </table>
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
        <div class="modal" id="modal"> 
            <div class="modal-content" >
            
            </div>
        </div> 
    </table>    

    </html>
<?php
}
?>