<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadItemContratoManter.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaItemContratoManter.php
# Autor:    Marcello Albuquerque
# Data:     28/04/2021
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------

# Exibe Aba Fornecedor - Formulário B #

if(!empty($_POST['seqscc'])){
    function ExibeAbaItemContratoManter(){
        $ObjContrato = new ContratoManter();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $idRegistro = $_POST['idregistro'];
            if(!empty($_SESSION['dadosSalvar']['csolcosequ']) && !empty($_SESSION['dadosSalvar']['aforcrsequ']) && !empty($_SESSION['dadosSalvar']['origemScc']) && !empty($_SESSION['citelpnuml'])){
                $dadosItensContrato = $ObjContrato->PesquisaItensSCC($_SESSION['dadosSalvar']['csolcosequ'], $_SESSION['dadosSalvar']['aforcrsequ'], $_SESSION['dadosSalvar']['origemScc'], $_SESSION['citelpnuml']);
            }else{
                $dadosItensContrato = $ObjContrato->GetItensContrato($idRegistro);
            }
            //precisa saber quando fazer essa pesquisa.
            //precisa trazer as entradas da func.
            //precisa adaptar na mostragem de tela e processo de salvar.
            
            
            $db = conexao();
            for($i=0; $i<count($dadosItensContrato); $i++){
                $cmatepsequ = empty($dadosItensContrato[$i]->codreduzidomat)?$dadosItensContrato[$i]->cmatepsequ:$dadosItensContrato[$i]->codreduzidomat;
                if(!is_null($cmatepsequ)){
                    $sql = 'SELECT  U.EUNIDMSIGL as unidade
                            FROM    SFPC.TBMATERIALPORTAL M, SFPC.TBUNIDADEDEMEDIDA U
                            WHERE   M.CMATEPSEQU = ' . $cmatepsequ . '
                                AND U.CUNIDMCODI = M.CUNIDMCODI ';

                    $res = executarSQL($db, $sql);
                    $res->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                    $dadosItensContrato[$i]->unidade = $retorno->unidade;
                }
            }
            $descricaoMaterial = "";
            $descricaoServico ="";
        }
        ?>
        <html>
        <?php
        # Carrega o layout padrão #
        layout();
        ?>
        <script language="JavaScript" src="../janela.js" type="text/javascript"></script>
        <script language="javascript" type="">
            <!--
            function Submete(Destino) {
                document.CadContratoManter.Destino.value = Destino;
                document.CadContratoManter.submit();
            }
            function enviar(valor){
                document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
                document.CadPregaoPresencialSessaoPublica.submit();
            }
            function AbreJanela(url,largura,altura) {
                window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
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
        <form action="CadContratoManter.php" method="post" name="CadContratoManter">
            <input type="hidden" name="idregistro" value="<?php echo $idRegistro;?>">
            <br><br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Manter
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
                        <table  border="0" cellspacing="0" cellpadding="3" summary="" width="1024px">
                            <tr>
                                <td class="textonormal">
                                    <table border="1px" bordercolor="#75ADE6" cellpadding="3" cellspacing="0" summary="" class="textonormal" bgcolor="#FFFFFF">
                                        <thead colspan="3" class="titulo3 itens_material" align="center"  bgcolor="#75ADE6" valign="middle">
                                            <td> 
                                                MANTER CONTRATO
                                            </td>
                                        </thead>
                                        <tr>
                                            <td align="left">
                                                <?php echo NavegacaoAbasManter(off,on); ?>

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

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESC ITEM </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="25%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> COD REDUZIDO </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> UND </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="23%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> QTD. </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR UNITARIO </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR TOTAL </td>

                                                                    <!-- <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MARCA </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MODELO </td> -->

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> TIPO </td>

                                                                </tr>
                                                                    <!-- Dados MEMBRO DE COMISSÃO  -->
                                                            <?php 
                                                                $valorFullTotal = 0;
                                                                if(!empty($dadosItensContrato)) {   
                                                                    foreach($dadosItensContrato as $itens){
                                                                           $codeMaterial = !empty($itens->codreduzidomat)?$itens->codreduzidomat:$itens->codreduzidoserv;
                                                                           $tipoGrupo = !empty($itens->codreduzidomat)?'M':"S";
                                                               
                                                            ?>
                                                                <tr>
                                                                    <td class="textonormal" align="center" style="text-align: center">
                                                                        <?php echo empty($itens->ord)?$itens->aitelporde:$itens->ord;?>
                                                                    </td>
                                                                    <td class="textonormal">
                                                                        <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo $codeMaterial;?>&amp;TipoGrupo=<?php echo  $tipoGrupo;?>&amp;ProgramaOrigem=',800,470);">
                                                                                    <?php 
                                                                                        if(!empty($itens->codreduzidomat)){
                                                                                            $descricaoMaterial = $ObjContrato->GetDescricaoMaterial($itens->codreduzidomat);
                                                                                            echo $descricaoMaterial[0]->ematepdesc;
                                                                                        }elseif(!empty($itens->cmatepsequ)){
                                                                                            $descricaoMaterial = $ObjContrato->GetDescricaoMaterial($itens->cmatepsequ);
                                                                                            echo $descricaoMaterial[0]->ematepdesc;
                                                                                        }
                                                                                        if(!empty($itens->codreduzidoserv)){
                                                                                            $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->codreduzidoserv);
                                                                                            echo $descricaoServico[0]->eservpdesc;
                                                                                        }elseif(!empty($itens->cservpsequ)){
                                                                                            $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->cservpsequ);
                                                                                            echo $descricaoServico[0]->eservpdesc;
                                                                                        }

                                                                                    ?>
                                                                            </a>
                                                                    </td>

                                                                    <td class="textonormal" align="center">
                                                                    <?php 
                                                                        if($itens->cmatepsequ || $itens->cservpsequ){
                                                                            echo !empty($itens->cservpsequ)?$itens->cservpsequ:$itens->cmatepsequ;
                                                                        }else{
                                                                            echo !empty($itens->codreduzidoserv)?$itens->codreduzidoserv:$itens->codreduzidomat;
                                                                        }
                                                                    ?>
                                                                    </td>

                                                                    <td class="textonormal" align="center"  style="cursor: help">
                                                                    <?php echo !empty($itens->unidade)?$itens->unidade:"";?>
                                                                    </td>
                                                                    <td class="textonormal" align="center">
                                                                        <?php
                                                                            if($itens->aitelpqtso){
                                                                                echo number_format($itens->aitelpqtso,4,',','.');
                                                                            }else{
                                                                                echo number_format($itens->qtd,4,',','.');
                                                                            }
                                                                        ?>
                                                                    </td>
                                                                    <td class="textonormal" align="center">
                                                                        <?php 
                                                                        if($itens->vitelpvlog){
                                                                            echo number_format($itens->vitelpvlog,4,',','.');
                                                                        }else{
                                                                            echo number_format($itens->valorunitario,4,',','.');
                                                                        }
                                                                        ?>

                                                                    </td>
                                                                    <!--  Coluna 7 = Situação-->
                                                                    <td class="textonormal" style="text-align: center !important;">
                                                                        <?php 
                                                                            if($itens->aitelpqtso && $itens->vitelpvlog){
                                                                                $valor = floatval($itens->aitelpqtso) * floatval($itens->vitelpvlog);
                                                                            }else{
                                                                                $valor = floatval($itens->qtd) * floatval($itens->valorunitario);
                                                                            }
                                                                            $valorFullTotal += $valor;
                                                                            echo number_format($valor,4,',','.');
                                                                        ?>
                                                                    </td>
                                                                    <!-- <td class="textonormal" style="text-align: center !important;">
                                                                        <?php echo $itens->marca;?>
                                                                    </td>
                                                                    <td class="textonormal" style="text-align: center !important;">
                                                                        <?php echo $itens->modelo;?>
                                                                    </td> -->
                                                                    <td class="textonormal" style="text-align: center !important;">
                                                                    <?php echo !empty($itens->codreduzidoserv)?"Serviço":"Material";?>
                                                                    </td>
                                                                </tr>
                                                            <?php       }
                                                                }else{
                                                            ?>
                                                                <tr>
                                                                    <td class="textonormal itens_material" colspan="7" style="color: red">
                                                                            Não há item associado ao contrato
                                                                    </td>
                                                                </tr>
                                                                <?php } ?>
                                                                <!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                                <tr>
                                                                    <td class="textonormal" colspan="1" align="center">
                                                                        Total
                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">
                                                                        
                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">
                                                                        <?php echo number_format($valorFullTotal,4,',','.'); ?>
                                                                    </td>
                                                                    <!-- <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td> -->
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>

                                                                </tr>
                                                            </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- <tr> -->
                                            <!-- <td colspan="4" align="right"> -->
                                                <!-- <input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('B');"> -->
                                                <input type="hidden" name="Botao" value="">
                                                <input type="hidden" name="Origem" value="B">
                                                <input type="hidden" name="Destino">
                                            <!-- </td> -->
                                        <!-- </tr> -->
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
}else{
    function ExibeAbaItemContratoManter(){
        $_SESSION['visitouItem'] = true;
        if(!empty($_SESSION['itemincluir'])){
           for($i=0; $i<count($_SESSION['itemincluir']);$i++){
                $itemIncluir[$i] = explode('Æ', $_SESSION['itemincluir'][$i]);
           }
           unset($_SESSION['itemincluir']);
        //    unset($_SESSION['manterItensZerados']);
        } 
        $ObjContrato = new ContratoManter();
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $InicioPrograma              = $_POST['InicioPrograma'];
            $idRegistro = $_POST['idregistro'];
            if(!empty($_SESSION['dadosItensContrato']) &&  $_POST['Botao'] == "Remover"){
                $arrayAux = array();
                $j=0;
                for($i=0;$i<count($_SESSION['dadosItensContrato']);$i++){
                    if(is_null($_POST['itemCheck'.$_SESSION['dadosItensContrato'][$i]->ord])){
                        $arrayAux[$j] = $_SESSION['dadosItensContrato'][$i];
                        $arrayAux[$j]->ord = $j+1;
                        $j++;
                    }
                }
                // var_dump($arrayAux);
                $_SESSION['dadosItensContrato'] = $arrayAux;
                $dadosItensContrato = $_SESSION['dadosItensContrato'];
                $removido = true;
            }
            if(!$dadosItensContrato && !$removido){
                if($_SESSION['manterItensZerados'] != true){
                    $dadosItensContrato = $ObjContrato->GetItensContrato($idRegistro);
                    $db = conexao();
                    for($i=0; $i<count($dadosItensContrato); $i++){
                        if(!is_null($dadosItensContrato[$i]->codreduzidomat)){
                            $sql = 'SELECT  U.EUNIDMSIGL as unidade
                                    FROM    SFPC.TBMATERIALPORTAL M, SFPC.TBUNIDADEDEMEDIDA U
                                    WHERE   M.CMATEPSEQU = ' . $dadosItensContrato[$i]->codreduzidomat . '
                                        AND U.CUNIDMCODI = M.CUNIDMCODI ';

                            $res = executarSQL($db, $sql);
                            $res->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                            $dadosItensContrato[$i]->unidade = $retorno->unidade;
                        }
                    }
                }
                if($_SESSION['dadosItensContrato']){
                    $flagCampPost   = $_POST['flagCampPost'];
                    $ordCampoPost = $_POST['ordCampoPost'];
                    if(!empty($flagCampPost) && !empty($ordCampoPost)){

                        for($i=0;$i<count($_SESSION['dadosItensContrato']);$i++){
                            if($_SESSION['dadosItensContrato'][$i]->ord == $ordCampoPost){
                                if($flagCampPost == 'qtditem'){
                                    $_SESSION['dadosItensContrato'][$i]->qtd = $ObjContrato->floatvalue($_POST['qtditem'.$ordCampoPost]);
                                }else{
                                    $_SESSION['dadosItensContrato'][$i]->valorunitario = $ObjContrato->floatvalue($_POST['valUnitItem'.$ordCampoPost]);
                                }
                            }
                        }
                    }
                    $dadosItensContrato = $_SESSION['dadosItensContrato'];
                }else{
                    $_SESSION['dadosItensContrato'] = $dadosItensContrato;
                }
            }
            if(!empty($itemIncluir)){
                $cont = count($dadosItensContrato);
                $ultimaOrdem = $dadosItensContrato[$cont-1]->ord; //Pega o ultimo item da lista para pegar a ordem
                $nextOrd = $ultimaOrdem + 1;
                $conDItens = count($dadosItensContrato);
                for($i=0; $i<count($itemIncluir); $i++){
                    $dadosItensContrato[$conDItens]->ord = $nextOrd;
                    if($itemIncluir[$i][3] == "M"){
                        $dadosItensContrato[$conDItens]->codreduzidomat  = $itemIncluir[$i][1];  
                    }else{
                        $dadosItensContrato[$conDItens]->codreduzidoserv = $itemIncluir[$i][1];
                    }
                    $conDItens++;
                    $nextOrd++;
                }
                $_SESSION['dadosItensContrato'] = $dadosItensContrato;
            }
             // $dadosItensContrato //pegar campos editados;
             $dadosMateriais = array();
             $dadosServico = array();
             $imat = 0; $iserv = 0;
             for($i=0;$i<count($dadosItensContrato);$i++){
                 if(!empty($dadosItensContrato[$i]->codreduzidomat)){
                     $dadosMateriais[$imat] = $dadosItensContrato[$i];
                     $imat++;
                 }else{
                     $dadosServico[$iserv] = $dadosItensContrato[$i];
                     $iserv++;
                 }
             }
            $descricaoMaterial = "";
            $descricaoServico = "";
        }
        ?>
        <html>
        <?php
        # Carrega o layout padrão #
        layout();
        ?>
        <script language="JavaScript" src="../janela.js" type="text/javascript"></script>
        <script language="javascript" type="">
            <!--
            function Submete(Destino) {
                document.CadContratoManter.Destino.value = Destino;
                document.CadContratoManter.submit();
            }
            function enviar(valor){
                document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
                document.CadPregaoPresencialSessaoPublica.submit();
            }
            function recharge(botao, flag, ord){
                document.CadContratoManter.Botao.value = botao;
                document.CadContratoManter.flagCampPost.value = flag;
                document.CadContratoManter.ordCampoPost.value = ord;
                document.CadContratoManter.Destino.value = "B";
                document.CadContratoManter.submit();
            }
            function AbreJanela(url,largura,altura) {
                window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
            }
            function AbreJanelaItem(url,largura,altura){
                window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
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
        <form action="CadContratoManter.php" method="post" name="CadContratoManter">
            <input type="hidden" name="idregistro" value="<?php echo $idRegistro;?>">
            <br><br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Manter
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
                        <table  border="0" cellspacing="0" cellpadding="3" summary="" width="1024px">
                            <tr>
                                <td class="textonormal">
                                    <table border="1px" bordercolor="#75ADE6" cellpadding="3" cellspacing="0" summary="" class="textonormal" bgcolor="#FFFFFF">
                                        <thead colspan="3" class="titulo3 itens_material" align="center"  bgcolor="#75ADE6" valign="middle">
                                            <td> 
                                                MANTER CONTRATO
                                            </td>
                                        </thead>
                                        <tr>
                                            <td align="left">
                                                <?php echo NavegacaoAbasManter(off,on); ?>

                                                <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                    <tr bgcolor="#bfdaf2">
                                                        <td colspan="4">
                                                            <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                                <tbody>
                                                                <tr>
                                                                    <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DA SOLICITAÇÃO DE MATERIAL</td>
                                                                </tr>
                                                                <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                                <tr class="head_principal">

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESC ITEM </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="25%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> COD REDUZIDO </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> UND </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="23%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> QTD. </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR UNITARIO </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR TOTAL </td>

                                                                    <!-- <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MARCA </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MODELO </td> -->

                                                                    <!-- <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> TIPO </td> -->

                                                                </tr>
                                                                    <!-- Dados MEMBRO DE COMISSÃO  -->
                                                            <?php 
                                                                $valorFullTotal = 0;
                                                                if(!empty($dadosMateriais)) {   
                                                                    foreach($dadosMateriais as $itens){
                                                                                    $codeMaterial = !empty($itens->codreduzidomat)?$itens->codreduzidomat:$itens->codreduzidoserv;
                                                                                    $tipoGrupo = !empty($itens->codreduzidomat)?'M':"S";
                                                                        ?>
                                                                <tr>
                                                                    <td class="textonormal" align="center" style="text-align: center">
                                                                        <?php echo $itens->ord;?>
                                                                    </td>
                                                                    <td class="textonormal">
                                                                        <input type="checkbox" class="dinheiro4casas" name="<?php echo "itemCheck".$itens->ord;?>" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> value="<?php echo $itens->ord;?>"> 
                                                                        <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo $codeMaterial;?>&amp;TipoGrupo=<?php echo  $tipoGrupo;?>&amp;ProgramaOrigem=',800,470);">
                                                                                    <?php 
                                                                                        if(!empty($itens->codreduzidomat)){
                                                                                            $descricaoMaterial = $ObjContrato->GetDescricaoMaterial($itens->codreduzidomat);
                                                                                            echo $descricaoMaterial[0]->ematepdesc;
                                                                                        }
                                                                                        if(!empty($itens->codreduzidoserv)){
                                                                                            $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->codreduzidoserv);
                                                                                            echo $descricaoServico[0]->eservpdesc;
                                                                                        }

                                                                                    ?>
                                                                            </a>
                                                                    </td>

                                                                    <td class="textonormal" align="center">
                                                                    <?php echo !empty($itens->codreduzidoserv)?$itens->codreduzidoserv:$itens->codreduzidomat;?>
                                                                    </td>

                                                                    <td class="textonormal" align="center"  style="cursor: help">
                                                                    <?php echo !empty($itens->unidade)?$itens->unidade:"";?>
                                                                    </td>
                                                                    <td class="textonormal" align="center">
                                                                        <input type="text" class="dinheiro4casas" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> name="<?php echo "qtditem".$itens->ord;?>" value="<?php echo number_format($itens->qtd,4,',','.');?>" onblur="javascript:recharge('', 'qtditem', <?php echo $itens->ord;?>);">
                                                                    </td>
                                                                    <td class="textonormal" align="center">
                                                                        <input type="text" class="dinheiro4casas" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> name="<?php echo "valUnitItem".$itens->ord;?>" value="<?php echo number_format($itens->valorunitario,4,',','.');?>" onblur="javascript:recharge('', 'valUnitItem', <?php echo $itens->ord;?>);">

                                                                    </td>
                                                                    <!--  Coluna 7 = Situação-->
                                                                    <td class="textonormal" style="text-align: center !important;">
                                                                        <?php 
                                                                                $valor = floatval($itens->qtd) * floatval($itens->valorunitario);
                                                                                $valorFullTotal += $valor;
                                                                                echo number_format($valor,4,',','.');
                                                                        ?>
                                                                    </td>
                                                                    <!-- <td class="textonormal" style="text-align: center !important;">
                                                                        <?php //echo $itens->marca;?>
                                                                    </td>
                                                                    <td class="textonormal" style="text-align: center !important;">
                                                                        <?php //echo $itens->modelo;?>
                                                                    </td> -->
                                                                    <!-- <td class="textonormal" style="text-align: center !important;">
                                                                    <?php //echo !empty($itens->codreduzidoserv)?"Serviço":"Material";?>
                                                                    </td> -->
                                                                </tr>
                                                            <?php       }
                                                                }else{
                                                            ?>
                                                                <tr>
                                                                    <td class="textonormal itens_material" colspan="7" style="color: red">
                                                                        Nenhum item de material informado
                                                                    </td>
                                                                </tr>
                                                                <?php } ?>
                                                                <!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                                <tr>
                                                                    <td class="textonormal" colspan="6" style="font-weight: bold;" align="left">
                                                                        VALOR TOTAL DA SOLICITAÇÃO DE MATERIAL
                                                                    </td>
                                                                    <!-- <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">
                                                                        
                                                                    </td> -->
                                                                    <td class="textonormal" colspan="1" align="center">
                                                                        <?php echo number_format($valorFullTotal,4,',','.'); ?>
                                                                    </td>
                                                                    <!-- <td class="textonormal" colspan="1" align="center">

                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">

                                                                    </td> -->
                                                                    <!-- <td class="textonormal" colspan="1" align="center"> -->

                                                                    </td>

                                                                </tr>
                                                            </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr bgcolor="#bfdaf2">
                                                        <td colspan="4">
                                                            <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                                <tbody>
                                                                <tr>
                                                                    <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DA SOLICITAÇÃO DE SERVIÇO</td>
                                                                </tr>
                                                                <!-- Headers ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                                                                <tr class="head_principal">

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="7%"><br /> ORD </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> DESC ITEM </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="25%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> COD REDUZIDO </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="23%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> QTD. </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR UNITARIO </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> VALOR TOTAL </td>

                                                                    <!-- <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MARCA </td>

                                                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> MODELO </td> -->

                                                                    <!-- <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%"><img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> TIPO </td> -->

                                                                </tr>
                                                                    <!-- Dados MEMBRO DE COMISSÃO  -->
                                                            <?php 
                                                                $valorFullTotal = 0;
                                                                if(!empty($dadosServico)) {   
                                                                    foreach($dadosServico as $itens){
                                                                                    $codeMaterial = !empty($itens->codreduzidomat)?$itens->codreduzidomat:$itens->codreduzidoserv;
                                                                                    $tipoGrupo = !empty($itens->codreduzidomat)?'M':"S";
                                                                        ?>
                                                                <tr>
                                                                    <td class="textonormal" align="center" style="text-align: center">
                                                                        <?php echo $itens->ord;?>
                                                                    </td>
                                                                    <td class="textonormal">
                                                                        <input type="checkbox" class="dinheiro4casas" name="<?php echo "itemCheck".$itens->ord;?>" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> value="<?php echo $itens->ord;?>"> 
                                                                        <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo $codeMaterial;?>&amp;TipoGrupo=<?php echo  $tipoGrupo;?>&amp;ProgramaOrigem=',800,470);">
                                                                                    <?php 
                                                                                        if(!empty($itens->codreduzidomat)){
                                                                                            $descricaoMaterial = $ObjContrato->GetDescricaoMaterial($itens->codreduzidomat);
                                                                                            echo $descricaoMaterial[0]->ematepdesc;
                                                                                        }
                                                                                        if(!empty($itens->codreduzidoserv)){
                                                                                            $descricaoServico = $ObjContrato->GetDescricaoServicos($itens->codreduzidoserv);
                                                                                            echo $descricaoServico[0]->eservpdesc;
                                                                                        }

                                                                                    ?>
                                                                            </a>
                                                                    </td>

                                                                    <td class="textonormal" align="center">
                                                                    <?php echo !empty($itens->codreduzidoserv)?$itens->codreduzidoserv:$itens->codreduzidomat;?>
                                                                    </td>

                                                                    <!-- <td class="textonormal" align="center"  style="cursor: help">
                                                                    <?php // echo !empty($itens->codreduzidoserv)?"":'und';?>
                                                                    </td> -->
                                                                    <td class="textonormal" align="center">
                                                                        <input type="text" class="dinheiro4casas" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> name="<?php echo "qtditem".$itens->ord;?>" value="<?php echo number_format($itens->qtd,4,',','.');?>" onblur="javascript:recharge('', 'qtditem', <?php echo $itens->ord;?>);">
                                                                    </td>
                                                                    <td class="textonormal" align="center">
                                                                        <input type="text" class="dinheiro4casas" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> name="<?php echo "valUnitItem".$itens->ord;?>" value="<?php echo number_format($itens->valorunitario,4,',','.');?>" onblur="javascript:recharge('', 'valUnitItem', <?php echo $itens->ord;?>);">

                                                                    </td>
                                                                    <!--  Coluna 7 = Situação-->
                                                                    <td class="textonormal" style="text-align: center !important;">
                                                                        <?php 
                                                                                $valor = floatval($itens->qtd) * floatval($itens->valorunitario);
                                                                                $valorFullTotal += $valor;
                                                                                echo number_format($valor,4,',','.');
                                                                        ?>
                                                                    </td>
                                                                    <!-- <td class="textonormal" style="text-align: center !important;">
                                                                        <?php// echo $itens->marca;?>
                                                                    </td>
                                                                    <td class="textonormal" style="text-align: center !important;">
                                                                        <?php// echo $itens->modelo;?>
                                                                    </td> -->
                                                                    <!-- <td class="textonormal" style="text-align: center !important;">
                                                                    <?php// echo !empty($itens->codreduzidoserv)?"Serviço":"Material";?>
                                                                    </td> -->
                                                                </tr>
                                                            <?php       }
                                                                }else{
                                                            ?>
                                                                <tr>
                                                                    <td class="textonormal itens_material" colspan="7" style="color: red">
                                                                        Nenhum item de serviço informado
                                                                    </td>
                                                                </tr>
                                                                <?php } ?>
                                                                <!-- FIM Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                                                                <tr>
                                                                    <td class="textonormal" colspan="5" style="font-weight: bold;" align="left">
                                                                        VALOR TOTAL DA SOLICITAÇÃO DE SERVIÇO
                                                                    </td>
                                                                    <td class="textonormal" colspan="1" align="center">
                                                                        <?php echo number_format($valorFullTotal,4,',','.'); ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" colspan="7" align="center">
                                                            <input name="IncluirItem" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('CadIncluirItem.php?ProgramaOrigem=CadContratoManter&amp;PesqApenas=C', 700, 350);" type="button">
                                                            <input name="RetirarItem" <?php echo $_SESSION['bloqueiaCampo']?'disabled="disabled"':'';?> value="Retirar Item" class="botao" onclick="javascript:recharge('Remover', '', '');" type="button"> <!--Retrabalhar-->
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- <tr> -->
                                            <!-- <td colspan="4" align="right"> -->
                                                <!-- <input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('B');"> -->
                                                <input type="hidden" name="InicioPrograma" value="1">
                                                <input type="hidden" name="Botao" value="">
                                                <input type="hidden" name="Origem" value="B">
                                                <input type="hidden" name="Destino">
                                                <input type="hidden" name="flagCampPost" value="">
                                                <input type="hidden" name="ordCampoPost" value="">
                                            <!-- </td> -->
                                        <!-- </tr> -->
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
}
?>