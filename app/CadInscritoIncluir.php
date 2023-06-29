<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package Novo Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version GIT: v1.19.0-6-ge9180d4
 */

/**
 * HISTORICO DE ALTERACAO NO PROGRAMA
 * --------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     03/07/2015
 * Objeitvo: CR74534 - Estouro no tamanho do campo em cadastro de fornecedores
 * Versão:   v1.22.0-4-g991f379
 * --------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     07/07/2015
 * Objetivo: CR91081 - Inscrição de fornecedor - Cadastro Internet
 * Versão:   v1.23.0-5-g7d5bcf6
 * --------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     25/07/2018
 * Objetivo: Tarefa Redmine 80154
 * --------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     15/07/2015
 * Objetivo: [CR Redmine 74534] Estouro no tamanho do campo em cadastro de fornecedores
 * Versão:   v1.23.0-5-g7d5bcf6.
 * --------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     18/09/2018
 * Objetivo: Tarefa Redmine 202539
 * --------------------------------------------------------------------------
 * Alterado: Ernesto Ferreira
 * Data:     08/11/2018
 * Objetivo: Tarefa Redmine 205834
 * --------------------------------------------------------------------------
 * Alterado: João Madson   
 * Data:     22/01/2021
 * Objetivo: Tarefa Redmine 233484 
 * --------------------------------------------------------------------------
 */

 if (! require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

require_once('../funcoes.php');
require_once('../fornecedores/funcoesFornecedores.php');
require_once("../fornecedores/funcoesDocumento.php");

$tpl = new TemplateAppPadrao("templates/CadInscritoIncluir.html", "CadInscritoIncluir");

/**
 */
class EscapaValor
{

    /**
     *
     * @param string $texto
     */
    public static function escape($texto)
    {
        // $db = Conexao();
        // return addslashes(pg_escape_string($db,$texto));
        return anti_injection($texto);
    }

    /**
     *
     * @param integer $inteiro
     */
    public static function trataInteiro($inteiro)
    {
        $inteiro = str_replace(".", "", $inteiro);
        $inteiro = str_replace(",", "", $inteiro);

        return $inteiro;
    }
}

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Origem                       = $_POST['Origem'];
    $Destino                      = $_POST['Destino'];
    $_SESSION['Botao']            = $_POST['Botao'];
    $_SESSION['Des']              = $Destino;
    $_SESSION['Ori']              = $Origem;
    $_SESSION["AutorizaNome"]     = $Nome;
    $_SESSION["AutorizaRegistro"] = $RegistroAutorizacao;
    $_SESSION["AutorizaData"]     = $DataAutorizacao;

    if ($Origem == "A") {
        # Variáveis do Formulário A #
        if ($_SESSION['CPF_CNPJ'] != $_POST['CPF_CNPJ']) {
            $_SESSION['Irregularidade'] = "";
        }
        $_SESSION['CPF_CNPJ']         = $_POST['CPF_CNPJ'];
        $_SESSION['CERTID_ESTRANG']         = $_POST['ESTRANG'];
        $_SESSION['TipoCnpjCpf']      = $_POST['TipoCnpjCpfInscrito'];
        $_SESSION['Critica']          = $_POST['Critica'];
        $_SESSION['MicroEmpresa']     = $_POST['MicroEmpresa'];
        $_SESSION['Identidade']       = strtoupper2(trim($_POST['Identidade']));
        $_SESSION['OrgaoUF']          = strtoupper2(trim($_POST['OrgaoUF']));
        $_SESSION['RazaoSocial']      = strtoupper2(trim($_POST['RazaoSocial']));
        $_SESSION['NomeFantasia']     = strtoupper2(trim($_POST['NomeFantasia']));
        $_SESSION['CEP']              = $_POST['CEP'];
        $_SESSION['CEPInformado']     = $_POST['CEP'];
        $_SESSION['CEPAntes']         = $_POST['CEPAntes'];
        $_SESSION['Logradouro']       = strtoupper2(trim($_POST['Logradouro']));
        $_SESSION['Numero']           = $_POST['Numero'];
        $_SESSION['Complemento']      = strtoupper2(trim($_POST['Complemento']));
        $_SESSION['Bairro']           = strtoupper2(trim($_POST['Bairro']));
        $_SESSION['Cidade']           = strtoupper2(trim($_POST['Cidade']));
        $_SESSION['UF']               = $_POST['UF'];
        $_SESSION['DDD']              = $_POST['DDD'];
        $_SESSION['Telefone']         = $_POST['Telefone'];
        $_SESSION['Email']            = trim($_POST['Email']);
        $_SESSION['Email2']           = trim($_POST['Email2']);
        $_SESSION['EmailPopup']       = $_SESSION['Email'];
        $_SESSION['Fax']              = $_POST['Fax'];
        $_SESSION['RegistroJunta']    = $_POST['RegistroJunta'];
        $_SESSION['DataRegistro']     = $_POST['DataRegistro'];
        $_SESSION['NomeContato']      = strtoupper2(trim($_POST['NomeContato']));
        $_SESSION['CPFContato']       = $_POST['CPFContato'];
        $_SESSION['CargoContato']     = strtoupper2(trim($_POST['CargoContato']));
        $_SESSION['DDDContato']       = $_POST['DDDContato'];
        $_SESSION['TelefoneContato']  = $_POST['TelefoneContato'];
        $_SESSION['TipoHabilitacao']  = $_POST['TipoHabilitacao'];
        $_SESSION['TipoNatureza']  = $_POST['TipoNatureza'];
        $_SESSION['NoSocios']         = $_POST['NoSocios']; // noSocios - conta o número de sócios, incluindo os sócios deletados
        $_SESSION['SociosCPF_CNPJ']   = $_POST['SociosCPF_CNPJ']; // SociosCPF_CNPJ - Possui o CPF/CNPJ dos sócios. Também inclui os CPF/CNPJs deletados
        $_SESSION['SociosNome']       = $_POST['SociosNome']; // SociosNome - Possui os nomes dos sócios. Note que o array inclui os nomes dos sócios deletados, que não serão adicionados
        $_SESSION['SocioNovoNome']    = $_POST['SocioNovoNome']; // SocioNovoNome - Nome do novo sócio a ser adicionado
        $_SESSION['SocioNovoCPF']     = $_POST['SocioNovoCPF']; // SocioNovoCPF - CPF do sócio a ser adicionado
        $_SESSION['SocioSelecionado'] = $_POST['SocioSelecionado']; // SocioSelecionado - Sócio selecionado para algum comando (apenas usado no comando de remover sócio)
        $_SESSION['MostrarNovoSocio'] = false; // MostrarNovoSocio - Informa que o nome e o CPF do sócio devem ser mostrados nas caixas de texto. Usado para o caso de correção do campo
    }

    if ($Origem == "B") {
        # Variáveis do Formulário B #
        if ($_SESSION['InscMercantil'] != $_POST['InscMercantil']) {
            $_SESSION['InscricaoValida'] = "";
        }
        $_SESSION['InscEstadual']        = $_POST['InscEstadual'];
        $_SESSION['InscMercantil']       = $_POST['InscMercantil'];
        $_SESSION['InscOMunic']          = $_POST['InscOMunic'];
        $_SESSION['Certidao']            = $_POST['Certidao'];
        $_SESSION['DataCertidaoComp']    = $_POST['DataCertidaoComp'];
        $_SESSION['CertidaoObrigatoria'] = $_POST['CertidaoObrigatoria'];
        $_SESSION['DataCertidaoOb']      = $_POST['DataCertidaoOb'];
        $_SESSION['CheckComplementar']   = $_POST['CheckComplementar'];
    }

    if ($Origem == "C") {
        # Variáveis do Formulário C #
        $_SESSION['CapSocial']            = $_POST['CapSocial'];
        $_SESSION['CapIntegralizado']     = $_POST['CapIntegralizado'];
        $_SESSION['Patrimonio']           = $_POST['Patrimonio'];
        $_SESSION['IndLiqCorrente']       = $_POST['IndLiqCorrente'];
        $_SESSION['IndLiqGeral']          = $_POST['IndLiqGeral'];
        $_SESSION['IndEndividamento']     = $_POST['IndEndividamento'];
        $_SESSION['IndSolvencia']         = $_POST['IndSolvencia'];
        $_SESSION['Banco1']               = strtoupper2(trim($_POST['Banco1']));
        $_SESSION['Agencia1']             = strtoupper2(trim($_POST['Agencia1']));
        $_SESSION['ContaCorrente1']       = strtoupper2(trim($_POST['ContaCorrente1']));
        $_SESSION['Banco2']               = strtoupper2(trim($_POST['Banco2']));
        $_SESSION['Agencia2']             = strtoupper2(trim($_POST['Agencia2']));
        $_SESSION['ContaCorrente2']       = strtoupper2(trim($_POST['ContaCorrente2']));
        $_SESSION['DataBalanco']          = $_POST['DataBalanco'];
        $_SESSION['DataNegativa']         = $_POST['DataNegativa'];
        $_SESSION['DataContratoEstatuto'] = $_POST['DataContratoEstatuto'];
    }

    if ($Origem == "D") {
        # Variáveis do Formulário D #
        $_SESSION['RegistroEntidade'] = $_POST['RegistroEntidade'];
        $_SESSION['NomeEntidade']     = strtoupper2(trim($_POST['NomeEntidade']));
        $_SESSION['DataVigencia']     = $_POST['DataVigencia'];
        $_SESSION['RegistroTecnico']  = $_POST['RegistroTecnico'];
        $_SESSION['AutorizaNome']     = strtoupper2(trim($_POST['AutorizaNome']));
        $_SESSION['AutorizaRegistro'] = $_POST['AutorizaRegistro'];
        $_SESSION['AutorizaData']     = $_POST['AutorizaData'];
        $_SESSION['CheckAutorizacao'] = $_POST['CheckAutorizacao'];
        $_SESSION['CheckMateriais']   = $_POST['CheckMateriais'];
        $_SESSION['CheckServicos']    = $_POST['CheckServicos'];
        $_SESSION['Cumprimento']      = $_POST['Cumprimento'];
        $_SESSION['EmailPopup']       = $_POST['EmailPopup'];
        $_SESSION['Email']            = $_SESSION['EmailPopup'];
    }
    
	if ($Origem == "E") {
		# Variáveis do Formulário E #

		$_SESSION['DDocumento']  = $_POST['DDocumento'];
		$_SESSION['tipoDoc'] = $_POST['tipoDoc'];
		$_SESSION['tipoDocDesc'] = $_POST['tipoDocDesc'];
		$_SESSION['obsDocumento'] = $_POST['obsDocumento'];

		// dados para retorno em caso de erro
		


	}
} else {
    $_SESSION['Irregularidade']  = $_GET['Irregularidade'];
    $_SESSION['InscricaoValida'] = $_GET['InscricaoValida'];
    $_SESSION['Mensagem']        = html_entity_decode(urldecode($_GET['Mensagem']));
    $_SESSION['Mens']            = $_GET['Mens'];
    $_SESSION['Tipo']            = $_GET['Tipo'];
    $Origem                      = $_REQUEST['Origem'];
    $Destino                     = $_GET['Destino'];
}

# Reseta variáveis que não são usadas quando não é licitação #
if ($_SESSION['TipoHabilitacao'] != 'L' && $Origem != "C") {
    # Origem C
    $_SESSION['CapSocial']            = null;
    $_SESSION['CapIntegralizado']     = null;
    $_SESSION['Patrimonio']           = null;
    $_SESSION['IndLiqCorrente']       = null;
    $_SESSION['IndLiqGeral']          = null;
    $_SESSION['IndEndividamento']     = null;
    $_SESSION['IndSolvencia']         = null;
    $_SESSION['DataBalanco']          = null;
    $_SESSION['DataNegativa']         = null;
    $_SESSION['DataContratoEstatuto'] = null;

    # Origem D
    $_SESSION['Cumprimento']      = null;
    $_SESSION['RegistroEntidade'] = null;
    $_SESSION['NomeEntidade']     = null;
    $_SESSION['DataVigencia']     = null;
    $_SESSION['RegistroTecnico']  = null;
    $_SESSION['AutorizaNome']     = null;
    $_SESSION['AutorizaRegistro'] = null;
    $_SESSION['AutorizaData']     = null;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Origem == "A" or $Origem == "") {
    $_SESSION['DestinoCons'] = $Destino;
} elseif ($Origem == "B") {
    $_SESSION['DestinoInsc'] = $Destino;
} elseif ($Origem == "C") {
} elseif ($Origem == "D" ) {
} elseif ($Origem == "E" && $_SESSION['Botao'] != "Incluir") {
	//CriticaAbaDocumentos();
}

# Limpa as variaveis que estão na sessão #
if ($Origem == "" or is_null($_SESSION['CPF_CNPJ'])) {
    LimparSessao();
    $_SESSION['TipoHabilitacao'] = "L";
}

# Aba de Habilitação Jurídica - Formulário A #
if (($Origem == "A" or $Origem == "")) {
    if ($_SESSION['Botao'] == "A") {
        $Destino = "B";
    }
}

# Aba de Regularidade Fiscal - Formulário B #
if ($Origem == "B") {
    if ($_SESSION['Botao'] == "B") {
        $Destino = "C";
    }
}

# Aba de Qualificação Econômica e Financeira - Formulário C #
if ($Origem == "C") {
    if ($_SESSION['Botao'] == "C") {
        $Destino = "D";
    }
}

# Aba de Qualificação Técnica - Formulário D #
if ($Origem == "D") {
	if ($_SESSION['Botao'] == "D") {
		$Destino = "E";
	}
}

# Salva Dados nas Tabelas #
if ($Origem == "E") {
    if ($_SESSION['Botao'] == "Incluir") {
        //CriticaAbaRegularidadeFiscal(); // Verifica Critica do Formulário B
        //CriticaAbaQualificEconFinanceira(); // // Verifica Critica do Formulário C
        //CriticaAbaQualificTecnica(); // // Verifica Critica do Formulário D
        //CriticaAbaDocumentos();//Verifica Critica do Formulário E

        if (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) != 11 and strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) != 14 &&   empty($_SESSION['CERTID_ESTRANG'])) {
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 1;
            $_SESSION['Mensagem'] = "CPF ou CNPJ inválido";
        } else {
            # Verifica se o Fornecedor já foi Cadastrado #
            $db = Conexao();
            $db->query("BEGIN TRANSACTION");

            $sql = "SELECT  COUNT(AFORCRSEQU)
                    FROM    SFPC.TBFORNECEDORCREDENCIADO
                    WHERE ";
                if($_SESSION['CPF_CNPJ']){
                    if (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 11) {
                        $sql .= "AFORCRCCPF = '".removeSimbolos($_SESSION['CPF_CNPJ'])."' ";
                    } elseif (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 14) {
                        $sql .= "AFORCRCCGC = '".removeSimbolos($_SESSION['CPF_CNPJ'])."' ";
                        
                    }
                }else{
                    $sql .= "aforcridfe = '".removeSimbolos($_SESSION['CERTID_ESTRANG'])."' ";
                }
                

            $result = executarSQL($db, $sql);

            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $rows = $result->numRows();

                if ($rows != 0) {
                    $Linha = $result->fetchRow();
                    $ExisteFornecedor = $Linha[0];

                    if ($ExisteFornecedor == 0) {
                        # Verifica se o Fornecedor Já foi Inscrito #
                        $sqlpre = "SELECT   CPREFSCODI, EPREFOMOTI
                                   FROM     SFPC.TBPREFORNECEDOR
                                   WHERE ";
                                if($_SESSION['CPF_CNPJ']){
                                     # Colocar CPF/CGC para o Inscrito #
                                    if (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 11) {
                                        $PreForCPF = "'".removeSimbolos($_SESSION['CPF_CNPJ'])."'";
                                        $PreForCGC = "NULL";
                                        $sqlpre .= "APREFOCCPF = $PreForCPF ";
                                    } elseif (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 14) {
                                        $PreForCGC = "'".removeSimbolos($_SESSION['CPF_CNPJ'])."'";
                                        $PreForCPF = "NULL";
                                        $sqlpre .= "APREFOCCGC = $PreForCGC ";
                                       
                                    }
                                }else{
                                    $PreForEstrang = "'".removeSimbolos($_SESSION['CERTID_ESTRANG'])."'";
                                    $PreForCPF = "NULL";
                                    $sqlpre .= "aprefoidfe = $PreForEstrang ";
                                }
                               
                                
                        
                        $respre = executarSQL($db, $sqlpre);
                        
                        if (PEAR::isError($respre)) {
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        } else {
                            $Linha = $respre->fetchRow();
                            $Situacao = $Linha[0];
                            $Motivo = $Linha[1];

                            if ($Situacao == "") {
                                if ($_SESSION['Cumprimento'] == "S" or $_SESSION['TipoHabilitacao'] != "L" || $_SESSION['CERTID_ESTRANG'] ) {
                                    # Recupera a último sequencial e incrementa mais um #
                                    $sqlpre = "SELECT   MAX(APREFOSEQU) AS Maximo
                                               FROM     SFPC.TBPREFORNECEDOR ";
                                      
                                    $respre = executarSQL($db, $sqlpre);
                                    
                                    if (PEAR::isError($respre)) {
                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                    } else {
                                        $_SESSION['cont'] ++;
                                        $Maximo = $respre->fetchRow();
                                        $PreForSeq = $Maximo[0] + 1;
                                        $PreForSituacao = 1; // Coloca a Situação do Pré-Cadastro - EM ANÁLISE
                                        $DataAtual = date("Y-m-d H:i:s"); // Data de Geração do Pré-Cadastro
                                        $ontem = new DateTime("now - 1 day");
                                        $dataOntem = $ontem->format('Y-m-d H:i:s');
                                        
                                        # Atribuindo Valor NULL - Formulário A #
                                        if ($_SESSION['MicroEmpresa'] == "") {
                                            $MicroEmpresa = "NULL";
                                        } else {
                                            $MicroEmpresa = "'" . $_SESSION['MicroEmpresa'] . "'";
                                        }
                                        
                                        if ($_SESSION['Identidade'] == "") {
                                            $Identidade = "NULL";
                                        } else {
                                            $Identidade = "'" . $_SESSION['Identidade'] . "'";
                                        }
                                        
                                        if ($_SESSION['OrgaoUF'] == "") {
                                            $OrgaoUF = "NULL";
                                        } else {
                                            $OrgaoUF = "'" . $_SESSION['OrgaoUF'] . "'";
                                        }
                                        
                                        if ($_SESSION['NomeFantasia'] == "") {
                                            $NomeFantasia = "NULL";
                                        } else {
                                            $NomeFantasia = "'" . EscapaValor::escape($_SESSION['NomeFantasia']) . "'";
                                        }
                                        
                                        if ($_SESSION['DDD'] == "") {
                                            $DDD = "NULL";
                                        } else {
                                            $DDD = $_SESSION['DDD'];
                                        }
                                        
                                        if ($_SESSION['Numero'] == "") {
                                            $Numero = "NULL";
                                        } else {
                                            $Numero = $_SESSION['Numero'];
                                        }
                                        
                                        if ($_SESSION['Complemento'] == "") {
                                            $Complemento = "NULL";
                                        } else {
                                            $Complemento = EscapaValor::escape($_SESSION['Complemento']);
                                        }
                                        
                                        if ($_SESSION['Telefone'] == "") {
                                            $Telefone = "NULL";
                                        } else {
                                            $Telefone = "'" . $_SESSION['Telefone'] . "'";
                                        }
                                        
                                        if ($_SESSION['Fax'] == "") {
                                            $Fax = "NULL";
                                        } else {
                                            $Fax = "'" . $_SESSION['Fax'] . "'";
                                        }
                                        
                                        if ($_SESSION['NomeContato'] == "") {
                                            $NomeContato = "NULL";
                                        } else {
                                            $NomeContato = "'" . EscapaValor::escape($_SESSION['NomeContato']) . "'";
                                        }
                                        
                                        if ($_SESSION['CPFContato'] == "") {
                                            $CPFContato = "NULL";
                                        } else {
                                            $CPFContato = "'" . removeSimbolos($_SESSION['CPFContato']) . "'";
                                        }
                                        
                                        if ($_SESSION['CargoContato'] == "") {
                                            $CargoContato = "NULL";
                                        } else {
                                            $CargoContato = "'" . $_SESSION['CargoContato'] . "'";
                                        }
                                        
                                        if ($_SESSION['DDDContato'] == "") {
                                            $DDDContato = "NULL";
                                        } else {
                                            $DDDContato = $_SESSION['DDDContato'];
                                        }
                                        
                                        if ($_SESSION['TelefoneContato'] == "") {
                                            $TelefoneContato = "NULL";
                                        } else {
                                            $TelefoneContato = "'" . $_SESSION['TelefoneContato'] . "'";
                                        }
                                        
                                        if ($_SESSION['RegistroJunta'] == "") {
                                            $RegistroJunta = "NULL";
                                        } else {
                                            $RegistroJunta = "'" . $_SESSION['RegistroJunta'] . "'";
                                        }
                                        
                                        if ($_SESSION['DataRegistro'] == "") {
                                            $DataRegistroInv = "NULL";
                                        } else {
                                            $DataRegistroInv = "'" . substr($_SESSION['DataRegistro'], 6, 4) . "-" . substr($_SESSION['DataRegistro'], 3, 2) . "-" . substr($_SESSION['DataRegistro'], 0, 2) . "'";
                                        }
                                        
                                        if ($_SESSION['Email'] == "" or $_SESSION['Email'] == "NULL") {
                                            $Email = "NULL";
                                        } else {
                                            $Email = "'" . $_SESSION['Email'] . "'";
                                        }
                                        
                                        if ($_SESSION['Email2'] == "" or $_SESSION['Email2'] == "NULL") {
                                            $Email2 = "NULL";
                                        } else {
                                            $Email2 = "'" . $_SESSION['Email2'] . "'";
                                        }
                                        
                                        # Atribuindo Valor NULL - Formulário B
                                        if ($_SESSION['InscMercantil'] == "") {
                                            $InscMercantil = "NULL";
                                        } else {
                                            $InscMercantil = removeSimbolos($_SESSION['InscMercantil']);
                                        }
                                        
                                        if ($_SESSION['InscEstadual'] == "") {
                                            $InscEstadual = "NULL";
                                        } else {
                                            $InscEstadual = removeSimbolos($_SESSION['InscEstadual']);
                                        }
                                        
                                        if ($_SESSION['InscOMunic'] == "") {
                                            $InscOMunic = "NULL";
                                        } else {
                                            $InscOMunic = removeSimbolos($_SESSION['InscOMunic']);
                                        }

                                        # Atribuindo Valor NULL - Formulário C #
                                        if ($_SESSION['CapSocial'] == "") {
                                            $CapSocial = "NULL";
                                        } else {
                                            $CapSocial = str_replace(".", "", $_SESSION['CapSocial']);
                                            $CapSocial = str_replace(",", ".", $CapSocial);
                                        }
                                        
                                        if ($_SESSION['Patrimonio'] == "") {
                                            $Patrimonio = "NULL";
                                        } else {
                                            $Patrimonio = str_replace(".", "", $_SESSION['Patrimonio']);
                                            $Patrimonio = str_replace(",", ".", $Patrimonio);
                                        }
                                        
                                        if ($_SESSION['CapIntegralizado'] == "") {
                                            $CapIntegralizado = "NULL";
                                        } else {
                                            $CapIntegralizado = str_replace(".", "", $_SESSION['CapIntegralizado']);
                                            $CapIntegralizado = str_replace(",", ".", $CapIntegralizado);
                                        }
                                        
                                        if ($_SESSION['IndLiqCorrente'] == "") {
                                            $IndLiqCorrente = "NULL";
                                        } else {
                                            $IndLiqCorrente = str_replace(".", "", $_SESSION['IndLiqCorrente']);
                                            $IndLiqCorrente = str_replace(",", ".", $IndLiqCorrente);
                                        }
                                        
                                        if ($_SESSION['IndLiqGeral'] == "") {
                                            $IndLiqGeral = "NULL";
                                        } else {
                                            $IndLiqGeral = str_replace(".", "", $_SESSION['IndLiqGeral']);
                                            $IndLiqGeral = str_replace(",", ".", $IndLiqGeral);
                                        }
                                        
                                        if ($_SESSION['IndEndividamento'] == "") {
                                            $IndEndividamento = "NULL";
                                        } else {
                                            $IndEndividamento = str_replace(".", "", $_SESSION['IndEndividamento']);
                                            $IndEndividamento = str_replace(",", ".", $IndEndividamento);
                                        }
                                        
                                        if ($_SESSION['IndSolvencia'] == "") {
                                            $IndSolvencia = "NULL";
                                        } else {
                                            $IndSolvencia = str_replace(".", "", $_SESSION['IndSolvencia']);
                                            $IndSolvencia = str_replace(",", ".", $IndSolvencia);
                                        }
                                       
                                        if ($_SESSION['DataBalanco'] == "" or $_SESSION['DataBalanco'] == "//") {
                                            $DataBalanco = "NULL";
                                        } else {
                                            $DataBalanco = "'" . substr($_SESSION['DataBalanco'], 6, 4) . "-" . substr($_SESSION['DataBalanco'], 3, 2) . "-" . substr($_SESSION['DataBalanco'], 0, 2) . "'";
                                        }
                                        
                                        if ($_SESSION['DataNegativa'] == "" or $_SESSION['DataBalanco'] == "//") {
                                            $DataNegativa = "NULL";
                                        } else {
                                            $DataNegativa = "'" . substr($_SESSION['DataNegativa'], 6, 4) . "-" . substr($_SESSION['DataNegativa'], 3, 2) . "-" . substr($_SESSION['DataNegativa'], 0, 2) . "'";
                                        }
                                        
                                        if ($_SESSION['DataContratoEstatuto'] == "" or $_SESSION['DataContratoEstatuto'] == "//") {
                                            $DataContratoEstatuto = "NULL";
                                        } else {
                                            $DataContratoEstatuto = "'" . substr($_SESSION['DataContratoEstatuto'], 6, 4) . "-" . substr($_SESSION['DataContratoEstatuto'], 3, 2) . "-" . substr($_SESSION['DataContratoEstatuto'], 0, 2) . "'";
                                        }

                                        # Atribuindo Valor NULL - Formulário D #
                                        if ($_SESSION['NomeEntidade'] == "") {
                                            $NomeEntidade = "NULL";
                                        } else {
                                            $NomeEntidade = "'" . EscapaValor::escape($_SESSION['NomeEntidade']) . "'";
                                        }
                                        
                                        if ($_SESSION['RegistroEntidade'] == "") {
                                            $RegistroEntidade = "NULL";
                                        } else {
                                            $RegistroEntidade = removeSimbolos($_SESSION['RegistroEntidade']);
                                        }
                                        
                                        if ($_SESSION['DataVigencia'] == "" or $_SESSION['DataBalanco'] == "//") {
                                            $DataVigencia = "NULL";
                                        } else {
                                            $DataVigencia = "'" . substr($_SESSION['DataVigencia'], 6, 4) . "-" . substr($_SESSION['DataVigencia'], 3, 2) . "-" . substr($_SESSION['DataVigencia'], 0, 2) . "'";
                                        }
                                        
                                        if ($_SESSION['RegistroTecnico'] == "") {
                                            $RegistroTecnico = "NULL";
                                        } else {
                                            $RegistroTecnico = removeSimbolos($_SESSION['RegistroTecnico']);
                                        }

                                        # Colocando o CEP de Logragouro ou Material #
                                        if ($_SESSION['Localidade'] == "S") {
                                            $Cep = "NULL";
                                            $CepLocalidade = removeSimbolos($_SESSION['CEP']);
                                        } else {
                                            $Cep = removeSimbolos($_SESSION['CEP']);
                                            $CepLocalidade = "NULL";
                                        }
                                         ($PreForCGC) ? $PreForCGC = $PreForCGC : $PreForCGC = 'NULL'; 
                                         if($_SESSION['TipoNatureza']){
                                            $tipoNatureza = $_SESSION['TipoNatureza'];
                                         }else{
                                            $tipoNatureza = 'NULL';
                                         }
                                        $certidaoEstrangeira = "'".$_SESSION['CERTID_ESTRANG']."'";
                     
                                        # Insere Pré-Fornecedor # erro- data contrato ou estatuto da tabela fornecedor credenciado
                                        $sql = "INSERT INTO SFPC.TBPREFORNECEDOR ( ";
                                        $sql .= "APREFOSEQU, APREFOCCGC, APREFOCCPF, APREFOIDEN, NPREFOORGU, CPREFSCODI, ";
                                        $sql .= "NPREFOSENH, EPREFOMOTI, NPREFORAZS, NPREFOFANT, CCEPPOCODI, CCELOCCODI, ";
                                        $sql .= "EPREFOLOGR, APREFONUME, EPREFOCOMP, EPREFOBAIR, NPREFOCIDA, CPREFOESTA, ";
                                        $sql .= "APREFOCDDD, APREFOTELS, APREFONFAX, NPREFOMAIL, APREFOCPFC, NPREFOCONT, ";
                                        $sql .= "NPREFOCARG, APREFODDDC, APREFOTELC, APREFOREGJ, DPREFOREGJ, APREFOINES, ";
                                        $sql .= "APREFOINME, APREFOINSM, VPREFOCAPS, VPREFOCAPI, VPREFOPATL, VPREFOINLC, ";
                                        $sql .= "VPREFOINLG, DPREFOULTB, DPREFOCNFC, DPREFOCONT, NPREFOENTP, APREFOENTR, ";
                                        $sql .= "DPREFOVIGE, APREFOENTT, DPREFOGERA, CGREMPCODI, CUSUPOCODI, APREFONTEN, ";
                                        $sql .= "DPREFOEXPS, TPREFOULAT, FPREFOMEPP, VPREFOINDI, VPREFOINSO, NPREFOMAI2, ";
                                        $sql .= "FPREFOTIPO, aprefoidfe, cfornjsequ ";
                                        $sql .= ") VALUES ( ";
                                        $sql .= "$PreForSeq, $PreForCGC, $PreForCPF, $Identidade, $OrgaoUF, $PreForSituacao, ";
                                        $sql .= "NULL, NULL, '" . EscapaValor::escape($_SESSION['RazaoSocial']) . "', $NomeFantasia, $Cep, $CepLocalidade, ";
                                        $sql .= "'" . $_SESSION['Logradouro'] . "', $Numero, '$Complemento', '" . $_SESSION['Bairro'] . "', '" . $_SESSION['Cidade'] . "', '" . $_SESSION['UF'] . "', ";
                                        $sql .= "$DDD, $Telefone, $Fax, $Email, $CPFContato, $NomeContato, ";
                                        $sql .= "$CargoContato, $DDDContato, $TelefoneContato, $RegistroJunta, $DataRegistroInv, $InscEstadual, ";
                                        $sql .= "$InscMercantil, $InscOMunic, $CapSocial, $CapIntegralizado, $Patrimonio, $IndLiqCorrente, ";
                                        $sql .= "$IndLiqGeral, $DataBalanco, $DataNegativa, $DataContratoEstatuto, $NomeEntidade, $RegistroEntidade, $DataVigencia, ";
                                        $sql .= "$RegistroTecnico, '" . substr($DataAtual, 0, 10) . "', " . $_SESSION['_cgrempcodi_'] . ", " . $_SESSION['_cusupocodi_'] . ", NULL, NULL, ";
                                        $sql .= "'$dataOntem', $MicroEmpresa, $IndEndividamento, $IndSolvencia, " . $Email2 . ", '" . $_SESSION['TipoHabilitacao'] ."',";
                                        $sql .= " $certidaoEstrangeira, $tipoNatureza )";
                                        $result = executarSQL($db, $sql);

                                        if (PEAR::isError($result)) {
                                            $db->query("ROLLBACK");
                                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                        } else {
                                            # Inserindo Contas Bancárias #
                                            if ($_SESSION['Banco1'] != "" and $_SESSION['Agencia1'] != "" and $_SESSION['ContaCorrente1'] != "") {
                                                $sql = "INSERT INTO SFPC.TBPREFORNCONTABANCARIA ( ";
                                                $sql .= "APREFOSEQU, CPRECOBANC, CPRECOAGEN, CPRECOCONT, TPRECOULAT ";
                                                $sql .= ") VALUES ( ";
                                                $sql .= "$PreForSeq, '" . $_SESSION['Banco1'] . "', '" . $_SESSION['Agencia1'] . "', '" . $_SESSION['ContaCorrente1'] . "', '$DataAtual' ) ";
                                                
                                                $result = executarSQL($db, $sql);
                                                if (PEAR::isError($result)) {
                                                    $db->query("ROLLBACK");
                                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                }
                                            }

                                            if ($_SESSION['Banco2'] != "" and $_SESSION['Agencia2'] != "" and $_SESSION['ContaCorrente2'] != "") {
                                                $sql = "INSERT INTO SFPC.TBPREFORNCONTABANCARIA ( ";
                                                $sql .= "APREFOSEQU, CPRECOBANC, CPRECOAGEN, CPRECOCONT, TPRECOULAT ";
                                                $sql .= ") VALUES ( ";
                                                $sql .= "$PreForSeq, '" . $_SESSION['Banco2'] . "', '" . $_SESSION['Agencia2'] . "', '" . $_SESSION['ContaCorrente2'] . "', '$DataAtual' ) ";
                                                
                                                $result = executarSQL($db, $sql);
                                                if (PEAR::isError($result)) {
                                                    $db->query("ROLLBACK");
                                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                }
                                            }

                                            # Inserindo Sócios #
                                            if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
                                                for ($i = 0; $i < $_SESSION['NoSocios']; $i ++) {
                                                    if (! is_null($_SESSION['SociosNome'][$i])) {
                                                        $cpfnocaracteres = strlen(removeSimbolos($_SESSION['SociosCPF_CNPJ'][$i]));
                                                        $tipocadastro = null;
                                                        if ($cpfnocaracteres == 11) {
                                                            $tipocadastro = "F";
                                                        } elseif ($cpfnocaracteres == 14) {
                                                            $tipocadastro = "J";
                                                        }
                                                        if (is_null($tipocadastro)) {
                                                            EmailErro("Erro na inclusão de sócios", __FILE__, __LINE__, "Erro na inclusão de sócios de fornecedores. CPF/CNPJ não está em um tamanho válido");
                                                        } else {
                                                            $sql = "INSERT INTO SFPC.TBSOCIOPREFORNECEDOR (APREFOSEQU, nsoprenome, asoprecada, fsopretcad, tsopreulat)
                                                                    VALUES (" . $PreForSeq . ", '" . EscapaValor::escape($_SESSION['SociosNome'][$i]) . "', '" . removeSimbolos($_SESSION['SociosCPF_CNPJ'][$i]) . "', '" . $tipocadastro . "', '$DataAtual') ";
                                                            
                                                            $result = executarSQL($db, $sql);
                                                            
                                                            if (PEAR::isError($result)) {
                                                                $db->query("ROLLBACK");
                                                                ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            # Inserindo Certidões #
                                            if (count($_SESSION['CertidaoObrigatoria']) != 0) {
                                                for ($i = 0; $i < count($_SESSION['CertidaoObrigatoria']); $i ++) {
                                                    if (! is_null($_SESSION['CertidaoObrigatoria'][$i]) and $_SESSION['CertidaoObrigatoria'][$i] != "" and ! is_null($_SESSION['DataCertidaoOb'][$i]) and $_SESSION['DataCertidaoOb'][$i] != "") {
                                                        $DataCertidaoObInv = substr($_SESSION['DataCertidaoOb'][$i], 6, 4) . "-" . substr($_SESSION['DataCertidaoOb'][$i], 3, 2) . "-" . substr($_SESSION['DataCertidaoOb'][$i], 0, 2);
                                                        $sql = "INSERT INTO SFPC.TBPREFORNCERTIDAO (APREFOSEQU, CTIPCECODI, DPREFCVALI, TPREFCULAT)
                                                                VALUES ($PreForSeq, " . $_SESSION['CertidaoObrigatoria'][$i] . ", '$DataCertidaoObInv', '$DataAtual') ";
                                                        
                                                        $result = executarSQL($db, $sql);
                                                        
                                                        if (PEAR::isError($result)) {
                                                            $db->query("ROLLBACK");
                                                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                        }
                                                    }
                                                }
                                            }

                                            if (count($_SESSION['CertidaoComplementar']) != 0) {
                                                for ($i = 0; $i < count($_SESSION['CertidaoComplementar']); $i ++) {
                                                    if (! is_null($_SESSION['CertidaoComplementar'][$i]) and $_SESSION['CertidaoComplementar'][$i] != "" and ! is_null($_SESSION['DataCertidaoComp'][$i]) and $_SESSION['DataCertidaoComp'][$i] != "") {
                                                        $DataCertidaoCompInv = substr($_SESSION['DataCertidaoComp'][$i], 6, 4) . "-" . substr($_SESSION['DataCertidaoComp'][$i], 3, 2) . "-" . substr($_SESSION['DataCertidaoComp'][$i], 0, 2);
                                                        
                                                        $sql = "INSERT INTO SFPC.TBPREFORNCERTIDAO (APREFOSEQU, CTIPCECODI, DPREFCVALI, TPREFCULAT) VALUES ($PreForSeq, " . $_SESSION['CertidaoComplementar'][$i] . ", '$DataCertidaoCompInv', '$DataAtual') ";
                                                        
                                                        $result = executarSQL($db, $sql);
                                                        
                                                        if (PEAR::isError($result)) {
                                                            $db->query("ROLLBACK");
                                                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            # Inserindo Autorização Específica #
                                            if (count($_SESSION['AutorizacaoNome']) != 0) {
                                                for ($i = 0; $i < count($_SESSION['AutorizacaoNome']); $i ++) {
                                                    if (! is_null($_SESSION['AutorizacaoNome'][$i]) and $_SESSION['AutorizacaoNome'][$i] != "") {
                                                        $AutorizacaoDataInv = substr($_SESSION['AutorizacaoData'][$i], 6, 4) . "-" . substr($_SESSION['AutorizacaoData'][$i], 3, 2) . "-" . substr($_SESSION['AutorizacaoData'][$i], 0, 2);
                                                        
                                                        $sql = "INSERT INTO SFPC.TBPREFORNAUTORIZACAOESPECIFICA (APREFOSEQU, NPREFANOMA, APREFANUMA, DPREFAVIGE, TPREFAULAT)
                                                                VALUES ($PreForSeq, '" . $_SESSION['AutorizacaoNome'][$i] . "', " . $_SESSION['AutorizacaoRegistro'][$i] . ", '$AutorizacaoDataInv', '$DataAtual') ";
                                                        
                                                        $result = executarSQL($db, $sql);
                                                        
                                                        if (PEAR::isError($result)) {
                                                            $db->query("ROLLBACK");
                                                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            # Incluindo Grupos de Fornecimento #
                                            if (count($_SESSION['Materiais']) != 0) {
                                                for ($i = 0; $i < count($_SESSION['Materiais']); $i ++) {
                                                    $GrupoMaterial = explode("#", $_SESSION['Materiais'][$i]);
                                                    
                                                    $sql = "INSERT INTO SFPC.TBGRUPOPREFORNECEDOR (CGRUMSCODI, APREFOSEQU, TGRUPFULAT)
                                                            VALUES (" . $GrupoMaterial[1] . ", $PreForSeq, '$DataAtual' ) ";
                                                    // var_dump($sqls);die;
                                                    $result = executarSQL($db, $sql);
                                                    
                                                    if (PEAR::isError($result)) {
                                                        $db->query("ROLLBACK");
                                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                    }
                                                }
                                            }

                                            if (count($_SESSION['Servicos']) != 0) {
                                                for ($i = 0; $i < count($_SESSION['Servicos']); $i ++) {
                                                    $GrupoServico = explode("#", $_SESSION['Servicos'][$i]);
                                                    $sql = "INSERT INTO SFPC.TBGRUPOPREFORNECEDOR (CGRUMSCODI, APREFOSEQU, TGRUPFULAT)
                                                            VALUES (" . $GrupoServico[1] . ", $PreForSeq, '$DataAtual' ) ";

                                                    $result = executarSQL($db, $sql);
                                                    
                                                    if (PEAR::isError($result)) {
                                                        $db->query("ROLLBACK");
                                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                    }
                                                }
                                            }

                                            // Documentos
                                            if (count($_SESSION['Arquivos_Upload_Insc']) != 0) {
												for ($i=0; $i< count($_SESSION['Arquivos_Upload_Insc']); $i++) {

													$arquivo = $_SESSION['Arquivos_Upload_Insc'];
													if($arquivo['situacao'][$i] == 'novo'){
														// fazer sql para trazer o sequencial
														$sql = ' SELECT cfdocusequ FROM SFPC.tbfornecedordocumento WHERE  1=1 ORDER BY cfdocusequ DESC limit 1';
														$seqDocumento = resultValorUnico(executarTransacao($db, $sql)) + 1;
								
														$anexo =  bin2hex($arquivo['conteudo'][$i]);

														$sqlAnexo = "INSERT INTO sfpc.tbfornecedordocumento
														(cfdocusequ, aprefosequ, aforcrsequ, afdocuanoa, cfdoctcodi, efdocunome, ifdocuarqu, ffdocuforn, tfdocuanex, ffdocusitu, cusupocodi, tfdoctulat)
														VALUES(".$seqDocumento.", ".$PreForSeq.", NULL, DATE_PART('YEAR', CURRENT_TIMESTAMP), ".$arquivo['tipoCod'][$i].", '".$arquivo['nome'][$i]."', decode('".$anexo."','hex'), 'S', now(), 'A', ".$_SESSION['_cusupocodi_'].", now());
														";

														$resultAnexo = $db->query($sqlAnexo);
														
														if (PEAR::isError($resultAnexo)) {
															$db->query("ROLLBACK");
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAnexo");
														}else{
															//insere a fase do documento
															$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
																	(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
																	VALUES(".$seqDocumento.", 2, '".$arquivo['observacao'][$i]."', now(), ".$_SESSION['_cusupocodi_'].", now());

															";
		
															$resultHist = $db->query($sqlHist);
															
															if (PEAR::isError($resultHist)) {
																$db->query("ROLLBACK");
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
															}

														}

													}
												}
											}

                                            # Busca E-mail do Fornecedor #
                                            $db = Conexao();
                                            
                                            $sql = "SELECT  NPREFOMAIL, NPREFOMAI2
                                                    FROM    SFPC.TBPREFORNECEDOR
                                                    WHERE   APREFOSEQU = $PreForSeq ";
                                            
                                            $result = executarSQL($db, $sql);
                                            
                                            if (PEAR::isError($result)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                            } else {
                                                $Linha = $result->fetchRow();
                                                $Email = $Linha[0];
                                                $Email2 = $Linha[1];
                                            }

                                            # Cria a senha do Usuário #
                                            $Senha = CriaSenha();
                                            $_SESSION['Senha'] = $Senha;
                                            $SenhaCript = crypt($Senha, "P");
                                            $DataExpSenha = SubtraiData("1", date("d/m/Y"));
                                            // $DataExpSenhaInv = substr($DataExpSenha, 6, 4) . "-" . substr($DataExpSenha, 3, 2) . "-" . substr($DataExpSenha, 0, 2);
                                            $DataExpSenhaInv = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date("Y")));

                                            # Atualiza a senha do Usuário #
                                            $sql = "UPDATE  SFPC.TBPREFORNECEDOR
                                                    SET     NPREFOSENH = '$SenhaCript',
                                                            DPREFOEXPS = '$DataExpSenhaInv',
                                                            APREFONTEN = 0,
                                                            TPREFOULAT = '$DataAtual' 
                                                    WHERE   APREFOSEQU = $PreForSeq ";
                                            
                                            $result = executarSQL($db, $sql);

                                            if (PEAR::isError($result)) {
                                                $db->query("ROLLBACK");
                                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                            } else {
                                                $_SESSION['Mens'] = 1;
                                                $_SESSION['Tipo'] = 1;
                                                $_SESSION['Mensagem'] = "Inscrição Incluída com Sucesso<br>";

                                                # Envia a senha pelo e-mail do usuário #
                                                if ($Email != "") {
                                                    if (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 11) {
                                                        $TipoForn = "Nome do Fornecedor";
                                                        $CpfCgcMail = "CPF";
                                                        $CpfCgcNumero = $_SESSION['CPF_CNPJ'];
                                                    } elseif (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 14) {
                                                        $TipoForn = "Razão Social do Fornecedor";
                                                        $CpfCgcMail = "CNPJ";
                                                        $CpfCgcNumero = $_SESSION['CPF_CNPJ'];
                                                    }
                                                    EnviaEmail("$Email", "Senha temporária de inscrição no Portal de Compras da Prefeitura do Recife", "\n A inscrição de fornecedor da Prefeitura do Recife foi efetuada com sucesso. \n A documentação relativa à habilitação jurídica, qualificação técnica, qualificação econômico-financeira e regularidade fiscal deverá ser preferencialmente anexada no Portal de Compras, ou enviada para protocolo da Gerência Geral de Licitações e Compras - GGLIC (Cais do Apolo, 925, 2.º andar, CEP:500030-903, Bairro do Recife, Recife-PE -> telefone: 3355-8235).  \n\t $TipoForn: " . $_SESSION['RazaoSocial'] . "\n\t $CpfCgcMail: $CpfCgcNumero \n\t Senha temporária: $Senha ", "from: portalcompras@recife.pe.gov.br");
                                                    
                                                    if ($Email2 != "" and ! is_null($Email2)) {
                                                        EnviaEmail("$Email2", "Senha temporária de inscrição no Portal de Compras da Prefeitura do Recife", "\n A inscrição de fornecedor da Prefeitura do Recife foi efetuada com sucesso. \n A documentação relativa à habilitação jurídica, qualificação técnica, qualificação econômico-financeira e regularidade fiscal deverá ser preferencialmente anexada no Portal de Compras, ou enviada para protocolo da Gerência Geral de Licitações e Compras - GGLIC (Cais do Apolo, 925, 2.º andar, CEP:500030-903, Bairro do Recife, Recife-PE -> telefone: 3355-8235).  \n\t $TipoForn: " . $_SESSION['RazaoSocial'] . "\n\t $CpfCgcMail: $CpfCgcNumero \n\t Senha temporária: $Senha ", "from: portalcompras@recife.pe.gov.br");
                                                    }
                                                    $_SESSION['Mensagem'] .= "A senha temporária foi enviada para o e-mail do Fornecedor<br>";
                                                }

                                                # Coloca na mensagem o caminho para Recibo #
                                                $Url = "RelReciboInscritoPdf.php?CodigoInsc=$PreForSeq";
                                                
                                                if (! in_array($Url, $_SESSION['GetUrl'])) {
                                                    $_SESSION['GetUrl'][] = $Url;
                                                }
                                                $_SESSION['Mensagem'] .= "A documentação relativa à habilitação jurídica, qualificação técnica, qualificação econômico-financeira e regularidade fiscal, deverá ser preferencialmente anexada no Portal de Compras, ou enviada para Protocolo da Gerência Geral de Licitações e Compras - GGLIC (Cais do Apolo, 925, 2.º andar, CEP:500030-903, Bairro do Recife, Recife-PE -> telefone: 3355-8235). Para visualizar o Recibo clique <a href=\"#\" class=\"titulo1\"  onClick=\"window.open('RelReciboInscritoPdf.php?CodigoInsc=$PreForSeq','popup', 'width=800px,height=600px') \" >AQUI</a><br>";
                                                $Origem = "";

                                                # Limpa Variáveis #
                                                if (isset($_SESSION['Botao'])) {
                                                    unset($_SESSION['Botao']);
                                                }
                                                LimparSessao();
                                                
                                                # Redireciona o programa de acordo com o botão voltar #
                                                if ($_SESSION['Mens'] == 0) {
                                                    $_SESSION['Botao'] = "";
                                                    $_SESSION['Mensagem'] = "Fornecedor inscrito alterado com sucesso<br>";
                                                    
                                                    $Url = "CadAvaliacaoInscritoManter.php?ProgramaSelecao=" . urlencode($_SESSION['ProgramaSelecao']) . "&Sequencial=" . $_SESSION['Sequencial'] . "&Mensagem=" . urlencode($_SESSION['Mensagem']) . "&Mens=1&Tipo=1";
                                                    
                                                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                                                        $_SESSION['GetUrl'][] = $Url;
                                                    }
                                                    header("location: " . $Url);
                                                    exit();
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                if ($Situacao == 5) {
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 1;
                                    $_SESSION['Mensagem'] = "A inscrição do fornecedor foi excluída. Motivo: $Motivo. Para regularizar o seu cadastro, procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE<br>";
                                } else {
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 1;
                                    $_SESSION['Mensagem'] = "Fornecedor já inscrito<li>";
                                }
                            }
                        }
                    } else {
                        $_SESSION['Mens'] = 1;
                        $_SESSION['Tipo'] = 1;
                        $_SESSION['Mensagem'] = "Fornecedor já cadastrado. Acesse a consulta de acompanhamento de fornecedor para visualizar o seu cadastro. Caso não possua a senha de acesso procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE<br>";
                    }
                }
                $db->query("COMMIT");
                $db->query("END");
                $db->disconnect();
                unset($_SESSION['Arquivos_Upload_Insc']);
                $Destino = "A";
            }
        }
    }
    $_SESSION['Des'] = $Destino;
    $_SESSION['Ori'] = $Origem;
    //ExibeAbas($Destino);
}

# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino, $tpl) {
    if ($Destino == "A" or $Destino == "") {
        ExibeAbaHabilitacao($tpl);
    } elseif ($Destino == "B") {
        ExibeAbaRegularidadeFiscal($tpl);
    } elseif ($Destino == "C") {
        ExibeAbaQualificEconFinanceira($tpl);
    } elseif ($Destino == "D") {
        ExibeAbaQualificTecnica($tpl);
    } elseif ($Destino == "E") {
		ExibeAbaDocumentos($tpl);
	}
}

function ExibeAbaHabilitacao($tpl) {
    $tpl->block('BLOCO_TEXTO_HABILITACAO');

    /* Define a aba de acesso */
    configurarBlocoHabilitacao($tpl);
    $tpl->ACTIVEHABI     = "active";
    $tpl->ACTIVEREG      = "";
    $tpl->ACTIVEQUALECO  = "";
    $tpl->ACTIVEQUALITEC = "";
    $tpl->block('BLOCO_ABA_HABILITACAO');
}

function verificaMudancaAba($origem, $tpl) {
    if ($origem == "A") {
        CriticaAbaHabilitacao();
    }

    if ($origem == "B") {
        CriticaAbaRegularidadeFiscal();
    }

    if ($origem == "C") {
        CriticaAbaQualificEconFinanceira();
    }

    if ($origem == "D") {
        CriticaAbaQualificTecnica();
    }

    if ($origem == "E") {
        CriticaAbaDocumentos();
    }
}

function ExibeAbaRegularidadeFiscal($tpl) {
    $tpl->block("BLOCO_TEXTO_REGULARIDADE_FISCAL");

    /* Define a aba de acesso */
    $tpl->ACTIVEHABI     = "";
    $tpl->ACTIVEREG      = "active";
    $tpl->ACTIVEQUALECO  = "";
    $tpl->ACTIVEQUALITEC = "";
    configurarBlocoRegularidade($tpl);
    $tpl->block("BLOCO_ABA_REGULARIDADE");
}

function ExibeAbaQualificEconFinanceira($tpl) {
    $tpl->block("BLOCO_TEXTO_QUALIFICACAO_FINANCEIRA");

    /* Define a aba de acesso */
    $tpl->ACTIVEHABI     = "";
    $tpl->ACTIVEREG      = "";
    $tpl->ACTIVEQUALECO  = "active";
    $tpl->ACTIVEQUALITEC = "";
    configuraBlocoQualificacaoEconomica($tpl);
    $tpl->block("bloco_regularidade_financeira");
}

function ExibeAbaQualificTecnica($tpl) {
    $tpl->block("BLOCO_TEXTO_QUALIFICACAO_TECNICA");

    /* Define a aba de acesso */
    $tpl->ACTIVEHABI     = "";
    $tpl->ACTIVEREG      = "";
    $tpl->ACTIVEQUALECO  = "";
    $tpl->ACTIVEQUALITEC = "active";
    configurarBlocoQualificacaoTecnica($tpl);
    $tpl->block("bloco_qualificacao_tecnica");
}


function ExibeAbaDocumentos($tpl) {
    $tpl->block("BLOCO_TEXTO_DOCUMENTOS");

    /* Define a aba de acesso */
    $tpl->ACTIVEHABI     = "";
    $tpl->ACTIVEREG      = "";
    $tpl->ACTIVEQUALECO  = "";
    $tpl->ACTIVEQUALITEC = "";
    $tpl->ACTIVEDOC = "active";
    configurarBlocoDocumentos($tpl);
    $tpl->block("bloco_documentos");
}

function CriticaAbaHabilitacao() { // Critica aos Campos - Formulário A
    $ErroPrograma = "CadInscritoIncluir ";
    if ($_SESSION['Botao'] == "Limpar") {
        $_SESSION['Botao']           = "";
        $_SESSION['Mens']            = "";
        $_SESSION['CPF_CNPJ']        = "";
        $_SESSION['TipoCnpjCpf']     = "";
        $_SESSION['MicroEmpresa']    = "";
        $_SESSION['Identidade']      = "";
        $_SESSION['OrgaoUF']         = "";
        $_SESSION['RazaoSocial']     = "";
        $_SESSION['NomeFantasia']    = "";
        $_SESSION['CEP']             = "";
        $_SESSION['CEPInformado']    = "";
        $_SESSION['Logradouro']      = "";
        $_SESSION['Numero']          = "";
        $_SESSION['Complemento']     = "";
        $_SESSION['Bairro']          = "";
        $_SESSION['Cidade']          = "";
        $_SESSION['UF']              = "";
        $_SESSION['DDD']             = "";
        $_SESSION['Telefone']        = "";
        $_SESSION['Email']           = "";
        $_SESSION['Email2']          = "";
        $_SESSION['Fax']             = "";
        $_SESSION['RegistroJunta']   = "";
        $_SESSION['DataRegistro']    = "";
        $_SESSION['CPFContato']      = "";
        $_SESSION['NomeContato']     = "";
        $_SESSION['CargoContato']    = "";
        $_SESSION['DDDContato']      = "";
        $_SESSION['TelefoneContato'] = "";
        $_SESSION['NoSocios']        = 0;
        $_SESSION['SociosCPF_CNPJ']  = array();
        $_SESSION['SociosNome']      = array();
        $_SESSION['SocioNovoNome']   = "";
        $_SESSION['SocioNovoCPF']    = "";
    } else {
        $_SESSION['Mens'] = 0;
        $_SESSION['Mensagem'] = "Informe: ";

        if ($_SESSION['CPF_CNPJ'] != "") {
            $_SESSION['CPF_CNPJ'] = FormataCPF_CNPJ($_SESSION['CPF_CNPJ'], $_SESSION['TipoCnpjCpf']);
        }

        if ($_SESSION['TipoCnpjCpf'] == "CPF" and $_SESSION['Critica'] != 1) {
            $Qtd = strlen(removeSimbolos($_SESSION['CPF_CNPJ']));
            $_SESSION['TipoDoc'] = 2;
            
            if (($Qtd != 11) and ($Qtd != 0)) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
            } elseif ($_SESSION['CPF_CNPJ'] == "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF</a>";
            } else {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $cpfcnpj = valida_CPF(removeSimbolos($_SESSION['CPF_CNPJ']));
                
                if ($cpfcnpj === false) {
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
                }
            }
        } elseif ($_SESSION['TipoCnpjCpf'] == "CNPJ" and $_SESSION['Critica'] != 1) {
            $Qtd = strlen(removeSimbolos($_SESSION['CPF_CNPJ']));
            $_SESSION['TipoDoc'] = 1;
            
            if (($Qtd != 14) and ($Qtd != 0)) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
            } elseif ($_SESSION['CPF_CNPJ'] == "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ</a>";
            } else {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $cpfcnpj = valida_CNPJ(removeSimbolos($_SESSION['CPF_CNPJ']));
                
                if ($cpfcnpj === false) {
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
                }
            }
        }
        if (($_SESSION['Mens'] != 1) && (($_SESSION['Botao'] == "A") || ($_SESSION['Botao'] == "Preencher"))) {
            if ($_SESSION['CEP'] != "") {
                if (! is_numeric(removeSimbolos($_SESSION['CEP']))) {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve ser número</a>";
                } elseif (strlen(removeSimbolos($_SESSION['CEP'])) != 8) {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve conter 8 dígitos</a>";
                } else {
                    $db = Conexao();
                    
                    $sql = "SELECT  CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL
                            FROM    PPDV.TBCEPLOGRADOUROBR
                            WHERE   CCEPPOCODI = " . removeSimbolos($_SESSION['CEP']);
                    
                    $res = executarSQL($db, $sql);

                    if (PEAR::isError($res)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $rows = $res->numRows();
                        
                        if ($rows == 0) {
                            $sqlloc = "SELECT   CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO
                                       FROM     PPDV.TBCEPLOCALIDADEBR
                                       WHERE    CCELOCCODI = " . removeSimbolos($_SESSION['CEP']);
                            
                            $resloc = $db->query($sqlloc);

                            if (PEAR::isError($resloc)) {
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlloc");
                            } else {
                                $rowloc = $resloc->numRows();
                                
                                if ($rowloc == 0) {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }

                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">O CEP informado não está cadastrado em nosso sistema. Confira novamente o número informado e, caso esteja correto, insira manualmente o logradouro, bairro, cidade e UF correspondente</a>";
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if ($_SESSION['Numero'] != "") {
            if (! is_numeric($_SESSION['Numero'])) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Numero.focus();\" class=\"titulo2\">Campo 'Número' deve conter apenas números</a>";
            }
        }
        
        if ($_SESSION['DDD'] != "") {
            if (! is_numeric($_SESSION['DDD'])) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter apenas números</a>";
            } elseif (strlen($_SESSION['DDD']) != 3) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter 3 dígitos</a>";
            }
        }
        if ($cpfcnpj === true) {
            # Verifica se o Fornecedor já foi Cadastrado #
            $db = Conexao();
            $db->query("BEGIN TRANSACTION");
            
            $sql = "SELECT  COUNT(AFORCRSEQU)
                    FROM    SFPC.TBFORNECEDORCREDENCIADO
                    WHERE ";
            
                if (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 11) {
                    $sql .= "AFORCRCCPF = '" . removeSimbolos($_SESSION['CPF_CNPJ']) . "' ";
                } elseif (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 14) {
                    $sql .= "AFORCRCCGC = '" . removeSimbolos($_SESSION['CPF_CNPJ']) . "' ";
                }
            
            $result = executarSQL($db, $sql);
            
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $Linha = $result->fetchRow();
                $ExisteFornecedor = $Linha[0];
                
                if ($ExisteFornecedor == 0) {
                    # Verifica se o Fornecedor Já foi Inscrito #
                    $sqlpre = "SELECT   CPREFSCODI, EPREFOMOTI
                               FROM     SFPC.TBPREFORNECEDOR
                               WHERE ";
                    
                        # Colocar CPF/CGC para o Pré-Cadastro #
                       if($_SESSION['CPF_CNPJ']){ 
                            if (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 11) {
                                $sqlpre .= "APREFOCCPF = '" . removeSimbolos($_SESSION['CPF_CNPJ']) . "'";
                            } elseif (strlen(removeSimbolos($_SESSION['CPF_CNPJ'])) == 14) {
                                $sqlpre .= "APREFOCCGC = '" . removeSimbolos($_SESSION['CPF_CNPJ']) . "'";
                                
                            }
                        }else{
                            $sqlpre .= "APREFOCCGC = '" . removeSimbolos($_SESSION['CERTID_ESTRANG']) . "'";
                        }

                    $respre = executarSQL($db, $sqlpre);
            
                    if (PEAR::isError($respre)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $rows = $respre->numRows();
            
                        if ($rows != 0) {
                            $Linha = $respre->fetchRow();
                            $Situacao = $Linha[0];
                            $Motivo = $Linha[1];

                            if ($Situacao == 5) {
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 1;
                                $_SESSION['Mensagem'] = "A inscrição do fornecedor foi excluída. Motivo: $Motivo. Para regularizar o seu cadastro, procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
                            } else {
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 0;
                                $_SESSION['Mensagem'] = "Fornecedor já inscrito";
                            }
                        }
                    }
                } else {
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 0;
                    $_SESSION['Mensagem'] = "Fornecedor já cadastrado. Acesse a consulta de acompanhamento de fornecedor para visualizar o seu cadastro. Caso não possua a senha de acesso procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
                }
            }
            
            if ($_SESSION['Mens'] == 0) {
                # Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #
                if ($_SESSION['Irregularidade'] == "") {
                    $NomePrograma = urlencode("CadInscritoIncluir.php");
                    $Url = "RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=" . $_SESSION['TipoDoc'] . "&Destino=" . $_SESSION['Des'] . "&CPF_CNPJ=" . removeSimbolos($_SESSION['CPF_CNPJ']) . "";

                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                        $_SESSION['GetUrl'][] = $Url;
                    }
                    header('Location: ' . $Url);
                    exit();
                } else {
                    if ($_SESSION['Irregularidade'] == "S") {
                        $_SESSION['Mens'] = 1;
                        $_SESSION['Tipo'] = 0;
                        $_SESSION['Mensagem'] = "Fornecedor possui alguma irregularidade com a Prefeitura e deve procurar o Centro de Atendimento ao Contribuinte - CAC da Prefeitura - térreo - no Cais do Apolo, 925 - Bairro do Recife/PE. Após a solução das pendências tentar executar a inscrição novamente";
                    } else {
                        if ($_SESSION['CEP'] == "") {
                            if ($_SESSION['Mens'] == 1) {
                                $_SESSION['Mensagem'] .= ", ";
                            }
                            $_SESSION['Mens'] = 1;
                            $_SESSION['Tipo'] = 2;
                            $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">CEP</a>";
                        } else {
                            if ($_SESSION['Botao'] == "Preencher") {
                                if ($_SESSION['Mens'] == 0) {
                                    # Seleciona o Endereço de acordo com o CEP informado #
                                    $_SESSION['CEPAntes'] = $_SESSION['CEP'];
                                    $sql = "SELECT  CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL
                                            FROM    PPDV.TBCEPLOGRADOUROBR
                                            WHERE   CCEPPOCODI = " . removeSimbolos($_SESSION['CEP']);

                                    $res = executarSQL($db, $sql);

                                    if (PEAR::isError($res)) {
                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                    } else {
                                        $rows = $res->numRows();

                                        if ($rows == 0) {
                                            $sqlloc = "SELECT   CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO
                                                       FROM     PPDV.TBCEPLOCALIDADEBR
                                                       WHERE    CCELOCCODI = " . removeSimbolos($_SESSION['CEP']);

                                            $resloc = $db->query($sqlloc);

                                            if (PEAR::isError($resloc)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlloc");
                                            } else {
                                                $rowloc = $resloc->numRows();
                                                
                                                if ($rowloc == 0) {
                                                    if ($_SESSION['Mens'] == 1) {
                                                        $_SESSION['Mensagem'] .= ", ";
                                                    }
                                                    $_SESSION['Mens'] = 1;
                                                    $_SESSION['Tipo'] = 2;
                                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">CEP Válido</a>";
                                                    $_SESSION['Logradouro'] = "";
                                                    $_SESSION['Bairro'] = "";
                                                    $_SESSION['Cidade'] = "";
                                                    $_SESSION['UF'] = "";
                                                    $_SESSION['Localidade'] = "";
                                                } else {
                                                    $LinhaLoc = $resloc->fetchRow();
                                                    $_SESSION['CEP'] = $LinhaLoc[0];
                                                    $_SESSION['Cidade'] = $LinhaLoc[1];
                                                    $_SESSION['UF'] = $LinhaLoc[2];
                                                    $_SESSION['Localidade'] = "S";
                                                }
                                            }
                                        } else {
                                            $linha = $res->fetchRow();
                                            $_SESSION['CEP'] = $linha[0];
                                            
                                            if ($linha[3] != "" and $linha[5] != "") {
                                                $_SESSION['Logradouro'] = $linha[3] . " " . $linha[1] . " " . $linha[5];
                                            } else {
                                                if ($linha[3] != "" and $linha[5] == "") {
                                                    $_SESSION['Logradouro'] = $linha[3] . " " . $linha[1];
                                                } elseif ($linha[3] == "" and $linha[5] != "") {
                                                    $_SESSION['Logradouro'] = $linha[1] . " " . $linha[5];
                                                } else {
                                                    $_SESSION['Logradouro'] = $linha[1];
                                                }
                                            }
                                            $_SESSION['Bairro'] = $linha[2];
                                            $_SESSION['Cidade'] = $linha[6];
                                            $_SESSION['UF'] = $linha[4];
                                            $_SESSION['Localidade'] = "";
                                        }
                                    }
                                    # colocar 8 digitos no CEP
                                    while (strlen($_SESSION['CEP']) < 8) {
                                        $_SESSION['CEP'] = "0" . $_SESSION['CEP'];
                                    }
                                }
                            } else {
                                if ($_SESSION['CEPAntes'] != $_SESSION['CEP']) {
                                    if ($_SESSION['CEPAntes'] != "") {
                                        if ($_SESSION['Mens'] == 1) {
                                            $_SESSION['Mensagem'] .= ", ";
                                        }
                                        $_SESSION['Mens'] = 1;
                                        $_SESSION['Tipo'] = 2;
                                        $_SESSION['Virgula'] = 2;
                                        $_SESSION['Mensagem'] = "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">O CEP informado não corresponde ao endereço, clique no Botão \"Preencher Endereço\" para atualizá-lo</a>";
                                        $_SESSION['Botao'] = "";
                                    /*} else {
                                        if ($_SESSION['Mens'] == 1) {
                                            $_SESSION['Mensagem'] .= ", ";
                                        }
                                        $_SESSION['Mens'] = 1;
                                        $_SESSION['Tipo'] = 2;
                                        $_SESSION['Mensagem'] = "<font class=\"titulo2\">Clique no botão \"Preencher Endereço\" para atualizar os campos do endereço</font>";*/
                                    }
                                }
                            }
                        }
                        
                        if ($_SESSION['Mens'] == 0 and $_SESSION['Botao'] != "Preencher") {
                            if ($_SESSION['Identidade'] == "" and $_SESSION['TipoCnpjCpf'] == "CPF") {
                                if ($_SESSION['Mens'] == 1) {
                                    $_SESSION['Mensagem'] .= ", ";
                                }
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 2;
                                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Identidade.focus();\" class=\"titulo2\">Identidade</a>";
                            }
                            
                            if ($_SESSION['OrgaoUF'] == "" and $_SESSION['TipoCnpjCpf'] == "CPF") {
                                if ($_SESSION['Mens'] == 1) {
                                    $_SESSION['Mensagem'] .= ", ";
                                }
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 2;
                                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.OrgaoUF.focus();\" class=\"titulo2\">Órgao Emissor/UF</a>";
                            }

                            # Criticar Razao Social #
                            if ($_SESSION['RazaoSocial'] == "") {
                                if ($_SESSION['Mens'] == 1) {
                                    $_SESSION['Mensagem'] .= ", ";
                                }
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 2;

                                if ($_SESSION['TipoCnpjCpf'] == 'CPF') {
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RazaoSocial.focus();\" class=\"titulo2\">Nome</a>";
                                } else {
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RazaoSocial.focus();\" class=\"titulo2\">Razão Social</a>";
                                }
                            } else {
                                $sql = "SELECT  COUNT(*) AS QTD
                                        FROM    SFPC.TBPREFORNECEDOR
                                        WHERE NPREFORAZS = '" . EscapaValor::escape($_SESSION['RazaoSocial']) . "'";
                                 
                                $result = executarSQL($db, $sql);

                                $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

                                if ($row->qtd > 0) {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RazaoSocial.focus();\" class=\"titulo2\">Razão social já existe </a>";
                                }
                            }

                            # Criticar Nome Fantasia #
                            if (! empty($_SESSION['NomeFantasia'])) {
                                $sql = "SELECT  COUNT(*) AS QTD
                                        FROM    SFPC.TBPREFORNECEDOR
                                        WHERE NPREFOFANT = '" . EscapaValor::escape($_SESSION['NomeFantasia']) . "'";
                              
                                $result = executarSQL($db, $sql);

                                $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

                                if ($row->qtd > 0) {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.NomeFantasia.focus();\" class=\"titulo2\">Nome fantasia já existe</a>";
                                }
                            }

                            if ($_SESSION['Localidade'] == "S") {
                                if ($_SESSION['Logradouro'] == "") {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Logradouro.focus();\" class=\"titulo2\">Logradouro</a>";
                                }
                            }
                            
                            if ($_SESSION['Numero'] != "" and ! SoNumeros($_SESSION['Numero'])) {
                                if ($_SESSION['Mens'] == 1) {
                                    $_SESSION['Mensagem'] .= ", ";
                                }
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 2;
                                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Numero.focus();\" class=\"titulo2\">Número Válido</a>";
                            }
                            
                            if ($_SESSION['Localidade'] == "S") {
                                if ($_SESSION['Bairro'] == "") {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Bairro.focus();\" class=\"titulo2\">Bairro</a>";
                                }
                            }

                            if ($_SESSION['RegistroJunta'] == "") {
                                if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { // cnpj
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório</a>";
                                }
                            } else {
                                if (! SoNumeros($_SESSION['RegistroJunta'])) {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório válido</a>";
                                }
                            }
                            
                            if ($_SESSION['DataRegistro'] == "") {
                                if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataRegistro.focus();\" class=\"titulo2\">Data do Registro na Junta Comercial ou Cartório</a>";
                                }
                            } else {
                                $MensErro = ValidaData($_SESSION['DataRegistro']);
                                
                                if ($MensErro != "") {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataRegistro.focus();\" class=\"titulo2\">Data válida</a>";
                                } else {
                                    $Hoje = date("Ymd");
                                    $Data = substr($_SESSION['DataRegistro'], - 4) . substr($_SESSION['DataRegistro'], 3, 2) . substr($_SESSION['DataRegistro'], 0, 2);
                                    
                                    if ($Data > $Hoje) {
                                        if ($_SESSION['Mens'] == 1) {
                                            $_SESSION['Mensagem'] .= ", ";
                                        }
                                        $_SESSION['Mens'] = 1;
                                        $_SESSION['Tipo'] = 2;
                                        $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataRegistro.focus();\" class=\"titulo2\">Data inferior ou igual a data atual</a>";
                                    }
                                }
                            }
                            
                            if ($_SESSION['Email'] == "") {
                                if ($_SESSION['Mens'] == 1) {
                                    $_SESSION['Mensagem'] .= ", ";
                                }
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 2;
                                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Email.focus();\" class=\"titulo2\">Email 1</a>";
                            } else {
                                if (! strchr($_SESSION['Email'], "@")) {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Email.focus();\" class=\"titulo2\">Email válido no campo 'Email 1'</a>";
                                }
                            }
                            
                            if ($_SESSION['Email2'] != "") {
                                if (! strchr($_SESSION['Email2'], "@")) {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Email2.focus();\" class=\"titulo2\">Email válido no campo 'Email 2'</a>";
                                }
                            }
                            
                            if ($_SESSION['CPFContato'] != "") {
                                $Qtd = strlen(removeSimbolos($_SESSION['CPFContato']));
                                if (($Qtd != 11) and ($Qtd != 0)) {
                                    if ($_SESSION['Mens'] == 1) {
                                        $_SESSION['Mensagem'] .= ", ";
                                    }
                                    $_SESSION['Mens'] = 1;
                                    $_SESSION['Tipo'] = 2;
                                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPFContato.focus();\" class=\"titulo2\">CPF do Contato com 11 números</a>";
                                } else {
                                    $cpfcnpj = valida_CPF(removeSimbolos($_SESSION['CPFContato']));
                                    if ($cpfcnpj === false) {
                                        if ($_SESSION['Mens'] == 1) {
                                            $_SESSION['Mensagem'] .= ", ";
                                        }
                                        $_SESSION['Mens'] = 1;
                                        $_SESSION['Tipo'] = 2;
                                        $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPFContato.focus();\" class=\"titulo2\">CPF do contato válido</a>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $db->disconnect();
        }
    }
    # Sócio - início #
    if ($_SESSION['Botao'] == "AdicionarSocio") {
        $_SESSION['MostrarNovoSocio'] = true; // no caso de erro devem ser mostrados de novo os dados

        if ($_SESSION['NoSocios'] == "") {
            $_SESSION['NoSocios'] = 0;
        }
        
        if ($_SESSION['SocioNovoNome'] == "") {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href='javascript:document.CadInscritoIncluir.SocioNovoNome.focus();' class='titulo2'>Nome do sócio</a>";
        } elseif ($_SESSION['SocioNovoCPF'] == "") {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ do sócio</a>";
        } elseif (strlen(removeSimbolos($_SESSION['SocioNovoCPF'])) != 11 and strlen(removeSimbolos($_SESSION['SocioNovoCPF'])) != 14) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
        } elseif ($_SESSION['SociosCPF_CNPJ'] != 0 and in_array($_SESSION['SocioNovoCPF'], $_SESSION['SociosCPF_CNPJ'])) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio não pode ser repetido</a>";
        } elseif (strlen(removeSimbolos($_SESSION['SocioNovoCPF'])) == 11 and ! valida_CPF(removeSimbolos($_SESSION['SocioNovoCPF'])) or strlen(removeSimbolos($_SESSION['SocioNovoCPF'])) == 14 and ! valida_CNPJ(removeSimbolos($_SESSION['SocioNovoCPF']))) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
        } else {
            $_SESSION['SociosCPF_CNPJ'][$_SESSION['NoSocios']] = $_SESSION['SocioNovoCPF'];
            $_SESSION['SociosNome'][$_SESSION['NoSocios']] = $_SESSION['SocioNovoNome'];
            $_SESSION['NoSocios'] ++;
            $_SESSION['MostrarNovoSocio'] = false;
        }
    } elseif ($_SESSION['Botao'] == "RemoverSocio") {
        $_SESSION['SociosNome'][$_SESSION['SocioSelecionado']] = null;
        $_SESSION['SociosCPF_CNPJ'][$_SESSION['SocioSelecionado']] = null;
    }
    # Sócio - fim #

    if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Preencher") or ($_SESSION['Botao'] == "Limpar")) {
        return;
    }
}

function CriticaAbaRegularidadeFiscal() { // Critica aos Campos - Formulário B
    if ($_SESSION['Botao'] == "Limpar") {
        $_SESSION['Botao']         = "";
        $_SESSION['InscEstadual']  = "";
        $_SESSION['InscMercantil'] = "";
        $_SESSION['InscOMunic']    = "";

        if ($_SESSION['DataCertidaoOb'] != 0) {
            $_SESSION['DataCertidaoOb'] = array_fill(5, count($_SESSION['DataCertidaoOb']), '');
        }

        if ($_SESSION['DataCertidaoComp'] != 0) {
            $_SESSION['DataCertidaoComp'] = array_fill(5, count($_SESSION['DataCertidaoComp']), '');
        }
        return;
    } elseif ($_SESSION['Botao'] == "RetirarComplementar") {
        if (count($_SESSION['CertidaoComplementar']) != 0) {
            $QtdComplementares =0;
            for ($i = 0; $i < count($_SESSION['CertidaoComplementar']); $i ++) {
                if ($_SESSION['CheckComplementar'][$i] == "") {
                    $QtdComplementares ++;
                    $_SESSION['CheckComplementar'][$i] = "";
                    $_SESSION['CertidaoComplementar'][$QtdComplementares - 1] = $_SESSION['CertidaoComplementar'][$i];
                    $_SESSION['DataCertidaoComp'][$QtdComplementares - 1] = $_SESSION['DataCertidaoComp'][$i];
                }
            }
            $_SESSION['CertidaoComplementar'] = array_slice($_SESSION['CertidaoComplementar'], 0, $QtdComplementares);
            $_SESSION['DataCertidaoComp'] = array_slice($_SESSION['DataCertidaoComp'], 0, $QtdComplementares);

            if (count($_SESSION['CertidaoComplementar']) == 1 and count($_SESSION['CertidaoComplementar']) == "") {
                unset($_SESSION['CertidaoComplementar']);
            }

            if (count($_SESSION['DataCertidaoComp']) == 1 and count($_SESSION['DataCertidaoComp']) == "") {
                unset($_SESSION['DataCertidaoComp']);
            }
            $_SESSION['Certidao'] = "";
        }
        return;
    } else {
        $_SESSION['Mens'] = "";
        $_SESSION['Mensagem'] = "Informe: ";

        # Verifica se as Inscrições estão vazias #
        if ($_SESSION['InscMercantil'] == "" and $_SESSION['InscOMunic'] == "" and $_SESSION['InscEstadual'] == "" and ($_SESSION['TipoHabilitacao'] == "L" || $_SESSION['CERTID_ESTRANG'])) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife, inscrição de outro munícipio ou inscrição estadual</a>";
        } else {
            # Verifica se as duas Inscrições estão preenchidas #
            if ($_SESSION['InscMercantil'] != "" and $_SESSION['InscOMunic'] != "" and ($_SESSION['TipoHabilitacao'] == "L"|| $_SESSION['CERTID_ESTRANG'])) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife ou inscrição de outro munícipio</a>";
            } else {
                # Verifica se a Inscrição Municipal é Númerica #
                if (($_SESSION['InscOMunic'] != "") and (! SoNumeros($_SESSION['InscOMunic']))) {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscOMunic.focus();\" class=\"titulo2\">Inscrição de outro município válida</a>";
                }
                if ($_SESSION['InscMercantil'] != "") {
                    // Verifica se a Inscrição Municipal Recife é Númerica #
                    if (($_SESSION['InscMercantil'] != "") && (! SoNumeros($_SESSION['InscMercantil']))) {
                        if ($_SESSION['Mens'] == 1) {
                            $_SESSION['Mensagem'] .= ", ";
                        }
                        $_SESSION['Mens'] = 1;
                        $_SESSION['Tipo'] = 2;
                        $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife válida</a>";
                    } else {
                        # Pesquisa se Inscrição Mercantil é Válida no Banco de Dados #
                        // $_SESSION['InscricaoValida'] = "S";
                        if ($_SESSION['InscricaoValida'] == "") {
                            $NomePrograma = urlencode("CadInscritoIncluir.php");
                            $Url = "RotConsultaInscricaoMercantil.php?NomePrograma=$NomePrograma&InscricaoMercantil=" . $_SESSION['InscMercantil'] . "&Destino=" . $_SESSION['DestinoInsc'] . "";

                            if (! in_array($Url, $_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }
                            header('Location:' . $Url);
                            exit();
                        } else {
                            if ($_SESSION['InscricaoValida'] == "N") {
                                if ($_SESSION['Mens'] == 1) {
                                    $_SESSION['Mensagem'] .= ", ";
                                }
                                $_SESSION['Mens'] = 1;
                                $_SESSION['Tipo'] = 2;
                                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife válida</a>";
                            }
                        }
                    }
                }
            }
        }

        if ($_SESSION['InscEstadual'] != "" and (! SoNumeros($_SESSION['InscEstadual']))) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscEstadual.focus();\" class=\"titulo2\">Inscrição estadual válida</a>";
        }

        # Criando o Array de Certidões Complementares #
        if ($_SESSION['Certidao'] != "") {
            if (! isset($_SESSION['CertidaoComplementar'])) {
                $_SESSION['CertidaoComplementar'] = array();
            }
            if ($_SESSION['CertidaoComplementar'] == "" || ! in_array($_SESSION['Certidao'], $_SESSION['CertidaoComplementar'])) {
                $_SESSION['CertidaoComplementar'][count($_SESSION['CertidaoComplementar'])] = $_SESSION['Certidao'];
            }
        }

        # Verifica se as Data de Certdão Obrigatória estão vazias #
        if ($_SESSION['DataCertidaoOb'] != 0) {
            $cont =0;
            $con =0;
            for ($i = 0; $i < count($_SESSION['DataCertidaoOb']); $i ++) {
                if ($_SESSION['DataCertidaoOb'][$i] == "") {
                    $cont ++;

                    if ($cont == 1) {
                        $PosOb = $i;
                    }

                    if ($i < 7) {
                        $ExisteDataOb = "N";
                    }

                    if (($i == 7) and (empty($_SESSION['MicroEmpresa']))) {
                        $ExisteDataOb = "N";
                    }
                } else {
                    if (ValidaData($_SESSION['DataCertidaoOb'][$i])) {
                        $con ++;

                        if ($con == 1) {
                            $PosOb = $i;
                        }
                        $DataValidaOb = "N";
                    }
                }
            }
            $PosOb = ($PosOb * 2) + 6;

            if (($ExisteDataOb == "N") and ($_SESSION['TipoHabilitacao'] == "L" || $_SESSION['CERTID_ESTRANG'])) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosOb].focus();\" class=\"titulo2\">Data(s) de validade da(s) certidão(ões) obrigatória(s)</a>";
            } elseif ($DataValidaOb == "N") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosOb].focus();\" class=\"titulo2\">Data(s) de validade da(s) certidão(ões) obrigatória(s) válida</a>";
            }
        }

        # Verifica se as Data de Certdão Complementar estão vazias #
        if ($_SESSION['DataCertidaoComp'] != 0) {
            for ($i = 0; $i < count($_SESSION['DataCertidaoComp']); $i ++) {
                if ($_SESSION['DataCertidaoComp'][$i] == "") {
                    $cont ++;

                    if ($cont == 1) {
                        $PosComp = $i + 1;
                    }
                    $ExisteDataOp = "N";
                } else {
                    if (ValidaData($_SESSION['DataCertidaoComp'][$i])) {
                        $con ++;

                        if ($con == 1) {
                            $PosComp = $i;
                        }
                        $DataValidaComp = "N";
                    }
                }
            }
            $PosOb = count($_SESSION['DataCertidaoOb']);

            if ($ExisteDataOp == "N") {
                $PosComp = ($PosComp * 2) + $PosOb + 10;

                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosComp].focus();\" class=\"titulo2\">Data de validade da certidão complementar</a>";
            } elseif ($DataValidaComp == "N") {
                $PosComp = ($PosComp * 2) + $PosOb + 12;

                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosComp].focus();\" class=\"titulo2\">Data(s) de validade da(s) certidão(ões) complementar(es) válida</a>";
            }
        }
    }

    if (($_SESSION['Mens'] != "") or ($_SESSION['Botao'] == "Limpar")) {
        return;
    }
}

function CriticaAbaQualificEconFinanceira() { // Critica aos Campos - Formulário C
    if ($_SESSION['Botao'] == "Limpar") {
        $_SESSION['Botao']                = "";
        $_SESSION['CapSocial']            = "";
        $_SESSION['CapIntegralizado']     = "";
        $_SESSION['Patrimonio']           = "";
        $_SESSION['IndLiqCorrente']       = "";
        $_SESSION['IndLiqGeral']          = "";
        $_SESSION['IndEndividamento']     = "";
        $_SESSION['IndSolvencia']         = "";
        $_SESSION['DataBalanco']          = "";
        $_SESSION['DataNegativa']         = "";
        $_SESSION['DataContratoEstatuto'] = "";
        $_SESSION['Banco1']               = "";
        $_SESSION['Banco2']               = "";
        $_SESSION['Agencia1']             = "";
        $_SESSION['Agencia2']             = "";
        $_SESSION['ContaCorrente1']       = "";
        $_SESSION['ContaCorrente2']       = "";
    } else {
        $_SESSION['Mens'] = "";
        $_SESSION['Mensagem'] = "Informe: ";

        if (($_SESSION['TipoCnpjCpf'] == "CNPJ") && ($_SESSION['TipoHabilitacao'] == 'L')) { // compra direta não mostra os campos de qualificação econômica financeira (excessão de banco)
            if ($_SESSION['CapSocial'] == "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapSocial.focus();\" class=\"titulo2\">Capital social</a>";
            } else {
                if ($_SESSION['CapSocial'] == 0) {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapSocial.focus();\" class=\"titulo2\">O capital social deve ser númerico e diferente de zero</a>";
                }
            }

            if ($_SESSION['CapIntegralizado'] != "") {
            }

            if ($_SESSION['Patrimonio'] == "") {
                if (empty($_SESSION['MicroEmpresa'])) {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Patrimonio.focus();\" class=\"titulo2\">Patrimônio líquido</a>";
                }
            } else {
                if ($_SESSION['Patrimonio'] == 0) {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Patrimonio.focus();\" class=\"titulo2\">O patrimônio líquido deve ser númerico e diferente de zero</a>";
                }
            }

            if ($_SESSION['DataBalanco'] == "") {
                if (empty($_SESSION['MicroEmpresa'])) {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço</a>";
                }
            } else {
                $MensErro = ValidaData($_SESSION['DataBalanco']);

                if ($MensErro != "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço Válida</a>";
                } else {
                    $DataBalancoInv = substr($_SESSION['DataBalanco'], 6, 4) . "-" . substr($_SESSION['DataBalanco'], 3, 2) . "-" . substr($_SESSION['DataBalanco'], 0, 2);


                    if ($DataBalancoInv <= date("Y-m-d")) {
                        if ($_SESSION['Mens'] == 1) {
                            $_SESSION['Mensagem'] .= ", ";
                        }
                        $_SESSION['Mens'] = 1;
                        $_SESSION['Tipo'] = 2;
                        $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço menor que data atual</a>";
                    }
                }
            }

            if ($_SESSION['DataNegativa'] == "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataNegativa.focus();\" class=\"titulo2\">Data de certidão negativa de falência ou concordata</a>";
            } else {
                $MensErro = ValidaData($_SESSION['DataNegativa']);

                if ($MensErro != "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataNegativa.focus();\" class=\"titulo2\">Data de certidão negativa de falência ou concordata válida</a>";
                }
            }

            if ($_SESSION['DataContratoEstatuto'] != "") {
                $MensErro = ValidaData($_SESSION['DataContratoEstatuto']);
                if ($MensErro != "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataContratoEstatuto.focus();\" class=\"titulo2\">Data de contrato ou estatuto válida</a>";
                }
            }
        }

        if (($_SESSION['ContaCorrente1'] == $_SESSION['ContaCorrente2']) and ($_SESSION['ContaCorrente1'] != "" and $_SESSION['ContaCorrente2'] != "")) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.ContaCorrente1.focus();\" class=\"titulo2\">Contas correntes diferentes</a>";
        }

        if ($_SESSION['Banco1'] != "") {
            if (strlen($_SESSION['Banco1']) != 3) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco1.focus();\" class=\"titulo2\">Código do banco 1 deve possuir 3 dígitos</a>";
            } else {
                if ($_SESSION['Agencia1'] == "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Agencia1.focus();\" class=\"titulo2\">Agência do Banco " . $_SESSION['Banco1'] . "</a>";
                }

                if ($_SESSION['ContaCorrente1'] == "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.ContaCorrente1.focus();\" class=\"titulo2\">Conta corrente do banco " . $_SESSION['Banco1'] . "</a>";
                }
            }
        } else {
            if ($_SESSION['Agencia1'] != "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco1.focus();\" class=\"titulo2\">Banco da agência " . $_SESSION['Agencia1'] . "</a>";
            } elseif ($_SESSION['ContaCorrente1'] != "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco1.focus();\" class=\"titulo2\">Banco da conta corrente " . $_SESSION['ContaCorrente1'] . "</a>";
            } else {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco1.focus();\" class=\"titulo2\">1ª conta de banco é requerida</a>";
            }
        }

        if ($_SESSION['Banco2'] != "") {
            if (strlen($_SESSION['Banco2']) != 3) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco2.focus();\" class=\"titulo2\">Código do banco 2 deve possuir 3 dígitos</a>";
            } else {
                if ($_SESSION['Agencia2'] == "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Agencia2.focus();\" class=\"titulo2\">Agência do banco " . $_SESSION['Banco2'] . "</a>";
                }

                if ($_SESSION['ContaCorrente2'] == "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.ContaCorrente2.focus();\" class=\"titulo2\">Conta corrente do banco " . $_SESSION['Banco2'] . "</a>";
                }
            }
        } else {

            if ($_SESSION['Agencia2'] != "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco2.focus();\" class=\"titulo2\">Banco da agência " . $_SESSION['Agencia2'] . "</a>";
            } else {
                if ($_SESSION['ContaCorrente2'] != "") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.ContaCorrente2.focus();\" class=\"titulo2\">Banco da conta corrente " . $_SESSION['ContaCorrente2'] . "</a>";
                }
            }
        }
    }

    if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar")) {
        return;
    }
}
function consultaNaturezaJuridica(){
    $sql = "SELECT cfornjsequ, efornjtpnj from sfpc.tbfornecedortiponaturezajuridica";
    $result = executarPGSQL($sql);
    
    while($result->fetchInto($dadosResultado, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $dadosResultado;
    }
    return $dadosRetorno;
}

function plotaBlocoNatureza($tpl){
    $natureza = consultaNaturezaJuridica();
    foreach($natureza as $dadosNatureza){
        $tpl->VALUE_NATUREZA = $dadosNatureza->cfornjsequ;
        $tpl->DESCRICAO_NATUREZA = $dadosNatureza->efornjtpnj;
        $tpl->block('bloco_natureza');
    }
}

function configurarBlocoHabilitacao($tpl) {
    $tipoCnpjCpf           = $_SESSION['TipoCnpjCpf'];
    $valorCpfCnpj          = $_SESSION['CPF_CNPJ'];
    $valorEstrang          = $_SESSION['CERTID_ESTRANG'];
    $tipoHabilitacao       = $_SESSION['TipoHabilitacao'];
    $microEmpresa          = $_SESSION['MicroEmpresa'];
    $identidade            = $_SESSION['Identidade'];
    $orgaoUF               = $_SESSION['OrgaoUF'];
    $razaoSocial           = $_SESSION['RazaoSocial'];
    $nomeFantasia          = $_SESSION['NomeFantasia'];
    $cepInformado          = $_SESSION['CEPInformado'];
    $cepAntes              = $_SESSION['CEPAntes'];
    $logradouro            = $_SESSION['Logradouro'];
    $localidade            = $_SESSION['Localidade'];
    $numero                = $_SESSION['Numero'];
    $complemento           = $_SESSION['Complemento'];
    $bairro                = $_SESSION['Bairro'];
    $cidade                = $_SESSION['Cidade'];
    $uf                    = $_SESSION['UF'];
    $ddd                   = $_SESSION['DDD'];
    $telefone              = $_SESSION['Telefone'];
    $email                 = $_SESSION['Email'];
    $email2                = $_SESSION['Email2'];
    $fax                   = $_SESSION['Fax'];
    $registroJunta         = $_SESSION['RegistroJunta'];
    $dataRegistro          = $_SESSION['DataRegistro'];
    $nomeContato           = $_SESSION['NomeContato'];
    $cpfContato            = $_SESSION['CPFContato'];
    $cargoContato          = $_SESSION['CargoContato'];
    $dddContato            = $_SESSION['DDDContato'];
    $telefoneContato       = $_SESSION['TelefoneContato'];
    $numeroSocios          = $_SESSION['NoSocios'];
    $sociosCPFCNPJ         = $_SESSION['SociosCPF_CNPJ'];
    $sociosNome            = $_SESSION['SociosNome'];
    $mostraNovoSocio       = $_SESSION['MostrarNovoSocio'];
    $tiposHabilitacao      = array();
    $tiposHabilitacao["D"] = "COMPRA DIRETA";
    $tiposHabilitacao["L"] = "LICITAÇÃO";

    /* Regras para formação dos blocos para a aba de habilitação */
    foreach ($tiposHabilitacao as $valor => $texto) {
        $tpl->VALUE = $valor;
        $tpl->DESCRICAO = $texto;

        if ($tipoHabilitacao === "D" && $valor === $tipoHabilitacao) {
            $tpl->SELECTED = "selected";
        } elseif ($tipoHabilitacao != "E" && $valor === $tipoHabilitacao) {
            $tpl->SELECTED = "selected";
        }
        $tpl->block("bloco_tipo_habitacao");
    }
    plotaBlocoNatureza($tpl);
    $orgaoUF = $_SESSION['OrgaoUF'];

    $tpl->CPFCNPJ           = removeSimbolos($valorCpfCnpj);
    $tpl->VALUE_ESTRANG           = removeSimbolos($valorEstrang);
    $tpl->IDENTIDADE        = $identidade;
    $tpl->NOME              = $razaoSocial;
    $tpl->FANTASIA          = $nomeFantasia;
    $tpl->CEP               = $cepInformado;
    $tpl->CEPANTES          = $cepAntes;
    $tpl->LOGRADOURO        = $logradouro;
    $tpl->NUMERO            = $numero;
    $tpl->COMPLEMENTO       = $complemento;
    $tpl->BAIRRO            = $bairro;
    $tpl->CIDADE            = $cidade;
    $tpl->UF                = $uf;
    $tpl->DDD               = $ddd;
    $tpl->TELEFONE          = $telefone;
    $tpl->EMAIL             = $email;
    $tpl->EMAIL2            = $email2;
    $tpl->REGIJUNTCOMER     = $registroJunta;
    $tpl->DATAJUNTCOMERCIAL = $dataRegistro;
    $tpl->CPFCONTATO        = $cpfContato;
    $tpl->NOMECONTATO       = $nomeContato;
    $tpl->DDDCONTATO        = $dddContato;
    $tpl->TELEFONECONTATO   = $telefoneContato;
    $tpl->Fax               = $fax;
    $tpl->NoSocios          = $numeroSocios;

    if ($_SESSION['TipoCnpjCpf'] == "" or $_SESSION['TipoCnpjCpf'] == "CPF") {
        $tpl->CHECKCPF = "checked";
    } elseif ($tpl->CHECKCNPJ = $_SESSION['TipoCnpjCpf'] == "CNPJ") {
        $tpl->CHECKCNPJ = "checked";
    }else{
        $tpl->CERTIDAO_ESTRANGEIRA = "checked";
    }
    
    $tpl->block("bloco_radio");

    if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
        $tpl->DESCRIPORTEEMPRETITULO = getDescPorteEmpresaTitulo();
        ob_start();
        selectPorteEmpresa($_SESSION['MicroEmpresa']);
        $select = ob_get_contents();
        ob_end_clean();

        $tpl->COMBOPORTEEMPRESA = $select;
        $tpl->block("bloco_empresa");
        $tpl->block("BLOCO_AJUSTA_MARGEM_TOPO_RADIO");
        $tpl->block("BLOCO_AJUSTA_MARGEM_TOPO_VALOR");
    }

    $tpl->UFORGAO = $orgaoUF;
    $tpl->DESCRCAMPOUFORGAO = $tipoCnpjCpf == "CNPJ" ? "Órgão Emissor/UF\n" : "Órgão Emissor/UF*\n";

    $tpl->DESCCAMPOIDENTIDADE = $tipoCnpjCpf == "CNPJ" ? "Identidade Repres.Legal(Empr.Individual)\n" : "Identidade*\n";
    $tpl->block("bloco_identidade");

    $tpl->block("bloco_dados_empresa");

    $tpl->DESCRCAMPRAZAOSOC = $tipoCnpjCpf == "CNPJ" ? "Razão Social*\n" : "Nome*\n";
    $tpl->block("bloco_razao_social");

    $tpl->ACAOLOGRADOURO = $localidade == "" ? "" : "";
    $tpl->block("bloco_logradouro");

    $tpl->ACAOBAIRRO = $localidade == "" ? "" : "";
    $tpl->block("bloco_bairro");

    $tpl->ACAO_CIDADE = ($localidade == "") ? "" : "";

    $tpl->ACAO_UF = ($localidade == "") ? "" : "";

    $tpl->OBRIGAREGISTRO = $tipoCnpjCpf == "CNPJ" ? "*" : "";
    $tpl->block("bloco_registro_junta_comercial");

    $tpl->OBRIGADATAJUNTA = $tipoCnpjCpf == "CNPJ" ? "*" : "";
    $tpl->block("bloco_data_reg");

    if ($tipoCnpjCpf == "CNPJ") {
        for ($itr = 0; $itr < $numeroSocios; $itr ++) {
            if (! is_null($_SESSION['SociosCPF_CNPJ'][$itr])) {
                $tpl->SociosNome            = $_SESSION['SociosNome'][$itr];
                $tpl->SociosCPF_CNPJ        = $_SESSION['SociosCPF_CNPJ'][$itr];
                $tpl->POSICAO               = $itr;
                $tpl->SociosNomePosicao     = $_SESSION['SociosNome'][$itr];
                $tpl->SociosCPF_CNPJPosicao = $_SESSION['SociosCPF_CNPJ'][$itr];
                $tpl->block("bloco_valores_socios");
            }
        }
        $tpl->block("bloco_CAMPOS_socios");
        $tpl->block("bloco_mostra_socios");
    }
}

function CriticaAbaQualificTecnica() { // Critica aos Campos - Formulário D
    
    if ($_SESSION['Botao'] == "Limpar") {
        $_SESSION['Botao']            = "";
        $_SESSION['RegistroEntidade'] = "";
        $_SESSION['NomeEntidade']     = "";
        $_SESSION['DataVigencia']     = "";
        $_SESSION['RegistroTecnico']  = "";
    } elseif ($_SESSION['Botao'] == "RetirarAutorizacao") {
        $QtdAutorizacao = 0;
        
        if (count($_SESSION['AutorizacaoNome']) > 0) {
            for ($i = 0; $i < count($_SESSION['AutorizacaoNome']); $i ++) {
                if ($_SESSION['CheckAutorizacao'][$i] == "") {
                    $QtdAutorizacao ++;
                    $_SESSION['CheckAutorizacao'][$i]                     = "";
                    $_SESSION['AutorizacaoNome'][$QtdAutorizacao - 1]     = $_SESSION['AutorizacaoNome'][$i];
                    $_SESSION['AutorizacaoRegistro'][$QtdAutorizacao - 1] = $_SESSION['AutorizacaoRegistro'][$i];
                    $_SESSION['AutorizacaoData'][$QtdAutorizacao - 1]     = $_SESSION['AutorizacaoData'][$i];
                    $_SESSION['AutoEspecifica'][$QtdAutorizacao - 1]      = $_SESSION['AutoEspecifica'][$i];
                }
            }
            $_SESSION['AutorizacaoNome']     = array_slice($_SESSION['AutorizacaoNome'], 0, $QtdAutorizacao);
            $_SESSION['AutorizacaoRegistro'] = array_slice($_SESSION['AutorizacaoRegistro'], 0, $QtdAutorizacao);
            $_SESSION['AutorizacaoData']     = array_slice($_SESSION['AutorizacaoData'], 0, $QtdAutorizacao);
            $_SESSION['AutoEspecifica']      = array_slice($_SESSION['AutoEspecifica'], 0, $QtdAutorizacao);

            if ($_SESSION['AutorizacaoNome'][0] == "" or count($_SESSION['AutorizacaoNome']) == 0) {
                unset($_SESSION['AutorizacaoNome']);
            }

            if ($_SESSION['AutorizacaoRegistro'][0] == "" or count($_SESSION['AutorizacaoRegistro']) == 0) {
                unset($_SESSION['AutorizacaoRegistro']);
            }

            if ($_SESSION['AutorizacaoData'][0] == "" or count($_SESSION['AutorizacaoData']) == 0) {
                unset($_SESSION['AutorizacaoData']);
            }

            if ($_SESSION['AutoEspecifica'][0] == "" or count($_SESSION['AutoEspecifica']) == 0) {
                unset($_SESSION['AutoEspecifica']);
            }
        }
        
        return;
    } elseif ($_SESSION['Botao'] == "RetirarGrupos") {
        $QtdMateriais = 0;

        if (count($_SESSION['Materiais']) != 0) {
            for ($i = 0; $i < count($_SESSION['Materiais']); $i ++) {
                if ($_SESSION['CheckMateriais'][$i] == "") {
                    $QtdMateriais ++;
                    $_SESSION['CheckMateriais'][$i] = "";
                    $_SESSION['Materiais'][$QtdMateriais - 1] = $_SESSION['Materiais'][$i];
                }
            }
            $_SESSION['Materiais'] = array_slice($_SESSION['Materiais'], 0, $QtdMateriais);

            if (count($_SESSION['Materiais']) == 1 and count($_SESSION['Materiais']) == "") {
                unset($_SESSION['Materiais']);
            }
        }
        $QtdServicos = 0;
        
        if (count($_SESSION['Servicos']) != 0) {
            for ($i = 0; $i < count($_SESSION['Servicos']); $i ++) {
                if ($_SESSION['CheckServicos'][$i] == "") {
                    $QtdServicos ++;
                    $_SESSION['CheckServicos'][$i] = "";
                    $_SESSION['Servicos'][$QtdServicos - 1] = $_SESSION['Servicos'][$i];
                }
            }
            $_SESSION['Servicos'] = array_slice($_SESSION['Servicos'], 0, $QtdServicos);

            if (count($_SESSION['Servicos']) == 1 and count($_SESSION['Servicos']) == "") {
                unset($_SESSION['Servicos']);
            }
        }
        return;
    } else {
        
        $_SESSION['Mens'] = "";
        $_SESSION['Mensagem'] = "Informe: ";

        if (isset($_SESSION['RegistroEntidade']) and ! SoNumeros($_SESSION['RegistroEntidade'])) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroEntidade.focus();\" class=\"titulo2\">Registro da entidade válido</a>";
        }
        $MensErro = ValidaData($_SESSION['DataVigencia']);

        if ($_SESSION['DataVigencia'] != "" and $MensErro != "") {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataVigencia.focus();\" class=\"titulo2\">$MensErro</a>";
        }

        if (($_SESSION['RegistroTecnico'] != "") and (! SoNumeros($_SESSION['RegistroTecnico']))) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroTecnico.focus();\" class=\"titulo2\">Registro ou inscrição do técinco válida</a>";
        }

        # Constuindo o array de Autorização Específica #
        if ($_SESSION['AutorizaNome'] != "" and $_SESSION['AutorizaRegistro'] != "" and $_SESSION['AutorizaData'] != "") {
            if (! isset($_SESSION['AutorizacaoNome'])) {
                $_SESSION['AutorizacaoNome'] = array();
            }

            if (! isset($_SESSION['AutorizacaoRegistro'])) {
                $_SESSION['AutorizacaoRegistro'] = array();
            }

            if (! isset($_SESSION['AutorizacaoData'])) {
                $_SESSION['AutorizacaoData'] = array();
            }
        }
        
        if (count($_SESSION['Materiais']) == 0 and count($_SESSION['Servicos']) == 0) {
            if ($_SESSION['Mens'] == 1) {
                $_SESSION['Mensagem'] .= ", ";
            }

            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "Pelo menos um grupo de fornecimento deve ser incluído";
        }

        if ($_SESSION['TipoHabilitacao'] == 'L') {
            if ($_SESSION['Cumprimento'] == "") {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['Mensagem'] .= ", ";
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "Resposta para o cumprimento da Lei";
            } else {
                if ($_SESSION['Cumprimento'] == "N") {
                    if ($_SESSION['Mens'] == 1) {
                        $_SESSION['Mensagem'] .= ", ";
                    }
                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 2;
                    $_SESSION['Mensagem'] = "A inscrição só será efetivada se o fornecedor cumprir o que está disposto no Inc. XXXIII do Art. 7º da Constituição Federal";
                    $_SESSION['Cumprimento'] = "S";
                }
            }
        }
    }
    
    if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar") or ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir")) {
        return;
    }
}

function CriticaAbaDocumentos() { // Formulário E - Documentos

	$DDocumento = $_SESSION['DDocumento'];


	if ($_SESSION['Botao'] == "Limpar") {

		$_SESSION['Botao'] = "";
		$_SESSION['tipoDoc'] = 0;
		$_SESSION['obsDocumento'] = "";

	} elseif  ($_SESSION['Botao'] == 'IncluirDocumento') {

		$_SESSION['Mens']     = "";
		$_SESSION['Mensagem'] = "Informe: ";



		if ($_POST['tipoDoc'] == '0' ) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "Tipo do documento deve ser preenchido";
		}else{

			$db = Conexao();
			$parametrosGerais = dadosParametrosGerais($db);

			$tamanhoArquivo = $parametrosGerais[4];
			$tamanhoNomeArquivo = $parametrosGerais[5];
			$extensoesArquivo = $parametrosGerais[6];


			//$Critica = 0;

			if ($_FILES['Documentacao']['tmp_name']) {
				$_FILES['Documentacao']['name'] = RetiraAcentos($_FILES['Documentacao']['name']);

				$extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf';

				$extensoes = explode(',', strtolower2($extensoesArquivo));
				array_push($extensoes, '.zip', '.xlsm');

				$noExtensoes = count($extensoes);
				$isExtensaoValida = false;
				for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
					if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
						$isExtensaoValida = true;
					}
				}
				if (! $isExtensaoValida) {
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
				}
				if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'].= ', ';
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
				}
				$Tamanho = 5120*1000;
				if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
					if ($_SESSION['Mens']  == 1) {
						$_SESSION['Mensagem'] .= ', ';
					}
					$Kbytes = $Tamanho;
					$Kbytes = (int) $Kbytes;
					$_SESSION['Mens']= 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: 5 MB";
				}
				if ($_SESSION['Mens'] == '') {
					if (! ($_SESSION['Arquivos_Upload_Insc']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
						$_SESSION['Mens']= 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] = 'Caminho da Documentação Inválido';
					} else {
						$_SESSION['Arquivos_Upload_Insc']['nome'][] = $_FILES['Documentacao']['name'];
						$_SESSION['Arquivos_Upload_Insc']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
						$_SESSION['Arquivos_Upload_Insc']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
						$_SESSION['Arquivos_Upload_Insc']['tipoCod'][] = $_POST['tipoDoc']; 
						$_SESSION['Arquivos_Upload_Insc']['tipoDocumentoDesc'][] = $_POST['tipoDocDesc']; 
						$_SESSION['Arquivos_Upload_Insc']['observacao'][] = strtoupper2($_POST['obsDocumento']); 
						$_SESSION['Arquivos_Upload_Insc']['dataHora'][] = date('d/m/Y H:i'); 

						$_SESSION['tipoDoc'] = 0;
						$_SESSION['obsDocumento'] = "";
					}
				}

			} else {
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] = 'Falta anexar o documento';
			}
		}
	} elseif ($_SESSION['Botao'] == 'RetirarDocumento') {
		//$Critica = 0;

		if ($DDocumento){
			foreach ($DDocumento as $valor) {

				if ($_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] == 'novo') {
					$_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
				} elseif ($_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] == 'existente') {
					$_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
				}

			}

		}else{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] = 'Selecione um anexo para ser retirado';
		}
	}

	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar") or ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir") or ($_SESSION['Botao'] == "IncluirDocumento") or ($_SESSION['Botao'] == "RetirarDocumento")) {
		return;
	}
}

function consultar($tpl) {
    $db = Conexao();
    $ErroPrograma ="CadInscritoIncluir";
    $sql = "SELECT  CTIPCECODI, ETIPCEDESC
            FROM    SFPC.TBTIPOCERTIDAO
            WHERE   FTIPCEOBRI = 'S'
            ORDER BY CTIPCECODI ";

    $res = executarSQL($db, $sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $ob = 0;

        while ($Linha = $res->fetchRow()) {
            $Descricao  = substr($Linha[1], 0, 75);
            $CertidaoOb = $Linha[0];
            $ElementoOb = (2 * $ob) + 3;
            
            $tpl->DESCRICAOCERTIDAO    = $Descricao;
            $tpl->ID                   = $ob;
            $tpl->INPUT_HIDDEN         = '<input type="hidden" name="CertidaoObrigatoria[' . $ob . ']" value="' . $CertidaoOb . '">';
            $tpl->DATAVALIDADECERTIDAO = $_SESSION['DataCertidaoOb'][$ob];

            $ob ++;

            $tpl->block("blocoTabelaCertidao");
        }
    }
    $db->disconnect();
}

function configuraBlocoQualificacaoEconomica($tpl) {
    $tpl->Banco1               = $_SESSION['Banco1'];
    $tpl->Agencia1             = $_SESSION['Agencia1'];
    $tpl->ContaCorrente1       = $_SESSION['ContaCorrente1'];
    $tpl->Banco2               = $_SESSION['Banco2'];
    $tpl->Agencia2             = $_SESSION['Agencia2'];
    $tpl->ContaCorrente2       = $_SESSION['ContaCorrente2'];
    $tpl->CapSocial            = $_SESSION['CapSocial'];
    $tpl->CapIntegralizado     = $_SESSION['CapIntegralizado'];
    $tpl->Patrimonio           = $_SESSION['Patrimonio'];
    $tpl->IndLiqCorrente       = $_SESSION['IndLiqCorrente'];
    $tpl->IndLiqGeral          = $_SESSION['IndLiqGeral'];
    $tpl->IndEndividamento     = $_SESSION['IndEndividamento'];
    $tpl->IndSolvencia         = $_SESSION['IndSolvencia'];
    $tpl->DataBalanco          = $_SESSION['DataBalanco'];
    $tpl->DataNegativa         = $_SESSION['DataNegativa'];
    $tpl->DataContratoEstatuto = $_SESSION['DataContratoEstatuto'];

    $tipoCnpjCpf = $_SESSION['TipoCnpjCpf'];

    if($tipoCnpjCpf == 'ESTRANGEIRO'){
        $tpl->CPFCNPJDESCRICAOCAMPO      = "CERTIDÃO ESTRANGEIRA";
        $tpl->CPFCNPJ                    =  $_SESSION['CERTID_ESTRANG'];
    }else{
        $tpl->CPFCNPJDESCRICAOCAMPO      = $tipoCnpjCpf == "CNPJ" ? "CNPJ" : "CPF";
        $tpl->CPFCNPJ                    = $_SESSION['CPF_CNPJ'];
    } 
    $tpl->CERTIDAO_ESTRANGEIRA               = $_SESSION['CERTID_ESTRANG'];
    $tpl->DESCRCAMPRAZAOSOC     = $tipoCnpjCpf == "CNPJ" ? "Razão Social\n" : "Nome\n";
    $tpl->RazaoSocial           = $_SESSION['RazaoSocial'];

    if (($_SESSION['TipoCnpjCpf'] == "CNPJ") && ($_SESSION['TipoHabilitacao'] == 'L')) {
        $tpl->OBRIGATORIOPATRI = empty($_SESSION['MicroEmpresa']) ? "*" : "";
        $tpl->OBRIGATORIODATA  = empty($_SESSION['MicroEmpresa']) ? "*" : "";
    }

    if (($_SESSION['TipoCnpjCpf'] == "CNPJ") && ($_SESSION['TipoHabilitacao'] == 'L')) {
        $tpl->block("bloco_tipo_habilitacao");
    }
}

function configurarBlocoQualificacaoTecnica($tpl) {
    if (isset($_POST['EmailPopup'])) {
        $_SESSION['EmailPopup'] = $_POST['EmailPopup'];
    }

    $tipoCnpjCpf = $_SESSION['TipoCnpjCpf'];

    $tpl->URLGRUPOS                  = 'CadIncluirGrupos.php?ProgramaOrigem=CadInscritoIncluir';

    if($tipoCnpjCpf == 'ESTRANGEIRO'){
        $tpl->CPFCNPJDESCRICAOCAMPO      = "CERTIDÃO ESTRANGEIRA";
        $tpl->CPFCNPJ                    =  $_SESSION['CERTID_ESTRANG'];
    }else{
        $tpl->CPFCNPJDESCRICAOCAMPO      = $tipoCnpjCpf == "CNPJ" ? "CNPJ" : "CPF";
        $tpl->CPFCNPJ                    = $_SESSION['CPF_CNPJ'];
    } 
    
    $tpl->CERTIDAO_ESTRANGEIRA       = $_SESSION['CERTID_ESTRANG'];
    $tpl->DESCRCAMPRAZAOSOC          = $tipoCnpjCpf == "CNPJ" ? "Razão Social\n" : "Nome\n";
    $tpl->RazaoSocial                = $_SESSION['RazaoSocial'];
    $tpl->SIMCHECKED                 = ($_SESSION['Cumprimento'] == "S") ? "checked" : "";
    $tpl->NAOCHECKED                 = ($_SESSION['Cumprimento'] == "N") ? "checked" : "";
    $tpl->NOME_ENTIDADE              = $_SESSION['NomeEntidade'];
    $tpl->REGISTRO_INSCRICAO         = $_SESSION['RegistroEntidade'];
    $tpl->DATA_VIGENCIA_PROFISSIONAL = $_SESSION['DataVigencia'];
    $tpl->REGISTRO_INSCRICAO_TECNICO = $_SESSION['RegistroTecnico'];
    $tpl->EmailPopup                 = $_SESSION["EmailPopup"];

    configurarBlocoGrupoFornecimento($tpl);
    configurarBlocoAutorizaoEspecifica($tpl);

    $tpl->block("bloco_dados_relativo");
}

function configurarBlocoDocumentos($tpl) {
    $ErroPrograma ="CadInscritoIncluir";
    $tipoCnpjCpf = $_SESSION['TipoCnpjCpf'];
    if($tipoCnpjCpf == 'ESTRANGEIRO'){
        $tpl->CPFCNPJDESCRICAOCAMPO      = "CERTIDÃO ESTRANGEIRA";
        $tpl->CPFCNPJ                    =  $_SESSION['CERTID_ESTRANG'];
    }else{
        $tpl->CPFCNPJDESCRICAOCAMPO      = $tipoCnpjCpf == "CNPJ" ? "CNPJ" : "CPF";
        $tpl->CPFCNPJ                    = $_SESSION['CPF_CNPJ'];
    }   
    $tpl->CERTIDAO_ESTRANGEIRA       = $_SESSION['CERTID_ESTRANG'];
    $tpl->DESCRCAMPRAZAOSOC          = $tipoCnpjCpf == "CNPJ" ? "Razão Social\n" : "Nome\n";
    $tpl->RazaoSocial                = $_SESSION['RazaoSocial'];
    $tpl->obsDocumento               = $_SESSION['obsDocumento'];
    $tpl->tipoDocDesc                = $_SESSION['tipoDocDesc'];

    // Tipos de documento
    $htmlTipoDoc = '';

    $db = Conexao();
    $sql = "SELECT CFDOCTCODI, EFDOCTDESC, ffdoctobri FROM
            SFPC.TBFORNECEDORDOCUMENTOTIPO
            WHERE FFDOCTSITU = 'A' ORDER BY afdoctorde, EFDOCTDESC";
    $res = executarSQL($db, $sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {

        while ($tipoDoc = $res->fetchRow()) {

            $docObrigatorio = '';
            if($tipoDoc[2] == 'S'){
                $docObrigatorio = ' (Obrigatório)';
            }

            if($tipoDoc[0] == $_SESSION['tipoDoc'] ){
                $htmlTipoDoc .= '<option value="'.$tipoDoc[0].'" selected>'.$tipoDoc[1].$docObrigatorio.'</option>';
            }else{
                $htmlTipoDoc .= '<option value="'.$tipoDoc[0].'">'.$tipoDoc[1].$docObrigatorio.'</option>';
            }

        }
    }

    //Documentos Anexados até o momento
    $htmlDoc = '';
    $htmlBotaoRetirarDoc = '';
    //var_dump($_SESSION['Arquivos_Upload_Insc']);
    //die();
    $qtd_anexo = 0;

        for ($j = 0; $j < count($_SESSION['Arquivos_Upload_Insc']['conteudo']) ; ++ $j) {
            if ($_SESSION['Arquivos_Upload_Insc']['situacao'][$j] == 'novo' ||  $_SESSION['Arquivos_Upload_Insc']['situacao'][$j] == 'existente') {
                $qtd_anexo++;
            }
        }

    if( $qtd_anexo > 0){
        $htmlDoc .= '<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        <tr>
            <td bgcolor="#75ADE6" class="textoabasoff" colspan="7" align="center">DOCUMENTOS ANEXADOS</td>
        </tr>
        <tr>
            <td bgcolor="#bfdaf2" align="center"><b>  </b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Tipo do documento</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Nome</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Responsável anexação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Data/Hora Anexação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Situação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Observação</b></td>
        </tr> ';


        $DTotal = count($_SESSION['Arquivos_Upload_Insc']['conteudo']);
        for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
            if ($_SESSION['Arquivos_Upload_Insc']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload_Insc']['situacao'][$Dcont] == 'existente') {
                $htmlDoc .= '<tr>
                    <td align="center" width="5%" bgcolor="#ffffff"><input type="checkbox" name="DDocumento['.$Dcont.']" value="'.$Dcont.'" ></td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload_Insc']['tipoDocumentoDesc'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload_Insc']['nome'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">';

                //if($nome_usuario){
                //    $htmlDoc .= $nome_usuario;
                //}else{
                //    $htmlDoc .= '-';
               // }

                $htmlDoc .=  $_SESSION['CPF_CNPJ'].'</td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload_Insc']['dataHora'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        EM ANÁLISE
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload_Insc']['observacao'][$Dcont].'
                    </td>
                    </tr>';

            }
        }

        $htmlDoc .= '</table>';
        $htmlBotaoRetirarDoc = '<div class="row-fluid">
                                 <div class="text-center">
                                    <input class="btn" type="button" value="Retirar Documento" onclick="javascript:enviarForm(\'RetirarDocumento\');">
                                </div>
                            </div>';
    }
    $tpl->htmlBotaoRetirarDoc   = $htmlBotaoRetirarDoc;
    $tpl->htmlDoc               = $htmlDoc;
    $tpl->htmlTipoDoc           = $htmlTipoDoc;
    $tpl->EmailPopup            = $_SESSION["EmailPopup"];

   // $tpl->block("bloco_dados");
}

function configurarBlocoAutorizaoEspecifica($tpl) {
    if ($_SESSION['TipoHabilitacao'] == "L" || $_SESSION['CERTID_ESTRANG']) {
        if (count($_SESSION['AutorizacaoNome']) != 0) {
            for ($i = 0; $i < count($_SESSION['AutorizacaoNome']); $i ++) {
                $tpl->NAME_TEC            = "CheckAutorizacao[$i]";
                $tpl->VALOR_TEC           = $i;
                $tpl->AUTORIZACAONOME     = $_SESSION['AutorizacaoNome'][$i];
                $tpl->AUTORIZACAOREGISTRO = $_SESSION['AutorizacaoRegistro'][$i];
                $tpl->AUTORIZACAODATA     = $_SESSION['AutorizacaoData'][$i];
                $tpl->block("bloco_tabela_tec");
                $tpl->AutorizaNome        = $_SESSION['AutorizaNome'][$i];
                $tpl->AutorizaRegistro    = $_SESSION['AutorizaRegistro'][$i];
                $tpl->AutorizaData        = $_SESSION['AutorizaData'][$i];
            }
        }
        $Url = "CadIncluirGrupos.php?ProgramaOrigem=CadInscritoIncluir";

        if (! in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        $tpl->URLGRUPOS = $Url;
        $tpl->URL = "CadIncluirAutorizacao.php?ProgramaOrigem=CadInscritoIncluir";
    }
    if (count($_SESSION['AutorizacaoNome']) > 0) {
        $tpl->block("bloco_autorizacao_especifica");
    }
}

function configurarBlocoGrupoFornecimento($tpl) {
    $ErroPrograma ="CadInscritoIncluir";
    // var_dump($_SESSION['Materiais']);die;
    if ($_SESSION['TipoHabilitacao'] == "L" || $_SESSION['CERTID_ESTRANG']) {
        $totalMaterial = 0;
        
        # Obtém os materiais da sessão para exibir no bloco de materiais em GRUPOS DE FORNECIMENTO 
        if (count($_SESSION['Materiais']) != 0) {
            $DescricaoGrupoAntes = "";

            for ($i = 0; $i < count($_SESSION['Materiais']); $i ++) {
                $GrupoMaterial = explode("#", $_SESSION['Materiais'][$i]);

                $db = Conexao();

                $sql = "SELECT  A.CGRUMSCODI, A.EGRUMSDESC
                        FROM    SFPC.TBGRUPOMATERIALSERVICO A
                        WHERE   A.CGRUMSCODI = " . $GrupoMaterial[1] . "
                        ORDER BY 2 ";

                $res = executarSQL($db, $sql);

                if (PEAR::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    while ($Linha = $res->fetchRow()) {
                        $totalMaterial ++;
                        $DescricaoGrupo = substr($Linha[1], 0, 75);
                        // var_dump($_SESSION['Materiais']);die;
                        if ($DescricaoGrupo != $DescricaoGrupoAntes) {
                            $tpl->CHECKMATERIAL  = "CheckMateriais[$i]";
                            $tpl->MATERIAIS      = $_SESSION['Materiais'][$i];
                            $tpl->DESCRICAOGRUPO = $DescricaoGrupo;
                        }
                        $tpl->block("bloco_tabela_materias");
                    }
                }
                $DescricaoGrupoAntes = $DescricaoGrupo;
            }
            $db->disconnect();

            if ($totalMaterial > 0) {
                $tpl->block("BLOCO_GRUPO_FORNECIMENTO_MATERIAL");
            }
        }
        $totalServico = 0;

        # Obtém os serviços da sessão para exibir no bloco de serviços em GRUPOS DE FORNECIMENTO #
        if (count($_SESSION['Servicos']) != 0) {
            $DescricaoGrupoAntes = "";

            for ($i = 0; $i < count($_SESSION['Servicos']); $i ++) {
                $ClasseServico = explode("#", $_SESSION['Servicos'][$i]);

                $db = Conexao();

                $sql = "SELECT  A.CGRUMSCODI, A.EGRUMSDESC
                        FROM    SFPC.TBGRUPOMATERIALSERVICO A
                        WHERE   A.CGRUMSCODI = " . $ClasseServico[1] . "
                        ORDER BY 2 ";

                $res = executarSQL($db, $sql);

                if (PEAR::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    while ($Linha = $res->fetchRow()) {
                        $totalServico ++;
                        $DescricaoGrupo = substr($Linha[1], 0, 75);

                        if ($DescricaoGrupo != $DescricaoGrupoAntes) {
                            $tpl->CHECKSERVICO = "CheckServicos[$i]";
                            $tpl->SERVICOS = $_SESSION['Servicos'][$i];
                            $tpl->DESCRICAOGRUPO = $DescricaoGrupo;
                        }
                    }
                    $tpl->block("bloco_tabela_servicos");
                }
                $DescricaoGrupoAntes = $DescricaoGrupo;
            }
            $db->disconnect();

            if ($totalServico > 0) {
                $tpl->block("BLOCO_GRUPO_FORNECIMENTO_SERVICO");
            }
        }
    }
}

function configurarBlocoRegularidade($tpl) {
    $ErroPrograma ="CadInscritoIncluir";

    $InscMercantil = $_SESSION['InscMercantil'];
    $InscOMunic    = $_SESSION['InscOMunic'];
    $InscEstadual  = $_SESSION['InscEstadual'];
    $razaoSocial   = $_SESSION['RazaoSocial'];
    $tipoCnpjCpf   = $_SESSION['TipoCnpjCpf'];

    if($tipoCnpjCpf == 'ESTRANGEIRO'){
        $tpl->CPFCNPJDESCRICAOCAMPO      = "CERTIDÃO ESTRANGEIRA";
        $tpl->CPFCNPJ                    =  $_SESSION['CERTID_ESTRANG'];
    }else{
        $tpl->CPFCNPJDESCRICAOCAMPO      = $tipoCnpjCpf == "CNPJ" ? "CNPJ" : "CPF";
        $tpl->CPFCNPJ                    = $_SESSION['CPF_CNPJ'];
    } 
    $tpl->DESCRCAMPRAZAOSOC     = $tipoCnpjCpf == "CNPJ" ? "Razão Social\n" : "Nome\n";
    $tpl->OBRIGATORIEDADEINCRI  = $_SESSION['TipoHabilitacao'] != "D" ? "*" : "";
    $tpl->RazaoSocial           = $razaoSocial;
    $tpl->InscMercantil         = $InscMercantil;
    $tpl->InscOMunic            = $InscOMunic;
    $tpl->InscEstadual          = $InscEstadual;
    $tpl->DATAVALIDADECERTIDAO  = "";

    $tpl->block("bloco_reg_cpfcnpj");
    $tpl->block("bloco_reg_nomerazao");

    # Certidões complementares #
    if (count($_SESSION['CertidaoComplementar']) > 0) {
        $totalResultados = 0;

        for ($i = 0; $i < count($_SESSION['CertidaoComplementar']); $i ++) {
            $db = Conexao();

            $sql = "SELECT  CTIPCECODI, ETIPCEDESC
                    FROM    SFPC.TBTIPOCERTIDAO
                    WHERE   CTIPCECODI = " . $_SESSION['CertidaoComplementar'][$i] . "
                    ORDER BY CTIPCECODI ";

            $res = executarSQL($db, $sql);

            $totalResultados = $res->numRows();

            if (PEAR::isError($res)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $op = 0;
                while ($Linha = $res->fetchRow()) {
                    $op ++;
                    $CertidaoOpCodigo = $Linha[0];
                    $Descricao = substr($Linha[1], 0, 75);

                    $tpl->ORDEM_CERTIDAO                      = $i;
                    $tpl->CODIGO_CERTIDAO                     = $CertidaoOpCodigo;
                    $tpl->DESCRICAO_CERTIDAO_COMPLEMENTAR     = $Descricao;
                    $tpl->DATA_VALIDADE_CERTIDAO_COMPLEMENTAR = $_SESSION['DataCertidaoComp'][$op - 1];
                    $tpl->block("BLOCO_CERTIDOES_COMPLEMENTARES");
                }
            }
            $db->disconnect();
        }

        if ($totalResultados > 0) {
            $tpl->block("BLOCO_HEADER_COMPLEMENTARES");
        }
    }
    consultar($tpl);
}

if ($Destino != $Origem) {
    if ($Destino == "" && $Origem != "") {
        verificaMudancaAba($Origem, $tpl);
        ExibeAbas($Origem, $tpl);

        if ($_SESSION['Mens'] != 0) {
            $tpl->exibirMensagemFeedback($_SESSION['Mensagem'], $_SESSION['Tipo']);
        }
    } elseif ($Destino != "") {
        verificaMudancaAba($Origem, $tpl);

        if ($_SESSION['Mens'] != 0) {
            ExibeAbas($Origem, $tpl);
            $tpl->exibirMensagemFeedback($_SESSION['Mensagem'], $_SESSION['Tipo']);
        } else {
            ExibeAbas($Destino, $tpl);
        }
    }
} elseif ($Destino == null && $Origem == null && $_SERVER['REQUEST_METHOD'] != "POST") {
    ExibeAbaHabilitacao($tpl);
}
$tpl->show();