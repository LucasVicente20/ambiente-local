<?php

/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REL-COD-20160630-0940
 *
 *
 * HISTORICO DE ALTERAÇÔES
 * -----------------------------------------------------------------------
 * Portal da DGCO
 * Programa: funcoesGui.php
 * Data: 20/10/2011
 * Objetivo: funções para renderização das páginas
 * Autor: Ariston Cordeiro
 * -----------------------------------------------------------------------
 * Alterado: adicionar o retorno do objeto na função enviaEmail
 * Data: 14/11/2014
 * -----------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     04/07/2016
 * Objetivo: Requisito 136739: Cartilhas, Guias e Manuais - Nova funcionalidade internet e intranet (#446)
 * -----------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     24/04/2018
 * Objetivo: Requisito 181348: Consulta de Sócio fornecedor (#540)
 * -----------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     09/07/2018
 * Objetivo: Tarefa Redmine 198633
 * -----------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     20/05/2019
 * Objetivo: Tarefa Redmine 217105
 * -----------------------------------------------------------------------
 * Alterado: João Madosn
 * Data:     09/03/2021
 * Objetivo: Tarefa Redmine #244818
 * -----------------------------------------------------------------------
 * Alterado: João Madosn
 * Data:     22/03/2021
 * Objetivo: Tarefa Redmine  #245334
 * -----------------------------------------------------------------------
 */

// Setar o navegador para encoding = UTF-8
header("Content-Type: text/html; charset=UTF-8", true);

function retornaCabecalho() {
    $db = Conexao();

    $sql = "SELECT EPARGEEMPR, EPARGEORG1, EPARGEORG2, EPARGESETR, EPARGESIST FROM SFPC.TBPARAMETROSGERAIS";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $result);
    }

    $Linha = $result->fetchRow();
    $empresa     = $Linha[0];
    $orgao1      = $Linha[1];
    $orgao2      = $Linha[2];
    $setor1      = $Linha[3];
    $nomesistema = $Linha[4];

    $consulta = compact("empresa", "orgao1", "orgao2", "setor1", "nomesistema");

    return $consulta;
}

/**
 * Gera um item de formulário tipo text area padrão
 */
function gerarTextArea($nomeFormulario, $nomeCampo, $texto, $qtdeMaxima, $apenasTexto = false) {
    assercao(! is_null($nomeFormulario), "Variável nomeFormulario não foi informada");
    assercao(! is_null($nomeCampo), "Variável nomeCampo não foi informada");
    assercao(! is_null($qtdeMaxima), "Variável qtdeMaxima não foi informada");

    if ($apenasTexto) {
        return $texto;
    }

    $qtde = strlen($texto);

    $template = new TemplatePortal(CAMINHO_SISTEMA . "geral/templates/InputTextArea.template.html");
    $template->FORMULARIO = $nomeFormulario;
    $template->TEXTO = $texto;
    $template->NOME_CAMPO = $nomeCampo;
    $template->QTDE_MAXIMA = $qtdeMaxima;
    $template->QTDE = $qtde;
    $template->ATRIBUTOS = "";

    return $template->parse();
}

/**
 * Gera um grupo de radio buttons
 */
function gerarRadioButtons($nomeCampo, $arrayItens, $arrayValores, $valorSelecionado, $isVertical = false, $apenasTexto = false, $onChange = '', $atributos = '') {
    assercao(! is_null($nomeCampo), "Variável 'nomeCampo' não foi informada");
    assercao(! is_null($arrayItens), "Variável 'arrayItens' não foi informada");
    assercao(! is_null($arrayValores), "Variável 'arrayValores' não foi informada");

    $qtde        = count($arrayItens);
    $qtdeValores = count($arrayValores);

    assercao($qtde > 0, "Variável 'arrayItens' deve ser um array com pelo menos 1 item");
    assercao($qtde == $qtdeValores, "Variável 'arrayValores' deve ser um array com mesmo tamanho do array 'arrayItens'");

    if ($apenasTexto) {
        for ($itr = 0; $itr < $qtde; $itr ++) {
            if ($arrayValores[$itr] == $valorSelecionado) {
                return $arrayItens[$itr];
            }
        }

        return '';
    }

    $template = new TemplatePortal(CAMINHO_SISTEMA . "geral/templates/RadioButtons.template.html");

    for ($itr = 0; $itr < $qtde; $itr ++) {
        assercao(! is_null($arrayItens[$itr]), "Variável 'arrayItens[$itr]' não foi informada");
        assercao(! is_null($arrayValores[$itr]), "Variável 'arrayValores[$itr]' não foi informada");

        $template->LABEL = $arrayItens[$itr];
        $template->VALOR = $arrayValores[$itr];

        if ($arrayValores[$itr] == $valorSelecionado) {
            $template->ATRIBUTOS = 'CHECKED';
        } else {
            $template->ATRIBUTOS = $atributos;
        }

        if ($isVertical) {
            $template->block("BLOCO_RADIO_BR");
        }

        $template->block("BLOCO_RADIO");
    }

    $template->NOME_CAMPO = $nomeCampo;
    $template->ON_CHANGE = $onChange;

    return $template->parse();
}

/**
 * Gera um combo box
 */
function gerarComboBox($nomeCampo, $arrayItens, $arrayValores, $valorSelecionado, $apenasTexto = false, $onChange = '') {
    assercao(! is_null($nomeCampo), "Variável 'nomeCampo' não foi informada");
    assercao(! is_null($arrayItens), "Variável 'arrayItens' não foi informada");
    assercao(! is_null($arrayValores), "Variável 'arrayValores' não foi informada");

    $qtde        = count($arrayItens);
    $qtdeValores = count($arrayValores);

    assercao($qtde > 0, "Variável 'arrayItens' deve ser um array com pelo menos 1 item");
    assercao($qtde == $qtdeValores, "Variável 'arrayValores' deve ser um array com mesmo tamanho do array 'arrayItens'");

    if ($apenasTexto) {
        for ($itr = 0; $itr < $qtde; $itr ++) {
            if ($arrayValores[$itr] == $valorSelecionado) {
                return $arrayItens[$itr];
            }
        }

        return '';
    }

    $template = new TemplatePortal(CAMINHO_SISTEMA . "geral/templates/ComboBox.template.html");

    for ($itr = 0; $itr < $qtde; $itr ++) {
        assercao(! is_null($arrayItens[$itr]), "Variável 'arrayItens[$itr]' não foi informada");
        assercao(! is_null($arrayValores[$itr]), "Variável 'arrayValores[$itr]' não foi informada");

        $template->ITEM = $arrayItens[$itr];
        $template->ITEM_VALOR = $arrayValores[$itr];

        if ($arrayValores[$itr] == $valorSelecionado) {
            $template->ATRIBUTOS = 'SELECTED';
        } else {
            $template->ATRIBUTOS = '';
        }

        $template->block("BLOCO_ITEM");
    }

    $template->NOME_CAMPO = $nomeCampo;
    $template->ON_CHANGE = $onChange;

    return $template->parse();
}

/**
 * Adiciona uma mensagem de aviso ou erro para ser mostrado ao usuário
 */
function adicionarMensagem($mensagem, $tipo) {
    if (! $GLOBALS['BloquearMensagens']) {
        if ($GLOBALS['Mens'] == 1) {
            $GLOBALS['Mensagem'] .= ", ";
        } else {
            $GLOBALS['Mensagem'] = "Informe: ";
        }

        $GLOBALS['Mens'] = 1;
        $GLOBALS['Tipo'] = $tipo;
        $GLOBALS['Mensagem'] .= $mensagem;
    }
}

/**
 * Adiciona uma mensagem de erro e bloqueia outras mensagens
 */
function mostrarMensagemErroUnica($mensagem) {
    $GLOBALS['Mens']              = 1;
    $GLOBALS['BloquearMensagens'] = true;
    $GLOBALS['Tipo']              = 2;
    $GLOBALS['Mensagem']          = $mensagem;
}

/**
 * Função de Cabeçalho dos Relatórios PDF
 */
function CabecalhoRodape() {
    // Classes FPDF #
    class PDF extends FPDF {
        // Cabeçalho #
        public function Header() {
            // #### Verificar endereço quando passar para produção #####
            $cabecalho = retornaCabecalho();

            $this->Image("../" . $GLOBALS["PASTA_MIDIA"] . "brasaopeq.jpg", 91, 5, 0);
            $this->SetFont("Arial", "B", 10);
            $this->Cell(0, 20, "$cabecalho[empresa]", 0, 0, "L");
            $this->Cell(0, 20, "$cabecalho[orgao1]", 0, 0, "R");
            $this->Ln(1);
            $this->Cell(0, 25, "$cabecalho[orgao2]", 0, 0, "L");
            $this->Cell(0, 25, "$cabecalho[setor1]", 0, 0, "R");
            $this->Ln(1);
            $this->Cell(0, 30, "$cabecalho[nomesistema]", 0, 0, "L");
            $this->Cell(0, 30, "", 0, 0, "R");
            $this->Ln(1);
            $this->Line(10, 30, 200, 30);
            $this->Cell(0, 39, $GLOBALS['TituloRelatorio'], 0, 0, "C");
            $this->Ln(1);
            $this->Line(10, 36, 200, 36);
            $this->Ln(25);
        }

        /**
         * Rodapé
         */
        public function Footer() {
            $this->SetFont("Arial", "", 10);
            $this->SetY(- 29);
            $this->Cell(0, 30, "Emissão: " . date("d/m/Y H:i:s"), 0, 0, "L");
            $this->Line(10, 280, 200, 280);
            $this->SetY(- 19);
            $this->Cell(0, 10, "Página: " . $this->PageNo() . "/{nb}", 0, 0, "R");
        }
    }
}

function CabecalhoRodapeBG() {
    // Classes FPDF #
    class PDF extends FPDF {
        // Cabeçalho #
        public function Header() {
            // #### Verificar endereço quando passar para produção #####
            $cabecalho = retornaCabecalho();

            $this->Image("../" . $GLOBALS["PASTA_MIDIA"] . "brasaopeq.jpg", 91, 5, 0);
            $this->Image("../" . $GLOBALS["PASTA_MIDIA"] . "brasaobg.jpg", - 5, 25, 0);
            $this->SetFont("Arial", "B", 10);
            $this->Cell(0, 20, "$cabecalho[empresa]", 0, 0, "L");
            $this->Cell(0, 20, "$cabecalho[orgao1]", 0, 0, "R");
            $this->Ln(1);
            $this->Cell(0, 25, "$cabecalho[orgao2]", 0, 0, "L");
            $this->Cell(0, 25, "$cabecalho[setor1]", 0, 0, "R");
            $this->Ln(1);
            $this->Cell(0, 30, "$cabecalho[nomesistema]", 0, 0, "L");
            $this->Cell(0, 30, "", 0, 0, "R");
            $this->Ln(1);
            $this->Line(10, 30, 200, 30);
            $this->Cell(0, 39, $GLOBALS['TituloRelatorio'], 0, 0, "C");
            $this->Ln(1);
            $this->Line(10, 36, 200, 36);
            $this->Ln(25);
        }

        /**
         * Rodapé
         */
        public function Footer() {
            $this->SetFont("Arial", "", 10);
            $this->SetY(- 29);
            $this->Cell(0, 30, "Emissão: " . date("d/m/Y H:i:s"), 0, 0, "L");
            $this->Line(10, 280, 200, 280);
            $this->SetY(- 19);
            $this->Cell(0, 10, "Página: " . $this->PageNo() . "/{nb}", 0, 0, "R");
        }
    }
}

function CabecalhoRodapePaisagem() {
    // Classes FPDF #
    class PDF extends FPDF {
        private $xHeader;
        private $yHeader;

        // Cabeçalho #
        public function Header() {
            $cabecalho = retornaCabecalho();
            // #### Verificar endereço quando passar para produção #####
            $this->Image("../" . $GLOBALS["PASTA_MIDIA"] . "brasaopeq.jpg", 135, 5, 0);
            $this->SetFont("Arial", "B", 10);
            $this->Cell(0, 20, "$cabecalho[empresa]", 0, 0, "L");
            $this->Cell(0, 20, "$cabecalho[orgao1]", 0, 0, "R");
            $this->Ln(1);
            $Empresa = $_SESSION['_egruatdesc_'];
            $this->Cell(0, 25, "$cabecalho[orgao2]", 0, 0, "L");
            $this->Cell(0, 25, "$cabecalho[setor1]", 0, 0, "R");
            $this->Ln(1);
            $this->Cell(0, 30, "$cabecalho[nomesistema]", 0, 0, "L");
            $this->Cell(0, 30, "", 0, 0, "R");
            $this->Ln(1);
            $this->Line(10, 30, 290, 30);
            $this->Cell(0, 39, $GLOBALS['TituloRelatorio'], 0, 0, "C");
            $this->Ln(1);
            $this->Line(10, 36, 290, 36);
            $this->Ln(25);
            $this->Line(10, 39, 290, 39);
            $this->xHeader = $this->GetX();
            $this->yHeader = $this->GetY();
        }

        /**
         * Rodapé
         */
        public function Footer() {
            $this->SetFont("Arial", "", 10);            

            if ($GLOBALS['TituloRelatorio'] == "Relatório Sintético de Entradas e Saídas") {
                $this->SetY(- 23);
                $this->Cell(280, 5, "Em conformidade com a lei 4.320/64, art 106, III, os bens de almoxarifado são avaliados pelo preço médio ponderado das compras.", 0, 0, "C", 0);    
            }

            $this->Line(10, 192, 290, 192);
            $this->SetY(- 17);
            $this->Cell(250, 5, "Emissão: " . date("d/m/Y H:i:s"), 0, 0, "L", 0);
            $this->Cell(30, 5, "Página: " . $this->PageNo() . "/{nb}", 0, 0, "R", 0);
        }

        /**
         * Returns the height of a string in user unit.
         * A font must be selected.
         *
         * @param float $w
         * @param float $h
         * @param string $txt
         * @param string $align
         *            Sets the text alignment. Possible values are:
         *            L: left alignment
         *            C: center
         *            R: right alignment
         *            J: justification (default value)
         */
        public function GetStringHeight($w, $h, $txt, $align) {
            $y1 = $this->GetY();
            $x1 = $this->GetX();
            $x2 = 9999; // 9999 margem de segurança

            $this->SetX($x2);            
            $this->MultiCell($w, $h, $txt, 0, $align);

            $y2 = $this->GetY();

            $this->SetXY($x1, $y1);

            $ret = $y2 - $y1;

            if ($ret < 0) {
                $this->SetXY($this->xHeader, $this->yHeader);

                $ret = $this->GetStringHeight($w, $h, $txt, $align);
            }

            return $ret;
        }
    }
}

/**
 * LogErro()- Escreve erro em arquivo
 */
function logErro($texto) {
    $myFile = $GLOBALS["CAMINHO_LOGS"] . "ERRO.LOG";
    $fh = fopen($myFile, 'a') or exit("Falha ao abrir arquivo de log de erros");
    $stringData = $texto;

    fwrite($fh, $stringData);
    fwrite($fh, "\n-----------------------------------------\n");
    fclose($fh);
}

function loggerPortalCompras($msg) {
    $sessionValues = array();

    foreach ($_SESSION as $key => $value) {
        if ($key == '_MENU_') {
            continue;
        }

        $sessionValues[$key] = $value;
    }

    $serverValues = array();
    $keysServerNotAllowed = array(
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'HTTP_COOKIE'
    );

    foreach ($_SERVER as $key => $value) {
        if (in_array($key, $keysServerNotAllowed)) {
            continue;
        }

        $serverValues[$key] = $value;
    }

    $arrayGlobals = array();
    $arrayGlobals['mensagem'] = $msg;
    $arrayGlobals['session'] = $sessionValues;
    $arrayGlobals['server'] = $serverValues;

    if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
        $arrayGlobals['post'] = $_POST;
    }

    if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
        $arrayGlobals['get'] = $_GET;
    }

    $text = var_export($arrayGlobals, true);

    $logFile = "/tmp/logPortalCompras.log";
    $handle = fopen($logFile, 'ab') or exit("Falha ao abrir arquivo de log de erros");

    fwrite($handle, $text);
    fwrite($handle, "\n----------------------------------------------------------------------------------\n");
    fclose($handle);
}

/**
 * Envia email
 * parametros requeridos (em ordem):
 * e-mail destino, assunto (subject do email), mensagem, e-mail remetente, arquivo atachado (opcional), nome do arquivo atachado (opcional)
 */
function EnviaEmail() {
    $numargs = func_num_args();
    
    global $Mail;
    
    // Ignorar o parametro do email para evitar que mande e-mail para os usuários não relacionados ao suporte, caso não esteja em produção
    $_PARA_ = func_get_arg(0);

    if ($GLOBALS["LOCAL_SISTEMA"] == CONST_NOMELOCAL_PRODUCAO) {
        $_PARA_ = func_get_arg(0);
    } else {
        $_PARA_ = $GLOBALS["EMAIL_SUPORTE"];
    }

    $_ASSUNTO_ = func_get_arg(1);
    $_MENSAGEM_ = func_get_arg(2);
    $_COMPLEMENTO_ = func_get_arg(3);

    if ($numargs > 4) {
        $Arquivo = func_get_arg(4);
        $ArquivoNome = func_get_arg(5);
    }

    loggerPortalCompras($_MENSAGEM_);

    require_once ($GLOBALS["CAMINHO_EMAIL"] . 'class.phpmailer.php');
    require_once ($GLOBALS["CAMINHO_EMAIL"] . 'class.smtp.php');

    /* Cria Objeto do E-mail */
    $objmail = new PHPMailer();

    /* Destinatários */
    $_PARA_ = explode(",", $_PARA_);

    foreach ($_PARA_ as $Address) {
        $objmail->addAddress(trim($Address));
    }

    /* Remetente */
    $_COMPLEMENTO_ = trim(str_replace("from:", "", strtolower2($_COMPLEMENTO_)));
    $objmail->From = $_COMPLEMENTO_;
    $objmail->Sender = $_COMPLEMENTO_;
    $objmail->FromName = $_COMPLEMENTO_;

    /* Mensagem */
    $objmail->Body = iconv('utf-8', 'iso-8859-1', $_MENSAGEM_);

    /* Assunto */
    $titulo = $GLOBALS["LOCAL_SISTEMA_TITULO"] . " " . $_ASSUNTO_;
    $objmail->Subject = iconv('utf-8', 'iso-8859-1', $titulo);

    /* Arquivo Atachado */
    if ($numargs > 4) {
        $objmail->AddAttachment($Arquivo, $ArquivoNome);
    }
    
    $objmail->IsSMTP();

    /* Dados do Servidor de Envio */
    $objmail->Mailer = 'smtp';
    $objmail->Host = 'smtp.recife.pe.gov.br';
    $objmail->Port = '25';

    return $objmail->send();
}
/**
 * Envia email com texto em formato HTML
 * parametros requeridos (em ordem):
 * e-mail destino, assunto (subject do email), mensagem, e-mail remetente, arquivo atachado (opcional), nome do arquivo atachado (opcional)
 */
function EnviaEmailHTML() {
    $numargs = func_num_args();
    
    global $Mail;
    
    // Ignorar o parametro do email para evitar que mande e-mail para os usuários não relacionados ao suporte, caso não esteja em produção
    $_PARA_ = func_get_arg(0);

    if ($GLOBALS["LOCAL_SISTEMA"] == CONST_NOMELOCAL_PRODUCAO) {
        $_PARA_ = func_get_arg(0);
    } else {
        $_PARA_ = $GLOBALS["EMAIL_SUPORTE"];
    }

    $_ASSUNTO_ = func_get_arg(1);
    $_MENSAGEM_ = func_get_arg(2);
    $_COMPLEMENTO_ = func_get_arg(3);

    if ($numargs > 4) {
        $Arquivo = func_get_arg(4);
        $ArquivoNome = func_get_arg(5);
    }

    loggerPortalCompras($_MENSAGEM_);

    require_once ($GLOBALS["CAMINHO_EMAIL"] . 'class.phpmailer.php');
    require_once ($GLOBALS["CAMINHO_EMAIL"] . 'class.smtp.php');

    /* Cria Objeto do E-mail */
    $objmail = new PHPMailer();
    
    /* Destinatários */
    $_PARA_ = explode(",", $_PARA_);

    foreach ($_PARA_ as $Address) {
        $objmail->addAddress(trim($Address));
    }

    /* Remetente */
    $_COMPLEMENTO_ = trim(str_replace("from:", "", strtolower2($_COMPLEMENTO_)));
    $objmail->From = $_COMPLEMENTO_;
    $objmail->Sender = $_COMPLEMENTO_;
    $objmail->FromName = $_COMPLEMENTO_;

    /* Mensagem */
    $objmail->Body = iconv('utf-8', 'iso-8859-1', $_MENSAGEM_);
    
    /* Assunto */
    $titulo = $GLOBALS["LOCAL_SISTEMA_TITULO"] . " " . $_ASSUNTO_;
    $objmail->Subject = iconv('utf-8', 'iso-8859-1', $titulo);
    $objmail->isHTML(true);
    /* Arquivo Atachado */
    if ($numargs > 4) {
        if(count($Arquivo) == 1){
                $objmail->AddAttachment($Arquivo, $ArquivoNome);
        }else{
            for($i=0;$i<count($Arquivo);$i++){
                $objmail->AddAttachment($Arquivo[$i], $ArquivoNome[$i]);
            }
        }
        
    }
    
    $objmail->IsSMTP();

    /* Dados do Servidor de Envio */
    $objmail->Mailer = 'smtp';
    $objmail->Host = 'smtp.recife.pe.gov.br';
    $objmail->Port = '25';

    return $objmail->send();
}

/**
 * envia email com mensagem do sistema
 * PARÂMETROS:
 * $assunto - título que aparecerá no email
 * $mensagem - Corpo do email
 * $paraAdm - Se true envia email só para os adminstradores do sistema,
 * por default = false (envia para os analista do sistema)
 */
function EnviaEmailSistema($assunto, $mensagem, $paraAdm = false) {
    if ($paraAdm) {
        EnviaEmail($GLOBALS["EMAIL_ADMINISTRADORES"], $assunto, $mensagem, $GLOBALS["EMAIL_FROM"]);
    } else {
        EnviaEmail($GLOBALS["EMAIL_SUPORTE"], $assunto, $mensagem, $GLOBALS["EMAIL_FROM"]);
    }
}

/**
 * Envia email com um erro no sistema.
 * PARÂMETROS:
 * $assunto- título que aparecerá no email
 * $mensagem- Descrição com informações detalhadas do erro.
 * $crash- Informa se a função deve finalizar a execução do script. Padrão = true
 */
function EmailErroSistema($assunto, $mensagem, $crash = true) {
    global $Mail;

    if ($crash) {
        echo "
			<!DOCTYPE html PUBLIC '-//IETF//DTD HTML 2.0//EN'>
			<html>
				<head><title>Prefeitura do Recife</title><head>
				<body bgcolor='#FFFFFF'>
					<center>
						<br/><br/>
						<table border='0' width='75%'>
							<tr>
								<td>
									<br/><br/><br/><br/>
								</td>
							</tr>
							<tr>
								<td bgcolor='#ff0000'>
									<font color='#ffffff' face='verdana,verdana,sans-serif' size='-1'>
										<b>Ocorreu um erro!</b>
									</font>
								</td>
							</tr>
							<tr>
								<td bgcolor='#efeeee'>
									<dfont face='verdana,verdana,sans-serif' size='-1'>
										Ocorreu uma falha no sistema. Foi enviado um email para o analista responsável informando o problema.
										<br/><br/>Lamentamos o ocorrido.<br/><br/>
										Tente novamente mais tarde e, se o problema persistir, entre em contato com a Gerência Geral de Licitação e Compras, telefone 3355-8790.
									</font>
								</td>
							</tr>
						</table>
					</center>
					<br>
				</body>
			</html>
		";

        $excecao = new Excecao($mensagem);

        //var_dump($excecao->toString());

        $msg = "Esta é uma mensagem automática do Portal de Compras. Um erro não tratado ocorreu.\n\n";
        $msg .= "Usuário: " . $_SESSION['_eusupologi_'];
        $msg .= "\n\nIP do usuário: " . $_SERVER["REMOTE_ADDR"];
        $msg .= "\n\nData: " . date("d/m/Y H:i:s");
        $msg .= "\n\n" . $excecao->toString();
    }

    // Enviar email em ISO-8859-1
    EnviaEmailSistema("ERRO: " . $assunto, $msg);

    if ($crash) {
        exit(0);
    }
}

function stri_replace($find, $replace, $string) {
    // Case-insensitive str_replace()
    $parts = explode(strtolower2($find), strtolower2($string));

    $pos = 0;

    foreach ($parts as $key => $part) {
        $parts[$key] = substr($string, $pos, strlen($part));
        $pos += strlen($part) + strlen($find);
    }

    return (join($replace, $parts));
}

/**
 * Transforms txt in html
 */
function txt2html($txt) {
    $txt = htmlspecialchars($txt);

    // Basic formatting
    $txt = str_replace("\r\n", "<br/>", $txt);
    $txt = str_replace("\n", "<br/>", $txt);
    $txt = str_replace("\r", "<br/>", $txt);

    return $txt;
}

/**
 * FUNÇÕES OBSOLETAS
 * As funções abaixco estão obsoletas por haver melhores opções.
 * Não foram deletados ou sobrescritos para compatibilidade.
 */

/**
 * Escreve os campos de Pesquisa nos Formulários
 */
function Pesquisa($ItemPesquisa, $Argumento, $Palavra, $Cols) {
    echo "<tr>\n";
    echo "	<td class=\"textonormal\" colspan=\"$Cols\">\n";
    echo "		<table border=\"0\" cellpadding=\"0\" cellspacing=\"2\" class=\"textonormal\" width=\"100%\" summary=\"\">\n";
    echo "			<tr>\n";
    echo "				<td class=\"textonormal\" bgcolor=\"#DCEDF7\" width=\"30%\">Pesquisa*</td>\n";
    echo "				<td class=\"textonormal\">\n";
    echo "					<select name=\"ItemPesquisa\" class=\"textonormal\">\n";
    echo "						<option value=\"RAZAO\"\n";

    if ($ItemPesquisa == "CNPJ" or $ItemPesquisa == "") {
        echo "selected";
    }

    echo ">Razão Social/Nome</option>\n";
    echo "						<option value=\"CNPJ\"\n";

    if ($ItemPesquisa == "CNPJ") {
        echo "selected";
    }

    echo ">CNPJ</option>\n";
    echo "						<option value=\"CPF\"\n";

    if ($ItemPesquisa == "CPF") {
        echo "selected";
    }

    echo ">CPF</option>\n";
    echo "					</select>\n";
    echo "				</td>\n";
    echo "			</tr>\n";
    echo "			<tr>\n";
    echo "				<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Argumento*</td>\n";
    echo "				<td>\n";
    echo "					<input type=\"text\" class=\"textonormal\" name=\"Argumento\" size=\"40\" maxlength=\"60\" value=\"$Argumento\">\n";
    echo "					<input type=\"checkbox\" class=\"textonormal\" name=\"Palavra\" value=\"1\"\n";

    if ($Palavra == 1) {
        echo "checked";
    }

    echo "					> Palavra Exata\n";
    echo "				</td>\n";
    echo "			</tr>\n";
    echo "		</table>\n";
    echo "	</td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "	<td align=\"right\" colspan=\"$Cols\">\n";
    echo "		<input type=\"button\" value=\"Pesquisar\" class=\"botao\" onclick=\"javascript:enviar('Pesquisar');\">\n";
    echo "		<input type=\"button\" value=\"Limpar\" class=\"botao\" onclick=\"javascript:enviar('Limpar');\">\n";
    echo "		<input type=\"hidden\" name=\"Botao\" value=\"\">\n";
    echo "	</td>\n";
    echo "	</tr>\n";
}

function PesquisaSocio($ItemPesquisa, $Argumento, $Palavra, $Cols) {
    echo "<tr>\n";
    echo "	<td class=\"textonormal\" colspan=\"$Cols\">\n";
    echo "		<table border=\"0\" cellpadding=\"0\" cellspacing=\"2\" class=\"textonormal\" width=\"100%\" summary=\"\">\n";
    echo "			<tr>\n";
    echo "				<td class=\"textonormal\" bgcolor=\"#DCEDF7\" width=\"30%\">Pesquisa*</td>\n";
    echo "				<td class=\"textonormal\">\n";
    echo "					<select name=\"ItemPesquisa\" class=\"textonormal\">\n";
    echo "						<option value=\"NOME\"\n";

    if ($ItemPesquisa == "NOME" or $ItemPesquisa == "") {
        echo "selected";
    }

    echo ">Nome</option>\n";
    echo "						<option value=\"CNPJCPF\"\n";

    if ($ItemPesquisa == "CNPJCPF") {
        echo "selected";
    }

    echo ">CNPJ/CPF</option>\n";
    echo "					</select>\n";
    echo "				</td>\n";
    echo "			</tr>\n";
    echo "			<tr>\n";
    echo "				<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Argumento*</td>\n";
    echo "				<td>\n";
    echo "					<input type=\"text\" class=\"textonormal\" name=\"Argumento\" size=\"40\" maxlength=\"60\" value=\"$Argumento\">\n";
    echo "					<input type=\"checkbox\" class=\"textonormal\" name=\"Palavra\" value=\"1\"\n";

    if ($Palavra == 1) {
        echo "checked";
    }

    echo "					> Palavra Exata\n";
    echo "				</td>\n";
    echo "			</tr>\n";
    echo "		</table>\n";
    echo "	</td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "	<td align=\"right\" colspan=\"$Cols\">\n";
    echo "		<input type=\"button\" value=\"Pesquisar\" class=\"botao\" onclick=\"javascript:enviar('Pesquisar');\">\n";
    echo "		<input type=\"button\" value=\"Limpar\" class=\"botao\" onclick=\"javascript:enviar('Limpar');\">\n";
    echo "		<input type=\"hidden\" name=\"Botao\" value=\"\">\n";
    echo "	</td>\n";
    echo "	</tr>\n";
}

/**
 * Exibe Mensagem de Erro (Bula)
 */
function ExibeMensStr($Mensagem, $Tipo, $Troca) {
    if ($Troca == 1) {
        if (strrpos($Mensagem, ",") != 0) {
            $Mensagem = substr_replace($Mensagem, " e ", strrpos($Mensagem, ",")) . substr($Mensagem, (strrpos($Mensagem, ",") + 1));
        }
    }

    $retorno = "<table border=\"0\" width=\"100%\">\n";

    if ($Tipo == 1) {
        $retorno .= "<tr>\n";
        $retorno .= "	<td bgcolor='DCEDF7' class='titulo2'>\n";
        $retorno .= "		<blink>Atenção!</blink>\n";
        $retorno .= "</td>\n";
        $retorno .= "</tr>\n";
        $retorno .= "<tr>\n";
        $retorno .= "	<td class=titulo2>$Mensagem.</td>\n";
        $retorno .= "</tr>\n";
    } else {
        $retorno .= "<tr>\n";
        $retorno .= "	<td bgcolor=\"DCEDF7\" class=\"titulo1\">\n";
        $retorno .= "		<blink><font class='titulo1'>Erro!</font></blink>\n";
        $retorno .= "	</td>\n";
        $retorno .= "</tr>\n";
        $retorno .= "<tr>\n";
        $retorno .= "	<td class=\"titulo2\">$Mensagem.</td>\n";
        $retorno .= "</tr>\n";
    }
    $retorno .= "</table><br>\n";

    return $retorno;
}

//
function ExibeMens($Mensagem, $Tipo, $Troca) {
    echo ExibeMensStr($Mensagem, $Tipo, $Troca);
}

function MesExt($mes) {
    $Meses = array(
        "JANEIRO",
        "FEVEREIRO",
        "MARÇO",
        "ABRIL",
        "MAIO",
        "JUNHO",
        "JULHO",
        "AGOSTO",
        "SETEMBRO",
        "OUTUBRO",
        "NOVEMBRO",
        "DEZEMBRO"
    );

    $mes = (int) $mes;
    $mes = $Meses[$mes - 1];

    return $mes;
}

/**
 * cria uma caixa de opções customizada
 * titulo- titulo descrevendo a pergunta
 * mensagem- texto com a pergunta (exemplo: Sim, Nao)
 * $enviarDif- início do nome de 'Botao'
 * (exemplo: Caso $enviarDif = 'Opcao', e $opcoes = {'Sim','Nao'} Os nomes de 'Botao' sera 'Opcao_Sim' e 'Opcao_Nao', respectivamente)
 */

/**
 * Obsoleto: não usar
 */
function CriarCaixaDeOpcoes($titulo, $msg, $opcoes, $enviarDif) {
    echo '
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
			<tr>
				<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
					' . $titulo . '
				</td>
			</tr>
			<tr>
				<td class="textonormal">
					<p align="justify">
						' . $msg . '
					</p>
				</td>
			</tr>
			<tr>
				<td class="textonormal" align="right">
	';

    for ($itr = 0; $itr < count($opcoes); $itr ++) {
        echo '<input type="button" value="' . $opcoes[$itr] . '" class="botao" onclick="javascript:enviar(\'' . $enviarDif . "_" . $opcoes[$itr] . '\');">';
    }

    echo '
		</td>
			</tr>
		</table>
	';
}

/**
 * Envia email com o erro no sistema
 * Obsoleto: usar EmailErroSistema();
 */
function EmailErro($assunto, $arquivo, $linha, $mensagem, $crash = true) {
    EmailErroSistema($assunto, $mensagem, $crash = true);
}

/**
 * Acrescenta o endereco correspondente ao array de paginas com permissao # Abreu
 */
function AddMenuAcesso($Endereco) {
    if (! is_array($_SESSION['_eacepocami_'])) {
        $_SESSION['_eacepocami_'] = array();
    }

    if ($Endereco[0] == "/") { // remover barra no inicio da string
        $Endereco = substr($Endereco, 1);
    }

    $End = "/" . $GLOBALS["PASTA_SISTEMA"] . $Endereco;
    
    if (! in_array($End, $_SESSION['_eacepocami_'])) {
        $_SESSION['_eacepocami_'][] = $End;
    }
}

/**
 * Pesquisa a Qtd de Filhos # (Luciano)
 */
function AcessoFilho_Qtd($AcessoCodigoPai, $Nivel) {
    $db = Conexao();

    $sql  = "SELECT CACEPOCODI, EACEPODESC FROM SFPC.TBACESSOPORTAL ";
    $sql .= "WHERE  CACEPOCODI <> CACEPOCPAI AND CACEPOCPAI = $AcessoCodigoPai ";

    $result = $db->query($sql);

    $c = 0;

    if (PEAR::isError($result)) {
        $CodErroEmail = $result->getCode();
        $DescErroEmail = $result->getMessage();

        ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Rows = $result->numRows();
        $QtdFilhos += $Rows;

        while ($col = $result->fetchRow()) {
            $c ++;
            $Dados[$c - 1] = $col[0];
        }

        for ($Row = 0; $Row < $Rows; $Row ++) {
            $Acesso = $Dados[$Row];
            $QtdFilhos += AcessoFilho_Qtd($Acesso, $Nivel + 1);
        }
    }

    return $QtdFilhos;
}

/**
 * Mostra os Acessos do Menu # (Luciano)
 */
function AcessoFilho_CheckBox($AcessoCodigoPai, $Nivel, $AcessoCodigo, $Pai, $Filhos) {
    $Endentacao = str_repeat("&nbsp;&nbsp;&nbsp;", $Nivel);

    $db = Conexao();

    $sql  = "SELECT CACEPOCODI, EACEPODESC FROM SFPC.TBACESSOPORTAL ";
    $sql .= "WHERE CACEPOCODI <> CACEPOCPAI AND CACEPOCPAI = $AcessoCodigoPai ";
    $sql .= "ORDER BY AACEPOORDE";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        $CodErroEmail = $result->getCode();
        $DescErroEmail = $result->getMessage();

        ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Rows = $result->numRows();

        $c = 0;

        while ($col = $result->fetchRow()) {
            $c ++;
            $Dados[$c - 1] = "$col[0]_$col[1]";
        }

        $GLOBALS['CheckBox'][$Filhos] = str_replace("Filhos", $Rows, $GLOBALS['CheckBox'][$Filhos]);

        if ($AcessoCodigo == "") {
            $AcessoCodigo = array();
        }

        for ($Row = 0; $Row < $Rows; $Row ++) {
            $Linha = explode("_", $Dados[$Row]);

            if (in_array($Linha[0], $AcessoCodigo)) {
                $GLOBALS['CheckBox'][$GLOBALS['L']] = "<tr><td class=\"textonormal\">$Endentacao<input type=\"checkbox\" checked name=\"AcessoCodigo[]\" OnClick=\"UnCheck(" . $GLOBALS['Numero'] . ",$Pai,Filhos)\" value=\"$Linha[0]\"> $Linha[1]\n</td></tr>\n";
            } else {
                $GLOBALS['CheckBox'][$GLOBALS['L']] = "<tr><td class=\"textonormal\">$Endentacao<input type=\"checkbox\" name=\"AcessoCodigo[]\" OnClick=\"UnCheck(" . $GLOBALS['Numero'] . ",$Pai,Filhos)\" value=\"$Linha[0]\"> $Linha[1]\n</td></tr>\n";
            }

            $GLOBALS['L'] ++;
            $GLOBALS['Numero'] ++;

            AcessoFilho_CheckBox($Linha[0], $Nivel + 1, $AcessoCodigo, $GLOBALS['Numero'] - 1, $GLOBALS['L'] - 1);
        }
    }

    return;
}

/**
 * Monta JavaScript (Menu Pai) # (Luciano)
 */
function MenuAcessoStr() {
    global $Menu, $L;

    if ($_SESSION['_eacepocami_'] == array()) {
        if ($_SESSION['_cperficodi_'] == "") {
            // Usuário sem Perfil ou Perfil Inativo #
            $_SESSION['_cperficodi_'] = 0; // Perfil Padrão (0 - INTERNET) #
        }

        $db = Conexao();

        $sql  = "SELECT A.CACEPOCODI, A.EACEPODESC, A.CACEPOCPAI, A.EACEPOCAMI ";
        $sql .= "FROM SFPC.TBACESSOPORTAL A, SFPC.TBPERFILACESSO B ";
        $sql .= "WHERE A.CACEPOCODI = A.CACEPOCPAI AND A.CACEPOCODI = B.CACEPOCODI ";
        $sql .= "AND B.CPERFICODI = " . $_SESSION['_cperficodi_'] . " ORDER BY A.AACEPOORDE ";
        // echo $sql;
        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            $CodErroEmail = $result->getCode();
            $DescErroEmail = $result->getMessage();
            
            ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        } else {
            $num = $result->numRows();

            $Menu[0] = "var NoOffFirstLineMenus=" . ($num) . ";\n";

            $L = 1;
            $M = 1;
            $c = 0;
            $p = 0;

            while ($registro = $result->fetchRow()) {
                $p ++;
                $dados[$p - 1] = "$registro[0]_$registro[1]_$registro[3]";
            }

            for ($i = 0; $i < $p; $i ++) {
                $pai = explode("_", $dados[$i]);

                $sqlf  = " SELECT A.CACEPOCODI, A.EACEPODESC, A.EACEPOCAMI";
                $sqlf .= " FROM SFPC.TBACESSOPORTAL A, SFPC.TBPERFILACESSO B";
                $sqlf .= " WHERE A.CACEPOCODI <> A.CACEPOCPAI AND A.CACEPOCPAI = $pai[0]";
                $sqlf .= " AND A.CACEPOCODI = B.CACEPOCODI AND B.CPERFICODI = " . $_SESSION['_cperficodi_'];
                $sqlf .= " ORDER BY A.AACEPOORDE";
                // echo $sqlf; die;
                $resultf = $db->query($sqlf);

                if (PEAR::isError($resultf)) {
                    $CodErroEmail = $result->getCode();
                    $DescErroEmail = $result->getMessage();

                    ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                } else {
                    $QtdFilhos = $resultf->numRows();

                    if ($pai[2] != "") {
                        $Menu[$L] = "Menu" . $M . " = new Array(\"$pai[1]\",\"$pai[2]\",'',$QtdFilhos,20,100,'','','','','','',-1,-1,-1,'','');\n";

                        if (! in_array($pai[2], $_SESSION['_eacepocami_'])) {
                            $PosFinal = strlen($pai[2]);
                            $Str = trim(substr($pai[2], 0, $PosFinal));

                            AddMenuAcesso($Str);
                        }
                    } else {
                        $Menu[$L] = "Menu" . $M . " = new Array(\"$pai[1]\",'','',$QtdFilhos,20,100,'','','','','','',-1,-1,-1,'','');\n";
                    }
                    $L ++;

                    MenuAcessoFilho($db, $resultf, $M);

                    $M ++;
                }
            }
        }

        $db->disconnect();
        
        for ($L = 0; $L < count($Menu); $L ++) {
            $_SESSION['_MENU_'] .= $Menu[$L];
        }
    }

    return $_SESSION['_MENU_'];
}

/**
 */
function MenuAcesso() {
    echo MenuAcessoStr();
}

/**
 * Monta JavaScript (Menu Filhos)
 */
function MenuAcessoFilho($db, $result, $M) {
    global $Menu, $L;

    $S = 1;

    $M .= "_";

    $num = $result->numRows();

    $Tam = 0;
    $f = 0;

    while ($linha = $result->fetchRow()) {
        $f ++;

        $filhos[$f - 1] = "$linha[0]_$linha[1]_$linha[2]";

        if (strlen($linha[1]) > $Tam) {
            $Tam = strlen($linha[1]);
        }
    }

    $Tam = $Tam * 8.5;

    for ($i = 0; $i < $f; $i ++) {
        $filho = explode("_", $filhos[$i]);

        $sql  = "SELECT A.CACEPOCODI, A.EACEPODESC, A.EACEPOCAMI ";
        $sql .= "FROM SFPC.TBACESSOPORTAL A, SFPC.TBPERFILACESSO B ";
        $sql .= "WHERE A.CACEPOCODI <> A.CACEPOCPAI AND A.CACEPOCPAI = $filho[0] ";
        $sql .= "AND A.CACEPOCODI = B.CACEPOCODI AND B.CPERFICODI = " . $_SESSION['_cperficodi_'];
        $sql .= " ORDER BY A.AACEPOORDE";

        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            $CodErroEmail = $result->getCode();
            $DescErroEmail = $result->getMessage();

            ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        } else {
            $QtdFilhos = $result->numRows();

            if ($filho[2] != "") {
                if ($filho[2][0] == "/") { // remover barra no inicio da string
                    $filho[2] = substr($filho[2], 1);
                }

                $Menu[$L] = "Menu" . $M . $S . " = new Array(\"" . $filho[1] . "\",\"" . $GLOBALS["DNS_SISTEMA"] . $filho[2] . "\",''," . $QtdFilhos . ",20," . $Tam . ",'','','','','','',-1,-1,-1,'','')	;\n";

                if (! in_array($filho[2], $_SESSION['_eacepocami_'])) {
                    $PosFinal = strlen($filho[2]);
                    $Str = trim(substr($filho[2], 0, $PosFinal));

                    AddMenuAcesso($Str);
                }
            } else {
                $Menu[$L] = "Menu" . $M . $S . " = new Array(\"" . $filho[1] . "\",'',''," . $QtdFilhos . ",20," . $Tam . ",'','','','','','',-1,-1,-1,'','');\n";
            }
            $L ++;

            MenuAcessoFilho($db, $result, $M . $S);

            $S ++;
        }
    }

    return;
}

function BuscaFilho($AcessoCodigo, $CodigoAtual, $db) {
    $sql = "SELECT CACEPOCPAI FROM SFPC.TBACESSOPORTAL WHERE CACEPOCODI = $CodigoAtual";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        $CodErroEmail = $result->getCode();
        $DescErroEmail = $result->getMessage();

        ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Linha = $result->fetchRow();

        if ($Linha[0] == $CodigoAtual) {
            $Retorno = 0;
        } else {
            if ($Linha[0] == $AcessoCodigo) {
                $Retorno = 1;
            } else {
                $Retorno = BuscaFilho($AcessoCodigo, $Linha[0], $db);
            }
        }
    }

    return $Retorno;
}

function BuscaFilhoLocalizacao($AcessoCodigo, $CodigoAtual, $db) {
    $sql = "SELECT CLOCMACPAI FROM SFPC.TBLOCALIZACAOMATERIAL WHERE CLOCMACODI = $CodigoAtual";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        $CodErroEmail = $result->getCode();
        $DescErroEmail = $result->getMessage();

        ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Linha = $result->fetchRow();

        if ($Linha[0] == $CodigoAtual) {
            $Retorno = 0;
        } else {
            if ($Linha[0] == $AcessoCodigo) {
                $Retorno = 1;
            } else {
                $Retorno = BuscaFilhoLocalizacao($AcessoCodigo, $Linha[0], $db);
            }
        }
    }

    return $Retorno;
}

/**
 * Verifica se um Acesso tem Filhos
 */
function TipoFilho($AcessoCodigoPai, $Nivel, $HierarquiaCodigo) {
    $Endentacao = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $Nivel);

    $db = Conexao();

    $sql  = "SELECT CACEPOCODI, EACEPODESC FROM SFPC.TBACESSOPORTAL ";
    $sql .= "WHERE CACEPOCODI <> CACEPOCPAI AND ";
    $sql .= "CACEPOCPAI = $AcessoCodigoPai ";
    $sql .= "ORDER BY AACEPOORDE";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        $CodErroEmail = $result->getCode();
        $DescErroEmail = $result->getMessage();

        ExibeErroBD("funcoes.php\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Rows = 0;

        while ($Linha = $result->fetchRow()) {
            $Rows ++;
            $CodigoPai[$Rows - 1] = "$Linha[0]_$Linha[1]";
        }

        for ($i = 0; $i < $Rows; $i ++) {
            $AcessoPai = explode("_", $CodigoPai[$i]);

            if ($AcessoPai[0] == $HierarquiaCodigo) {
                echo "<option value=\"$AcessoPai[0]\" selected>$Endentacao $AcessoPai[1]\n";
            } else {
                echo "<option value=\"$AcessoPai[0]\">$Endentacao $AcessoPai[1]\n";
            }

            TipoFilho($AcessoPai[0], $Nivel + 1, $HierarquiaCodigo);
        }
    }

    return;
}