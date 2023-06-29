<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotEnviaEmailGestorARPAVENCER.php
# Autor:    João Madson
# Data:     28/01/2021
# Objetivo: Disparar emails para os gestores de contratos a vencer em 90 dias
#-------------------------------------------------------------------------
#-------------------------------------------------------------------------
# Portal da DGCO
# Autor:    Osmar Celestino
# Data:     23/04/2021
# Objetivo: 247229 Ajustar a data de envio para 90 e 30 dias
#-------------------------------------------------------------------------
# Programa: RotEnviaEmailGestorARPAVENCER.php
# Autor:    Osmar Celestino
# Data:     30/08/2021
# Objetivo: 251793
#-------------------------------------------------------------------------
include "../funcoes.php";
// require_once "ClassContratos.php";
// $ObjContrato = new Contrato();

function MascarasCPFCNPJ($valor){
    $checaSeFormatado = strripos($valor, "-");
    if($checaSeFormatado == true){
        return $valor;
    }
    if(strlen($valor) == 11){
        $mascara = "###.###.###-##";
        for($i =0; $i <= strlen($mascara); $i++){
            if($mascara[$i] == "#"){
                if(isset($valor[$k])){
                $maskared .= $valor[$k++];
                }
            }else{
                $maskared .= $mascara[$i];
            }
        }
        return $maskared;
    }
    if(strlen($valor) == 14){
        $mascara = "##.###.###/####-##";
        for($i =0; $i <= strlen($mascara); $i++){
            if($mascara[$i] == "#"){
                if(isset($valor[$k])){
                $maskared .= $valor[$k++];
                }
            }else{
                $maskared .= $mascara[$i];
            }
        }
        // var_dump($maskared);
        return $maskared;
    }
}

$acao = null;
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$acao = $_GET['acao']; //ação tem que ser igual a "enviar" para rotina ser executada
}
$arquivoErro="RotEnviaEmailGestorARPAVENCER.php";
session_start();
$noventaDias = date('Y-m-d', strtotime('+90 days'));
$trintaDias = date('Y-m-d', strtotime('+30 days'));
if($acao=="enviar"){   
    $db = Conexao();
    $dbDadosARPI = array();
    $dbDadosARPE = array();
    $sqlARPE = "select  (ARPE.tarpexdini + (ARPE.aarpexpzvg || ' month')::INTERVAL) as vigencia, ARPE.*
                    from sfpc.tbataregistroprecoexterna as ARPE
                    where ARPE.farpexsitu = 'A'";
    
    $resultadoARPE = executarSQL($db, $sqlARPE);
    if( PEAR::isError($resultadoARPE) ){
        $db->disconnect;
        echo "ERRO: mensagem enviada ao analista";
        EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
        exit(0);
    }
    
    while($resultadoARPE->fetchInto($retornoARPE, DB_FETCHMODE_OBJECT)){
        $dbDadosARPE[] = $retornoARPE;
    }
    $AtasAVencerE = array(); //Atas a vencer externa
    $j=0;
    for($i=0; $i<count($dbDadosARPE);$i++){
        $auxVig = explode(" ",$dbDadosARPE[$i]->vigencia);
        if($auxVig[0] == $noventaDias){
            $dbDadosARPE[$i]->flagIE = "E";
            $AtasAVencerE[$j] = $dbDadosARPE[$i];
            $j++;
        }
        elseif($auxVig[0] == $trintaDias){
            $dbDadosARPE[$i]->flagIE = "E";
            $AtasAVencerE[$j] = $dbDadosARPE[$i];
            $j++;
        }
    }
    $sqlARPI = "select (ARPI.tarpindini + (ARPI.aarpinpzvg || ' month')::INTERVAL) as vigencia, org.eorglidesc, ARPI.*
                from  sfpc.tbataregistroprecointerna as ARPI
                inner join sfpc.tborgaolicitante as org on(org.corglicodi = ARPI.corglicodi)
                where ARPI.farpinsitu = 'A'";
    
    $resultadoARPI = executarSQL($db, $sqlARPI);
    if( PEAR::isError($resultadoARPI) ){
        $db->disconnect;
        echo "ERRO: mensagem enviada ao analista";
        EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
        exit(0);
    }

    while($resultadoARPI->fetchInto($retornoARPI, DB_FETCHMODE_OBJECT)){
        $dbDadosARPI[] = $retornoARPI;
    }
    
    $AtasAVencerI = array(); //Atas a vencer externa
    $j=0;
    for($i=0; $i<count($dbDadosARPI);$i++){
        $auxVig = explode(" ",$dbDadosARPI[$i]->vigencia);
        if($auxVig[0] == $noventaDias){
            $dbDadosARPI[$i]->flagIE = "I";
            $AtasAVencerI[$j] = $dbDadosARPI[$i];
            $j++;
        }
        if($auxVig[0] == $trintaDias){
            $dbDadosARPI[$i]->flagIE = "I";
            $AtasAVencerI[$j] = $dbDadosARPI[$i];
            $j++;
        }
    }
    //Aqui junta todas as atas em um array para o loop onde eu vou endereçar cada campo da mensagem.
    $atasAVencerIE = array_merge($AtasAVencerI, $AtasAVencerE);

    $sqlEmail = " SELECT epargeeval FROM sfpc.tbparametrosgerais";
    $resultadoEmail = executarSQL($db, $sqlEmail);
    if( PEAR::isError($resultadoEmail) ){
        $db->disconnect;
        echo "ERRO: mensagem enviada ao analista";
        EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
        exit(0);
    }

    $resultadoEmail->fetchInto($retornoemail, DB_FETCHMODE_OBJECT);


    if(!empty($atasAVencerIE)){
        $emails = array();
        foreach($atasAVencerIE as $value){

            $auxVig = explode(" ",$value->vigencia);
            if($auxVig[0] == $noventaDias){
               $string = 90;
            }
            elseif($auxVig[0] == $trintaDias){
                $string = 30;
            }
            $dataSHR = explode(" ", $value->vigencia);
            $OrganizaDT = explode("-", $dataSHR[0]);
            $data = "$OrganizaDT[2]/$OrganizaDT[1]/$OrganizaDT[0]";
            
            $sqlForn = "select aforcrccgc, aforcrccpf, nforcrrazs from  sfpc.tbfornecedorcredenciado where aforcrsequ = $value->aforcrsequ";
            $resultadoForn = executarSQL($db, $sqlForn);
            if( PEAR::isError($resultadoForn) ){
                $db->disconnect;
                echo "ERRO: mensagem enviada ao analista";
                EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
                exit(0);
            }
        
            $resultadoForn->fetchInto($retornoForn, DB_FETCHMODE_OBJECT);
            if(!is_null($retornoForn->aforcrccgc)){
                $fornecedor = "CNPJ ".MascarasCPFCNPJ($retornoForn->aforcrccgc)." ".$retornoForn->nforcrrazs;
            }else{
                $fornecedor = "CNPJ ".MascarasCPFCNPJ($retornoForn->aforcrccpf)." ".$retornoForn->nforcrrazs;
            }

            if($value->flagIE == "I"){
                $sqlC = "
                    SELECT distinct ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
                    FROM sfpc.tbcentrocustoportal ccp
                    WHERE 1=1 AND ccp.corglicodi = $value->corglicodi";
                $resultadoCC = executarSQL($db, $sqlC);
                if( PEAR::isError($resultadoCC) ){
                    $db->disconnect;
                    echo "ERRO: mensagem enviada ao analista";
                    EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
                    exit(0);
                }
                $resultadoCC->fetchInto($retornoCC, DB_FETCHMODE_OBJECT);
                
                $numeroAta      = $retornoCC->ccenpocorg . str_pad($retornoCC->ccenpounid, 2, '0', STR_PAD_LEFT);
                $numeroAta      .= "." . str_pad($value->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $value->aarpinanon;
                
                $proc = str_pad($value->clicpoproc, 4, '0', STR_PAD_LEFT)."/".$value->alicpoanop;

                $mensagem = "
                <!DOCTYPE html>
                <html lang=\"en\">
                <head>
                    <meta charset=\"UTF-8\">
                    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                    <title>Document</title>
                    <style>
                        div{
                            width: 800px;
                            align-items: center;
                        }
                        table{
                            align-items: center;
                            text-align:justify;
                            color: #0B62C3;
                            width: 800px;
                            padding-left: 5em;
                            padding-right: 5em;
                        }
                        img{
                            width: 800px;
                        }
                        td{
                            width: 50%;
                        }
            
                        #content{
                            width: 800px;
                        }
                        #bordatd{
                            border: 1px solid grey;
                        }
                        #head{
                            width: 800px;
                            height: 65px;
                            align-items: center;
                            background-image: linear-gradient(to right, #0B62C3 , #c8d9f0e3);
                            color: #ffffff;
                        }
                        #port{
                            align-items: center;
                            color: #ffffff;
                            height: 5px;
                            font-size: 2.3em;
                        }
                    </style>
                </head>
                <body>
                    
                    <div>
                        <div id=\"head\">
                            <ul id=\"port\">PORTAL DE COMPRAS</ul>
                            <ul>Prefeitura do Recife</ul>
                        </div>
                        <container id=\"content\">
                            <table>
                                <thead>
                                    <tr>
                                        <td colspan=\"2\">
                                            <br>
                                            Assunto: Aviso de Término de Vigência de Ata de Registro de Preços
                                            <br><br>
                                            Informamos que a Ata de Registro de Preços abaixo irá vencer em aproximadamente $string dias:
                                            <br><br>
                                        </td>
                                    </tr>
                                </thead>
                                <tr>
                                    <td id=\"bordatd\">
                                        <strong>Tipo de Ata</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                    Ata Interna
                                    </td>
                                </tr>
                                <tr>
                                    <td id=\"bordatd\">
                                        <strong>Órgão Gestor</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $value->eorglidesc
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Processo licitatório</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $proc
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Número da Ata de Registro de Preços</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $numeroAta
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Objeto </strong>
                                    </td>
                                    <td id=\"bordatd\">
                                         $value->earpinobje
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Fornecedor </strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $fornecedor
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Data de Término de Vigência</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $data
                                    </td>
                                </tr>
                                <tr>
                                <td colspan=\"2\">
                                    <br><br>
                                    Se for do interesse do Órgão solicite um novo processo de Compra.
                                    <br><br><br>
                                    <hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                </td>
                            </tr>
        
                            <tr>
                                <td colspan=\"2\">
                                    Este e-mail foi enviado pelo sistema Portal de Compras do Recife, assim por favor não responda.<br><br> 
                                    Em caso de dúvida, entre em contato com a equipe de suporte do Portal de Compras através do 
                                    e-mail portalcompras@recife.pe.gov.br ou do telefone 3355-8790.
                                    <hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                </td>
                            </tr>
                            </table>
                        </container>
                    </div>
                </body>
            
            </html>";
            }else{
                $mensagem = "
                    <!DOCTYPE html>
                    <html lang=\"en\">
                    <head>
                        <meta charset=\"UTF-8\">
                        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                        <title>Document</title>
                        <style>
                            div{
                                width: 800px;
                                align-items: center;
                            }
                            table{
                                align-items: center;
                                text-align:justify;
                                color: #0B62C3;
                                width: 800px;
                                padding-left: 5em;
                                padding-right: 5em;
                            }
                            img{
                                width: 800px;
                            }
                            td{
                                width: 50%;
                            }
                
                            #content{
                                width: 800px;
                            }
                            #bordatd{
                                border: 1px solid grey;
                            }
                            #head{
                                width: 800px;
                                height: 65px;
                                align-items: center;
                                background-image: linear-gradient(to right, #0B62C3 , #c8d9f0e3);
                                color: #ffffff;
                            }
                            #port{
                                align-items: center;
                                color: #ffffff;
                                height: 5px;
                                font-size: 2.3em;
                            }
                        </style>
                    </head>
                    <body>
                        
                        <div>
                            <div id=\"head\">
                                <ul id=\"port\">PORTAL DE COMPRAS</ul>
                                <ul>Prefeitura do Recife</ul>
                            </div>
                            <container id=\"content\">
                                <table>
                                    <thead>
                                        <tr>
                                            <td colspan=\"2\">
                                                <br>
                                                Assunto: Aviso de Término de Vigência de Ata de Registro de Preços
                                                <br><br>
                                                Informamos que a Ata de Registro de Preços abaixo irá vencer em aproximadamente $string
                                                dias:
                                                <br><br>
                                            </td>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <td id=\"bordatd\">
                                            <strong>Tipo de Ata</strong>
                                        </td>
                                        <td id=\"bordatd\">
                                            Ata Externa
                                        </td>
                                    </tr>
                                    <tr>
                                        <td id=\"bordatd\">
                                            <strong>Órgão Gestor</strong>
                                        </td>
                                        <td id=\"bordatd\">
                                            $value->earpexorgg
                                        </td>
                                    </tr>
                                    <tr >
                                        <td id=\"bordatd\">
                                            <strong>Processo licitatório</strong> 
                                        </td>
                                        <td id=\"bordatd\">
                                            $value->earpexproc
                                        </td>
                                    </tr>
                                    <tr >
                                        <td id=\"bordatd\">
                                            <strong>Número da Ata de Registro de Preços</strong>
                                        </td>
                                        <td id=\"bordatd\">
                                            $value->carpexcodn/$value->aarpexanon
                                        </td>
                                    </tr>
                                    <tr>
                                        <td id=\"bordatd\">
                                            <strong>Objeto</strong> 
                                        </td>
                                        <td id=\"bordatd\">
                                            $value->earpexobje
                                        </td>
                                    </tr>
                                    <tr>
                                        <td id=\"bordatd\">
                                            <strong>Fornecedor </strong>
                                        </td>
                                        <td id=\"bordatd\">
                                            $fornecedor
                                        </td>
                                     </tr>
                                    <tr>
                                        <td id=\"bordatd\">
                                            <strong>Data de Término de Vigência</strong>
                                        </td>
                                        <td id=\"bordatd\">
                                            $data
                                        </td>
                                    </tr>
                                    <tr>
                                    <td colspan=\"2\">
                                        <br><br>
                                        Se for do interesse do Órgão solicite um novo processo de Compra.
                                        <br><br><br>
                                        <hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                    </td>
                                </tr>
            
                                <tr>
                                    <td colspan=\"2\">
                                        Este e-mail foi enviado pelo sistema Portal de Compras do Recife, assim por favor não responda.<br><br> 
                                        Em caso de dúvida, entre em contato com a equipe de suporte do Portal de Compras através do 
                                        e-mail portalcompras@recife.pe.gov.br ou do telefone 3355-8790.
                                        <hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                    </td>
                                </tr>
                                </table>
                            </container>
                        </div>
                    </body>
                
                </html>";
                
               
           
            }
               
        }
        EnviaEmailSistema("Rotina RotEnviaEmailGestorARPAVencer.php executada", "Rotina de envio de email a gestores de Atas de Registro de Preço com vencimento em 90 dias.");
        EnviaEmailHTML($retornoemail->epargeeval,$NomeLocalTitulo." - Aviso de Término de Vigência de Ata de Registro de Preços",$mensagem,$GLOBALS["EMAIL_FROM"]);
        echo "Emails: "; 
        echo $retornoemail->epargeeval; 
        echo "<br>";
        echo $mensagem; 
        echo "<br>--------------------------------------------------------------<br><br>";
        echo "Executado com sucesso.";
        $db->disconnect();
    }else{
        echo "Não há atas a vencer em" .$string. " dias";
    }
}else{
    # mensagem para avisar que comando 'acao' não foi recebido
	echo "ERRO: comando requerido inválido";
}
?>

