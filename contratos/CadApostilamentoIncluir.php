<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadApostilamentoIncluir.php
# Autor:    Eliakim Ramos | Edson Dionisio
# Data:     23/04/2020
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
#Alterado : Osmar Celestino
# Data: 29/03/2021
# Objetivo: CR CR #245212  Correção da  transformação da string e-mail pra Upper, manter como o cliente digitou.
#---------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor:    Marcello Albuquerque
# Data:     09/11/2021
# CR #255536
# -------------------------------------------------------------------------
# Autor:    Marcello Albuquerque
# Data:     09/11/2021
# CR #251686 
# -------------------------------------------------------------------------
# Autor:    João Madson Felix
# Data:     04/11/2022
# CR #274878
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassApostilamento.php";
require_once "./ApostilamentoGeral.php";


# Executa o controle de segurança	#
session_start();
Seguranca();

$ObjApostilamento = new ClassApostilamento();
$ObjApostilamentoGeral = new ApostilamentoGeral();
$idContrato = $_POST['idregistro'];
if (isset($idContrato)){
    $_SESSION['idregistro'] = $idContrato;
}else{
    $idContrato = $_SESSION['idregistro'];
}
$contrato_pesq = $ObjApostilamento->getContrato($idContrato);
$cdocpcsequ = $contrato_pesq[0]->cdocpcsequ;
$ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;

$codApostilamento = $ObjApostilamento->getUltimoCodApostilamentoGeral($cdocpcsequ);

session_start();
//var_dump($_SERVER['REQUEST_METHOD']);die;
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['dadosTabela']);
    unset( $_SESSION['fiscal_selecionado_incluir']);
}



if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Busca de dados do fornecedor
    $Botao = $_POST['Botao'];

    $DadosDocFiscaisFiscal = array();
    unset( $_SESSION['fiscal_selecionado_incluir']);

    if(!empty($_SESSION['fiscal_selecionado_incluir'])){
        $DDFF = $ObjApostilamentoGeral->getDocumentosFicaisEFical($cdocpcsequ);
        $i=0;
        foreach($DDFF as $k){
        foreach($_SESSION['fiscal_selecionado_incluir'] as $f){
                if($k->fiscalcpf == $f->fiscalcpf){
                    unset($_SESSION['fiscal_selecionado_incluir'][$i]);
                }else{
                    $fiscalselecionado[] = (object)  array(
                        'tipofiscal'      => $f->tipofiscal,
                        'fiscalnome'      => $f->fiscalnome,
                        'fiscalmatricula' => $f->fiscalmatricula,
                        'fiscalcpf'       => $f->fiscalcpf,
                        'fiscalemail'     => $f->fiscalemail,
                        'fiscaltel'       => $f->fiscaltel,
                        'docsequ'         =>  $f->docsequ,
                        'registro'         =>  $f->registro,
                        'ent'         =>  $f->ent,
                        'docsituacao'     => 'ATIVO',
                        'remover'         =>'N'
                    ); 
                }
                $i++;
        }
            $fiscalselecionado[] = (object)  array(
                                'tipofiscal'      => $k->tipofiscal,
                                'fiscalnome'      => $k->fiscalnome,
                                'fiscalmatricula' => $k->fiscalmatricula,
                                'fiscalcpf'       => $k->fiscalcpf,
                                'fiscalemail'     => $k->fiscalemail,
                                'fiscaltel'       => $k->fiscaltel,
                                'docsequ'         =>  $k->docsequ,
                                'registro'         =>  $k->registro,
                                'ent'         =>  $k->ent,
                                'docsituacao'     => 'ATIVO',
                                'remover'         =>'N'
                );
        }
        unset( $_SESSION['fiscal_selecionado_incluir']);
        $_SESSION['fiscal_selecionado_incluir'] = $fiscalselecionado;
        $DadosDocFiscaisFiscal = $_SESSION['fiscal_selecionado_incluir'];
    }else{
        $DDFF = $ObjApostilamentoGeral->getDocumentosFicaisEFical($cdocpcsequ);
        $i=0;
        foreach($DDFF as $k){
                $fiscalselecionado[] = (object)  array(
                                'tipofiscal'      => $k->tipofiscal,
                                'fiscalnome'      => $k->fiscalnome,
                                'fiscalmatricula' => $k->fiscalmatricula,
                                'fiscalcpf'       => $ObjApostilamentoGeral->MascarasCPFCNPJ($k->fiscalcpf),
                                'fiscalemail'     => $k->fiscalemail,
                                'fiscaltel'       => $k->fiscaltel,
                                'docsequ'         =>  $k->docsequ,
                                'registro'         =>  $k->registro,
                                'ent'         =>  $k->ent,
                                'docsituacao'     => 'ATIVO',
                                'remover'         =>'N'
                );
        }
        unset( $_SESSION['fiscal_selecionado_incluir']);
        $_SESSION['fiscal_selecionado_incluir'] = $fiscalselecionado;
        $DadosDocFiscaisFiscal = $_SESSION['fiscal_selecionado_incluir'];

        //var_dump($DadosDocFiscaisFiscal);
    }

    // Busca de dados do fornecedor
    if (!empty($_POST['seqScc'])) {
        $_SESSION['csolcosequ'] = $_POST['seqScc'];
        $csolcosequ = $_SESSION['csolcosequ'];
        $aforcrsequ = $_SESSION["fornsequ" . $csolcosequ];
        $corglicodi = $_SESSION["org" . $csolcosequ];
        $cctrpciden = $_SESSION["flagCPFPJ" . $csolcosequ];
    } else {
        $csolcosequ = $_SESSION['csolcosequ'];
        $aforcrsequ = $_SESSION["fornsequ" . $csolcosequ];
        $corglicodi = $_SESSION["org" . $csolcosequ];
        $cctrpciden = $_SESSION["flagCPFPJ" . $csolcosequ];
    }
    if (!empty($csolcosequ) && !empty($aforcrsequ)) {
        $_SESSION['dadosContratado']  = $ObjApostilamentoGeral->getFornecedorDados($aforcrsequ);
        $_SESSION['dadosObjOrgao']    = $ObjApostilamentoGeral->GetOrgaoEDescObj($csolcosequ);
        $dadosSalvar['csolcosequ'] = $csolcosequ;
        $dadosSalvar['aforcrsequ'] = $aforcrsequ;
    }

    $_SESSION['Botao'] = $_POST['Botao'];


    //Inicio da coleta dos dados selecionados em pesquisa via post  MADSON 
    if (!is_null($_POST['sccselec-' . $_POST['seqScc']])) {
        $_SESSION['origemScc']       = $ObjApostilamento->corrigeString($_POST['origselec-' . $_POST['seqScc']]);
        $_SESSION['numScc']          = $_POST['sccselec-' . $_POST['seqScc']];
        $_SESSION['CpfCnpj']         = $_POST['cpfselec-' . $_POST['seqScc']];
    }
    
    $dadosSalvar['origemScc'] = $_SESSION['origemScc'];
    $dadosSalvar['numScc']    = $_SESSION['numScc'];
    $dadosSalvar['CpfCnpj']   = $_SESSION['CpfCnpj'];

    // Este if se encarrega de adicionar os campos abaixo no template caso a função objeto que é obrigatorio seja valida
    if (!is_null($dadosSalvar['numScc'])) {
        $origemScc    = '<span id="origem">' . $dadosSalvar['origemScc'] . '</span>';
        $numScc       = '<input type="text" id="numeroscc" name="numeroscc" value="' . $dadosSalvar['numScc'] . '" readonly disabled="disabled">';
        $CpfCnpj      = $dadosSalvar['CpfCnpj'];
    } else {
        $origemScc    = '<span id="origem"></span>';
        $numScc       = '<input type="text" id="numeroscc" name="numeroscc" value="" readonly disabled="disabled">';
        $CpfCnpj      = "";
    }

    $_SESSION['dadosSalvar'] = $dadosSalvar;
    $_SESSION['documento_anexo_incluir'] = $DadosDocAnexo;
    $dadosAnexo = array();
    if (!empty($_SESSION['documento_anexo_incluir'])) {
        
        foreach ($_SESSION['documento_anexo_incluir'] as $doc) {
            $DadosDocAnexo[]  =  (object) array(
                'nomearquivo'       => $doc['nome_arquivo'],
                'arquivo'           => $doc['arquivo'],
                'sequdocumento'     => $doc['sequdoc'],
                'datacadasarquivo'  => $doc['data_inclusao'],
                'usermod'           => $doc['usermod'],
                'arquivo'           => $doc['arquivo'],
                'ativo'             => 'S'
            );
        }
        unset($_SESSION['documento_anexo_incluir']);
        $_SESSION['documento_anexo_incluir'] = $DadosDocAnexo;
        $dadosAnexo = $_SESSION['documento_anexo_incluir'];
    }
}

    $tipoapost = $_POST['tipo_apostilamento'];
    $dadosTiposApostilamentos = $ObjApostilamento->getTiposApostilamentos();
    //var_dump($dadosTiposApostilamentos);
    $dadosHtmlSelectTipoApostilamento = $ObjApostilamento->MontaSelectBoxTipoApostilamento($dadosTiposApostilamentos,$tipoapost);

    $CNPJ_CPF = $_POST['CNPJ_CPF'];

    if ($_POST['CnpjCpf'] != "") {
        if ($CNPJ_CPF == 2) {
            $CnpjCpf = str_replace('.', '', str_replace('-', '', $_POST['CnpjCpf']));
            
        } else {
            $CnpjCpf = str_replace('.', '', str_replace('-', '', str_replace('/', '', $_POST['CnpjCpf'])));
        
        }
    } else {
        $CnpjCpf = $_POST['CnpjCpf'];
    }

    $Mensagem = "Informe: ";

if ($Botao == "Verificar" || $_POST['CnpjCpf'] != "") {
    
	$Mens     = 0;
	
	if ($CNPJ_CPF == "") {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "A opção CNPJ ou CPF";
	} else {
        
		if ($CNPJ_CPF == 1) {
			$TipoDocumento = "Contratado";
		} else {
			$TipoDocumento = "Contratado";
		}
		
		if ($CnpjCpf == "") {
			$RazaoSocial = null;
			
			if ($Mens == 1) {
				$Mensagem.=", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadApostilamentoIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
		} else {
			if ($CNPJ_CPF == 1) {
               
				$valida_cnpj = valida_CNPJ($CnpjCpf);
                
				if ($valida_cnpj === false) {
					$RazaoSocial = null;
					
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadApostilamentoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}
			} else {
				$valida_cpf = valida_CPF($CnpjCpf);
				
				if ($valida_cpf === false) {
					$RazaoSocial = null;
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadApostilamentoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
				}
			}
		}
	
        
        if ($CNPJ_CPF == "") {
            $RazaoSocial = null;
            
            if ($Mens == 1) {
                $Mensagem.=", ";
            }
            
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "A opção CNPJ ou CPF";
        } else {
            if ($CNPJ_CPF == 1) {
                $TipoDocumento = "Contratado";
            } else {
                $TipoDocumento = "Contratado";
            }
            
            if (($CNPJ_CPF == 1 && $valida_cnpj === true) || ($CNPJ_CPF == 2 && $valida_cpf === true)) {
                
                # Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
                $db = Conexao();
                
                $sql  = "SELECT AFORCRSEQU, NFORCRRAZS, AFORCRCCGC, CCEPPOCODI, EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, AFORCRCDDD, AFORCRTELS FROM SFPC.TBFORNECEDORCREDENCIADO ";
                $sql .= " WHERE ";
                    
                    if ($CNPJ_CPF == 1) {
                        $sql .= " AFORCRCCGC = '$CnpjCpf' ";
                    } else {
                        $sql .= " AFORCRCCPF = '$CnpjCpf' ";
                    }

                $res  = $db->query($sql);
                //var_dump($res);die;
                if (db::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $rows = $res->numRows();
                    
                    if ($rows > 0) {
                        $linha = $res->fetchRow();
                        $RazaoSocial = $linha[1];
                        $_SESSION['razao_social'] = $RazaoSocial;
                        $codFornecedor = $linha[0];
                        $_SESSION['codigo_forn'] = $codFornecedor;
                        //var_dump($codFornecedor);
                        if(strlen($linha[2]) === 11){
                            $CnpjCpf_forn = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $linha[2]);
                        }else{
                            $CnpjCpf_forn = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $linha[2]);
                        }
                       // $CnpjCpf_forn = $linha[2];
                        $cep = $linha[3];
                        $logradouro = $linha[4];
                        $numero = $linha[5];
                        $compl = $linha[6];
                        $bairro = $linha[7];
                        $cidade = $linha[8];
                        $estado = $linha[9];
                        //$ddd = $linha[10];
                    } else {
                        if ($Mens == 1) {
                            $Mensagem.=", ";
                        }
                        
                        $Mens     = 1;
                        $Tipo     = 1;
                        $Mensagem = "Fornecedor Não Cadastrado";
                    }
                }
            }

        }
	}
}

//$teste = $_SESSION['dadosTabela'];
?>
<html>
<?php

//$dadosTipoCompra = $ObjApostilamento->ListTipoCompra();
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
<script language="javascript" type="">
        function TestaCPF(strCPF) {
            let Soma;
            let Resto;
            Soma = 0;
            strCPF = strCPF.replace(/[^\d]+/g,'');
           // console.log(strCPF);
            if (strCPF == "00000000000") return false;
            if (strCPF == "11111111111") return false;
            if (strCPF == "22222222222") return false;
            if (strCPF == "33333333333") return false;
            if (strCPF == "44444444444") return false;
            if (strCPF == "55555555555") return false;
            if (strCPF == "66666666666") return false;
            if (strCPF == "77777777777") return false;
            if (strCPF == "88888888888") return false;
            if (strCPF == "99999999999") return false;
                
            for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
            Resto = (Soma * 10) % 11;
            
                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;
            
            Soma = 0;
                for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
                Resto = (Soma * 10) % 11;
            
                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
                return true;
        }
        function enviar(valor){
            document.CadApostilamentoIncluir.submit();
            }
        function AbreJanela(url,largura,altura) {
            window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
        }
        function aviso(mensagem){
                 $("#tdmensagem").show();
                $('html, body').animate({scrollTop:0}, 'slow');
				 $(".mensagem-texto").html(mensagem);
        }
        function limpaMensagem(){
            $("#tdmensagem").hide();
            $("#tdmensagemM").hide();
        }
        function avisoModal(mensagem){
                 $("#tdmensagemM").show();
                $('html, body').animate({scrollTop:0}, 'slow');
				 $(".mensagem-textoM").html(mensagem);
        }
        function Submete(Destino){
            document.CadApostilamentoIncluir.Destino.value = Destino;
            document.CadApostilamentoIncluir.submit();
        }
        function retiraFornecedor(dado){
            $.post("postDadosApostilamentoGeral.php",{op:"ExcluirForneModal",info:dado}, function(data){
                    ObjJson = JSON.parse(data);
                    $(".dadosFornec").html(CriatableModal(ObjJson));
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
			 });
        }
        function CriatableModal(objJson){
                    tabelaHtml = '<table border="1" bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Identificador do Contratado';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Razão Social';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += '';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i]){
                            if(objJson[i].remover == 'N'){
                                tabelaHtml += '<tr>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<input type="hidden" name="codFornecedorModalPesquisa[]" value="'+objJson[i].aforcrsequ+'">';
                                tabelaHtml +=  (objJson[i].aforcrccpf == '')?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += objJson[i].nforcrrazs;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<button type="button" onclick="retiraFornecedor(\''+objJson[i].aforcrsequ+'\')"><img src="../midia/excluirfornec.png" alt="Excluir" /> </button>';
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                            }
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function CriatableView(objJson){
                    tabelaHtml = '<table border="1" bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Identificador do Contratado';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Razão Social';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i]){
                            if(objJson[i].remover == 'N'){
                                tabelaHtml += '<tr>';
                                tabelaHtml += '<td>';
                                tabelaHtml += '<input type="hidden" name="codFornecedorModalPesquisa[]" value="'+objJson[i].aforcrsequ+'">';
                                tabelaHtml +=  (objJson[i].aforcrccpf == '')?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
                                tabelaHtml += '</td>';
                                tabelaHtml += '<td>';
                                tabelaHtml += objJson[i].nforcrrazs;
                                tabelaHtml += '</td>';
                                tabelaHtml += '</tr>';
                            }
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        
        function CriaTabelaFiscal(objJson){
            tabelaHtml = '<table border="1" width="100%" bordercolor="#75ADE6" class="textonormal">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr> <td  class="titulo3" colspan="9"  align="center"  bgcolor="#75ADE6">RESULTADO DA PESQUISA</td></tr>';
                    tabelaHtml += '<tr style="background-color: #bfdaf2; text-align: center; font-weight: bold; color: #3165a5;">';
                    tabelaHtml += '<td>';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'TIPO FISCAL';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'NOME';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'MATRÍCULA';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'CPF';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'ENT. COMPET.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'REGISTRO OU INSC.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'E-MAIL';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'TEL.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i].cfiscdcpff){
                            tabelaHtml += '<tr>';
                            tabelaHtml += '<td>';
                            tabelaHtml += '<input type="radio" name="cpfFiscal" value="'+objJson[i].cfiscdcpff+'">';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdtipo != '')?objJson[i].nfiscdtipo:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdnmfs != '')?objJson[i].nfiscdnmfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdmtfs != '')?objJson[i].efiscdmtfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].cpfformatado != '')?objJson[i].cpfformatado.toUpperCase():'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdencp != '')?objJson[i].nfiscdencp:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  ((objJson[i].efiscdrgic != "") || objJson[i].efiscdrgic != null || objJson[i].efiscdrgic != "null") ? objJson[i].efiscdrgic : "";
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  ((objJson[i].nfiscdmlfs != "") || objJson[i].nfiscdmlfs != null || objJson[i].nfiscdmlfs != "null") ? objJson[i].nfiscdmlfs.toString() : "";
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdtlfs != '')?objJson[i].efiscdtlfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '</tr>';
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '<tfoot>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td colspan="9">';
                    tabelaHtml += '<input  type="button" name="excluir" id="btnExcluirModal" value="Excluir" style="float:right" title="Excluir" class="botao_excluir botao">';
                    tabelaHtml += '<input  type="button" name="alterar" id="btnAlterarModal" value="Alterar" style="float:right" title="Alterar" class="botao_Alterar botao">';
                    tabelaHtml += '<input  type="button" name="adicionarFiscal" id="btnAdicionarFiscalModal" value="Criar Novo Fiscal" style="float:right" title="Adicionar" class="botao_final botao">';
                    tabelaHtml += '<input  type="button" name="newselect" id="btnNewSelectModal" value="Selecionar" style="float:right;" onclick="SelecionarFiscalModal()" title="Selecionar" class="botao_New_Selecionar botao">';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</tfoot>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function CriaTabelaFiscalView(objJson){
            tabelaHtml = '';
            let situacao;
            var arrayAux = new Array();
                    for(i in objJson){
                        if(objJson[i].registro == null){
                            objJson[i].registro = "";
                        }
                        if(objJson[i].ent == null){
                            objJson[i].ent = "";
                        }
                        //console.log(objJson[i].registro);
                        if(objJson[i].fiscalcpf){
                             if(objJson[i].remover == "N"){ 
                                 if(arrayAux.indexOf(objJson[i].fiscalcpf) == -1){   
                                    tabelaHtml += '<tr>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml += '<input type="radio" name="fiscais" value="'+objJson[i].fiscalcpf+'-'+objJson[i].docsequ+'">';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].tipofiscal != '')?objJson[i].tipofiscal:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalnome != '')?objJson[i].fiscalnome:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalmatricula != '')?objJson[i].fiscalmatricula:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalcpf != '')?objJson[i].fiscalcpf:'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].registro != '' || objJson[i].registro != null) ? objJson[i].registro : '';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].ent != '' || objJson[i].ent != null) ? objJson[i].ent : '';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscalemail != '')?objJson[i].fiscalemail.toString():'';
                                    tabelaHtml += '</td>';
                                    tabelaHtml += '<td>';
                                    tabelaHtml +=  (objJson[i].fiscaltel != '')?objJson[i].fiscaltel:'';
                                    tabelaHtml += '</td>';
                                    situacao = (objJson[i].docsituacao != '') ? objJson[i].docsituacao : '';
                                    tabelaHtml += '</tr>';
                                 }
                             }
                        }
                        arrayAux.push(objJson[i].fiscalcpf);
                    }
                return tabelaHtml;
        }

        function SelecionarFiscalModal(){
            const Doc = "<?php echo $dadosContratos->seqdocumento;?>";
            const cpf = $("input[name='cpfFiscal']:checked").val();
                $.post("postDadosApostilamentoGeral.php",{op:"SelecFiscal",cpf:cpf,doc:Doc}, function(data){
                    ObjJson = JSON.parse(data);
                     $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson));
                     $("#cpffiscal").removeAttr('disabled');
                     $("#cpffiscal").val('');
                     $("input[name='tipofiscalr']").removeAttr('disabled');
                     $("#btnNewPesquisaModal").hide();
                     $("#btnPesquisaModal").show();
                     $(".dadosFiscal").html('');
                     $("#modal").hide();
			    });
        }

        function Subirarquivo(){
                window.top.frames['frameArquivo'].subirArquivo();
        }

        function verifica(value){

            var cnpj = document.getElementById("CNPJ_forn");
            var cpf = document.getElementById("CPF_forn");
            var CnpjCpfforn = document.getElementById("CnpjCpf_forn");

            cnpj.disabled = true;
            cpf.disabled = true;;
            CnpjCpfforn.disabled = true;

            var valor_retroativo = document.getElementById("valor_retroativo_apostilamento");
            var valor_apostilamento = document.getElementById("valor_apostilamento");


            // if(value == "REPACTUAÇÃO" || value == "REAJUSTE POR ÍNDICE"){
            if(value == "4" || value == "1"){
                valor_retroativo.disabled = false;
                valor_apostilamento.disabled = false;

                cnpj.disabled = true;
                cpf.disabled = true;
                CnpjCpfforn.disabled = true;

            seleciona_opcao(value);

            var x =  seleciona_opcao(value);

            //}else if(value == "ALTERAÇÃO DE FORNECEDOR"){
            }else if(value == "5"){

                seleciona_opcao(value);
                var cnpj = document.getElementById("CNPJ_forn");
                var cpf = document.getElementById("CPF_forn");
                var CnpjCpfforn = document.getElementById("CnpjCpf_forn");

                cnpj.disabled = false;
                cpf.disabled = false;
                CnpjCpfforn.disabled = false; 
            }else{

                seleciona_opcao(value);

                valor_retroativo.disabled = true;
                valor_apostilamento.disabled = true;
                var valor_retro_zerado = $("#valor_retroativo_apostilamento").val(0.00);
                var valor_apostilamento_zerado = $("#valor_apostilamento").val(0.00);

                cnpj.disabled = true;
                cpf.disabled = true;
                CnpjCpfforn.disabled = true;
            }
        };

        function limpaDados(){
            var nome = document.getElementById("gestorNome");
            var cpf = document.getElementById("gestorCPF");
            var matricula = document.getElementById("gestorMatricula");
            var email = document.getElementById("gestorEmail");
            var telefone = document.getElementById("gestorTelefone");

        }

        function seleciona_opcao(value){

            var valor_retroativo = document.getElementById("valor_retroativo_apostilamento");
            var valor_apostilamento = document.getElementById("valor_apostilamento");

            var nome = document.getElementById("gestorNome");
            var cpf = document.getElementById("gestorCPF");
            var matricula = document.getElementById("gestorMatricula");
            var email = document.getElementById("gestorEmail");
            var telefone = document.getElementById("gestorTelefone");
            var removefiscal = document.getElementById("removefiscal");
            var manterFiscal = document.getElementById("manterfiscal");
            var cnpjF = document.getElementById("CNPJ_forn");
            var cpfF = document.getElementById("CPF_forn");
            var CnpjCpfforn = document.getElementById("CnpjCpf_forn");
            var valorRetroativoApostilamento = document.getElementById("valor_retroativo_apostilamento");
            var valorApostilamento = document.getElementById("valor_apostilamento");
            var showFornecedor = document.getElementById("linhasresForn");



            // console.log(value);
            if(value == "1" || value == "4"){

                limpaDados();

                nome.disabled = true;
                cpf.disabled = true;
                matricula.disabled = true;
                email.disabled = true;
                telefone.disabled = true;
                removefiscal.disabled = true;
                manterFiscal.disabled = true;
                showFornecedor.style.display = 'none';

                valor_retroativo.disabled = false;
                valor_apostilamento.disabled = false;
                cnpjF.disabled = true;
                cpfF.disabled = true;
                CnpjCpfforn.disabled = true;
                valorRetroativoApostilamento.disabled = false;
                valorApostilamento.disabled = false;
            }else if(value == "5"){
                limpaDados();

                nome.disabled = true;
                cpf.disabled = true;
                matricula.disabled = true;
                email.disabled = true;
                telefone.disabled = true;
                removefiscal.disabled = true;
                manterFiscal.disabled = true;
                showFornecedor.style.display = 'contents';
                cnpjF.disabled = false;
                cpfF.disabled = false;
                CnpjCpfforn.disabled = false;
                valor_retroativo.disabled = true;
                valor_apostilamento.disabled = true;
            }else if(value == "" || value == null ){

                limpaDados();

                nome.disabled = true;
                cpf.disabled = true;
                matricula.disabled = true;
                email.disabled = true;
                telefone.disabled = true;
                removefiscal.disabled = true;
                manterFiscal.disabled = true;
                showFornecedor.style.display = 'none';
                cnpjF.disabled = true;
                cpfF.disabled = true;
                CnpjCpfforn.disabled = true;
                valor_retroativo.disabled = true;
                valor_apostilamento.disabled = true;
            }else if(value == "2" || value == "3"){

                limpaDados();

                nome.disabled = false;
                cpf.disabled = false;
                matricula.disabled = false;
                email.disabled = false;
                telefone.disabled = false;
                removefiscal.disabled = false;
                manterFiscal.disabled = false;
                showFornecedor.style.display = 'none';

                valor_retroativo.disabled = true;
                valor_apostilamento.disabled = true;
            }
        }

        $(document).ready(function() {
            $('#CPF_forn').live('click',function(){
                //$(".mostracnpj").hide();
                //$(".mostracpf").show();
                $('#CnpjCpf_forn').mask('999.999.999-99');
            });
            $('#CNPJ_forn').live('click',function(){
                //$(".mostracnpj").show();
                //$(".mostracpf").hide();
                $('#CnpjCpf_forn').mask('99.999.999/9999-99');
            });

            var radio_cnpj_cpf = $("input[name='CNPJ_CPF']:checked").val();
            
            if(radio_cnpj_cpf == 1){
                $('#CnpjCpf_forn').mask('99.999.999/9999-99');
            }else{
                $('#CnpjCpf_forn').mask('999.999.999-99');
            }
                
            $('#numcontrato').mask('9999.9999/9999');
            $('.data').mask('99/99/9999');
            $('.cnpj').mask('99.999.999/9999-99');
            $('.CPF').mask('999.999.999-99');
            // $('.telefone').mask('(99)99999-9999');

            $("input.telefone")
            .mask("(99) 9999-9999?9")
            .focusout(function (event) {  
                var target, phone, element;  
                target = (event.currentTarget) ? event.currentTarget : event.srcElement;  
                phone = target.value.replace(/\D/g, '');
                element = $(target);  
                element.unmask();  
                if(phone.length > 10) {  
                    console.log(phone.length);
                    element.mask("(99) 99999-999?9");  
                } else {  
                    console.log(phone.length);
                    element.mask("(99) 9999-9999?9");  
                }  
            });

            $("#radio-tipofiscal-interno").live('click', function(){
                $(".mostramatricula").show();
            });
            
         /*   $("#radio-tipofiscal-externo").live('click', function(){
                console.log("teste3");
                $(".mostramatricula").hide();
            });
            */
            if($("#addFornecedor").is(':visible')){
                $.post("postDadosApostilamentoGeral.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
                });
            }

            $("#gestorCPF").on("blur",function(){
                if(!TestaCPF($("#gestorCPF").val())){
                    aviso("Informe: Um CPF válido para o gestor.");
                }
            });

            $("#btnvoltar").on('click', function(){
                window.history.back();
            });

            $(".btn-pesquisa-scc").on('click', function(){
                $.post("postDadosApostilamentoGeral.php",{op:"modalSccPesquisa"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
                    $('#modalNScc').mask('9999.9999/9999');
                    //Montagem da data inicial e final, sugestão de pesquisa para tres meses
                    var hoje = new Date(); 
                    var mesRegular = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
                    diaComZero = hoje.getDate() <= 9 ? '0' + hoje.getDate() : hoje.getDate();
                    var mostrar = diaComZero + '/' + mesRegular[hoje.getMonth()] + '/' + hoje.getFullYear();
                    $('#DataFimPCS').val(mostrar);
                    hoje.setMonth(hoje.getMonth() -3);
                    diaComZero = hoje.getDate() <= 9 ? '0' + hoje.getDate() : hoje.getDate();
                    mostrar = diaComZero + '/' + mesRegular[hoje.getMonth()] + '/' + hoje.getFullYear();
                    $('#DataIniPCS').val(mostrar);
                    });
            });

            $("#manterfiscal").on('click', function(){
                $.post("postDadosApostilamentoGeral.php",{op:"modalFiscal"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });

            $("#btnAdicionarFiscalModal").live("click",function(){
                $.post("postDadosApostilamentoGeral.php",{op:"ModalInserirFiscal"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });

            $("#cpffiscal").live("blur",function(){
                    if(!TestaCPF($("#cpffiscal").val())){
                        avisoModal("Informe: Um CPF válido!");
                        return false;
                    }else{
                        limpaMensagem();
                    }
            });

           $("#btnSalvarModal").live("click",function(){

                if(!TestaCPF($("#cpffiscal").val())){
                        avisoModal("Informe: Um CPF válido!");
                        return false;
                    }else{
                        limpaMensagem();
                    }
                var formulario = {
                    'op' :$("#op").val(),
                    'tipofiscalr' : ($("#radio-tipofiscal-interno").prop("checked"))?$("#radio-tipofiscal-interno").val():$("#radio-tipofiscal-externo").val(),
                    'nomefiscal': $("#nomefiscal").val(),
                    'matfiscal'   : $("#matfiscal").val(),
                    'cpffiscal'    : $("#cpffiscal").val(),
                    'entidadefiscal': $("#entidadefiscal").val(),
                    'RegInsfiscal': $("#RegInsfiscal").val(),
                    'emailfiscal': $("#emailfiscal").val(),
                    'telfiscal': $("#telfiscal").val(),
                };
                $.post("postDadosApostilamentoGeral.php",formulario,function(data){
                    ObjJson = JSON.parse(data);
                      if(!ObjJson.Sucess){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                      }else{
                        $("#modal").hide();
                        $(".dadosFiscal").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#btnPesquisaModal").hide();
                      }
                });
                return false;
            });

            $("#removefiscal").live('click',function(){
                const fiscalselec = $("input[name='fiscais']:checked").val();
                $.post("postDadosApostilamentoGeral.php",{op:"RemoveFiscal",marcador:fiscalselec},function(data){
                     ObjJson = JSON.parse(data);
                     $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson));
                });
            });
            $("#formAltFiscal").live("submit",function(){
                var formulario = $("#formAltFiscal").serialize();
                $.post("postDadosApostilamentoGeral.php",$("#formAltFiscal").serialize(),function(data){
                    ObjJson = JSON.parse(data);
                      if(!ObjJson.Sucess){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                      }else{
                        $("#modal").hide();
                        $(".dadosFiscal").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson.dados));
                        $("#btnPesquisaModal").hide();
                      }
                });
                return false;
            });
            $("#cnpj").live('focus', function(){
                $('#cnpj').mask('99.999.999/9999-99');
            });
            $("#cpffiscal").live('focus', function(){
                $('#cpffiscal').mask('999.999.999-99');
            });
            $("#btnPesquisaModal").live("click",function(){
                $('#LoadPesqFiscal').show();
                const tipo = $("input[name='tipofiscalr']:checked").val();
                $.post("postDadosApostilamentoGeral.php",{op:"Fiscal",cpf:$("#cpffiscal").val(),tipo:tipo}, function(data){
                    ObjJson = JSON.parse(data);
                    console.log(ObjJson.dados);
                    if(ObjJson.status){
                        $(".dadosFiscal").html(CriaTabelaFiscal(ObjJson.dados));
                        $("#cpffiscal").attr('disabled','disabled');
                        $("input[name='tipofiscalr']").attr('disabled','disabled');
                        $("#btnNewPesquisaModal").show();
                        //$("#mostrartbfiscais").show();
                        $("#btnPesquisaModal").hide();
                        $("#tdmensagemM").hide();
                        $('#LoadPesqFiscal').hide();
                    }else if(!ObjJson.status){
                        $("#tdmensagemM").show();
					    $(".mensagem-textoM").html(ObjJson.msm);
                        $('#LoadPesqFiscal').hide();
                    }
			    });
            });
            $("#btnNewPesquisaModal").live('click', function(){
                     $("#cpffiscal").removeAttr('disabled');
                     $("#cpffiscal").val('');
                     $("input[name='tipofiscalr']").removeAttr('disabled');
                     $("#btnNewPesquisaModal").hide();
                     $("#btnPesquisaModal").show();
                     $(".dadosFiscal").html('');
            });
            
            $("#btnPesquisaModalSCC").live('click', function(){
                $.post("./postDadosApostilamentoGeral.php",
                        {
                            op               : "PesquisaModalScc",
                            numeroScc        : $("#modalNScc").val(),
                            CodTipoCompra    : $("#modal-origem").val(),
                            NumContrato      : $("#numcontrato").val(),
                            dataIni          : $("#DataIniPCS").val(),
                            dataFim          : $("#DataFimPCS").val()
                        },
                    function(data){
                        $("#selectDivModal").html(data);
                    });
            });
            $("#btnExcluirModal").live("click", function(){
                        const cpfFiscal = $("input[name='cpfFiscal']:checked").val();
                        const tipofiscal = $("input[name='tipofiscalr']:checked").val();
                        const op           = "excluirFiscal";
                        $.post("postDadosApostilamentoGeral.php", {op:op,cpf:cpfFiscal,tipo:tipofiscal}, function(data){
                                    ObjJson = JSON.parse(data);
                                if(!ObjJson.Sucess){
                                    $("#tdmensagemM").show();
                                    $(".mensagem-textoM").html(ObjJson.msm);
                                }else{
                                    $("#tdmensagemM").show();
                                    $(".error").css("color","#00ff08");
                                    $(".error").html("Sucesso");
                                    $(".mensagem-textoM").html(ObjJson.msm);
                                    $(".dadosFiscal").html(CriaTabelaFiscal(ObjJson.dados));
                                }
                        });
            });
            
            $("#btnAlterarModal").live("click",function(){
                const docanexselec = $("input[name='cpfFiscal']:checked").val();
                if(docanexselec != undefined){
                    $.post("postDadosApostilamentoGeral.php",{op:"ModalAlterarFiscal",marcador:docanexselec},function(data){
                        $(".modal-content").html(data);
                        $("#modal").show();

                        if($("#radio-tipofiscal-interno").prop("checked")){
                            $(".mostramatricula").show();
                        }
                        if($("#radio-tipofiscal-externo").prop("checked")){
                            $(".mostramatricula").hide();
                        }
                    });
                }else{
                    avisoModal("Selecione fiscal do contrato.");
                    $('div, .modal-body').animate({scrollTop:0}, 'slow');
                }
            });

            $("#adicionarFornecedorButton").on('click', function(){
                $.post("postDadosApostilamentoGeral.php",{op:"ModalFornecedorCred"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });

            //fornecedor
            $('#radio-cpf').live('click',function(){
                $(".mostracnpj").hide();
                $(".mostracpf").show();
                $('#cpf').mask('999.999.999-99');
            });
            $('#radio-cnpj').live('click',function(){
                $(".mostracnpj").show();
                $(".mostracpf").hide();
            });
            $("#btnAdicionarModal").live('click',function(){
                $.post("postDadosApostilamentoGeral.php",{op:"Fornecerdor2",cpf:$("#cpf").val(),cnpj:$("#cnpj").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    if(ObjJson.status == false){
                        alert(ObjJson.msm);
                    }else{
                        $(".dadosFornec").html(CriatableModal(ObjJson));
                        $("#shownewfornecedores").html(CriatableView(ObjJson));
                        $("#modal").hide();
                    }
                });
            });

            $("#btn-fecha-modal").live('click', function(){
                $("#modal").hide();
            });
            $(".botao_fechar").live('click',function(){
                $("#modal").hide();
            });
            $(".botao_fechar_fiscal").live('click',function(){
                $("#modal").hide();
            });
            $("#btn-fecha-modal-fiscal").live('click',function(){
                $("#modal").hide();
            });           
            $("#habilita_gestor0").on('click', function(){
                $("#addFornecedor").show();
            });
            $("#habilita_gestor1").on('click', function(){
                $("#addFornecedor").hide();
            });
            
            $("#addFornecedores").on('click', function(){
                $.post("postDadosApostilamentoGeral.php",{op:"ModalFornecedorCred"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
                    $.post("postDadosApostilamentoGeral.php",{op:"ExibeFornecedorExtra",idregistro:$("input:[name=idregistro]").val()}, function(data){
                        ObjJson = JSON.parse(data);
                        $(".dadosFornec").html(CriatableModal(ObjJson));
			        });
			    });
            });
            //criei essa variavel valua para quando a tela for atualizada  não perder o valor selecionado
            var selectHtml = '<?php echo $dadosHtmlSelectTipoApostilamento;?>';
            $("#tipo_apostilamento").html(selectHtml);
    
            $("#file").on('change', function(){
                var file = $("#file").val();
                $.post("postDadosApostilamentoGeral.php",{op:"InsereArquivo", arquivo:file}, function(data){
                });
            });

            $('#btnIncluirAnexo').live("click",()=>{
                $('#loadArquivo').show();
            })
            $("#btnRemoveAnexo").live("click",function(){
                const docanexselec = $("input[name='docanex']:checked").val();
                $.post("postDadosApostilamento.php",{op:"RemoveDocAnex",marcador:docanexselec},function(data){
                     $("#FootDOcFiscal").html(data);
                });
            });
            $("#salvarApostilamento").on('click', function(){
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').show();
               // const habilitaGestos = $("input[name='habilita_gestor']:checked").val();

                var valida_arquivo = window.top.frames['frameArquivo'].validaAnexo();
                //console.log(valida_arquivo);
                if(!valida_arquivo){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("Informe: Documentos anexos. Devem ser anexados o apostilamento assinado e a cópia do empenho.");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                    $('#tdload').hide();
                    return false;
                }

                if($("#gestorCPF").val() != ""){
                    if(!TestaCPF($("#gestorCPF").val())){
                        aviso("Informe: Um CPF válido para o gestor.");
                        $("#salvarApostilamento").prop("disabled", false);
                        $('#tdload').hide();
                        return false;
                    }
                }

                $.post("postDadosApostilamento.php", $("#formContratoIncluir").serialize(), function(data){ 

                    const response = JSON.parse(data);
                    if(!response.status){
                        $('#tdload').hide(); 
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-texto").html(response.msm);
                        $(".error").html("Erro!");
                        $("#tdmensagem").show();
                        $("#salvarApostilamento").prop("disabled", false);
                    }else{
                        $('#tdload').hide();
                        $('html, body').animate({scrollTop:0}, 'slow');
                        $(".mensagem-texto").html(response.msm);
                        $(".error").html("Atenção!");
                        $(".error").css("color","#007fff");
                        $("#tdmensagem").show();
                        setTimeout(function(){ 
                            window.location.href = "./CadApostilamentoPesquisar.php";
                        }, 2000);
                    }
                });
            });
            seleciona_opcao($("#tipo_apostilamento").val());
        });

        <?php MenuAcesso(); ?>
       
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time(); ?>">
<style>
    #tabelaficais thead tr td {
        align-items: center;
        white-space: nowrap;
        -webkit-user-modify: read-write-plaintext-only;
    }

    #tabelaficais tfoot tr td {
        align-items: center;
        white-space: nowrap;
    }

    #tabelaficais tfoot tr.FootFiscaisDoc {
        align-items: center;
        white-space: nowrap;
        text-align: center;
        background-color: #bfdaf2;
    }

    .msg {
        text-align: center;
        font-size: larger;
        font-weight: 600;
        color: #75ade6;
    }

    .input {
        font-size: 10.6667px;
    }
</style>

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">
        Init();
    </script>
    <form action="CadApostilamentoIncluir.php" method="post" id="formContratoIncluir" name="CadApostilamentoIncluir">
        <input type="hidden" name="op" value="incluirApostilamento">
        <input type="hidden" name="idregistro" value="<?php echo $idContrato; ?>">
        <br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a> > Contratos > Apostilamento > Incluir
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
                <td width="100"></td>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                        <tr>
                            <td class="textonormal" border="3px" bordercolor="#75ADE6">

                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="white" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="left">
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <!-- <td colspan="4"> -->
                                                    <table class="textonormal" id="scc_material" summary="" bordercolor="#75ADE6" style="border: 1px solid #75ade6; border-radius: 4px;" width="100%">
                                                        <thead>
                                                            <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>INCLUIR APOSTILAMENTO</b></td>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Contrato/Ano
                                                                <td bgcolor="White">
                                                                    <?php echo $ectrpcnumf; ?>
                                                                </td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Apostilamento
                                            <td bgcolor="White">
                                                <?php echo $codApostilamento; ?>
                                            </td>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td bgcolor="#DCEDF7">Tipo de Apostilamento*</td>
                                        <td class="inputs">
                                            <select class="selectContrato" id="tipo_apostilamento" onchange="verifica(this.value)" name="tipo_apostilamento" size="1">
                                                <option></option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="50px">Valor retroativo Apostilamento</td>
                                        <td style="font-size: 10.6667px;" bgcolor="White" class="textonormal">
                                            <input type="text" style="font-size: 10.6667px;" class = "dinheiro4casas"  id="valor_retroativo_apostilamento" name="valor_retroativo_apostilamento" disabled>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="50px">Valor do Apostilamento</td>
                                        <td style="font-size: 10.6667px;" class="inputs">
                                            <input style="font-size: 10.6667px;" type="text" class = "dinheiro4casas" id="valor_apostilamento" name="valor_apostilamento"  disabled>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Data do Apostilamento*</td>
                                        <td style="font-size: 10.6667px;" class="inputs">
                                            <input id="dataPublicacaoDom" style="font-size: 10.6667px;"  type="text" name="dataPublicacaoDom" class="data" maxlength="10" size="12" title="" value="<?php echo !empty($_POST['dataPublicacaoDom'])? $_POST['dataPublicacaoDom']:'';?>">
                                            <a style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadApostilamentoIncluir&amp;Campo=dataPublicacaoDom','Calendario',220,170,1,0)">
                                                <img src="../midia/calendario.gif" border="0" alt="">
                                            </a>
                                        </td>
                                    </tr>

                                    <tr id="linhasForn">
                                            <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Contratado* 
                                            
                                            <td class="textonormal" width="60px">
                                                <input type="radio" name="CNPJ_CPF" id="CNPJ_forn" value="1" <?php if( $CNPJ_CPF == 1 ){ echo "checked"; }?>>CNPJ*
                                                <input type="radio" name="CNPJ_CPF" id="CPF_forn" value="2" <?php if( $CNPJ_CPF == 2 ){ echo "checked"; }?>>CPF*
                                                <input class="textonormal" type="text" name="CnpjCpf" id="CnpjCpf_forn" size="18" style="font-size: 10.6667px;" value="<?php echo !empty($CnpjCpf) ? $CnpjCpf : ''; ?>">    
                                                
                                                <a href="javascript:enviar('Verificar');"><img src="../midia/lupa.gif" border="0"></a>
                                            </td>
                                        
                                            </tr>

                                         <tr id="linhasresForn">
                                            <td bgcolor="#DCEDF7" class="textonormal"  width="225px">
                                            Fornecedor*
                                            <td class="textonormal">
                                                <table id="_gridContratadoNovo">
                                                    <tbody><tr><td class="labels">
                                                        <span id="_panelLblCpfCnpj">
                                                        <label for="" style=";" class="textonormal" ><?php echo (strlen($CnpjCpf_forn) == 18) ? "CNPJ do Contratado :" : "CPF do Contratado :"; ?></label></span></td><td class="textonormal" colspan="3"><div id="_panelInputCpfCnpj" name="_panelInputCpfCnpj"><?php if(!is_null($CnpjCpf_forn)){  echo $CnpjCpf_forn; } ?><label>
                                                            </label></div></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Razão Social :</label></td><td class="textonormal" colspan="3" ><div id="_panelGroupRazao"><span id="_razaoSocialfornecedor"  style="font-size: 10.6667px;" name="razao"><?php if(!is_null($RazaoSocial)){ echo "$RazaoSocial"; } ?></span></div></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Logradouro :</label></td><td class="textonormal" colspan="3"><span id="_logradourofornecedor" style="font-size: 10.6667px;"><?php if(!is_null($logradouro)){  echo "$logradouro"; } ?></span></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Número :</label></td><td class="textonormal" colspan="3"><span id="_numerofornecedor" style="font-size: 10.6667px;"><?php if(!is_null($numero)){  echo "$numero"; } ?></span></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Complemento :</label></td><td class="textonormal"><span id="_complementoLogradourofornecedor" style="font-size: 10.6667px;"><?php if(!is_null($compl)){  echo "$compl"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">Bairro:</label></td><td class="textonormal"><span id="_bairrofornecedor"><?php if(!is_null($bairro)){  echo "$bairro"; } ?></span></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Cidade :</label></td><td class="textonormal"><span id="_cidadefornecedor" style="font-size: 10.6667px;"><?php if(!is_null($cidade)){  echo "$cidade"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">UF:</label></td><td class="textonormal"><span id="_estadofornecedor"><?php if(!is_null($estado)){  echo "$estado"; } ?></span></td></tr>
                                                            </tbody></table>
                                            </td>
                                        </td>
                                    </tr>



            <tr>
                <thead bgcolor="#bfdaf2">
                    <tr>
                        <th class="titulo3" colspan="3" <?php echo $bloqueiacampo ? 'disabled="disabled"' : ''; ?> scope="colgroup">GESTOR (Só preencher se houver alteração)</th>
                    </tr>
                </thead>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nome
                <td style="font-size: 10.6667px;" bgcolor="White" class="inputs">
                    <input type="text" id="gestorNome" name="gestorNome" maxlength="120" style="width:315px; font-size: 10.6667px;" disabled>
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Matrícula
                <td style="font-size: 10.6667px;" bgcolor="White" class="inputs">
                    <input type="text" name="gestorMatricula" style="font-size: 10.6667px;" id="gestorMatricula" maxlength="20" size="10" disabled>
                </td>
                </td>
            </tr>

            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">CPF
                <td style="font-size: 10.6667px;" bgcolor="White" class="inputs">
                    <input class="CPF" type="text" name="gestorCPF" id="gestorCPF" size="10" style="font-size: 10.6667px;"  disabled>
                </td>
                </td>
            </tr>

            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">E-mail
                <td style="font-size: 10.6667px;" bgcolor="White" class="inputs">
                    <input type="text" id="gestorEmail" name="gestorEmail" maxlength="60" style="font-size: 10.6667px; width:173px; text-transform: none;" disabled>
                </td>
                </td>
            </tr>

            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Telefone(s)
                <td style="font-size: 10.6667px;"  bgcolor="White" class="inputs">
                    <input style="font-size: 10.6667px;" class="telefone" type="text" name="gestorTelefone" id="gestorTelefone" size="13" disabled>
                </td>
                </td>
            </tr>
            <tr>
                <thead bgcolor="#bfdaf2">
                    <tr>
                        <th class="titulo3" colspan="3" <?php echo $bloqueiacampo ? 'disabled="disabled"' : ''; ?> scope="colgroup">FISCAL(IS)* (Só preencher se houver alteração)</th>
                    </tr>
                </thead>
            </tr>
            <tr>
                <!-- Eliakim Ramos 05032019 -->
                <td colspan="3" >
                    <table style="width:100%; border:1px solid #bfdaf2;"  id="tabelaficais" class="textonormal">
                            <tr bgcolor="#DCEDF7" style="font-weight: bold;">
                                <td colspan="1"></td>
                                <td colspan="1">Tipo Fiscal</td>
                                <td colspan="1">Nome</td>
                                <td colspan="1">Matrícula</td>
                                <td colspan="1">CPF</td>
                                <td colspan="1">Ent. Compet.</td>
                                <td colspan="1"> Registro ou INSC.</td>
                                <td colspan="1">E-MAIL</td>
                                <td colspan="1">Telefone.</td>
                            </tr>
                        <tbody id="mostrartbfiscais">
                        
                            <?php 
                                $auxAnt = array();
                                if(!empty($DadosDocFiscaisFiscal)){
                                    foreach($DadosDocFiscaisFiscal as $fiscal){ 
                                        $situacao = $fiscal->docsituacao; 
                                        
                                        if( strtoupper($fiscal->remover) == "N"){
                                                if(!in_array($fiscal->fiscalcpf,$auxAnt)){
                            ?>
                                        <tr>
                                            <td> 
                                                <input type="radio" name="fiscais" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> value="<?php echo $fiscal->fiscalcpf.'-'.$fiscal->docsequ;?>">
                                            </td>
                                            <td><?php echo $fiscal->tipofiscal;?></td>
                                            <td><?php echo $fiscal->fiscalnome;?></td>
                                            <td><?php echo $fiscal->fiscalmatricula;?></td>
                                            <td><?php echo $fiscal->fiscalcpf;?></td>
                                            <td><?php echo (!empty($fiscal->ent) || $fiscal->ent != null) ? $fiscal->ent : ""; ?></td>
                                            <td><?php echo (!empty($fiscal->registro) || $fiscal->registro != null) ? $fiscal->registro : ""; ?></td>
                                            <td><?php echo $fiscal->fiscalemail;?></td>
                                            <!-- <td><?php echo $fiscal->fiscaltipo;?></td> -->
                                            <td><?php echo $fiscal->fiscaltel;?></td>
                                        </tr>
                            <?php          
                                                } 
                                            }
                                            $auxAnt[] = $fiscal->fiscalcpf;
                                        }
                                    }
                            ?> 
                        </tbody>
                        <tfoot>
                            <tr>
                                <td  colspan="9" style="itens-align:right;">
                                <?php if( !$bloqueiacampo){ ?>
                                        <button class="botao" type="button" id="removefiscal" style="float: right;">Remover Fiscal</button>
                                        <button class="botao" type="button" id="manterfiscal" style="float: right;">Manter Fiscal</button>                                                                                            
                                <?php } ?>
                                </td>
                            </tr>
                            <tr>
                            
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr>
                <thead class="titulo3" bgcolor="#bfdaf2">
                    <tr>
                        <th colspan="3" scope="colgroup">ANEXAR DOCUMENTOS</th>
                    </tr>
                </thead>
            </tr>
            <tr>
                <td colspan="3">
                    <table id="tabelaficais" bgcolor="#bfdaf2" class="textonormal" width="100%">
                        <tbody>
                            <tr>
                                <td bgcolor="#DCEDF7">Anexação de Documentos</td>
                                <td colspan="1" style="border:none">
                                    <iframe src="formupload.php" id="frameArquivo" height="39" width="520" name="frameArquivo" frameborder="0"></iframe>
                                </td>
                            </tr>
                            <!-- Inicio upload carregando  -->
                            <div class="load" id="loadArquivo" style="display: none;"> 
                                <div class="load-content" >
                                <img src="../midia/loading.gif" alt="Carregando">
                                <spam>Carregando...</spam>
                                </div>
                            </div>    
                            <!-- Fim upload carregando  --> 
                        </tbody>
                        <tfoot id="FootDOcFiscal">
                            <tr class="FootFiscaisDoc">
                                <td></td>
                                <td colspan="4">ARQUIVO</td>
                                <td colspan="4">DATA DA INCLUSÃO</td>
                            </tr>
                            <?php
                            if (!empty($dadosAnexo)) {
                                foreach ($dadosAnexo as $anexo) { ?>
                                    <tr bgcolor="#ffffff">
                                        <td><input type="radio" name="docanex" value="<?php echo $anexo->sequdocumento . '*' . $anexo->nomearquivo; ?>"></td>
                                        <td colspan="4"> <?php echo $anexo->nomearquivo; ?></td>
                                        <td colspan="4"> <?php echo $anexo->datacadasarquivo; ?></td>
                                    </tr>
                            <?php }
                            } else {
                                echo ' <tr bgcolor="#ffffff">';
                                echo ' <td colspan="8" bgcolor="#ffffff">Nenhum documento informado</td>';
                                echo ' </tr>';
                            }
                            ?>
                            <tr bgcolor="#ffffff">
                                <td colspan="8" align="center">
                                    <button type="button" class="botao" id="btnIncluirAnexo" onclick="Subirarquivo()">Incluir Documento</button>
                                    <button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    </br>
                </td>
            </tr>
            <tr>
                <td colspan="4" align="right">
                    <button type="button" name="salvarApostilamento" class="botao" id="salvarApostilamento">Salvar</button>
                    <input type="button" name="btnVoltar" title="Voltar" value="Voltar" class="botao" id="btnvoltar">
                </td>
            </tr>
            <input type="hidden" id="Destino" name="Destino">
            <!-- </td> -->
                <!-- </tr> -->
            <!-- </table> -->
        </table>
    </form>
    <div class="modal" id="modal">
        <div class="modal-content" style="min-height: 105px;width: 1100px;">

        </div>
    </div>
    <!-- Fim Modal -->
</body>

</html>