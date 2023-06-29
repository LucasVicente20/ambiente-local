<?php
/**
 * Portal da DGCO
 *
 * PHP version 7.4
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.

 * # Portal da DGCO
 * # Programa: EmissaoCHF.php
 * # Autor:    Osmar Celestino
 * # Data:     25/05/2022
 * # Objetivo: Detalhamento de Adesão
 * #---------------------------------**/
# Alterado: Lucas Vicente
# Data:     06/09/2022
# Objetivo: CR 268483
# ---------------------------------------------------------------------------
# Alterado: Osmar Celestino 
# Data:     02/06/2023
# Objetivo: CR 284097
# ---------------------------------------------------------------------------
require_once "TemplateAppPadrao.php";
$tpl = new TemplateAppPadrao("templates/ConsRegistroPrecoAdesaoAtasDetalhes.html");
$db = Conexao();

// # Acesso ao arquivo de funções #
// include "../funcoes.php";

// # Executa o controle de segurança #
// session_start();
// Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
} else {
    // $GrupoCodigo          = $_GET['GrupoCodigo'];
    // $Processo             = $_GET['Processo'];
    // $ProcessoAno          = $_GET['ProcessoAno'];
    // $ComissaoCodigo       = $_GET['ComissaoCodigo'];
    // $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
    // $ObjetoPesquisa       = $_GET['ItemObjeto'];
    // $ComissaoPesquisa     = $_GET['ItemComissao'];
    // $OrgaoPesquisa        = $_GET['ItemOrgao'];
    $seqScc                  = $_GET['seqScc'];
    $tipoAta              = $_GET['tipoAta'];



}
function MascarasCPFCNPJ($valor){
    if(strlen($valor) == 11){
        $mascara = "###.###.###-##";
        for($i =0; $i <= strlen($mascara)-1; $i++){
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
        for($i =0; $i <= strlen($mascara)-1; $i++){
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
}

$titulo = ''; $dados = ''; $tituloDocumentos = ''; $documentos = '';

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

function documentoAta($csolsequ){
    $db = Conexao();
    $documentos = array();
    $sql = " SELECT  csolcosequ, crpaddcodi, erpaddnome, cusupocodi, frpaddexcl ";
    $sql .= " FROM sfpc.tbregistroprecoadesaodoc doc ";
    $sql .= " WHERE doc.csolcosequ = " . $csolsequ;
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        while ($Linha = $result->fetchRow()) {
            $documentos[] = $Linha;
            
        }
        return $documentos;
    }
}
function valorAta($csolsequ){
    $db = Conexao();
    $documentos = array();
    $sql = " select sum (vitescunit * aitescqtso)  from sfpc.tbitemsolicitacaocompra ";
    $sql .= "where csolcosequ = " . $csolsequ;
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        while ($Linha = $result->fetchRow()) { 
            $documentos[] = (object) array(
                "valor_total" =>  $Linha[0],
            ); 
            
        }
        return $documentos;
    }
}


  
function PesquisarSCC($dados){
    $conexaoDb = Conexao();
    if($dados){
        $orgao = 'org.eorglidesc as orgaodesc';
        $sql =  "SELECT scc.csolcosequ, scc.ctpcomcodi, scc.esolcoobje, scc.asolcoanos,  scc.csolcocodi, cc.ccenpocorg, scc.fsolcorpcp,".$orgao.", cc.ccenpounid, fc.nforcrrazs, fc.aforcrccgc, fc.aforcrccpf ";
        $sql .= " FROM sfpc.tbsolicitacaocompra AS scc INNER JOIN sfpc.tbcentrocustoportal AS cc ON scc.ccenposequ = cc.ccenposequ ";  
        $sql .= " INNER JOIN sfpc.tborgaolicitante as org on scc.corglicodi = org.corglicodi ";   
        $sql .= " INNER JOIN sfpc.tbitemsolicitacaocompra AS iscc ON scc.csolcosequ = iscc.csolcosequ ";
        $sql .= " INNER JOIN sfpc.tbfornecedorcredenciado AS fc ON iscc.aforcrsequ = fc.aforcrsequ ";
        $sql .= " INNER JOIN sfpc.tbregistroprecoadesaodoc DOC ON scc.csolcosequ = DOC.csolcosequ";
        
        $sql .= " where scc.csitsocodi IN (3,4) AND scc.ctpcomcodi = 5 ";
        $sql .= " and scc.csolcosequ = ".$dados['seqScc'];
        $sql .= " ORDER BY scc.asolcoanos DESC, scc.csolcocodi ASC, cc.ccenpounid ASC, cc.ccenpocorg ASC";
        $resultado = executarSQL($conexaoDb, $sql);
        $dadosPesquisa = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosPesquisa[] = (object) array(
                            'csolcosequ'=> $retorno->csolcosequ,
                            'ctpcomcodi'=> $retorno->ctpcomcodi,
                            'esolcoobje'=> $retorno->esolcoobje,
                            'asolcoanos'=> $retorno->asolcoanos,
                            'csolcocodi'=> $retorno->csolcocodi,
                            'ccenpocorg'=> $retorno->ccenpocorg,
                            'ccenpounid'=> $retorno->ccenpounid,
                            'nforcrrazs'=> $retorno->nforcrrazs,
                            'aforcrccgc'=> $retorno->aforcrccgc,
                            'aforcrccpf'=> $retorno->aforcrccpf,
                            'carpnosequ'=> $retorno->carpnosequ,
                            'orgaodesc' => $retorno->orgaodesc,
                            'fsolcorpcp' => $retorno->fsolcorpcp,
                            'valor_scc' => $retorno->valor,
                            'qtd'       => $retorno->aitescqtso,
                    );
        }
}
    return $dadosPesquisa;
}


$dadosEntrada = array(
    'seqScc' => $seqScc,
    'tipo_ata' => $tipoAta,
); 

$dadosEntrada = PesquisarSCC($dadosEntrada);
foreach($dadosEntrada as $dados){
    $codigoDinamico = str_pad($dados->csolcocodi,4,'0',STR_PAD_LEFT);
    $codigoDinamicoUni = str_pad($dados->ccenpounid,2,'0',STR_PAD_LEFT);
    $scc = $dados->ccenpocorg.$codigoDinamicoUni.'.'.$codigoDinamico.'.'.$dados->asolcoanos;
    $partes = explode('.', $scc);
        if(strlen($partes[0]) < 4) {
            $partes[0] = str_pad($partes[0], 4, '0', STR_PAD_LEFT);
        }
    $tipoAtaDesc = ($tipoAta == "I") ? "INTERNA" : "EXTERNA";
    $tipoSolicitacao = ($dados->fsolcorpcp == "P") ? "PARTICIPANTE" : "CARONA";
    $csolsequ = $dados->csolcosequ;
    $cpfFornecedor=MascarasCPFCNPJ($dados->aforcrccpf);
    $cnpjForncenedor=MascarasCPFCNPJ($dados->aforcrccgc);
    
    $tpl->SCC = $partes[0].'.'.$codigoDinamico.'/'.$dados->asolcoanos;;
    $tpl->OBJETO = $dados->esolcoobje;
    $tpl->FORNECEDOR = $dados->nforcrrazs;
    $tpl->FORNECEDORCPF = $cpfFornecedor;
    $tpl->FORNECEDORCNPJ = $cnpjForncenedor;
    $tpl->TIPO_SOLICITACAO = $tipoSolicitacao;
    $tpl->ORGAO = $dados->orgaodesc;
    $tpl->TIPO_ATA = $tipoAtaDesc;
    
             

    //          <tr>\n";
    //              <td valign=\"top\" colspan=\"2\"><strong>VALOR TOTAL</strong></td>\n";
    //              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">""</td>\n";
    //          </tr>\n";


        
  

    // Tabela de itens 
}
$valor_total = valorAta($csolsequ);
foreach($valor_total as $valor){
    $valor_formatado = number_format($valor->valor_total,2,",",".");
    $tpl->VALOR_SCC = "R$ ".$valor_formatado;
}

$documentos = documentoAta($csolsequ);
if (count($documentos) > 0) {        
    foreach ($documentos as $key => $documento) {
        $documento_key = 'documento'.$seqScc.'arquivo'.$key;    
        $url = 'registropreco/'.$seqScc.'/'.$documento[2];
        if ($documento[4] != 'S') {
            $doc .= '<input type="hidden" value="'.$documento[2].'" id="'.$documento_key.'">';
            $doc .= ' <form action="kfiledownload.php" method="post" id="'.$documento[2].'" style="margin: 0 0 6px;"> <input type="hidden" name="arq" value="'.$url.'" /> ';
            $doc .= ' <input type="hidden" name="arq_nome" value="'.$documento[2].'" /> ';
            $doc .= ' <button type="submit" class="btn btn-link">'.$documento[2].'</button> </form>';
            // $doc .= " <a href=\"../uploads/registropreco/".$seqScc."/".$documento[2]."\" class=\"$documento_key\" donwload target='_blank'>$documento[2]</a>";
            //$doc .= "<br>";
        }
    }
} else {
    $doc = "Nenhum documento encontrado";
}

$tpl->DOCUMENTOS = $doc;
echo $tpl->show();
