<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompFornecedor.php
# Autor:    Roberta Costa
# Data:     09/09/2004
# Objetivo: Programa que Exibe os dados do Fornecedor Cadastrado
#-------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     20/06/2006 - Posição das funções MenuAcesso e enviar
#                        e retirada de uma conexão desnecessária
#-------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     14/05/2007 - Verifica se a data de balanço expirou
#                      - Retirada do $_SESSION['GetUrl'] = array();
#               28/05/2007 - Exibir comissão e data análise documentação
#           29/05/2007 - Exibir novos campos (índice Endividamento e Microempresa ou EPP)
#-------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     09/06/2008 - Novo campo Email 2
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     04/08/2008 - Novo campo: habilitação do fornecedor
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     16/08/2008 - Alterações para mostrar corretamente habilitações de tipo 'compra direta' e 'estoques'
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     05/08/2010 - Verificação de data de balanço anual se está no prazo
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     09/08/2010  - Adicionado opção para incluir sócios
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     30/08/2010 - Data de última alteração de contrato ou estatuto
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     14/10/2010 - ?
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     05/11/2010 - Alterando prazos de balanço anual e certidão negativa
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
# OBJETIVO: Correção
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data:     27/01/2015 - Tarefa Redmine: 249 - Verificar permissões do usuário "INTERNET"
# Objetivo: Fix redirecionamento de programas
#-------------------------------------------------------------------------
# Alterado: Pitang Agile IT
# Data:     15/07/2015
# Objetivo: [CR Redmine 74234] - Acompanhamentos de Fornecedores - Mensagem Errada
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho  
# Data:     25/07/2018 
# Objetivo: Tarefa Redmine 80154
#-------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:     07/11/2018 
# Objetivo: Tarefa Redmine 206429
#-------------------------------------------------------------------------



# Acesso ao arquivo de funções #
require_once 'funcoesFornecedores.php';
require_once( "funcoesDocumento.php");
# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSenha.php');
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSelecionar.php');
AddMenuAcesso('/fornecedores/RelAcompFornecedorPdf.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorExcluido.php');
AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == 'GET') {
    $Sequencial = $_GET['Sequencial'];
    $Irregularidade = $_GET['Irregularidade'];
    $_SESSION['origem'] = $_SESSION['origem'] != null ? $_SESSION['origem'] : $_GET['origem'];//Este é o programa pelo qual chegamos até aqui

    $Retorno     = $_GET['Retorno'];
    $docSituacao 	= $_GET['docSituacao'];
    $docInicio 	= $_GET['docInicio'];
    $docFim 	= $_GET['docFim'];


} else {
    $Botao = $_POST['Botao'];
    $Irregularidade = $_POST['Irregularidade'];
    $Sequencial = $_POST['Sequencial'];
    $codDownload = $_POST['codDownload'];
    $pesqAnoDoc = $_POST['pesqAnoDoc'];
    $_SESSION['origem'] = $_SESSION['origem'] != null ? $_SESSION['origem'] : $_POST['origem'];//Este é o programa pelo qual chegamos até aqui
    $Mensagem = $_POST['Mensagem'];

    $Retorno     = $_POST['Retorno'];
    $docSituacao 	= $_POST['docSituacao'];
    $docInicio 	= $_POST['docInicio'];
    $docFim 	= $_POST['docFim'];
     
}



if ($Botao == 'PesquisaAnoDoc'){
		
    //Espaço para futuras críticas, caso existam.
    $Botao = '';
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona o programa de acordo com o botão voltar #
if ($Botao == 'Voltar') {
    if ($_SESSION['_cperficodi_'] == 0) {
        header('Location: ConsAcompFornecedorSenha.php');
        exit;
    } else {
        if ($_SESSION['AcompFornecedorDesvio'] == 'CadRenovacaoCadastroIncluir') {
            header('Location: ConsAcompFornecedorSelecionar.php?Desvio=CadRenovacaoCadastroIncluir');
            exit;
        } elseif ($_SESSION['AcompFornecedorDesvio'] == 'CadAnaliseCertidaoFornecedor') {
            header('Location: ConsAcompFornecedorSelecionar.php?Desvio=CadAnaliseCertidaoFornecedor');
            exit;
        } elseif ($Retorno == 'ConsDocsFornecedor') {

            
            header('Location: ConsDocsFornecedor.php?Botao=Pesquisar&docSituacao='.$docSituacao.'&docInicio='.$docInicio.'&docFim='.$docFim);
            exit;
        } else {
            //Caso chegamos aqui por algum outro caminho, que não seja pelo pŕoprio acompanhamento
            //Deveremos, tendo a parametro via GET em mãos, voltar para este.
            $origem = $_SESSION['origem'];
            if (!empty($origem)) {
                header('Location: '.$origem.'.php');
                unset($_SESSION['origem']);
                exit;
            }

            header('Location: ConsAcompFornecedorSelecionar.php');
            exit;
        }
    }
} elseif ($Botao == 'Imprimir') {
    $Url = "RelAcompFornecedorPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem)."&anoAnexacao=".$_POST['pesqAnoDoc'];
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header('Location: '.$Url);
    exit;
}elseif ($Botao == 'Download'){
	
	$db = Conexao();
	$sqlDown = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
			   doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
			   doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat			   
		   FROM sfpc.tbfornecedordocumento doc
		   WHERE doc.aforcrsequ = " . $Sequencial . " AND doc.cfdocusequ = " . $codDownload . " AND ffdocusitu = 'A' order by tfdoctulat DESC limit 1";


   $result = $db->query($sqlDown);
   if (PEAR::isError($result)) {
	   ExibeErroBD($ErroPrograma . "\nLinha: " . __LINE__ . "\nSql: $sqlDown");
   } else {

		while ($linha = $result->fetchRow()) {
			$arrNome = explode('.',$linha[5]);
			$extensao = $arrNome[1];

			$mimetype = 'application/octet-stream';

			header( 'Content-type: '.$mimetype ); 
			header( 'Content-Disposition: attachment; filename='.$linha[5] );   
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Pragma: no-cache');

			echo pg_unescape_bytea($linha[6]);

			die();
		}


   }

	
}

$db = Conexao();

// Verificar data da certidão de último balanço
$dt_val_bal = getDataUltimoBalanco($db, $Sequencial);

if ($Botao == '') {
    $Mens = 0;
    $Mensagem = '';
        # Pega os Dados do Fornecedor Cadastrado #
        $sql = "
            SELECT
                AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, AFORCRIDEN,
                NFORCRORGU, NFORCRRAZS, NFORCRFANT, CCEPPOCODI, CCELOCCODI,
                EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA,
                CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL,
                AFORCRCPFC, NFORCRCONT, NFORCRCARG, AFORCRDDDC, AFORCRTELC,
                AFORCRREGJ, DFORCRREGJ, AFORCRINES, AFORCRINME, AFORCRINSM,
                VFORCRCAPS, VFORCRCAPI, VFORCRPATL, VFORCRINLC, VFORCRINLG,
                DFORCRULTB, DFORCRCNFC, NFORCRENTP, AFORCRENTR, AFORCRENTT,
                DFORCRVIGE, DFORCRGERA, FFORCRCUMP, ECOMLIDESC, DFORCRANAL,
                FFORCRMEPP, VFORCRINDI, VFORCRINSO, NFORCRMAI2, FFORCRTIPO,
                DFORCRCONT
            FROM
                SFPC.TBFORNECEDORCREDENCIADO FORN
            LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM
                ON FORN.CCOMLICODI = COM.CCOMLICODI
            WHERE AFORCRSEQU = $Sequencial
        ";

    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $result->fetchRow();
        # Variáveis Formulário A #
        $Sequencial = $Linha[0];
        $PreInscricao = $Linha[1];
        $CNPJ = $Linha[2];
        $CPF = $Linha[3];

        if ($CNPJ != null) {
            $CPF_CNPJ = $CNPJ;
        } elseif ($CPF != null) {
            $CPF_CNPJ = $CPF;
        }
        
        $MicroEmpresa = $Linha[45];
        $Identidade = $Linha[4];
        $OrgaoEmissorUF = $Linha[5];
        $RazaoSocial = $Linha[6];
        $NomeFantasia = $Linha[7];
        if ($Linha[8] != '') {
            $CEP = $Linha[8];
        } else {
            $CEP = $Linha[9];
        }
        $Logradouro = $Linha[10];
        $Numero = $Linha[11];
        $Complemento = $Linha[12];
        $Bairro = $Linha[13];
        $Cidade = $Linha[14];
        $UF = $Linha[15];
        $DDD = $Linha[16];
        $Telefone = $Linha[17];
        $Fax = $Linha[18];
        $Email = $Linha[19];
        $Email2 = $Linha[48];
        if ($Linha[20] != '') {
            $CPFContato = substr($Linha[20], 0, 3).'.'.substr($Linha[20], 3, 3).'.'.substr($Linha[20], 6, 3).'-'.substr($Linha[19], 9, 2);
        }
        $NomeContato = $Linha[21];
        $CargoContato = $Linha[22];
        $DDDContato = $Linha[23];
        $TelefoneContato = $Linha[24];
        $RegistroJunta = $Linha[25];

        $DataRegistro = '';
        if ($Linha[26] != '') {
            $DataRegistro = substr($Linha[26], 8, 2).'/'.substr($Linha[26], 5, 2).'/'.substr($Linha[26], 0, 4);
        }

        # Variáveis Formulário B #
        $InscEstadual = $Linha[27];
        $InscMercantil = $Linha[28];
        $InscOMunic = $Linha[29];

        # Variáveis Formulário C #
        $CapSocial = converte_valor($Linha[30]);
        $CapIntegralizado = converte_valor($Linha[31]);
        $Patrimonio = converte_valor($Linha[32]);
        $IndLiqCorrente = converte_valor($Linha[33]);
        $IndLiqGeral = converte_valor($Linha[34]);
        $IndEndividamento = converte_valor($Linha[46]);
        $IndSolvencia = converte_valor($Linha[47]);
        if ($Linha[35] != '') {
            $DataUltBalanco = substr($Linha[35], 8, 2).'/'.substr($Linha[35], 5, 2).'/'.substr($Linha[35], 0, 4);
            $DataNovaUltBalanco = $Linha[35]; // data sem formatação
        }

        if ($Linha[36] != '') {
            $DataCertidaoNeg = substr($Linha[36], 8, 2).'/'.substr($Linha[36], 5, 2).'/'.substr($Linha[36], 0, 4);
            $DataNovaCertidaoNeg = $Linha[36]; // data sem formatção
        }
        if ($Linha[50] != '') {
            $DataContratoEstatuto = substr($Linha[50], 8, 2).'/'.substr($Linha[50], 5, 2).'/'.substr($Linha[50], 0, 4);
        }

        # Variáveis Formulário D #
        $NomeEntidade = $Linha[37];
        $RegistroEntidade = $Linha[38];
        $TecnicoEntidade = $Linha[39];
        if ($Linha[40] != '') {
            $DataVigencia = substr($Linha[40], 8, 2).'/'.substr($Linha[40], 5, 2).'/'.substr($Linha[40], 0, 4);
        }
        $DataInscricao = substr($Linha[41], 8, 2).'/'.substr($Linha[41], 5, 2).'/'.substr($Linha[41], 0, 4);
        $Cumprimento = $Linha[42];
        $ComissaoResp = $Linha[43];
        $DataAnaliseDoc = '';
        if ($Linha[44] != '') {
            $DataAnaliseDoc = substr($Linha[44], 8, 2).'/'.substr($Linha[44], 5, 2).'/'.substr($Linha[44], 0, 4);
        }
        $TipoHabilitacao = $Linha[49];
    }

    # Pega os Dados da Tabela de Situação #
    $sql = 'SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI, A.TFORSIULAT ';
    $sql   .= '  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ';
    $sql   .= " WHERE A.AFORCRSEQU = $Sequencial ";
    $sql   .= '   AND A.CFORTSCODI = B.CFORTSCODI ';
    $sql   .= ' ORDER BY A.TFORSIULAT DESC';
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        for ($i = 0; $i < 1; ++$i) {
            $Linha = $result->fetchRow();
            if ($Linha[0] != '') {
                $DataSituacao = substr($Linha[0], 8, 2).'/'.substr($Linha[0], 5, 2).'/'.substr($Linha[0], 0, 4);
            } else {
                $DataSituacao = '';
            }
            $Situacao = $Linha[1];
            if ($Situacao == 5) {
                $Url = 'CadGestaoFornecedorExcluido.php?Programa='.urlencode('ConsAcompFornecedor')."&Sequencial=$Sequencial";
                if (!in_array($Url, $_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }
                header('Location: '.$Url);
                exit;
            }
            $Motivo = strtoupper2($Linha[2]);
            if ($Linha[3] != '') {
                $DataSuspensao = substr($Linha[3], 8, 2).'/'.substr($Linha[3], 5, 2).'/'.substr($Linha[3], 0, 4);
            } else {
                $DataSuspensao = '';
            }
        }
    }

    $Cadastrado = 'HABILITADO';

    if ($TipoHabilitacao == 'L') {
        # Verifica a Validação das Certidões do Fornecedor #
        $sql = 'SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ';
        $sql .= '  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ';
        $sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
        $sql .= "   AND B.AFORCRSEQU = $Sequencial";
        $sql .= ' ORDER BY B.DFORCEVALI';

        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Rows = $result->numRows();
            for ($i = 0; $i <= $Rows; ++$i) {
                $DataHoje = date('Y-m-d');
                $Linha = $result->fetchRow();
                if ($i == 0) {
                    if ($Linha[2] < $DataHoje) {
                        $Cadastrado = 'INABILITADO';
                        $InabilitacaoCertidaoObrigatoria = true;
                    } else {
                        $Cadastrado = 'HABILITADO';
                    }
                }
            }
        }

        //Everton  - correção de erro

        $dataHoje = new DateTime();

        # Verifica também se a data de balanço anual está no prazo #
        # só compara se Data Ultimo Ballanço for diferento de nulo

        if (!empty($DataNovaUltBalanco)) {
            if ($DataNovaUltBalanco < prazoUltimoBalanço()->format('Y-m-d')) {
                $Cadastrado = 'INABILITADO';
                $InabilitacaoUltBalanco = true;
            }
        }

        if ($DataNovaCertidaoNeg != '' and $DataNovaCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d')) {
            $Cadastrado = 'INABILITADO';
            $InabilitacaoCertidaoNeg = true;
        }
    } else {
        $Cadastrado = 'HABILITADO';
    }

    # Mostra Tabela de Situação #
    $sql = 'SELECT EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO';
    $sql   .= ' WHERE CFORTSCODI = '.$Situacao.'';
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $situacao = $result->fetchRow();
        $DescSituacao = $situacao[0];
        if ($Situacao == 1) {
            if ($TipoHabilitacao == 'L') {
                $DescSituacao = $DescSituacao.' '.$Cadastrado;
            } else {
                $DescSituacao = $DescSituacao;
            }
        }
    }

    # Verifica se já Existe Data de CHF #
    $sql = 'SELECT DFORCHGERA,DFORCHVALI FROM SFPC.TBFORNECEDORCHF ';
    $sql   .= " WHERE AFORCRSEQU = $Sequencial ";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        if ($Rows != 0) {
            $Linha = $result->fetchRow();
            $DataGeracaoCHF = substr($Linha[0], 8, 2).'/'.substr($Linha[0], 5, 2).'/'.substr($Linha[0], 0, 4);
            $DataValidadeCHF = substr($Linha[1], 8, 2).'/'.substr($Linha[1], 5, 2).'/'.substr($Linha[1], 0, 4);
        } else {
            $DataGeracaoCHF = '-';
            $DataValidadeCHF = '-';
        }
    }

    # Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #
    $sql = 'SELECT CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ';
    $sql   .= '  FROM SFPC.TBFORNCONTABANCARIA ';
    $sql   .= " WHERE AFORCRSEQU = $Sequencial ";
    $sql   .= ' ORDER BY TFORCBULAT';
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        for ($i = 0; $i < $Rows; ++$i) {
            $Linha = $result->fetchRow();
            if ($i == 0) {
                $Banco1 = $Linha[0];
                $Agencia1 = $Linha[1];
                $ContaCorrente1 = $Linha[2];
            } else {
                $Banco2 = $Linha[0];
                $Agencia2 = $Linha[1];
                $ContaCorrente2 = $Linha[2];
            }
        }
    }
    
    # Verifica se o Fornecedor está Regular na Prefeitura #
    if ($Irregularidade == '') {
        if ($CNPJ != '') {
            $TipoDoc = 1;
            $CPF_CNPJ = $CNPJ;
        } elseif ($CPF != '') {
            $TipoDoc = 2;
            $CPF_CNPJ = $CPF;
        }
        
        $NomePrograma = urlencode('ConsAcompFornecedor.php');

        $infoExtra = '&Retorno='.$Retorno.'&docSituacao='.$docSituacao.'&docInicio='.$docInicio.'&docFim='.$docFim;
        $Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial".$infoExtra;
        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        
        // Redireciona($Url);
        // exit;
    }
    
}

# Aviso para Fornecedor de estoque #
if ($TipoHabilitacao == 'E') {
    $Mens = 1;
    $Tipo = 2;
    $Mensagem = 'FORNECEDOR DE MÓDULO DE ESTOQUES DEVE SER ALTERADO PARA TIPO (COMPRA DIRETA) ou (LICITAÇÃO) ';
}
$inabilitarFornecedor = false; //imprimir mensagem de fornecedor INABILITADO?
//and $CNPJ != ""
if ($TipoHabilitacao == 'L') {
    # Mensagem para Fornecedor Inabilitado #

    if ($Situacao != 3) {
        if ($Cadastrado == 'INABILITADO' and $InabilitacaoCertidaoObrigatoria) {
            $Mens = 1;
            $Tipo = 1;
            $inabilitarFornecedor = true;
            $Mensagem = 'Fornecedor inabilitado ';

            if ($Irregularidade == 'S') {
                if ($Cadastrado == 'INABILITADO') {
                    $Mensagem .= 'CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE E COM SITUAÇÃO IRREGULAR NA PREFEITURA';
                } else {
                    $Mensagem .= 'SITUAÇÃO IRREGULAR NA PREFEITURA';
                }
            } elseif ($Irregularidade == 'N' and  $Cadastrado == 'INABILITADO') {
                $Mens = 1;
                $Tipo = 1;
                $Mensagem .= 'Certidão(ões) fora do prazo de validade';
            }
        }

        if (!empty($MicroEmpresa)) {
            if ($Cadastrado == 'INABILITADO' and $InabilitacaoUltBalanco) {
                if ($Mens == 1) {
                    $Mensagem .= '. ';
                }
                $Mens = 1;
                $Tipo = 1;
                if (!$inabilitarFornecedor) {
                    $inabilitarFornecedor = true;
                    $Mensagem = 'Fornecedor inabilitado ';
                }
                $Mensagem .= 'Data de Validade do Balanço expirada';
                /*(data menor que ".prazoUltimoBalanço()->format('d/m/Y').")"*/
            }
        }

        if ($Cadastrado == 'INABILITADO' and $InabilitacaoCertidaoNeg) {
            if ($Mens == 1) {
                $Mensagem .= '. ';
            }
            $Mens = 1;
            $Tipo = 1;
            if (!$inabilitarFornecedor) {
                $inabilitarFornecedor = true;
                $Mensagem = 'Fornecedor inabilitado ';
            }
            $Mensagem .= 'Data de Certidão Negativa expirada';
            /*(data menor que ".prazoCertidaoNegDeFalencia()->format('d/m/Y').")"*/
        }

        if (!empty($MicroEmpresa) and  empty($dt_val_bal)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Mens = 1;

            $Tipo = 1;
            $Mensagem .= 'CHF SIMPLIFICADO SEM DEMONSTRAÇÕES CONTÁBEIS';
        }

        /*
        # Verifica se a data de balanço expirou, baseado no seguinte: se a data atual for maior que 01/05 do ano corrente só aceitar
        # a data de balanço com um ano a menos do ano atual, caso contrário aceitar com 2 anos a menos do ano atual
        if (    (date("Y-m-d") <= date("Y")."04"."30")) {
                $AnoBalanco = date("Y") - 2;
                if  (substr($DataUltBalanco,6,4) < $AnoBalanco) {
                        if( $Mens == 0 ){
                                $Mensagem = "Fornecedor com ";
                        }
                        if( $Mens == 1 ){ $Mensagem .=", "; }
                        $Mens      = 1;
                        $Tipo      = 1;
                        $Virgula   = 1;
                        $Mensagem .= " Ano de validade do balanço menor que $AnoBalanco";
                }
        }   */
    }
}

//DOCUMENTOS RELACIONADOS AO FORNECEDOR
$SequencialPre	= $PreInscricao;


$sql = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
           doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
           doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat,
           (SELECT h.cfdocscodi
           FROM sfpc.tbfornecedordocumentohistorico h
           where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao, 
           (SELECT h.efdochobse
           FROM sfpc.tbfornecedordocumentohistorico h
           where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as observacao, 

           t.efdoctdesc, 

           (SELECT h.cusupocodi
           FROM sfpc.tbfornecedordocumentohistorico h
           where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as usuarioUltimaAlt, 
           (SELECT u.eusuporesp
           FROM sfpc.tbfornecedordocumentohistorico h
           join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
           where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as nomeUsuUltimaAlt, 
           
           (SELECT h.tfdochulat
           FROM sfpc.tbfornecedordocumentohistorico h
           where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as datahoraUltimaAlt, 
           u.eusuporesp,

           (SELECT s.efdocsdesc
           FROM sfpc.tbfornecedordocumentohistorico h
           join sfpc.tbfornecedordocumentosituacao s ON s.cfdocscodi = h.cfdocscodi
           where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao_nome,
                t.ffdoctobri,

           (SELECT u.eusuporesp
            FROM sfpc.tbfornecedordocumentohistorico h
            join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
            where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat asc limit 1) as nomeUsuAnex 
       FROM sfpc.tbfornecedordocumento doc
       join sfpc.tbfornecedordocumentotipo t ON t.cfdoctcodi = doc.cfdoctcodi
       join sfpc.tbusuarioportal u on doc.cusupocodi = u.cusupocodi
       WHERE aforcrsequ = " . $Sequencial;
        if($SequencialPre){
            $sql .= " OR aprefosequ = " . $SequencialPre;
        }
       $sql .= " AND ffdocusitu = 'A' order by  tfdoctulat DESC";

$resultDocumentos = $db->query($sql);

$htmlOptionsAnosAnexacao = '';
$htmlDocumentosAnexados = '';

$arr = array();
$anos = array();
$arrDocs = array();

$resultAno = $resultDocumentos;
while ($linha = $resultAno->fetchRow()) {
    $arrDocs[] = $linha;
    $anos[] = $linha[3];
}

for($j = date('Y'); $j > 2000; $j--){
    if(in_array($j, $anos)){
        $arr[] = $j;
    }
}

foreach ($arr as $value) {
    $ultimoano = $value;
    if( $value == $pesqAnoDoc ){
        $htmlOptionsAnosAnexacao .= '<option value="'.$value.'" selected>'.$value.'</option>';
        
    }else{
        $htmlOptionsAnosAnexacao .= '<option value="'.$value.'">'.$value.'</option>';
    }
}

// formata data de validade da CHF
$arrValidadeCHF= explode("/", $DataValidadeCHF);
$DataValidadeCHFFormatada = $arrValidadeCHF[2]."-".$arrValidadeCHF[1]."-".$arrValidadeCHF[0];
$mostrarAviso = 0;

foreach ($arrDocs as $linha) {

    if(!$pesqAnoDoc){

        if($arr[0]){
            $pesqAnoDoc = $arr[0];
        }else{
            $pesqAnoDoc = date('Y');
        }


    }


    if($pesqAnoDoc == $linha[3]){

        $htmlDocumentosAnexados .='<tr>
        <td class="textonormal">'.$linha[14].'</td>
        <td class="textonormal"><a href="javascript: baixarArquivo('.$linha[0].');">'.$linha[5].'</a></td>
        <td class="textonormal">'.$linha[18].'</td>
        <td class="textonormal">'.formatarDataHora($linha[8]).'</td>
        <td class="textonormal">'.$linha[19].'</td>
        <td class="textonormal">'.$linha[16].'</td>
        <td class="textonormal">'.formatarDataHora($linha[17]).'</td>
        <td class="textonormal">'.$linha[13].'</td>
        </tr>';

    }
    $arrDataAnexacaoSemHoras = explode(" ", $linha[8]);
    //$arrDataAnexacao = explode("-", $arrDataAnexacaoSemHoras[0]);
    //$dataAnexacaoFormatada = $arrDataAnexacao[2]."-".$arrDataAnexacao[1]."-".$arrDataAnexacao[0];                                        
    $dataAnexacaoFormatada = $arrDataAnexacaoSemHoras[0];
    
    if( (strtotime($dataAnexacaoFormatada) < strtotime($DataValidadeCHFFormatada)) && ($linha[20]=='S') ) {
        
        $mostrarAviso++;
        $arrAvisoDocs[] = $linha[14];

    }
     

}

    
if($mostrarAviso>0){

    if ($Mens == 1) {
        $Mensagem .= ', ';
    }
    $Mens = 1;

    $Tipo = 1;
    $Mensagem .= '<b>CHF- Certificado de Habilitação de Firmas expirado, assim é necessário atualizar os documentos abaixo para a renovação do Cadastro:</b><br>';

    foreach ($arrAvisoDocs as $tipoDocNome) {
        $Mensagem .= $tipoDocNome.'<br>';
    }
} 
    



?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<link rel="stylesheet" type="text/css" href="../estilo.css">

<script language="javascript" type="">
<!--
function enviar(valor){
    document.ConsAcompFornecedor.Botao.value = valor;
    document.ConsAcompFornecedor.submit();
}
function baixarArquivo(cod){
				document.ConsAcompFornecedor.codDownload.value = cod;
				enviar('Download');
}
<?php MenuAcesso(); ?>
//-->
</script>

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="ConsAcompFornecedor.php" method="post" name="ConsAcompFornecedor">
    <br /><br /><br /><br /><br />
    <table cellpadding="3" border="0" width="100%" summary="">
        <!-- Caminho -->
        <tr>
            <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Acompanhamento
            </td>
        </tr>
        <!-- Fim do Caminho-->
        <!-- Erro -->
        <tr>
            <td width="100"></td>
            <td align="left" colspan="2">
<?php
if ($Mens != 0) {
    ExibeMens($Mensagem, $Tipo, $Virgula);
}
?>
            </td>
        </tr>
        <!-- Fim do Erro -->
        <!-- Corpo -->
        <tr>
            <td width="100"></td>
            <td class="textonormal">
                <table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
                    <tr>
                        <td class="textonormal">
                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" bgcolor="#FFFFFF" width="100%" summary="">
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                                ACOMPANHAMENTO DE FORNECEDORES
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" >
                                        <p align="justify">Para imprimir os dados cadastrais abaixo clique no botão "Imprimir".<br />               </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                            <tr>
                                                <td>
                                                    <table class="textonormal" border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
                                                        <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Tipo de Fornecedor</td>
                                                            <td class="textonormal">FORNECEDOR</td>
                                                </tr>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Fornecedor</td>
                                                        <td class="textonormal"><?php echo $Sequencial; ?></td>
                                                </tr>
                                                    <?php
                                                    if ($TipoHabilitacao == 'L') {
                                                        ?>
                                                        <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7" height="20">Cumprimento Inc. XXXIII Art. 7º Cons. Fed.</td>
                                                            <td class="textonormal">SIM</td>
                                                        </tr>
                                                    <?php

                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
                                                        <td class="textonormal"><?php echo $DescSituacao; ?></td>
                                                </tr>
                                                    <?php if ($Situacao != 1) {
    ?>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Situação</td>
                                                        <td class="textonormal"><?php echo $DataSituacao;
    ?></td>
                                                </tr>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Motivo</td>
                                                        <td class="textonormal"><?php echo strtoupper2($Motivo);
    ?></td>
                                                </tr>
                                                <?php

}
if ($Situacao == 3) {
    ?>
<tr>
        <td class="textonormal" bgcolor="#DCEDF7" width="45%" height="20">Data de Expiração da Suspensão</td>
        <td class="textonormal"><?php echo $DataSuspensao;
    ?></td>
                                                </tr>
                                                <?php

} ?>
                                                <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Geração de CHF</td>
                                                        <td class="textonormal"><?php echo $DataGeracaoCHF;?></td>
                                                </tr>
                                                <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Validade de CHF</td>
                                                        <td class="textonormal"><?php echo $DataValidadeCHF;?></td>
                                                </tr>

                                                <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Cadastramento</td>
                                                        <td class="textonormal"><?php echo $DataInscricao;?></td>
                                                </tr>
                                                <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Comissão Responsável Análise </td>
                                                        <td class="textonormal"><?php echo $ComissaoResp;?></td>
                                                </tr>
                                                <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Data da Análise </td>
                                                        <td class="textonormal"><?php echo $DataAnaliseDoc;?></td>
                                                </tr>
                                                <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Habilitação do fornecedor</td>
                                                        <td class="textonormal">
                                                        <?php
                                                        if ($TipoHabilitacao == 'D') {
                                                            echo 'COMPRA DIRETA';
                                                        } elseif ($TipoHabilitacao == 'L') {
                                                            echo 'LICITAÇÃO';
                                                        } elseif ($TipoHabilitacao == 'E') {
                                                            echo 'MÓDULO DE ESTOQUES';
                                                        }
                                                        ?>
                                                        </td>
                                                </tr>
                                                </table>
                                        </td>
                                    </tr>
                            <!-- OCORRÊNCIAS -->
                                    <tr>
                                            <td colspan="2" class="textonormal">
                                                <table class="textonormal" border="1" align="left" bordercolor="#75ADE6" cellpadding="3" cellspacing="0" width="100%">
                                    <tr>
                                                        <td align="center" bgcolor="#bfdaf2" colspan="3" class="textoabason">OCORRÊNCIAS</td>
                                                    </tr>
                                                <?php
                                                    $sql = 'SELECT A.CFORTOCODI, A.EFOROCDETA, A.DFOROCDATA, B.EFORTODESC ';
                                                    $sql .= '  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B';
                                                    $sql .= " WHERE A.CFORTOCODI = B.CFORTOCODI AND A.AFORCRSEQU = $Sequencial ORDER BY 3,1";
                                                    $res = $db->query($sql);
                                                if (PEAR::isError($res)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                } else {
                                                    $Rows = $res->numRows();
                                                    if ($Rows == 0) {
                                                        echo "<tr>\n";
                                                        echo "<td align=\"center\" bgcolor=\"#FFFFFF\" colspan=\"3\" class=\"textonormal\">Nenhuma Ocorrência Informada</td></tr>\n";
                                                        echo "</tr>\n";
                                                    } else {
                                                        for ($i = 0; $i < $Rows; ++$i) {
                                                            $Linha = $res->fetchRow();
                                                            $Codigo = $Linha[0];
                                                            $Detalhe = $Linha[1];
                                                            $Data = $Linha[2];
                                                            $Descricao = $Linha[3];
                                                            if ($i == 0) {
                                                                echo "<tr>\n";
                                                                echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" valign=\"top\" width=\"11%\">Data</td>\n";
                                                                echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" valign=\"top\">Tipo de Ocorrência</td>\n";
                                                                echo "  <td class=\"textonormal\" bgcolor=\"#DCEDF7\" valign=\"top\">Detalhamento</td>\n";
                                                                echo "</tr>\n";
                                                            }
                                                            echo "<tr>\n";
                                                            echo '  <td class="textonormal" bgcolor="#FFFFFF" align="center" valign="top">'.substr($Data, 8, 2).'/'.substr($Data, 5, 2).'/'.substr($Data, 0, 4)."</td>\n";
                                                            echo '  <td class="textonormal" bgcolor="#FFFFFF" valign="top">'.strtoupper2($Descricao)."</td>\n";
                                                            echo "  <td class=\"textonormal\" bgcolor=\"#FFFFFF\" valign=\"top\">$Detalhe</td>\n";
                                                            echo "</tr>\n";
                                                        }
                                                    }
                                                }
                                    ?>
                                          </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                    </tr>
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
                            <!-- HABLITAÇÃO JURÍDICA -->
                            <tr>
                            <td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">HABLITAÇÃO JURÍDICA</td>
                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">
<?php
if ($CNPJ != 0) {
    echo "CNPJ\n";
} else {
    echo "CPF\n";
}
?>
                            </td>
                                            <td class="textonormal" height="20">
                                <?php
                                if ($CNPJ != 0) {
                                    $CNPJCPFForm = substr($CNPJ, 0, 2).'.'.substr($CNPJ, 2, 3).'.'.substr($CNPJ, 5, 3).'/'.substr($CNPJ, 8, 4).'-'.substr($CNPJ, 12, 2);
                                    echo $CNPJCPFForm;
                                } else {
                                    $CNPJCPFForm = substr($CPF, 0, 3).'.'.substr($CPF, 3, 3).'.'.substr($CPF, 6, 3).'-'.substr($CPF, 9, 2);
                                    echo $CNPJCPFForm;
                                }
                                                ?>
                            </td>
                            </tr>
                                        <?php if ($CNPJ != 0) {
    ?>

                                </tr>
                                                <td class="textonormal" bgcolor="#DCEDF7"><?php echo getDescPorteEmpresaTitulo();
    ?></td>
                                                <td class="textonormal" height="20"><?php echo getDescPorteEmpresa($MicroEmpresa) ?></td>

                                </tr>

                                        <?php

}
if ($Identidade != '') {
    ?>
                                        <tr>
                                            <?php if ($CNPJ != 0) {
    ?>
                                                <td class="textonormal" bgcolor="#DCEDF7">Identidade Repres.Legal(Empr.Individual)</td>
                                            <?php

} else {
    ?>
                                                <td class="textonormal" bgcolor="#DCEDF7">Identidade</td>
                                            <?php

}
    ?>
                                            <td class="textonormal" height="20"><?php echo $Identidade;
    ?>  </td>
                            </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Órgao Emissor/UF</td>
                                            <td class="textonormal" height="20"><?php echo $OrgaoEmissorUF;
    ?></td>
                            </tr>
                        <?php

}?>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">
<?php
if ($CNPJ != 0) {
    echo "Razão Social\n";
} else {
    echo "Nome\n";
}
?>
                              </td>
                              <td class="textonormal" height="20"><?php echo $RazaoSocial;?></td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Nome Fantasia</td>
                              <td class="textonormal" height="20">
<?php
if ($NomeFantasia != '') {
    echo $NomeFantasia;
} else {
    echo 'NÃO INFORMADO';
}
?>
                              </td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">CEP</td>
                                            <td class="textonormal" height="20"><?php echo $CEP; ?></td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Logradouro</td>
                              <td class="textonormal" height="20"><?php echo $Logradouro; ?></td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Número</td>
                              <td class="textonormal" height="20">
<?php
if ($Numero != '') {
    echo $Numero;
} else {
    echo 'NÃO INFORMADO';
}
?>
                            </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Complemento</td>
                              <td class="textonormal" height="20">
<?php
if ($Complemento != '') {
    echo $Complemento;
} else {
    echo 'NÃO INFORMADO';
}
?>
                              </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Bairro</td>
                              <td class="textonormal" height="20"><?php echo $Bairro; ?></td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Cidade</td>
                              <td class="textonormal" height="20"><?php echo $Cidade; ?></td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">UF</td>
                                <td class="textonormal" height="20"><?php echo $UF; ?></td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">DDD</td>
                              <td class="textonormal" height="20">
<?php
if ($DDD != '') {
    echo $DDD;
} else {
    echo 'NÃO INFORMADO';
}
?>
                              </td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Telefone(s)</td>
                                    <td class="textonormal" height="20">
<?php
if ($Telefone != '') {
    echo $Telefone;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                        </td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">E-mail 1</td>
                          <td class="textonormal" height="20">
<?php
if ($Email != '') {
    echo $Email;
} else {
    echo 'NÃO INFORMADO';
}
?>
                          </td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">E-mail 2</td>
                          <td class="textonormal" height="20">
<?php
if ($Email2 != '') {
    echo $Email2;
} else {
    echo 'NÃO INFORMADO';
}
?>
                          </td>
                            </tr>
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7">Fax</td>
                                <td class="textonormal" height="20">
<?php
if ($Fax != '') {
    echo $Fax;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                          </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Registro Junta Comercial ou Cartório</td>
                                        <td class="textonormal" height="20">
<?php
if ($RegistroJunta != '') {
    echo $RegistroJunta;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                            </td>
                            </tr>
                            <tr>
                              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data Reg. Junta Comercial ou Cartório</td>
                              <td class="textonormal" height="20">
<?php
if ($DataRegistro != '') {
    echo $DataRegistro;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                            </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Nome do Contato</td>
                                          <td class="textonormal" height="20">
<?php
if ($NomeContato != '') {
    echo $NomeContato;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                          </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">CPF do Contato</td>
                                            <td class="textonormal" height="20">
<?php
if ($CPFContato != '') {
    echo $CPFContato;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                            </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Cargo do Contato</td>
                                          <td class="textonormal" height="20">
<?php
if ($CargoContato != '') {
    echo $CargoContato;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                          </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">DDD do Contato</td>
                                          <td class="textonormal" height="20">
<?php
if ($DDDContato != '') {
    echo $DDDContato;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                          </td>
                            </tr>
                                        <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Telefone do Contato</td>
                                          <td class="textonormal" height="20">
<?php
if ($TelefoneContato != '') {
    echo $TelefoneContato;
} else {
    echo 'NÃO INFORMADO';
}
?>
                                        </td>
                            </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                        <!-- SÓCIOS -->
                            <tr>
                            <td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">SÓCIOS</td>
                        </tr>
                        <tr>
                            <td>

                                    <table align="center" border="1" cellpadding="3" cellspacing="0" bgcolor="#DCEDF7" bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
                                    <?php
                                    if ($CNPJ != 0) {
                                        # Pega os Dados dos sócios #
                                                        $sql = '
                                                            SELECT
                                                                asoforcada, nsofornome
                                                            FROM SFPC.TBsociofornecedor
                                                            WHERE aforcrsequ = '.$Sequencial.'
                                                        ';
                                        $res = $db->query($sql);

                                        if (PEAR::isError($res)) {
                                            EmailErroSQL('Erro ao obter sócios de fornecedor', __FILE__, __LINE__, 'Erro ao obter sócios de fornecedor', $sql, $res);
                                        } else {
                                            $Rows = $res->numRows();
                                            if ($Rows == 0) {
                                                ?>
                                <tr>
                                    <td align="center" class="textonormal" bgcolor="#FFFFFF" colspan="2">Nenhum cadastrado</td>
                                </tr>
                                    <?php

                                            } else {
                                                ?>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7" align="center">Nome</td>
                                    <td class="textonormal" bgcolor="#DCEDF7" align="center" width="150px">CPF/CNPJ</td>
                                </tr>
                                    <?php
                                    for ($itr = 0; $itr < $Rows; ++$itr) {
                                        $Linha = $res->fetchRow();
                                        $socioCPF = $Linha[0];
                                        $socioNome = $Linha[1];
                                        ?>
                                <tr>
                                    <td class="textonormal" bgcolor="#FFFFFF"><?php echo $socioNome;
                                        ?></td>
                                    <td class="textonormal" bgcolor="#FFFFFF"><?php echo $socioCPF;
                                        ?></td>
                                </tr>
                                    <?php

                                    }
                                            }
                                        }
                                    }
                                    ?>
                                  </table>

                            </td>
                        </tr>
                                    </table>
                                </td>
                            </tr>


                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                        <!-- REGULARIDADE FISCAL -->
                                <tr>
                                <td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">REGULARIDADE FISCAL</td>
                            </tr>
                                <tr>
                              <td class="textonormal" bgcolor="#DCEDF7">Inscrição Municipal Recife</td>
                              <td class="textonormal" height="20">
<?php
if ($InscMercantil != '') {
    echo $InscMercantil;
} else {
    echo '-';
}
?>
                              </td>
                            </tr>
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7" width="45%">Inscrição Outro Município</td>
                                <td class="textonormal" height="20">
<?php
if ($InscOMunic != '') {
    echo $InscOMunic;
} else {
    echo '-';
}
?>
                                            </td>
                            </tr>
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7">Inscrição Estadual</td>
                                <td class="textonormal" height="20">
<?php
if ($InscEstadual != '') {
    echo $InscEstadual;
} else {
    echo 'NÃO INFORMADO';
}
?>
                              </td>
                            </tr>
                                        <?php
                                        if ($TipoHabilitacao == 'L') {
                                            ?>
                            <tr>
                              <td class="textonormal" colspan="2">
                                                <table border="1" cellpadding="3" cellspacing="0" bgcolor="#DCEDF7" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
                                <tr>
                                        <td bgcolor="#bfdaf2" class="textoabason" colspan="2" align="center">CERTIDÃO FISCAL</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFFFF" class="textoabason" colspan="2" align="center">OBRIGATÓRIAS</td>
                                    </tr>

                                    <?php
                                    # Mostra a lista de certidões obrigatórias com datas vazias #
                                    $sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY 1";
                                            $res = $db->query($sql);
                                            if (PEAR::isError($res)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                            } else {
                                                $Rows = $res->numRows();
                                                ?>
                                                <tr>
                                                    <td bgcolor="#DCEDF7" class="textonormal">Nome da Certidão</td>
                                                    <td bgcolor="#DCEDF7" class="textonormal">Data de Validade</td>
                                                </tr>
                                            <?php
                                            for ($i = 0; $i < $Rows; ++$i) {
                                                $Linha = $res->fetchRow();
                                                $DescricaoOb = substr($Linha[1], 0, 75);
                                                $CertidaoOb = $Linha[0];

                                                    # Verifica se existem certidões obrigatórias cadastradas para o Fornecedor #
                                                    $sqlData = 'SELECT DFORCEVALI FROM SFPC.TBFORNECEDORCERTIDAO ';
                                                $sqlData .= " WHERE AFORCRSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";

                                                $resData = $db->query($sqlData);
                                                if (PEAR::isError($resData)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                } else {
                                                    $LinhaData = $resData->fetchRow();
                                                    if ($LinhaData[0] != 0) {
                                                        $DataCertidaoOb[$ob - 1] = substr($LinhaData[0], 8, 2).'/'.substr($LinhaData[0], 5, 2).'/'.substr($LinhaData[0], 0, 4);
                                                    } else {
                                                        $DataCertidaoOb[$ob - 1] = null;
                                                    }
                                                }
                                                if ($LinhaData[0] < date('Y-m-d')) {
                                                    $Validade = 'titulo1';
                                                } else {
                                                    $Validade = 'textonormal';
                                                }
                                                echo "<tr>\n";

                                                if ($DescricaoOb == 'DATA ÚLTIMO BALANÇO ') {
                                                    $dataUltimoBalanco = $Validade;
                                                }

                                                echo "  <td bgcolor=\"#FFFFFF\" class=\"$Validade\" width=\"*\">$DescricaoOb</td>\n";
                                                echo "  <td bgcolor=\"#FFFFFF\" class=\"textonormal\" width=\"22%\" align=\"center\">\n";
                                                if (is_null($DataCertidaoOb[$ob - 1])) {
                                                    echo 'NÃO INFORMADO';
                                                } else {
                                                    echo $DataCertidaoOb[$ob - 1];
                                                }
                                                echo "  </td>\n";
                                            }
                                                echo "</tr>\n";
                                            }

                                    # Verifica se existem certidões complementares cadastradas para o Fornecedor #
                                    $sql = 'SELECT A.DFORCEVALI, B.CTIPCECODI, B.ETIPCEDESC  ';
                                            $sql .= '  FROM SFPC.TBFORNECEDORCERTIDAO A, SFPC.TBTIPOCERTIDAO B ';
                                            $sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
                                            $sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY B.CTIPCECODI";
                                            $res = $db->query($sql);
                                            if (PEAR::isError($res)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                            } else {
                                                $Rows = $res->numRows();
                                                echo "<tr>\n";
                                                echo "  <td bgcolor=\"#FFFFFF\" class=\"textoabason\" colspan=\"2\" align=\"center\">COMPLEMENTARES</td>\n";
                                                echo "</tr>\n";
                                                if ($Rows != 0) {
                                                    # Mostra as certidões complementares cadastradas #
                                            for ($i = 0; $i < $Rows; ++$i) {
                                                $Linha = $res->fetchRow();
                                                $DescricaoOp = substr($Linha[2], 0, 75);
                                                $CertidaoOpCodigo = $Linha[1];
                                                $CertidaoComplementar[$i] = $Linha[1];
                                                $DataCertidaoOp[$i] = substr($Linha[0], 8, 2).'/'.substr($Linha[0], 5, 2).'/'.substr($Linha[0], 0, 4);
                                                if ($Linha[0] < date('Y-m-d')) {
                                                    $Validade = 'titulo1';
                                                } else {
                                                    $Validade = 'textonormal';
                                                }
                                                if ($i == 0) {
                                                    echo "<tr>\n";
                                                    echo "  <td bgcolor=\"#DCEDF7\" class=\"textonormal\">Nome da Certidão</td>\n";
                                                    echo "  <td bgcolor=\"#DCEDF7\" class=\"textonormal\">Data de Validade</td>\n";
                                                    echo "</tr>\n";
                                                }
                                                echo "      <tr>\n";
                                                echo "          <td bgcolor=\"#FFFFFF\" class=\"$Validade\" width=\"*\">$DescricaoOp</td>\n";
                                                echo '          <td bgcolor="#FFFFFF" class="textonormal" width="22%" align="center">'.$DataCertidaoOp[$i]."</td>\n";
                                                echo "      </tr>\n";
                                            }
                                                } else {
                                                    echo "<tr>\n";
                                                    echo "  <td bgcolor=\"#FFFFFF\" class=\"textonormal\" align=\"center\" colspan=\"6\">NÃO INFORMADO</td>\n";
                                                    echo "</tr>\n";
                                                }
                                            }
                                            ?>
                                        </table>
                                    </td>
                                </tr>
                                        <?php

                                        }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                        <!-- QUALIFICAÇÃO ECONÔMICA E FINANCEIRA -->
                      <tr>
                            <td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center" height="20">QUALIFICAÇÃO ECONÔMICA E FINANCEIRA</td>
                        </tr>
                            <?php if ($CNPJ != 0 and $TipoHabilitacao == 'L') {
    ?>
                                            <tr>
                                  <td class="textonormal" bgcolor="#DCEDF7">Capital Social</td>
                                  <td class="textonormal" height="20"><?php echo $CapSocial;
    ?></td>
                                </tr>
                                            <tr>
                                  <td class="textonormal" bgcolor="#DCEDF7">Capital Integralizado </td>
                                  <td class="textonormal" height="20">
<?php
if ($CapIntegralizado != '') {
    echo $CapIntegralizado;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                  </td>
                                </tr>
                                <tr>
                                  <td class="textonormal" bgcolor="#DCEDF7">Patrimônio Líquido</td>
                                  <td class="textonormal" height="20"><?php echo $Patrimonio;
    ?></td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7" width="45%">Índice de Liquidez Corrente </td>
                                    <td class="textonormal" height="20">
<?php
if ($IndLiqCorrente != '') {
    echo $IndLiqCorrente;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Índice de Liquidez Geral </td>
                                    <td class="textonormal" height="20">
<?php
if ($IndLiqGeral != '') {
    echo $IndLiqGeral;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Índice de Endividamento</td>
                                    <td class="textonormal" height="20">
<?php
if ($IndEndividamento != '') {
    echo $IndEndividamento;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Índice de Solvência</td>
                                    <td class="textonormal" height="20">
<?php
if ($IndSolvencia != '') {
    echo $IndSolvencia;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data validade do balanço</td>
                                    <td class="textonormal" height="20">
<?php
if ($DataUltBalanco != '') {
    echo $DataUltBalanco;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data Certidão Negativa de Falência e Concordata</td>
                                    <td class="textonormal" height="20">
<?php
if ($DataCertidaoNeg != '') {
    echo $DataCertidaoNeg;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data de última alteração de contrato ou estatuto</td>
                                    <td class="textonormal" height="20">
<?php
if ($DataContratoEstatuto != '') {
    echo $DataContratoEstatuto;
} else {
    echo 'NÃO INFORMADO';
}
    ?>
                                                </td>
                                </tr>
<?php

}
?>
                                        <tr>
                                <td colspan="2">
                                    <table align="center" border="1" cellpadding="3" cellspacing="0" bgcolor="#DCEDF7" bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" align="center">Banco</td>
                                        <td class="textonormal" bgcolor="#DCEDF7" align="center">Agência </td>
                                        <td class="textonormal" bgcolor="#DCEDF7" align="center">Conta Corrente</td>
                                    </tr>
                                    <tr>
                                            <?php if ($Banco1 == '' and  $Banco2 == '') {
    ?>
                                            <td class="textonormal" bgcolor="#FFFFFF" align="center" colspan="3"><?php echo 'NÃO INFORMADO';
    ?></td>
                                            <?php

} else {
    if ($Banco1 != '') {
        ?>
<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Banco1;
        ?></td>
                        <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Agencia1;
        ?></td>
                          <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $ContaCorrente1;
        ?></td>
                            <?php

    }
    if ($Banco2 != '') {
        ?>
</tr>
<tr>
<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Banco2;
        ?></td>
<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Agencia2;
        ?></td>
<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $ContaCorrente2;
        ?></td>
        <?php

    }
}
                                                        ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                        <!-- QUALIFICAÇÃO TÉCNICA -->
                                  <tr>
                            <td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center" height="20">QUALIFICAÇÃO TÉCNICA</td>
                        </tr>
                                        <?php
                                        if ($TipoHabilitacao == 'L') {
                                            ?>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Nome da Entidade</td>
                                                <td class="textonormal" height="20">
<?php
if ($NomeEntidade != '') {
    echo "$NomeEntidade";
} else {
    echo 'NÃO INFORMADO';
}
                                            ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7" width="45%">Registro ou Inscrição </td>
                                                <td class="textonormal" height="20">
<?php
if ($RegistroEntidade != '') {
    echo "$RegistroEntidade";
} else {
    echo 'NÃO INFORMADO';
}
                                            ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Data da Vigência</td>
                                                <td class="textonormal" height="20">
<?php
if ($DataVigencia != '') {
    echo "$DataVigencia";
} else {
    echo 'NÃO INFORMADO';
}
                                            ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Registro ou Inscrição do Técnico</td>
                                                <td class="textonormal" height="20">
<?php
if ($TecnicoEntidade != '') {
    echo "$TecnicoEntidade";
} else {
    echo 'NÃO INFORMADO';
}
                                            ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
                                                    <tr>
                                                        <td bgcolor="#bfdaf2" class="textoabason"  colspan="6" align="center" height="20">AUTORIZAÇÃO ESPECÍFICA </td>
                                                    </tr>
                                                    <?php
                                                    # Mostra as autorizações específicas do Inscrito cadatradas #
                                                    $sql = 'SELECT AFORAENUMA, NFORAENOMA, DFORAEVIGE FROM SFPC.TBFORNAUTORIZACAOESPECIFICA ';
                                            $sql .= " WHERE AFORCRSEQU = $Sequencial";
                                            $res = $db->query($sql);
                                            if (PEAR::isError($res)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                            } else {
                                                $Rows = $res->numRows();
                                                if ($Rows != 0) {
                                                    echo "<tr>\n";
                                                    echo "  <td bgcolor=\"#DCEDF7\" class=\"textonormal\" colspan=\"2\">Nome da Entidade Emissora</td>\n";
                                                    echo "  <td bgcolor=\"#DCEDF7\" class=\"textonormal\" colspan=\"2\">Registro ou Inscrição</td>\n";
                                                    echo "  <td bgcolor=\"#DCEDF7\" class=\"textonormal\" colspan=\"2\" align=\"center\">Data de Vigência</td>\n";
                                                    echo "  </td>\n";
                                                    echo "</tr>\n";
                                                    for ($i = 0; $i < $Rows; ++$i) {
                                                        $Linha = $res->fetchRow();
                                                        $RegistroAutor = $Linha[0];
                                                        $NomeAutor = $Linha[1];
                                                        $DataVigAutor = substr($Linha[2], 8, 2).'/'.substr($Linha[2], 5, 2).'/'.substr($Linha[2], 0, 4);
                                                        echo "<tr>\n";
                                                        echo "  <td class=\"textonormal\" bgcolor=\"#FFFFFF\" height=\"20\" colspan=\"2\">\n";
                                                        if ($NomeAutor != '') {
                                                            echo "$NomeAutor";
                                                        } else {
                                                            echo 'NÃO INFORMADO';
                                                        }
                                                        echo "  </td>\n";
                                                        echo "  <td class=\"textonormal\" bgcolor=\"#FFFFFF\" height=\"20\" colspan=\"2\">\n";
                                                        if ($RegistroAutor != '') {
                                                            echo "$RegistroAutor";
                                                        } else {
                                                            echo 'NÃO INFORMADO';
                                                        }
                                                        echo "  </td>\n";
                                                        echo "  <td class=\"textonormal\" bgcolor=\"#FFFFFF\" height=\"20\" align=\"center\">\n";
                                                        if ($DataVigAutor != '') {
                                                            echo "$DataVigAutor";
                                                        } else {
                                                            echo 'NÃO INFORMADO';
                                                        }
                                                        echo "  </td>\n";
                                                        echo "</tr>\n";
                                                    }
                                                } else {
                                                    echo "<tr>\n";
                                                    echo "  <td bgcolor=\"#FFFFFF\" class=\"textonormal\" colspan=\"6\" align=\"center\">NÃO INFORMADO</td>\n";
                                                    echo "</tr>\n";
                                                }
                                            }
                                            ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php

                                        }
                                        ?>
                            <tr>
                            <td colspan="2">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
                                        <tr>
                                        <td bgcolor="#bfdaf2" class="textoabason" colspan="2" align="center" height="20">GRUPOS DE FORNECIMENTO (OBJETO SOCIAL)</td>
                                    </tr>
                                    <?php
                                    # Mostra os grupos de materiais já cadastrados do Fornecedor #
                                    $sql = 'SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ';
                                    $sql .= '  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ';
                                    $sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
                                    $sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
                                    $res = $db->query($sql);
                                    if (PEAR::isError($res)) {
                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                    } else {
                                        $Rows = $res->numRows();
                                        if ($Rows != 0) {
                                            # Mostra os grupos de materiais cadastrados #
                                            echo "<tr>\n";
                                            echo "  <td bgcolor=\"#DDECF9\" class=\"textoabason\" colspan=\"2\" align=\"center\">MATERIAIS</td>\n";
                                            echo "</tr>\n";
                                            $DescricaoGrupoAntes = '';
                                            for ($i = 0; $i < $Rows; ++$i) {
                                                $Linha = $res->fetchRow();
                                                $DescricaoGrupo = substr($Linha[2], 0, 75);

                                                $Materiais[$i] = 'M#'.$Linha[1];
                                                if ($DescricaoGrupoAntes != $DescricaoGrupo) {
                                                    echo "          <tr bgcolor=\"#FFFFFF\">\n";
                                                    echo "              <td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
                                                    echo "          </tr>\n";
                                                }
                                                $DescricaoGrupoAntes = $DescricaoGrupo;
                                            }
                                        }
                                    }

                                    # Mostra os grupos de serviços já cadastrados do Fornecedor #
                                    $sql = 'SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ';
                                    $sql .= '  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ';
                                    $sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
                                    $sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
                                    $res = $db->query($sql);
                                    if (PEAR::isError($res)) {
                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                    } else {
                                        $Rows = $res->numRows();
                                        if ($Rows != 0) {
                                            # Mostra os grupos de serviços cadastrados #
                                            echo "<tr>\n";
                                            echo "  <td bgcolor=\"#DDECF9\" class=\"textoabason\" colspan=\"2\" align=\"center\" height=\"20\">SERVIÇOS</td>\n";
                                            echo "</tr>\n";
                                            $DescricaoGrupoAntes = '';
                                            for ($i = 0; $i < $Rows; ++$i) {
                                                $Linha = $res->fetchRow();
                                                $DescricaoGrupo = substr($Linha[2], 0, 75);
                                                $Servicos[$i] = 'S#'.$Linha[1];
                                                if ($DescricaoGrupo != $DescricaoGrupoAntes) {
                                                    echo "          <tr bgcolor=\"#FFFFFF\">\n";
                                                    echo "              <td class=\"textonormal\" colspan=\"2\" height=\"18\">$DescricaoGrupo</td>\n";
                                                    echo "          </tr>\n";
                                                }
                                                $DescricaoGrupoAntes = $DescricaoGrupo;
                                            }
                                        }
                                    }
                                        ?>
                                    </table>
                            </td>
                            </tr>
                          </table>
                        </td>
                            </tr>
                            <?php      

									if (PEAR::isError($resultDocumentos)) {
										ExibeErroBD($ErroPrograma . "\nLinha: " . __LINE__ . "\nSql: $sql");
									} else {
                                        //var_dump($resultDocumentos);
                                        //die();
										if($resultDocumentos->numRows() > 0){
										?>
										<tr>
											<td>
											<table cellpadding="3" cellspacing="0" border="1" bordercolor="#75ADE6" width="100%" summary="">
												<!-- DOCUMENTOS-->
											<tr>
												<td bgcolor="#75ADE6" class="textoabasoff" bordercolor="#75ADE6" colspan="8" align="center" height="20">DOCUMENTOS</td>
											</tr>
											<tr>
												<td bgcolor="#DCEDF7" class="textonormal" colspan="8" align="left" height="20">
												Ano da anexação: <select name="pesqAnoDoc" id="pesqAnoDoc" class="tamanho_campo textonormal" onChange="javascript:enviar('PesquisaAnoDoc');">
                                                <?php
														echo $htmlOptionsAnosAnexacao;
												?>
													</select>
												</td>
											</tr>
											<tr>
											
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Tipo do documento</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Nome</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Responsável anexação</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Data/Hora Anexação</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Situação</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Responsável última alteração</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Data/Hora última alteração</td>
												<td bgcolor="#DCEDF7" align="center" class="textonormal"> Observação</td>
											</tr> 
                                        <?php

                                        // DADOS DE DOCUMENTOS ANEXADOS
                                        echo $htmlDocumentosAnexados;

										?></table>
											</td>
                                        </tr>

									<?php }

                                    }
                                   // print_r($sql);
                                 //   die();
									?>

                            <tr>
                                <td align="right">
                                    <input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
                                    <input type="hidden" name="Mensagem" value="<?php echo $Mensagem; ?>">
                                    <input type="hidden" name="Irregularidade" value="<?php echo $Irregularidade; ?>">
                                    <input type="hidden" name="origem" value="<?php echo $_SESSION['origem']; ?>">
                                    <input type="hidden" name="codDownload" id="codDownload" value="<?php echo $_SESSION['origem']; ?>">
                                    
                                    <input type="hidden" name="Retorno" id="Retorno" value="<?php echo $Retorno ?>">
                                    <input type="hidden" name="docSituacao" id="docSituacao" value="<?php echo $docSituacao ?>">
                                    <input type="hidden" name="docInicio" id="docInicio" value="<?php echo $docInicio ?>">
                                    <input type="hidden" name="docFim" id="docFim" value="<?php echo $docFim ?>">

                                    <input type="button" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
                                    <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
                                    <input type="hidden" name="Botao" value="">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?php $db->disconnect();?>
