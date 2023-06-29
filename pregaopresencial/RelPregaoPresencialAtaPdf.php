<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelPregaoPresencialAtaPdf.php
# Objetivo: Imprimir a Ata do Pregão Presencial
# Autor:    Hélio Miranda
# Data:     06/02/2017
#---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Processo        = $_GET['Processo'];
		$Ano             = $_GET['Ano'];
		$Comissao        = $_GET['Comissao'];
		$Orgao           = $_GET['Orgao'];	
		$TipoAta         = $_GET['TipoAta'];
		$Recurso         = $_GET['Recurso'];
		$IntervaloLotes  = $_GET['IntervaloLotes'];
		$NomeEquipe		 = $_GET['NomeEquipe'];
		$TipoAtaExt 	 = "";
		
		switch ($TipoAta) {
			case 1:
				$TipoAtaExt = "Sessão Única";
				break;
			case 2:
				$TipoAtaExt = "Sessão Inicial";
				break;
			case 3:
				$TipoAtaExt = "Continuação de Sessão";
				break;
			case 4:
				$TipoAtaExt = "Sessão Final";
				break;
			case 5:
				$TipoAtaExt = "Sessão Deserta";
				break;				
		}				
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Informa o Título do Relatório #
$TituloRelatorio = "";

# Classes FPDF #
class PDF extends FPDF {
	# Cabeçalho #
	function Header() {
		##### Verificar endereço quando passar para produção #####
		Global $CaminhoImagens;
		$cabecalho = retornaCabecalho();
		
		$this->Image("$CaminhoImagens/brasaopeq.jpg",105,5,0);
		$this->Image("$CaminhoImagens/brasaobg.jpg",5,25,0);
		$this->SetMargins(35,10,15);
		$this->SetFont("Arial","B",10);
		$this->Cell(25,20,"",0,0,"L",0);
		$this->Cell(0,20,"$cabecalho[empresa]",0,0,"L");
		$this->Cell(0,20,"$cabecalho[orgao1]",0,0,"R");
		$this->Ln(1);
		
		$Empresa = $_SESSION['_egruatdesc_'];
		
		$this->Ln(20);
		
		/*
		$this->Cell(0,25,"Secretaria de Administração e Gestão de Pessoas ",0,0,"L",0);
		$this->Cell(0,25,"Gerência Geral de Licitações e Compras",0,0,"R",0);
		$this->Ln(1);
		$this->Cell(0,30,"Portal de Compras",0,0,"L",0);
		$this->Cell(0,30,"",0,0,"R");
		$this->Ln(20);
		*/
	}

	# Rodapé #
	function Footer() {
		$this->SetFont("Arial","",10);
		$this->SetY(-29);
		$this->Cell(0,30,"Emissão: ".date("d/m/Y H:i:s"),0,0,"L");
		$this->Line(10,280,200,280);
		$this->SetY(-19);
		$this->Cell(0,10,"Página: ".$this->PageNo()."/{nb}",0,0,"R");
	}
	
	function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
	{
		$txt = utf8_decode($txt);
		
		$k=$this->k;
		if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
		{
			$x=$this->x;
			$ws=$this->ws;
			if($ws>0)
			{
				$this->ws=0;
				$this->_out('0 Tw');
			}
			$this->AddPage($this->CurOrientation);
			$this->x=$x;
			if($ws>0)
			{
				$this->ws=$ws;
				$this->_out(sprintf('%.3F Tw',$ws*$k));
			}
		}
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$s='';
		if($fill || $border==1)
		{
			if($fill)
				$op=($border==1) ? 'B' : 'f';
			else
				$op='S';
			$s=sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
		}
		if(is_string($border))
		{
			$x=$this->x;
			$y=$this->y;
			if(is_int(strpos($border,'L')))
				$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
			if(is_int(strpos($border,'T')))
				$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
			if(is_int(strpos($border,'R')))
				$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
			if(is_int(strpos($border,'B')))
				$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		}
		if($txt!='')
		{
			if($align=='R')
				$dx=$w-$this->cMargin-$this->GetStringWidth($txt);
			elseif($align=='C')
				$dx=($w-$this->GetStringWidth($txt))/2;
			elseif($align=='FJ')
			{
				//Set word spacing
				$wmax=($w-2*$this->cMargin);
				//Solução para divisão por zero (Anterior: $this->ws=($wmax-$this->GetStringWidth($txt))/substr_count($txt,' ');)
				$this->ws=($wmax-$this->GetStringWidth($txt))/(substr_count($txt,' ') != 0 ? substr_count($txt,' ') : 1);
				$this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
				$dx=$this->cMargin;
			}
			else
				$dx=$this->cMargin;
			$txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
			if($this->ColorFlag)
				$s.='q '.$this->TextColor.' ';
			$s.=sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt);
			if($this->underline)
				$s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
			if($this->ColorFlag)
				$s.=' Q';
			if($link)
			{
				if($align=='FJ')
					$wlink=$wmax;
				else
					$wlink=$this->GetStringWidth($txt);
				$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$wlink,$this->FontSize,$link);
			}
		}
		if($s)
			$this->_out($s);
		if($align=='FJ')
		{
			//Remove word spacing
			$this->_out('0 Tw');
			$this->ws=0;
		}
		$this->lasth=$h;
		if($ln>0)
		{
			$this->y+=$h;
			if($ln==1)
				$this->x=$this->lMargin;
		}
		else
			$this->x+=$w;
	}	
}

function convert_month($month)
{
	$months = array("janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
	
	$x = $month - 1;
	
	return $months[$x];
}

function convert_number_to_words($number) {

    $hyphen      = '-';
    $conjunction = ' e ';
    $separator   = ', ';
    $negative    = 'menos ';
    $decimal     = ' ponto ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'um',
        2                   => 'dois',
        3                   => 'três',
        4                   => 'quatro',
        5                   => 'cinco',
        6                   => 'seis',
        7                   => 'sete',
        8                   => 'oito',
        9                   => 'nove',
        10                  => 'dez',
        11                  => 'onze',
        12                  => 'doze',
        13                  => 'treze',
        14                  => 'quatorze',
        15                  => 'quinze',
        16                  => 'dezesseis',
        17                  => 'dezessete',
        18                  => 'dezoito',
        19                  => 'dezenove',
        20                  => 'vinte',
        30                  => 'trinta',
        40                  => 'quarenta',
        50                  => 'cinquenta',
        60                  => 'sessenta',
        70                  => 'setenta',
        80                  => 'oitenta',
        90                  => 'noventa',
        100                 => 'cento',
        200                 => 'duzentos',
        300                 => 'trezentos',
        400                 => 'quatrocentos',
        500                 => 'quinhentos',
        600                 => 'seiscentos',
        700                 => 'setecentos',
        800                 => 'oitocentos',
        900                 => 'novecentos',
        1000                => 'mil',
        1000000             => array('milhão', 'milhões'),
        1000000000          => array('bilhão', 'bilhões'),
        1000000000000       => array('trilhão', 'trilhões'),
        1000000000000000    => array('quatrilhão', 'quatrilhões'),
        1000000000000000000 => array('quinquilhão', 'quinquilhões')
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words só aceita números entre ' . PHP_INT_MAX . ' à ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $conjunction . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = floor($number / 100)*100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            if ($baseUnit == 1000) {
                $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[1000];
            } elseif ($numBaseUnits == 1) {
                $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit][0];
            } else {
                $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit][1];
            }
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Adiciona uma página no documento #
$pdf->AddPage();

# Configura as margens #
$pdf->SetMargins(35,10,15);

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",10);

# Carrega os dados da licitação selecionada	#
$db     = Conexao();
$sql    = "SELECT 		A.CMODLICODI, D.EMODLIDESC, A.CLICPOCODL, A.ALICPOANOL, ";
$sql   .= "       		A.XLICPOOBJE, B.ECOMLIDESC, A.CORGLICODI, C.EORGLIDESC, ";
$sql   .= "       		A.VLICPOVALE, A.FLICPOREGP, A.CLICPOPROC, A.ALICPOANOP, C.forglitipo";
$sql   .= "  	FROM 	SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBORGAOLICITANTE C, SFPC.TBMODALIDADELICITACAO D ";
$sql   .= " 	WHERE 	A.CMODLICODI = D.CMODLICODI AND A.CLICPOPROC = $Processo ";
$sql   .= "   		AND A.ALICPOANOP = $Ano AND A.CCOMLICODI = $Comissao ";
$sql   .= "   		AND A.CCOMLICODI = B.CCOMLICODI AND A.CORGLICODI = C.CORGLICODI ";
$sql   .= "   		AND A.CORGLICODI = $Orgao AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		while( $Linha = $result->fetchRow() ){
				$Modalidade          = $Linha[0];
				$ModalidadeDescricao = $Linha[1];
				$Licitacao           = substr($Linha[2] + 10000,1);
				$LicitacaoAno        = $Linha[3];
				$Objeto              = trim($Linha[4]);
				$ComissaoDescricao   = $Linha[5];
				$OrgaoCodigo         = $Linha[6];
				$OrgaoDescricao      = $Linha[7];
				$OrgaoTipo		     = $Linha[12];
				$Registro            = $Linha[9];
				$ProcessoNumero      = substr($Linha[10] + 10000,1);
				$ProcessoAno         = $Linha[11];
		}
		
		$sqlSolicitacoes = "SELECT  cpregasequ, fpregatipo, tpregaaber
							FROM 	sfpc.tbpregaopresencial pp 
							WHERE 	pp.clicpoproc  = $ProcessoNumero 
								AND pp.alicpoanop  = $ProcessoAno
								AND pp.ccomlicodi  = $Comissao 
								AND pp.corglicodi  = $OrgaoCodigo 
								AND pp.cgrempcodi  =". $_SESSION['_cgrempcodi_']; 
			
			

		$result= $db->query($sqlSolicitacoes);
		$Linha = $result->fetchRow();

		$PregaoCod		 	= $Linha[0];
		$PregaoTipo			= $Linha[1];
		$DataHoraAbertura	= $Linha[2];

		$DataHoraExtenso = "Às ".date("H:i", strtotime($DataHoraAbertura))." (".convert_number_to_words((int) date("H", strtotime($DataHoraAbertura)))." horas".((date("i", strtotime($DataHoraAbertura)) > 0) ? (" e ".convert_number_to_words(date("i", strtotime($DataHoraAbertura)))." minutos)") : (")"))." do dia ".date("d", strtotime($DataHoraAbertura))." (".convert_number_to_words((int) date("d", strtotime($DataHoraAbertura))).") de ".(convert_month(date("m", strtotime($DataHoraAbertura))))." de ".date("Y", strtotime($DataHoraAbertura));
		
		$sql    = "SELECT 		 	MAX(pl.cpregtnuml), us.eusuporesp";
		$sql   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl, sfpc.tbpregaopresencialmembro mc, sfpc.tbusuarioportal us";
		$sql   .= "  WHERE 			pl.cpregasequ = $PregaoCod
								AND mc.cpregasequ = $PregaoCod
								AND mc.cusupocodi = us.cusupocodi
								AND mc.epregmtipo = 'P'
					 GROUP BY		us.eusuporesp";

		$ResultLance = $db->query($sql);
		if( PEAR::isError($ResultLance) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		
		
		$LinhaLance   = $ResultLance->fetchRow();		
		$TotalLotes			= $LinhaLance[0];
		
		$Pregoeiro			= $LinhaLance[1];
		
		$sqlMembros = "SELECT  	us.eusuporesp
						FROM 	sfpc.tbpregaopresencialmembro mc, sfpc.tbusuarioportal us  
						WHERE 	mc.cpregasequ = $PregaoCod
								AND mc.cusupocodi = us.cusupocodi
								AND mc.epregmtipo <> 'P'"; 		
		
		$ResultMembros = $db->query($sqlMembros);
		if( PEAR::isError($ResultMembros) )
		{
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		
		$CountMembros		= 0;
		$Membros[] = array();
		
		while($LinhaMembros = $ResultMembros->fetchRow())
		{
			$CountMembros = $CountMembros + 1;
			$Membros["membro_".$CountMembros] = $LinhaMembros[0];
		}
		
		$DataHoraAberturaExtenso 	= "";
		
		
	//Início E01...
		$E01 = "Constitui objeto desta licitação ";
		if($Registro == "S")
		{
			$E01 .= "o Registro de Preços ";
		}
		$E01 .= "visando à contratação de empresa para: ".$Objeto.", com ";
		if($TotalLotes == 1)
		{
			$E01 .= "LOTE ÚNICO.";
		}
		else
		{
			$E01 .= ($TotalLotes > 9 ? $TotalLotes : ("0".$TotalLotes))." LOTES.";
		}
		
	//... Fim E01.
	
	$x = strtotime($DataHoraAbertura);
	
	if($_SESSION['deserta'] == "N")
	{
		//Início E03...	
			$E03 = "Dando início à sessão, o(a) Pregoeiro(a) solicitou os documentos de credenciamento das empresas presentes abaixo relacionadas. ";
			$E03 .= "Após o credenciamento, o(a) Pregoeiro(a) recebeu os envelopes de documentação para habilitação e das propostas de preços das ";
			$E03 .= "empresas credenciadas. Em sequência, foi dado início à abertura dos envelopes de proposta(s). Após a análise da(s) proposta(s),"; 
			$E03 .= "o(a) Pregoeiro(a) leu os preços iniciais propostos em voz alta, que foram lançados no Sistema Portal de Compras da Prefeitura ";
			$E03 .= "do Recife, conforme anexo I.";
		//... Fim E03.
		
		//Início E04...	
			$E04 = "Foi iniciada a fase de lances, conforme Anexo II.";
		//... Fim E04.
		
		//Início E05...		
			$E05 = "Em seguida, o(a) Pregoeiro(a) franquiou a palavra aos representantes presentes que nada acrescentaram. Dessa forma, o(a) ";
			$E05 .= "Pregoeiro(a) adjudica o(s) lote(s) de acordo com o Anexo IV.";
		//... Fim E05.
		
		//Início E06...
			$E06 = "Continuando, os documentos de credenciamento, habilitação, proposta de preços e envelopes de habilitação foram rubricados "; 
			$E06 .= "pelos representantes da Prefeitura da Cidade do Recife e pelos representantes presentes.";
		//... Fim E06.
		
		//Início E09...		
			$E09 = "Empresas Credenciadas:";
		//... Fim E09.

		//Início E09...		
			$E10 = "Empresas Participantes sem representante:";
		//... Fim E09.			
	}
	
	
	//Início E08...		
		$E08 = "Membros de Comissão:";		
	//... Fim E08.
	
		
	
		$sqlAta = "SELECT  	pa.epreatpara, pa.epreatparb, pa.epreatparc, pa.epreatpard, pa.epreatorgl, pa.epreatendo, pa.npreattemd, pa.epreatdpar
						FROM 	sfpc.tbpregaopresencialata pa  
						WHERE 	pa.cpregasequ = $PregaoCod"; 		
		
		$ResultAta = $db->query($sqlAta);
		if( PEAR::isError($ResultAta) )
		{
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAta");
		}	
		
		$LinhaAta = $ResultAta->fetchRow();
		
		$P01 = $LinhaAta[0];
		$P02 = $LinhaAta[1];
		$P03 = $LinhaAta[2];
		$P04 = $LinhaAta[3];
		
		$OrgaoLicitante 			= $LinhaAta[4];
		$EnderecoOrgaoLicitante 	= $LinhaAta[5];		
		$TempoTolerancia 			= $LinhaAta[6];
		$DemaisParticipantes 		= $LinhaAta[7];
		
	//Início E02...
		$StringMembros = "";
		
		for($itrL = 1; $itrL <= $CountMembros; ++ $itrL)
		{	
			$StringMembros .= $Membros["membro_".$itrL];
			
			if(($itrL + 1) == $CountMembros)
			{
				$StringMembros .= " e ";
			}
			else
			{
				if($itrL < $CountMembros)
				{
					$StringMembros .= ", ";		
				}				
			}
			

		}	
		
		$OrgaoSede = "PREFEITURA DO RECIFE";
		
		if($OrgaoTipo == "I")
		{
			if($OrgaoLicitante == "")
			{
				$OrgaoSede = $OrgaoDescricao;				
			}
			else
			{
				$OrgaoSede = $OrgaoLicitante;
			}
		}
		
		if($OrgaoLicitante <> "")
		{
			$OrgaoSede = $OrgaoLicitante;
		}
		
		if($EnderecoOrgaoLicitante <> "")
		{
			$OrgaoSede .= ", situada no(a) $EnderecoOrgaoLicitante";
		}		
		
		if($DemaisParticipantes != "")
		{
			$DemaisParticipantes = " e ".$DemaisParticipantes.", ";
		}

		if($NomeEquipe == 0)
		{
			$NomeEquipeTxt = "pelos membros da comissão";
		}
		else
		{
			$NomeEquipeTxt = "pela equipe de apoio";
		}
		
		$E02 = "$DataHoraExtenso, na sede do(a) $OrgaoSede, sob a coordenação do(a) Pregoeiro(a) $Pregoeiro, auxiliada $NomeEquipeTxt $StringMembros, $DemaisParticipantes foi instalada a sessão para proceder ao Processo Licitatório n° $ProcessoNumero/$ProcessoAno e o Pregão Presencial nº $Licitacao/$LicitacaoAno.";
		
		if($_SESSION['deserta'] == "D")
		{
			$E02 .= "Iniciada a sessão, fora dada tolerância de $TempoTolerancia minutos para comparecimento de interessado. ";
			$E02B = "Não havendo comparecido nenhum interessado, a licitação foi considerada DESERTA. Não mais havendo a declarar, a sessão foi encerrada com a lavratura da presente ata, lida e assinada pelos representantes do(a) $OrgaoSede";
		}
	//... Fim E02.

	//Início E07...		
		$E07 = "Nada mais havendo a declarar, a sessão foi encerrada com a lavratura da presente ata, lida e assinada pelos representantes "; 
		$E07 .= "da $OrgaoSede e pelos representantes das empresas presentes.";
	//... Fim E07.	
}

$pdf->SetFont("Arial","B",9);
$pdf->Cell(10,10,"",0,0,"L",0);
$pdf->Cell(140,5,$ComissaoDescricao,0,1,"C",0);      
$pdf->Cell(10,10,"",0,0,"L",0);
$pdf->Cell(140,5,"Ata da $TipoAtaExt do Processo Licitatório nº $ProcessoNumero/$ProcessoAno e Pregão Presencial nº $Licitacao/$LicitacaoAno ",0,1,"C",0);

if($TipoAta >= 2 and $TipoAta <= 4)
{
	$pdf->Cell(10,10,"",0,0,"L",0);
	$pdf->Cell(140,5,"Lances (Lotes:".$_SESSION['LoteInicialIntervalo']." a ".$_SESSION['LoteFinalIntervalo'].")",0,1,"C",0);
}

$pdf->Cell(10,10,"",0,0,"L",0);
$pdf->Cell(140,5,$OrgaoDescricao,0,1,"C",0);

$pdf->SetFont("Arial","",9);
$pdf->ln(10);
$pdf->MultiCell(160,5,$E01,0,"FJ",0);
$pdf->ln(5);
$pdf->MultiCell(160,5,$E02,0,"FJ",0);
$pdf->ln(5);

if($_SESSION['deserta'] == "D")
{
	if($P01 != "")
	{
		$pdf->MultiCell(160,5,$P01,0,"FJ",0);
		$pdf->ln(5);	
	}	
	
	$pdf->MultiCell(160,5,$E02B,0,"FJ",0);
	$pdf->ln(5);	
}

if($_SESSION['deserta'] == "N")
{
	$pdf->MultiCell(160,5,$E03,0,"FJ",0);
	$pdf->ln(5);
	
	
	/**/
	
	//Select todos os Lotes com Fornecedores Desclassificados para o Pregao
	
	$SQLDesclassificados = 
	"SELECT		DISTINCT fn.cpregfsequ, fn.npregfrazs, fn.apregfccpf, fn.apregfccgc
	FROM 		sfpc.tbpregaopresencialclassificacao cl 
				INNER JOIN sfpc.tbpregaopresencialfornecedor fn ON fn.cpregfsequ = cl.cpregfsequ
				INNER JOIN sfpc.tbpregaopresenciallote lt ON lt.cpregtsequ = cl.cpregtsequ
				INNER JOIN sfpc.tbpregaopresencial pp ON pp.cpregasequ = lt.cpregasequ
	WHERE		pp.cpregasequ = $PregaoCod
		AND 	lt.cpreslsequ > 1
		AND 	cl.cpresfsequ > 1		
	ORDER BY	fn.npregfrazs";
		 // echo $SQLDesclassificados;
		 // exit;
	
	$ResultDesclassificados = $db->query($SQLDesclassificados);
	if( PEAR::isError($ResultDesclassificados) )
	{
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SQLDesclassificados");
	}	
		
		
	$TotalDesclassificado = $ResultDesclassificados->numRows();
	if($TotalDesclassificado > 0)
	{
		$DesclassificadosIntroducao = "Todas as empresas foram classificadas com exceção de:";
		
		$pdf->MultiCell(160,5,$DesclassificadosIntroducao,0,"L",0);		
		
		while($LinhaDesclassificados = $ResultDesclassificados->fetchRow())
		
		{
		
			$CodigoDesclassificado 			= $LinhaDesclassificados[0];
			$RazaoSocialDesclassificado 	= $LinhaDesclassificados[1];
			$CnpjCpfDesclassificado			= ($LinhaDesclassificados[3] == "" 
												?
												(substr($LinhaDesclassificados[2], 0, 2).'.'
												.substr($LinhaDesclassificados[2], 2, 3).'.'
												.substr($LinhaDesclassificados[2], 5, 3).'/'
												.substr($LinhaDesclassificados[2], 8, 4).'-'
												.substr($LinhaDesclassificados[2], 12, 2)) 
												: 
												(substr($LinhaDesclassificados[3], 0, 3).'.'
												.substr($LinhaDesclassificados[3], 3, 3).'.'
												.substr($LinhaDesclassificados[3], 6, 3).'-'
												.substr($LinhaDesclassificados[3], 9, 2)));

			// echo $RazaoSocialDesclassificado;
			// exit;
			$pdf->ln(5);												
			$pdf->MultiCell(160,5,$RazaoSocialDesclassificado." - ".$CnpjCpfDesclassificado,0,"L",0);
			$pdf->ln(1);	

			
			//Select todos os Lotes, tipo > 2, no qual o Fornecedor foi desclassificado
			
			$SQLDesclassificadosLote =  
			"SELECT		DISTINCT lt.cpregtnuml, lt.epregtdesc, sf.epresfnome, cl.epregcmoti
			FROM 		sfpc.tbpregaopresencialclassificacao cl 
						INNER JOIN sfpc.tbpregaopresencialfornecedor fn ON fn.cpregfsequ = cl.cpregfsequ
						INNER JOIN sfpc.tbpregaopresenciallote lt ON lt.cpregtsequ = cl.cpregtsequ
						INNER JOIN sfpc.tbpregaopresencial pp ON pp.cpregasequ = lt.cpregasequ
						INNER JOIN sfpc.tbpregaopresencialsituacaofornecedor sf ON sf.cpresfsequ = cl.cpresfsequ
			WHERE		pp.cpregasequ = $PregaoCod
				AND 	cl.cpregfsequ = $CodigoDesclassificado
				AND 	lt.cpreslsequ > 1
				AND 	cl.cpresfsequ > 1
			ORDER BY	lt.cpregtnuml";
			
			// echo $SQLDesclassificados;
			// exit;
			$ResultDesclassificadosLote = $db->query($SQLDesclassificadosLote);
			if( PEAR::isError($ResultDesclassificadosLote) )
			
			{
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SQLDesclassificadosLote");
			}			
			
			while($LinhaLotesFornecedorDesclassificadoLote = $ResultDesclassificadosLote->fetchRow())
			{
				$NumeroLote					= $LinhaLotesFornecedorDesclassificadoLote[0];
				$DescricaoLote				= $LinhaLotesFornecedorDesclassificadoLote[1];
				$TipoDesclassificacao		= $LinhaLotesFornecedorDesclassificadoLote[2];
				$MotivoDesclassificacao		= $LinhaLotesFornecedorDesclassificadoLote[3];
				
				$pdf->MultiCell(160,5,"Lote: ".$NumeroLote.(($DescricaoLote != "") ? (" (".$DescricaoLote.")") : ("")),0,"L",0);
				$pdf->MultiCell(160,5,"Situação: ".$TipoDesclassificacao,0,"L",0);
				$pdf->MultiCell(160,5,"Motivo: ".$MotivoDesclassificacao,0,"L",0);				
				$pdf->ln(5);
			}
			
			// echo $LinhaDesclassificados[1];
			// exit;
			$pdf->ln(5);
		}
	}	
	/**/

	if($P01 != "")
	{
		$pdf->MultiCell(160,5,$P01,0,"FJ",0);
		$pdf->ln(5);	
	}

	$pdf->MultiCell(160,5,$E04,0,"L",0);
	$pdf->ln(5);



	$SqlLotes 			= "SELECT  		pl.cpregtnuml, pl.vpregtvalv, pl.vpregtvalr, pf.apregfccgc, pf.apregfccpf, pf.npregfrazs, pl.cpreslsequ, pl.epregtdess
							FROM 		sfpc.tbpregaopresenciallote pl, sfpc.tbpregaopresencialfornecedor pf 
							WHERE 		pf.cpregasequ = $PregaoCod
								AND		pl.cpregfsequ = pf.cpregfsequ
								AND		pl.cpreslsequ > 1
							ORDER BY 	pl.cpregtnuml"; 		

		// echo $SqlLotes;
		// die;
	$ResultLotes = $db->query($SqlLotes);
	if( PEAR::isError($ResultLotes) )
	{
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlLotes");
	}

	$PregaoEmAndamento = False;
	
	while($LinhaLotes = $ResultLotes->fetchRow())
	{
		if($IntervaloLotes == True)
		{

			$NumLote 			= $LinhaLotes[0];
						
			if($NumLote >= $_SESSION['LoteInicialIntervalo'] and $NumLote <= $_SESSION['LoteFinalIntervalo'])
			{				
				$ValorVencedor 		= number_format($LinhaLotes[1], 4, ',', '.');
				$ValorRenegociado 	= number_format($LinhaLotes[2], 4, ',', '.');
				$CnpjCpf			= ($LinhaLotes[4] == "" 
									?
									(substr($LinhaLotes[3], 0, 2).'.'.substr($LinhaLotes[3], 2, 3).'.'.substr($LinhaLotes[3], 5, 3).'/'.substr($LinhaLotes[3], 8, 4).'-'.substr($LinhaLotes[3], 12, 2)) 
									: 
									(substr($LinhaLotes[4], 0, 3).'.'.substr($LinhaLotes[4], 3, 3).'.'.substr($LinhaLotes[4], 6, 3).'-'.substr($LinhaLotes[4], 9, 2)));
				$RazaoSocial		= $LinhaLotes[5];
				$TipoLicitacaoTxt	= (($PregaoTipo == "N") ? ("ofertou o menor valor") : ("deu a maior oferta"));
				$MotivoFracasso 	= $LinhaLotes[7];
				
				$ELA = "Após a rodada de lances para o Lote $NumLote, a empresa  $CnpjCpf - $RazaoSocial $TipoLicitacaoTxt, ";
				$ELA .= "estando dentro da estimativa de preço da administração, o que foi aceito pela o(a) Pregoeiro(a).";
				
				if($ValorRenegociado > 0)
				{
					$ELB = "O(A) Pregoeiro(a) fez uma renegociação com a empresa arrematante e chegou ao valor final de R$ $ValorRenegociado";
				}
				else
				{
					$ELB = "A  empresa arrematante chegou ao valor final de R$ $ValorVencedor .";
				}
				
				if($LinhaLotes[6] == 1)
				{
					$PregaoEmAndamento = True;
				}
				
				if($LinhaLotes[6] == 5 and $MotivoFracasso != "")
				{
					$ELA = "O Lote $NumLote, foi fracassado pelo motivo: ".$MotivoFracasso.".";
					$ELB = "";
				}
				
				$pdf->MultiCell(160,5,$ELA,0, ($MotivoFracasso != "" ? "FJ" : "L") ,0);
				$pdf->ln(5);
				
				if($ELB != "")
				{
					$pdf->MultiCell(160,5,$ELB,0,"L",0);
					$pdf->ln(5);	
				}
			}
		}
		else
		{
			$NumLote 			= $LinhaLotes[0];
			$ValorVencedor 		= number_format($LinhaLotes[1], 4, ',', '.');
			$ValorRenegociado 	= number_format($LinhaLotes[2], 4, ',', '.');
			$CnpjCpf			= ($LinhaLotes[4] == "" 
								?
								(substr($LinhaLotes[3], 0, 2).'.'.substr($LinhaLotes[3], 2, 3).'.'.substr($LinhaLotes[3], 5, 3).'/'.substr($LinhaLotes[3], 8, 4).'-'.substr($LinhaLotes[3], 12, 2)) 
								: 
								(substr($LinhaLotes[4], 0, 3).'.'.substr($LinhaLotes[4], 3, 3).'.'.substr($LinhaLotes[4], 6, 3).'-'.substr($LinhaLotes[4], 9, 2)));
			$RazaoSocial		= $LinhaLotes[5];
			$TipoLicitacaoTxt	= (($PregaoTipo == "N") ? ("ofertou o menor valor") : ("deu a maior oferta"));
			$MotivoFracasso 	= $LinhaLotes[7];
			
			$ELA = "Após a rodada de lances para o Lote $NumLote, a empresa $RazaoSocial - $CnpjCpf $TipoLicitacaoTxt, ";
			$ELA .= "estando dentro da estimativa de preço da administração, o que foi aceito pela o(a) Pregoeiro(a).";
			
			if($ValorRenegociado > 0)
			{
				$ELB = "O(A) Pregoeiro(a) fez uma renegociação com a empresa arrematante e chegou ao valor final de R$ $ValorRenegociado";
			}
			else
			{
				$ELB = "A  empresa arrematante chegou ao valor final de R$ $ValorVencedor .";
			}
			
			if($LinhaLotes[6] == 1)
			{
				$PregaoEmAndamento = True;
			}
			
			if($LinhaLotes[6] == 5 and $MotivoFracasso != "")
			{
				$ELA = "O Lote $NumLote, foi fracassado pelo motivo: ".$MotivoFracasso.".";
				$ELB = "";
			}
			
			$pdf->MultiCell(160,5,$ELA,0, ($MotivoFracasso != "" ? "FJ" : "L") ,0);
			$pdf->ln(5);
			
			if($ELB != "")
			{
				$pdf->MultiCell(160,5,$ELB,0,"L",0);
				$pdf->ln(5);	
			}			
		}
	}

	if($P02 != "")
	{
		$pdf->MultiCell(160,5,$P02,0,"FJ",0);
		$pdf->ln(5);	
	}

	if($Recurso == "N")
	{
		$pdf->MultiCell(160,5,$E05,0,"FJ",0);
		$pdf->ln(5);
	}

	if($P03 != "")
	{
		$pdf->MultiCell(160,5,$P03,0,"FJ",0);
		$pdf->ln(5);	
	}

	if($P04 != "")
	{
		$pdf->MultiCell(160,5,$P04,0,"FJ",0);
		$pdf->ln(5);	
	}

	$pdf->MultiCell(160,5,$E06,0,"FJ",0);
	$pdf->ln(5);
	$pdf->MultiCell(160,5,$E07,0,"FJ",0);
	$pdf->ln(15);

}

$pdf->MultiCell(160,5,$E08,0,"L",0);
$pdf->ln(5);

$pdf->Cell(160,5,"____________________________________________________",0,1,"L",0);
$pdf->Cell(160,5,"Pregoeiro(a): $Pregoeiro",0,0,"L",0);
$pdf->ln(10);

for($itrL = 1; $itrL <= $CountMembros; ++ $itrL)
{
	$pdf->Cell(160,5,"____________________________________________________",0,1,"L",0);
	$pdf->Cell(160,5,"Apoio: ".$Membros["membro_".$itrL],0,0,"L",0);
	if($itrL == $CountMembros)
	{
		$pdf->ln(15);		
	}
	else
	{
		$pdf->ln(10);
	}
}

if($DemaisParticipantes != "")
{
	$pdf->Cell(160,5,"___________________________________________________________________________________________",0,1,"L",0);
	$pdf->Cell(160,5,"Demais Participantes: ",0,0,"L",0);
	$pdf->ln(15);
}

if($_SESSION['deserta'] == "N")
{
	$pdf->MultiCell(160,5,$E09,0,"L",0);
	$pdf->ln(5);

	$pdf->SetFillColor(220,220,220);

	$SqlFornecedores = "SELECT  		pf.apregfccgc, pf.apregfccpf, pf.npregfrazs, pf.npregfnomr, pf.apregfnurg, pf.npregforgu
							FROM 		sfpc.tbpregaopresencialfornecedor pf  
							WHERE 		pf.cpregasequ = $PregaoCod
									AND	pf.npregfnomr <> ''
									AND	pf.apregfnurg <> ''							
							ORDER BY 	pf.npregfrazs"; 		

	$ResultFornecedores = $db->query($SqlFornecedores);
	if( PEAR::isError($ResultFornecedores) )
	{
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlFornecedores");
	}

	while($LinhaFornecedores = $ResultFornecedores->fetchRow())
	{
		$Fornecedor 	= $LinhaFornecedores[2];
		
		$Representante 	= $LinhaFornecedores[3].", R.G.: ".$LinhaFornecedores[4]." - ".$LinhaFornecedores[5];
		
		$CnpjCpf			= ($LinhaFornecedores[1] == "" 
										?
										(substr($LinhaFornecedores[0], 0, 2).'.'.substr($LinhaFornecedores[0], 2, 3).'.'.substr($LinhaFornecedores[0], 5, 3).'/'.substr($LinhaFornecedores[0], 8, 4).'-'.substr($LinhaFornecedores[0], 12, 2)) 
										: 
										(substr($LinhaFornecedores[1], 0, 3).'.'.substr($LinhaFornecedores[1], 3, 3).'.'.substr($LinhaFornecedores[1], 6, 3).'-'.substr($LinhaFornecedores[1], 9, 2)));		
		
		$pdf->Cell(25,5,"Empresa: ",1,0,"L",1);
		$pdf->Cell(135,5,$Fornecedor,1,1,"L",0);
		$pdf->Cell(25,5,"CNPJ/CPF: ",1,0,"L",1);
		$pdf->Cell(135,5,$CnpjCpf,1,1,"L",0);		
		$pdf->Cell(25,5,"Representante",1,0,"L",1);
		$pdf->Cell(135,5,$Representante,1,1,"L",0);
		$pdf->Cell(25,5,"Assinatura: ",1,0,"L",1);	
		$pdf->Cell(135,5,"",1,1,"L",0);
		$pdf->ln(5);
	}
	
	
	$SqlFornecedoresB = "SELECT  		pf.apregfccgc, pf.apregfccpf, pf.npregfrazs, pf.npregforgu
							FROM 		sfpc.tbpregaopresencialfornecedor pf  
							WHERE 		pf.cpregasequ = $PregaoCod
									AND	pf.npregfnomr = ''
									AND	pf.apregfnurg = ''
							ORDER BY 	pf.npregfrazs"; 		

	$ResultFornecedoresB = $db->query($SqlFornecedoresB);
	if( PEAR::isError($ResultFornecedoresB) )
	{
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlFornecedoresB");
	}	
	
	$QuantidadeFornecedores = $ResultFornecedoresB->numRows();	
	
	if($QuantidadeFornecedores > 0)
	{
		$pdf->MultiCell(160,5,$E10,0,"L",0);
		$pdf->ln(5);

		$pdf->SetFillColor(220,220,220);

		while($LinhaFornecedoresB = $ResultFornecedoresB->fetchRow())
		{
			$Fornecedor 	= $LinhaFornecedoresB[2];

			$CnpjCpf			= ($LinhaFornecedoresB[1] == "" 
											?
											(substr($LinhaFornecedoresB[0], 0, 2).'.'.substr($LinhaFornecedoresB[0], 2, 3).'.'.substr($LinhaFornecedoresB[0], 5, 3).'/'.substr($LinhaFornecedoresB[0], 8, 4).'-'.substr($LinhaFornecedoresB[0], 12, 2)) 
											: 
											(substr($LinhaFornecedoresB[1], 0, 3).'.'.substr($LinhaFornecedoresB[1], 3, 3).'.'.substr($LinhaFornecedoresB[1], 6, 3).'-'.substr($LinhaFornecedoresB[1], 9, 2)));		

				
			$pdf->Cell(25,5,"Empresa: ",1,0,"L",1);
			$pdf->Cell(135,5,$Fornecedor,1,1,"L",0);
			$pdf->Cell(25,5,"CNPJ/CPF: ",1,0,"L",1);
			$pdf->Cell(135,5,$CnpjCpf,1,1,"L",0);			
			$pdf->ln(5);
			
		}
	}
	
}

$db->disconnect();
$pdf->Output();
?> 
