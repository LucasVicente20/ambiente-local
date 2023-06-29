<?php
/**
 * Portal de Compras
 * 
 * Programa: funcoesTramitacao.php
 * --------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     10/07/2018
 * Objetivo: Tarefa Redmine 199114
 * --------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     10/08/2018
 * Objetivo: Tarefa Redmine 199435
 * --------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     10/07/2019
 * Objetivo: Tarefa Redmine 220301
 * --------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     31/07/2019
 * Objetivo: Tarefa Redmine 221264
 * --------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     09/08/2019
 * Objetivo: Tarefa Redmine 222003
 * ---------------------------------------------------------------------------------------
 * Alterado: Rossana Lira
 * Data:     03/09/2019
 * Objetivo: Tarefa Redmine 223158
 * ---------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     03/10/2019 
 * Objetivo: Tarefa Redmine 223486
 * ---------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     22/10/2019 
 * Objetivo: Tarefa Redmine 224700
 * ---------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     20/11/2019 
 * Objetivo: Tarefa Redmine 225660
 * ---------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     01/01/2021 
 * Objetivo: Tarefa Redmine 223278
 * ---------------------------------------------------------------------------------------
 */
# arquivo geral de funcoes
require_once("../funcoes.php");

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Abrindo Conexão
if (!isset($db)) {
    $db = Conexao();
}

function getGrupos($cgrempcodi = null) {
	
    $db = $GLOBALS["db"];

    $sql = " SELECT cgrempcodi, egrempdesc FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI <> 0";

    if(!is_null($cgrempcodi)){
        $sql .= " AND CGREMPCODI = " . $cgrempcodi;
    }

    $sql .= " ORDER BY egrempdesc, CGREMPCODI ASC";
    $res  = $db->query($sql);

    if(!isError($res)) {
        return $res;
    }
}

function getOrgaos($grupo = null) {
	
	$db = $GLOBALS["db"];
    $sql = " SELECT DISTINCT OL.CORGLICODI, OL.EORGLIDESC FROM SFPC.TBORGAOLICITANTE OL ";

    if(!is_null($grupo)) {
        $sql .= " LEFT JOIN SFPC.TBGRUPOORGAO GRO ";
        $sql .= " ON GRO.CORGLICODI = OL.CORGLICODI ";
        $sql .= " WHERE GRO.CGREMPCODI <> 0 AND GRO.CGREMPCODI = " . $grupo;
    }

    $sql .= " ORDER BY OL.EORGLIDESC ASC";
    $res  = $db->query($sql);

    if(!isError($res)) {
        return $res;
    }
}

function getComissaoLicitacao($cgrempcodi) {
    $db = $GLOBALS["db"];

    $sql  = "SELECT CCOMLICODI, ECOMLIDESC ";
    $sql .= "FROM   SFPC.TBCOMISSAOLICITACAO ";
    $sql .= "WHERE  FCOMLISTAT = 'A' ";
    $sql .= "       AND CGREMPCODI =  " . $cgrempcodi;
    $sql .= " ORDER BY ECOMLIDESC ASC ";

    $res  = $db->query($sql);

    if (!isError($res)) {
        return $res;
    }
}

#gerar arquivo xls para download
function gerarXls($dados){
 
    // Nome do arquivo que será exportado
    $arquivo = 'planilha.xls';
    $html = '';
    // Configurações header para forçar o download
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header ("Content-type: application/vnd.ms-excel; ");//charset=UTF-8
    header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );


    // Criamos uma tabela HTML com o formato da planilha
    if(!empty($dados)) { 
                        
    //$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $html = '<table>';
    $html .= strtoupper2('
       <tr>
           <td width="25" rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Número/Ano Protocolo do Processo</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Órgão Demandante</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Objeto</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Número CI</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Número Ofício</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Data Entrada Protocolo</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Monitoramento</td>
           <td width="75" rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">SCC</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Processo</td>
           <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Última Fase</td>
           <td colspan="7" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Último Passo</td>
           <td colspan="6" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Comparativo Valores Totais Processo Licitatório</td>
           </tr>
           <tr>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Ação</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Entrada</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Saída</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Atraso</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Agente de Tramitaçao</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Usuário Responsável</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Observação</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Entrada Protocolo</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Estimado Licitação</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Economicidade %</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Estimado (Itens que Lograram Êxito)</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Homologado (Itens que Lograram Êxito)</td>
               <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">Economicidade %</td>
           </tr>
                ');
                   foreach($dados as $value) { 

                       
                        $html .= '<tr>
                           <td align="center">'; 
                        $html .= str_pad($value[1], 4, "0", STR_PAD_LEFT).'/'.$value[5];
                        $html .= '</td>
                           <td>'.$value[2].'</td>
                           <td>'.$value[4].'</td>
                           <td align="center">';
                        $html .= (!empty($value[6])) ? $value[6] : ' - ';
                        $html .= '</td>
                           <td align="center">';
                        $html .= (!empty($value[7])) ? $value[7] : ' - ';
                        $html .= '</td>
                           <td align="center">'.DataBarra($value[3]).'</td>
                           <td>'.$value[23].'</td>
                           <td align="center">';
                               $processo = ''; 
                               $fase = '';

                               if(!empty($value[8])){ 
                                    $html .= getNumeroSolicitacaoCompra($GLOBALS["db"], $value[8]);
                                    
                                       //APRESENTAR DADOS DO PROCESSO ASSOCIADO A SCC;
                                       
                                       $arrFase = getFaseLicitacaoScc($value[8]);
                                       $arrProcesso = getProcessoScc($value[8]);

                                       if(!empty($arrFase)){
                                           $arrFase = $arrFase[0];
                                       }
                                       if(!empty($arrProcesso)){
                                           $arrProcesso = $arrProcesso[0];
                                       }

                                       ////($arrProcesso);
                                   
                                       if(!empty($arrProcesso[0])) {
                                           $processo = str_pad($arrProcesso[0], 4, "0", STR_PAD_LEFT) . '/' . $arrProcesso[1]. ' - '. $arrProcesso[5];
                                           $fase = $arrFase[1];
                                       }else{
                                           $processo = "-";
                                           $fase = "-";
                                       }

                               }else{
                                    $html .= ' - ';

                                   //verifica se existe processo cadastrado 
                                   if(!empty($value[17])) {
                                       $processo = str_pad($value[17], 4, "0", STR_PAD_LEFT) . '/' . $value[18]. ' - '. $value[16];
                                       $fase = $value[15];
                                   }

                               }
                           
                               $html .='</td>
                           <td align="center">';
                           $html .= (!empty($processo)) ? $processo : ' - ';
                           $html .= '</td>
                                     <td align="center">';
                           $html .= (!empty($fase)) ? $fase : ' - ';
                           $html .= '</td>
                                <td>'.$value['ultimo_passo'][2].'</td>
                                <td align="center">'.DataBarra($value['ultimo_passo'][3]).'</td>
                                <td align="center">';
                           $html .= (!empty($value['ultimo_passo'][5])) ? DataBarra($value['ultimo_passo'][5]) : ' - ';
                           $html .= '</td>
                           <td align="center"><font color="red">'.$value['ultimo_passo']['atraso'].'</font></td>
                           <td class="apresentaHintAgente" id ="'.$value['ultimo_passo'][18].'">'.strtoupper2($value['ultimo_passo'][0]).'</td>
                           <td>';
                           
                           // usuario
                           $usuarioDesc = '';
                           if($value['ultimo_passo'][17]=='S'){
                                                           
                               if($value['ultimo_passo'][8] <= 0 ){
                                   $usuarioDesc = $value['ultimo_passo'][0];
                               }else{
                                   $usuarioDesc = $value['ultimo_passo'][1];
                               }
                           }else{
                               if($value['ultimo_passo'][8] <= 0){
                                   if($value['ultimo_passo'][9]=='I'){
                                       $usuarioDesc = $value['ultimo_passo'][0];
                                   }else{
                                       $usuarioDesc = 'ÓRGÃO EXTERNO';
                                   }
                               }else{
                                   $usuarioDesc = $value['ultimo_passo'][1];
                               }
                           }
                           $html .= strtoupper2($usuarioDesc);
                           
                           $html .= '</td>
                                    <td>'.$value['ultimo_passo'][6].'</td>
                                    <td align="center">R$ '. converte_valor_estoques2($value[9]).'</td>
                                    <td align="center">R$ '. converte_valor_estoques2($value[10]).'</td>';
                           
                               $diferenca_1 = floatval($value[9]) - floatval($value[10]);
                               $economicidade_1 = ( ($diferenca_1 != 0) && ($value[9] != 0)) ? number_format(((($diferenca_1 * 100) / $value[9])), 2, ',', '.') : '0';
                               if($value[10] <= 0){
                                   $economicidade_1 = '-';
                               }else{
                                   $economicidade_1 = $economicidade_1 . ' %';
                               }
                           
                               $html .= '<td align="center">'.$economicidade_1.'</td>
                                         <td align="center">'.converte_valor_estoques2($value[11]).'</td>
                                         <td align="center">'.converte_valor_estoques2($value[12]).'</td>';
                           
                               $diferenca_2 = floatval($value[11]) - floatval($value[12]);
                               $economicidade_2 = ($diferenca_2 != 0) ?number_format(((($diferenca_2 * 100) / $value[11])), 2, ',', '.') : '0';
                           
                               $html .= '<td align="center">'.$economicidade_2 . ' %</td>
                       </tr>';
                    } 
               
        $html .= '</td>
            </tr>';
     }
    $html .= '</table>';

    echo utf8_decode($html);
    die();

}

#Gerar arquivo csv para download
function gerarCsv($dados){

    header( 'Content-type: application/csv;charset=UTF-8' );   
    header( 'Content-Disposition: attachment; filename=RelatorioDeMonitoramento.csv' );   
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Pragma: no-cache');

    //fclose( $out );
    $out = fopen( 'php://output', 'w' );
    $csv = array('NÚMERO/ANO PROTOCOLO DO PROCESSO','ÓRGÃO DEMANDANTE','OBJETO','NÚMERO CI','NÚMERO OFÍCIO','DATA ENTRADA PROTOCOLO','SCC','PROCESSO','ÚLTIMA FASE','AÇÃO','ENTRADA','SAÍDA','ATRASO','AGENTE DE TRAMITAÇAO','USUÁRIO RESPONSÁVEL','OBSERVAÇÃO','ENTRADA PROTOCOLO','ESTIMADO LICITAÇÃO','ECONOMICIDADE %','ESTIMADO (ITENS QUE LOGRARAM ÊXITO)','HOMOLOGADO (ITENS QUE LOGRARAM ÊXITO)','ECONOMICIDADE %');
    fputcsv($out, $csv);
    foreach($dados as $value) { 
        $csv = array();
        
        $csv[] = str_pad($value[1], 4, "0", STR_PAD_LEFT).'/'.$value[5];
        $csv[] = $value[2];
        $csv[] = $value[4];

        if(!empty($value[6])) { 
            $csv[] = $value[6];
        }else{
            $csv[] = ' - ';
        };

        if(!empty($value[7])) { 
            $csv[] =$value[7];
        }else{ 
            $csv[] = ' - ';
        };

        $csv[] = DataBarra($value[3]);
        
        if(!empty($value[8])){ 
            $csv[] = getNumeroSolicitacaoCompra($GLOBALS["db"], $value[8]);
                
        }else{
            $csv[] = ' - '; 
        }
        
        if(!empty($value[17])) {
            //$processo
            $csv[] = str_pad($value[17], 4, "0", STR_PAD_LEFT) . '/' . $value[18]. ' - '. $value[16];
            //Fase
            $csv[] = $value[15];
        }else{
            //$processo
            $csv[] = ' - ';
            //Fase
            $csv[] = ' - ';
        }


        $csv[] = $value['ultimo_passo'][2];
        $csv[] = DataBarra($value['ultimo_passo'][3]);

        if(!empty($value['ultimo_passo'][5])){
            $csv[] = DataBarra($value['ultimo_passo'][5]);
        }else{ 
            $csv[] = ' - ';
        }
        
        $csv[] = $value['ultimo_passo']['atraso'];
        $csv[] = strtoupper2($value['ultimo_passo'][0]);
        $csv[] = strtoupper2($value['ultimo_passo'][1]);
        $csv[] = $value['ultimo_passo'][6];
        $csv[] = 'R$ '. converte_valor_estoques2($value[9]);
        $csv[] = 'R$ '. converte_valor_estoques2($value[10]);
        //cálculos prévios
        $diferenca_1 = floatval($value[9]) - floatval($value[10]);
        $economicidade_1 = ( ($diferenca_1 != 0) && ($value[9] != 0)) ? number_format(((($diferenca_1 * 100) / $value[9])), 2, ',', '.') : '0';
        
        $csv[] = $economicidade_1 . ' %';
        $csv[] = converte_valor_estoques2($value[11]);
        $csv[] = converte_valor_estoques2($value[12]);

        //cálculos previos
        $diferenca_2 = floatval($value[11]) - floatval($value[12]);
        $economicidade_2 = ($diferenca_2 != 0) ?number_format(((($diferenca_2 * 100) / $value[11])), 2, ',', '.') : '0';
        
        $csv[] = $economicidade_2 . ' %'; 

        //fwrite($out, $csv);


        fputcsv($out, $csv);
    
    } 

    fclose( $out );
    die();
}

#Gerar arquivo csv para download
function gerarCsvRelGeralTramitacao($dados, $arrBase, $arrAcoes, $titulo = null, $dadosExtras = null){

	
    header( 'Content-type: application/csv;charset=UTF-8' );   
    header( 'Content-Disposition: attachment; filename=RelGerencialTramitacao.csv' );   
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Pragma: no-cache');

    //fclose( $out );
    $out = fopen( 'php://output', 'w' );

    //titulos
    $csv = array();

    $csv[] = $titulo;

    foreach($arrAcoes as $objAcao) {
        $csv[] = $objAcao[0];
    }

    $csv[] = 'PRAZO PREVISTO';
    $csv[] = 'PRAZO REALIZADO';
    $csv[] = 'ATRASO';

    fputcsv($out, $csv);

    //conteudo
    foreach($arrBase as $value) {

        $csv = array();

        $mediaRealizado = 0;
        $mediaPrevisto = 0;
        $htmlAcoesDados = '';

        $csv[] = $value[0]; //modalidade

        foreach($arrAcoes as $objAcao) {
            
            if($titulo == 'MODALIDADE'){
                $arrMedia = getMediaDiasAcao($objAcao[1], $value[1], $dados);
            }else if($titulo == 'COMISSÃO'){
            //modalidade
                $arrMedia = getMediaDiasAcaoComissao($objAcao[1], $dadosExtras['codModalidade'] ,$value[1], $dados);
            }else if($titulo == 'PROCESSO'){
            //Comissão
                $arrMedia = getMediaDiasAcaoProcesso($objAcao[1], $dadosExtras['codModalidade'] , $dadosExtras['codComissao'], $value[0], $value[1], $dados);
            }


            if(!is_int($arrMedia[0])){
                $media = (int)$arrMedia[0];
            }else{
                $media = $arrMedia[0];
            }
            

            // if(!is_int($arrMedia[0])){
            //     $media = number_format($arrMedia[0], 2, ',', '');
            // }else{
            //     $media = $arrMedia[0];
            // }
            
            $mediaRealizado = $mediaRealizado + $arrMedia[0];
            $mediaPrevisto = $mediaPrevisto + $arrMedia[1];

            
            $csv[] = $media;

        }    
        if(!is_int($mediaPrevisto)){
            $csv[] = (int)$mediaPrevisto;
        }else{
            $csv[] = $mediaPrevisto ;
        }
        // $csv[] = $mediaPrevisto;

        if(!is_int($mediaRealizado)){
            $csv[] = (int)$mediaRealizado;
        }else{
            $csv[] = $mediaRealizado ;
        }

        //atraso
        $atraso = $mediaRealizado - $mediaPrevisto;
        if($atraso > 0){
            if(!is_int($atraso)){
                $csv[] = (int)$atraso;
            }else{
                $csv[] = $atraso ;
            }
        }else{
            $csv[] = '0';
        }

        fputcsv($out, $csv);
     }



    fclose( $out );
    die();
}



function gerarCsvRelGeralTramitacaoProcesso($dados, $arrBase, $arrAcoes, $titulo = null, $dadosExtras = null){

	
    header( 'Content-type: application/csv;charset=UTF-8' );   
    header( 'Content-Disposition: attachment; filename=RelGerencialTramitacaoProcesso.csv' );   
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Pragma: no-cache');

    //fclose( $out );
    $out = fopen( 'php://output', 'w' );

    //titulos
    $csv = array();

    $csv[] = 'AÇÃO';
    $csv[] = 'AGENTE';
    $csv[] = 'USUÁRIO RESPONSÁVEL';
    $csv[] = 'PRAZO PREVISTO';
    $csv[] = 'PRAZO REALIZADO';
    $csv[] = 'ATRASO';

    fputcsv($out, $csv);

    $mediaRealizado = 0;
    $mediaPrevisto = 0;
    foreach($arrAcoes as $objAcao) {

        $csv = array();


        $arrMedia = getMediaDiasAcaoProcessoDetalhes($objAcao[1], $dadosExtras['codModalidade'] , $dadosExtras['codComissao'], $dadosExtras['codProcesso'], $dadosExtras['anoProcesso'], $dados);
        // //($arrMedia);
        if($arrMedia){
            $mediaRealizado = $mediaRealizado + $arrMedia[3];
            $mediaPrevisto = $mediaPrevisto + $arrMedia[2];        

            $csv[] = $objAcao[0]; 
            $csv[] = $arrMedia[0];
            $csv[] = $arrMedia[1];

            if(!is_int($arrMedia[2])){
                $csv[] = (int)$arrMedia[2];
            }else{
                //if($arrMedia[2]){
                    $csv[] = $arrMedia[2];
                //}
                    
            }
                
            if(!is_int($arrMedia[3])){
                $csv[] = (int)$arrMedia[3];
            }else{
                $csv[] = $arrMedia[3];
            }

            $atraso = $arrMedia[3] - $arrMedia[2];
            if($atraso > 0){
                if(!is_int($atraso)){
                    $csv[] = (int)$atraso;
                }else{
                    $csv[] = $atraso ;
                }
            }else{
                $csv[] = '0';
            }

            fputcsv($out, $csv);
        }
     
    }
    $csv = array();
    $atraso = $mediaRealizado - $mediaPrevisto;
    $csv[] = '';                            
    $csv[] = '';                            
    $csv[] = 'Total';                            
    $csv[] = $mediaPrevisto;                            
    $csv[] = $mediaRealizado;                            
    $csv[] = $atraso > 0 ? $atraso:0;                            
    fputcsv($out, $csv);

    fclose( $out );
    die();
}



# LISTA SOLICITAÇÕES INDIVIDUAL
function listarIndividual($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao, $boolFiltrarGrupo = true, $filtrarPelaComissaoUsuario = false)
{
	
    $arrLinhas = array();

    $db            = $GLOBALS["db"];
    // 	$Situacao 		= $GLOBALS["Situacao"];
    // 	$Orgao 			= $GLOBALS["Orgao"];
    // 	$DataIni  		= $GLOBALS["DataIni"];
    // 	$DataFim  		= $GLOBALS["DataFim"];
    // 	$strSolicitacao = $GLOBALS["strSolicitacao"];

    //Procurando as comissão de licitação do usuario logado
    $intCodUsuario    = $_SESSION['_cusupocodi_'];
    $arrComissaoLicitacao = array();
    $sqlComiss = "SELECT CCOMLICODI FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $intCodUsuario";
    $res  = $db->query($sqlComiss);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $arrComissaoLicitacao[] = $Linha[0];
        }
    }
    //removendo o filtro para testes
    //unset($comissaoLicitacao);


     $sql = "SELECT
		SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
		ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
		CEN.ECENPODESC, CEN.ECENPODETA, COM.CCOMLICODI,
		COM.ECOMLIDESC, SOL.FSOLCORGPR, SOL.ESOLCOOBJE,
		SOL.FSOLCOCONT, ITEM.EITESCDESCMAT, ITEM.EITESCDESCSE,
        LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CCOMLICODI
	FROM
		SFPC.TBSOLICITACAOCOMPRA AS SOL
    LEFT JOIN SFPC.tbsolicitacaolicitacaoportal LIC
		on SOL.csolcosequ = LIC.csolcosequ 
	JOIN
		SFPC.TBORGAOLICITANTE AS ORG
			ON SOL.CORGLICODI = ORG.CORGLICODI
	JOIN
		SFPC.TBSITUACAOSOLICITACAO AS SSO
			ON SOL.CSITSOCODI = SSO.CSITSOCODI
	JOIN
		SFPC.TBCENTROCUSTOPORTAL AS CEN
			ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
	LEFT JOIN
		SFPC.TBCOMISSAOLICITACAO AS COM
			ON SOL.CCOMLICOD1 = COM.CCOMLICODI
    JOIN SFPC.TBITEMSOLICITACAOCOMPRA AS ITEM
            ON SOL.CSOLCOSEQU = ITEM.CSOLCOSEQU
	WHERE
		SOL.CTPCOMCODI = 2
		";

    /*if ($filtrarPelaComissaoUsuario) {
        if (count($arrComissaoLicitacao)>0) {
            $strComissao = implode(",", $arrComissaoLicitacao);
            $sql .= " AND SOL.CCOMLICOD1 in($strComissao) ";
        }
    }*/
    //Filtrando Pela Situação
    if ($Situacao != ""&SoNumeros($Situacao)) {
        $sql .= " AND SSO.CSITSOCODI = $Situacao ";
    }
    //Filtrando Pelo orgao
    if ($Orgao != "TODOS") {
        $sql .= " AND ORG.CORGLICODI = ".$Orgao;//SOL
    }

    //Filtrando Pela data
    if ($DataIni != "" and $DataFim != "") {
        $sql .= " AND DATE(SOL.TSOLCODATA)  >= '".DataInvertida($DataIni)."' AND DATE(SOL.TSOLCODATA)  <= '".DataInvertida($DataFim)."' ";
    }
    if (isset($strSolicitacao) & is_numeric($strSolicitacao)) {
        $sql .= " AND SOL.CSOLCOSEQU = $strSolicitacao ";
    }
    if (isset($boolFiltrarGrupo)&$boolFiltrarGrupo) {
        $sql .= " AND SOL.CSOLCOSEQU NOT IN (SELECT CSOLCOSEQU FROM SFPC.TBAGRUPASOLICITACAO)";
    }
    $sql .= " ORDER BY ORG.EORGLIDESC ASC, CEN.ECENPODESC, CEN.ECENPODETA, SOL.CSOLCOSEQU, SOL.ASOLCOANOS DESC ";



    $res  = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $SeqSolicitacao = "";
        $DataSolicitacao = "";
        $CodSituacao = "";
        $CodComissaoLici = "";
        $TipoRegistroPreco = "";
        $FlagGeraContrato = "";

        while ($Linha = $res->fetchRow()) {
            /* OLD - [CR123141]: REDMINE 23 (P4)
             if (
                ($SeqSolicitacao != $Linha[0]) &&
                ($DataSolicitacao != $Linha[1]) &&
                ($CodSituacao != $Linha[4]) &&
                ($CodComissaoLici != $Linha[8]) &&
                ($TipoRegistroPreco != $Linha[10]) &&
                ($FlagGeraContrato != $Linha[12])

            )
             */

            // if (
            //     ($SeqSolicitacao != $Linha[0]) &&
            //     ($DataSolicitacao != $Linha[1]) &&
            //     ($CodSituacao != $Linha[4]) &&
            //     ($CodComissaoLici != $Linha[8]) &&
            //     (($TipoRegistroPreco != $Linha[10]) || ($FlagGeraContrato != $Linha[12]))

            // ) {
                $linhaRetorno['SeqSolicitacao']    = $Linha[0];             // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['DataSolicitacao']    = DataBarra($Linha[1]);  // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['CodOrgao']            = $Linha[2];             // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
                $linhaRetorno['DescOrgao']            = $Linha[3];             // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
                $linhaRetorno['CodSituacao']        = $Linha[4];             // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
                $linhaRetorno['DescSolicitacao']    = $Linha[5];             // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
                $linhaRetorno['DescCentroCusto']    = $Linha[6];             // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
                $linhaRetorno['DetaCentroCusto']    = $Linha[7];             // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
                $linhaRetorno['CodComissaoLici']    = $Linha[8];             // COM.CCOMLICODI, /* CÓDIGO DA COMISSÃO DE LICITAÇÃO */
                $linhaRetorno['DescComissaoLici']    = $Linha[9];             // COM.ECOMLIDESC, /* DESCRIÇÃO DA COMISSÃO DE LICITAÇÃO */
                $linhaRetorno['TipoRegistroPreco']    = $Linha[10];             // SOL.FSOLCORGPR, /* Tipo de Compra Registro de Preço (S - Sim ou N - Não) */
                $linhaRetorno['ObjetoSolicitacao']    = $Linha[11];             // SOL.ESOLCOOBJE, /* OBJETO DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['FlagGeraContrato']    = $Linha[12];             // SOL.FSOLCOCONT, /* Flag Gera Contrato (S - Sim ou N - Não) */
                $linhaRetorno['DescDetaMat']        = $Linha[13];            // ITEM.EITESCDESCMAT Descrição detalhada de Material
                $linhaRetorno['DescDetaServ']        = $Linha[14];            // ITEM.EITESCDESCSE  Descrição detalhada de Serviço
                $linhaRetorno['numProcesso']         = $Linha[15];            // LIC.CLICPOPROC
                $linhaRetorno['anoProcesso']        = $Linha[16];             // LIC.ALICPOANOP
                $linhaRetorno['codComissaoAlt']        = $Linha[17];            // LIC.CCOMLICODI

                $SeqSolicitacao = $Linha[0];
                $DataSolicitacao = $Linha[1];
                $CodSituacao = $Linha[4];
                $CodComissaoLici = $Linha[8];
                $TipoRegistroPreco = $Linha[10];
                $FlagGeraContrato = $Linha[12];

                $arrLinhas[] = $linhaRetorno;
            //}
        }
    }

    return $arrLinhas;
    //return $sql;
}

function listarIndividualLicitacaoIncluir($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao, $boolFiltrarGrupo = true, $filtrarPelaComissaoUsuario = false)
{
	
	$arrLinhas = array();
    $db = $GLOBALS["db"];

    //Procurando as comissão de licitação do usuario logado
    $intCodUsuario = $_SESSION['_cusupocodi_'];
    $arrComissaoLicitacao = array();
    $sqlComiss = "SELECT CCOMLICODI FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $intCodUsuario";
    $res = $db->query($sqlComiss);

    if(!isError($res)) {
        while ($Linha = $res->fetchRow()) {
            $arrComissaoLicitacao[] = $Linha[0];
        }
    }

    $sql = "SELECT DISTINCT 
		SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
		ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
		CEN.ECENPODESC, CEN.ECENPODETA, COM.CCOMLICODI,
		COM.ECOMLIDESC, SOL.FSOLCORGPR, SOL.ESOLCOOBJE,
		SOL.FSOLCOCONT, ITEM.EITESCDESCMAT, ITEM.EITESCDESCSE
	FROM
		SFPC.TBSOLICITACAOCOMPRA AS SOL
	JOIN
		SFPC.TBORGAOLICITANTE AS ORG
			ON SOL.CORGLICODI = ORG.CORGLICODI
	JOIN
		SFPC.TBSITUACAOSOLICITACAO AS SSO
			ON SOL.CSITSOCODI = SSO.CSITSOCODI
	JOIN
		SFPC.TBCENTROCUSTOPORTAL AS CEN
			ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
	LEFT JOIN
		SFPC.TBCOMISSAOLICITACAO AS COM
			ON SOL.CCOMLICOD1 = COM.CCOMLICODI
    JOIN SFPC.TBITEMSOLICITACAOCOMPRA AS ITEM
            ON SOL.CSOLCOSEQU = ITEM.CSOLCOSEQU
	WHERE
		SOL.CTPCOMCODI = 2 ";

   /* if ($filtrarPelaComissaoUsuario) {
        if (count($arrComissaoLicitacao) > 0) {
            $strComissao = implode(",", $arrComissaoLicitacao);
            $sql .= " AND SOL.CCOMLICOD1 in($strComissao) ";
        }
    }*/

    //Filtrando Pela Situação
    //conferir com Rossana se manterá isso 17-10-2018
    /*if ($Situacao != "") {
        $sql .= " AND SSO.CSITSOCODI IN $Situacao ";
    }*/

    //Filtrando Pelo orgao
    if ($Orgao != "TODOS") {
        $sql .= " AND ORG.CORGLICODI = ".$Orgao; //SOL
    }

    if ($_SESSION['grupo_selecionado_protocolo']) {
       $sql .= " AND ".$_SESSION['grupo_selecionado_protocolo']." IN 
                (select gruorg.cgrempcodi from sfpc.tbgrupoorgao gruorg
                where gruorg.CORGLICODI = SOL.CORGLICODI)"; 
    }

    //Filtrando Pela data
    if ($DataIni != "" and $DataFim != "") {
        $sql .= " AND DATE(SOL.TSOLCODATA)  >= '".DataInvertida($DataIni)."' AND DATE(SOL.TSOLCODATA)  <= '".DataInvertida($DataFim)."' ";
    }
    if (isset($strSolicitacao) & is_numeric($strSolicitacao)) {
        $sql .= " AND SOL.CSOLCOSEQU = $strSolicitacao ";
    }
    if (isset($boolFiltrarGrupo) & $boolFiltrarGrupo) {
        $sql .= " AND SOL.CSOLCOSEQU NOT IN (SELECT CSOLCOSEQU FROM SFPC.TBAGRUPASOLICITACAO)";
    }
    $sql .= " ORDER BY SOL.CSOLCOSEQU ASC";
    $res = $db->query($sql);

    if(!isError($res)) {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['SeqSolicitacao'] = $Linha[0];     // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['DataSolicitacao'] = DataBarra($Linha[1]);  // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['CodOrgao'] = $Linha[2];     // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
            $linhaRetorno['DescOrgao'] = $Linha[3];     // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
            $linhaRetorno['CodSituacao'] = $Linha[4];    // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
            $linhaRetorno['DescSolicitacao'] = $Linha[5];    // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
            $linhaRetorno['DescCentroCusto'] = $Linha[6];    // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['DetaCentroCusto'] = $Linha[7];    // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['CodComissaoLici'] = $Linha[8];    // COM.CCOMLICODI, /* CÓDIGO DA COMISSÃO DE LICITAÇÃO */
            $linhaRetorno['DescComissaoLici'] = $Linha[9];    // COM.ECOMLIDESC, /* DESCRIÇÃO DA COMISSÃO DE LICITAÇÃO */
            $linhaRetorno['TipoRegistroPreco'] = $Linha[10];     // SOL.FSOLCORGPR, /* Tipo de Compra Registro de Preço (S - Sim ou N - Não) */
            $linhaRetorno['ObjetoSolicitacao'] = $Linha[11];     // SOL.ESOLCOOBJE, /* OBJETO DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['FlagGeraContrato'] = $Linha[12];     // SOL.FSOLCOCONT, /* Flag Gera Contrato (S - Sim ou N - Não) */
            $linhaRetorno['DescDetaMat'] = $Linha[13];            // ITEM.EITESCDESCMAT Descrição detalhada de Material
            $linhaRetorno['DescDetaServ'] = $Linha[14];            // ITEM.EITESCDESCSE  Descrição detalhada de Serviço
            $arrLinhas[] = $linhaRetorno;
        }
    }

    return $arrLinhas;
}

function listarGrupo($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao = "", $strCodGrupo = "", $filtrarPelaComissaoUsuario = false)
{
	
	$arrLinhasGrupo    = array();
    $db = $GLOBALS["db"];

    //Procurando comissão de licitação do usuario logado
    //Procurando as comissão de licitação do usuario logado
    $intCodUsuario    = $_SESSION['_cusupocodi_'];
    $arrComissaoLicitacao = array();
    $sqlComiss = "SELECT CCOMLICODI FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $intCodUsuario";
    $res  = $db->query($sqlComiss);

    if(!isError($res)) {
        while ($Linha = $res->fetchRow()) {
            $arrComissaoLicitacao[] = $Linha[0];
        }
    }
    //removendo o filtro para testes
    //unset($comissaoLicitacao);


    $sql1 = "SELECT
		DISTINCT (AGR.CAGSOLSEQU) AS GRUPO
	FROM
		SFPC.TBAGRUPASOLICITACAO AS AGR
	JOIN
		SFPC.TBSOLICITACAOCOMPRA AS SOL
			ON AGR.CSOLCOSEQU = SOL.CSOLCOSEQU
	JOIN
		SFPC.TBSITUACAOSOLICITACAO AS SSO
			ON SOL.CSITSOCODI = SSO.CSITSOCODI
	JOIN
		SFPC.TBORGAOLICITANTE AS ORG
			ON SOL.CORGLICODI = ORG.CORGLICODI
	WHERE
		SOL.CTPCOMCODI = 2 ";
    /*if ($filtrarPelaComissaoUsuario) {
        if (count($arrComissaoLicitacao)>0) {
            $strComissao = implode(",", $arrComissaoLicitacao);
            $sql .= " AND SOL.CCOMLICOD1 in($strComissao) ";
        }
    }*/
    //Filtrando Pela Situação
    if ($Situacao != "") {
        $sql1 .= " AND SSO.CSITSOCODI IN $Situacao ";
    }
    //Filtrando Pelo orgao
    if ($Orgao != "TODOS") {
        $sql1 .= " AND ORG.CORGLICODI = ".$Orgao;//SOL
    }
    //Filtrando Pela data
    if ($DataIni != "" and $DataFim != "") {
        $sql1 .= " AND DATE(SOL.TSOLCODATA)  >= '".DataInvertida($DataIni)."' AND DATE(SOL.TSOLCODATA)  <= '".DataInvertida($DataFim)."' ";
    }
    //Filtrando pelo código do grupo
    if (isset($strCodGrupo) & is_numeric($strCodGrupo)) {
        $sql1 .= " AND AGR.CAGSOLSEQU = $strCodGrupo ";
    }
    //Filtrando pelo código do grupo
    if (isset($strSolicitacao) & is_numeric($strSolicitacao)) {
        $sql1 .= " AND AGR.CSOLCOSEQU = $strSolicitacao ";
    }

    $sql = "SELECT
		SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
		ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
		CEN.ECENPODESC, CEN.ECENPODETA, GRU.CAGSOLSEQU,
		GRU.FAGSOLFLAG, GRU.TAGSOLULAT, COM.CCOMLICODI,
		COM.ECOMLIDESC, SOL.FSOLCORGPR, SOL.ESOLCOOBJE,
		SOL.FSOLCOCONT
	FROM
		SFPC.TBSOLICITACAOCOMPRA AS SOL
	JOIN
		SFPC.TBORGAOLICITANTE AS ORG
			ON SOL.CORGLICODI = ORG.CORGLICODI
	JOIN
		SFPC.TBSITUACAOSOLICITACAO AS SSO
			ON SOL.CSITSOCODI = SSO.CSITSOCODI
	JOIN
		SFPC.TBCENTROCUSTOPORTAL AS CEN
			ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
	JOIN
		SFPC.TBAGRUPASOLICITACAO AS GRU
			ON SOL.CSOLCOSEQU = GRU.CSOLCOSEQU
	LEFT JOIN
		SFPC.TBCOMISSAOLICITACAO AS COM
			ON SOL.CCOMLICOD1 = COM.CCOMLICODI
	WHERE
		GRU.CAGSOLSEQU IN ($sql1)";

    $sql .= " ORDER BY GRU.CAGSOLSEQU, GRU.FAGSOLFLAG DESC, ORG.EORGLIDESC, SOL.CSOLCOSEQU DESC";
    $res  = $db->query($sql);

    if(!isError($res)) {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['SeqSolicitacao']    = $Linha[0];             // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['DataSolicitacao']    = DataBarra($Linha[1]);  // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['CodOrgao']            = $Linha[2];             // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
            $linhaRetorno['DescOrgao']            = $Linha[3];             // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
            $linhaRetorno['CodSituacao']        = $Linha[4];             // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
            $linhaRetorno['DescSolicitacao']    = $Linha[5];             // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
            $linhaRetorno['DescCentroCusto']    = $Linha[6];             // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['DetaCentroCusto']    = $Linha[7];             // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['CodGrupo']            = $Linha[8];             // GRU.CAGSOLSEQU, /* CÓDIGO SEQUENCIAL DO AGRUPAMENTO DAS LICITAÇÕES */
            $linhaRetorno['FlagGrupo']            = $Linha[9];             // GRU.FAGSOLFLAG, /* FLAG QUE INDICA A SCC COM O ÓRGÃO GESTOR RESPONSÁVEL PELO AGRUPAMENTO - S/N */
            $linhaRetorno['DataAgrupamento']    = DataBarra($Linha[10]); // GRU.TAGSOLULAT, /* DATA E HORA DA ÚLTIMA ATUALIZAÇÃO */
            $linhaRetorno['CodComissaoLici']    = $Linha[11];             // COM.CCOMLICODI, /* CÓDIGO DA COMISSÃO DE LICITAÇÃO */
            $linhaRetorno['DescComissaoLici']    = $Linha[12];             // COM.ECOMLIDESC, /* DESCRIÇÃO DA COMISSÃO DE LICITAÇÃO */
            $linhaRetorno['TipoRegistroPreco']    = $Linha[13];             // SOL.FSOLCORGPR, /* Tipo de Compra Registro de Preço (S - Sim ou N - Não) */
            $linhaRetorno['ObjetoSolicitacao']    = $Linha[14];             // SOL.ESOLCOOBJE, /* OBJETO DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['FlagGeraContrato']    = $Linha[15];             // SOL.FSOLCOCONT, /* Flag Gera Contrato (S - Sim ou N - Não) */

            $arrLinhasGrupo[] = $linhaRetorno;
        }
    }

    return $arrLinhasGrupo;
}

#
# Função copiada de funcoesComplementaresLicitacao.php
#
function exibeDetalhamento($SeqSolicitacao)
{
    ?>
	
	
	
    <!-- INÍCIO DO DETALHAMENTO DA SOLICITAÇÃO -->
    <tr style="display:none;" class="opdetalhe <?php echo $SeqSolicitacao; ?>">
        <td style="background-color:#F1F1F1;" colspan="4">
            <table bordercolor="#75ADE6" border="1" bgcolor="bfdaf2" width="100%" class="textonormal">
                <tr>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">TIPO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">CÓD.RED</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">QUANTIDADE</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR ESTIMADO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR TOTAL</td>
                </tr>
                <?php
                $arrayDetalhamento = infoDetalhamento($SeqSolicitacao);
                foreach ($arrayDetalhamento as $itens) {
                    ?>
                    <tr>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['OrdItemSoli'];
                            ?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['DescMaterial'];
                            ?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['Tipo'];
                            ?></td>
                        <td	class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['CodMaterial'];
                            ?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_quant($itens['QtdItemSoli']);
                            ?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['VlrUnitItem']);
                            ?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['QtdItemSoli']*$itens['VlrUnitItem']);
                            ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </td>
    </tr>
    <!-- FIM DO DETALHAMENTO DA SOLICITAÇÃO -->
    <?php

}

#
# Função copiada de funcoesComplementaresLicitacao.php
#
function infoDetalhamento($SeqSolicitacao)
{
	
    $arrInfo = array();

    $db = $GLOBALS["db"];
    $sql = "SELECT
		ITEM.CITESCSEQU, 	ITEM.CMATEPSEQU, ITEM.CSERVPSEQU,
		ITEM.AITESCORDE, 	ITEM.AITESCQTSO, ITEM.VITESCUNIT,
		ITEM.VITESCVEXE, 	MAT.EMATEPDESC,  SERV.ESERVPDESC,
		ITEM.EITESCDESCSE, 	MAT.CUNIDMCODI,  ITEM.AITESCQTEX,
		ITEM.EITESCDESCMAT , ITEM.EITESCDESCSE, UNIDADE.EUNIDMSIGL
	FROM
		SFPC.TBITEMSOLICITACAOCOMPRA ITEM
	LEFT JOIN
		SFPC.TBMATERIALPORTAL MAT ON (MAT.CMATEPSEQU = ITEM.CMATEPSEQU)
	LEFT JOIN
		SFPC.TBSERVICOPORTAL SERV ON (SERV.CSERVPSEQU = ITEM.CSERVPSEQU)
	LEFT JOIN
		SFPC.TBUNIDADEDEMEDIDA UNIDADE ON (MAT.CUNIDMCODI = UNIDADE.CUNIDMCODI)
	WHERE
            ITEM.CSOLCOSEQU = $SeqSolicitacao ORDER BY ITEM.CITESCSEQU ASC";

    $res  = $db->query($sql);

    if(!isError($res)) {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['CodSeqItens']    = $Linha[0];        //ITEM.CITESCSEQU - Código sequencial dos itens da solicitação de compras
            $linhaRetorno['CodMaterial']    = $Linha[1];        //ITEM.CMATEPSEQU - Código do Material
            $linhaRetorno['CodServPortal']    = $Linha[2];        //ITEM.CSERVPSEQU - Código do Servico Portal
            $linhaRetorno['OrdItemSoli']    = $Linha[3];        //ITEM.AITESCORDE - Ordem do item na solicitação de compras
            $linhaRetorno['QtdItemSoli']    = $Linha[4];        //ITEM.AITESCQTSO - Quantidade do item na solicitação de compras
            $linhaRetorno['VlrUnitItem']    = $Linha[5];        //ITEM.VITESCUNIT - Valor unitário do item (estimado / Cotado / da Ata)
            $linhaRetorno['VlrItemSoli']    = $Linha[6];        //ITEM.VITESCVEXE  - Valor no exercício do item na solicitação de compras
            $linhaRetorno['DescMaterial']    = $Linha[7];        //MAT.EMATEPDESC - Descricao do material
            $linhaRetorno['DescServico']    = $Linha[8];        //SERV.ESERVPDESC - Descricao do servico
            $linhaRetorno['DescDetaServ']    = $Linha[9];        //ITEM.EITESCDESCSE - Descrição detalhada do item de Serviço
            $linhaRetorno['QtdExercicio']    = $Linha[11];        //ITEM.AITESCQTEX - Quantidade no Exercício
            $linhaRetorno['DescDetaMat']    = $Linha[12];        //ITEM.EITESCDESCMAT - Descrição detalhada do item de Material
            $linhaRetorno['DescDetaServ']    = $Linha[13];        //ITEM.EITESCDESCSE - Descrição detalhada do item de Serviço
            $linhaRetorno['Unidade']        = $Linha[14];        //MAT.CUNIDMCODI - Unidade

            //$linhaRetorno['DescServicoDetalhado']	= $Linha[12]; 		//ITEM.AITESCQTEX - Descrição detalhada do serviço

            #Se é material
            if ($linhaRetorno['CodMaterial'] != "") {
                $linhaRetorno['DescDet']    = $Linha[12];        //ITEM.EITESCDESCMAT - Descrição detalhada do material
                $linhaRetorno['Tipo'] = "CADUM";
            } else {
                $linhaRetorno['CodMaterial'] = $linhaRetorno['CodServPortal'];
//				$linhaRetorno['DescDet'] = $linhaRetorno['DescServico']."-".$linhaRetorno['DescServicoDetalhado'];
                $linhaRetorno['DescDet']    = $Linha[9];        //ITEM.EITESCDESCSE - Descrição detalhada do serviço
                $linhaRetorno['Tipo'] = "CADUS";
                $linhaRetorno['ValorTrp'] = '-';
            }

            $arrInfo[] = $linhaRetorno;
        }
    }

    return $arrInfo;
}

function dadosParametrosGerais()
{
	
    $db = $GLOBALS["db"];
    $sql = ' select qpargetmaobjeto, qpargetmajustificativa, qpargedescse,
                        epargesubelemespec, qpargeqmac, qpargeqmac, epargetdov
             from sfpc.tbparametrosgerais ';
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}

function getNumeroProtocolo($grupo, $ano) {
    
	
	$db = $GLOBALS["db"];
    $sql = ' SELECT MAX(TP.CPROTCNUMP) AS cont FROM SFPC.TBTRAMITACAOPROTOCOLO TP WHERE TP.CGREMPCOD1 = ' . $grupo . ' AND TP.APROTCANOP = ' . $ano;
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        $numero = $array[0];
        return $numero + 1;
    }
}

function getSequencial() {
    $db = $GLOBALS["db"];
    $sql = ' SELECT cprotcsequ FROM sfpc.tbtramitacaoprotocolo order by cprotcsequ desc limit 1';
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        if($res->numRows()>0){
            while ($Linha = $res->fetchRow()) {
                $array = $Linha;
            }
    
            $numero = $array[0] + 1;
        }else{
            $numero = 1;
        }

        return $numero;
    }
}

function getSequencialTramitacaoLicitacao($seqTramitacao = null) {
    
	$db = $GLOBALS["db"];
    $sql = ' SELECT ctramlsequ FROM sfpc.tbtramitacaolicitacao where cprotcsequ ='.$seqTramitacao;
    $sql.= ' order by ctramlsequ desc limit 1';

    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        if($res->numRows()>0){
            while ($Linha = $res->fetchRow()) {
                $array = $Linha;
            }
            $numero = $array[0] + 1;
            return $numero;
        }else{
            return 1;
        }

    }
}

function getInicialAgente($grupo) {
    
	$db = $GLOBALS["db"];
    $sql = ' SELECT MAX(ctagensequ) FROM sfpc.tbtramitacaoagente TA ';
    $sql .= "   WHERE TA.CGREMPCODI = " . $grupo;
    $sql .= "   AND TA.FTAGENSITU = 'A' AND TA.FTAGENINIC = 'S'";
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        $numero = $array[0];
        return $numero;
    }
}

function verificaOficioEmProtocolos($numeroOficioAtual, $orgaoAtual, $protocoloAtual = null){
    
	$db = $GLOBALS["db"];
    $sql = "  SELECT PR.cprotcnump, PR.APROTCANOP FROM SFPC.TBTRAMITACAOPROTOCOLO PR ";
    $sql .= " WHERE PR.EPROTCNUOF = '" . $numeroOficioAtual."' ";
    $sql .= " AND PR.CORGLICOD1 = " . $orgaoAtual;

    if($protocoloAtual){
        $sql .= " AND PR.CPROTCSEQU <> " . $protocoloAtual;
    }

    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}


function verificaSccJaCadastrada($numeroScc, $protocoloAtual = null){
    
	$db = $GLOBALS["db"];
    $sql = '  SELECT PR.CPROTCSEQU FROM SFPC.TBTRAMITACAOPROTOCOLO PR ';
    $sql .= ' WHERE PR.CSOLCOSEQU = ' . $numeroScc;

    if($protocoloAtual){
        $sql .= " AND PR.CPROTCSEQU <> " . $protocoloAtual;
    }

    $res  = $db->query($sql);



    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}

function verificaProcessoEmProtocolos($numero, $ano, $comissao, $protocoloAtual = null) {
    
	$db = $GLOBALS["db"];
    $sql = '  SELECT PR.CPROTCNUMP, PR.APROTCANOP FROM SFPC.TBTRAMITACAOPROTOCOLO PR ';
    $sql .= ' WHERE PR.CLICPOPROC = ' . $numero;
    $sql .= ' AND PR.ALICPOANOP = ' . $ano;
    $sql .= ' AND PR.CCOMLICODI = ' . $comissao;

    if($protocoloAtual){
        $sql .= " AND PR.CPROTCSEQU <> " . $protocoloAtual;
    }

    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}

function getProcesso($numero, $ano, $comissao) {
    
	$db = $GLOBALS["db"];
    $sql = '  SELECT LP.CORGLICODI, LP.CGREMPCODI FROM SFPC.TBLICITACAOPORTAL LP ';
    $sql .= ' WHERE LP.CLICPOPROC = ' . $numero;
    $sql .= ' AND LP.ALICPOANOP = ' . $ano;
    $sql .= ' AND LP.CCOMLICODI = ' . $comissao;
    $res  = $db->query($sql);
    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}

function getData_HoraLicitacao($Processo, $Processoano, $Comissao) {
    
	$db = $GLOBALS["db"];
    $sql  = "SELECT A.TLICPODHAB  ";
    $sql .= "FROM   SFPC.TBLICITACAOPORTAL A ";
    $sql .= "WHERE  A.CLICPOPROC = " . $Processo;
    $sql .= "       AND A.ALICPOANOP = " . $Processoano;
    $sql .= "       AND A.CCOMLICODI = " . $Comissao;
    $res  = $db->query($sql);
    if(!isError($res)) {
       $Linha = $res->fetchRow();
        return $Linha;
    }
}

/**
 * Pesquisar por protocolo
 * 
 * @param $buscar array com os dados para filtrar
 * 
 * @return array
 */
function protocoloPesquisar($buscar, $pagina = null) {
        //madson Sql alterada CR 225660
	//CR 223158 - ROSSANA
    $db = $GLOBALS["db"];
    $arraySituacoesConcluidas = getIdFasesConcluidas($db);
    $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    $sql = '  SELECT DISTINCT TP.CPROTCSEQU, TP.CPROTCNUMP, OL.EORGLIDESC, TP.TPROTCENTR, TP.XPROTCOBJE, TP.APROTCANOP ';

    if($pagina == 'relMonitoramento') {
        $sql .= '  , TP.EPROTCNUCI, TP.EPROTCNUOF, TP.CSOLCOSEQU, TP.VPROTCVALE, LP.VLICPOVALE, 
                     LP.VLICPOTGES, LP.VLICPOVALH , 
					 CASE WHEN SOLIC.CLICPOPROC IS NOT NULL THEN SOLIC.CLICPOPROC ELSE LP.CLICPOPROC END,
					 CASE WHEN SOLIC.ALICPOANOP IS NOT NULL THEN SOLIC.ALICPOANOP ELSE LP.ALICPOANOP END,
					 (   select f.efasesdesc from sfpc.tbfaselicitacao fase 
                    join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                    where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
                    and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
                    and fase.cgrempcodi = TP.cgrempcodi
                    order by fase.tfaseldata desc
                    limit 1
                ) as fase_licitacao, 
                CL.ECOMLIDESC, TP.clicpoproc, TP.alicpoanop, TP.cgrempcodi, TP.ccomlicodi, 
                TP.corglicodi, (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
                join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
                and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
                and fase.cgrempcodi = TP.cgrempcodi
                order by fase.tfaseldata desc
                limit 1
            ) as fase_licitacao_cod, TP.XPROTCMONI, LP.TLICPODHAB AS data_hora';
    }

    $sql .= '  FROM SFPC.TBTRAMITACAOPROTOCOLO TP ';
    $sql .= '  LEFT JOIN SFPC.TBORGAOLICITANTE OL ON '; 
    $sql .= '       OL.CORGLICODI = TP.CORGLICOD1 ';

    // $sql .= '  LEFT JOIN SFPC.TBLICITACAOPORTAL LP ON ';
    $sql .= '  LEFT JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL SOLIC ON '; 
    // $sql .= '       LP.CLICPOPROC = TP.CLICPOPROC ';
    $sql .= '       SOLIC.CSOLCOSEQU = TP.CSOLCOSEQU ';
    $sql .= '   LEFT JOIN SFPC.TBLICITACAOPORTAL LP ON LP.CLICPOPROC = TP.CLICPOPROC ';
    $sql .= '       AND LP.ALICPOANOP = TP.ALICPOANOP ';
    $sql .= '       AND LP.CGREMPCODI = TP.CGREMPCODI ';
    $sql .= '       AND LP.CCOMLICODI = TP.CCOMLICODI ';
    $sql .= '       AND LP.CORGLICODI = TP.CORGLICODI ';
    $sql .= ' OR (LP.CLICPOPROC = SOLIC.CLICPOPROC AND LP.ALICPOANOP = SOLIC.ALICPOANOP AND LP.CCOMLICODI = SOLIC.CCOMLICODI AND LP.CGREMPCODI = SOLIC.CGREMPCODI) ';
    //$sql .= '  LEFT JOIN SFPC.TBFASELICITACAO FL ON '; 
   // $sql .= '       FL.CLICPOPROC = TP.CLICPOPROC ';
    //$sql .= '       AND FL.ALICPOANOP = TP.ALICPOANOP ';
   // $sql .= '       AND FL.CGREMPCODI = TP.CGREMPCODI ';
   // $sql .= '       AND FL.CCOMLICODI = TP.CCOMLICODI ';
   // $sql .= '       AND FL.CORGLICODI = TP.CORGLICODI ';
    $sql .= '  LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL ON '; 
    $sql .= '       CL.CCOMLICODI = TP.CCOMLICODI ';
    $sql .= '       AND CL.CGREMPCODI = TP.CGREMPCODI ';
    $sql .= '       AND CL.CCOMLICODI = TP.CCOMLICODI ';
	// $sql .= '  LEFT JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL SOLIC ON ';
	// $sql .= '  SOLIC.CSOLCOSEQU = TP.CSOLCOSEQU ';

    $sql .= '  WHERE 1=1 ';


    // Número do protocolo
    if(!empty($buscar['protocolo'])) {
        $sql .= " AND TP.CPROTCNUMP = ".$buscar['protocolo'];
    }

    // Ano do protocolo
    if(!empty($buscar['anoProtocolo'])) {
        $sql .= " AND TP.APROTCANOP = ".$buscar['anoProtocolo'];
    }

    // Número CI
    if(!empty($buscar['numeroCI'])) {
        $sql .= " AND TP.EPROTCNUCI like '%".$buscar['numeroCI']."%'";
    }

    // Número Oficio
    if(!empty($buscar['numeroOficio'])) {
        $sql .= " AND TP.EPROTCNUOF like '%".$buscar['numeroOficio']."%'";
    }

    // Número SCC
    if(!empty($buscar['numeroScc'])) {

        $retirar = array(".", "/");
        $scc = str_replace($retirar, ".", $buscar['numeroScc']);

        if(isNumeroSCCValido($scc)){
            $numscc = getSequencialSolicitacaoCompra($db, $scc);
            $sql .= " AND TP.CSOLCOSEQU = ".$numscc;
        }

    }

    // Comissão
    if(!empty($buscar['comissao'])) {
        $sql .= " AND (TP.ccomlicodi  = ".$buscar['comissao'];
        $sql .= " OR (select count(tl.*) from sfpc.tbtramitacaolicitacao as tl 
                      where tl.cprotcsequ = TP.cprotcsequ and tl.ccomlicodi = ".$buscar['comissao'].")>0 ";
        $sql .= " OR (select count(*) from sfpc.tbsolicitacaolicitacaoportal slc  
                        left join sfpc.tblicitacaoportal l on 
                        l.clicpoproc= slc.clicpoproc AND l.alicpoanop= slc.alicpoanop
                        AND l.ccomlicodi = slc.ccomlicodi
                        AND l.cgrempcodi = slc.cgrempcodi
                        AND l.corglicodi = slc.corglicodi
                        where slc.csolcosequ = TP.CSOLCOSEQU and l.ccomlicodi =  ".$buscar['comissao'].")>0 )";

    }



    // Ação
    if($pagina == 'relMonitoramento'){
        if(!empty($buscar['acao'])) {
            $sql .= " AND TP.cprotcsequ in 
                        (select distinct tram.cprotcsequ 
                        from sfpc.tbtramitacaolicitacao tram
                        where tram.ctacaosequ = ".$buscar['acao'].")";                    
        }

        // Agente Destino
        if(!empty($buscar['agente'])) {
            $sql .= " AND TP.cprotcsequ in 
                    (select tram.cprotcsequ 
                    from sfpc.tbtramitacaolicitacao tram
                    where tram.ctagensequ in (select agusu.ctagensequ
                    from sfpc.tbtramitacaoagente agusu
                    where agusu.ctagensequ = ".$buscar['agente']."
                    ))";
        }

    }else{

        if(!empty($buscar['acao'])) {
            $sql .= " AND (select tram.ctacaosequ 
                        from sfpc.tbtramitacaolicitacao tram
                        where TP.CPROTCSEQU = tram.cprotcsequ
                        limit 1) = ".$buscar['acao']." ";        
        }

        // Agente Destino
        if(!empty($buscar['agente'])) {
            $sql .= " AND  (select tram.ctagensequ from sfpc.tbtramitacaolicitacao tram
                    where TP.CPROTCSEQU = tram.cprotcsequ
                    limit 1) = ".$buscar['agente']." ";
        }
    }
    // Processo Licitatório
    if(!empty($buscar['processoNumero'])) {

        $sql .= " AND TP.clicpoproc = ".(int)$buscar['processoNumero']." ";
        //$sql .= " AND prot.ccomlicodi = $arrProLicitatorio[2] ";
       // $sql .= " AND prot.cgrempcodi = $arrProLicitatorio[3] ";
       // $sql .= " AND prot.corglicodi = $arrProLicitatorio[4] ";
    }

    // Ano Processo Licitatório
    if(!empty($buscar['processoAno'])) {

        $sql .= " AND TP.alicpoanop = ".(int)$buscar['processoAno']." ";
    }
    
    // Grupo
    if(!empty($buscar['grupo'])) {
        $sql .= " AND TP.CGREMPCOD1 = ".$buscar['grupo'];
    }

    // Objeto
    if(!empty($buscar['objeto'])) {
        $sql .= " AND TP.XPROTCOBJE like '%".$buscar['objeto']."%' ";
    }

    // Orgão
    if(!empty($buscar['orgao'])) {
        $sql .= ' AND TP.CORGLICOD1 = ' . $buscar['orgao'];
    }

    // Datas
    if(!empty($buscar['dataInicio']) && !empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR BETWEEN '" . DataInvertida($buscar['dataInicio']) . "' AND '" . DataInvertida($buscar['dataFim']) . "'";
    } else if(!empty($buscar['dataInicio'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataInicio']) . "'";
    } else if(!empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataFim']) . "'";
    }
        
    // Situação
    if (!empty($buscar['situacao'])) {
        if ($buscar['situacao'] == 'concluidas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $sql   .= " AND (((   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdConcluidas)) ";
		    $sql   .= " OR (SELECT FASE.CFASESCODI FROM SFPC.TBFASELICITACAO FASE 
			WHERE FASE.CLICPOPROC = SOLIC.CLICPOPROC AND FASE.ALICPOANOP = SOLIC.ALICPOANOP 
			AND FASE.CCOMLICODI = SOLIC.CCOMLICODI AND FASE.CGREMPCODI = SOLIC.CGREMPCODI 
			AND FASE.CORGLICODI = SOLIC.CORGLICODI ORDER BY FASE.tfaseldata DESC 
            LIMIT 1 ) 
            IN ($strIdConcluidas)) "; 
		
        } elseif ($buscar['situacao'] == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            
            $sql   .= " AND (((   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdAndamento)) "; 
            $sql   .= " OR (SELECT FASE.CFASESCODI FROM SFPC.TBFASELICITACAO FASE 
			WHERE FASE.CLICPOPROC = SOLIC.CLICPOPROC AND FASE.ALICPOANOP = SOLIC.ALICPOANOP 
			AND FASE.CCOMLICODI = SOLIC.CCOMLICODI AND FASE.CGREMPCODI = SOLIC.CGREMPCODI 
			AND FASE.CORGLICODI = SOLIC.CORGLICODI ORDER BY FASE.tfaseldata DESC 
			LIMIT 1
		) IN ($strIdAndamento)) "; 
        }
    }

    $sql .= " AND OL.FORGLISITU = 'A'";



    if (!empty($buscar['ordem'])) {

        switch ($buscar['ordem']) {
            case "numAnoDesc":
                $sql .= " ORDER BY TP.APROTCANOP DESC, TP.CPROTCNUMP DESC ";
                break;
            case "orgao":
                $sql .= " ORDER BY OL.EORGLIDESC ASC ";
                break;
        }

    }else{
        $sql .= " ORDER BY TP.APROTCANOP DESC, TP.CPROTCNUMP DESC ";
    }
    $res  = $db->query($sql);
    
    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }
	

	
}

function getFaseLicitacaoScc($scc) {
    //retorna a fase atual do processo
    $db = $GLOBALS["db"];

    $sql  = "SELECT SP.CSOLCOSEQU, ";
    $sql .= "       (SELECT F.EFASESDESC ";
    $sql .= "        FROM   SFPC.TBFASELICITACAO FASE ";
	$sql .= "               JOIN SFPC.TBFASES F ON F.CFASESCODI = FASE.CFASESCODI ";
    $sql .= "        WHERE  FASE.CLICPOPROC = SP.CLICPOPROC ";
    $sql .= "               AND FASE.ALICPOANOP = SP.ALICPOANOP ";
    $sql .= "               AND FASE.CCOMLICODI = SP.CCOMLICODI ";
    $sql .= "               AND FASE.CORGLICODI = SP.CORGLICODI ";
	$sql .= "               AND FASE.CGREMPCODI = SP.CGREMPCODI ";
	$sql .= "        ORDER BY FASE.TFASELDATA DESC, F.AFASESORDE DESC ";
    $sql .= "        LIMIT 1) AS FASE_LICITACAO, ";
    $sql .= "       (SELECT F.CFASESCODI ";
    $sql .= "        FROM   SFPC.TBFASELICITACAO FASE ";
    $sql .= "               JOIN SFPC.TBFASES F ON F.CFASESCODI = FASE.CFASESCODI ";
    $sql .= "        WHERE  FASE.CLICPOPROC = SP.CLICPOPROC ";
    $sql .= "               AND FASE.ALICPOANOP = SP.ALICPOANOP ";
    $sql .= "               AND FASE.CCOMLICODI = SP.CCOMLICODI ";
    $sql .= "               AND FASE.CORGLICODI = SP.CORGLICODI ";
    $sql .= "               AND FASE.CGREMPCODI = SP.CGREMPCODI ";
    $sql .= "        ORDER BY FASE.TFASELDATA DESC, F.AFASESORDE DESC ";
    $sql .= "        LIMIT 1) AS CODIGO_FASE, ";
    $sql .= "       (SELECT F.AFASESORDE ";
    $sql .= "        FROM   SFPC.TBFASELICITACAO FASE ";
    $sql .= "               JOIN SFPC.TBFASES F ON F.CFASESCODI = FASE.CFASESCODI ";
    $sql .= "        WHERE  FASE.CLICPOPROC = SP.CLICPOPROC ";
    $sql .= "               AND FASE.ALICPOANOP = SP.ALICPOANOP ";
    $sql .= "               AND FASE.CCOMLICODI = SP.CCOMLICODI ";
    $sql .= "               AND FASE.CORGLICODI = SP.CORGLICODI ";
    $sql .= "               AND FASE.CGREMPCODI = SP.CGREMPCODI ";
    $sql .= "        ORDER BY FASE.TFASELDATA DESC, F.AFASESORDE DESC ";
    $sql .= "        LIMIT 1) AS ORDEM_FASE ";
    $sql .= "FROM   SFPC.TBSOLICITACAOCOMPRA SCC ";
    $sql .= "       LEFT JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL SP ON SP.CSOLCOSEQU = SCC.CSOLCOSEQU ";
    $sql .= "WHERE  SCC.CSOLCOSEQU = " . $scc;
    $sql .= " LIMIT 1 ";

    $res  = $db->query($sql);

    if (!isError($res)) {
        $array = array();

        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }
}

function getProcessoScc($scc){
    //retorna o processo da Scc em questão(se houver)
    if($scc != 'null'){ // ATENÇÃO ESTE IF COMPARA UMA STRING |MADSON|
        $db = $GLOBALS["db"];
        $sql = "select sp.clicpoproc, sp.alicpoanop, sp.ccomlicodi, sp.cgrempcodi, sp.corglicodi, cl.ecomlidesc, scc.ESOLCOOBJE
        from sfpc.tbsolicitacaocompra scc
        LEFT JOIN sfpc.tbsolicitacaolicitacaoportal sp on sp.csolcosequ = scc.csolcosequ
        LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL ON CL.CCOMLICODI = sp.CCOMLICODI AND CL.CGREMPCODI = sp.CGREMPCODI AND CL.CCOMLICODI = sp.CCOMLICODI 
        where scc.csolcosequ = '". $scc ."' limit 1";

        $res  = $db->query($sql);

        if (!isError($res)) {
            $array = array();

            while ($Linha = $res->fetchRow()) {
                $array[] = $Linha;
            }

            return $array;
        } else {
        }
    }
}



function getProtocolo($protocolo) {
    $db = $GLOBALS["db"];

    $sql  = "SELECT * ";
    $sql .= "FROM   SFPC.tbtramitacaoprotocolo TP ";
    $sql .= "WHERE  TP.CPROTCSEQU = " . $protocolo;

    $res  = $db->query($sql);

    if (!isError($res)) {
        $array = array();

        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}
function getProtocoloAnexos($protocolo) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT * FROM SFPC.TBTRAMITACAOPROTOCOLOANEXO TP ';
    $sql .= " WHERE TP.CPROTCSEQU = " . $protocolo;
    $res  = $db->query($sql);
   
    if(!isError($res)) {
        $array = array();
        $cont = 0;
        while ($Linha = $res->fetchRow()) {
            $array['conteudo'][$cont] = '';
            $array['nome'][$cont]     = $Linha[2];
            $array['situacao'][$cont] = 'existente';
            $array['codigo'][$cont]   = $Linha[1];
            $cont++;
        }
        $compCont = count($array['codigo']);
            if($compCont >> 1){
                for($i = 0; $i < $compCont; $i++){
                    for($j = 0; $j < $compCont; $j++){
                        if($array['codigo'][$i] <= $array['codigo'][$j]){
                            $aux['codigo'][$j] = $array['codigo'][$j];
                            $aux['nome'][$j] = $array['nome'][$j];
                            $aux['conteudo'][$j] = $array['conteudo'][$j];            
                            $aux['situacao'][$j] = $array['situacao'][$j];
                            //--------------------------------------------
                            $array['codigo'][$j] = $array['codigo'][$i];
                            $array['nome'][$j] = $array['nome'][$i];
                            $array['conteudo'][$j] = $array['conteudo'][$i];            
                            $array['situacao'][$j] = $array['situacao'][$i];
                            //--------------------------------------------
                            $array['codigo'][$i] = $aux['codigo'][$j];
                            $array['nome'][$i] =  $aux['nome'][$j];
                            $array['conteudo'][$i] = $aux['conteudo'][$j];            
                            $array['situacao'][$i] = $aux['situacao'][$j];
                        }
                    }
                }
            }   
        return $array;
    }

}
function checaProtocolo($cprotcsequ) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT CPROTCSEQU, CPANEXSEQU, EPANEXNOME FROM SFPC.TBTRAMITACAOPROTOCOLOANEXO TP ';
    $sql .= " WHERE TP.CPROTCSEQU = ". $cprotcsequ;
    $res  = $db->query($sql);
    if(!isError($res)) {
        $array = array();
        $cont = 0;
        
        while ($Linha = $res->fetchRow()) {
            $array['protocolo'][$cont]   = $Linha[0];
            $array['seqAnexo'][$cont]   = $Linha[1];
            $array['nome'][$cont]     = $Linha[2];         
            $array['situacao'][$cont] = 'existente';
            $cont++;
        } 
        $compCont = count($array['seqAnexo']);
        for($i = 0; $i < $compCont; $i++){
            for($j = 0; $j < $compCont; $j++){
                if($array['seqAnexo'][$i] <= $array['seqAnexo'][$j]){
                    $aux['protocolo'][$j] = $array['protocolo'][$j];
                    $aux['seqAnexo'][$j] = $array['seqAnexo'][$j];
                    $aux['nome'][$j] = $array['nome'][$j];          
                    $aux['situacao'][$j] = $array['situacao'][$j];
                    //--------------------------------------------
                    $array['protocolo'][$j] = $array['protocolo'][$i];
                    $array['seqAnexo'][$j] = $array['seqAnexo'][$i];
                    $array['nome'][$j] = $array['nome'][$i];         
                    $array['situacao'][$j] = $array['situacao'][$i];
                    //--------------------------------------------
                    $array['protocolo'][$i] = $aux['protocolo'][$j];
                    $array['seqAnexo'][$i] = $aux['seqAnexo'][$j];
                    $array['nome'][$i] =  $aux['nome'][$j];            
                    $array['situacao'][$i] = $aux['situacao'][$j];
                }
            }
        }
        return $array;
    }

}
function getProtocoloAnexosFull($cprotcsequ, $seqAnex) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT TP.CPROTCSEQU, TP.CPANEXSEQU, TP.EPANEXNOME, TP.IPANEXARQU FROM SFPC.TBTRAMITACAOPROTOCOLOANEXO TP ';
    $sql .= " WHERE TP.CPROTCSEQU = ". $cprotcsequ;
    if(!is_null($seqAnex)){
        $sql .= " AND TP.CPANEXSEQU = ". $seqAnex;
    }
    $res  = $db->query($sql);
    if(!isError($res)) {
        $array = array();
        $cont = 0;
        
        while ($Linha = $res->fetchRow()) {
            $array['protocolo'][$cont]   = $Linha[0];
            $array['seqAnexo'][$cont]   = $Linha[1];
            $array['nome'][$cont]     = $Linha[2];
            $array['conteudo'][$cont] = $Linha[3];            
            $array['situacao'][$cont] = 'existente';
            $cont++;
        }
        
        $compCont = count($array['seqAnexo']);
        for($i = 0; $i < $compCont; $i++){
            for($j = 0; $j < $compCont; $j++){
                if($array['seqAnexo'][$i] <= $array['seqAnexo'][$j]){
                    $aux['protocolo'][$j] = $array['protocolo'][$j];
                    $aux['seqAnexo'][$j] = $array['seqAnexo'][$j];
                    $aux['nome'][$j] = $array['nome'][$j];
                    $aux['conteudo'][$j] = $array['conteudo'][$j];            
                    $aux['situacao'][$j] = $array['situacao'][$j];
                    //--------------------------------------------
                    $array['protocolo'][$j] = $array['protocolo'][$i];
                    $array['seqAnexo'][$j] = $array['seqAnexo'][$i];
                    $array['nome'][$j] = $array['nome'][$i];
                    $array['conteudo'][$j] = $array['conteudo'][$i];            
                    $array['situacao'][$j] = $array['situacao'][$i];
                    //--------------------------------------------
                    $array['protocolo'][$i] = $aux['protocolo'][$j];
                    $array['seqAnexo'][$i] = $aux['seqAnexo'][$j];
                    $array['nome'][$i] =  $aux['nome'][$j];
                    $array['conteudo'][$i] = $aux['conteudo'][$j];            
                    $array['situacao'][$i] = $aux['situacao'][$j];
                }
            }
        }
        return $array;
    }
}

/**
 * Pesquisa feita para o Relatório Gerencial 
 * Retorna as modalidades de envolvidas no relatório
 * 
 * @param $buscar array com os dados para filtrar
 * 
 * @return array
 */
function getModalidadesRelGerencialTramitacao($buscar, $pagina = null){

    $db = $GLOBALS["db"];

    $arraySituacoesConcluidas = getIdFasesConcluidas($db);
    $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    $sql = "  SELECT DISTINCT ML.EMODLIDESC, LP.CMODLICODI
    FROM SFPC.TBTRAMITACAOPROTOCOLO TP 
    LEFT JOIN SFPC.TBORGAOLICITANTE OL ON OL.CORGLICODI = TP.CORGLICOD1 
    LEFT JOIN SFPC.TBLICITACAOPORTAL LP ON LP.CLICPOPROC = TP.CLICPOPROC AND LP.ALICPOANOP = TP.ALICPOANOP AND LP.CGREMPCODI = TP.CGREMPCODI AND LP.CCOMLICODI = TP.CCOMLICODI AND LP.CORGLICODI = TP.CORGLICODI 
    LEFT JOIN SFPC.TBMODALIDADELICITACAO ML ON ML.CMODLICODI = LP.CMODLICODI
    WHERE 1=1 AND OL.FORGLISITU = 'A' AND ML.EMODLIDESC IS NOT NULL ";

    // Comissão
    if(!empty($buscar['comissao'])) {
        $sql .= " AND (TP.ccomlicodi  = ".$buscar['comissao'];
        $sql .= " OR (select count(tl.*) from sfpc.tbtramitacaolicitacao as tl 
                      where tl.cprotcsequ = TP.cprotcsequ and tl.ccomlicodi = ".$buscar['comissao'].")>0 ";
        $sql .= " OR (select count(*) from sfpc.tbsolicitacaolicitacaoportal slc  
                        left join sfpc.tblicitacaoportal l on 
                        l.clicpoproc= slc.clicpoproc AND l.alicpoanop= slc.alicpoanop
                        AND l.ccomlicodi = slc.ccomlicodi
                        AND l.cgrempcodi = slc.cgrempcodi
                        AND l.corglicodi = slc.corglicodi
                        where slc.csolcosequ = TP.CSOLCOSEQU and l.ccomlicodi =  ".$buscar['comissao'].")>0)";

    }

    // MODALIDADE
    if(!empty($buscar['codmodalidade'])) {
        $sql .= " AND LP.CMODLICODI = ".$buscar['codmodalidade'];
    }


    // Ação
    if(!empty($buscar['acao'])) {
        $sql .= " AND TP.cprotcsequ in 
                    (select distinct tram.cprotcsequ 
                    from sfpc.tbtramitacaolicitacao tram
                    where tram.ctacaosequ = ".$buscar['acao'].")";                    
    }

    // Processo Licitatório
    if(!empty($buscar['processoNumero'])) {

        $sql .= " AND TP.clicpoproc = ".(int)$buscar['processoNumero']." ";
        //$sql .= " AND prot.ccomlicodi = $arrProLicitatorio[2] ";
        // $sql .= " AND prot.cgrempcodi = $arrProLicitatorio[3] ";
        // $sql .= " AND prot.corglicodi = $arrProLicitatorio[4] ";
    }

    // Ano Processo Licitatório
    if(!empty($buscar['processoAno'])) {

        $sql .= " AND TP.alicpoanop = ".(int)$buscar['processoAno']." ";
    }

    // Grupo
    if(!empty($buscar['grupo'])) {
        $sql .= " AND TP.CGREMPCOD1 = ".$buscar['grupo'];
    }

    // Orgão
    if(!empty($buscar['orgao'])) {
        $sql .= ' AND TP.CORGLICOD1 = ' . $buscar['orgao'];
    }

    // Datas
    if(!empty($buscar['dataInicio']) && !empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR BETWEEN '" . DataInvertida($buscar['dataInicio']) . "' AND '" . DataInvertida($buscar['dataFim']) . "'";
    } else if(!empty($buscar['dataInicio'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataInicio']) . "'";
    } else if(!empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataFim']) . "'";
    }

    // Situação
    if (!empty($buscar['situacao'])) {
        if ($buscar['situacao'] == 'concluidas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $sql   .= " AND (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdConcluidas) ";
        } elseif ($buscar['situacao'] == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $sql   .= " AND ((   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdAndamento) OR (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
                join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
                and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
                and fase.cgrempcodi = TP.cgrempcodi
                order by fase.tfaseldata desc
                limit 1
            ) IS NULL) ";
        }
    }

    $sql .= " AND OL.FORGLISITU = 'A' ";
    //$sql .= " ORDER BY TP.CPROTCNUMP DESC, TP.APROTCANOP DESC ";

    
    //return $sql;
    $res  = $db->query($sql);


    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }

}

function getComissoesRelGerencialTramitacao($buscar, $pagina = null){

    $db = $GLOBALS["db"];

    $arraySituacoesConcluidas = getIdFasesConcluidas($db);
    $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    $sql = "  SELECT DISTINCT COM.ecomlidesc, COM.ccomlicodi
    FROM SFPC.TBTRAMITACAOPROTOCOLO TP 
    LEFT JOIN SFPC.TBORGAOLICITANTE OL ON OL.CORGLICODI = TP.CORGLICOD1 
    LEFT JOIN SFPC.TBLICITACAOPORTAL LP ON LP.CLICPOPROC = TP.CLICPOPROC AND LP.ALICPOANOP = TP.ALICPOANOP AND LP.CGREMPCODI = TP.CGREMPCODI AND LP.CCOMLICODI = TP.CCOMLICODI AND LP.CORGLICODI = TP.CORGLICODI 
    LEFT JOIN SFPC.tbcomissaolicitacao COM ON COM.ccomlicodi = LP.ccomlicodi
    WHERE 1=1 AND OL.FORGLISITU = 'A' AND COM.ecomlidesc IS NOT NULL  ";

    // Comissão
    if(!empty($buscar['comissao'])) {
        $sql .= " AND TP.ccomlicodi  = ".$buscar['comissao'];
        $sql .= " OR (select count(tl.*) from sfpc.tbtramitacaolicitacao as tl 
                      where tl.cprotcsequ = TP.cprotcsequ and tl.ccomlicodi = ".$buscar['comissao'].")>0 ";
        $sql .= " OR (select count(*) from sfpc.tbsolicitacaolicitacaoportal slc  
                        left join sfpc.tblicitacaoportal l on 
                        l.clicpoproc= slc.clicpoproc AND l.alicpoanop= slc.alicpoanop
                        AND l.ccomlicodi = slc.ccomlicodi
                        AND l.cgrempcodi = slc.cgrempcodi
                        AND l.corglicodi = slc.corglicodi
                        where slc.csolcosequ = TP.CSOLCOSEQU and l.ccomlicodi =  ".$buscar['comissao'].")>0";

    }

    // MODALIDADE
    if(!empty($buscar['codmodalidade'])) {
        $sql .= " AND LP.CMODLICODI = ".$buscar['codmodalidade'];
    }


    // Ação
    if(!empty($buscar['acao'])) {
        $sql .= " AND TP.cprotcsequ in 
                    (select distinct tram.cprotcsequ 
                    from sfpc.tbtramitacaolicitacao tram
                    where tram.ctacaosequ = ".$buscar['acao'].")";                    
    }

    // Processo Licitatório
    if(!empty($buscar['processoNumero'])) {

        $sql .= " AND TP.clicpoproc = ".(int)$buscar['processoNumero']." ";
        //$sql .= " AND prot.ccomlicodi = $arrProLicitatorio[2] ";
        // $sql .= " AND prot.cgrempcodi = $arrProLicitatorio[3] ";
        // $sql .= " AND prot.corglicodi = $arrProLicitatorio[4] ";
    }

    // Ano Processo Licitatório
    if(!empty($buscar['processoAno'])) {

        $sql .= " AND TP.alicpoanop = ".(int)$buscar['processoAno']." ";
    }

    // Grupo
    if(!empty($buscar['grupo'])) {
        $sql .= " AND TP.CGREMPCOD1 = ".$buscar['grupo'];
    }

    // Orgão
    if(!empty($buscar['orgao'])) {
        $sql .= ' AND TP.CORGLICOD1 = ' . $buscar['orgao'];
    }

    // Datas
    if(!empty($buscar['dataInicio']) && !empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR BETWEEN '" . DataInvertida($buscar['dataInicio']) . "' AND '" . DataInvertida($buscar['dataFim']) . "'";
    } else if(!empty($buscar['dataInicio'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataInicio']) . "'";
    } else if(!empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataFim']) . "'";
    }

    // Situação
    if (!empty($buscar['situacao'])) {
        if ($buscar['situacao'] == 'concluidas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $sql   .= " AND (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdConcluidas) ";
        } elseif ($buscar['situacao'] == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $sql   .= " AND ((   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdAndamento) OR (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
                join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
                and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
                and fase.cgrempcodi = TP.cgrempcodi
                order by fase.tfaseldata desc
                limit 1
            ) IS NULL) ";
        }
    }

    $sql .= " AND OL.FORGLISITU = 'A' ";
    //$sql .= " ORDER BY TP.CPROTCNUMP DESC, TP.APROTCANOP DESC ";


    $res  = $db->query($sql);


    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }

}

function getProcessosRelGerencialTramitacao($buscar, $pagina = null){
    $db = $GLOBALS["db"];

    $arraySituacoesConcluidas = getIdFasesConcluidas($db);
    $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    $sql = "  SELECT DISTINCT TP.CLICPOPROC , TP.ALICPOANOP, TP.csolcosequ
    FROM SFPC.TBTRAMITACAOPROTOCOLO TP 
    LEFT JOIN SFPC.TBORGAOLICITANTE OL ON OL.CORGLICODI = TP.CORGLICOD1 
    LEFT JOIN SFPC.TBLICITACAOPORTAL LP ON LP.CLICPOPROC = TP.CLICPOPROC AND LP.ALICPOANOP = TP.ALICPOANOP AND LP.CGREMPCODI = TP.CGREMPCODI AND LP.CCOMLICODI = TP.CCOMLICODI AND LP.CORGLICODI = TP.CORGLICODI 
    LEFT JOIN SFPC.tbcomissaolicitacao COM ON COM.ccomlicodi = LP.ccomlicodi
    WHERE 1=1 AND OL.FORGLISITU = 'A'   ";

    // Comissão
    if(!empty($buscar['codcomissao'])) {
        $sql .= " AND (TP.ccomlicodi  = ".$buscar['codcomissao'];
        $sql .= " OR (select count(tl.*) from sfpc.tbtramitacaolicitacao as tl 
                      where tl.cprotcsequ = TP.cprotcsequ and tl.ccomlicodi = ".$buscar['codcomissao'].")>0 ";
        $sql .= " OR (select count(*) from sfpc.tbsolicitacaolicitacaoportal slc  
                        left join sfpc.tblicitacaoportal l on 
                        l.clicpoproc= slc.clicpoproc AND l.alicpoanop= slc.alicpoanop
                        AND l.ccomlicodi = slc.ccomlicodi
                        AND l.cgrempcodi = slc.cgrempcodi
                        AND l.corglicodi = slc.corglicodi
                        where slc.csolcosequ = TP.CSOLCOSEQU and l.ccomlicodi =  ".$buscar['codcomissao'].")>0)";

    }else{

    }

    // MODALIDADE
    if(!empty($buscar['codmodalidade'])) {
        $sql .= " AND LP.CMODLICODI = ".$buscar['codmodalidade'];
    }


    // Ação
    if(!empty($buscar['acao'])) {
        $sql .= " AND TP.cprotcsequ in 
                    (select distinct tram.cprotcsequ 
                    from sfpc.tbtramitacaolicitacao tram
                    where tram.ctacaosequ = ".$buscar['acao'].")";                    
    }

    // Processo Licitatório
    if(!empty($buscar['processoNumero'])) {

        $sql .= " AND TP.clicpoproc = ".(int)$buscar['processoNumero']." ";
        //$sql .= " AND prot.ccomlicodi = $arrProLicitatorio[2] ";
        // $sql .= " AND prot.cgrempcodi = $arrProLicitatorio[3] ";
        // $sql .= " AND prot.corglicodi = $arrProLicitatorio[4] ";
    }

    // Ano Processo Licitatório
    if(!empty($buscar['processoAno'])) {

        $sql .= " AND TP.alicpoanop = ".(int)$buscar['processoAno']." ";
    }

    // Grupo
    if(!empty($buscar['grupo'])) {
        $sql .= " AND TP.CGREMPCOD1 = ".$buscar['grupo'];
    }

    // Orgão
    if(!empty($buscar['orgao'])) {
        $sql .= ' AND TP.CORGLICOD1 = ' . $buscar['orgao'];
    }

    // Datas
    if(!empty($buscar['dataInicio']) && !empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR BETWEEN '" . DataInvertida($buscar['dataInicio']) . "' AND '" . DataInvertida($buscar['dataFim']) . "'";
    } else if(!empty($buscar['dataInicio'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataInicio']) . "'";
    } else if(!empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataFim']) . "'";
    }

    // Situação
    if (!empty($buscar['situacao'])) {
        if ($buscar['situacao'] == 'concluidas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $sql   .= " AND (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdConcluidas) ";
        } elseif ($buscar['situacao'] == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $sql   .= " AND ((   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdAndamento) OR (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
                join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
                and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
                and fase.cgrempcodi = TP.cgrempcodi
                order by fase.tfaseldata desc
                limit 1
            ) IS NULL) ";
        }
    }

    $sql .= " AND OL.FORGLISITU = 'A' ";
    //$sql .= " ORDER BY TP.CPROTCNUMP DESC, TP.APROTCANOP DESC ";

    

    $res  = $db->query($sql);


    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {

            if($Linha[2]){ // caso tenha SCC associada
                $arrProcesso = getProcessoScc($Linha[2]);
                
                if(!empty($arrProcesso)){
                    $arrPro = $arrProcesso[0];

                    $Linha[0] = $arrPro[0];
                    $Linha[1] = $arrPro[1];
                }
            }

            $array[] = $Linha;
        }

        return $array;
    }

}

function getModalidades(){

    $db = $GLOBALS["db"];

    $sql = "  SELECT DISTINCT ML.CMODLICODI, ML.EMODLIDESC
    FROM  SFPC.TBMODALIDADELICITACAO ML ";
    
    
    $res  = $db->query($sql);


    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }

}
/**
 * Pesquisa feita para o Relatório Gerencial
 * 
 * @param $buscar array com os dados para filtrar
 * 
 * @return array
 */
function relatorioGerencialTramitacao($buscar, $pagina = null) {

    $db = $GLOBALS["db"];

    $arraySituacoesConcluidas = getIdFasesConcluidas($db);
    $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
	// Alteração CR 223158 - Rossana2
    $sql = '  SELECT DISTINCT TP.CPROTCSEQU, TP.CPROTCNUMP, OL.EORGLIDESC, TP.TPROTCENTR, TP.XPROTCOBJE, TP.APROTCANOP ';
    $sql .= '  , TP.EPROTCNUCI, TP.EPROTCNUOF, TP.CSOLCOSEQU, TP.VPROTCVALE, LP.VLICPOVALE, 
                LP.VLICPOTGES, LP.VLICPOVALH , 
				CASE WHEN SOLIC.CLICPOPROC IS NOT NULL THEN SOLIC.CLICPOPROC ELSE LP.CLICPOPROC END,
				CASE WHEN SOLIC.ALICPOANOP IS NOT NULL THEN SOLIC.ALICPOANOP ELSE LP.ALICPOANOP END,				
				(   select f.efasesdesc from sfpc.tbfaselicitacao fase 
                join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
                and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
                and fase.cgrempcodi = TP.cgrempcodi
                order by fase.tfaseldata desc
                limit 1
            ) as fase_licitacao, 
            CL.ECOMLIDESC, TP.clicpoproc, TP.alicpoanop, TP.cgrempcodi, TP.ccomlicodi, 
            TP.corglicodi, (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) as fase_licitacao_cod, ML.EMODLIDESC, LP.CMODLICODI ';


    $sql .= '  FROM SFPC.TBTRAMITACAOPROTOCOLO TP ';
    $sql .= '  LEFT JOIN SFPC.TBORGAOLICITANTE OL ON '; 
    $sql .= '       OL.CORGLICODI = TP.CORGLICOD1 ';
    $sql .= '  LEFT JOIN SFPC.TBLICITACAOPORTAL LP ON '; 
    $sql .= '       LP.CLICPOPROC = TP.CLICPOPROC ';
    $sql .= '       AND LP.ALICPOANOP = TP.ALICPOANOP ';
    $sql .= '       AND LP.CGREMPCODI = TP.CGREMPCODI ';
    $sql .= '       AND LP.CCOMLICODI = TP.CCOMLICODI ';
    $sql .= '       AND LP.CORGLICODI = TP.CORGLICODI ';

    $sql .= '  LEFT JOIN SFPC.TBMODALIDADELICITACAO ML ON ';
    $sql .= '       ML.CMODLICODI = LP.CMODLICODI ';

    //$sql .= '  LEFT JOIN SFPC.TBFASELICITACAO FL ON '; 
   // $sql .= '       FL.CLICPOPROC = TP.CLICPOPROC ';
    //$sql .= '       AND FL.ALICPOANOP = TP.ALICPOANOP ';
   // $sql .= '       AND FL.CGREMPCODI = TP.CGREMPCODI ';
   // $sql .= '       AND FL.CCOMLICODI = TP.CCOMLICODI ';
   // $sql .= '       AND FL.CORGLICODI = TP.CORGLICODI ';
    $sql .= '  LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL ON '; 
    $sql .= '       CL.CCOMLICODI = TP.CCOMLICODI ';
    $sql .= '       AND CL.CGREMPCODI = TP.CGREMPCODI ';
    $sql .= '       AND CL.CCOMLICODI = TP.CCOMLICODI ';
	$sql .= '  LEFT JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL SOLIC ON ';
	$sql .= '  SOLIC.CSOLCOSEQU = TP.CSOLCOSEQU ';
    $sql .= '  WHERE 1=1 ';




    // Número do protocolo
    //if(!empty($buscar['protocolo'])) {
    //   $sql .= " AND TP.CPROTCNUMP = ".$buscar['protocolo'];
    //}

    // Ano do protocolo
    //if(!empty($buscar['anoProtocolo'])) {
    //    $sql .= " AND TP.APROTCANOP = ".$buscar['anoProtocolo'];
    //}
    
    // MODALIDADE
    if(!empty($buscar['codmodalidade'])) {
        $sql .= " AND LP.CMODLICODI = ".$buscar['codmodalidade']." ";
    }

    // Comissão
    if(!empty($buscar['comissao'])) {
        $sql .= " AND ( TP.ccomlicodi  = ".$buscar['comissao'];
        $sql .= " OR (select count(tl.*) from sfpc.tbtramitacaolicitacao as tl 
                      where tl.cprotcsequ = TP.cprotcsequ and tl.ccomlicodi = ".$buscar['comissao'].")>0 ";
        $sql .= " OR (select count(*) from sfpc.tbsolicitacaolicitacaoportal slc  
                        left join sfpc.tblicitacaoportal l on 
                        l.clicpoproc= slc.clicpoproc AND l.alicpoanop= slc.alicpoanop
                        AND l.ccomlicodi = slc.ccomlicodi
                        AND l.cgrempcodi = slc.cgrempcodi
                        AND l.corglicodi = slc.corglicodi
                        where slc.csolcosequ = TP.CSOLCOSEQU and l.ccomlicodi =  ".$buscar['comissao'].")>0 )";

    }


    // Ação
    if(!empty($buscar['acao'])) {
        $sql .= " AND TP.cprotcsequ in 
                    (select distinct tram.cprotcsequ 
                    from sfpc.tbtramitacaolicitacao tram
                    where tram.ctacaosequ = ".$buscar['acao'].")";                    
    }

    // Processo Licitatório
    if(!empty($buscar['processoNumero'])) {

        $sql .= " AND TP.clicpoproc = ".(int)$buscar['processoNumero']." ";
        //$sql .= " AND prot.ccomlicodi = $arrProLicitatorio[2] ";
        // $sql .= " AND prot.cgrempcodi = $arrProLicitatorio[3] ";
        // $sql .= " AND prot.corglicodi = $arrProLicitatorio[4] ";
    }

    // Ano Processo Licitatório
    if(!empty($buscar['processoAno'])) {

        $sql .= " AND TP.alicpoanop = ".(int)$buscar['processoAno']." ";
    }


    // Grupo
    if(!empty($buscar['grupo'])) {
        $sql .= " AND TP.CGREMPCOD1 = ".$buscar['grupo'];
    }

    // Orgão
    if(!empty($buscar['orgao'])) {
        $sql .= ' AND TP.CORGLICOD1 = ' . $buscar['orgao'];
    }

    // Datas
    if(!empty($buscar['dataInicio']) && !empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR BETWEEN '" . DataInvertida($buscar['dataInicio']) . "' AND '" . DataInvertida($buscar['dataFim']) . "'";
    } else if(!empty($buscar['dataInicio'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataInicio']) . "'";
    } else if(!empty($buscar['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($buscar['dataFim']) . "'";
    }

    // Situação
    if (!empty($buscar['situacao'])) {
        if ($buscar['situacao'] == 'concluidas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $sql   .= " AND ( ((  select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdConcluidas)) ";
		    $sql   .= " OR (SELECT FASE.CFASESCODI FROM SFPC.TBFASELICITACAO FASE 
			WHERE FASE.CLICPOPROC = SOLIC.CLICPOPROC AND FASE.ALICPOANOP = SOLIC.ALICPOANOP 
			AND FASE.CCOMLICODI = SOLIC.CCOMLICODI AND FASE.CGREMPCODI = SOLIC.CGREMPCODI 
			AND FASE.CORGLICODI = SOLIC.CORGLICODI ORDER BY FASE.tfaseldata DESC 
			LIMIT 1
		) IN ($strIdConcluidas)) "; 
        } elseif ($buscar['situacao'] == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $sql   .= " AND ((   select f.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
            and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
            and fase.cgrempcodi = TP.cgrempcodi
            order by fase.tfaseldata desc
            limit 1
        ) IN ($strIdAndamento) OR (   select f.cfasescodi from sfpc.tbfaselicitacao fase 
                join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                where fase.clicpoproc = TP.clicpoproc and fase.alicpoanop = TP.alicpoanop 
                and fase.ccomlicodi= TP.ccomlicodi and fase.corglicodi = TP.corglicodi 
                and fase.cgrempcodi = TP.cgrempcodi
                order by fase.tfaseldata desc
                limit 1
            ) IS NULL) ";
        }
    }

    $sql .= " AND OL.FORGLISITU = 'A' AND ML.EMODLIDESC IS NOT NULL";
    $sql .= " ORDER BY TP.CPROTCNUMP DESC, TP.APROTCANOP DESC ";


    $res  = $db->query($sql);


    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }
}
function getTramitacaoLicitacaoAnexos($licitacao, $protocolo, $sequencial = ''){
    $db = $GLOBALS["db"];
    $sql = '  SELECT cprotcsequ, ctramlsequ, ctramasequ, etramanome,
                    itramaarqu, ttramacada, cusupocodi, 
                    ttramaulat FROM SFPC.TBTRAMITACAOLICITACAOANEXO TP ';
    $sql .= " WHERE TP.CPROTCSEQU = " . $protocolo;
    $sql .= " AND TP.CTRAMLSEQU = ". $licitacao;
    if($sequencial != ''){
        $sql .= " AND TP.CTRAMASEQU = ". $sequencial;
    }
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        $cont = 0;
        while ($Linha = $res->fetchRow()) {
            $array['conteudo'][$cont] = $Linha[4];
            $array['nome'][$cont]     = $Linha[3];
            $array['situacao'][$cont] = 'existente';
            $array['codigo'][$cont]   = $Linha[2];
            $array['licitacao'][$cont]   = $Linha[1];
            $array['protocolo'][$cont]   = $Linha[0];
            $cont++;
        }

        return $array;
    }

}


function getProtocoloDetalhe($protocolo) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT TP.CPROTCSEQU, TP.CPROTCNUMP, OL.EORGLIDESC, TP.TPROTCENTR, TP.XPROTCOBJE, 
                     TP.APROTCANOP, TP.EPROTCNUCI, TP.EPROTCNUOF, TP.CLICPOPROC, TP.ALICPOANOP, 
                     CL.CCOMLICODI, CL.ECOMLIDESC, TP.CSOLCOSEQU, TP.cusupocod1, 
                    ( select eusuporesp from SFPC.tbusuarioportal
                    where cusupocodi = TP.cusupocod1) as usuario_alteracao,
                    TP.tprotculat, 
                    TP.cusupocodi , 
                    ( select eusuporesp from SFPC.tbusuarioportal
                    where cusupocodi = TP.cusupocodi) as usuario_criador,
                    TP.XPROTCMONI
              FROM SFPC.TBTRAMITACAOPROTOCOLO TP 
                LEFT JOIN SFPC.TBORGAOLICITANTE OL ON 
                  OL.CORGLICODI = TP.CORGLICOD1
                  LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL ON 
                  CL.CCOMLICODI = TP.CCOMLICODI WHERE 1=1 ';
    $sql .= "   AND TP.CPROTCSEQU = " . $protocolo;
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}

function getAcoesRelGerencial($protocolo = null, $buscar = null) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT distinct TA.ETACAODESC, TA.ctacaosequ ,TA.atacaoorde
    FROM SFPC.TBTRAMITACAOLICITACAO TL
      LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON 
        UP.CUSUPOCODI = TL.CUSUPOCODI 
      LEFT JOIN SFPC.TBTRAMITACAOPROTOCOLO TP ON 
        TP.CPROTCSEQU = TL.CPROTCSEQU
      LEFT JOIN SFPC.TBTRAMITACAOACAO TA ON 
        TA.CTACAOSEQU = TL.CTACAOSEQU
      LEFT JOIN SFPC.TBTRAMITACAOAGENTE TAG ON 
        TAG.CTAGENSEQU = TL.CTAGENSEQU 
      WHERE 1=1 ';
    if($protocolo){
        $sql .= " AND TL.CPROTCSEQU = " . $protocolo; 
    }

    if($buscar['grupo']){
        $sql .= " AND TA.CGREMPCODI = " . $buscar['grupo']; 
    }
    $sql .= ' order by TA.atacaoorde ';


    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }
        
        return $array;
    }
}




function getMediaDiasAcao($codAcao, $codModalidade, $dados) {
    $totDias = 0;
    $totPassos = 0;
    $numDias = 0;
    $numDiasPrevistos = 0;
    $totDiasPrev = 0;
    foreach($dados as $key => $value) {   
        
        if($codModalidade == $value[24]){
        
            $atual = date('d/m/Y');
            $passos_ = getTramitacaoPassos($value[0]);
            
            foreach($passos_ as $passo){
                

                if($passo[3] && $codAcao == $passo[16]){
                    
                    //código novo de atraso

                    $arrSaida = explode("-",substr($passo[5],0,10));
                    $saida = $arrSaida[2]."/".$arrSaida[1]."/".$arrSaida[0];
                    $arrEntrada = explode("-",substr($passo[3],0,10));
    
                    $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];
                    
                    $previsto = calcularTramitacaoSaida($dataHoraEntrada, $passo[4]);
                    $arrPrevisto = explode("/",$previsto);
                    $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];                                        
                    ////($dataHoraEntrada .' - '. $saida.' - '.$totDias.' - '.$totPassos.' -> '.$codAcao);
                    if($passo[5]){   
                        
                        $diffDias = calculaDias($dataHoraEntrada, $saida);
                        $numDias = $diffDias; 
    
                    }else{

                        $diffDias = calculaDias($dataHoraEntrada, $atual); 
                        $numDias = $diffDias;

                    }
                    //para os dias previstos
                    $diffDiasPrev = calculaDias($dataHoraEntrada, $previsto);
                    $numDiasPrevistos = $diffDiasPrev;
    
                    $totDias = $totDias + $numDias;
                    $totDiasPrev = $totDiasPrev + $numDiasPrevistos;
                    $totPassos++;
                }


            }
            
        }
    }

    $resposta = array();
    if($totPassos > 0){
        $resposta[0] = $totDias/$totPassos;
        $resposta[1] = $totDiasPrev/$totPassos;
        $resposta[2] = $totPassos;
    }else{
        $resposta[0] = 0;
        $resposta[1] = 0;
        $resposta[2] = $totPassos;

    }
    return $resposta;
}


function getMediaDiasAcaoComissao($codAcao, $codModalidade, $comissao, $dados) {
    $totDias = 0;
    $totPassos = 0;
    $numDias = 0;
    $numDiasPrevistos = 0;
    $totDiasPrev = 0;
    foreach($dados as $key => $value) {   
        
        if($codModalidade == $value[24]){

            if($comissao == $value[20]){

            
                $atual = date('d/m/Y');
                $passos_ = getTramitacaoPassos($value[0]);
                
                foreach($passos_ as $passo){
                    

                    if($passo[3] && $codAcao == $passo[16]){
                        
                        //código novo de atraso

                        $arrSaida = explode("-",substr($passo[5],0,10));
                        $saida = $arrSaida[2]."/".$arrSaida[1]."/".$arrSaida[0];
                        $arrEntrada = explode("-",substr($passo[3],0,10));
        
                        $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];
                        
                        $previsto = calcularTramitacaoSaida($dataHoraEntrada, $passo[4]);
                        $arrPrevisto = explode("/",$previsto);
                        $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];                                        
                        ////($dataHoraEntrada .' - '. $saida.' - '.$totDias.' - '.$totPassos.' -> '.$codAcao);
                        if($passo[5]){   
                            
                        // if(strtotime($saida) > strtotime($dataPrevista)) { 
                                $diffDias = calculaDias($dataHoraEntrada, $saida);
                                $numDias = $diffDias;
                                


                        // } else {
                            //    $passo['atraso'] = ' - ';
                        // }
        
                        }else{
                            
                        // if(strtotime($atual) > strtotime($dataPrevista)) { 
                                $diffDias = calculaDias($dataHoraEntrada, $atual); 
                                $numDias = $diffDias;

                                
                        // } else {
                            //    $passo['atraso'] = ' - ';
                        //  }
                        }
                        //para os dias previstos
                        $diffDiasPrev = calculaDias($dataHoraEntrada, $previsto);
                        $numDiasPrevistos = $diffDiasPrev;
        
                        $totDias = $totDias + $numDias;
                        $totDiasPrev = $totDiasPrev + $numDiasPrevistos;
                        $totPassos++;
                    }


                }
            }
            
        }
    }

    $resposta = array();
    if($totPassos > 0){
        $resposta[0] = $totDias/$totPassos;
        $resposta[1] = $totDiasPrev/$totPassos;
        $resposta[2] = $totPassos;
    }else{
        $resposta[0] = 0;
        $resposta[1] = 0;
        $resposta[2] = $totPassos;

    }
    return $resposta;
}


function getMediaDiasAcaoProcesso($codAcao, $codModalidade , $comissao, $processo, $anoProcesso, $dados){
    $totDias = 0;
    $totPassos = 0;
    $numDias = 0;
    $numDiasPrevistos = 0;
    $totDiasPrev = 0;
    foreach($dados as $key => $value) {   
        
        if($codModalidade == $value[24]){

            if($comissao == $value[20]){
            
            //processo e ano do processo
            if($processo == $value[17] && $anoProcesso == $value[18]){

                $atual = date('d/m/Y');
                $passos_ = getTramitacaoPassos($value[0]);
                
                foreach($passos_ as $passo){
                    

                    if($passo[3] && $codAcao == $passo[16]){
                        
                        //código novo de atraso

                        $arrSaida = explode("-",substr($passo[5],0,10));
                        $saida = $arrSaida[2]."/".$arrSaida[1]."/".$arrSaida[0];
                        $arrEntrada = explode("-",substr($passo[3],0,10));
        
                        $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];
                        
                        $previsto = calcularTramitacaoSaida($dataHoraEntrada, $passo[4]);
                        $arrPrevisto = explode("/",$previsto);
                        $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];                                        
                        ////($dataHoraEntrada .' - '. $saida.' - '.$totDias.' - '.$totPassos.' -> '.$codAcao);
                        if($passo[5]){   
                            
                        // if(strtotime($saida) > strtotime($dataPrevista)) { 
                                $diffDias = calculaDias($dataHoraEntrada, $saida);
                                $numDias = $diffDias;
                                


                        // } else {
                            //    $passo['atraso'] = ' - ';
                        // }
        
                        }else{
                            
                        // if(strtotime($atual) > strtotime($dataPrevista)) { 
                                $diffDias = calculaDias($dataHoraEntrada, $atual); 
                                $numDias = $diffDias;

                                
                        // } else {
                            //    $passo['atraso'] = ' - ';
                        //  }
                        }
                        //para os dias previstos
                        $diffDiasPrev = calculaDias($dataHoraEntrada, $previsto);
                        $numDiasPrevistos = $diffDiasPrev;
        
                        $totDias = $totDias + $numDias;
                        $totDiasPrev = $totDiasPrev + $numDiasPrevistos;
                        $totPassos++;
                    }


                }
            }//comissao
            }//processo
            
        }
    }

    $resposta = array();
    if($totPassos > 0){
        $resposta[0] = $totDias/$totPassos;
        $resposta[1] = $totDiasPrev/$totPassos;
        $resposta[2] = $totPassos;
    }else{
        $resposta[0] = 0;
        $resposta[1] = 0;
        $resposta[2] = $totPassos;

    }
    return $resposta;
}



function getMediaDiasAcaoProcessoDetalhes($codAcao, $codModalidade , $comissao, $processo, $anoProcesso, $dados){
    $totDias = 0;
    $totPassos = 0;
    $numDias = 0;
    $numDiasPrevistos = 0;
    $totDiasPrev = 0;
    $resposta = array();

    foreach($dados as $key => $value) {   

        if($codModalidade == $value[24]){
            ////($processo.'/'.$value[17].' - '.$anoProcesso.'/'.$value[18].':'.$comissao.' -> '.$value[20]);
            //die();
            if($comissao == $value[20]){

            //processo e ano do processo
            if($processo == $value[17] && $anoProcesso == $value[18]){


                $atual = date('d/m/Y');
                $passos_ = getTramitacaoPassos($value[0]);
                
                foreach($passos_ as $passo){
                    

                    if($passo[3] && $codAcao == $passo[16]){
                        
                        //código novo de atraso
                        $arrSaida = explode("-",substr($passo[5],0,10));
                        $saida = $arrSaida[2]."/".$arrSaida[1]."/".$arrSaida[0];
                        $arrEntrada = explode("-",substr($passo[3],0,10));
        
                        $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];
                        
                        $previsto = calcularTramitacaoSaida($dataHoraEntrada, $passo[4]);
                        $arrPrevisto = explode("/",$previsto);
                        $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];                                        
                        ////($dataHoraEntrada .' - '. $saida.' - '.$totDias.' - '.$totPassos.' -> '.$codAcao);
                        if($passo[5]){   
                            
                        // if(strtotime($saida) > strtotime($dataPrevista)) { 
                            $diffDias = calculaDias($dataHoraEntrada, $saida);
                            $numDias = $diffDias;
                            


                        // } else {
                            //    $passo['atraso'] = ' - ';
                        // }
        
                        }else{
                            
                        // if(strtotime($atual) > strtotime($dataPrevista)) { 
                            $diffDias = calculaDias($dataHoraEntrada, $atual); 
                            $numDias = $diffDias;

                                
                        // } else {
                            //    $passo['atraso'] = ' - ';
                        //  }
                        }
                        //para os dias previstos
                        $diffDiasPrev = calculaDias($dataHoraEntrada, $previsto);
                        $numDiasPrevistos = $diffDiasPrev;
        
                        $totDias = $totDias + $numDias;
                        $totDiasPrev = $totDiasPrev + $numDiasPrevistos;
                        $resposta[0] = $passo[0];
                        $resposta[1] = $passo[1];
                        $resposta[2] = $totDiasPrev;
                        $resposta[3] = $totDias;
                        $resposta[4] = $passo[18];

                        $totPassos++;
                    }


                }
            }//processo
            }//comissao
            
        }
    }

    /*
    if($totPassos > 0){
        $resposta[0] = $totDias/$totPassos;
        $resposta[1] = $totDiasPrev/$totPassos;
    }else{
        $resposta[0] = 0;
        $resposta[1] = 0;

    }*/
    return $resposta;
}




function getTramitacaoPassos($protocolo) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT TAG.ETAGENDESC, UP.EUSUPORESP, TA.ETACAODESC, TL.TTRAMLENTR, 
                    TL.ATRAMLPRAZ, TL.TTRAMLSAID, TL.XTRAMLOBSE, TA.ATACAOORDE, 
                    TL.CUSUPOCODI, TAG.FTAGENTIPO, TL.CUSUPOCOD1, TL.CTRAMLSEQU, TL.CPROTCSEQU, 
                    TL.CCOMLICODI, COM.ECOMLIDESC, TA.FTACAOFINA, TA.CTACAOSEQU, TA.FTACAOTUSU,
                    TL.CTAGENSEQU   
              FROM SFPC.TBTRAMITACAOLICITACAO TL
                LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON 
                  UP.CUSUPOCODI = TL.CUSUPOCODI 
                LEFT JOIN SFPC.TBTRAMITACAOPROTOCOLO TP ON 
                  TP.CPROTCSEQU = TL.CPROTCSEQU
                LEFT JOIN SFPC.TBTRAMITACAOACAO TA ON 
                  TA.CTACAOSEQU = TL.CTACAOSEQU
                LEFT JOIN SFPC.TBTRAMITACAOAGENTE TAG ON 
                  TAG.CTAGENSEQU = TL.CTAGENSEQU 
                LEFT JOIN SFPC.TBCOMISSAOLICITACAO COM ON 
                  COM.CCOMLICODI = TL.CCOMLICODI ';
    $sql .= "   WHERE TL.CPROTCSEQU = " . $protocolo;
    $sql .= "   ORDER BY TL.TTRAMLENTR DESC";
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }
        
        return $array;
    }
}

function getTramitacaoUltimoPasso($protocolo) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT TAG.ETAGENDESC, UP.EUSUPORESP, TA.ctacaosequ, 
                     TAG.CTAGENSEQU, TA.ETACAODESC, TL.TTRAMLENTR, 
                     TL.ATRAMLPRAZ, TL.TTRAMLSAID, TL.XTRAMLOBSE, 
                     TA.ATACAOORDE, TAG.FTAGENTIPO, TL.ctramlsequ,
                     TL.CUSUPOCODI, TA.ATACAOPRAZ, TA.FTACAOCOMI, TA.FTACAOANEX, TA.FTACAOTUSU, TL.CCOMLICODI
              FROM SFPC.TBTRAMITACAOLICITACAO TL
                LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON 
                  UP.CUSUPOCODI = TL.CUSUPOCODI 
                LEFT JOIN SFPC.TBTRAMITACAOPROTOCOLO TP ON 
                  TP.CPROTCSEQU = TL.CPROTCSEQU
                LEFT JOIN SFPC.TBTRAMITACAOACAO TA ON 
                  TA.CTACAOSEQU = TL.CTACAOSEQU
                LEFT JOIN SFPC.TBTRAMITACAOAGENTE TAG ON 
                  TAG.CTAGENSEQU = TL.CTAGENSEQU ';
    $sql .= "   WHERE TL.CPROTCSEQU = " . $protocolo;
    $sql .= "   ORDER BY TL.ctramlsequ DESC ";
    $sql .= "   LIMIT 1 ";
    //print_r($sql);
    //die();
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array =  $res->fetchRow();
        //while ($Linha = $res->fetchRow()) {
        //    $array[] = $Linha;
        //}
        
        return $array;
    }
}

function getAcoes($grupo, $ordem) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT CTACAOSEQU, ETACAODESC  FROM SFPC.TBTRAMITACAOACAO TA ';
    $sql .= "   WHERE TA.CGREMPCODI = " . $grupo;

    if(!empty($ordem)) {
        $sql .= "   AND TA.ATACAOORDE = " . $ordem;
    }
    //$sql .= "  LIMIT 1 ";
    $sql .= "  order by TA.ETACAODESC ASC ";
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }
}

function getAcao($grupo, $ordem) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT *  FROM SFPC.TBTRAMITACAOACAO TA ';
    $sql .= "   WHERE TA.FTACAOINIC = 'S' AND TA.CGREMPCODI = " . $grupo;

    if(!empty($ordem)) {
        $sql .= "   AND TA.ATACAOORDE = " . $ordem;
    }
    $sql .= "  LIMIT 1 ";


    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array = $Linha;
        }

        return $array;
    }
}


function getAgente($grupo) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT CTAGENSEQU, ETAGENDESC FROM SFPC.TBTRAMITACAOAGENTE TA ';
    $sql .= "   WHERE TA.CGREMPCODI = " . $grupo;
    $sql .= "  order by TA.ETAGENDESC ASC";
    //$sql .= "  LIMIT 1 ";


    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }

        return $array;
    }
}

// TODO verificar está função
function getAgenteById($db, $agente) {
    $array = array();

    if(!empty($agente)) {
        $sql = " SELECT TA.CTAGENSEQU, TA.CGREMPCODI, TA.ETAGENDESC, TA.FTAGENTIPO, TAU.CUSUPOCODI, TA.FTAGENINIC, TA.FTAGENSITU ";
        $sql .= " FROM SFPC.TBTRAMITACAOAGENTE TA";
        $sql .= " LEFT JOIN SFPC.TBTRAMITACAOAGENTEUSUARIO TAU ON ";
        $sql .= "    TAU.CTAGENSEQU = TA.CTAGENSEQU ";
        $sql .= " WHERE TA.FTAGENSITU = 'A' AND TA.CTAGENSEQU = " . $agente;

        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            while ($Linha = $result->fetchRow()) {
                $array = $Linha;
                unset($array[4]);
                $array['usuarios'][] = $Linha[4];
            }
        }
    }

    return $array;
}

function getAgentes($db, $grupo = null) {
    $array = array();

    $sql = " SELECT TA.CTAGENSEQU, TA.ETAGENDESC, TA.FTAGENTIPO ";
    $sql .= " FROM SFPC.TBTRAMITACAOAGENTE TA ";
    $sql .= " LEFT JOIN SFPC.TBTRAMITACAOAGENTEUSUARIO TAU ON ";
    $sql .= "    TAU.CTAGENSEQU = TA.CTAGENSEQU ";
    $sql .= " WHERE TA.FTAGENSITU = 'A'  ";

    if(!is_null($grupo)) {
        $sql .= "   AND TA.CGREMPCODI = " . $grupo;
    }

    $sql .= " GROUP BY TA.CTAGENSEQU, TA.FTAGENTIPO, TA.ETAGENDESC
              ORDER BY TA.ETAGENDESC ASC ";
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

function getAgentesUsuario($db, $usuario) {
    $array = array();

    $sql = " SELECT DISTINCT(TAU.CTAGENSEQU)";
    $sql .= " FROM SFPC.TBTRAMITACAOAGENTEUSUARIO TAU";
    $sql .= " WHERE TAU.CUSUPOCODI = ".$usuario;

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}


function getComissoesUsuario($db, $usuario) {
    $array = array();

    $sql = " select ccomlicodi, cgrempcodi, cusupocodi, tusucoulat from sfpc.tbusuariocomis ";
    $sql .= " where cusupocodi = ". $usuario;

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

function existeReferenciaAgente($agentesUsuario, $passos){
    $bolExiste = false;
    $arrAg = array();


    foreach($agentesUsuario as $ObjAg){
        $arrAg[] = $ObjAg[0];
    }

    foreach ($passos as $key => $value) { 

        if( in_array($value[18], $arrAg)){
            $bolExiste = true;
        }


    }


    return $bolExiste;
}

function existeReferenciaComissao($comissoesUsuario, $passos){
    $bolExiste = false;
    $arrCom = array();

    foreach($comissoesUsuario as $ObjCom){
        $arrCom[] = $ObjCom[0];
    }

    foreach ($passos as $key => $value) { 

        if( in_array($value[13], $arrCom)){
            $bolExiste = true;
        }

    }


    return $bolExiste;
}

function listarResultado($dados){

    //o primeiro item será listado separado por virgula num string

    $txtResultados = '';
    foreach($dados as $dado){
        $txtResultados .= $dado[0].',' ;
    }
    ////($dados);
    //die();
    $txtResultados = substr($txtResultados, 0, strlen($string)-1);
    return $txtResultados;

}

function getResponsaveisAgente($db, $agente) {
    $array = array();

    $sql = " SELECT DISTINCT(TA.CTAGENSEQU), TAU.CUSUPOCODI, UP.EUSUPORESP ";
    $sql .= " FROM SFPC.TBTRAMITACAOAGENTE TA ";
    $sql .= " LEFT JOIN SFPC.TBTRAMITACAOAGENTEUSUARIO TAU ON ";
    $sql .= "    TAU.CTAGENSEQU = TA.CTAGENSEQU ";
    $sql .= " LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON ";
    $sql .= "    UP.CUSUPOCODI = TAU.CUSUPOCODI ";
    $sql .= " WHERE TA.FTAGENSITU = 'A'  ";
    $sql .= "   AND TA.CTAGENSEQU = " . $agente;

    $sql .= " ORDER BY UP.EUSUPORESP ASC ";
    //print_r($sql);exit;
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

function getUsuariosAgentes($db) {
    $array = array();

    $sql = " SELECT DISTINCT(TA.CTAGENSEQU), TAU.CUSUPOCODI, UP.EUSUPORESP ";
    $sql .= " FROM SFPC.TBTRAMITACAOAGENTE TA ";
    $sql .= " LEFT JOIN SFPC.TBTRAMITACAOAGENTEUSUARIO TAU ON ";
    $sql .= "    TAU.CTAGENSEQU = TA.CTAGENSEQU ";
    $sql .= " LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON ";
    $sql .= "    UP.CUSUPOCODI = TAU.CUSUPOCODI ";
    $sql .= " WHERE TA.FTAGENSITU = 'A'  ";

    $sql .= " ORDER BY UP.EUSUPORESP ASC ";
    //print_r($sql);exit;
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

function getUsuariosComissao($db) {
    $array = array();

    $sql = " SELECT distinct COM.ccomlicodi, UC.CUSUPOCODI, UP.EUSUPORESP ";
    $sql .= " FROM SFPC.TBCOMISSAOLICITACAO COM ";
    $sql .= " LEFT JOIN SFPC.TBUSUARIOCOMIS UC ON  ";
    $sql .= "    COM.CCOMLICODI = UC.CCOMLICODI  ";
    $sql .= " LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON ";
    $sql .= "    UP.CUSUPOCODI = UC.CUSUPOCODI  ";
    //$sql .= " WHERE TA.FTAGENSITU = 'A'  ";
    $sql .= " ORDER BY UP.EUSUPORESP ASC ";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

function protocoloPesquisarAgentes($db, $params) {
    $arraySituacoesConcluidas = getIdFasesConcluidas($db);
    $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    $array = array();

    $sql = " SELECT DISTINCT TL.cprotcsequ, TA.etagendesc, 
            UP.eusuporesp, 
            TP.cprotcnump, TP.aprotcanop,
            (select usu.eusuporesp
            from sfpc.tbusuarioportal usu
            where usu.cusupocodi = TL.cusupocod1 ) as Saida, TA.FTAGENTIPO
        FROM sfpc.tbtramitacaolicitacao TL
        join sfpc.tbtramitacaoprotocolo TP on
            TP.cprotcsequ = TL.cprotcsequ
        LEFT JOIN sfpc.tbusuarioportal UP ON 
            UP.cusupocodi = TL.cusupocodi 
        LEFT JOIN sfpc.tbtramitacaoagente TA ON 
            TA.ctagensequ = TL.ctagensequ 

        WHERE 1=1 ";
    
    // Agente
    if(!empty($params['agente'])) {
        $sql .= "   AND TL.CTAGENSEQU = " . $params['agente'];        
    }

    // Responsável
    if(!empty($params['responsavel'])) {
        $sql .= "   AND (TL.cusupocodi = " . $params['responsavel'];  
        $sql .= "   OR TL.cusupocod1 = " . $params['responsavel'] . " )";     
    }

    // Datas
    if(!empty($params['dataInicio']) && !empty($params['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR BETWEEN '" . DataInvertida($params['dataInicio']) . "' AND '" . DataInvertida($params['dataFim']) . "'";
    } else if(!empty($params['dataInicio'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($params['dataInicio']) . "'";
    } else if(!empty($params['dataFim'])) {
        $sql .= " AND TP.TPROTCENTR = '" . DataInvertida($params['dataFim']) . "'";
    }

    // Situação
    if (!empty($params['situacao'])) {
        if ($params['situacao'] == 'concluidas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $sql   .= " AND (select (select fase.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = sp.clicpoproc and fase.alicpoanop = sp.alicpoanop 
            and fase.ccomlicodi= sp.ccomlicodi and fase.corglicodi = sp.corglicodi 
            and fase.cgrempcodi = sp.cgrempcodi
            order by fase.tfaseldata desc 
        limit 1) as fase_licitacao 
        from sfpc.tbsolicitacaocompra scc
        left join sfpc.tbsolicitacaolicitacaoportal sp on sp.csolcosequ = scc.csolcosequ
        where scc.csolcosequ = TP.csolcosequ limit 1) IN ($strIdConcluidas) ";
        } elseif ($params['situacao'] == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $sql   .= " AND (select (select fase.cfasescodi from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = sp.clicpoproc and fase.alicpoanop = sp.alicpoanop 
            and fase.ccomlicodi= sp.ccomlicodi and fase.corglicodi = sp.corglicodi 
            and fase.cgrempcodi = sp.cgrempcodi
            order by fase.tfaseldata desc 
        limit 1) as fase_licitacao 
        from sfpc.tbsolicitacaocompra scc
        left join sfpc.tbsolicitacaolicitacaoportal sp on sp.csolcosequ = scc.csolcosequ
        where scc.csolcosequ = TP.csolcosequ limit 1) IN ($strIdAndamento) ";
        }
    }

    $sql .= " group by TL.cprotcsequ,TA.etagendesc, 
             UP.eusuporesp, 
             TP.cprotcnump, TP.aprotcanop,            
             (select usu.eusuporesp
             from sfpc.tbusuarioportal usu
             where usu.cusupocodi = TL.cusupocod1 ), TA.FTAGENTIPO ORDER BY TL.cprotcsequ ASC ";
        //print_r($sql);
        //die();
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;    
}


/**
 * Retornar os dias não trabalhados
 *
 * @param $entrada date pt-br
 * @param $saida date pt-br
 *
 * @return array
 */
function getDiasNaoTrabalhados($entrada, $saida) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT ATDIANDIAT, ATDIANMEST, ATDIANANOT FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE 1 = 1 ';
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = str_pad($Linha[0], 2, "0", STR_PAD_LEFT).'/'.str_pad($Linha[1], 2, "0", STR_PAD_LEFT).'/'.$Linha[2];
        }

        return $array;
    }
}

/**
 * Calcular a quantidade de dias úteis de atraso
 *
 * @param $entrada
 * @param $prazo
 *
 * @return date
 */
function calcularTramitacaoDiasUteisAtraso($yDataInicial, $yDataFinal) {

    $diaFDS = 0; //dias não úteis(Sábado=6 Domingo=0 ou dia não trabalhado(feriado))
    
    $calculoDias = CalculaDias2($yDataInicial, $yDataFinal); //número de dias entre a data inicial e a final
    //para os dias não trabalhados (FERIADOS E Etc.)


    $diasNaoTrabalhados = getDiasNaoTrabalhados(DataBarra($yDataInicial), DataBarra($yDataFinal));
    
    //Soma um dia para compreender o último dia no cálculo
    $yDataFinal = SomaDia(DataBarra($yDataFinal));
    $yDataInicial = DataBarra($yDataInicial );


    while($yDataInicial!=$yDataFinal){


        $diaSemana = date("w", dataToTimestamp($yDataInicial ));
        ////('Dia da Semana: '.$diaSemana . ' Data:'. $yDataInicial);
        if($diaSemana==0 || $diaSemana==6){
            //se SABADO OU DOMINGO, SOMA 01
            $diaFDS++;
        }else{
            //calcula os dias não trabalhados
            if(in_array($yDataInicial, $diasNaoTrabalhados)) {
                $diaFDS++;
            
            }
        }

        $yDataInicial = SomaDia($yDataInicial); //dia + 1
    
    }

    return $calculoDias - $diaFDS ;
}

/**
 * Calcular a data de estimada da tramitação
 *
 * @param $entrada
 * @param $prazo
 *
 * @return date
 */
function calcularTramitacaoSaida($entrada, $prazo) {
    $estimado = SomaDia($entrada, $prazo);

    $adicional = 0;
    $diasUteis = diasUteis($entrada, $estimado);
    while($diasUteis < $prazo){
        $diasUteis = diasUteis($entrada, $estimado);
        ////('estimado - '.$estimado);
        ////('diasUteis - '.$diasUteis);


        $adicional = $adicional + ($prazo - $diasUteis);

        

        $novoPrazo = SomaDia($entrada, ($prazo + $adicional));
        $estimado = $novoPrazo;

    }




    return SomaDia($entrada, ($prazo + $adicional));
}

/**
 * Somar dias
 *
 * @param $data
 * @param int $dias
 *
 * @return false|string
 */
function SomaDia($data, $dias = 1){
    $ano = substr($data, 6,4);
    $mes = substr($data, 3,2);
    $dia = substr($data, 0,2);


    return   date("d/m/Y", mktime(0, 0, 0, $mes, $dia+$dias, $ano));
}

/**
 * Converter data para Timestamp
 *
 * @param $data
 * @return false|int
 */
function dataToTimestamp($data){
    $ano = substr($data, 6,4);
    $mes = substr($data, 3,2);
    $dia = substr($data, 0,2);
            
 
    return mktime(0, 0, 0, $mes, $dia, $ano);
}

/**
 * Calcular dias entre intervalo de datas
 *
 * @param $xDataInicial
 * @param $xDataFinal
 *
 * @return float|int
 */
function calculaDias($xDataInicial, $xDataFinal){

    ////($xDataInicial.' - '.$xDataFinal);

    $time1 = dataToTimestamp($xDataInicial);
    $time2 = dataToTimestamp($xDataFinal);


    $tMaior = $time1>$time2 ? $time1 : $time2;
    $tMenor = $time1<$time2 ? $time1 : $time2;


    $diff = $tMaior-$tMenor;
    $numDias =(int)($diff/86400); //86400 é o número de segundos que 1 dia possui



    return $numDias;
}


/**
 * Calcular dias entre intervalo de datas de outra maneira
 *
 * @param $xDataInicial
 * @param $xDataFinal
 *
 * @return float|int
 */
function calculaDias2($xDataInicial, $xDataFinal){

    $diffTime = abs(strtotime($xDataInicial) - strtotime($xDataFinal));
    $numDias = (int)($diffTime/84600);


    return $numDias;
}

/**
 * Calcular dias úteis entre datas
 *
 * @param $yDataInicial
 * @param $yDataFinal
 *
 * @return int
 */
function diasUteis($yDataInicial,$yDataFinal){

    $diaFDS = 0; //dias não úteis(Sábado=6 Domingo=0 ou dia não trabalhado(feriado))
    
    $calculoDias = CalculaDias($yDataInicial, $yDataFinal); //número de dias entre a data inicial e a final
    //para os dias não trabalhados (FERIADOS E Etc.)
    $diasNaoTrabalhados = getDiasNaoTrabalhados($yDataInicial, $yDataFinal);
    //Soma um dia para compreender o último dia no cálculo
    $yDataFinal = SomaDia($yDataFinal);
    while($yDataInicial!=$yDataFinal){
        $diaSemana = date("w", dataToTimestamp($yDataInicial));
        ////('Dia da Semana: '.$diaSemana . ' Data:'. $yDataInicial);
        if($diaSemana==0 || $diaSemana==6){
            //se SABADO OU DOMINGO, SOMA 01
            $diaFDS++;
        }else{
            //calcula os dias não trabalhados
            if(in_array($yDataInicial, $diasNaoTrabalhados)) {
                $diaFDS++;
            
            }
        }

        $yDataInicial = SomaDia($yDataInicial); //dia + 1
    }

    return $calculoDias - $diaFDS;
}

/**
 * Verificar erro nas consultas
 *
 * @param $res
 *
 * @return bool
 */
function isError($res) {
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    }

    return false;
}
