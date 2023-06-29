<?php
/**
 * Portal de Compras
 * 
 * Programa: CadMaterialAlterar.php
 * Autor:    Roberta Costa
 * Data:     25/04/2005
 * Objetivo: Programa de Alteração de Material das Classes de Fornecimento
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     26/12/2006
 * Objetivo: Retirada de quebra de linha da descrição do material
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rossana Lira
 * Data:     01/06/2007
 * Objetivo: Verificação de movimentação de material antes da alteração 
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     18/01/2008
 * Objetivo: Alteração do campo "Descrição Completa" ser obrigatório, além de manter a impossibilidade de alterar a unidade de medida dos materiais
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     22/01/2008
 * Objetivo: Correção da unidade, pois estava faltando um campo hidden com o código da unidade, assim, ao realizar o updadte ocorria um erro
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     08/02/2008
 * Objetivo: Alteração para incluir campo "Situação" para ativar e inativar um determinando material
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     18/02/2008
 * Objetivo: Alteração para não permitir a exclusão do material, apenas a sua inativação. Para preservar integridade com o SOFIN e CADUM
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     04/03/2008
 * Objetivo: Alteração  para permitir que o material seja inativado após a retirada do item da tabela referencial de preços
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     04/03/2008
 * Objetivo: Alteração para não permitir a exibição de grupos, classes, subclasses e materiais inativos ao pesquisar por família
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     05/03/2008
 * Objetivo: Alteração para escrever o Código Reduzido do material que irá substituir o material inativado. Isso ocorre, caso o usuário queira substituir o material ao inativá-lo
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     13/03/2008
 * Objetivo: Alteração para remover o botão o colocar a "Lupa" para pesquisar o material substituto
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     13/03/2008
 * Objetivo: Alteração para aumentar o campo Observação de 100 Caracteres para 150
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     26/03/2008
 * Objetivo: Alteração para permitir alteração do nome do material e a descrição completa, sem inativa-lo
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     16/06/2008
 * Objetivo: Não permitir que um material ativo seja mudado para inativo e substituído por ele mesmo
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     19/09/2008
 * Objetivo: Adicionar opção de excluir (apenas exclui materiais quando não há dependência em outra tabela)
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     14/05/2009
 * Objetivo: Permitindo alteração de ativo para inativo quando material for substituído
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     16/09/2009
 * Objetivo: Alteração para inserir o cadastro de serviços
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     04/02/2011
 * Objetivo: #1546 Red Mine- Campo de descrição de serviços vai de 300 para 500 caracteres
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     17/05/2011
 * Objetivo: Tarefa do Redmine: 2694 - Campo de descrição de serviços vai de 500 para 700 caracteres
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Heraldo Botelho
 * Data:     01/11/2012
 * Objetivo: Tarefa do Redmine: 17354 - Criar o campo que indica que não deve gravar na TRP
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     21/08/2014
 * Objetivo: [CR123139]: REDMINE 20 (P2) Na tecla de excluir não levava a informação quando "Não Gravar na TRP" ou "Genérico" estava marcado e "Voltar" de excluir agora mantém os dados marcados
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     21/07/2015
 * Objetivo: CR73616 - Não está permitindo inativar o item de material 17036 e o 17037 mesmo que não tenha nada gravado na TRP
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     09/07/2018
 * Objetivo: Tarefa Redmine 165579
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile IT - Caio Coutinho
 * Data:     18/12/2018
 * Objetivo: Tarefa Redmine 207930
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/06/2019
 * Objetivo: Tarefa Redmine 218516
 * ---------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/06/2019
 * Objetivo: Tarefa Redmine 217790
 * ---------------------------------------------------------------------------------------------------------------------
 * # Alterado : Osmar Celestino
# Data: 06/04/2022
# Objetivo: CR #261752
#---------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/materiais/CadMaterialManterSelecionar.php');
AddMenuAcesso('/estoques/CadIncluirItem.php');

$ProgramaOrigem = 'CadMaterialAlterar';

/* Pega um novo sequencial de SFPC.TBCORRECAOMATERIAL */
function CorrecaoMax($db) {
    /* Pega último sequencial com o incremento */
    $sql = 'SELECT MAX( ACORMASEQU ) ';
    $sql .= '  FROM SFPC.TBCORRECAOMATERIAL ';
    $sql .= ' WHERE ACORMAANOC = '.date('Y').'';

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        $db->query('ROLLBACK');
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        $Erro = 1;
    } else {
        $CorrecaoSequ = $res->fetchRow();
        $CorrecaoSequ = $CorrecaoSequ[0];
    }

    return $CorrecaoSequ + 1;
}

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Botao               = $_POST['Botao'];
    $TipoMaterial        = $_POST['TipoMaterial'];
    $Grupo               = $_POST['Grupo'];
    $DescGrupo           = $_POST['DescGrupo'];
    $Classe              = $_POST['Classe'];
    $DescClasse          = $_POST['DescClasse'];
    $Subclasse           = $_POST['Subclasse'];
    $DescSubclasse       = $_POST['DescSubclasse'];
    $Unidade             = $_POST['Unidade'];
    $Material            = $_POST['MaterialServico'];
    $TipoGrupo           = $_POST['TipoGrupo'];
    $NCaracteresM        = $_POST['NCaracteresM'];
    $NCaracteresC        = $_POST['NCaracteresC'];
    $NCaracteresO        = $_POST['NCaracteresO'];
    $DescMaterial        = stripslashes(strtoupper2(str_replace("\r\n", '', str_replace("'", '', trim($_POST['DescMaterial'])))));
    $DescMaterialComp    = stripslashes(strtoupper2(str_replace("\r\n", '', str_replace("'", '', trim($_POST['DescMaterialComp'])))));
    $Observacao          = stripslashes(strtoupper2(str_replace("\r\n", '', str_replace("'", '', trim($_POST['Observacao'])))));
    $DescUnidade         = $_POST['DescUnidade'];
    $Situacao            = $_POST['Situacao'];
    $MatSubstituto       = $_POST['MatSubstituto'];
    $CodMatSubst         = $_POST['CodMatSubst'];
    $CodServSubst        = $_POST['CodServSubst'];
    $DescMatSubst        = $_POST['DescMatSubst'];
    $MaterialJaInativado = $_POST['MaterialJaInativado'];
    $InicioPrograma      = $_POST['InicioPrograma'];
    $indGravarTRP        = $_POST['indGravarTRP'];
    $CampoGenerico       = $_POST['CampoGenerico'];
    $ItemSustentavel     = $_POST['ItemSustentavel'];
} else {
    $Grupo     = $_GET['Grupo'];
    $Classe    = $_GET['Classe'];
    $Subclasse = $_GET['Subclasse'];
    $Material  = $_GET['MaterialServico'];
    $TipoGrupo = $_GET['TipoGrupo'];
}

$noCasacteresCampoDescricaoServico          = 700;
$noCasacteresCampoDescricaoMaterial         = 300;
$noCasacteresCampoDescricaoCompletaMaterial = 3000;
$noCasacteresCampoObservacao                = 150;

if (is_null($Material)) {
    header('location: CadMaterialManterSelecionar.php');
    exit();
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

//Variáveis dinâmicas para colocar as informações para material ou serviço.
if ($TipoGrupo == 'M') {
    $DescricaoMaterialServico = 'Material';
} else {
    $DescricaoMaterialServico = 'Serviço';
}

if ($Botao == 'Voltar') {
    echo '<script>window.history.back()</script>';
    header('location: CadMaterialManterSelecionar.php');
    exit;
} elseif ($Botao == 'Alterar') {
    $Mens     = 0;
    $Mensagem = 'Informe: ';

    if ($DescMaterial == '') {
        if ($Mens == 1) {
            $Mensagem .= ', ';
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadMaterialAlterar.DescMaterial.focus();\" class=\"titulo2\">$DescricaoMaterialServico</a>";
    } else {
        if ((strlen($DescMaterial) > $noCasacteresCampoDescricaoMaterial and $TipoGrupo == 'M') or (strlen($DescMaterial) > $noCasacteresCampoDescricaoServico and $TipoGrupo == 'S')) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Mens = 1;
            $Tipo = 2;

            if ($TipoGrupo == 'M') {
                $Mensagem .= "<a href=\"javascript:document.CadMaterialAlterar.DescMaterial.focus();\" class=\"titulo2\">$DescricaoMaterialServico no Máximo com ".$noCasacteresCampoDescricaoMaterial.' Caracteres</a>';
            } else {
                $Mensagem .= "<a href=\"javascript:document.CadMaterialAlterar.DescMaterial.focus();\" class=\"titulo2\">$DescricaoMaterialServico no Máximo com ".$noCasacteresCampoDescricaoServico.' Caracteres</a>';
            }
        }
    }

    if ($TipoGrupo == 'M') { //APENAS PARA MATERIAL
        if ($DescMaterialComp == '') {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= '<a href="javascript:document.CadMaterialAlterar.DescMaterialComp.focus();" class="titulo2">Descrição Completa do Material</a>';
        } else {
            if (strlen($DescMaterialComp) > $noCasacteresCampoDescricaoCompletaMaterial) {
                if ($Mens == 1) {
                    $Mensagem .= ', ';
                }

                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= '<a href="javascript:document.CadMaterialAlterar.DescMaterialComp.focus();" class="titulo2">Descrição Completa do Material no Máximo com '.$noCasacteresCampoDescricaoCompletaMaterial.' Caracteres</a>';
            }
        }
    }

    if (strlen($Observacao) > $noCasacteresCampoObservacao) {
        if ($Mens == 1) {
            $Mensagem .= ', ';
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= '<a href="javascript:document.CadMaterialAlterar.Observacao.focus();" class="titulo2">Observação no Máximo com '.$noCasacteresCampoObservacao.' Caracteres</a>';
    }
       
    # Não permitir que um material ativo seja mudado para inativo e substituído por ele mesmo
    if (($Situacao == 'I') && ($Material == $CodMatSubst) && ($TipoGrupo == 'M')) {
        if ($Mens == 1) {
            $Mensagem .= ', ';
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= 'Material substituto não pode ser igual ao próprio material';
    }
    //var_dump($CodServSubst);exit;
    # Não permitir que um serviço ativo seja mudado para inativo e substituído por ele mesmo
    if (($Situacao == 'I') && ($Material == $CodServSubst) && ($TipoGrupo == 'S')) {
        if ($Mens == 1) {
            $Mensagem .= ', ';
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= 'Serviço substituto não pode ser igual ao próprio serviço';
    }

    if ($Mens == 0 && $TipoGrupo != null && $TipoGrupo != '') {
        $db = Conexao();

        if ($TipoGrupo == 'M') {
            $sql = 'SELECT COUNT(*) FROM SFPC.TBMATERIALPORTAL ';
            $sql .= " WHERE EMATEPDESC = '$DescMaterial' AND CMATEPSEQU <> $Material ";
        } else {
            $sql = 'SELECT COUNT(*) FROM SFPC.TBSERVICOPORTAL ';
            $sql .= " WHERE ESERVPDESC = '$DescMaterial' AND CSERVPSEQU <> $Material ";
        }

        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Qtd = $res->fetchRow();

            if ($Qtd[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.CadMaterialAlterar.DescMaterial.focus();\" class=\"titulo2\">$DescricaoMaterialServico já Cadastrado</a>";
            } else {
                if ($Observacao == '') {
                    $Obs = 'NULL';
                } else {
                    $Obs = "'".$Observacao."'";
                }

                if ($TipoGrupo == 'M') {
                    //Verificar se o material a ser inativado e substituido já foi movimentado, não permitir isso, caso ocorra.
                    $sql  = "SELECT COUNT(*) FROM SFPC.TBCORRECAOMATERIAL ";
                    $sql .= "WHERE  CCORMAMATE = $Material AND ACORMANTAB = 'TBMOVIMENTACAOMATERIAL' ";

                    $res = $db->query($sql);

                    if (PEAR::isError($res)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                        $Linha = $res->fetchRow();
                        $QtdItem = $Linha[0];

                        $sql = "SELECT CMATEPSITU FROM SFPC.TBMATERIALPORTAL WHERE CMATEPSEQU = $Material ";

                        $res = $db->query($sql);

                        if (PEAR::isError($res)) {
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                        } else {
                            $Linha = $res->fetchRow();
                            $SituacaoAtual = $Linha[0];

                            // Validação para não poder alterar um material inativado pela correção por substituição de material (Materiais > Correção > Substituição de Materiais).
                            // Note que materiais substituídos e ativos podem ser alterados
                            if (($QtdItem > 0) && ($SituacaoAtual == 'I')) {
                                $Mens     = 1;
                                $Tipo     = 2;
                                $Mensagem = "Alteração Cancelada!<br>Material foi substituído pelo Material cód. $CodMatSubst e portanto não pode mais ser modificado";
                                $Critica  = 0;
                            } else {
                                if ($Observacao == '') {
                                    $Obs = 'NULL';
                                } else {
                                    $Obs = "'".$Observacao."'";
                                }

                                $sql  = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBPRECOMATERIAL ";
                                $sql .= "WHERE  CMATEPSEQU = $Material ";

                                $result = $db->query($sql);

                                if (PEAR::isError($result)) {
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                } else {
                                    $Linha = $result->fetchRow();
                                    $QtdItem = $Linha[0];

                                    # Atribuindo NULL aos Campos não obrigatórios #
                                    if ($DescMaterialComp == '') {
                                        $DescCompleta = 'NULL';
                                    } else {
                                        $DescCompleta = "'".$DescMaterialComp."'";
                                    }

                                    # Inclui na Tabela de Materiais/Serviços #
                                    if ($indGravarTRP) {
                                        $naoGravarTRP = 'S';
                                    } else {
                                        $naoGravarTRP = 'N';
                                    }

                                    if ($CampoGenerico) {
                                        $cadumgenerico = 'S';
                                    } else {
                                        $cadumgenerico = 'N';
                                    }

                                    if ($ItemSustentavel) {
                                        $itemsustentavel = 'S';
                                    } else {
                                        $itemsustentavel = 'N';
                                    }

                                    $db->query('BEGIN TRANSACTION');
                                    $Usuario = $_SESSION['_cusupocodi_'];

                                    $sql  = "UPDATE SFPC.TBMATERIALPORTAL ";
                                    $sql .= "SET    EMATEPDESC = '$DescMaterial', EMATEPOBSE = $Obs, ";
                                    $sql .= "       CUNIDMCODI = '$Unidade', EMATEPCOMP = $DescCompleta, ";
                                    $sql .= "       CMATEPSITU = '$Situacao', TMATEPULAT = '".date('Y-m-d H:i:s')."' , ";
                                    $sql .= "       FMATEPNTRP = '$naoGravarTRP', ";
                                    $sql .= "       FMATEPGENE = '$cadumgenerico', ";
                                    $sql .= "       FMATEPSUST = '$itemsustentavel', ";
                                    $sql .= "       CUSUPOCODI = $Usuario ";
                                    $sql .= " WHERE CMATEPSEQU = $Material ";

                                    $res = $db->query($sql);

                                    if (PEAR::isError($res)) {
                                        $db->query('ROLLBACK');
                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                    } else {
                                        if ($Situacao !=  $SituacaoAtual) {
                                            if ($Situacao == 'A') {
                                                // Situação de I --> A
                                                //Removendo da tabela de Correção material substituido, pois o mesmo passou a ser utilizado normalmente e sem críticas (Não foi movimentado).
                                                $sql  = "DELETE FROM SFPC.TBCORRECAOMATERIAL ";
                                                $sql .= " WHERE  CCORMAMATE = '$Material'   ";

                                                $res = $db->query($sql);

                                                if (PEAR::isError($res)) {
                                                    $db->query('ROLLBACK');
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                }
                                            }

                                            if ($Situacao == 'I') {
                                                // Situação de A --> I
                                                if ($MatSubstituto == 'S' and $CodMatSubst != null and $DescMatSubst != null) {
                                                    //Inserindo na tabela de Correção para saber o material substituido e quem o substituiu
                                                    $sql = 'INSERT INTO SFPC.TBCORRECAOMATERIAL ';
                                                    $sql .= '     VALUES ('.date('Y').', '.CorrecaoMax($db).', NULL, ';
                                                    $sql .= "             'TBMATERIALPORTAL', NULL, ";
                                                    $sql .= "             NULL, $Material, '$DescMaterial', ";
                                                    $sql .= "             $CodMatSubst, ".$_SESSION['_cgrempcodi_'].', ';
                                                    $sql .= '             '.$_SESSION['_cusupocodi_'].", '".date('Y-m-d H:i:s')."'";
                                                    $sql .= '            )';

                                                    $res = $db->query($sql);

                                                    if (PEAR::isError($res)) {
                                                        $db->query('ROLLBACK');
                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                    }
                                                }
                                            }
                                        }

                                        //Inclusão não alteração
                                        $db->query('COMMIT');
                                        $db->query('END TRANSACTION');
                                        $db->disconnect();

                                        # Redireicona para a tela de Análise #
                                        $Mensagem = urlencode('Material Alterado com Sucesso');
                                        $Url = "CadMaterialManterSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
                                                                      
                                        if (!in_array($Url, $_SESSION['GetUrl'])) {
                                            $_SESSION['GetUrl'][] = $Url;
                                        }
                                        
                                        header('location: '.$Url);
                                        exit;
                                    }
                                    
                                    $db->query('COMMIT');
                                    $db->query('END TRANSACTION');
                                }
                            }
                        }
                    }
                } else {
                    if ($CodServSubst == '' || $Situacao == 'A') {
                        $CodServSubst = 'null';
                    }

                    # Inclui na Tabela de Serviços #
                    $db->query('BEGIN TRANSACTION');

                    $sql  = "UPDATE SFPC.TBSERVICOPORTAL ";
                    $sql .= "SET    ESERVPDESC = '" . $DescMaterial . "', ";
                    $sql .= "       ESERVPOBSE = " . $Obs . ", ";
                    $sql .= "       CSERVPSITU = '" . $Situacao . "', ";
                    $sql .= "       TSERVPULAT = '" . date('Y-m-d H:i:s'). "', ";
                    $sql .= "       CSERVPSEQ1 = " . $CodServSubst . "";
                    $sql .= " WHERE CSERVPSEQU = $Material";

                    $res = $db->query($sql);

                    if (PEAR::isError($res)) {
                        $db->query('ROLLBACK');
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                        //COMMIT - TESTAR....
                        $db->query('COMMIT');
                        $db->query('END TRANSACTION');
                        $db->disconnect();

                        # Redireicona para a tela de Análise #
                        $Mensagem = urlencode('Serviço Alterado com Sucesso');
                        $Url = "CadMaterialManterSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";

                        if (!in_array($Url, $_SESSION['GetUrl'])) {
                            $_SESSION['GetUrl'][] = $Url;
                        }

                        header('location: '.$Url);
                        exit;
                    }
                }
            }
        }

        $db->disconnect();
    }
} elseif ($Botao == 'Inativar') {
    $db = Conexao();

    // Apenas para material
    // Checa se o material foi inativado e corrigido no almoxarifado
    $sql  = 'SELECT COUNT(*) FROM SFPC.TBCORRECAOMATERIAL ';
    $sql .= " WHERE CCORMAMATE = '$Material'";

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $res->fetchRow();
        $MaterialInativado = $Linha[0]; //Caso o material tenha sido substituido a quantidade será maior do que 0.

        if ($MaterialInativado > 0) { //Material já inativado por motivo de substituição
            $MaterialJaInativado = true;
        }
    }

    // Checa se o item está presente em algum ata de RP
    // Verifica qual o campo que deve usar para o verificar o item na ata
    if ($TipoGrupo == 'M') {
        $campo = "CMATEPSEQU";
    } else {
        $campo = "CSERVPSEQU";
    }
    function SelecionaOrgao($campo, $Material, $db){
        $sql  = "SELECT DISTINCT ARPI.CORGLICODI
        FROM SFPC.TBATAREGISTROPRECOINTERNA ARPI LEFT JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON 
        IARPN.CARPNOSEQU = ARPI.CARPNOSEQU WHERE IARPN." . $campo . " = " . $Material." AND now() BETWEEN ARPI.TARPINDINI AND ARPI.TARPINDINI + (ARPI.AARPINPZVG::text || 'month')::interval and
        ARPI.FARPINSITU = 'A' AND IARPN.FITARPSITU = 'A'";
        $res = $db->query($sql);
        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sq2");
        } else {
            if(!empty($res)){
                $resultado = $res->fetchRow();
                return $resultado[0];
            }else{
                return False;
            }
        }

    }
     function consultarDCentroDeCustoUsuario($corglicodi)
    {   
        $db = Conexao();

        $sql = "
            SELECT distinct
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
             WHERE 1=1 ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }

        $sql = sprintf($sql, $corglicodi);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        return $itens;
    }
    function consultaNumeroAta($campo, $Material, $db){
        $sql  = "SELECT DISTINCT ARPI.CARPINCODN, ARPI.AARPINANON
        FROM SFPC.TBATAREGISTROPRECOINTERNA ARPI LEFT JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON 
        IARPN.CARPNOSEQU = ARPI.CARPNOSEQU WHERE IARPN." . $campo . " = " . $Material." AND now() BETWEEN ARPI.TARPINDINI AND ARPI.TARPINDINI + (ARPI.AARPINPZVG::text || 'month')::interval and
        ARPI.FARPINSITU = 'A' AND IARPN.FITARPSITU = 'A'";
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        return $itens;

    }
    function consultaNumeroAtaExterna($campo, $Material, $db){
        $sql  = "SELECT DISTINCT ARPE.CARPEXCODN, ARPE.AARPEXANON
        FROM SFPC.TBATAREGISTROPRECOEXTERNA ARPE LEFT JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON IARPN.CARPNOSEQU = ARPE.CARPNOSEQU 
        WHERE IARPN." . $campo . " = " . $Material."  AND now() BETWEEN ARPE.TARPEXDINI AND ARPE.TARPEXDINI + (ARPE.AARPEXPZVG::text || 'month')::interval ";
        $sql .= "               AND ARPE.FARPEXSITU = 'A' ";
        $sql .= "               AND IARPN.FITARPSITU = 'A'";
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        return $itens;

    }
    $ataExterna = consultaNumeroAtaExterna($campo, $Material, $db);
    $ataExterna = current($ataExterna);
    $codigoOrgao = SelecionaOrgao($campo, $Material, $db);
    if($codigoOrgao != False){
        $centroDeCusto =  consultarDCentroDeCustoUsuario($codigoOrgao);
        $objeto         = current($centroDeCusto);
        $ata = consultaNumeroAta($campo, $Material, $db);
        $ata = current($ata);
        //var_dump($ata);
        $numerodaAta      = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numerodaAta      .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon .',';
    }
    if(!empty($ataExterna)){
        $numerodaAta .= str_pad($ata->carpexcodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpexanon .',';
    }

    $sq2  = "SELECT (COUNT(INTERNAS.*) + COUNT(EXTERNAS.*)) AS TOTAL ";
    $sq2 .= "FROM   (SELECT COUNT(DISTINCT ARPI.CARPNOSEQU) ";
    $sq2 .= "        FROM   SFPC.TBATAREGISTROPRECOINTERNA ARPI ";
    $sq2 .= "               LEFT JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON IARPN.CARPNOSEQU = ARPI.CARPNOSEQU ";
    $sq2 .= "        WHERE  IARPN." . $campo . " = " . $Material;
    $sq2 .= "               AND now() BETWEEN ARPI.TARPINDINI AND ARPI.TARPINDINI + (ARPI.AARPINPZVG::text || 'month')::interval ";
    $sq2 .= "               AND ARPI.FARPINSITU = 'A' ";
    $sq2 .= "               AND IARPN.FITARPSITU = 'A') AS INTERNAS, ";
    $sq2 .= "       (SELECT COUNT(DISTINCT ARPE.CARPNOSEQU) ";
    $sq2 .= "        FROM   SFPC.TBATAREGISTROPRECOEXTERNA ARPE ";
    $sq2 .= "               LEFT JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON IARPN.CARPNOSEQU = ARPE.CARPNOSEQU ";
    $sq2 .= "        WHERE  IARPN." . $campo . " = " . $Material;
    $sq2 .= "               AND now() BETWEEN ARPE.TARPEXDINI AND ARPE.TARPEXDINI + (ARPE.AARPEXPZVG::text || 'month')::interval ";
    $sq2 .= "               AND ARPE.FARPEXSITU = 'A' ";
    $sq2 .= "               AND IARPN.FITARPSITU = 'A') AS EXTERNAS ";

    $res2 = $db->query($sq2);

    if (PEAR::isError($res2)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sq2");
    } else {
        $Linha2 = $res2->fetchRow();
        $numeroAtas = ' '.$Linha2[0].'';

        if ($numeroAtas > 0 && $Situacao == 'I') {
            $Mens     = 1;
            $Tipo     = 1;
            $Mensagem = 'Este item está associado à '. $numeroAtas . ' ata(s) de registro de preços ativa(s) e vigente(s)  de número:'. $numerodaAta .'.  </br>Entre em contato com a Equipe do Portal de Compras (3355.8790) antes de realizar a alteração';
        }
    }

    $db->disconnect();
} elseif ($Botao == 'ExcluirConfirmar') {
    $db = Conexao();

    if ($TipoGrupo == 'M') { //PARA MATERIAL
        $sql = "SELECT COUNT(*) FROM SFPC.TBARMAZENAMENTOMATERIAL WHERE CMATEPSEQU = '".$Material."' ";

        $res = $db->query($sql);
        $Linha = $res->fetchRow();

        if ($Linha[0] > 0) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = 'Material está sendo usado em um ou mais almoxarifados';
        }

        if ($Mens == 0) {
            $sql = "SELECT ((SELECT COUNT(*) FROM SFPC.TBINVENTARIOMATERIAL WHERE CMATEPSEQU = '".$Material."') + (SELECT COUNT(*) FROM SFPC.TBINVENTARIOREGISTRO WHERE CMATEPSEQU = '".$Material."')) AS COUNTREGISTROSINVENTARIOS ";

            $res = $db->query($sql);

            $Linha = $res->fetchRow();

            if ($Linha[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Material está sendo usado no registro de um ou mais inventários';
            }
        }

        if ($Mens == 0) {
            $sql = "SELECT COUNT(*) FROM SFPC.TBITEMNOTAFISCAL WHERE CMATEPSEQU = '".$Material."' ";

            $res = $db->query($sql);
 
            $Linha = $res->fetchRow();

            if ($Linha[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Material está sendo usado no registro de uma ou mais notas fiscais';
            }
        }

        if ($Mens == 0) {
            $sql = " SELECT COUNT(*) FROM SFPC.TBITEMREQUISICAO WHERE CMATEPSEQU = '".$Material."' ";

            $res = $db->query($sql);

            $Linha = $res->fetchRow();

            if ($Linha[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Material está sendo usado no registro de uma ou mais requisições';
            }
        }

        if ($Mens == 0) {
            $sql = "SELECT COUNT(*) FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CMATEPSEQU = '".$Material."' ";

            $res = $db->query($sql);

            $Linha = $res->fetchRow();

            if ($Linha[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Material está sendo usado no registro de uma ou mais solicitações de compra';
            }
        }

        if ($Mens == 0) {
            $sql = "SELECT COUNT(*) FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CMATEPSEQU = '".$Material."' ";

            $res = $db->query($sql);

            $Linha = $res->fetchRow();

            if ($Linha[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Material está envolvido em uma ou mais movimentações de materiais em almoxarifado';
            }
        }

        if ($Mens == 0) {
            $sql = "SELECT COUNT(*) FROM SFPC.TBPRECOMATERIAL WHERE CMATEPSEQU = '".$Material."' ";

            $res = $db->query($sql);

            $Linha = $res->fetchRow();

            if ($Linha[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Material está contido na tabela de preços';
            }
        }

        if ($Mens == 0) {
            $db->query('BEGIN TRANSACTION');

            $sql = "DELETE FROM SFPC.TBMATERIALPORTAL WHERE CMATEPSEQU = '".$Material."' ";

            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                $db->query('ROLLBACK');
                EmailErroSQL($ProgramaOrigem, __FILE__, __LINE__, 'Deleção de material falhou (ROLLBACK executado)', $sql, $res);
                exit(0);
            }

            $db->query('COMMIT');
            $db->query('END TRANSACTION');
            $db->disconnect();

            $Url = 'CadMaterialManterSelecionar.php?Mens=1&Tipo=1&Mensagem=Material excluído com sucesso';

            header('location: '.$Url);
            exit;
        }
    } else {
        $db->query('BEGIN TRANSACTION');

        $sql = "DELETE FROM SFPC.TBSERVICOPORTAL WHERE CSERVPSEQU = '".$Material."' ";

        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            $db->query('ROLLBACK');
            EmailErroSQL($ProgramaOrigem, __FILE__, __LINE__, 'Deleção de serviço falhou (ROLLBACK executado)', $sql, $res);
            exit(0);
        }

        $db->query('COMMIT');
        $db->query('END TRANSACTION');
        $db->disconnect();

        $Url = 'CadMaterialManterSelecionar.php?Mens=1&Tipo=1&Mensagem=Serviço excluído com sucesso';

        header('location: '.$Url);
        exit;
    }

    $db->disconnect();
} elseif ($Botao == '') {
    # Verificar aqui se é a primeira vez q entra neste programa #
    if ($InicioPrograma == '') {
        unset($_SESSION['item']);
    }

    # Pega os dados do Material/Serviço de acordo com o código #
    $db = Conexao();

    if ($TipoGrupo == 'M') {
        $sql  = 'SELECT GRU.EGRUMSDESC, CLA.ECLAMSDESC, MAT.EMATEPDESC, MAT.EMATEPOBSE, MAT.CMATEPSITU, ';
        $sql .= '       GRU.FGRUMSTIPM, SUB.ESUBCLDESC, MAT.EMATEPCOMP, MAT.CUNIDMCODI, UND.EUNIDMDESC, COR.CCORMAMAT1 , MAT.FMATEPNTRP, MAT.FMATEPGENE, MAT.FMATEPSUST ';
        $sql .= '  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ';
        $sql .= '       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND, SFPC.TBMATERIALPORTAL MAT ';
        $sql .= '       LEFT OUTER JOIN SFPC.TBCORRECAOMATERIAL COR  ';
        $sql .= '       ON COR.CCORMAMATE = MAT.CMATEPSEQU  ';
        $sql .= ' WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ';
        $sql .= '   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU  ';
        $sql .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CMATEPSEQU = $Material ";
    } else {
        $sql  = 'SELECT GRU.EGRUMSDESC, CLA.ECLAMSDESC, SER.ESERVPDESC, SER.ESERVPOBSE, SER.CSERVPSITU, SER.CSERVPSEQ1 ';
        $sql .= '  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ';
        $sql .= '       SFPC.TBSERVICOPORTAL SER ';
        $sql .= ' WHERE CLA.CGRUMSCODI = GRU.CGRUMSCODI AND SER.CGRUMSCODI = GRU.CGRUMSCODI ';
        $sql .= " AND SER.CCLAMSCODI = CLA.CCLAMSCODI AND SER.CSERVPSEQU = $Material ";
    }

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $res->fetchRow();
        $DescGrupo    = $Linha[0];
        $DescClasse   = $Linha[1];
        $NCaracteresM = strlen($DescMaterial);
        $Observacao   = $Linha[3];
        $NCaracteresO = strlen($Observacao);
        $Situacao     = $Linha[4];
        $DescMaterial = $Linha[2];

        if ($TipoGrupo == 'M') {
            $TipoMaterial     = $Linha[5];
            $DescSubclasse    = $Linha[6];
            $DescMaterialComp = $Linha[7];
            $NCaracteresC     = strlen($DescMaterialComp);
            $Unidade          = $Linha[8];
            $DescUnidade      = $Linha[9];
            $CodMatSubst      = $Linha[10];
            $naoGravarTRP     = $Linha[11];
            $cadumgenerico    = $Linha[12];
            $itemsustentavel  = $Linha[13];

            if ($naoGravarTRP == 'S') {
                $indGravarTRP = true;
            } else {            
                $indGravarTRP = false;
            }
        } else {
            $CodServSubst = $Linha[5];
        }
    }

    $db->disconnect();
}

// Obtém o Código do material e a descrição do mesmo.
if (count($_SESSION['item']) > 0) {
    if (count($_SESSION['item']) == 1) {
        $dadosMaterialSubstituto = explode($SimboloConcatenacaoArray, $_SESSION['item'][0]);
        $CodMatSubst = $dadosMaterialSubstituto[1];
        $Situacao = 'I'; //Quando o material vem do CadItem.php, a situacao dele deve ser Inativo.
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = 'Informe: Apenas um único item para ser substituto';
        $MatSubstituto = null; //Deixar o campo não selecionado.
    }

    unset($_SESSION['item']);
}

if ($CodMatSubst != null && $TipoGrupo == 'M') {
    $db = Conexao();

    $sql = "SELECT MAT.EMATEPDESC FROM SFPC.TBMATERIALPORTAL MAT WHERE CMATEPSEQU = $CodMatSubst";

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $res->fetchRow();
        $DescMatSubst = $Linha[0];
        $MatSubstituto = 'S'; //Para setar o checkbox, pois possui material substituto.
    }

    $db->disconnect();
}

if ($CodMatSubst != null && $TipoGrupo == 'S') {

    $CodServSubst = $CodMatSubst;

    $db = Conexao();

    $sql = "SELECT SERV.ESERVPDESC FROM SFPC.TBSERVICOPORTAL SERV WHERE CSERVPSEQU = $CodServSubst";

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $res->fetchRow();
        $descServSubst = $Linha[0];
        $MatSubstituto = 'S'; //Para setar o checkbox, pois possui material substituto.
    }

    $db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
    <!--
    function remeter(){
	    document.CadMaterialAlterar.Grupo.value  = '';
	    document.CadMaterialAlterar.Classe.value = '';
	    document.CadMaterialAlterar.submit();
    }

    function enviar(valor){
	    document.CadMaterialAlterar.Botao.value=valor;
	    document.CadMaterialAlterar.submit();
    }

    function ncaracteresM(valor){
	    document.CadMaterialAlterar.NCaracteresM.value = '' +  document.CadMaterialAlterar.DescMaterial.value.length;
	    
        if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
		    document.CadMaterialAlterar.NCaracteresM.focus();
	    }
    }

    function ncaracteresC(valor){
	    document.CadMaterialAlterar.NCaracteresC.value = '' +  document.CadMaterialAlterar.DescMaterialComp.value.length;

        if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
		    document.CadMaterialAlterar.NCaracteresC.focus();
	    }
    }

    function ncaracteresO(valor){
	    document.CadMaterialAlterar.NCaracteresO.value = '' +  document.CadMaterialAlterar.Observacao.value.length;

        if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
		    document.CadMaterialAlterar.NCaracteresO.focus();
	    }
    }

    function AbreJanelaItem(url,largura,altura){
	    window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
    }

    function aparecerSubstituto(){
        var select = document.getElementById("Situacao");
        var valorOptionSelecionado = select.options[select.selectedIndex].value;
        var d = eval('document.getElementById("divSituacao")');

        if (valorOptionSelecionado == 'I') {
            d.style.visibility = "visible";
            alert(valorOptionSelecionado);
        } else {
            d.style.visibility = "hidden";
            alert(valorOptionSelecionado);
        }
    }

    function noCaracteresTextArea(textAreaField, limit) {
    	var ta = document.getElementById(textAreaField);

	    if (ta.value.length >= limit) {
		    ta.value = ta.value.substring(0, limit-1);
	    }
    }
    <?php MenuAcesso(); ?>
    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadMaterialAlterar.php" method="post" name="CadMaterialAlterar">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
	        <!-- Caminho -->
	        <tr>
		        <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		        <td align="left" class="textonormal" colspan="2">
			        <font class="titulo2">|</font>
			        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Cadastro > Manter
		        </td>
	        </tr>
	        <!-- Fim do Caminho-->
	        <!-- Erro -->
            <?php
            if ($Mens == 1) {
                ?>
	            <tr>
		            <td width="100"></td>
		            <td align="left" colspan="2">
                        <?php
                        if ($Mens == 1) {
                            ExibeMens($Mensagem, $Tipo, 1);
                        }
                        ?>
		            </td>
	            </tr>
	            <?php
            }
            ?>
	        <!-- Fim do Erro -->
	        <!-- Corpo -->
	        <tr>
		        <td width="100"></td>
		        <td class="textonormal">
			        <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				        <tr>
					        <td class="textonormal">
						        <table border="0" cellspacing="0" cellpadding="0" summary="">
							        <tr>
								        <td class="textonormal">
									        <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
										        <tr>
											        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
												        ALTERAÇÃO DE CADASTRO DE MATERIAIS/SERVIÇOS
											        </td>
										        </tr>
										        <tr>
											        <td class="textonormal">
												        <p align="justify">
														    <?php
                                                            if ($Botao == 'Excluir') {
                                                                ?>
															    Para confirmar a exclusão permanente do <?php echo strtolower($DescricaoMaterialServico); ?>, clique novamente em 'Excluir'.
														        <?php
                                                            } else {
                                                                ?>
															    Para atualizar um <?php echo "$DescricaoMaterialServico"; ?> já cadastrado, preencha os dados abaixo e clique no botão "Alterar".
															    Para apagar o <?php echo "$DescricaoMaterialServico"; ?> clique no botão "Excluir".
														        <?php
                                                            }
                                                            ?>
												        </p>
											        </td>
										        </tr>
										        <tr>
											        <td>
												        <table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													        <tr>
														        <td colspan="2">
															        <table class="textonormal" border="0" width="100%" summary="">
                                                                        <?php
                                                                        if ($TipoGrupo == 'M') {
                                                                            ?>
																	        <tr>
																		        <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
																		        <td class="textonormal">
                                                                                    <?php
                                                                                    if ($TipoMaterial == 'C') {
                                                                                        echo 'CONSUMO';
                                                                                    } else {
                                                                                        echo 'PERMANENTE';
                                                                                    }
                                                                                    ?>
																		        </td>
																	        </tr>
																            <?php
                                                                        } ?>
																        <tr>
																	        <td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
																	        <td class="textonormal"><?php echo $DescGrupo; ?></td>
																        </tr>
																        <tr>
																	        <td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
																	        <td class="textonormal"><?php echo $DescClasse; ?></td>
																        </tr>
                                                                        <?php
                                                                        if ($TipoGrupo == 'M') { //APENAS PARA MATERIAL ?>
																	        <tr>
																		        <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
																		        <td class="textonormal"><?php echo $DescSubclasse; ?></td>
																	        </tr>
																	        <tr>
																		        <td class="textonormal" bgcolor="#DCEDF7">Unidade de Medida</td>
	                                  									        <td class="textonormal"><?php echo $DescUnidade; ?></td>
																	        </tr>
																            <?php
                                                                        } // FIM  if($TipoGrupo == 'M') ?>
																        <tr>
																	        <td class="textonormal" bgcolor="#DCEDF7" height="20"><?php echo 'Cod. Reduzido do '.$DescricaoMaterialServico;?></td>
																	        <td class="textonormal"><?php echo $Material;?></td>
																        </tr>
                                                                        <?php
                                                                        if ($TipoGrupo == 'M') { //APENAS PARA MATERIAL ?>
																	        <tr>
																		        <td class="textonormal" bgcolor="#DCEDF7"><?php echo "$DescricaoMaterialServico"; ?>*</td>
																		        <td class="textonormal">
																			    <?php
                                                                                    if ($Botao == 'Excluir') {
                                                                                        echo $DescMaterial;
                                                                                        echo "<input type='hidden' name='NCaracteresM' value = '".$NCaracteresM."' >";
                                                                                        echo "<input type='hidden' name='DescMaterial' value = '".$DescMaterial."' >";
                                                                                    } else {
                                                                                        ?>
																				        <font class="textonormal">máximo de <?=$noCasacteresCampoDescricaoMaterial?> caracteres</font>
																				        <input type="text" name="NCaracteresM" size="3" value="<?php echo $NCaracteresM ?>" OnFocus="javascript:document.CadMaterialAlterar.DescMaterial.focus();" class="textonormal"><br>
																				        <textarea id="DescMaterial" name="DescMaterial" cols="60" rows="5" OnKeyUp="javascript:ncaracteresM(1); noCaracteresTextArea('DescMaterial', <?=$noCasacteresCampoDescricaoMaterial?>);" OnKeyDown="javascript:ncaracteresM(1); noCaracteresTextArea('DescMaterial', <?=$noCasacteresCampoDescricaoMaterial?>);" OnBlur="javascript:ncaracteresM(0)" OnSelect="javascript:ncaracteresM(1)" class="textonormal" ><?php echo $DescMaterial; ?></textarea>
																			            <?php
                                                                                    }
                                                                                ?>
																		        </td>
																	        </tr>
																	        <tr>
																		        <td class="textonormal" bgcolor="#DCEDF7">Descrição Completa*</td>
																		        <td class="textonormal">
																			        <?php
                                                                                    if ($Botao == 'Excluir') {
                                                                                        echo $DescMaterialComp;
                                                                                        echo "<input type='hidden' name='NCaracteresC' value = '".$NCaracteresC."' >";
                                                                                        echo "<input type='hidden' name='DescMaterialComp' value = '".$DescMaterialComp."' >";
                                                                                    } else {
                                                                                        ?>
																				        <font class="textonormal">máximo de <?=$noCasacteresCampoDescricaoCompletaMaterial?> caracteres</font>
																				        <input type="text" name="NCaracteresC" size="4" value="<?php echo $NCaracteresC ?>" OnFocus="javascript:document.CadMaterialAlterar.DescMaterialComp.focus();" class="textonormal"><br>
																				        <textarea id="DescMaterialComp" name="DescMaterialComp" cols="60" rows="8" OnKeyUp="javascript:ncaracteresC(1);noCaracteresTextArea('DescMaterialComp', <?=$noCasacteresCampoDescricaoCompletaMaterial?>);" OnKeyDown="javascript:ncaracteresC(1);noCaracteresTextArea('DescMaterialComp', <?=$noCasacteresCampoDescricaoCompletaMaterial?>);" OnBlur="javascript:ncaracteresC(0);" OnSelect="javascript:ncaracteresC(1)" class="textonormal" ><?php echo $DescMaterialComp; ?></textarea>
																			            <?php
                                                                                    }
                                                                                    ?>
																		        </td>
																	        </tr>
																            <?php
                                                                        } else { //servico
                                                                            ?>
																	        <tr>
																		        <td class="textonormal" bgcolor="#DCEDF7"><?php echo "$DescricaoMaterialServico"; ?>*</td>
																		        <td class="textonormal">
																			        <?php
                                                                                    if ($Botao == 'Excluir') {
                                                                                        echo $DescMaterial;
                                                                                        echo "<input type='hidden' name='NCaracteresM' value = '".$NCaracteresM."' >";
                                                                                        echo "<input type='hidden' name='DescMaterial' value = '".$DescMaterial."' >";
                                                                                    } else {
                                                                                        ?>
																				        <font class="textonormal">máximo de <?=$noCasacteresCampoDescricaoServico?> caracteres</font>
																				        <input type="text" name="NCaracteresM" size="3" value="<?php echo $NCaracteresM ?>" OnFocus="javascript:document.CadMaterialAlterar.DescMaterial.focus();" class="textonormal"><br>
																				        <textarea id="DescMaterial" name="DescMaterial" cols="60" rows="5" OnKeyUp="javascript:ncaracteresM(1); noCaracteresTextArea('DescMaterial', <?=$noCasacteresCampoDescricaoServico?>);" OnKeyDown="javascript:ncaracteresM(1); noCaracteresTextArea('DescMaterial', <?=$noCasacteresCampoDescricaoServico?>);" OnBlur="javascript:ncaracteresM(0)" OnSelect="javascript:ncaracteresM(1)" class="textonormal" ><?php echo $DescMaterial; ?></textarea>
																			            <?php
                                                                                    } ?>
																		        </td>
																	        </tr>
																            <?php
                                                                        } // FIM  if($TipoGrupo == 'M')
                                                                        ?>
																        <tr>
																	        <td class="textonormal" bgcolor="#DCEDF7">Observação</td>
																	        <td class="textonormal">
																		        <?php
                                                                                if ($Botao == 'Excluir') {
                                                                                    echo $Observacao;
                                                                                    echo "<input type='hidden' name='NCaracteresO' value = '".$NCaracteresO."' >";
                                                                                    echo "<input type='hidden' name='Observacao' value = '".$Observacao."' >";
                                                                                } else {
                                                                                    ?>
																		            <font class="textonormal">máximo de <?=$noCasacteresCampoObservacao?> caracteres</font>
																		            <input type="text" name="NCaracteresO" size="3" value="<?php echo $NCaracteresO ?>" OnFocus="javascript:document.CadMaterialAlterar.Observacao.focus();" class="textonormal"><br>
																		            <textarea id="Observacao" name="Observacao" cols="39" rows="3" OnKeyUp="javascript:ncaracteresO(1);noCaracteresTextArea('Observacao', <?=$noCasacteresCampoObservacao?>);" OnKeyDown="javascript:ncaracteresO(1);noCaracteresTextArea('Observacao', <?=$noCasacteresCampoObservacao?>);" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo $Observacao; ?></textarea>
																		            <?php
                                                                                }
                                                                                ?>
																	        </td>
																        </tr>
                                                                        <tr>
                              		                                        <td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
                  	                                                        <td>
																		        <?php
                                                                                if ($Botao == 'Excluir') {
                                                                                    if ($Situacao == 'A') {
                                                                                        echo 'ATIVO';
                                                                                    } elseif ($Situacao == 'I') {
                                                                                        echo 'INATIVO';
                                                                                    }

                                                                                    echo "<input type='hidden' name='Situacao' value = '".$Situacao."' >";
                                                                                } else {
                                                                                    ?>
                  	                                                                <select name="Situacao" id="Situacao" class="textonormal" onChange="javascript:enviar('Inativar');">
                  	        	                                                        <option value="A" <?php if ($Situacao == 'A') { echo 'selected'; } ?>>ATIVO</option>
                                                                                        <option value="I" <?php if ($Situacao == 'I') { echo 'selected'; } ?>>INATIVO</option>
                                                                                    </select>
																		            <?php
                                                                                }
                                                                                ?>
                                                                            </td>
                          		                                        </tr>
                          		                                        <tr>
								                                            <td class="textonormal" bgcolor="#DCEDF7">Não Gravar na TRP</td>
								                                            <td class="textonormal">
                                                                                <input type="checkbox" name="indGravarTRP" value="checked" <?php echo ($indGravarTRP or $naoGravarTRP == 'S') ? 'checked' : ''; ?>/>
								                                            </td>
								                                        </tr>
                                                                        <!-- Campo Generico -->
                                                                        <tr>
                                                                            <td class="textonormal" bgcolor="#DCEDF7">Genérico</td>
                                                                            <td class="textonormal">
                                                                                <input type="checkbox" name="CampoGenerico" value="checked" <?php echo ($CampoGenerico or $cadumgenerico == 'S') ? 'checked' : ''; ?>/>
                                                                            </td>
                                                                        </tr>
                                                                        <!-- Campo Generico -->
                                                                        <!-- Campo Item Sustentável -->
                                                                        <tr>
                                                                            <td class="textonormal" bgcolor="#DCEDF7">Item Sustentável</td>
                                                                            <td class="textonormal">
                                                                                <input type="checkbox" name="ItemSustentavel" value="checked" <?php echo ($ItemSustentavel or $itemsustentavel == 'S') ? 'checked' : ''; ?>/>
                                                                            </td>
                                                                        </tr>
                                                                        <!-- Campo Item Sustentável -->
                                                                        <?php
                                                                        if ($Situacao == 'I') {
                                                                            if ($TipoGrupo == 'M') {
                                                                                ?>
                                                                                <tr>
                                  		                                            <td class="textonormal"  bgcolor="#DCEDF7">Substituição de material</td>
                      	                                                            <td>
                                                                                        <?php
                                                                                        $Url = "../estoques/CadIncluirItem.php?ProgramaOrigem=$ProgramaOrigem&PesqApenas=C";

                                                                                        if (!in_array($Url, $_SESSION['GetUrl'])) {
                                                                                            $_SESSION['GetUrl'][] = $Url;
                                                                                        }
                                                                                        ?>
                      	                                                                <input type="checkbox" name="MatSubstituto" value="S"
                                                                                            <?php if ($MaterialJaInativado) {echo 'disabled'; } ?> <?php if ($MatSubstituto == 'S') { echo 'checked'; } ?>
                                                                                            onclick="javascript: if(document.getElementsByName('MatSubstituto').item(0).checked){AbreJanelaItem('<?php echo $Url; ?>',700,350);}">

                                                                                        <a href="<?php if ($MaterialJaInativado) { echo('#'); } else { echo("javascript:AbreJanelaItem('$Url',700,350);"); } ?>"><img src="../midia/lupa.gif" border="0"></a>
                                                                                    </td>
                              			                                        </tr>
                                                                                <?php
                                                                                if ($CodMatSubst != null) {
                                                                                    ?>
                                                                                    <tr>
                                    		                                            <td class="textonormal"  bgcolor="#DCEDF7">Cód. Red. do Material Substituto</td>
                                                                                        <td class="textonormal"><?php echo $CodMatSubst; ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                    		                                            <td class="textonormal"  bgcolor="#DCEDF7">Descrição do Material Substituto</td>
                                                                                        <td class="textonormal"><?php echo $DescMatSubst; ?></td>
                                			                                        </tr>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                                <?php
                                                                            } else {
                                                                                ?>
                                                                                <tr>
                                                                                    <td class="textonormal" bgcolor="#DCEDF7">Substituição de serviço</td>
                                                                                    <td>
                                                                                        <?php
                                                                                        $Url = "../estoques/CadIncluirItem.php?ProgramaOrigem=$ProgramaOrigem&PesqApenas=S";

                                                                                        if (!in_array($Url, $_SESSION['GetUrl'])) {
                                                                                            $_SESSION['GetUrl'][] = $Url;
                                                                                        }
                                                                                        ?>
                                                                                        <input type="checkbox" name="MatSubstituto" value="S"
                                                                                            <?php if ($MatSubstituto == 'S') { echo 'checked'; } ?>
                                                                                            onclick="javascript: if(document.getElementsByName('MatSubstituto').item(0).checked){AbreJanelaItem('<?php echo $Url; ?>',700,350);}">

                                                                                        <a href="<?php if ($ServicoJaInativado) { echo('#'); } else { echo("javascript:AbreJanelaItem('$Url',700,350);"); } ?>"><img src="../midia/lupa.gif" border="0"></a>
                                                                                    </td>
                                                                                </tr>
                                                                                <?php
                                                                                if ($CodServSubst != null) {
                                                                                    ?>
                                                                                    <tr>
                                    		                                            <td class="textonormal"  bgcolor="#DCEDF7">CADUS substituto</td>
                                                                                        <td class="textonormal"><?php echo $CodServSubst; ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                    		                                            <td class="textonormal"  bgcolor="#DCEDF7">Descrição do serviço substituto</td>
                                                                                        <td class="textonormal"><?php echo $descServSubst; ?></td>
                                			                                        </tr>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                                <?php
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
											        <td colspan="2" align="right">
												        <input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
                                                        <input type="hidden" name="InicioPrograma" value="1">
												        <input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
												        <input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
												        <input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
												        <input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
												        <input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
												        <input type="hidden" name="DescSubclasse" value="<?php echo $DescSubclasse; ?>">
												        <input type="hidden" name="MaterialServico" value="<?php echo $Material; ?>">
												        <input type="hidden" name="TipoGrupo" value="<?php echo $TipoGrupo; ?>">
                                                        <input type="hidden" name="Unidade" value="<?php echo $Unidade; ?>">
                                                        <input type="hidden" name="DescUnidade" value="<?php echo $DescUnidade; ?>">
                                                        <input type="hidden" name="DescMatSubst" value="<?php echo $DescMatSubst; ?>">
                                                        <input type="hidden" name="CodMatSubst" value="<?php echo $CodMatSubst; ?>">
                                                        <input type="hidden" name="CodServSubst" value="<?php echo $CodServSubst; ?>">
                                                        <input type="hidden" name="MaterialJaInativado" value="<?php echo $MaterialJaInativado; ?>">
												        <input type="hidden" name="Botao" value="">

                                                        <?php
                                                        if ($Botao == 'Excluir') {
                                                            echo "<input type='button' value='Excluir' class='botao' onclick=\"javascript:enviar('ExcluirConfirmar');\">";
                                                            echo "<input type='button' value='Voltar' class='botao' onclick=\"javascript:enviar('ExcluirCancelar');\">";
                                                        } else {
                                                            ?>
													        <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
													        <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
													        <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												            <?php
                                                        }
                                                        ?>
											        </td>
										        </tr>
									        </table>
								        </td>
							        </tr>
						        </table>
					        </td>
				        </tr>
			        </table>
		        </td>
	        </tr>
	        <!-- Fim do Corpo -->
        </table>
    </form>
</body>
</html>