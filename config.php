<?php
/**
 * Portal de Compras
 * 
 * Programa:   config.php
 * Objetivo:   Definir variáveis de configuração usadas por todo sistema
 * Autor:      Ariston Cordeiro
 * Observação: Variáveis de ambiente devem estar em configvars.php
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     03/10/2008
 * Objetivo: Variáveis de produção descomentadas
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     06/11/2008
 * Objetivo: Nome do título de mensagens de email alterados para "[Portal de Compras][Ambiente]" para padronização.
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     15/12/2008
 * Objetivo: Alteração da data inicial e final do inventário de "21/12/2008 e 02/01/2008" para "23/12/2008 e 31/12/2008",
 *           conforme Diario Oficial - 25/Nov/2008 - Edição 135 - DECRETO Nº. 24.165 DE 24 DE NOVEMBRO DE 2008.
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     09/03/2009
 * Objetivo: Removi todas variáveis de ambiente e as coloquei em um arquivo separado configvars.php. Cada ambiente deve ter um configvars.php configurado para ele.
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     10/03/2009
 * Objetivo: Setando CAMINHO_ROOT na mão, pois servidor não está retornando o $_SERVER["DOCUMENT_ROOT"] quando acessado pelo CRON (por rotinas agendadas em produção).
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     10/06/2010
 * Objetivo: Adicionado variáveis $GLOBALS["ARQUIVO_CARACTERES_INVALIDOS"] e $GLOBALS["ARQUIVO_CARACTERES_SUBSTITUICAO"]
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     29/10/2010
 * Objetivo: (Redmine #395) Adicionado variáveis com dados mostrados em Controles > Informação
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     23/02/2011
 * Objetivo: #407 Red Mine- Movendo lista de unidades permitidas de pre-materiais de CadPreMaterialIncluir.php. Adicionando unidade RESMA.
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     05/09/2011
 * Objetivo: Adicionando constantes de tipo de mensagem
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     10/11/2011
 * Objetivo: Trazendo variáveis de configvars.php
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Heraldo Botelho
 * Data:     12/04/2013
 * Objetivo: Criar o array de e variáveis globais contendo os emails dos administradores do sistema==>$GLOBALS["EMAIL_ADMINISTRADORES"]
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     04/02/2021
 * Objetivo: #243388
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * */ 

 # Descomentar para bloquear o sistema #
/*
 * echo "Sistema fora do ar para atualização. Tente novamente mais tarde.";
 * exit;
 */

// ECHO "F1- ".$GLOBALS["LOCAL_SISTEMA"]." ";

// Carregando variáveis opcionais
if (file_exists('../configvars.php')) {
    include_once ('../configvars.php');
}

// ECHO "F2- ".$GLOBALS["LOCAL_SISTEMA"]." ";

// Variáveis mostradas na página Controles > Informação
define("VERSAO", "4.8");

// Periodo Inventario - CUIDADO AO ALTERAR - BLOQUEIA OS ALMOXARIFADOS
$InventarioDataInicial = "2013-12-01";
$InventarioDataFinal   = "2014-01-07";

// Tipos de mensagem (modificar para constante para define() depois que integrar as funcionalidades da bank system)
// VARIÁVEIS MANTIDAS APENAS PARA COMPATIBILIDADE. FAVOR USAR AS CONSTANTES
$GLOBALS["TIPO_MENSAGEM_ATENCAO"] = 1;
$GLOBALS["TIPO_MENSAGEM_ERRO"]    = 2;
define("TIPO_MENSAGEM_ATENCAO", 1);
define("TIPO_MENSAGEM_ERRO", 2);

// Variáveis que variam entre teste/homologação/produção
define("CONST_NOMELOCAL_DESENVOLVIMENTO", "DESENVOLVIMENTO");
define("CONST_NOMELOCAL_HOMOLOGACAO", "HOMOLOGAÇÃO");
define("CONST_NOMELOCAL_PRODUCAO", "PRODUÇÃO");

/*
 * define("CONST_DATASOURCE_LABEL_POSTGRESQL_DESENVOLVIMENTO", "DSPODBPOTEMP_UTF8SFPC");
 * define("CONST_DATASOURCE_LABEL_ORACLE_DESENVOLVIMENTO", "DSORDBDESENVSFPC");
 *
 * define("CONST_DATASOURCE_LABEL_POSTGRESQL_HOMOLOGACAO", "DSPODBPOTHOMOLOGA_UTF8SFPC");
 * define("CONST_DATASOURCE_LABEL_ORACLE_HOMOLOGACAO", "DSORDBORHEMPSFPC");
 *
 * define("CONST_DATASOURCE_LABEL_POSTGRESQL_PRODUCAO", "DSPODBPOPEMP_UTF8SFPC");
 * define("CONST_DATASOURCE_LABEL_ORACLE_PRODUCAO", "DSORDBDBEMPRELSFPC");
 */

$ServerName = $_SERVER['SERVER_NAME'];
$scriptUrl  = $_SERVER["SCRIPT_URL"];

$GLOBALS["DNS_DOMINIO"] = "http://" . $ServerName . "/";

$scriptUrlPartes = explode('/', $scriptUrl);

if ($ServerName == 'cohab.recife' and $scriptUrlPartes[1] == 'portalcompras') {
    define("URL_TRANSPARENCIA", "http://cohab.recife/pr/gabinete/cgm/cgpt/codigos/web/geral/home.php");

    // Desenvolvimento
    if (is_null($GLOBALS["CAMINHO_SISTEMA"])) {
        $GLOBALS["CAMINHO_SISTEMA"] = '/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/programas/'; // caminho para este sistema
    }

    if (is_null($GLOBALS["LOCAL_SISTEMA"])) {
        $GLOBALS["LOCAL_SISTEMA"] = CONST_NOMELOCAL_DESENVOLVIMENTO;
    }

    if (is_null($GLOBALS["PASTA_SISTEMA"])) {
        $GLOBALS["PASTA_SISTEMA"] = "portalcompras/";
    }

    if (is_null($GLOBALS["DNS_SISTEMA"])) {
        $GLOBALS["DNS_SISTEMA"] = $GLOBALS["DNS_DOMINIO"];
    }

    if (is_null($GLOBALS["DATASOURCE_LABEL_POSTGRESQL"])) {
        $GLOBALS["DATASOURCE_LABEL_POSTGRESQL"] = CONST_DATASOURCE_LABEL_POSTGRESQL_DESENVOLVIMENTO;
    }

    if (is_null($GLOBALS["DATASOURCE_LABEL_ORACLE"])) {
        $GLOBALS["DATASOURCE_LABEL_ORACLE"] = CONST_DATASOURCE_LABEL_ORACLE_DESENVOLVIMENTO;
    }

    if (is_null($GLOBALS["BD_NOME"])) {
        $GLOBALS["BD_USUARIO"]        = "us_sfpc";
        $GLOBALS["BD_SENHA"]          = "select";
        $GLOBALS["BD_SERVIDOR"]       = "saojose.recife";
        $GLOBALS["BD_NOME"]           = "dbpotemp_utf8";
        $GLOBALS["BD_ORACLE_USUARIO"] = "us_portal";
        $GLOBALS["BD_ORACLE_SENHA"]   = "portal";
        $GLOBALS["BD_ORACLE_NOME"]    = "dbdesenv";
    }
} elseif ($ServerName == 'cdu.recife') {
    define("URL_TRANSPARENCIA", "http://cdu.recife/pr/gabinete/cgm/cgpt/codigos/web/geral/home.php");

    // Homologação
    if (is_null($GLOBALS["CAMINHO_SISTEMA"])) {
        $GLOBALS["CAMINHO_SISTEMA"] = '/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/programas/'; // caminho para este sistema
    }

    if (is_null($GLOBALS["LOCAL_SISTEMA"])) {
        $GLOBALS["LOCAL_SISTEMA"] = CONST_NOMELOCAL_HOMOLOGACAO;
    }

    if (is_null($GLOBALS["PASTA_SISTEMA"])) {
        $GLOBALS["PASTA_SISTEMA"] = "portalcompras/";
    }

    if (is_null($GLOBALS["DNS_SISTEMA"])) {
        $GLOBALS["DNS_SISTEMA"] = $GLOBALS["DNS_DOMINIO"] . $GLOBALS["PASTA_SISTEMA"];
    }

    if (is_null($GLOBALS["DATASOURCE_LABEL_POSTGRESQL"])) {
        $GLOBALS["DATASOURCE_LABEL_POSTGRESQL"] = CONST_DATASOURCE_LABEL_POSTGRESQL_HOMOLOGACAO;
    }

    if (is_null($GLOBALS["DATASOURCE_LABEL_ORACLE"])) {
        $GLOBALS["DATASOURCE_LABEL_ORACLE"] = CONST_DATASOURCE_LABEL_ORACLE_HOMOLOGACAO;
    }

    if (is_null($GLOBALS["BD_NOME"])) {
        $GLOBALS["BD_USUARIO"]        = "us_sfpc";
        $GLOBALS["BD_SENHA"]          = 'select';
        $GLOBALS["BD_SERVIDOR"]       = "saojose.recife";
        $GLOBALS["BD_NOME"]           = "dbpotemp_utf8";
        $GLOBALS["BD_ORACLE_USUARIO"] = "us_portal";
        $GLOBALS["BD_ORACLE_SENHA"]   = "portal";
        $GLOBALS["BD_ORACLE_NOME"]    = "dbhomolog";
    }

    ini_set('display_errors', 0);
    error_reporting(E_ALL ^ E_NOTICE);
} elseif ($ServerName == 'varzea.recife' or $ServerName == 'www.recife.pe.gov.br') {
    define("URL_TRANSPARENCIA", "http://portaltransparencia.recife.pe.gov.br/");

    // Produção
    if (is_null($GLOBALS["CAMINHO_SISTEMA"])) {
        $GLOBALS["CAMINHO_SISTEMA"] = '/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/programas/'; // caminho para este sistema
    }

    if (is_null($GLOBALS["LOCAL_SISTEMA"])) {
        $GLOBALS["LOCAL_SISTEMA"] = CONST_NOMELOCAL_PRODUCAO;
    }

    if (is_null($GLOBALS["PASTA_SISTEMA"])) {
        $GLOBALS["PASTA_SISTEMA"] = "portalcompras/";
    }

    if (is_null($GLOBALS["DNS_SISTEMA"])) {
        $GLOBALS["DNS_SISTEMA"] = $GLOBALS["DNS_DOMINIO"] . $GLOBALS["PASTA_SISTEMA"];
    }

    if (is_null($GLOBALS["DATASOURCE_LABEL_POSTGRESQL"])) {
        $GLOBALS["DATASOURCE_LABEL_POSTGRESQL"] = CONST_DATASOURCE_LABEL_POSTGRESQL_PRODUCAO;
    }

    if (is_null($GLOBALS["DATASOURCE_LABEL_ORACLE"])) {
        $GLOBALS["DATASOURCE_LABEL_ORACLE"] = CONST_DATASOURCE_LABEL_ORACLE_PRODUCAO;
    }

    // necessário tentar carregar de novo configvars.php, caso ele não foi encontrado da 1a vez, já que o CAMINHO_SISTEMA agora é conhecido
    /*
     * if(file_exists ( $GLOBALS["CAMINHO_SISTEMA"].'..\configvars.php' )){
     * include_once($GLOBALS["CAMINHO_SISTEMA"].'..\configvars.php');
     * }else if(file_exists ( $GLOBALS["CAMINHO_SISTEMA"].'..\..\configvars.php' )){
     * include_once($GLOBALS["CAMINHO_SISTEMA"].'..\..\configvars.php');
     * }
     */

    if (is_null($GLOBALS["BD_NOME"])) {
        // echo "VARIÁVEIS DE BANCO NÃO FORAM INICIALIZADAS. ABORTANDO.";
        // exit();
        $GLOBALS["BD_USUARIO"]        = "sfpc";
        $GLOBALS["BD_SENHA"]          = 'pcsf-02';
        //$GLOBALS["BD_SERVIDOR"]       = "casaforte.recife";
        $GLOBALS["BD_SERVIDOR"]       = "apipucos.recife";
        $GLOBALS["BD_NOME"]           = "dbpopemp_utf8";
        $GLOBALS["BD_ORACLE_USUARIO"] = "us_portal";
        $GLOBALS["BD_ORACLE_SENHA"]   = "portal#13";
        $GLOBALS["BD_ORACLE_NOME"]    = "dbemprel";
    }
} else {
    $GLOBALS["NOME_DESENVOLVEDOR"] = 'Equipe do Portal de Compras';

    if (! is_null($GLOBALS["NOME_DESENVOLVEDOR"])) {
        ini_set('display_errors', 0);
        error_reporting(E_ALL ^ E_NOTICE);
        // Servidor sendo rodado em algum ambiente fora do padrão que é informado pelo configvars
        $GLOBALS["CAMINHO_SISTEMA"] = '/var/www/html/desenvolvimento/'; // caminho para este sistema
        $GLOBALS["LOCAL_SISTEMA"]   =  ( $_SERVER["HTTP_HOST"] === "setubal.recife:8088") ? CONST_NOMELOCAL_DESENVOLVIMENTO : CONST_NOMELOCAL_HOMOLOGACAO;
        $GLOBALS["PASTA_SISTEMA"]   = "portalcompras/";
        $GLOBALS["DNS_SISTEMA"]     = $_SERVER['PHP_SELF'];
        var_dump($GLOBALS["DNS_SISTEMA"]);die;
        

        $GLOBALS["DATASOURCE_LABEL_ORACLE"] = CONST_DATASOURCE_LABEL_ORACLE_HOMOLOGACAO;
        $GLOBALS["BD_ORACLE_USUARIO"]       = "us_portal";
        $GLOBALS["BD_ORACLE_SENHA"]         = "portal";
        $GLOBALS["BD_ORACLE_NOME"]          = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=192.168.0.123)(PORT=1521))(CONNECT_DATA=(SID=dborhemp)(SERVER=DEDICATED)))";
		
        $GLOBALS["DATASOURCE_LABEL_POSTGRESQL"] =  //CONST_DATASOURCE_LABEL_POSTGRESQL_HOMOLOGACAO;
        
        // BD versão 8
        ///*
        $GLOBALS["BD_USUARIO"]  = "us_sfpc";
		$GLOBALS["BD_SENHA"]    = "select";
		$GLOBALS["BD_SERVIDOR"] = "saojose.recife";
        $GLOBALS["BD_NOME"]     = "dbpotemp_utf8";
        //*/
        
        // BD versão 9
        /*
        $GLOBALS["BD_USUARIO"]  = "sfpc";
        $GLOBALS["BD_SENHA"]    = "sfpc";
        $GLOBALS["BD_SERVIDOR"] = "saojose2.recife";
        $GLOBALS["BD_NOME"]     = "dbpotstefanini";
        */
        
        // $GLOBALS["BD_USUARIO"]  = "us_sfpc";
        // $GLOBALS["BD_SENHA"]    = 'us_sfpc';
        // $GLOBALS["BD_SERVIDOR"] = "192.168.33.24";
        // $GLOBALS["BD_NOME"]     = "sfpc";

        /*$GLOBALS["BD_USUARIO"] = "postgres";
        $GLOBALS["BD_SENHA"] = 'postgres';
        $GLOBALS["BD_SERVIDOR"] = "172.27.138.59:5432";
        $GLOBALS["BD_NOME"] = "postgres";*/

        $GLOBALS["EMAIL_SUPORTE"]         = "joao.madson@recife.pe.gov.br, rossana@recife.pe.gov.br, osmar.celestino@recife.pe.gov.br, eliakim.dev@gmail.com, has_lucas@hotmail.com";
        $GLOBALS["EMAIL_ADMINISTRADORES"] = "joao.madson@recife.pe.gov.br, rossana@recife.pe.gov.br, osmar.celestino@recife.pe.gov.br, eliakim.dev@gmail.com, has_lucas@hotmail.com";
        $GLOBALS["CAMINHO_SISTEMA"]       = $GLOBALS["CAMINHO_SISTEMA"];
        $GLOBALS["CAMINHO_UPLOADS"]       = $GLOBALS["CAMINHO_SISTEMA"] . 'uploads/'; // caminho para salvar arquivos de upload
        $GLOBALS["CAMINHO_LOGS"]          = $GLOBALS["CAMINHO_SISTEMA"] . 'uploads/'; // caminho para salvar arquivos de log
        $GLOBALS["DNS_UPLOADS"]           = $GLOBALS["DNS_DOMINIO"] . $GLOBALS["PASTA_SISTEMA"] . 'uploads/';
        $GLOBALS["CAMINHO_EMAIL"]         = $GLOBALS["CAMINHO_SISTEMA"] . 'common/phpmailer/';
        $GLOBALS["CAMINHO_PDF"]           = $GLOBALS["CAMINHO_SISTEMA"] . "import/fpdf/";
    } else {
        if ($scriptUrlPartes[1] == 'emprel' and $scriptUrlPartes[2] == 'desenv'/*and $scriptUrlPartes[6] == 'sfpc'*/) {
            // Servidor sendo rodado no ambiente de algum desenvolvedor do Portal
            $nomeDesenvolvedor = $scriptUrlPartes[3];
            
            // Apenas o analista responsável e o desenvolvedor receberão emails
            // $GLOBALS["EMAIL_SUPORTE"] = "ariston@recife.pe.gov.br, ".$nomeDesenvolvedor."@recife.pe.gov.br";
            define("URL_TRANSPARENCIA", "http://cohab.recife/pr/gabinete/cgm/cgpt/codigos/web/geral/home.php");

            // Necessário tentar carregar de novo configvars.php, caso ele não foi encontrado da 1a vez, já que o CAMINHO_SISTEMA agora é conhecido
            if (file_exists($GLOBALS["CAMINHO_SISTEMA"] . 'configvars.php')) {
                include_once ($GLOBALS["CAMINHO_SISTEMA"] . 'configvars.php');
            }

            $array = explode("sfpc/", $_SERVER['SCRIPT_FILENAME']);
            
            if (is_null($GLOBALS["CAMINHO_SISTEMA"]))
                $GLOBALS["CAMINHO_SISTEMA"] = $array[0] . 'sfpc/'; // caminho para este sistema
            if (is_null($GLOBALS["LOCAL_SISTEMA"]))
                $GLOBALS["LOCAL_SISTEMA"] = CONST_NOMELOCAL_DESENVOLVIMENTO . "_" . $nomeDesenvolvedor;
            if (is_null($GLOBALS["PASTA_SISTEMA"]))
                $GLOBALS["PASTA_SISTEMA"] = "sfpc/";
            $array = explode("sfpc/", $_SERVER['SCRIPT_URI']);
            if (is_null($GLOBALS["DNS_SISTEMA"]))
                $GLOBALS["DNS_SISTEMA"] = $array[0] . 'sfpc/';
            if (is_null($GLOBALS["DATASOURCE_LABEL_POSTGRESQL"]))
                $GLOBALS["DATASOURCE_LABEL_POSTGRESQL"] = CONST_DATASOURCE_LABEL_POSTGRESQL_DESENVOLVIMENTO;
            if (is_null($GLOBALS["DATASOURCE_LABEL_ORACLE"]))
                $GLOBALS["DATASOURCE_LABEL_ORACLE"] = CONST_DATASOURCE_LABEL_ORACLE_DESENVOLVIMENTO;

            if (is_null($GLOBALS["BD_NOME"])) {
                $GLOBALS["BD_USUARIO"]        = "us_sfpc";
                $GLOBALS["BD_SENHA"]          = "select";
                $GLOBALS["BD_SERVIDOR"]       = "saojose.recife";
                $GLOBALS["BD_NOME"]           = "dbpotemp_utf8";
                $GLOBALS["BD_ORACLE_USUARIO"] = "us_portal";
                $GLOBALS["BD_ORACLE_SENHA"]   = "portal";
                $GLOBALS["BD_ORACLE_NOME"]    = "dbdesenv";
            }
        } else {
            echo 'Não foi possível iniciar o sistema. O servidor não foi reconhecido nem foi informado pelo config.php. Favor contactar o administrador do sistema. Abortando.';
            exit();
        }
    }
    if (is_null($GLOBALS["DNS_SISTEMA"])) {
        echo 'Não foi possível iniciar o sistema. Variável "DNS_SISTEMA" está nula. Abortando.';
        exit();
    }
}

if (is_null($GLOBALS["EMAIL_SUPORTE"])) {
    $GLOBALS["EMAIL_SUPORTE"] = "joao.madson@recife.pe.gov.br, rossana@recife.pe.gov.br, osmar.celestino@recife.pe.gov.br, eliakim.dev@gmail.com, has_lucas@hotmail.com";
}

if (is_null($GLOBALS["EMAIL_ADMINISTRADORES"])) {
    $GLOBALS["EMAIL_ADMINISTRADORES"] = "joao.madson@recife.pe.gov.br, rossana@recife.pe.gov.br, osmar.celestino@recife.pe.gov.br, eliakim.dev@gmail.com, has_lucas@hotmail.com";
}

$_SESSION["LOCAL_SISTEMA"] = $GLOBALS["LOCAL_SISTEMA"];

// Identificação do sistema e o local em que está rodando em títulos em texto (Ex.:Assunto em email)
if ($GLOBALS["LOCAL_SISTEMA"] == CONST_NOMELOCAL_PRODUCAO) {
    $GLOBALS["LOCAL_SISTEMA_TITULO"] = "[Portal de Compras]";
} else {
    $GLOBALS["LOCAL_SISTEMA_TITULO"] = "[Portal de Compras][DESENV]";
}

$GLOBALS["PASTA_ORACLE"] = "oracle/";

// Pastas dos módulos do sistema, em relação a PASTA_SISTEMA_LOCAL
$GLOBALS["PASTA_ESTOQUES"]     = "estoques/";
$GLOBALS["PASTA_FORNECEDORES"] = "fornecedores/";
$GLOBALS["PASTA_LICITACOES"]   = "licitacoes/";
$GLOBALS["PASTA_MATERIAIS"]    = "materiais/";
$GLOBALS["PASTA_MIDIA"]        = "midia/";

// # Caminhos locais (vistos pelo servidor)
define("CAMINHO_SISTEMA", $GLOBALS["CAMINHO_SISTEMA"]);

if (is_null($GLOBALS["CAMINHO_UPLOADS"]))
    $GLOBALS["CAMINHO_UPLOADS"] = '/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/uploads/'; // Caminho para salvar arquivos de upload
if (is_null($GLOBALS["CAMINHO_LOGS"]))
    $GLOBALS["CAMINHO_LOGS"] = '/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/uploads/'; // Caminho para salvar arquivos de log
if (is_null($GLOBALS["DNS_UPLOADS"]))
    $GLOBALS["DNS_UPLOADS"] = $GLOBALS["DNS_DOMINIO"] . 'pr/secfinancas/portalcompras/uploads/'; // Caminho para baixar arquivos de upload
                                                                                                 // dependências
if (is_null($GLOBALS["CAMINHO_EMAIL"]))
    $GLOBALS["CAMINHO_EMAIL"] = '/home/wwwdisco1/portal/html/common/phpmailer/';
    // $GLOBALS["CAMINHO_PDF"] = '/home/wwwdisco1/portal/html/common/fpdf/';
if (is_null($GLOBALS["CAMINHO_PDF"]))
    $GLOBALS["CAMINHO_PDF"] = $GLOBALS["CAMINHO_SISTEMA"] . "import/fpdf/";

// Parâmetros para envio de e-mail direto pelos programas.#
$GLOBALS["EMAIL_FROM"] = "From: portalcompras@recife.pe.gov.br";

// Variáveis para substituição de caracteres em nomes de arquivo
$GLOBALS["ARQUIVO_CARACTERES_INVALIDOS"]    = "çÇáéíóúÁÉÍÓÚãõÃÕàèìòùÀÈÌÒÙäëïöüÄËÏÖÜâêîôûÂÊÎÔÛ '!@#$%¨&*(),:}{^~/\\\"<>;[]{}§´`'º°ª";
$GLOBALS["ARQUIVO_CARACTERES_SUBSTITUICAO"] = "cCaeiouAEIOUaoAOaeiouAEIOUaeiouAEIOUaeiouAEIOU________________________________ooa";

// variáveis de caracteres
define("CHARS_STRING_UPCASE", "ABCDEFGHIJKLMNOPQRSTUVWXYZÇÁÉÍÓÚÀÈÌÒÙÃÕÂÔÛ ");
define("CHARS_STRING_LOWCASE", "abcdefghijklmnopqrstuvwxyzçáéíóúàèìòùãõâôû ");
define("CHARS_STRING", CHARS_STRING_UPCASE + $GLOBALS["CHARS_STRING_LOWCASE"]);
define("CHARS_INTEIRO_POSITIVO", "0123456789");
define("CHARS_INTEIRO", CHARS_INTEIRO_POSITIVO + "-");
define("CHARS_NUMERO", CHARS_INTEIRO + ".,");
define("CHARS_ALFANUMERICO", CHARS_STRING + CHARS_INTEIRO_POSITIVO);
define("CHARS_SIMBOLO", "'!@#$%¨&*(),:}{^~/\\\"<>;[]{}§´`'º°ª_-.");

// Definição das variáveis de Concatenação de Array com sort #
$SimboloConcatenacaoArray = "Æ";
$SimboloConcatenacaoDesc  = "æ";

// Unidades de medida que aparecem em inclusão e edição de pre-materiais
$GLOBALS["PREMATERIAL_UNIDADES"] = "'ARROBA','GRAMA','GROSA','LITRO','METRO','METRO QUADRADO','METRO CÚBICO','MILHEIRO','PAR','POLEGADA','QUILOGRAMA','RESMA', 'TONELADA','UNIDADE','MILILITRO'";

// Variáveis obsoletas, mantidas apenas para compatibilidade
// ----------------------------------------------------------------------------------------------------------------------------
$CaminhoImagens = "../" . $GLOBALS["PASTA_MIDIA"];
$RedirecionaJanela = $GLOBALS["DNS_SISTEMA"];
$Mail = $GLOBALS["EMAIL_SUPORTE"];

// Ações padrões para uma página
define("ACAO_PAGINA_INCLUIR", 1);
define("ACAO_PAGINA_MANTER", 2);
define("ACAO_PAGINA_ACOMPANHAR", 3);
define("ACAO_PAGINA_EXCLUIR", 4);
?>