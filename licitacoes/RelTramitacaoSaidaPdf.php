<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelTramitacaoSaidaPdf.php
# Objetivo: Programa para impressão do relatório de Tramitação
# Autor:    Pitang Agile TI - Ernesto Ferreira
# Data:     30/07/2018
#------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     10/06/2019
# Objetivo: Tarefa Redmine 218525
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "../common/mpdf60/mpdf.php";

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';
require_once 'funcoesTramitacao.php';

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
//AddMenuAcesso( '/estoques/RelMovimentacaoTipoMovimento.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){

	$numProtocolo   = $_GET['numProtocolo'];
	$anoProtocolo   = $_GET['anoProtocolo'];
	$orgao          = $_GET['orgao'];
	$objeto         = $_GET['objeto'];
	$numeroci       = $_GET['numeroci'];
	$numeroOficio   = $_GET['numeroOficio'];
	$numeroScc      = $_GET['numeroScc'];
	$proLicitatorio = $_GET['proLicitatorio'];
	$acao           = $_GET['acao'];
	$origem         = $_GET['origem'];
	$DataSaidaIni    = $_GET['DataSaidaIni'];
	$DataSaidaFim    = $_GET['DataSaidaFim'];



}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();


# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Tramitação";


# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
	$pdf->SetFont("Arial","",8);

// Início conteudo do PDF
$html = "<html><body>";
$css = "
@page {
	margin-top:15px;
	margin-left:40px;
	margin-right:40px;
}

body{
	font-family:Arial;
	font-size: 12px;
}

#cabecalho{
	margin-top:0px;
	font-weight: bold;
	font-family:Arial;
	font-size: 14px;
}

.titulo{
	font-weight: bold;
	font-family:Arial;
	font-size: 14px;
}

.campos_pesquisa{
	border-spacing:0px;
	border-bottom: 1px solid #000;
	border-left: 1px solid #000;
}

.campos_pesquisa td{
	border: 1px solid #000;
	border-bottom: 0px solid #000;
	border-left: 0px solid #000;

	padding:3px;
}

.campo_pesquisado{
	background-color: #D3D3D3;
}

#titulorelatorio{
	margin-top: 10px;
	border-top: 1px solid #000;
	border-bottom: 1px solid #000;

}
#rodaperelatorio{
	margin-bottom: 0px;
	border-top: 1px solid #000;


}
#brasao{
	margin-left:215px;
}";
$brasao = "../" . $GLOBALS["PASTA_MIDIA"] . "brasaopeq.jpg";
$cabecalho = "<table width='100%' id='cabecalho' >
				<tr>
					<td ><br><br>Prefeitura do Recife</td>
					<td ><img id='brasao' src='$brasao' width='85' height='auto'></td>
					<td style='text-align:right;'><br><br>Portal de Compras</td>
					</tr>

				</table>
				<br>
				<table width='100%' id='titulorelatorio'>
					<tr>
						<td align='center' class='titulo'>".$GLOBALS['TituloRelatorio']."</td>
					</tr>
				</table>
				<br>
				";

$html .= $cabecalho;

$htmlPesquisa .= "<table width='100%' class='campos_pesquisa'>";
# Pega os dados do almoxarifado #
$db   = Conexao();
$sql = "SELECT cprotcsequ, cgrempcod1, cprotcnump, aprotcanop, corglicod1, 
xprotcobje, eprotcnuci, eprotcnuof, csolcosequ, prot.clicpoproc, 
prot.alicpoanop, prot.cgrempcodi, prot.ccomlicodi, prot.corglicodi, TPROTCENTR, 
vprotcvale, xprotcobse, prot.cusupocodi, cusupocod1, tprotculat, 
org.eorglidesc, 
	(   select f.efasesdesc from sfpc.tbfaselicitacao fase 
		join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
		where fase.clicpoproc = prot.clicpoproc and fase.alicpoanop = prot.alicpoanop 
		and fase.ccomlicodi= prot.ccomlicodi and fase.corglicodi = prot.corglicodi 
		and fase.cgrempcodi = prot.cgrempcodi 
		order by fase.tfaselulat DESC 
		limit 1 
	) as fase_licitacao, 
	(select acao.etacaodesc from sfpc.tbtramitacaolicitacao tram 
		join sfpc.tbtramitacaoacao acao on acao.ctacaosequ = tram.ctacaosequ 
		where prot.cprotcsequ = tram.cprotcsequ 
		order by tram.ttramlentr desc, acao.ttacaoulat desc 
		limit 1)as acaoDesc, 

	(select agente.etagendesc from sfpc.tbtramitacaolicitacao tram 
		join sfpc.tbtramitacaoagente agente on agente.ctagensequ = tram.ctagensequ 
		where prot.cprotcsequ = tram.cprotcsequ 
		order by tram.ttramlentr desc 
		limit 1)as agenteOrigemDesc, 

	(select tram.ctagensequ from sfpc.tbtramitacaolicitacao tram
		where prot.cprotcsequ = tram.cprotcsequ
		limit 1)as codAgenteOrigem,

	(select usu.eusuporesp from sfpc.tbtramitacaolicitacao tram
		join sfpc.tbusuarioportal usu on usu.cusupocodi = tram.cusupocodi
		where prot.cprotcsequ = tram.cprotcsequ
		order by tram.ttramlentr desc
		limit 1)as agenteUsuDesc,

	(select tram.ttramlentr from sfpc.tbtramitacaolicitacao tram
		where prot.cprotcsequ = tram.cprotcsequ
		order by tram.ttramlentr desc
		limit 1)as datahoraSaidaAcao,
		
		com.ecomlidesc,

	(select acao.ftacaotusu from sfpc.tbtramitacaolicitacao tram
		join sfpc.tbtramitacaoacao acao on acao.ctacaosequ = tram.ctacaosequ
		where prot.cprotcsequ = tram.cprotcsequ
		order by tram.ttramlentr desc, acao.ttacaoulat desc
		limit 1)as acaoParaTodosUsu,

	(select usu.cusupocodi from sfpc.tbtramitacaolicitacao tram
		join sfpc.tbusuarioportal usu on usu.cusupocodi = tram.cusupocodi
		where prot.cprotcsequ = tram.cprotcsequ
		order by tram.ttramlentr desc
		limit 1)as tramUsuCod,

	(select agente.FTAGENTIPO from sfpc.tbtramitacaolicitacao tram 
		join sfpc.tbtramitacaoagente agente on agente.ctagensequ = tram.ctagensequ 
		where prot.cprotcsequ = tram.cprotcsequ 
		order by tram.ttramlentr desc 
		limit 1)as agentetipo
	
FROM sfpc.tbtramitacaoprotocolo prot
join sfpc.tborgaolicitante org on org.corglicodi = prot.corglicod1
LEFT JOIN sfpc.tbcomissaolicitacao com on com.ccomlicodi = prot.ccomlicodi 
WHERE 1=1  ";

$comissoesUsuario = getComissoesUsuario($db, $_SESSION['_cusupocodi_']);
$listaComissoesUsuario = listarResultado($comissoesUsuario);

if (count($comissoesUsuario)){
	//Caso tenha comissão associada ao usuario 
	//Lista todos os protocolos que tenham a comissão(ões) encontrada
	$sql .= " AND prot.cprotcsequ in (select tram.cprotcsequ from sfpc.tbtramitacaolicitacao tram
	join sfpc.tbtramitacaoacao ac on tram.ctacaosequ = ac.ctacaosequ 
	join sfpc.tbtramitacaoagente agen on tram.ctagensequ = agen.ctagensequ
	where tram.ttramlsaid is not NULL 
		AND (tram.ccomlicodi in (".$listaComissoesUsuario.")) OR
		tram.ctagensequ in (select agusu.ctagensequ
		from sfpc.tbtramitacaoagenteusuario agusu
		where agusu.cusupocodi = ".$_SESSION['_cusupocodi_'].")

	AND ((ac.ftacaotusu = 'S' ) OR tram.cusupocodi = ".$_SESSION['_cusupocodi_'].") AND tram.ttramlsaid is not NULL
	and (agen.ftagencomis <> 'S' OR agen.ftagencomis is null)
	)";

	//die($sql);
}else{

	//Caso tenha agente associado ao usuário
	$sql .= " AND prot.cprotcsequ in (select tram.cprotcsequ from sfpc.tbtramitacaolicitacao tram
	join sfpc.tbtramitacaoacao ac on tram.ctacaosequ = ac.ctacaosequ 
	join sfpc.tbtramitacaoagente agen on tram.ctagensequ = agen.ctagensequ
	where tram.ctagensequ in (select agusu.ctagensequ
							  from sfpc.tbtramitacaoagenteusuario agusu
							  where agusu.cusupocodi = ".$_SESSION['_cusupocodi_'].")
							  AND ((ac.ftacaotusu = 'S') OR tram.cusupocodi = ".$_SESSION['_cusupocodi_'].") AND tram.ttramlsaid is not NULL
							  and (agen.ftagencomis <> 'S' or agen.ftagencomis is null)
	) ";


}

        // Numero/ano de protocolo licitatório
        if($numProtocolo != ""){

			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>NÚMERO/ANO DO PROTOCOLO</td>
								<td>".str_pad($numProtocolo, 4, "0", STR_PAD_LEFT)."/".$anoProtocolo."</td>
							</tr>";
			//SQL
            $sql .= " AND cprotcnump = ".$numProtocolo." AND aprotcanop =".$anoProtocolo." ";
        }

        // Órgão
        if($orgao != 0){

			//Retorna os Dados dos Orgãos
			$sqlorgao = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE FORGLISITU = 'A' AND CORGLICODI =".$orgao;
			$db = Conexao();
			$resultOrgao = $db->query($sqlorgao);
			if (PEAR::isError($resultOrgao)) {
				ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlorgao");
			} else {
				while ($LinhaOrgao = $resultOrgao->fetchRow()) {
						$orgaoDesc = $LinhaOrgao[1];
				}

			}

			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>ÓRGÃO DEMANDANTE</td>
								<td>".$orgaoDesc."</td>
							</tr>";
			//SQL
            $sql .= " AND prot.corglicod1 = ".$orgao." ";
        }

        // Objeto
        if($objeto != ""){
			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>OBJETO</td>
								<td>".$objeto."</td>
							</tr>";
			//SQL
            $sql .= " AND xprotcobje like '%".strtoUpper2($objeto)."%' ";
        }

        // Numero CI
        if($numeroci != ""){ 
			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>NÚMERO CI</td>
								<td>".$numeroci."</td>
							</tr>";

			//SQL
            $sql .= " AND eprotcnuci like '%".strtoUpper2($numeroci)."%' ";
        }

        // Numero Oficio
        if($numerooficio != ""){

			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>NÚMERO OFÍCIO</td>
								<td>".$numerooficio."</td>
							</tr>";
			//SQL
            $sql .= " AND eprotcnuof like '%".strtoUpper2($numeroOficio)."%' ";
        	}
        // Número da SCC
        if($numeroScc != ""){ 

			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>NÚMERO DA SCC</td>
								<td>".getNumeroSolicitacaoCompra($db, $numeroScc)."</td>
							</tr>";
			//SQL
            $sql .= " AND csolcosequ = ".$numeroScc." ";
		}
		
        // Processo Licitatorio
		if($proLicitatorio != 0 ){ // prot.clicpoproc, prot.alicpoanop, prot.cgrempcodi

			$arrProLicitatorio = explode("_",$proLicitatorio);
			
			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>PROCESSO LICITATÓRIO</td>
								<td>".str_pad($arrProLicitatorio[0], 4, "0", STR_PAD_LEFT)."/".$arrProLicitatorio[1]."</td>
							</tr>";
            
            $sql .= " AND prot.clicpoproc = $arrProLicitatorio[0] ";
            $sql .= " AND prot.alicpoanop = $arrProLicitatorio[1] ";
            $sql .= " AND prot.ccomlicodi = $arrProLicitatorio[2] ";
            $sql .= " AND prot.cgrempcodi = $arrProLicitatorio[3] ";
            $sql .= " AND prot.corglicodi = $arrProLicitatorio[4] ";

		}  
		
        // Ação
        if($acao != 0){

			//Retorna os Dados das Ações
			$sqlAcao = "SELECT CTACAOSEQU, ETACAODESC FROM SFPC.TBTRAMITACAOACAO WHERE CTACAOSEQU=".$acao;
			$resultAcao = $db->query($sqlAcao);
			if (PEAR::isError($resultAcao)) {
				ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlAcao");
			} else {

				while ($LinhaAcao = $resultAcao->fetchRow()) {
					if ($LinhaAcao[0] == $acao) {
						$acaoDesc = $LinhaAcao[1];
					} 
				}

			}

			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>AÇÃO</td>
								<td>".$acaoDesc."</td>
							</tr>";
			//SQL
            $sql .= " AND prot.cprotcsequ in 
						(select distinct tram.cprotcsequ 
						from sfpc.tbtramitacaolicitacao tram
						where tram.ctacaosequ = ".$acao.")";
        }      

        // Agente de Origem
        if ($origem != 0) {
			//Retorna os Dados dos Agentes Origem
			$sqlorigem = "SELECT CTAGENSEQU, ETAGENDESC FROM SFPC.TBTRAMITACAOAGENTE WHERE CTAGENSEQU = " . $origem;

			$resultorigem = $db->query($sqlorigem);
			if (PEAR::isError($resultorigem)) {
				ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlorigem");
			} else {
				while ($LinhaOrigem = $resultorigem->fetchRow()) {
					if ($LinhaOrigem[0] == $origem) {
						$origemDesc = $LinhaOrigem[1];
					} 
				}
			}
			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>AGENTE ORIGEM</td>
								<td>".$origemDesc."</td>
							</tr>";
			//SQL
            $sql .= " AND prot.cprotcsequ in 
						(select tram.cprotcsequ 
						from sfpc.tbtramitacaolicitacao tram
						where tram.ctagensequ in (select agusu.ctagensequ
						from sfpc.tbtramitacaoagente agusu
						where agusu.ctagensequ = ".$origem."
						))";
        }    

        // Data Saida
        if($DataSaidaIni != ""){
			$htmlPesquisa .= "<tr>
								<td width='15%' class='campo_pesquisado'>PERÍODO</td>
								<td>".$DataSaidaIni." A ".$DataSaidaFim."</td>
							</tr>";
			//SQL
            $DataSaidaIniFormatada = explode("/",$DataSaidaIni);
            $DataSaidaIniFormatada = $DataSaidaIniFormatada[2]."-".$DataSaidaIniFormatada[1] ."-".$DataSaidaIniFormatada[0];
            
            $DataSaidaFimFormatada = explode("/",$DataSaidaFim);
            $DataSaidaFimFormatada = $DataSaidaFimFormatada[2]."-".$DataSaidaFimFormatada[1] ."-".$DataSaidaFimFormatada[0];
            $sql .= " AND TPROTCENTR between '".$DataSaidaIniFormatada."' AND '".$DataSaidaFimFormatada."' ";
        }

$resitens = $db->query($sql);

$htmlPesquisa .= "</table>";

$html .= $htmlPesquisa;

if( PEAR::isError($resitens) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlitens");
}else{
		$rowsitens = $resitens->numRows();
		if($rowsitens > 0){

			$htmlCabecalhoItem .="<br><table width='100%' class='campos_pesquisa'><tr>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2' width='7%'>NÚMERO/ANO PROTOCOLO</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2' width='9%'>ÓRGÃO</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2' width='20%'>OBJETO</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2'>NÚMERO CI</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2'>NÚMERO OFÍCIO</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2'>NÚMERO SCC</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2'>PROCESSO LICITATÓRIO</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2'>FASE ATUAL</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' rowspan='2' width='6%'>DATA/HORA ENTRADA</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' colspan='4'>ÚLTIMO PASSO</td>";
			$htmlCabecalhoItem .="</tr><tr>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado'>AÇÃO</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado'>AGENTE ORIGEM</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' width='8%'>USUÁRIO RESPONSÁVEL</td>";
			$htmlCabecalhoItem .="<td align='center' class='campo_pesquisado' width='7%'>DATA/HORA TRAMITAÇÃO</td>";
			$htmlCabecalhoItem .= "</tr>";

			$html .= $htmlCabecalhoItem;
			$htmlItem .= "";

			while($Linha = $resitens->fetchRow()){
				$seq = $Linha[0];//
				$protocolo 	   = str_pad($Linha[2], 4, "0", STR_PAD_LEFT)."/".$Linha[3];// Protocolo / Ano
				$orgao = $Linha[20];//órgão
				$objeto = $Linha[5];//objeto
				$numCi = $Linha[6];//num. CI
				$numOficio = $Linha[7];//num oficio

				if($Linha[8]){
					$numScc = getNumeroSolicitacaoCompra($db, $Linha[8]);//Numero SCC
				}else{
					$numScc = "";
				}
				

				if($Linha[9]>0){
					$procLic = str_pad($Linha[9], 4, "0", STR_PAD_LEFT)."/".$Linha[10]." - ".$Linha[27];//Código do processo licitatório
				}else{
					$procLic = '';
				}
				
				$faseLic = $Linha[21];//fase licitacao
				$dataSaida = date('d/m/Y H:i:s',strtotime($Linha[19]));
				//Dados da Ação                                      
				$acao = $Linha[22];
				$origem = $Linha[23];
                    //Usuario
                    $usuario = '';
                    if($Linha[28]=='S'){
                                                    
                        if($Linha[29] <= 0 ){
                            $usuario = $Linha[23];
                        }else{
                            $usuario = $Linha[25];
                        }
                    }else{
                        if($Linha[29] <= 0){
                            if($Linha[30]=='I'){
                                $usuario = $Linha[23]; // ETAGENDESC
                            }else{
                                $usuario = 'ÓRGÃO EXTERNO';
                            }
                        }else{
                            $usuario = $Linha[25];
                        }
                    }


				$dataTramitacao = date('d/m/Y H:i:s',strtotime($Linha[26]));

			
				$htmlItem .= "<tr><td align='center'>".$protocolo."</td>";
				$htmlItem .= "<td align='left'>".$orgao."</td>";
				$htmlItem .= "<td align='left'>".$objeto."</td>";
				$htmlItem .= "<td align='center'>".$numCi."</td>";
				$htmlItem .= "<td align='center'>".$numOficio."</td>";
				$htmlItem .= "<td align='center'>".$numScc."</td>";
				$htmlItem .= "<td align='center'>".$procLic."</td>";
				$htmlItem .= "<td align='center'>".$faseLic."</td>";
				$htmlItem .= "<td align='center'>".$dataSaida."</td>";
				$htmlItem .= "<td align='center'>".$acao."</td>";
				$htmlItem .= "<td align='center'>".$origem."</td>";
				$htmlItem .= "<td align='center'>".$usuario."</td>";
				$htmlItem .= "<td align='center'>".$dataTramitacao."</td></tr>";
			}
								
			
			$html .= $htmlItem;
			$html .= "</table>";			

		}else{
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "CadTramitacaoSaida.php?retornoVazioPdf=1";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
$db->disconnect();





//finaliza arquivo
$html .= "</body></html>";

$mpdf=new mPDF("c","A4-L"); 
$mpdf->SetDisplayMode('fullpage');
//$css = file_get_contents("css/estilo.css");

$mpdf->WriteHTML($css,1);
$mpdf->WriteHTML($html);
$mpdf->SetHTMLFooter('
<table width="100%" id="rodaperelatorio"><tr><td></td></tr></table>
<table width="100%">
    <tr>
        <td width="33%">Emissão: {DATE d/m/Y H:i:s}</td>
        <td width="33%" align="center"></td>
        <td width="33%" style="text-align: right;">{PAGENO}/{nbpg}</td>
    </tr>
</table>');
$mpdf->Output();
?>
