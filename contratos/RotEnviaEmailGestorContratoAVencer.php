<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadEnviaEmailGestorContratoAVencer.php
# Autor:    João Madson
# Data:     28/01/2021
# Objetivo: Disparar emails para os gestores de contratos a vencer em 90 dias
#-------------------------------------------------------------------------
# Autor:    Osmar Celestino
# Data:     30/08/2021
# Objetivo: 251793
#-------------------------------------------------------------------------
include "../funcoes.php";


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
$arquivoErro="RotEnviaEmailGestorContratoAVencer.php";
session_start();
$centoEVinteDias = date('Y-m-d', strtotime('+120 days'));
$noventaDias = date('Y-m-d', strtotime('+90 days'));
$secentaDias = date('Y-m-d', strtotime('+60 days'));
$trintaDias = date('Y-m-d', strtotime('+30 days'));
if($acao=="enviar"){   
    $db = Conexao();
    $dbDados = array();
    $sql = "SELECT DISTINCT ON (con.cdocpcsequ) con.cdocpcsequ, con.nctrpcmlgt, ectrpcnumf, actrpcanoc, ectrpcobje, 
    dctrpcinvg, dctrpcfivg, dctrpcinex, dctrpcfiex, adt.daditifivg, org.eorglidesc, forn.aforcrccgc, forn.aforcrccpf, 
    forn.nforcrrazs, fisc.nfiscdmlfs, con.nctrpcnmgt, con.nctrpccpfg,
    CASE 
        WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
        on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
        where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 
        and doc2.csitdcsequ = 1) IS NULL THEN ( dctrpcfivg ) 
    end as aditivo_n_cadastrado FROM sfpc.tbcontratosfpc CON  
    inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ = doc.cdocpcsequ )
    inner join sfpc.tborgaolicitante as org on (org.corglicodi = con.corglicodi)
    inner join sfpc.tbdocumentofiscalsfpc as fidoc on (fidoc.cdocpcsequ = con.cdocpcsequ)
    inner join sfpc.tbfiscaldocumento as fisc on (fidoc.cfiscdcpff = fisc.cfiscdcpff)
    inner join sfpc.tbfornecedorcredenciado as forn on (forn.aforcrsequ = con.aforcrsequ)
    left outer join sfpc.tbaditivo adt on (con.cdocpcsequ = adt.cdocpcseq1 and adt.faditialpz = 'SIM') 
    where doc.csitdcsequ = 1 and
    (CASE 
        WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg in ('$centoEVinteDias', '$noventaDias', '$secentaDias', '$trintaDias') ) 
        WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
        on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
        where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 
        and doc2.csitdcsequ = 1) IS NULL THEN (con.dctrpcfivg in ('$centoEVinteDias', '$noventaDias', '$secentaDias', '$trintaDias') ) 
        WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg in ('$centoEVinteDias', '$noventaDias', '$secentaDias', '$trintaDias') 
        and adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
        left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
        where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 
        and doc2.csitdcsequ = 1)) 
    END ) group by con.cdocpcsequ, adt.cdocpcseq1, con.csolcosequ, con.cctrpcopex, con.actrpcpzec, con.dctrpcdtpr, 
    con.vctrpcglaa, con.vctrpcvlor, con.cctrpciden, con.vctrpceant, con.vctrpcsean, con.tctrpculat, con.corglicodi, 
    con.actrpcnumc, ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, adt.aaditinuad, 
    adt.daditifivg, dctrpcinex, dctrpcfiex, con.nctrpcmlgt, org.eorglidesc, forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs, fisc.nfiscdmlfs, con.nctrpcnmgt, con.nctrpccpfg";
    $resultado = executarSQL($db, $sql);
    if( db::isError($resultado) ){
        $db->disconnect;
        echo "ERRO: mensagem enviada ao analista";
        EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
        exit(0);
    }

    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
        $dbDados[] = $retorno;
    }
    if(!empty($dbDados)){
        $emails = array();
        foreach($dbDados as $value){
            if(!empty($value->aforcrccgc)){
                $cnpj = MascarasCPFCNPJ($value->aforcrccgc);
                $forn = "CNPJ ".$cnpj." - ".$value->nforcrrazs;
            }else{
                $cpf  = MascarasCPFCNPJ($value->aforcrccpf);
                $forn = "CPF ".$cpf." - ".$value->nforcrrazs;
            }

            if(empty($value->aditivo_n_cadastrado)){
                $data = explode("-", $value->daditifivg);
                $dataTermVig = "$data[2]/$data[1]/$data[0]";
                //Checa em qual periodo se encaixa
                if($value->daditifivg == $centoEVinteDias){
                    $prazo = "120 dias";
                }elseif($value->daditifivg == $noventaDias){
                    $prazo = "90 dias";            
                }elseif($value->daditifivg == $secentaDias){
                    $prazo = "60 dias";
                }elseif($value->daditifivg == $trintaDias){
                    $prazo = "30 dias";
                }
            }else if(!empty($value->aditivo_n_cadastrado)){
                $data = explode("-", $value->dctrpcfivg);
                $dataTermVig = "$data[2]/$data[1]/$data[0]";
                //Checa em qual periodo se encaixa
                if($value->dctrpcfivg == $centoEVinteDias){
                    $prazo = "120 dias";
                }elseif($value->dctrpcfivg == $noventaDias){
                    $prazo = "90 dias";            
                }elseif($value->dctrpcfivg == $secentaDias){
                    $prazo = "60 dias";
                }elseif($value->dctrpcfivg == $trintaDias){
                    $prazo = "30 dias";
                }
            }
            
            //Inicio da busca de alteração de gestor via apostilamento implantado
            $gestorAost = array();
            $sqlApost =  "select apost.napostmlgt, apost.napostnmgt, apost.napostcpfg
                        from sfpc.tbapostilamento as apost
                        inner join sfpc.tbdocumentosfpc as doc on (doc.cdocpcsequ = apost.cdocpcsequ)
                        where apost.cdocpcseq2 = $value->cdocpcsequ and apost.ctpaposequ in (2,3) and doc.ctidocsequ = 3 
                        and doc.cfasedsequ = 6 and doc.csitdcsequ = 1 and (
                        select max(a2.aapostnuap) from sfpc.tbapostilamento as a2 
                        inner join sfpc.tbdocumentosfpc as doc2 on (doc2.cdocpcsequ = a2.cdocpcsequ) 
                        where a2.cdocpcseq2 = apost.cdocpcseq2 and a2.ctpaposequ in (2,3) and doc2.ctidocsequ = 3 
                        and doc2.cfasedsequ = 6 and doc2.csitdcsequ = 1 and a2.napostmlgt is not null and a2.napostmlgt <> ''
                        ) = apost.aapostnuap";
            $resultApost = executarSQL($db, $sqlApost);
            if( db::isError($resultApost) ){
                $db->disconnect;
                echo "ERRO: mensagem enviada ao analista";
                EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
                exit(0);
            }      
            $gestorApost = array();
            
            while($resultApost->fetchInto($retornoApost, DB_FETCHMODE_OBJECT)){
                $gestorApost[] = $retornoApost;
            }
            
            if(!empty($gestorApost[0]->napostmlgt)){
                $emails = $gestorApost[0]->napostmlgt.", ";
                $gestor = "CPF : ".MascarasCPFCNPJ($gestorApost[0]->napostcpfg)." - ".$gestorApost[0]->napostnmgt;
            }else{
                $emails = "$value->nctrpcmlgt, ";
                $gestor = "CPF : ".MascarasCPFCNPJ($value->nctrpccpfg)." - ".$value->nctrpcnmgt;
            }
            
            //-------------------------------||----------------------------------

            //Inicio da busca de fiscais |MADSON|
            $emailsFiscais = array();
            $Apost = array();
            $sqlChecaApost = "SELECT distinct apost.cdocpcsequ 
                                FROM sfpc.tbapostilamento as apost
                                inner join sfpc.tbdocumentofiscalsfpc as fidoc on (fidoc.cdocpcsequ = apost.cdocpcsequ)
                                inner join sfpc.tbfiscaldocumento as fisc on (fidoc.cfiscdcpff = fisc.cfiscdcpff)
                                INNER JOIN sfpc.tbdocumentosfpc AS doc ON (apost.cdocpcsequ = doc.cdocpcsequ)
                                WHERE apost.cdocpcseq2 = $value->cdocpcsequ and apost.ctpaposequ in (2,3) and doc.ctidocsequ = 3 
                                and doc.cfasedsequ = 6 and doc.csitdcsequ = 1 and (
                                select max(a2.aapostnuap) from sfpc.tbapostilamento as a2 
                                inner join sfpc.tbdocumentosfpc as doc2 on (doc2.cdocpcsequ = a2.cdocpcsequ) 
                                where a2.cdocpcseq2 = apost.cdocpcseq2 and a2.ctpaposequ in (2,3) and doc2.ctidocsequ = 3 
                                and doc2.cfasedsequ = 6 and doc2.csitdcsequ = 1 
                                ) = apost.aapostnuap and fisc.nfiscdmlfs is not null";
            $resultApostFisc = executarSQL($db, $sqlChecaApost);
            $Apost = array();
            while($resultApostFisc->fetchInto($retornoApostFisc, DB_FETCHMODE_OBJECT)){
                $Apost[] = $retornoApostFisc;
            }
            if(empty($Apost)){
                $docFiscal = $value->cdocpcsequ;
            }else{
                $docFiscal = $Apost[0]->cdocpcsequ;
            }
            $sqlFisc =  "select  fisc.nfiscdmlfs, fidoc.cfiscdcpff, fisc.nfiscdnmfs
                        from sfpc.tbfiscaldocumento as fisc
                        inner join sfpc.tbdocumentofiscalsfpc as fidoc on (fidoc.cfiscdcpff = fisc.cfiscdcpff)
                        where fidoc.cdocpcsequ = $docFiscal";
            $resultFisc = executarSQL($db, $sqlFisc);
            if( db::isError($resultFisc) ){
                $db->disconnect;
                echo "ERRO: mensagem enviada ao analista";
                EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
                exit(0);
            }     
            while($resultFisc->fetchInto($retornoFisc, DB_FETCHMODE_OBJECT)){
                $emailsFiscais[] = $retornoFisc;
            }      
            $fiscais = "";
            if(count($emailsFiscais) > 1){
                $contFiscais = count($emailsFiscais) - 1;
                for($i=0; $i<$contFiscais; $i++){
                    $emails .= $emailsFiscais[$i]->nfiscdmlfs.", ";
                    $fiscais .= "CPF : ".MascarasCPFCNPJ($emailsFiscais[$i]->cfiscdcpff)." - ".$emailsFiscais[$i]->nfiscdnmfs."<br>";
                }
                $auxCont = $i++; 
                $emails .= $emailsFiscais[$auxCont]->nfiscdmlfs;
                $fiscais .= "CPF : ".MascarasCPFCNPJ($emailsFiscais[$auxCont]->cfiscdcpff)." - ".$emailsFiscais[$auxCont]->nfiscdnmfs;
            }else{
                $emails .= $emailsFiscais[0]->nfiscdmlfs;
                $fiscais = "CPF : ".MascarasCPFCNPJ($emailsFiscais[0]->cfiscdcpff)." - ".$emailsFiscais[0]->nfiscdnmfs;
            }
            //--------------------------------------||--------------------------------------
            
            

            // $teste = '
            //     <font color=\"#0000ff\">Teste</font>
            // ';

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
                                            Assunto: Aviso de Término de Vigência de Contrato
                                            <br><br>
                                            Informamos que o contrato abaixo irá vencer em aproximadamente $prazo: 
                                            <br><br>
                                        </td>
                                    </tr>
                                </thead>
                                <tr>
                                    <td id=\"bordatd\">
                                        <strong>Órgão</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $value->eorglidesc
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Número do Contrato</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $value->ectrpcnumf
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Objeto</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $value->ectrpcobje
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Fornecedor</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $forn
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Data de Término de Vigência</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $dataTermVig
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Gestor</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $gestor
                                    </td>
                                </tr>
                                <tr >
                                    <td id=\"bordatd\">
                                        <strong>Fiscal(is)</strong>
                                    </td>
                                    <td id=\"bordatd\">
                                        $fiscais
                                    </td>
                                </tr>
                                <tr>
                                <td colspan=\"2\">
                                    <br><br>
                                    Se for do interesse do órgão / entidade solicite um novo processo de contratação.
                                    <br><br><br>
                                    <hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                </td>
                            </tr>
        
                            <tr>
                                <td colspan=\"2\">
                                    Este e-mail foi enviado pelo sistema Portal de Compras do Recife, assim por favor não responda.<br><br> 
                                    Em caso de dúvida, entre em contato com a equipe de suporte do Portal de Compras do Recife através do 
                                    e-mail suportecontratos@recife.pe.gov.br ou do telefone 3355-8790.
                                    <hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                </td>
                            </tr>
                            </table>
                        </container>
                    </div>
                </body>
            
            </html>    
            ";
           EnviaEmailHTML($emails,"NÃO RESPONDA".$NomeLocalTitulo." - Aviso de Término de Vigência de Contrato",$mensagem,$GLOBALS["EMAIL_FROM"]);
            echo $emails;
            echo $mensagem;
            echo "<br>------------------------------------------------------------------<br><br>";
      }
        EnviaEmailSistema("Rotina RotEnviaEmailGestorContratoAVencer.php executada", "Rotina de envio de email a gestores/fiscais de contrato a vencer.");
        echo "Executado com sucesso.";
        $db->disconnect();
    }else{
        echo 'Não há contratos a vencer em 30, 60, 90 ou 120 dias';
    }
}else{
    # mensagem para avisar que comando 'acao' não foi recebido
	echo "ERRO: comando requerido inválido";
}
?>

