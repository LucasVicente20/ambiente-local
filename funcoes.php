<?php
/**
 * Portal de Compras
 * 
 * Programa:   funcoes.php
 * Objetivo:   Diversas funções utilizadas no sistema
 * Observação: 1. Em mudança de link, trocar os caminhos em segurança (substr) e menuacessofilho
 *             2. Não sobrescrever este arquivo, se for o caso adicionar/alterar as funções
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     20/12/2007
 * Objetivo: Modificação do período de invetário
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     14/01/2008
 * Objetivo: Modificação do período de inventário, para o ALMOXARIFADO FUNDO MUNICIPAL VIAS PÚBLICAS poder fazer o inventário.
 *           O gestor do almoxarifado não fez na data estipulada (21-12-2007 - 08-01-2008) pois o mesmo estava de férias.
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     10/06/2008
 * Objetivo: Nova função 'trimNumeros()'
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     12/06/2008
 * Objetivo: função EnviarMail() agora envia e-mail para os emails contidos na variável $Mail (antes tinha que definir os emails em $Mail, na função, e toda vez que chamasse essa função).
 *           Novas funções específicas para envido de erro por email: EmailErro() e EmailErroSQL(). Devem ser usadas ao invés de EnviarMail pois provê mensagem do Banco e mensagem não precisa ser formatada.
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     13/06/2008
 * Objetivo: Função Seguranca() modificado para verificar a sessão toda vez que abrir uma página (NECESSITA DA VARIÁVEL GLOBAL $NomeLocal)
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     16/06/2008
 * Objetivo: Função CriarCaixaDeOpcoes() - cria uma caixa de opções customizada
 *           Função TruncarTexto() - para truncar textos que aparecem em caixas de texto
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     20/06/2008
 * Objetivo: Correções em EmailErroSQL() e Segurança()
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     25/06/2008
 * Objetivo: Nova variável $NomeLocalTitulo (apenas possui a variável $NomeLocal formatada para ser usada em títulos e cabeçalhos)
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     30/06/2008
 * Objetivo: Inúmeras alterações para automatizar variáveis de configurações e migrar o sistema para CVS:
 *              Criado e usando variáveis de configuração (config.php)
 *              Alterado as funções AddMenuAcesso(), Sistema(), MenuAcesso() e MenuAcessoFilho() para aceitar o novo padrão de endereços web e locais
 *              Demais correções e alterações necessárias
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     06/08/2008
 * Objetivo: Adicionado nova função EnviaEmailSistema
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     05/10/2008
 * Objetivo: Correção de bug na função EnviaEmail()
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     30/12/2009
 * Objetivo: Adicionado a sessão $_SESSION['AnoGeracaoUnidadeOrcamentaria]
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     09/06/2010
 * Objetivo: Adicionado a função tratarNomeArquivo()
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Everton Lino
 * Data:     28/07/2010
 * Objetivo: Adicionada uma função de tratar caracteres
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     13/09/2010
 * Objetivo: Corrigido valida_CPF para não permitir letras
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     05/11/2010
 * Objetivo: Adicionado funçoes prazoCertidaoNegDeFalencia() e prazoUltimoBalanco()
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     01/06/2011
 * Objetivo: Adicionado funções Hora(), LogErro()
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     03/06/2011
 * Objetivo: Tarefa Redmine: 2727
 *           Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     05/09/2011
 * Objetivo: Função adicionarMensagem()
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     08/09/2011
 * Objetivo: Função checarSituacaoFornecedor
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     12/09/2011
 * Objetivo: Alterações gerais para novos arquivos de funções
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     16/09/2011
 * Objetivo: Tarefa Redmine: 3718
 *           Remoção de campos de Representante Legal no módulo de fornecedores 
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     16/11/2012
 * Objetivo: Removendo todas as funções obsoletas para funcoesOld.php
 *           Carregando classes globaix como DataHora e Template
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - José Almir
 * Data:     21/11/2014
 * Objetivo: Ajustar bracha de segurança na função Seguranca()
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     24/10/2018
 * Objetivo: Tarefa Redmine 73662
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     15/03/2019
 * Objetivo: Tarefa Redmine 73662
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     20/05/2019
 * Objetivo: Tarefa Redmine 210696
 * --------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     19/06/2019
 * Objetivo: Tarefa Redmine 218517
 * --------------------------------------------------------------------------------------------------------------------
 * # Alterado: João Madson
 * Data:     14/07/2020
 * Objetivo: Tarefa Redmine #235540
 * -------------------------------------------------------------------------------------------------------------------- 
 * Autor:    João Madson
 * Data:     06/04/2021 
 * Objetivo: CR #245672
 * -------------------------------------------------------------------------
 * Alterado : Osmar Celestino
 * Data: 21/09/2021
 * Objetivo: CR #248922
 *---------------------------------------------------------------------------
 * Alterado : Osmar Celestino
 * Data: 08/04/2022
 * Objetivo: CR #261839
 *---------------------------------------------------------------------------
*/

// 220038--


require_once 'geral/config.php';

# Classe FPDF de Criação de Documentos PDF #
define('FPDF_FONTPATH',$GLOBALS["CAMINHO_PDF"].'font/');
require_once($GLOBALS["CAMINHO_PDF"].'fpdf.php');

# Classes importadas
require_once(CAMINHO_SISTEMA."geral/Excecao.class.php");

//require_once("geral/TemplatePortal.class.php"); //template genérico para qualquer html. Já é importado por TemplatePaginaPadrao.
require_once(CAMINHO_SISTEMA."geral/TemplatePaginaPadrao.class.php"); //template de uma página do Portal de Compras, com menu de acesso e layouts padrões
require_once(CAMINHO_SISTEMA."geral/TemplateNovaJanela.class.php"); //template nova janela
require_once(CAMINHO_SISTEMA."geral/DataHora.class.php"); // classe para manipular data e hora de diversos formatos


#arquivos de funcoes específicos
require_once(CAMINHO_SISTEMA."geral/funcoesOld.php"); // funções antigas que não devem mais ser usadas, mas são carregadas para compatibilidade
require_once(CAMINHO_SISTEMA."geral/funcoesBanco.php"); // funções relacionadas ao banco
require_once(CAMINHO_SISTEMA."geral/funcoesGui.php"); // funções relacionadas a interface e visualização
require_once(CAMINHO_SISTEMA."geral/configBloqueioViradaAno.php"); // Configuração para bloqueio de virada de ano

$EMAIL_ASSUNTO = "Arquivo de funções";

/** redireciona para fora do sistema */
function RedirecionaPraFora()
{
    session_unset();
    session_destroy();
    header("location: ".$GLOBALS["DNS_SISTEMA"]);
    exit;
}

/** Verifica se ambiente é o mesmo do acesso anterior, e desloga caso não seja */
function VerificarAmbiante()
{
    
    if (is_null($_SESSION["LOCAL_SISTEMA"])) {
        $_SESSION["LOCAL_SISTEMA"] = $GLOBALS["LOCAL_SISTEMA"];
    } elseif ($_SESSION["LOCAL_SISTEMA"] != $GLOBALS["LOCAL_SISTEMA"]) {
        RedirecionaPraFora();
    }
}

/** Cria Senha Aleatória */
function CriaSenha()
{
    $vetor = array("A","B","C","D","E","F","G","H","I",
    "J","K","L","M","N","P","Q","R","S","T","U","V","W",
    "X","Y","Z","a","b","c","d","e","f","g","h","i","j",
    "k","m","n","p","q","r","s","t","u","v","w","x","y",
    "z",2,3,4,5,6,7,8,9);
    srand (time());
    shuffle ($vetor);
    $NewSenha = "";
    for ($i = 0; $i < 8; $i++) { $NewSenha .= $vetor[$i]; }

    return $NewSenha;
}

function RetornaDados($Dado,$Nivel,$Chave)
{
        $Caixa = '';
        if ( is_Array($Dado) ) {
                foreach ($Dado as $Chave2 => $Dado2) {
                        $Caixa.=RetornaDados($Dado2, $Chave, $Chave2);
                }
        } else {
                if ($Nivel) {
                        $Caixa="[".$Nivel."] [".$Chave."] => ".$Dado."\n";
                } else {
                        $Caixa="[".$Chave."] => ".$Dado."\n";
                }
        }

        return $Caixa;
}

function VarreDadosSeguranca($valor)
{
        $Proibido = array( "SELECT ", "INSERT ", "UPDATE ", "DELETE ", "RULE ", "REFERENCES ",/* "TRIGGER ",*/
                            "CREATE ", /*"TEMPORARY ", "EXECUTE ",*/ "USAGE ", "--" );
        $Achou = 0;
        if (is_array($valor)) {
                foreach ($valor as $Dado) {
                        if (VarreDadosSeguranca($Dado)==1) { $Achou = 1; }
                }
        } else {
                foreach ($Proibido as $Palavra) {
                        $Pos=strpos(strtoupper2($valor),$Palavra);
                        if (is_integer($Pos)) {$Achou = 1;}
                }
        }

        return $Achou;
}

function retiraHifen($frase)
{
        if (strpos($frase, "--") === false) {
            return $frase;
        } else {
            return retiraHifen(str_replace("--","-",$frase));
        }

}

function VarreDados(&$valor)
{      
        if (!is_array($valor)) {
            $valor = retiraHifen($valor);
        } else {
            foreach ($valor as $key => $value) {
                VarreDados($valor[$key]);
            }
        }
}

/** finaliza a sessão do acesso do usuário */
function NegarAcesso()
{
                    session_unset();
                    session_destroy();
                    //header("location: http://cohab.recife/pr/secfinancas/portalcompras/programas/");
                    exit;
}

/** Rotina de Segurança # Abreu */
function Seguranca()
{
    $Liberado=true;
    // [CUSTOMIZAÇÃO] - Ajusta verificação de segurança
    if (isset($_SESSION['_eacepocami_']) && !isset($_SESSION['_cgrempcodi_'])) {
        // Verifica se no array do menu da sessão existe o recurso que o usuário está tentando acessar.
    	$recursoAcessado = $_SERVER['SCRIPT_NAME'];
    	$arrayDoMenuNaSessao = $_SESSION['_eacepocami_'];

    	if (!in_array($recursoAcessado, $arrayDoMenuNaSessao)) {
    		RedirecionaPraFora();
    	}
    }
    else if (!isset($_SESSION['_eacepocami_'])) {
    	//header("location: ".$GLOBALS["DNS_SISTEMA"]);
    	header("location: ../index.php");
    	exit;
    }
    //[/CUSTOMIZAÇÃO]
    
    VerificarAmbiante();
    $db = Conexao();
    $sql  = "SELECT EUSUPORESP, AUSUPOFONE ";
    $sql .= "  FROM SFPC.TBUSUARIOPORTAL ";
    $sql .= " WHERE CGREMPCODI = '".$_SESSION['_cgrempcodi_']."' ";
    $sql .= "   AND EUSUPOLOGI = '".$_SESSION['_eusupologi_']."' ";
    $result = $db->query($sql);
    if ( PEAR::isError($result) ) {
        // colocar o codigo de envio de email com erro db
        $db->disconnect();
		EmailErroSQL("Erro no arquivo de listar sanções de funcionários",__FILE__,__LINE__,"Select de listar resultado da procura de fornecedores com sanções no SICREF falhou.",$sql,$result);
    } else {
            $UsuarioNome ="desconhecido (não está no banco)";
            $UsuarioFone ="---";
            while ( $Linha = $result->fetchRow() ) {
                    $UsuarioNome = $Linha[0];
                    $UsuarioFone = $Linha[1];
            }
            if (($UsuarioNome=="")&&($_SESSION['_eusupologi_']!="INTERNET")) {
                EmailErro("Checagem de acesso.", __FILE__, __LINE__, "Usuário não encontrado no banco de dados.");
                RedirecionaPraFora();
            }
    }

    $Mensagem       = "Um e-mail foi enviado ao administrador por motivos de segurança. ";
    $Mensagem      .= "Para maiores informações entrar em contato com o analista responsável pelo sistema.<br><br>";
    $Mensagem      .= "Foi registrado em nossos sistemas o endereço IP: ".$_SERVER["REMOTE_ADDR"]."<br><br>";
    $Mensagem      .= date("d/m/Y H:i:s");
    $MensagemEmail  = "VERIFICAR POSSIVEL TENTATIVA DE INVASÃO\n\n";
    $MensagemEmail .= "IP.......: ".$_SERVER["REMOTE_ADDR"]."\n";
    $MensagemEmail .= "DATA/HORA: ".date("d/m/Y H:i:s")."\n";
    $MensagemEmail .= "GRUPO....: ".$_SESSION['_cgrempcodi_']."\n";
    $MensagemEmail .= "USUÁRIO..: ".$_SESSION['_eusupologi_']." - $UsuarioNome ($UsuarioFone)\n";
    $MensagemEmail .= "PROGRAMA.: ".$_SERVER['SCRIPT_NAME']."\n";
    $MensagemEmail .= "VARIAVEIS: \n\n";


    $CaminhoValido = $GLOBALS["DNS_SISTEMA"];           // Desenvolvimento

    $Pagina = "/".str_replace( $CaminhoValido, "", $_SERVER["SCRIPT_URI"] );


    $PaginaFalsa = 0;

    /* Verifica se a página vem do servidor da Emprel */
    if ( ($_SERVER["REMOTE_ADDR"] == "127.0.0.1") || (strpos( $_SERVER["SCRIPT_URI"], $CaminhoValido ) !== false) ) {
        /* Se array de acesso não existe cria */
        if ( ! is_array($_SESSION['_eacepocami_']) ) { $_SESSION['_eacepocami_'] = array();	}
        
        if ( in_array( $Pagina, $_SESSION['_eacepocami_'] ) ) {
             $PaginaFalsa = 0;
        }
    }

    /* Se a página ainda é falsa destroi variáveis de sessão e redireciona para página inicial da Prefeitura */
    if ($PaginaFalsa == 1) {
        echo"pagina falsa";
        session_unset();
        header("location: ".$GLOBALS["DNS_SISTEMA"]."index.php");
        exit;
    }
    # Validacao de URL com dados # Abreu #
    if (!isset($_SESSION['GetUrl'])) { $_SESSION['GetUrl']=array(); }
    if ($_SERVER["REQUEST_METHOD"]=="GET" and $_SERVER["QUERY_STRING"]) {
            if (!$_SESSION['GetUrl']) {$_SESSION['GetUrl']=array();}
            for ($i=0;$i<count($_SESSION['GetUrl']);$i++) {
                if (!is_null($_SESSION['GetUrl'][$i]) and $_SESSION['GetUrl'][$i]!='') {
                    if (strpos($_SERVER['REQUEST_URI'], str_replace("../","",$_SESSION['GetUrl'][$i]) ) !== false ) {
                            $Liberado=1;
                    }
                }
            }
            if (!$Liberado) {

                    $Texto  = "Grupo: ".$_SESSION['_cgrempcodi_']." Usuário: ".$_SESSION['_eusupologi_']."\n\n";
                    $Texto .= "Programa: ".$_SERVER['SCRIPT_NAME']."\n";
                    $Texto .= "\nRedirecionado para a página inicial.";
                    $Email  = $GLOBALS["EMAIL_SUPORTE"];
                    EnviaEmail($Email, "Erro no Portal de Compras", $Texto, $GLOBALS["EMAIL_FROM"]);
                        session_unset();
                        session_destroy();
                        header("location: ".$GLOBALS["DNS_SISTEMA"]);
                        header("location: ".$GLOBALS["DNS_SISTEMA"]);
                        exit;
                }
    }
}

function TiraSeguranca() { # Luciano #
    $_SESSION['_cgrempcodi_'] = 0; $_SESSION['_cusupocodi_'] = 0;
    $_SESSION['_cperficodi_'] = 0; $_SESSION['_eusupologi_'] = "INTERNET";
    $_SESSION['_eacepocami_'] = array();
}

/** Retira Acentos */
function RetiraAcentos($Str)
{
    $ComAcento = array ("á","à","ã","â","é","è","ê","ë","í","ì","î","ï","ó","ò","õ","ô","ú","ù","û","ü","ç",
        "Á","À","Ã","Â","É","È","Ê","Í","Ì","Î","Ó","Ò","Ô","Õ","Ú","Ù","Û","Ç","~","^","´","`","[","]","º","ª",",","(",")","\"","'","\\","--","Ü","-","€","ç","ã","ê");
    $SemAcento = array ("a","a","a","a","e","e","e","e","i","i","i","i","o","o","o","o","u","u","u","u","c",
        "A","A","A","A","E","E","E","I","I","I","O","O","O","O","U","U","U","C"," "," "," "," ","-","-",".",".",".","-","-","”"," ","\\","-","U"," ","","c","a","e");
    /*
    for ( $i = 0; $i < count($SemAcento); $i++ ) {
            $Str = str_replace ($ComAcento[$i], $SemAcento[$i], $Str);
    }
    */
    $Str = str_replace ($ComAcento, $SemAcento, $Str);

    return $Str;
}
function RetiraAcentosVirgula($Str)
{
    $ComAcento = array ("á","à","ã","â","é","è","ê","ë","í","ì","î","ï","ó","ò","õ","ô","ú","ù","û","ü","ç",
        "Á","À","Ã","Â","É","È","Ê","Í","Ì","Î","Ó","Ò","Ô","Õ","Ú","Ù","Û","Ç","~","^","´","`","[","]","º","ª","(",")","\"","'","\\","--","Ü","-","€","ç","ã","ê");
    $SemAcento = array ("a","a","a","a","e","e","e","e","i","i","i","i","o","o","o","o","u","u","u","u","c",
        "A","A","A","A","E","E","E","I","I","I","O","O","O","O","U","U","U","C"," "," "," "," ","-","-",".",".","-","-","”"," ","\\","-","U"," ","","c","a","e");
    /*
    for ( $i = 0; $i < count($SemAcento); $i++ ) {
            $Str = str_replace ($ComAcento[$i], $SemAcento[$i], $Str);
    }
    */
    $Str = str_replace ($ComAcento, $SemAcento, $Str);

    return $Str;
}

function removeSimbolos($string)
{
    $simbolos =     str_split(CHARS_SIMBOLO);
    $string = str_replace ($simbolos,"",$string);

    return $string;
}

/** Valida o CPF */
function valida_CPF($CPF_CNPJ)
{
    if (!is_numeric($CPF_CNPJ)) return false;
  $s  = $CPF_CNPJ;
    $c  = substr($s,0,9);
    $dv = substr($s,9,2);
    $d1 = 0;
    # Array do CPF #
    $v[0]= substr($s,0,1);
    $v[1]= substr($s,1,1);
    $v[2]= substr($s,2,1);
    $v[3]= substr($s,3,1);
    $v[4]= substr($s,4,1);
    $v[5]= substr($s,5,1);
    $v[6]= substr($s,6,1);
    $v[7]= substr($s,7,1);
    $v[8]= substr($s,8,1);

    for ($i=0;$i<9;$i++) {
           $d1 +=  $v[$i] * ( 10 - $i );
    }
    if ($d1 == 0) {
      return false;
  }
    $d1 = 11 - ($d1 % 11);
  if ($d1 > 9) {
          $d1 = 0;
    }
    $dv = substr($dv,0,1);
    if ($dv != $d1) {
      return false;
    }
    $d1 *= 2;
    for ($i=0;$i<9;$i++) {
           $d1 +=  $v[$i] * ( 11 - $i );
    }
    $d1 = 11 - ($d1 % 11);

    if ($d1 > 9) {
          $d1 = 0;
    }
    $dv =substr($s,10,1);
    if ($dv != $d1) {
          return false;
    }

  return true;
}

function valida_CPFNovo($cpf = null) {

    $cpf = preg_replace('[^0-9]', '', $cpf);
    $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

    // CPF tem que ser igual a 11 digitos
    if (strlen($cpf) != 11) {
        return false;
    }

    //sequências inválidas
    else if ($cpf == '00000000000' ||
        $cpf == '11111111111' ||
        $cpf == '22222222222' ||
        $cpf == '33333333333' ||
        $cpf == '44444444444' ||
        $cpf == '55555555555' ||
        $cpf == '66666666666' ||
        $cpf == '77777777777' ||
        $cpf == '88888888888' ||
        $cpf == '99999999999') {
        return false;

    } else {

        for ($t = 9; $t < 11; $t++) {

            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}

/** Valida o CNPJ */
function valida_CNPJ($CPF_CNPJ)
{
    $s = $CPF_CNPJ;
    $c  = substr($s,0,12);
    $dv = substr($s,12,2);
    $d1 = 0;

    # Array do CPF #
    $v[0]= substr($s,11,1);
    $v[1]= substr($s,10,1);
    $v[2] = substr($s,9,1);
    $v[3] = substr($s,8,1);
    $v[4] = substr($s,7,1);
    $v[5] = substr($s,6,1);
    $v[6] = substr($s,5,1);
    $v[7] = substr($s,4,1);
    $v[8] = substr($s,3,1);
    $v[9] = substr($s,2,1);
    $v[10]= substr($s,1,1);
    $v[11]= substr($s,0,1);
    for ($i=0;$i<12;$i++) {
          $d1 = $d1 + ($v[$i] * ( 2 + ( $i % 8 ) ) );
    }

  if ($d1 == 0) {
      return false;
  }

    $d1 = 11 - ($d1 % 11);
  if ($d1 > 9) {
        $d1 = 0;
    }
    $dv = substr($s,12,1);
    if ($dv != $d1) {
      return false;
    }
    $d1 *= 2;
    for ($i=0;$i<12;$i++) {
           $d1 += $v[$i] * ( 2 + ( ( $i + 1 ) % 8 ) );
    }
    $d1 = 11 - ($d1 % 11);

    if ($d1 > 9) {
          $d1 = 0;
    }
    $dv = substr($s,13,1);

    if ($dv != $d1) {
      return false;
    }

    return true;
}

/** Ler o Arquivo de Layout */
function layout()
{
    require_once 'geral/layout.php';
}

/** Função que Formata Valor de float para moeda no formato Estoques - 4 dígitos após vírgula */
function converte_valor_estoques($valor)
{
        $valor = str_replace(",","",$valor);

        return number_format((float) $valor,4,",",".");
}

function converte_valor_estoques2($valor)
{
        $valor = str_replace(",","",$valor);

        return number_format((float) $valor,2,",",".");
}


/** Função que formata os valores da licitação **/
function converte_valor_licitacao($valor)
{
    $valor = (string) $valor;
    // Verifica se o valor já foi formatado
    if (strstr($valor, ',')) {
        $valorFormatado = $valor;
        // Se não estiver formatado, realiza a formatação
    } else {
        $valorFormatado = number_format((float) $valor,4,",",".");
    }

    return $valorFormatado;
}

/** Função que converte valores de moeda (ex: 24.300,20) para float (24300.20) */
function moeda2float($valor)
{
        $valor2 = str_replace(".","",$valor);
        $valor2 = str_replace(",",".",$valor2);

        return $valor2;
}

/**
 * Valida uma Data # Abreu
 * Alterar função para usar ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})"
 * */
function ValidaData($Data)
{
    $Data = explode("/",$Data);
    $Dia  = $Data[0];
    $Mes  = $Data[1];
    $Ano  = $Data[2];
    if ( (sizeof($Data)>3) || (strlen($Dia)!=2) || (strlen($Mes)!=2) || (strlen($Ano)!=4) ) {
        $MensErro = "Formato incorreto (deve ser NN/NN/NNNN)";
    } elseif ( SoNumeros($Dia) and SoNumeros($Mes) and SoNumeros($Ano) ) {
            if ( ! checkdate( $Mes, $Dia, $Ano ) ) {
                    $MensErro = "Data informada não existe";
            }
    } else {
            $MensErro = "Dia, mês e ano devem possuir apenas números";
    }

    return($MensErro);
}

/** Valida uma Hora */
function ValidaHora($Hora)
{
    $Hora = explode(":",$Hora);
    $Hh = $Hora[0];
    $Mm = $Hora[1];
    $MensErro = "";
    if ($Hh == "" ||  $Mm == "") {
        $MensErro = "Hora (hh:mm)";
    } else {
        if ($Hh < 0 || $Hh > 24) {
            $MensErro = "Hora (hora inválida)";
        } elseif ($Mm < 0 || $Mm > 60) {
            $MensErro = "Minuto (minuto inválido)";
        }
    }

    return($MensErro);
}

/** Data por Extenso */
function DataExtenso()
{
    $mes = Array("Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");
    $diasemana = Array("Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sábado");
    $DataExtenso = $diasemana[date("w")].", ".date("d")." de ".$mes[date("n")-1]." de ".date("Y");

    return $DataExtenso;
}

/** Verifica erros no preenchimento de períodos */
function ValidaPeriodo($DataIni,$DataFim,$Mens,$Programa)
{
        $Mensagem = "";
        $MensErro = "";
        if ($DataIni != "") {
                $MensErro = ValidaData($DataIni);
                if ($MensErro != "") {
                        if ($Mens == 1) { $Mensagem .= ", "; }
                        $Mens      = 1;
                        $Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data inicial: ".$MensErro."</a>";
                }
        }
        if ( ($DataFim != "") and ($Mensagem == "") ) {
                $MensErro = ValidaData($DataFim);
                if ($MensErro != "") {
                        if ($Mens == 1) { $Mensagem .= ", "; }
                        $Mens      = 1;
                        $Mensagem .= "<a href=\"javascript:document.".$Programa.".DataFim.focus();\" class=\"titulo2\">Data final: ".$MensErro."</a>";
                }
        }
        if ( ($DataIni != "") and ($DataFim != "") and ($Mensagem == "") ) {
                echo $Mensagem;
                if ( DataInvertida($DataIni) > DataInvertida($DataFim) ) {
                        if ($Mens == 1) { $Mensagem .= ", "; }
                        $Mens      = 1;
                        $Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data Inicial menor que a Data Final</a>";
                }
        }

        return $Mensagem;
}

/** Formata CPF para 999.999.999-99 */
function FormataCPF($CPF)
{
    if ( strlen($CPF) < 11 ) { $CPF = sprintf("%011s",$CPF); }

    return substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
}

/** Formata CNPJ */
function FormataCNPJ($CNPJ)
{
    if ( strlen($CNPJ) < 14 ) { $CNPJ = sprintf("%014s",$CNPJ); }

    return substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
}

/** Formata como CPF ou CNPJ dependendo da quantidade de números */
function FormataCpfCnpj($Doc)
{
    $Doc = RemoveFormatoCPF_CNPJ($Doc);
    $Tamanho = strlen($Doc);
    if ($Tamanho == 14) {
        $Doc = FormataCNPJ($Doc);
    } elseif ($Tamanho == 11) {
        $Doc = FormataCPF($Doc);
    }

    return $Doc;
}

/** Aciciona zeros para completar o número do CPF ou CNPJ */
function FormataCPF_CNPJ($Doc,$Opcao)
{
    $Tamanho = strlen($Doc);
    if ($Opcao == "CNPJ") {
            if ($Tamanho < 14) {
                $Doc = sprintf("%014s",$Doc);
            }
    } else {
            if ($Tamanho < 11) {
                    $Doc = sprintf("%011s",$Doc);
            }
    }

    return $Doc;
}

function RemoveFormatoCPF_CNPJ($CNPJ)
{
    $CNPJ = str_replace(".","",$CNPJ);
    $CNPJ = str_replace("-","",$CNPJ);
    $CNPJ = str_replace("/","",$CNPJ);

    return $CNPJ;
}

// Busca o primeiro dia do mês corrente no ano anterior e último dia do mês e ano correntes
function DataMesAno() {
    $Data  = date("d/m/Y");
    $Data  = explode("/",$Data);
    $Dia   = $Data[0];
    
    if ($Data[2] > 1) {
        $AnoInicial = $Data[2] - 1;
        $Ano        = $Data[2];
    } else {
        $MesInicial = 12;
        $Ano        = $Ano -1;
        $Mes        = $Data[1];
    }

    $Mes   = $Data[1];
    $Dias1 = array("31","28","31","30","31","30","31","31","30","31","30","31");
    $Dias2 = array("31","29","31","30","31","30","31","31","30","31","30","31");
    $i     = $Mes - 1;

    if ($Ano%4 == 0) {
        $FimMes = $Dias2[$i];
    } else {
        $FimMes = $Dias1[$i];
    }

    $DataMes[0] = ("01/".$Mes."/".$AnoInicial);
    $DataMes[1] = ($FimMes."/".$Mes."/".$Ano);

    return $DataMes;
}

/** Busca o primeiro e último dia do mês corrente # ( Utilizar função date() para pegar ultimo dia ) */
function DataMes()
{
    $Data  = date("d/m/Y");
    $Data  = explode("/",$Data);
    $Dia   = $Data[0];
    $Mes   = $Data[1];
    $Ano   = $Data[2];
    $Dias1 = array("31","28","31","30","31","30","31","31","30","31","30","31");
    $Dias2 = array("31","29","31","30","31","30","31","31","30","31","30","31");
    $i     = $Mes - 1;
    if ($Ano%4 == 0) {$FimMes = $Dias2[$i];} else {$FimMes = $Dias1[$i];}
    $DataMes[0] = ("01/".$Mes."/".$Ano);
    $DataMes[1] = ($FimMes."/".$Mes."/".$Ano);

    return $DataMes;
}

/** Função que checa se foi digitado nome+espaço+sobrenome - Álvaro */
function NomeSobrenome($nome)
{
    if ( strpos(trim($nome)," ") ) {
            return true;
    } else {
            return false;
    }
}

/** So Numeros # (Abreu) */
function SoNumeros($Numero)
{
    return (preg_match( "/^\d+$/", trim($Numero) ));
}

/** Monta o Número de Dotação */
function NumeroDotacao($Funcao,$Subfuncao,$Programa,$Orgao,$Unidade,$TipoProjAtiv,$ProjAtividade,$Elemento1,$Elemento2,$Elemento3,$Elemento4,$Fonte)
{
    if ($Funcao == "") { $Funcao = 0; }
    if ($Subfuncao == "") { $Subfuncao = 0; }
    if ($Programa == "") { $Programa = 0; }
    $Dotacao  = sprintf("%02d",$Orgao).sprintf("%02d",$Unidade).".";
    $Dotacao .= sprintf("%02d",$Funcao).".".sprintf("%03d",$Subfuncao).".";
    $Dotacao .= sprintf("%04d",$Programa).".".$TipoProjAtiv.".";
    $Dotacao .= sprintf("%03d",$ProjAtividade).".".$Elemento1.".".$Elemento2.".";
    $Dotacao .= sprintf("%02d",$Elemento3).".".sprintf("%02d",$Elemento4).".";
    $Dotacao .= sprintf("%02d",$Fonte);

    return $Dotacao;
}


/** trunca um texto, colocando a substringFinal no final
* 	tamanho- máximo tamanho da caracteres permitido */
function truncarTexto($str, $tamanho)
{
    $strfim="...";
    $tam=$tamanho-strlen($strfim);
    if (strlen($str)>$tam) {
        $str=substr($str,0,$tam);
        $str.="...";

        return $str;
    }

    return $str;
}

/** Remove caracteres em nomes de arquivos que causam erro no sistema
* OBS.: O erro ocorre devido a uma diferença na codificação de texto (erro não ocorre mais desde o UTF8, mas é bom remover caracteres estranhos)
* entre a configuração do servidor web e um nome de arquivo no Linux */
function tratarNomeArquivo($ArquivoNome)
{
        $caracteres= str_split($GLOBALS["ARQUIVO_CARACTERES_INVALIDOS"]);
        $chrSubstitui= str_split($GLOBALS["ARQUIVO_CARACTERES_SUBSTITUICAO"]);
        $ArquivoNomeTratado= str_replace($caracteres,$chrSubstitui,$ArquivoNome);

        return $ArquivoNomeTratado;
}

/** remove um item de um array ordenado */
function array_removerItem($array, $posItem)
{
    $tamanho = count($array);
    if ($posItem<0) {
        assercao(false,"valor de posição menor que zero");
    } elseif ($posItem>=$tamanho) {
        assercao(false,"valor de posição maior que tamanho do array");
    } else {
        for ($itr=$posItem;$itr<$tamanho;$itr++) {
                unset($array[$itr]);
                if ($itr<$tamanho-1) { //ultimo ítem é apenas excluido
                    if (is_null($array[$itr+1])) {
                        $array[$itr]=null;
                    } else {
                        $array[$itr]=$array[$itr+1];
                    }
                }
        }
    }

    return $array;
}

/** Função que pre-supóe uma condição, e aborta o programa caso esta condição não esteja satisfeita */
function assercao($condicao,$mensagemDeErro, $db=null)
{
    if (!$condicao) {
        //fechar banco caso esteja no meio de uma transação (ver funções de transação em funcoesBanco.php)
        if ($GLOBALS["iniciouTransacaoBanco"]) {
            if (is_null($db)) {
                $db = $GLOBALS["db"]; // se não foi informado o db, verificar na raiz
            }
            if (!is_null($db)) {
                cancelarTransacao($db);
                $db->disconnect();
            } else {

            }
        }
        if(
            ($GLOBALS["LOCAL_SISTEMA"]==CONST_NOMELOCAL_PRODUCAO) or
            ($GLOBALS["LOCAL_SISTEMA"]==CONST_NOMELOCAL_HOMOLOGACAO)
        ){
            EmailErroSistema("Checagem de asserção falhou", $mensagemDeErro);
            exit;
        } else {
            # Em desenvolvimento, além de mandar email, imprimir na tela o erro
            $excecao = new Excecao($mensagemDeErro);
            echo "<html><body>";
            echo txt2html($excecao->toString());
            echo "</body></html>";
            EmailErroSistema("Checagem de asserção falhou", $mensagemDeErro);
            exit;
        }
    }
}

/** Limpa o whitelist de arquivos de usuários que podem ser acessados pelo usuário  */
function resetArquivoAcesso()
{
    $_SESSION['arquivo'] = array();
}

/** Adiciona um arquivo de usuários na whitelist de arquivos que podem ser acessados pelo usuário */
function addArquivoAcesso($arquivo)
{
    $_SESSION['arquivo'][count($_SESSION['arquivo'])] = $arquivo;
}


/** função converte caracteres (>'") */
function mb_htmlentities($str)
{
    return str_replace( array("<", ">","'"), array("&lt;", "&gt;","&#039;"), htmlspecialchars($str, ENT_NOQUOTES,'UTF-8'));
}

/** função para validar Emails (a ser implementado) */
function validaEmail($email)
{
    $resultado = true;

    return $resultado;
}

/** função para validar Emails (a ser implementado) */
function isEmailCorporativo($email)
{
    $padrao='/^[\\w-]+(\\.[\\w-]+)*@recife\.pe\.gov\.br$/';
    $result = preg_match($padrao,$email);

    return $result;
}

/** Função para testar format monetario ex.:  999.999.999,99
* se o ponto ou a virgula estiver fora do lugar ele critica */
function validaMonetario($num)
{
  $num = trim ($num);
  $tam = strlen($num);
  $lim=$tam-1;
  $ret=true;

  for ($i=$lim;$i>=0;$i--) {
    $char=substr($num,$i,1);
    if ($i==$lim || $i==$lim-1) { if  ( !validaMonNum($char)  ) $ret=false; }
    if ($i==$lim-2) { if  ( !validaMonVirg($char)  ) $ret=false; }
    if ($i==$lim-3 || $i==$lim-4 || $i==$lim-5) { if  ( !validaMonNum($char)  ) $ret=false; }
    if ($i==$lim-6) { if  ( !validaMonPont($char)  ) $ret=false; }
    if ($i==$lim-7 || $i==$lim-8 || $i==$lim-9) { if  ( !validaMonNum($char)  ) $ret=false; }
    if ($i==$lim-10) { if  ( !validaMonPont($char)  ) $ret=false; }
    if ($i==$lim-11 || $i==$lim-12 || $i==$lim-13) { if  ( !validaMonNum($char)  ) $ret=false; }

  }

  return $ret;
}

function validaMonVirg($char)
{
  if ( $char!="," ) return false; else return true;
}
function validaMonPont($char)
{
  if ( $char!="." ) return false; else return true;
}
function validaMonNum($char)
{
  if ( $char<"0" or $char>"9"  ) return false; else return true;
}

/** Mudar codificação de ISO 8859-1  para UTF-8 */
function to_utf8($linha)
{
  return   utf8_encode($linha); //iconv('iso-8859-1','utf-8',$linha) ;
}

/** Mudar codificação de UTF-8  para  ISO8859-1 */
function to_iso($linha)
{
  return   utf8_decode($linha); //iconv('utf-8','iso-8859-1',$linha) ;
}

/** Função para substituir o strtoupper */
function strtoupper2($string)
{
  return strtoupper($string);
}

/** Função para substituir o strtolower */
function strtolower2($string)
{
  return mb_strtolower($string,'UTF-8');
}


/** Função que compara floats, a partir de uma precisão.
*   No PHP há problemas de se comparar floats. Comparações falham quando os floats são iguais. exemplo: .17 <> 0.17 <> 0.1700 */
function comparaFloat($float1, $operador, $float2, $precisao = 4)
{
    $float1corrigido = number_format((float) $float1,$precisao,".","");
    $float2corrigido = number_format((float) $float2,$precisao,".","");
    //$float1corrigido = (float) $float1;
    //$float2corrigido = (float) $float2;
    assercao((0<$precisao) and (11>$precisao), "Precisão deve ser de 1 a 10");
    assercao(($operador == "<") or ($operador == ">") or ($operador == "==") or ($operador == "<=") or ($operador == ">=") or ($operador == "!="), "Operador deve ser < ou > ou ==");
    $limitePrecisao = "0";
    $limitePrecisao = number_format((float) $limitePrecisao,$precisao,".","");
    $limitePrecisao .= "1"; //para ficar além de precisão, 0,00001
    if ($operador == "==") {
        return (abs($float1corrigido - $float2corrigido)<$limitePrecisao);
    } elseif ($operador == "!=") {
        return (!(abs($float1corrigido - $float2corrigido)<$limitePrecisao));
    } elseif ($operador == "<") {
        //echo $float1corrigido.$operador;
        //echo $float2corrigido;
        //echo "[".($float1corrigido < $float2corrigido)."]";
        return ($float1corrigido < $float2corrigido);
    } elseif ($operador == ">") {
        return ( $float1corrigido> $float2corrigido );
    } elseif ($operador == ">=") {
        return ( $float1corrigido>= $float2corrigido );
    } elseif ($operador == "<=") {
        return ( $float1corrigido<= $float2corrigido );
    }
}

function carregarVariavel($nomeVariavel, $metodo)
{
    if ($metodo=="POST") {
        $GLOBALS[$nomeVariavel]=$_POST[$nomeVariavel];
    } elseif ($metodo=="GET") {
        $GLOBALS[$nomeVariavel]=$_GET[$nomeVariavel];
    } elseif ($metodo=="SESSION") {
        $GLOBALS[$nomeVariavel]=$_SESSION[$nomeVariavel];
    } else {
        assercao(false, "Método de envio inválido.");
    }
}


/** Retorna total valor estimado da licitação
 *  */
function totalValorEstimado($db,$processo,$ano,$grupo,$comissao,$orgao)
{
    $sql  = " select item.clicpoproc as proc ,sum( item.aitelpqtso * item.vitelpunit ) as total ";
    $sql .= " from sfpc.tbitemlicitacaoportal item  ";
    $sql .= " where item.clicpoproc = $processo ";
    $sql .= " and item.alicpoanop = $ano ";
    $sql .= " and item.cgrempcodi = $grupo ";
    $sql .= " and item.ccomlicodi = $comissao ";
    $sql .= " and item.corglicodi = $orgao ";
    $sql .= " group by item.clicpoproc ";
    //print_r($sql);
    $result	= executarTransacao($db, $sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);

    return  $row->total;
}

/** Verifica se ja usuario esta ativo ou existe no mesmo grupo
 *  */

// [CUSTOMIZAÇÃO]
/**
 * [getIdFasesConcluidas description]
 *
 * @return array [description]
 */
function getIdFasesConcluidas($db = null)
{
    VerificarAmbiante();
    $db = Conexao();
    $sql = "SELECT * FROM SFPC.TBFASES WHERE AFASESORDE >= 96 OR AFASESORDE IN (50,55)";
    $result = $db->query($sql);
    $fases = array();
    while($item = $result->fetchRow()) {
        $fases[] = $item[0];
    }

    return $fases;
}

/**
 * [getIdFasesEmAndamento description]
 *
 * @return array [description]
 */
function getIdFasesEmAndamento($db = null)
{
    VerificarAmbiante();
    $db = Conexao();
    $sql = "SELECT * FROM SFPC.TBFASES WHERE AFASESORDE < 96 AND AFASESORDE NOT IN (50,55)";
    $result = $db->query($sql);
    $fases = array();
    while($item = $result->fetchRow()) {
        $fases[] = $item[0];
    }

    return $fases;
}

function getPathNovoLayout()
{
	return 'app';
}

function getUriRedirecionamentoFornecedores($nomeDoPrograma, $recursoOrigem)
{
	$seguimentoUri = explode("/", $recursoOrigem);
	$origem = $seguimentoUri[4];
	
	return $GLOBALS["DNS_SISTEMA"] . "$origem/$nomeDoPrograma";
}

function getUriCaptcha(){
	// Define captcha
	$uriCaptcha = '/common/rotinas_php/Gerajpeg/Gerajpeg.php';
	if (strpos($GLOBALS["LOCAL_SISTEMA"], CONST_NOMELOCAL_DESENVOLVIMENTO) !== false) {
		$uriCaptcha = '../common/rotinas_php/Gerajpeg/Gerajpeg.php';
	}
	if (strpos($GLOBALS["LOCAL_SISTEMA"], CONST_NOMELOCAL_HOMOLOGACAO) !== false) {
		$uriCaptcha = '../common/rotinas_php/Gerajpeg/Gerajpeg.php';
	}
	return $uriCaptcha;
}

/*
 * Remove os caracteres especiais de uma string
 */
function removeCaracteresEspeciais($string = null)
{
    if (is_null($string) == false) {
        $string = str_replace('€', '', $string);
        $string = str_replace('£', '', $string);
        $string = str_replace('¢', '', $string);
        $string = str_replace('¬', '', $string);
        $string = str_replace('§', '', $string);
        $textoLimpo = preg_replace('/[^A-Za-z0-9\. -\(\)\[\]\{\}\!\ª\º\?\%çÇáàãâäÁÀÃÂÄéèêëÉÈÊËíìîïÍÌÎÏóòõôöÓÒÕÔÖúùûüÚÙÛÜñÑ]/', '', $string);
    } else {
        $textoLimpo = '';
    }

    return $textoLimpo;
}

/**
 * Retornar os orgãos pilotos
 * 
 * @param $db
 * 
 * @return array
 */
function getOrgaosPilotos($db)
{
    $sql = " SELECT EPARGEOPRP FROM SFPC.TBPARAMETROSGERAIS WHERE 1 = 1";
    $result = executarTransacao($db, $sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

    return $row->epargeoprp;
}

function getOrgaoUsuarioLogado($db)
{
    $cgrempcodi = $_SESSION['_cgrempcodi_'];
    $cusupocodi = $_SESSION['_cusupocodi_'];
    $orgaos = array();

    $sql = " SELECT distinct Orgao.corglicodi FROM sfpc.tbusuariocentrocusto AS UsuarioCusto
        INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON UsuarioCusto.ccenposequ = CentroCusto.ccenposequ
        INNER JOIN sfpc.tborgaolicitante AS Orgao ON Orgao.corglicodi = CentroCusto.corglicodi
        WHERE UsuarioCusto.cgrempcodi = $cgrempcodi AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
    
    $resultado = executarSQL($db, $sql);
    
    while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
        $orgaos[] = $orgao->corglicodi;
    }

    return $orgaos;
}

function listaFornecedoresComSancoes($db, $Argumento, $Todos, $Palavra, $ItemPesquisa) {
    $sql  = "SELECT	CASE WHEN F.AFORCRCCGC IS NULL THEN F.AFORCRCCPF ELSE F.AFORCRCCGC END AS CGCCPF, ";
	$sql .= "		F.NFORCRRAZS, FTS.EFORTSDESC , FS.DFORSISITU, FS.EFORSIMOTI, FS.DFORSIEXPI, F.AFORCRSEQU ";
	$sql .= "FROM 	SFPC.TBFORNECEDORCREDENCIADO F, ";
	$sql .= "		SFPC.TBFORNSITUACAO FS, ";
	$sql .= "		SFPC.TBFORNECEDORTIPOSITUACAO FTS ";
	$sql .= "WHERE 	F.AFORCRSEQU = FS.AFORCRSEQU";
	$sql .= "		AND FS.CFORTSCODI = FTS.CFORTSCODI ";
	$sql .= "		AND FS.CFORTSCODI IN (2, 3, 4, 5, 6) ";		
	$sql .= "		AND FS.DFORSISITU = (SELECT MAX(FS2.DFORSISITU) ";
	$sql .= "							 FROM SFPC.TBFORNSITUACAO FS2 ";
	$sql .= "							 WHERE F.AFORCRSEQU = FS2.AFORCRSEQU) ";
			
		if ($Todos != null) {
			if (!$Palavra != null) {
				if ($ItemPesquisa == "CPF") {
					$sql .= " AND F.AFORCRCCPF = '".$Argumento."%' ";
				} elseif ($ItemPesquisa == "CNPJ") {
					$sql .= " AND F.AFORCRCCGC LIKE '".$Argumento."%' ";
				} elseif ($ItemPesquisa == "RAZAO") {
					$sql .= " AND ( F.NFORCRRAZS ILIKE '%".$Argumento."%' ) ";
				}
			} else {
				if ($ItemPesquisa == "CPF") {
					$sql .= " AND F.AFORCRCCPF = '".$Argumento."' ";
				} elseif ($ItemPesquisa == "CNPJ") {
					$sql .= " AND F.AFORCRCCGC = '".$Argumento."' ";
				} elseif ($ItemPesquisa == "RAZAO") {
					$sql .= " AND ( F.NFORCRRAZS = '".$Argumento."' ) ";
				}
			}
		}

    $sql .= "ORDER BY FTS.EFORTSDESC, F.NFORCRRAZS ";

    $result = $db->query($sql);

	if (PEAR::isError($result)) {
		$db->disconnect();
		EmailErroSQL("Erro no arquivo de listar sanções de funcionários",__FILE__,__LINE__,"Select de listar resultado da procura de fornecedores com sanções no SICREF falhou.",$sql,$result);
	}
    
    return $result;
}

function checarSituacaoAtualFornecedor($codForn) {
    $db = Conexao();

    $sql  = "SELECT CFORTSCODI ";
    $sql .= "FROM   SFPC.TBFORNSITUACAO ";
    $sql .= "WHERE  AFORCRSEQU = " . $codForn;
    $sql .= " ORDER BY DFORSISITU DESC ";
    $sql .= "LIMIT 1 ";
    
    $result = $db->query($sql);
    $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

    if (PEAR::isError($result)) {
        $db->disconnect();
        EmailErroSQL("Erro na função de checar situação atual do fornecedor",__FILE__,__LINE__,"Checagem da situação atual do fornecedor no SICREF falhou.",$sql,$result);
    }

    $situacaoAtual = $row->cfortscodi;

    return $situacaoAtual;
}

/**
 * Função utilizada para  evitar o injeção de sql malicioso
 * Função usada no sistema todo.
 */
function anti_injection($sql)
{
    // remove palavras que contenham sintaxe sql
    preg_match("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/",$sql,$matches);
    $sql = @preg_replace($matches,"",$sql);
    $sql = trim($sql);//limpa espaços vazio
    $sql = strip_tags($sql);//tira tags html e php
    $sql = addslashes($sql);//Adiciona barras invertidas a uma string
    return $sql;
}

 // função que tranforma data  de 03/02/2020 para 2020-02-03 ou de 2020-02-03  para 03/02/2020
  function date_transform($data,$today = false,$separador="/"){
    $dataBr = '/^(0[1-9]|[1-2][0-9]|3[0-1])[\/](0[1-9]|1[0-2])[\/](19|20)[0-9]{2}$/';
    $dataSql = '/^(19|20)[0-9]{2}[\-](0[1-9]|1[0-2])[\-](0[1-9]|[1-2][0-9]|3[0-1])$/';
    if(preg_match($dataSql,$data,$retorno)){
        $date = explode('-', $retorno[0]);
        if($separador == ""){
            $date_transform = $date[2].'/'.$date[1].'/'.$date[0];
        }else{
            $date_transform = $date[2].$separador.$date[1].$separador.$date[0];
        }
        return $date_transform;
    }else if(preg_match($dataBr,$data,$retorno)){
        $date = explode('/', $retorno[0]);
        $date_transform = $date[2].'-'.$date[1].'-'.$date[0];
        return $date_transform;
    }elseif($data == "" && $today == true){
        return date("d/m/Y");
    }else{
        return $data;
    }
}

// função que tranforma data  de 03/02/2020 10:50:00 para 2020-02-03 10:50:00 ou de 2020-02-03 10:50:00  para 03/02/2020 10:50:00
function datetime_transform($datetime) {
    $dataBr ="/^(\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{1,2}):(\d{2}):(\d{2})$/";
    $dataSql = "/^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{2}):(\d{2})$/";
    if(preg_match($dataSql, $datetime, $dt)){
        $new = date("d/m/Y H:i:s", mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]));
    }else if(preg_match($dataBr, $datetime, $dt)){
        $new = date("Y-m-d H:i:s", mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[1], $dt[3]));
    }
    return $new;

}