<?php
/**
 * Portal de Compras
 *
 * Programa: CadSolicitacaoCompraIncluirManterExcluir.php
 * Autor:    Ariston Cordeiro
 * Data:     10/08/2011
 * Objetivo: Programa de solicitação de compra de material, comum para as ferramentas de inclusão, manutenção e exclusão
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     12/12/2011
 * Objetivo: Adicionado restrições informadas pela tabela de parâmetros gerais
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Marcos Túlio
 * Data:     28/02/2012
 * Objetivo: Suporte para o IE
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     28/02/2012
 * Objetivo: Suportando nova estrutura do bloqueio
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Batista Brito
 * Data:     15/03/2012
 * Objetivo: Resolvendo problemas na exibição do browser IE
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Batista Brito
 * Data:     04/06/2012
 * Objetivo: Correção na mensagem exibida - demanda Redmine: #11228
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Batista Brito
 * Data:     06/06/2012
 * Objetivo: Correção na mensagem exibida - demanda Redmine: #11258
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Heraldo Botelho
 * Data:     28/06/2012
 * Objetivo: Inserir a tabela de pré-solicitação de empenho no acompanhamento - demanda Redmine: #11570
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Batista Brito
 * Data:     03/08/2012
 * Objetivo: Correção na mensagem exibida - demanda Redmine: #13347
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Batista Brito
 * Data:     23/10/2012
 * Objetivo: Criar campo--> Número - demanda Redmine: #15787
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Heraldo Botelho
 * Data:     29/10/2012
 * Objetivo: Permitir que campo Quantidade no Exercício seja 0.0000
 * ----------------------------------------------------------------------------------------------------------------------------------------------------------- 
 * Alterado: João Batista Brito
 * Data:     01/11/2012
 * Objetivo: Correção de erros - demanda Redmine: #17770
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Batista Brito
 * Data:     21/11/2012
 * Objetivo: Correção de erros - demanda Redmine: #18167
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Heraldo Botelho
 * Data:     27/08/2013
 * Objetivo: Só criticar dotação se Licitação e Registro de Preço = "S"
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     11/06/2014
 * Objetivo: [CR123140]: REDMINE 21 (P3)
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     25/09/2014
 * Objetivo: [CR129149]: REDMINE 92 (Registro de Preço)
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     18/06/2014
 * Objetivo: [CR123149]: REDMINE 21
 *           [CR123142]: REDMINE 22
 *           Verifica se $materiais existe, eliminando mensagem de warning do foreach.
 *           Gravação da descrição detalhada CADUM genérico em maiúsculo.
 *           Melhora da query que pega os dados dos materiais via POST
 *           Não exibe mais a palavra "Array" quando dois ou mais materiais com descrição detalhada não estiverem preenchidos.
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     31/07/2014
 * Objetivo: Adiciona novas regras para alteração ou exclusão de SCC
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     21/08/2014
 * Objetivo: [CR123140]: REDMINE 21 (P3)
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     27/10/2014
 * Objetivo: [CR129149]: REDMINE 92 (Registro de Preço)
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     14/11/2014
 * Objetivo: [CR]: issue 44 - Alterar inclusão/manutenção da SCC par retirar a validação que não permite incluir tens de material e serviço na mesma SCC
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     17/11/2014
 * Objetivo: #45 - Erro em SQL de produção - Realiza validação de preenchimento de dados para tipo de compra SARP em rascunho
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     11/03/2015
 * Objetivo: [CR129149]: REDMINE 92 (Registro de Preço)
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     24/03/2015
 * Objetivo: [Sem CR redmine] Sistema obriga o preenchimento do campo ""Intenção de Registro de Preço" na inclusão da SCC
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     18/05/2015
 * Objetivo: Tarefa Redmine 78563
 * Versão:   v1.16.1-40-g4a5491e
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     03/06/2015
 * Objetivo: Tarefa Redmine 80467
 * Versão:   v1.18.0-17-g9920068
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: Tarefa Redmine 81057
 * Versão:   v1.22.0-8-g375a774
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: Tarefa Redmine 81057
 * Versão:   v1.22.0-8-g375a774
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     04/09/2015
 * Objetivo: Tarefa Redmine 80498
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     29/12/2015
 * Objetivo: Tarefa Redmine 115579
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     10/02/2016
 * Objetivo: Tarefa Redmine 123570
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     04/05/2018
 * Objetivo: Tarefa Redmine 165624
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/09/2018
 * Objetivo: Tarefa Redmine 203480
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 28/08/2018
 * Objetivo: Tarefa Redmine 200463
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     25/10/2018
 * Objetivo: Tarefa Redmine 205467
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     07/11/2018
 * Objetivo: Tarefa Redmine 205440
 * ----------------------------------------------------------------------------------------------------------------------------------------------------------- 
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     28/11/2018
 * Objetivo: Tarefa Redmine 207416
 * ----------------------------------------------------------------------------------------------------------------------------------------------------------- 
 * Alterado: Lucas Baracho
 * Data:     29/11/2018
 * Objetivo: Tarefa Redmine 205466
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     30/11/2018
 * Objetivo: Tarefa Redmine 207575
 * ----------------------------------------------------------------------------------------------------------------------------------------------------------- 
 * Alterado: Lucas Baracho
 * Data:     30/11/2018
 * Objetivo: Tarefa Redmine 207413
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/12/2018
 * Objetivo: Tarefa Redmine 207786
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile Ti - Caio Coutinho
 * Data:     21/12/2018
 * Objetivo: Tarefa Redmine 208655
 * ----------------------------------------------------------------------------------------------------------------------------------------------------------- 
 * Alterado: Pitang Agile Ti - Caio Coutinho
 * Data:     12/02/2019
 * Objetivo: Tarefa Redmine 210579
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile Ti - Caio Coutinho
 * Data:     12/03/2019
 * Objetivo: Tarefa Redmine 210683
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile Ti - Caio Coutinho
 * Data:     12/04/2019
 * Objetivo: Tarefa Redmine 214731
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     23/05/2019
 * Objetivo: Tarefa Redmine 210696
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     01/07/2019
 * Objetivo: Tarefa Redmine 218788
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     31/07/2019
 * Objetivo: Tarefa Redmine 221527
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     01/08/2019
 * Objetivo: Tarefa Redmine 221694
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     30/10/2019
 * Objetivo: Tarefa Redmine 225679
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     09/12/2019
 * Objetivo: Tarefa Redmine 227641
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     20/12/2019
 * Objetivo: Tarefa Redmine 228067
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     09/01/2020
 * Objetivo: Tarefa Redmine #228616
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     20/02/2020
 * Objetivo: Tarefa Redmine #230327
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     14/07/2020
 * Objetivo: Tarefa Redmine #235540
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     27/11/2020
 * Objetivo: Tarefa Redmine #241283
 * -----------------------------------------------------------------------------------------------------------------------------------------------------------
 *Alterado : Osmar Celestino
 *Data: 10/11/2021
 *Objetivo: CR #255563
 *---------------------------------------------------------------------------
 *Alterado : Marcello Albuquerque
 *Data: 22/11/2021
 *Objetivo: CR #250497
 *---------------------------------------------------------------------------
 *Alterado : Osmar Celestino
 *Data: 25/11/2021
 *Objetivo: CR Correção #255563
 *---------------------------------------------------------------------------
 *Alterado : Eliakim Ramos
 *Data: 17/02/2022
 *Objetivo: CR Correção #257188
 *---------------------------------------------------------------------------
 *Alterado : Osmar Celestino
 *Data: 19/14/2022
 *Objetivo: CR Correção #255563
 *---------------------------------------------------------------------------
 *Alterado : Osmar Celestino
 *Data: 28/02/2022
 *Objetivo: Tarefa Redmine  #255162
 *---------------------------------------------------------------------------
 *Alterado : Osmar Celestino
 *Data: 28/02/2022
 *Objetivo: Correção Redmine  #255162
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 14/07/2022
 * Objetivo: CR #231121
 *---------------------------------------------------------------------------
 * Alterado : João Madson
 * Data: 05/09/2022
 * Objetivo: CR #268394
 *---------------------------------------------------------------------------
 * Alterado : João Madson e Lucas Vicente
 * Data: 06/10/2022
 * Objetivo: CR #269737
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 27/03/2023
 * Objetivo: CR #280983 
 *---------------------------------------------------------------------------
 * Alterado : Lucas André
 * Data: 27/03/2023
 * Objetivo: CR #280982 
 *---------------------------------------------------------------------------
 * Alterado : Osmar Celestino
 * Data: 05/04/2023
 * Objetivo: Ajustes na Tela de Dispensa/Inexigibilidade que usa essa tela
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 10/04/2023
 * Objetivo: CR 281565
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 10/04/2023
 * Objetivo: CR 281566
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 13/04/2023
 * Objetivo: CR 281707
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 24/04/2023
 * Objetivo: CR 282154
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 24/04/2023
 * Objetivo: CR 282156
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 24/04/2023
 * Objetivo: CR 282157
 *---------------------------------------------------------------------------
 * Alterado : Osmar Celestino
 * Data: 25/04/2023
 * Objetivo: Cr 282199
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 25/04/2023
 * Objetivo: Cr 282153
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 27/04/2023
 * Objetivo: Cr 282311
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 28/04/2023
 * Objetivo: Cr 282312
 *---------------------------------------------------------------------------
 * Alterado : Osmar Celestino
 * Data: 09/05/2023
 * Objetivo: Cr 282926
 *---------------------------------------------------------------------------
 * Alterado : Osmar Celestino
 * Data: 10/05/2023
 * Objetivo: Cr 283081
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 17/05/2023
 * Objetivo: Cr 283272
 *---------------------------------------------------------------------------
 * Alterado : Osmar Celestino
 * Data: 12/06/2023
 * Objetivo: Cr 284507
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 14/06/2023
 * Objetivo: Cr 284510
 *---------------------------------------------------------------------------
 * Alterado : Lucas Vicente
 * Data: 14/06/2023
 * Objetivo: Cr 284667
 *---------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
require_once 'funcoesCompras.php';
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 0);
error_reporting(E_ALL ^ E_NOTICE);

# Executa o controle de segurança #
session_start();
Seguranca();

$_SESSION['telaAppView'] = !(empty($telaAppView))?$telaAppView:false;

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadIncluirItem.php');
AddMenuAcesso ('/estoques/CadItemDetalhe.php');
AddMenuAcesso ('/estoques/CadIncluirCentroCusto.php');
AddMenuAcesso ('/compras/RotDadosFornecedor.php');
AddMenuAcesso ('/compras/ConsProcessoPesquisar.php');
AddMenuAcesso ('/compras/RelAcompanhamentoSCCPdf.php');
AddMenuAcesso ('/compras/RelTRPConsultar.php');
AddMenuAcesso ('/compras/RelTRPConsultarDireta.php');
AddMenuAcesso ('/compras/InfPreenchimentoBloqueios.php');
AddMenuAcesso ('/registropreco/CadIncluirIntencaoRegistroPreco.php');
AddMenuAcesso ('/registropreco/CadVisualizarIntencaoRegistroPreco.php');

echo '<div style="display:none"> 308 <br>';

echo ini_get('max_input_vars') .'<br>';
echo ini_get('post_max_size');

echo '</div>';

global $programaSelecao, $programa, $acaoPagina;

// Volta para o programa de origem
if (is_null($programaSelecao)) {
    AddMenuAcesso('compras/' . $programaSelecao);
} else {
    if ($programa == 'CadLicitacaoIncluir.php') {} else {
        AddMenuAcesso('compras/' . $programa);
    }
    if ($programa == 'JanelaLicitacaoIncluir.php') {} else {
        AddMenuAcesso('compras/' . $programa);
    }
}

class CR92 {
    public static function retornarItensMateriasAtaSarp() {
        $itens = array();

        if ($_SESSION['materialSarp'] != null) {
            $itens = $_SESSION['materialSarp'];
            unset($_SESSION['materialSarp']);
        }
        return $itens;
    }

    public static function retornarItensServicoAtaSarp() {
        $itens = array();

        if ($_SESSION['servicoSarp'] != null) {
            $itens = $_SESSION['servicoSarp'];
            unset($_SESSION['servicoSarp']);
        }
        return $itens;
    }

    private function sqlQuantidadeItemAtaCarona($ata, $item, $isMaterial) {
        $sql = 'select sum(coei.acoeitqtat) as qtdTotalOrgao from sfpc.tbcaronaorgaoexterno coe';
        ' left outer join sfpc.tbcaronaorgaoexternoitem coei';
        ' on coe.ccaroesequ = coei.ccaroesequ';
        ' left outer join sfpc.tbitemataregistropreconova iarpn';
        ' on iarpn.carpnosequ = coe.carpnosequ';
        ' and iarpn.citarpsequ = coei.citarpsequ';
        ' where coe.carpnosequ =' . $ata;
        if ($isMaterial) {
            $sql = ' and iarpn.cmatepsequ =' . $item;
        } else {
            $sql = ' and iarpn.cservpsequ =' . $item;
        }
        return $sql;
    }

    private function sqlvalidarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial) {
        $sql = 'select sum(itp.apiarpqtat) as qtdTDSoli from sfpc.tbsolicitacaocompra s';
        $sql .= ' left outer join sfpc.tbitemsolicitacaocompra i';
        $sql .= ' on i.csolcosequ = s.csolcosequ';
        $sql .= ' left outer join sfpc.tbparticipanteatarp p';
        $sql .= ' on s.carpnosequ = p.carpnosequ';
        $sql .= ' left outer join sfpc.tbparticipanteitematarp itp';
        $sql .= ' on itp.carpnosequ = s.carpnosequ ';
        $sql .= ' and itp.carpnosequ = p.carpnosequ';
        $sql .= ' where s.carpnosequ =' . $ata;

        if (! empty($orgao)) {
            $sql .= ' and p.corglicodi =' . $orgao;
        }

        if ($isMaterial) {
            $sql .= ' and i.cmatepsequ =' . $item;
        } else {
            $sql .= ' and i.cservpsequ =' . $item;
        }
        return $sql;
    }

    private function sqlQuantidadeMaxAtaParticipante($orgao, $ata, $item, $isMaterial) {
        $sql = 'select sum(iarp.aitarpqtor) as qtdMaxAta from sfpc.tbparticipanteatarp p';
        $sql .= ' left outer join sfpc.tbitemataregistropreconova iarp';
        $sql .= ' on iarp.carpnosequ = p.carpnosequ';
        $sql .= ' where p.carpnosequ =' . $ata;
        $sql .= ' and p.corglicodi =' . $orgao;

        if ($isMaterial) {
            $sql .= ' and iarp.cmatepsequ =' . $item;
        } else {
            $sql .= ' and iarp.cservpsequ =' . $item;
        }
        return $sql;
    }

    /* Validação Retirada para entrega sem registro de preço, favor recolocar quando o ato de entregar */
    public static function validarCondicaoSARPCarona($orgao, $ata, $item, $isMaterial, $quantidadeInformada) {
        // $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlvalidarCondicaoSARPParticpante(null, $ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeSolicitadaSemOrgao, DB_FETCHMODE_OBJECT);
        //
        // $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlQuantidadeItemAtaCarona($ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeSolicitadaCarona, DB_FETCHMODE_OBJECT);
        //
        // if ($quantidadeSolicitadaSemOrgao > $quantidadeInformada) {
        // return false;
        // }
        //
        // if ($quantidadeSolicitadaCarona > (5 * $quantidadeSolicitadaSemOrgao)) {
        // return false;
        // }
        return true;
    }

    /* Validação Retirada para entrega sem registro de preço, favor recolocar quando o ato de entregar */
    public static function validarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial, $quantidadeInformada) {
        // $dao = Conexao();
        //
        // $resultado = executarSQL($dao, self::sqlvalidarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeSolicitada, DB_FETCHMODE_OBJECT);
        //
        // $resultado = executarSQL($dao, self::sqlQuantidadeMaxAtaParticipante($orgao, $ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeTotalItem, DB_FETCHMODE_OBJECT);
        //
        // $qtdSolicitada = $quantidadeSolicitada->qtdTDSoli;
        // $qtdMaxAtaItem = $quantidadeTotalItem->qtdMaxAta;
        //
        // if ($qtdMaxAtaItem < ($qtdSolicitada + $quantidadeInformada)) {
        // return false;
        // }
        return true;
    }

    public static function sqlVerificarCarpnosequ($Solicitacao) {
        $sql  = " SELECT sc.carpnosequ, sc.ctpcomcodi ";
        $sql .= " FROM SFPC.TBsolicitacaocompra sc ";
        $sql .= " WHERE sc.csolcosequ = " . $Solicitacao ;
        $sql .= " AND sc.csitsocodi in (1,5) ";
        $sql .= " AND sc.ctpcomcodi = 5 ";

        return $sql;
    }
}

$Troca = 1; // Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e"
            // Recebendo variáveis via POST

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Botao                       = $_POST['Botao'];
    $InicioPrograma              = $_POST['InicioPrograma'];
    // $Orgao                       = $_POST['Orgao'];
    $CentroCusto                 = $_POST['CentroCusto'];
    // $OrgaoUsuario                = $_POST['OrgaoUsuario'];
    $Observacao                  = strtoupper2(trim($_POST['Observacao']));
    $Objeto                      = strtoupper2(trim($_POST['Objeto']));
    $Justificativa               = strtoupper2(trim($_POST['Justificativa']));
    $sequencialIntencao          = $_POST['sequencialIntencao'];
    $anoIntencao                 = $_POST['anoIntencao'];
    /*
     * # contadores de caracteres agora estão sendo setados em baixo a partir da variável associada
     * $NCaracteresObservacao       = $_POST['NCaracteresObservacao'];
     * $NCaracteresObjeto           = $_POST['NCaracteresObjeto'];
     * $NCaracteresJustificativa    = $_POST['NCaracteresJustificativa'];
     */
    $DataDom                     = $_POST['DataDom'];
    $DataInicioProposta          = $_POST['DataInicioProposta'];
    $DataFimProposta             = $_POST['DataFimProposta'];
    $Lei                         = $_POST['Lei'];
    $Artigo                      = $_POST['Artigo'];
    $Inciso                      = $_POST['Inciso'];
    $Foco                        = $_POST['Foco'];
    $TipoLei                     = $_POST['TipoLei'];
    $RegistroPreco               = $_POST['RegistroPreco'];
    $SarpTipo                    = $_REQUEST['Sarp'];
    $tipoAta                     = $_POST['tipoAta'];
    $TipoReservaOrcamentaria     = $_POST['TipoReservaOrcamentaria']; // se é bloqueio (1) ou dotação (2)
    $DotacaoTodos                = $_POST['DotacaoTodos'];
    $CnpjFornecedor              = $_POST['CnpjFornecedor'];
    $CompromissoValor           = $_POST['campoCompromisso'];
    $DisputaValor           = $_POST['campoDisputa'];
    $PublicaValor           = $_POST['campoPublicar'];
    $GeraContrato                = $_POST['GeraContrato'];
    $TipoCompra                  = $_POST['TipoCompra'];
    $NomeDocumento               = $_POST['NomeDocumento'];
    $DDocumento                  = $_POST['DDocumento'];
    $OrigemBancoPreços           = $_POST['OrigemBancoPreços'];
    $NumProcessoSARP             = $_POST['NumProcessoSARP'];
    $AnoProcessoSARP             = $_POST['AnoProcessoSARP'];
    $ComissaoCodigoSARP          = $_POST['ComissaoCodigoSARP'];
    $OrgaoLicitanteCodigoSARP    = $_POST['OrgaoLicitanteCodigoSARP'];
    $GrupoEmpresaCodigoSARP      = $_POST['GrupoEmpresaCodigoSARP'];
    $CarregaProcessoSARP         = $_POST['CarregaProcessoSARP'];
    $isDotacaoAnterior           = $_POST['isDotacaoAnterior']; // informa se na pagina anterior era dotação ou bloqueio
    $Bloqueios                   = $_POST['Bloqueios'];
    $BloqueiosCheck              = $_POST['BloqueiosCheck'];
    $BloqueioAno                 = $_POST['BloqueioAno'];
    $BloqueioOrgao               = $_POST['BloqueioOrgao'];
    $BloqueioUnidade             = $_POST['BloqueioUnidade'];
    $BloqueioDestinacao          = $_POST['BloqueioDestinacao'];
    $BloqueioSequencial          = $_POST['BloqueioSequencial'];
    $DotacaoAno                  = $_POST['DotacaoAno'];
    $DotacaoOrgao                = $_POST['DotacaoOrgao'];
    $DotacaoUnidade              = $_POST['DotacaoUnidade'];
    $DotacaoFuncao               = $_POST['DotacaoFuncao'];
    $DotacaoSubfuncao            = $_POST['DotacaoSubfuncao'];
    $DotacaoPrograma             = $_POST['DotacaoPrograma'];
    $DotacaoTipoProjetoAtividade = $_POST['DotacaoTipoProjetoAtividade'];
    $DotacaoProjetoAtividade     = $_POST['DotacaoProjetoAtividade'];
    $DotacaoElemento1            = $_POST['DotacaoElemento1'];
    $DotacaoElemento2            = $_POST['DotacaoElemento2'];
    $DotacaoElemento3            = $_POST['DotacaoElemento3'];
    $DotacaoElemento4            = $_POST['DotacaoElemento4'];
    $DotacaoFonte                = $_POST['DotacaoFonte'];
    $MaterialCheck               = $_POST['MaterialCheck'];
    $MaterialCod                 = $_POST['MaterialCod'];
    $MaterialTrp                 = $_POST['MaterialTrp'];
    $MaterialQuantidade          = $_POST['MaterialQuantidade'];
    $MaterialValorEstimado       = $_POST['MaterialValorEstimado'];
    $MaterialQuantidadeExercicio = $_POST['MaterialQuantidadeExercicioValor'];
    $MaterialTotalExercicio      = $_POST['MaterialTotalExercicioValor'];
    $MaterialMarca               = $_POST['MaterialMarca'];
    $MaterialModelo              = $_POST['MaterialModelo'];
    $MaterialFornecedor          = $_POST['MaterialFornecedorValor'];
    $MaterialDescricaoDetalhada  = $_POST['MaterialDescricaoDetalhada'];
    $ServicoCheck                = $_POST['ServicoCheck'];
    $ServicoCod                  = $_POST['ServicoCod'];
    $ServicoQuantidade           = $_POST['ServicoQuantidade'];
    $ServicoDescricaoDetalhada   = $_POST['ServicoDescricaoDetalhada'];
    $ServicoQuantidadeExercicio  = $_POST['ServicoQuantidadeExercicioValor'];
    $ServicoValorEstimado        = $_POST['ServicoValorEstimado'];
    $ServicoTotalExercicio       = $_POST['ServicoTotalExercicioValor'];
    $ServicoFornecedor           = $_POST['ServicoFornecedorValor'];
    $Solicitacao                 = $_POST['SeqSolicitacao']; // sequencial da solicitação usado pelo 'Manter'
    $Numero                      = $_POST['Numero'];
    $DataSolicitacao             = $_POST['DataSolicitacao'];
    $valorBloqueio               = $_POST['ValorBloqueio'];
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    unset($_SESSION['ccSituacao_origem']);

    $Solicitacao = $_GET['SeqSolicitacao'];
    if (is_null($Solicitacao)) {
        unset($_SESSION['Arquivos_Upload']); // inicio de uma inclusão. excluir arquivos na sessão
    }
}


if(isset($Solicitacao)){
    $_SESSION['SeqSolicitacaok'] = $Solicitacao;
}

// Variáveis para teste
$desabilitarChecagemFornecedorSistemaMercantil = false; // correto é false. se true, permite inclusão de fornecedores que não passaram na checagem do cadastro mercantil
$desabilitarChecagemBloqueioSofin              = false; // correto é false. se true, não valida bloqueios no sistema do sofin e valores de bloqueio (só valida bloqueio pelo portal)

$db       = Conexao();
$dbOracle = ConexaoOracle();

$sql = 'SELECT  QPARGETMAOBJETO, QPARGETMAJUSTIFICATIVA, QPARGEDESCSE, EPARGESUBELEMESPEC, QPARGEQMAC, QPARGEQMAC,
                EPARGETDOV
        FROM    SFPC.TBPARAMETROSGERAIS ';

$linha = resultLinhaUnica(executarSQL($db, $sql));

if (is_null($linha)) {
    echo '<br/><br/><br/><br/><br/><br/>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo "<b>Falha do sistema, pois os Parâmetros Gerais precisam ser preenchidos. Vá em em 'Tabelas > Parâmetros Gerais' e preencha os campos.</b>";
}

$tamanhoObjeto           = 400;
$tamanhoJustificativa    = 200;
$tamanhoDescricaoServico = strlen($linha[2]);
$subElementosEspeciais   = explode(',', $linha[3]);
$tamanhoArquivo          = 30;
$tamanhoNomeArquivo      = $linha[5];
$extensoesArquivo        = $linha[6];
$cargaBloqueiosManter    = false; // informa se é primeiro carregamento e existem bloqueios, para serem carregados para o javascript

if ((!empty($Solicitacao) && $acaoPagina == ACAO_PAGINA_MANTER && $_SESSION['_fperficorp_'] != 'S')) {
    $sql   = CR92::sqlVerificarCarpnosequ($Solicitacao);

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    
    //if (empty($linha[0]) && $linha[1] == TIPO_COMPRA_SARP) {
      //  $Botao = 'Voltar';
//        $_SESSION['mensagemSarp'] = "Esta Solicitação de Compra e Contratação de Material e Serviço (SCC) não poderá ser alterada, pois antecede a criação do Módulo de Registro de Preços. Proceda a inclusão de uma nova SCC do tipo SARP com os mesmos dados";
  //  }
}

if ($Botao == 'Voltar') {
    $_SESSION['carregarSelecionarDoSession'] = true;
    
    if ($programa == 'CadLicitacaoIncluir.php') {
        $programaSelecao = $programa;
    } else {
        if (isset($pesquisa) && (($_GET['origemTramitacao']==1) || ($_GET['origemTramitacao']==2))) {
            $programaSelecao = $urlTramitacao;
            unset($_SESSION['origemPesquisa']);
        } else {
            if (is_null($programaSelecao)) {
                $programaSelecao = '../licitacoes/CadLicitacaoIncluir.php';
            }
        }
    }

    header('Location: ' . $programaSelecao);
    exit();
} elseif ($Botao == 'Imprimir') {
    $sql = "SELECT CSITSOCODI FROM SFPC.TBSOLICITACAOCOMPRA WHERE CSOLCOSEQU = " . $Solicitacao;

    $res = $db->query($sql);

    $situacaoImp = $res->fetchRow();

    $situacaoImp = $situacaoImp[0];

    if ($situacaoImp <> 10) {
        $Url = 'RelAcompanhamentoSCCPdf.php?Solicitacao=' . $Solicitacao;
        header('Location: ' . $Url);
        exit();
    } else {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = 'Não é possível gerar PDF de SCC cancelada';
    }
}


$Mensagem = urldecode($_GET['Mensagem']);
$Mens     = $_GET['Mens'];
$Tipo     = $_GET['Tipo'];

// <?php  esse trecho de codigo que estava gerando  o espaço
// if ($Mens == 1) { 
//    echo '<tr>';
//    echo'     <td width="150"></td>';
//    echo'     <td align="left" colspan="2">'.ExibeMens($Mensagem,$Tipo,1).'</td>';
//    echo' </tr>';
//  } 
// recuperando dados da SCC (acompanhamento, manter, excluir)

// echo '<div style="display:none">';
//     var_dump($CentroCusto);
// echo '</div>';die;

if (($acaoPagina == ACAO_PAGINA_MANTER and is_null($CentroCusto)) or $acaoPagina == ACAO_PAGINA_ACOMPANHAR or $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    // em manter apenas recuperar dados quando ainda não foi preenchido
    // echo '<div style="display:none">';
    // var_dump($Solicitacao);
    // echo '</div>';die;
    if (is_null($Solicitacao)) { // solicitação nao foi informada. voltar para seleção de solicitacao
        header('Location: ' . $programaSelecao);
        exit();
    } else {
        $cargaBloqueiosManter = true;

        // recuperando dados da SCC informada
        $sql = "SELECT  CCENPOSEQU, ESOLCOOBSE, ESOLCOOBJE, ESOLCOJUST, ASOLCOANOS,
                        CTPCOMCODI, TSOLCODATA, CLICPOPROC, ALICPOANOP, CCOMLICODI,
                        CORGLICOD1, CGREMPCODI, DSOLCODPDO, CTPLEITIPO, CLEIPONUME,
                        CARTPOARTI, CINCPAINCI, FSOLCORGPR, FSOLCORPCP, FSOLCOCONT,
                        CSOLCOTIPCOSEQU, CINTRPSEQU, CINTRPSANO, FSOLCOTIPI, FSOLCODISP, FSOLCOPUBL, TSOLCOABPO, TSOLCOENPO
                FROM    SFPC.TBSOLICITACAOCOMPRA
                WHERE   CSOLCOSEQU = $Solicitacao "; // here

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        
        $cargaInicial = true;
        
        $CentroCusto              = $linha[0];
        $Observacao               = trim($linha[1]);
        $Objeto                   = trim($linha[2]);
        $Justificativa            = trim($linha[3]);
        $Ano                      = $linha[4];
        $TipoCompra               = $linha[5];
        $DataHora                 = $linha[6];
        $DataSolicitacao          = DataBarra($DataHora);
        $NumProcessoSARP          = $linha[7];
        $AnoProcessoSARP          = $linha[8];
        $ComissaoCodigoSARP       = $linha[9];
        $OrgaoLicitanteCodigoSARP = $linha[10];
        $GrupoEmpresaCodigoSARP   = $linha[11];
        $DataDom                  = DataBarra($linha[12]);
        $TipoLei                  = $linha[13];
        $Lei                      = $linha[14];
        $Artigo                   = $linha[15];
        $Inciso                   = $linha[16];
        $RegistroPreco            = $linha[17];
        $Sarp                     = $linha[18];
        $GeraContrato             = $linha[19];
        $Numero                   = $linha[20];
        $IntencaoSequ             = $linha[21];
        $IntencaoAno              = $linha[22];
        $campoCompromissoManter   = $linha[23];
        $DisputaValor             = $linha[24];
        $PublicaValor             = $linha[25];
        $DataInicioProposta       = DataBarra($linha[26]);
        $DataFimProposta          = DataBarra($linha[27]);
        $SarpTipo                 = $Sarp;
        $TipoReservaOrcamentaria  = 1; // se é bloqueio (1) ou dotação (2)
        $CarregaProcessoSARP      = 1;


        $sql = "SELECT  SC.CMATEPSEQU, SC.CSERVPSEQU, SC.EITESCDESCSE, SC.AITESCQTSO, SC.VITESCUNIT,
                        SC.VITESCVEXE, SC.AITESCQTEX, SC.EITESCMARC, SC.EITESCMODE, SC.CUSUPOCODI,
                        SC.AFORCRSEQU, SC.CITESCSEQU, F.AFORCRCCPF, F.AFORCRCCGC, SC.EITESCDESCMAT
                FROM    SFPC.TBITEMSOLICITACAOCOMPRA SC
                        LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO F ON F.AFORCRSEQU = SC.AFORCRSEQU
                WHERE   SC.CSOLCOSEQU = $Solicitacao
                ORDER BY SC.AITESCORDE ";

        $res = executarSQL($db, $sql);
        
        $cntMaterial        = - 1;
        $cntServico         = - 1;
        $tipoItem           = null;
        $strBloqueioDotacao = null;

        // para cada item de solicitação
        while ($linha = $res->fetchRow()) {
            $codigoItem = $linha[11];
            
            if (! is_null($linha[12])) {
                $fornecedorItem = $linha[12]; // CPF
            } else {
                $fornecedorItem = $linha[13]; // CNPJ
            }
            
            if (! is_null($linha[0])) {
                ++ $cntMaterial;
                $MaterialCheck[$cntMaterial]               = false;
                $MaterialCod[$cntMaterial]                 = $linha[0];
                $MaterialQuantidade[$cntMaterial]          = converte_valor_estoques($linha[3]);
                $MaterialValorEstimado[$cntMaterial]         = converte_valor_estoques($linha[4]);
                $MaterialTotalExercicio[$cntMaterial]         = converte_valor_estoques($linha[5]);
                $MaterialQuantidadeExercicio[$cntMaterial] = converte_valor_estoques($linha[6]);
                $MaterialMarca[$cntMaterial]               = $linha[7];
                $MaterialModelo[$cntMaterial]              = $linha[8];
                $MaterialFornecedor[$cntMaterial]          = $fornecedorItem;
                $MaterialDescricaoDetalhada[$cntMaterial]  = strtoupper2(trim($linha[14]));
                $tipoItem                                  = 'M';
            } else {
                ++ $cntServico;
                $ServicoCheck[$cntServico]               = false;
                $ServicoCod[$cntServico]                 = $linha[1];
                $ServicoDescricaoDetalhada[$cntServico]  = strtoupper2(trim($linha[2]));
                $ServicoQuantidade[$cntServico]          = converte_valor_estoques($linha[3]);
                $ServicoValorEstimado[$cntServico]       = converte_valor_estoques($linha[4]);
                $ServicoTotalExercicio[$cntServico]      = converte_valor_estoques($linha[5]);
                $ServicoQuantidadeExercicio[$cntServico] = converte_valor_estoques($linha[6]);
                $ServicoFornecedor[$cntServico]          = $fornecedorItem;
                $tipoItem                                = 'S';
            }
        }
        
        // para cada bloqueio$Justificativa
        $Bloqueios = array();
        if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == 'S') { // neste caso é uma dotação. pegar dotação
            $sql = "SELECT  DISTINCT AITCDOUNIDOEXER, CITCDOUNIDOORGA, CITCDOUNIDOCODI, CITCDOTIPA, AITCDOORDT,
                                     CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT
                    FROM    SFPC.TBITEMDOTACAOORCAMENT
                    WHERE   CSOLCOSEQU = $Solicitacao ";

            $res2 = executarSQL($db, $sql);

            $cntBloqueios = - 1;

            while ($linha = $res2->fetchRow()) {
                ++ $cntBloqueios;
                $pos           = $cntBloqueios + 1;
                $ano           = $linha[0];
                $orgao         = $linha[1];
                $unidade       = $linha[2];
                $tipoAtividade = $linha[3];
                $atividade     = $linha[4];
                $elemento1     = $linha[5];
                $elemento2     = $linha[6];
                $elemento3     = $linha[7];
                $elemento4     = $linha[8];
                $fonte         = $linha[9];

                $dotacao = getDadosDotacaoOrcamentariaFromChave($dbOracle, $ano, $orgao, $unidade, $tipoAtividade, $atividade, $elemento1, $elemento2, $elemento3, $elemento4, $fonte);

                $strBloqueioDotacao = $dotacao['dotacao'];

                if (is_null($strBloqueioDotacao) or $strBloqueioDotacao == '') {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('" . $strIdNome . 'Bloqueio_' . $cntBloqueios . "').focus();\" class='titulo2'>Dotação foi excluída do sistema orçamentário em material ord " . $pos . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
                array_push($Bloqueios, $strBloqueioDotacao);
            }
        } else { // pegar bloqueio
            $sql = "SELECT  DISTINCT AITCBLNBLOQ, AITCBLANOB
                    FROM    SFPC.TBITEMBLOQUEIOORCAMENT
                    WHERE   CSOLCOSEQU = $Solicitacao";

            $res2 = executarSQL($db, $sql);

            $cntBloqueios = - 1;

            while ($linha = $res2->fetchRow()) {
                ++ $cntBloqueios;
                $pos           = $cntBloqueios + 1;
                $bloqChaveAno  = $linha[1];
                $bloqChaveSequ = $linha[0];
                $bloqueioArray = getDadosBloqueioFromChave($dbOracle, $bloqChaveAno, $bloqChaveSequ);
                $strBloqueioDotacao = $bloqueioArray['bloqueio'];
                $valorBloqueio[] = $bloqueioArray['valorTotal'];

                if (is_null($strBloqueioDotacao) or $strBloqueioDotacao == '') {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('" . $strIdNome . 'Bloqueio_' . $cntBloqueios . "').focus();\" class='titulo2'>Bloqueio foi excluído do sistema orçamentário</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
                array_push($Bloqueios, $strBloqueioDotacao);
            }
        }

        unset($_SESSION['Arquivos_Upload']); // Recuperando documentos

        $sql = "SELECT CDOCSOCODI, EDOCSONOME, EDOCSOEXCL, EDOCSONOMO
                FROM    SFPC.TBDOCUMENTOSOLICITACAOCOMPRA
                WHERE   CSOLCOSEQU = $Solicitacao
                        AND EDOCSOEXCL != 'S' ";

        $res = executarSQL($db, $sql);

        while ($linha = $res->fetchRow()) {
            $codigoDoc       = $linha[0];
            $codigoNome      = $linha[1];
            $codigoExcl      = $linha[2];
            $nomeOriginalDoc = $linha[3];

            $_SESSION['Arquivos_Upload']['nome'][]           = $codigoNome;
            $_SESSION['Arquivos_Upload']['conteudo'][]       = '';
            $_SESSION['Arquivos_Upload']['situacao'][]       = 'existente';
            $_SESSION['Arquivos_Upload']['codigo'][]         = $codigoDoc;
            $_SESSION['Arquivos_Upload']['nomeOriginal'][]   = $nomeOriginalDoc;
        }
    }
}

// pegando limites de compra
// sintaxe para pegar o limite de compra: $limiteCompra[cód do tipo da compra][administração D ou I][é obras?]
$limiteCompra          = null;
$JSCriacaoLimiteCompra = '';

if ($acaoPagina == ACAO_PAGINA_MANTER or $acaoPagina == ACAO_PAGINA_INCLUIR) {
    // em manter apenas recuperar dados quando ainda não foi preenchido
    $sql = 'SELECT  CTPCOMCODI, FLICOMTIPO, CMODLICODI, VLICOMOBRA, VLICOMSERV
            FROM    SFPC.TBLIMITECOMPRA
            ORDER BY CTPCOMCODI, FLICOMTIPO, CMODLICODI, VLICOMOBRA, VLICOMSERV ';

    $res = executarSQL($db, $sql);
    // echo '<div style="display:none">';
    //     var_dump($sql);
    // echo '</div>';die;
    $oldctpcomcodi = null;
    $oldflicomtipo = null;

    while ($obj = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        if (is_null($obj->CMODLICODI) or $obj->CMODLICODI == '') {
            $limiteCompra[$obj->ctpcomcodi][$obj->flicomtipo][true]  = $obj->vlicomobra;
            $limiteCompra[$obj->ctpcomcodi][$obj->flicomtipo][false] = $obj->vlicomserv;

            if ($oldctpcomcodi == null) {
                $JSCriacaoLimiteCompra .= 'limiteCompra = new Array();';
            }

            if ($oldctpcomcodi != $obj->ctpcomcodi) {
                $JSCriacaoLimiteCompra .= 'limiteCompra[' . $obj->ctpcomcodi . '] = new Array();';
                $oldctpcomcodi = $obj->ctpcomcodi;
                $oldflicomtipo = null;
            }

            if ($oldflicomtipo != $obj->flicomtipo) {
                $JSCriacaoLimiteCompra .= 'limiteCompra[' . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "'] = new Array();";
                $oldflicomtipo = $obj->flicomtipo;
            }
            $JSCriacaoLimiteCompra .= '
                    limiteCompra[' . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "']['true'] = " . $obj->vlicomobra . ';
                    limiteCompra[' . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "']['false'] = " . $obj->vlicomserv . ';
                ';
        }
    }
}

// pegando situação atual da SCC (Este if está fora do if acima pois essa parte tem que ser carregada toda vez que recarrega a página, enquanto acima só carrega uma vez apenas)
if ($acaoPagina == ACAO_PAGINA_MANTER or $acaoPagina == ACAO_PAGINA_ACOMPANHAR or $acaoPagina == ACAO_PAGINA_EXCLUIR) { // em manter apenas recuperar dados quando ainda não foi preenchido
    $solicitacaok = isset($Solicitacao) ? $Solicitacao : $_SESSION['SeqSolicitacaok'];
    $sql = "SELECT  CSITSOCODI
            FROM    SFPC.TBSOLICITACAOCOMPRA
            WHERE   csolcosequ = $solicitacaok ";

    $situacaoSolicitacaoAtual = resultValorUnico(executarSQL($db, $sql));
}
$NCaracteresObservacao    = strlen($Observacao);
$NCaracteresObjeto        = strlen($Objeto);
$NCaracteresJustificativa = strlen($Justificativa);

// variáveis para ocultar campos e checagens associadas
$ocultarCompromisso              = false;

$ocultarCampoRegistroPreco       = false;
$ocultarCampoProcessoLicitatorio = false;
$ocultarCampoGeraContrato        = false;
$ocultarCampoLegislacao          = false;
$ocultarCampoTRP                 = false; // campo não aparecerá enquanto não for definido a tabela TRP
$ocultarCampoSARP                = false;
$ocultarCampoDataDOM             = false;
$ocultarCampoExercicio           = false;
$ocultarCampoFornecedor          = false;
$ocultarCampoNumeroSCC           = false;
$ocultarCampoNumero              = false; // ocultar campo numero
$ocultarCampoJustificativa       = false;
$preencherCampoGeraContrato      = false; // informa que, apesar de ser oculto, CampoGeraContrato deve possuir o valor 'S'
$isFornecedorUnico               = false; // informa se o campo de fornecedores dos itens está bloqueado para edição
$isValidacaoFornecedorLicitacao  = true; // informa se a validação do fornecedor deve ser de licitação. Caso false, irá validar os fornecedores como compra direta
$isFracionamentoDespesa          = false;
$ocultarCamposEdicao             = false;
$isDotacao                       = false; // caso true o campo bloqueio é usado para dotação
$isBloqueioUnico                 = false; // Se o bloqueio ou dotação é o mesmo para toda SCC (todos os itens terão os mesmos bloqueios/dotacoes)
$codigoUsuario = $_SESSION['_cperficodi_'];

if (is_null($cargaInicial)) {
    $cargaInicial = false;
}

if ($TipoCompra != TIPO_COMPRA_DISPENSA and $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) {
    
    $ocultarCampoLegislacao = true;
} else {
    $isFornecedorUnico = true;
}
if ($TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
    $ocultarCampoDataDOM = false;
    $ocultarCompromisso = true;
} else {
    $ocultarCompromisso = true;
}

if ($TipoCompra == TIPO_COMPRA_LICITACAO) {
    $ocultarCampoDataDOM = true;
    $ocultarCampoFornecedor = true;
} else {
    $ocultarCampoRegistroPreco = true;
}

if ($TipoCompra != TIPO_COMPRA_SARP && $tipoAta ==  NULL) {
    $ocultarCampoSARP = true;
    $ocultarCampoProcessoLicitatorio = true;
} else {
    $ocultarCampoTRP = true; // Pra SARP, o TRP não é mostrado
    $ocultarCampoDataDOM = true;
}

if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == 'S') {
    $ocultarCampoGeraContrato = true;
    $isDotacao = true;
}

if (($TipoCompra == TIPO_COMPRA_LICITACAO and ($RegistroPreco == 'S' or is_null($RegistroPreco)))) {
    $ocultarCampoExercicio = true;
}

if ($acaoPagina == ACAO_PAGINA_EXCLUIR or $acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
    $ifVisualizacaoThenReadOnly = 'disabled'; // variável para bloquear alteração de dados nos campos do form
    $ocultarCamposEdicao = true;
}

if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $ocultarCampoNumeroSCC = true;
    $ocultarCampoNumero = true;
}

if ($TipoCompra == TIPO_COMPRA_DIRETA or $TipoCompra == TIPO_COMPRA_DISPENSA or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
    $isValidacaoFornecedorLicitacao = false;
}

if (is_null($NCaracteresObservacao) or $NCaracteresObservacao == '') {
    $NCaracteresObservacao = '0';
}

if (is_null($NCaracteresObjeto) or $NCaracteresObjeto == '') {
    $NCaracteresObjeto = '0';
}

if (is_null($NCaracteresJustificativa) or $NCaracteresJustificativa == '') {
    $NCaracteresJustificativa = '0';
}

if ($isFornecedorUnico) {
    $ifVisualizacaoThenReadOnlyFornecedorItens = 'disabled';
}

if ($isDotacao) {
    $isBloqueioUnico = true;
}
$reserva = 'Bloqueio';

if ($isDotacao) {
    $reserva = 'Dotação';
}
$QuantidadeMaterial = count($MaterialCod); // Materiais do POST

// Pegando os dados dos materiais enviados por POST
for ($itr = 0; $itr < $QuantidadeMaterial; ++ $itr) {
    $sql = 'SELECT  M.EMATEPDESC, U.EUNIDMSIGL, I.EITESCDESCMAT
            FROM    SFPC.TBMATERIALPORTAL M
                    LEFT JOIN SFPC.TBUNIDADEDEMEDIDA U ON U.CUNIDMCODI = M.CUNIDMCODI
                    LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA I ON M.CMATEPSEQU = I.CMATEPSEQU
            WHERE   M.CMATEPSEQU = ' . $MaterialCod[$itr] . '
            GROUP BY M.EMATEPDESC, U.EUNIDMSIGL, I.EITESCDESCMAT
            ORDER BY M.EMATEPDESC, U.EUNIDMSIGL, I.EITESCDESCMAT ';

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
    }
    $Linha             = $res->fetchRow();
    $MaterialDescricao = $Linha[0];
    $MaterialUnidade   = $Linha[1];
    $MaterialDescDet   = strtoupper2($Linha[2]);
    $pos               = count($materiais);

    // preenchendo valores padrões caso seja nulo, para não ocorrer erro.
    if (is_null($MaterialQuantidade[$itr]) or $MaterialQuantidade[$itr] == '') {
        $MaterialQuantidade[$itr] = '0,0000';
    }

    if (is_null($MaterialValorEstimado[$itr]) or $MaterialValorEstimado[$itr] == '') {
        $MaterialValorEstimado[$itr] = '0,0000';
    }

    if (is_null($MaterialQuantidadeExercicio[$itr]) or $MaterialQuantidadeExercicio[$itr] == '') {
        $MaterialQuantidadeExercicio[$itr] = '0,0000';
    }

    if (is_null($MaterialTotalExercicio[$itr]) or $MaterialTotalExercicio[$itr] == '') {
        $MaterialTotalExercicio[$itr] = '0,0000';
    }
    $materiais[$pos]['posicao']              = $pos; // posição no array
    $materiais[$pos]['posicaoItem']          = $pos + 1; // posição mostrada na tela
    $materiais[$pos]['tipo']                 = TIPO_ITEM_MATERIAL;
    $materiais[$pos]['codigo']               = $MaterialCod[$itr];
    $materiais[$pos]['descricao']            = $MaterialDescricao;
    $materiais[$pos]['unidade']              = $MaterialUnidade;
    $materiais[$pos]['descricaoDetalhada']   = strtoupper(trim($MaterialDescricaoDetalhada[$itr]));

    if (is_null($MaterialCheck[$itr]) or ! $MaterialCheck[$itr]) {
        $materiais[$pos]['check'] = false;
    } else {
        $materiais[$pos]['check'] = true;
    }
    $materiais[$pos]['quantidade']          = $MaterialQuantidade[$itr];
    $materiais[$pos]['valorEstimado']       = $MaterialValorEstimado[$itr];
    $materiais[$pos]['quantidadeItem']      = moeda2float($MaterialQuantidade[$itr]); // valores em float para uso em funções
    $materiais[$pos]['valorItem']           = moeda2float($MaterialValorEstimado[$itr]); // valores em float para uso em funções
    $materiais[$pos]['quantidadeExercicio'] = $MaterialQuantidadeExercicio[$itr];
    $materiais[$pos]['marca']               = $MaterialMarca[$itr];
    $materiais[$pos]['modelo']              = $MaterialModelo[$itr];
    $materiais[$pos]['fornecedor']          = $MaterialFornecedor[$itr];
    $materiais[$pos]['isObras']             = isObras($db, $materiais[$pos]['codigo'], TIPO_ITEM_MATERIAL);

    if (moeda2float($materiais[$pos]['quantidade']) == 1) {
        $materiais[$pos]['totalExercicio'] = $MaterialTotalExercicio[$itr];
    } else {
        $materiais[$pos]['totalExercicio'] = converte_valor_estoques(moeda2float($materiais[$pos]['quantidadeExercicio']) * moeda2float($materiais[$pos]['valorEstimado']));
    }
     $materiais[$pos]['trp'] = calcularValorTrp($db,  $TipoCompra, $materiais[$pos]['codigo']);

    if (! is_null($materiais[$pos]['trp'])) {
        $materiais[$pos]['trp'] = converte_valor_estoques($materiais[$pos]['trp']);
        // Na regra o valor estimado deveria ser preenchido, mas isso gera um problema.
        // alterado por outros usuários antes da SCC ser salva, o que, ao apertar o botáo incluir, alteraria o valor estimado
        /*
         * if (is_null($materiais[$pos]["valorEstimado"]) or moeda2float($materiais[$pos]["valorEstimado"])==0) {
         * $materiais[$pos]["valorEstimado"] = $materiais[$pos]["trp"];
         * }
         */
    }

    // [CUSTOMIZAÇÃO] - Fornecedor único para compras do tipo dispensa e inexigibilidade
    if (($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $materiais[$pos]['fornecedor'] = $CnpjFornecedor;
    }
    // [/CUSTOMIZAÇÃO]
    // reservas (bloqueios ou dotações)
    /*
     * $materiais[$pos]["reservas"] = array();
     * $posReserva =-1;
     * if (!is_null($MaterialBloqueioItem[$pos])) {
     * foreach ($MaterialBloqueioItem[$pos] as $bloqueio) {
     * $posReserva ++;
     * $materiais[$pos]["reservas"][$posReserva] = $bloqueio;
     * }
     * }
     */
}
$QuantidadeServico = count($ServicoCod); // Pegando os dados dos servicos enviados por POST

for ($itr = 0; $itr < $QuantidadeServico; ++ $itr) {
    $sql = 'SELECT  M.ESERVPDESC
            FROM    SFPC.TBSERVICOPORTAL M
            WHERE   M.CSERVPSEQU = ' . $ServicoCod[$itr] . ' ';

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
    }
    $Linha = $res->fetchRow();

    $Descricao = $Linha[0];

    $pos = count($servicos);

    // preenchendo valores padrões caso seja nulo, para não ocorrer erro.
    if (is_null($ServicoQuantidade[$itr]) or $ServicoQuantidade[$itr] == '') {
        $ServicoQuantidade[$itr] = '0,0000';
    }

    if (is_null($ServicoValorEstimado[$itr]) or $ServicoValorEstimado[$itr] == '') {
        $ServicoValorEstimado[$itr] = '0,0000';
    }

    if (is_null($ServicoQuantidadeExercicio[$itr]) or $ServicoQuantidadeExercicio[$itr] == '') {
        $ServicoQuantidadeExercicio[$itr] = '0,0000';
    }

    if (is_null($ServicoTotalExercicio[$itr]) or $ServicoTotalExercicio[$itr] == '') {
        $ServicoTotalExercicio[$itr] = '0,0000';
    }
    $servicos[$pos]['posicao']              = $pos;
    $servicos[$pos]['posicaoItem']          = $pos + 1; // posição mostrada na tela
    $servicos[$pos]['tipo']                 = TIPO_ITEM_SERVICO;
    $servicos[$pos]['codigo']               = $ServicoCod[$itr];
    $servicos[$pos]['descricao']            = $Descricao;
    $servicos[$pos]['descricaoDetalhada']   = strtoupper(trim($ServicoDescricaoDetalhada[$itr]));

    if (is_null($ServicoCheck[$itr]) or ! $ServicoCheck[$itr]) {
        $servicos[$pos]['check'] = false;
    } else {
        $servicos[$pos]['check'] = true;
    }
    $servicos[$pos]['quantidade']          = $ServicoQuantidade[$itr];
    $servicos[$pos]['valorEstimado']       = $ServicoValorEstimado[$itr];
    $servicos[$pos]['quantidadeItem']      = moeda2float($ServicoQuantidade[$itr]); // valores em float para uso em funções
    $servicos[$pos]['valorItem']           = moeda2float($ServicoValorEstimado[$itr]); // valores em float para uso em funções
    $servicos[$pos]['quantidadeExercicio'] = $ServicoQuantidadeExercicio[$itr];
    $servicos[$pos]['fornecedor']          = $ServicoFornecedor[$itr];
    $servicos[$pos]['isObras']             = isObras($db, $servicos[$pos]['codigo'], TIPO_ITEM_SERVICO);

    if (moeda2float($servicos[$pos]['quantidade']) == 1) {
        $servicos[$pos]['totalExercicio'] = $ServicoTotalExercicio[$itr];
    } else {
        $servicos[$pos]['totalExercicio'] = converte_valor_estoques(moeda2float($servicos[$pos]['quantidadeExercicio']) * moeda2float($servicos[$pos]['valorEstimado']));
    }
    /*
     * $servicos[$pos]["reservas"] = array();
     * $posReserva =-1;
     * if (!is_null($ServicoBloqueioItem[$pos])) {
     * foreach ($ServicoBloqueioItem[$pos] as $bloqueio) {
     * $posReserva ++;
     * $servicos[$pos]["reservas"][$posReserva] = $bloqueio;
     * }
     * }
     */
}

// Pegando os materiais e serviços sendo incluídos via SESSION (janela de seleção de material/serviço) #
if (count($_SESSION['item']) != 0) {
    // [CUSTOMIZAÇÃO]
    if ($TipoCompra == TIPO_COMPRA_LICITACAO && $RegistroPreco == "S" && $sequencialIntencao != "" && $anoIntencao != "") {
        $servicos = array();
        $materiais = array();
    }
    // [/CUSTOMIZAÇÃO]
    sort($_SESSION['item']);
    for ($i = 0; $i < count($_SESSION['item']); ++ $i) {
        $DadosSessao = explode($SimboloConcatenacaoArray, $_SESSION['item'][$i]);
        $ItemCodigo  = $DadosSessao[1];
        $ItemTipo    = $DadosSessao[3];

        if ($ItemTipo == 'M') {
            // verificando se item já existe
            /*
             * $itemJaExiste = false;
             *
             * $qtdeMateriais = count($materiais);
             *
             * //$dataMinimaValidaTrp = prazoValidadeTrp($db,$TipoCompra)->format('Y-m-d');
             *
             * for ($i2=0; $i2<$qtdeMateriais; $i2++) {
             * if ($ItemCodigo == $materiais[$i2]["codigo"]) {
             * $itemJaExiste = true;
             * }
             * }
             */
            // incluindo item
            // if (!$itemJaExiste) {
            $sql = 'SELECT  M.EMATEPDESC, U.EUNIDMSIGL
                    FROM    SFPC.TBMATERIALPORTAL M, SFPC.TBUNIDADEDEMEDIDA U
                    WHERE   M.CMATEPSEQU = ' . $ItemCodigo . '
                            AND U.CUNIDMCODI = M.CUNIDMCODI ';

            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
            }
            $Linha = $res->fetchRow();
            $MaterialDescricao = $Linha[0];
            $MaterialUnidade = $Linha[1];

            $pos = count($materiais);
            $materiais[$pos]                        = array();
            $materiais[$pos]['tipo']                = TIPO_ITEM_MATERIAL;
            $materiais[$pos]['codigo']              = $ItemCodigo;
            $materiais[$pos]['descricao']           = $MaterialDescricao;
            $materiais[$pos]['descricaoDetalhada']  = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
            $materiais[$pos]['unidade']             = $MaterialUnidade;
            $materiais[$pos]['check']               = false;
            $materiais[$pos]['quantidade']          = !empty($DadosSessao[7]) ? $DadosSessao[7] : '0,0000';
            $materiais[$pos]['valorEstimado']       = !empty($DadosSessao[4]) ? converte_valor_estoques($DadosSessao[4]) : '0,0000';
            $materiais[$pos]['quantidadeItem']      = 0;
            $materiais[$pos]['valorItem']           = 0;
            $materiais[$pos]['quantidadeExercicio'] = '0,0000';
            $materiais[$pos]['totalExercicio']      = '0,0000';
            $materiais[$pos]['marca']               = '';
            $materiais[$pos]['modelo']              = '';
            $materiais[$pos]['fornecedor']          = '';
            $materiais[$pos]['posicao']             = $pos;
            $materiais[$pos]['posicaoItem']         = $pos + 1; // posição mostrada na tela // $materiais[$pos]["reservas"] = array();
            $materiais[$pos]['trp']                 = calcularValorTrp($db, $TipoCompra, $materiais[$pos]['codigo']);
            $materiais[$pos]['isObras']             = isObras($db, $materiais[$pos]['codigo'], TIPO_ITEM_MATERIAL);

            if (! is_null($materiais[$pos]['trp'])) {
                $materiais[$pos]['trp'] = converte_valor_estoques($materiais[$pos]['trp']);
            }

            // [CUSTOMIZAÇÃO] - Fornecedor único para compras do tipo dispensa e inexigibilidade
            if (($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) && $_SERVER['REQUEST_METHOD'] == 'POST') {
                $materiais[$pos]['fornecedor'] = $CnpjFornecedor;
            } // [/CUSTOMIZAÇÃO]
        } elseif ($ItemTipo == 'S') {
            // verificando se item já existe
            /*
             * $itemJaExiste = false;
             * $qtdeServicos = count($servicos);
             *
             * for ($i2=0; $i2<$qtdeServicos; $i2++) {
             * if ($ItemCodigo == $servicos[$i2]["codigo"]) {
             * $itemJaExiste = true;
             * }
             * }
             *
             * #incluindo item
             * if (!$itemJaExiste) {
             */
            $sql = 'SELECT  M.ESERVPDESC
                    FROM    SFPC.TBSERVICOPORTAL M
                    WHERE   M.CSERVPSEQU = ' . $ItemCodigo . ' ';

            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
            }
            $Linha = $res->fetchRow();
            $Descricao = $Linha[0];

            $pos = count($servicos);
            $servicos[$pos]                        = array();
            $servicos[$pos]['tipo']                = TIPO_ITEM_SERVICO;
            $servicos[$pos]['codigo']              = $ItemCodigo;
            $servicos[$pos]['descricao']           = $Descricao;
            $servicos[$pos]['descricaoDetalhada']  = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
            $servicos[$pos]['check']               = false;
            $servicos[$pos]['quantidade']          = !empty($DadosSessao[7]) ? $DadosSessao[7] : '0,0000';
            $servicos[$pos]['valorEstimado']       = !empty($DadosSessao[4]) ? converte_valor_estoques($DadosSessao[4]) : '0,0000';
            $servicos[$pos]['quantidadeItem']      = 0;
            $servicos[$pos]['valorItem']           = 0;
            $servicos[$pos]['quantidadeExercicio'] = '0';
            $servicos[$pos]['totalExercicio']      = '0,0000';
            $servicos[$pos]['fornecedor']          = '';
            $servicos[$pos]['posicao']             = $pos;
            $servicos[$pos]['posicaoItem']         = $pos + 1; // posição mostrada na tela
            $servicos[$pos]['isObras']             = isObras($db, $servicos[$pos]['codigo'], TIPO_ITEM_SERVICO);
        } else {
            EmailErro('Erro', __FILE__, __LINE__, 'ItemTipo não é nem material nem serviço! /n var SimboloConcatenacaoArray = ' . $SimboloConcatenacaoArray . '');
        }
    }
    unset($_SESSION['item']);
}
$qtdeMateriais = 0;

if (! is_null($materiais)) {
    $qtdeMateriais = count($materiais);
}
$qtdeServicos = 0;

if (! is_null($servicos)) {
    $qtdeServicos = count($servicos);
}
$materiaisSARP = CR92::retornarItensMateriasAtaSarp();
$servicosSARP  = CR92::retornarItensServicoAtaSarp();

if (! empty($materiaisSARP)) {
    $materiais = $materiaisSARP;
}

if (! empty($servicosSARP)) {
    $servicos = $servicosSARP;
}

// Verificando se o valor de 'GeraContrato' é automático (no campo SFPC.TBgruposubelementodespesa.fgrusecont)
if (! $ocultarCampoGeraContrato and ($qtdeMateriais + $qtdeServicos) > 0) {
    $gruposMateriaisServicos = '';
    $colocarVirgula = false;

    if (! is_null($materiais)) {
        foreach ($materiais as $item) {
            $grupo = getGrupoDeMaterialServico($db, $item['codigo'], TIPO_ITEM_MATERIAL);

            if ($colocarVirgula) {
                $gruposMateriaisServicos .= ', ';
            }
            $gruposMateriaisServicos .= $grupo;
            $colocarVirgula = true;
        }
    }

    if (! is_null($servicos)) {
        foreach ($servicos as $item) {
            $grupo = getGrupoDeMaterialServico($db, $item['codigo'], TIPO_ITEM_SERVICO);

            if ($colocarVirgula) {
                $gruposMateriaisServicos .= ', ';
            }
            $gruposMateriaisServicos .= $grupo;
            $colocarVirgula = true;
        }
    }
    $sql = "SELECT  COUNT(FGRUSECONT)
            FROM    SFPC.TBGRUPOSUBELEMENTODESPESA
            WHERE   FGRUSECONT = 'S'
                    AND CGRUMSCODI IN ($gruposMateriaisServicos) ";

    $quantidadeSubElementoComGeraContrato = resultValorUnico(executarSQL($db, $sql));

    if ($quantidadeSubElementoComGeraContrato > 0) { // Preenchendo contrato
        $GeraContrato               = 'S';
        $preencherCampoGeraContrato = true;
        $ocultarCampoGeraContrato   = false;
    }
}

// Identifica o Programa para Erro de Banco de Dados
$ErroPrograma = __FILE__;

if ($Botao == 'Imprimir' && $situacaoImp <> 10) {
    $Url = 'RelAcompanhamentoSCCPdf.php';

    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    header('Location: ' . $Url);
    exit();
}

// Ano da Requisição Ano Atual #
$AnoSolicitacao = date('Y');
$DataAtual      = date('Y-m-d');
$anoAtual       = date('Y');

// verificar se SCC está em uma situação que não pode ser alterada
if ($Botao == 'Manter' or $Botao == 'Excluir') {
    $sql = "SELECT  SOL.CORGLICODI, SOL.CSITSOCODI, SOL.CSOLCOTIPCOSEQU, SOL.CTPCOMCODI, CS.CDOCPCSEQU
            FROM    SFPC.TBSOLICITACAOCOMPRA SOL
                    LEFT OUTER JOIN SFPC.TBCONTRATOSFPC CS ON SOL.CSOLCOSEQU = CS.CSOLCOSEQU
            WHERE   SOL.CSOLCOSEQU = $Solicitacao ";

    $linha = resultLinhaUnica(executarTransacao($db, $sql));
    
    // $linha = resultObjetoUnico(executarTransacao($db, $sql));
    // echo '<div style="display:none">';
    //  var_dump($linha);
    // echo '</div>';die;
    $Orgao                 = $linha[0];
    $SituacaoCompra        = $linha[1];
    $Numero                = $linha[2];
    $tipoCompra            = $linha[3];
    $idContratoSolicitacao = $linha[4];
    $alterarSCC = false;

    // OBS.: TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO == PSE GERADA
    $arrayVerificacaoSarp = array(
        TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO,
        TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO
    );
    $arrayVerificacaoLicitacao = $arrayVerificacaoSarp;

    $arrayTipoCompra = array(
        TIPO_COMPRA_DIRETA,
        TIPO_COMPRA_DISPENSA,
        TIPO_COMPRA_INEXIGIBILIDADE
    );

    if (($SituacaoCompra == TIPO_SITUACAO_SCC_EM_ANALISE) or ($SituacaoCompra == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO)|| $_SESSION['_cperficodi_'] == 2) {
        $alterarSCC = true;
    } elseif ($tipoCompra == TIPO_COMPRA_SARP && in_array($SituacaoCompra, $arrayVerificacaoSarp) and $_SESSION['_cperficodi_'] != 2) { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (SARP - PSE Gerada). SCC='" . $Solicitacao . "'");
    } elseif ($tipoCompra == TIPO_COMPRA_LICITACAO && in_array($SituacaoCompra, $arrayVerificacaoLicitacao) and $_SESSION['_cperficodi_'] != 2) { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (Licitação - PSE Gerada). SCC='" . $Solicitacao . "'");
    } elseif (in_array($tipoCompra, $arrayTipoCompra) && $SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO && $idContratoSolicitacao != '' and $_SESSION['_cperficodi_'] != 2) { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (Contrato associado a solicitação). SCC='" . $Solicitacao . "'");
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
        if (! hasPSEImportadaSofin($db, $Solicitacao) ||$_SESSION['_cperficodi_'] == 2) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois o SOFIN já efetuou a importação dos dados da PSE. SCC='" . $Solicitacao . "'");
        }
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO ||$_SESSION['_cperficodi_'] == 2) {
        if (! hasSSCContrato($db, $Solicitacao) ||$_SESSION['_cperficodi_'] == 2) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois já está relacionada com Contrato. SCC='" . $Solicitacao . "'");
        }
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP ||$_SESSION['_cperficodi_'] == 2) {
        $alterarSCC = true;
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO) {
        if (administracaoOrgao($db, $Orgao) == 'I' ||$_SESSION['_cperficodi_'] == 2) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois não está mais na fase de análise. SCC='" . $Solicitacao . "'");
        }
    } else {
        if (($SituacaoCompra == TIPO_SITUACAO_SCC_EM_LICITACAO or $SituacaoCompra == 11) && ($_SESSION['_cperficodi_'] == 2 or $_SESSION['_cperficodi_'] == 30)) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois está em uma situação que não pode ser alterada. SCC='" . $Solicitacao . "'");
        }
    }
}
if ($CnpjFornecedor!='') {
    $existeFornecedor = getSequencialFromCpfCnpj($db, $CnpjFornecedor);
    if(is_null($existeFornecedor)){
        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidade[" . $pos . "]').focus();\" class='titulo2'>  Fornecedor não cadastrado No SICREF " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }        
}

if ($Botao == 'Excluir' and $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $sql = "SELECT  CSITSOCODI
            FROM    SFPC.TBSOLICITACAOCOMPRA
            WHERE   CSOLCOSEQU = $Solicitacao ";

    $situacao = resultValorUnico(executarSQL($db, $sql));

    $sql = 'UPDATE  SFPC.TBSOLICITACAOCOMPRA
            SET     CUSUPOCOD1 = ' . $_SESSION['_cusupocodi_'] . ',
                    TSOLCOULAT = NOW(),
                    CSITSOCODI = ' . $TIPO_SITUACAO_SCC_CANCELADA . "
            WHERE   CSOLCOSEQU = $Solicitacao ";

    executarTransacao($db, $sql);

    $sql = "INSERT INTO SFPC.TBHISTSITUACAOSOLICITACAO(CSOLCOSEQU, THSITSDATA, CSITSOCODI, XHSITSOBSE, CUSUPOCODI, THSITSULAT)
            VALUES ($Solicitacao, now(), " . $TIPO_SITUACAO_SCC_CANCELADA . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()); ';

    executarTransacao($db, $sql);
    finalizarTransacao($db);

    $Mensagem = 'Solicitação cancelada com Sucesso';

    header('Location: ' . $programaSelecao . '?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
    exit();
} elseif ($Botao == 'Incluir' or $Botao == 'Manter' or $Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
    $Mens     = 0;
    $Mensagem = '';
    // echo '<div style="display:none">';
    //     var_dump($materiais);
    // echo '</div>';die;
    if (empty($materiais) === false) {
        foreach ($materiais as $mat) {
            if ((hasIndicadorCADUM($db, (int) $mat['codigo']) && trim($mat['descricaoDetalhada']) == '') && (($Botao != 'Rascunho') && ($Botao != 'ManterRascunho'))) {
                $idHtmlDescricao = 'MaterialDescricaoDetalhada_' . $mat['posicao'];
                $posicaoTela     = $mat['posicao'] + 1;

                adicionarMensagem("<a href='javascript:document.getElementById(\"$idHtmlDescricao\").focus();' class='titulo2'>Descrição detalhada do material ord $posicaoTela</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

                $idHtmlDescricao = null;
                $posicaoTela     = null;
            }

            $DescDetMat = trim($mat['descricaoDetalhada']);

            if (strlen($DescDetMat) > 1000 && strlen(trim($DescDetMat)) > 1000) {
                $idHtmlDescricao = 'MaterialDescricaoDetalhada_' . $mat['posicao'];
                $posicaoTela     = $mat['posicao'] + 1;

                adicionarMensagem("<a href='javascript:document.getElementById(\"$idHtmlDescricao\").focus();' class='titulo2'>Descrição detalhada do material ord $posicaoTela acima do limite de 1000 caracteres</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

                $idHtmlDescricao = null;
                $posicaoTela     = null;
            }
        }
    }

    if ($CentroCusto == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"CentroCustoLink\").focus();' class='titulo2'>Centro de Custo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }

    if ($TipoCompra == '') {
        adicionarMensagem("<a href='javascript:formulario.TipoCompra.focus();' class='titulo2'> Tipo de Compra </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }
    if ($TipoCompra == 1 && $Botao == 'Manter') {
        adicionarMensagem("<a href='javascript:formulario.TipoCompra.focus();' class='titulo2'> Tipo Compra Deve ser Diferente de 'COMPRA DIRETA' Altere o tipo de compra para DISPENSA ou INEXIGIBILIDADE </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }

    if (($TipoCompra == TIPO_COMPRA_SARP) && is_null($SarpTipo)) {
        adicionarMensagem("<a href='javascript:formulario.Sarp.focus();' class='titulo2'>Tipo Sarp</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }

    if($Botao == 'Incluir' || $Botao == 'Manter'){
        if(($TipoCompra == "3" || $TipoCompra == "4") && ($_SESSION['Arquivos_Upload']['situacao'] == NULL || $_SESSION['Arquivos_Upload']['situacao'][0] == "cancelado" || $_SESSION['Arquivos_Upload']['situacao'][0] == "excluido")){
            if(($Lei == "8666" && $Artigo == "24" && $Inciso > "2") || (($TipoCompra == "4" || $TipoCompra == "3") && ($Lei == "8666" || $Lei == "14133")) || ($TipoCompra == "3" && $CnpjFornecedor == "")){
                adicionarMensagem("<a href='javascript:formulario.Documentacao.focus();' class='titulo2'> Anexar pelo menos um documento </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            
            unset($_SESSION['Arquivos_Upload']['situacao'][0]);
        }
    }

    if ($Botao != 'Rascunho' and $Botao != 'ManterRascunho') {
        if (! $ocultarCampoFornecedor and !is_null($CnpjFornecedor) and $CnpjFornecedor != "") { 
            $retorno = validaFormatoCNPJ_CPF($CnpjFornecedor);
            if (! $retorno[0]) {
                $msgAux = $retorno[1];
                adicionarMensagem("<a href='javascript:formulario.CnpjFornecedor.focus();' class='titulo2'>erro em campo de fornecedor com a mensagem '$msgAux'</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
        if ($Objeto == '') {
            adicionarMensagem("<a href='javascript:formulario.Objeto.focus();' class='titulo2'>Objeto</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        } elseif ($NCaracteresObjeto > $tamanhoObjeto) {
            adicionarMensagem("<a href='javascript:formulario.Objeto.focus();' class='titulo2'>Objeto menor que " . $tamanhoObjeto . ' caracteres</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if ($NCaracteresObservacao > '200') {
            adicionarMensagem("<a href='javascript:formulario.Observacao.focus();' class='titulo2'>Observação deve ser menor que 200 caracteres</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (! $ocultarCampoJustificativa) {
            if ($NCaracteresJustificativa > $tamanhoJustificativa) {
                adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>Justificativa menor que " . $tamanhoJustificativa . ' caracteres</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
        // var_dump($ocultarCampoGeraContrato, $GeraContrato);die;
        if (! $ocultarCampoGeraContrato and is_null($GeraContrato)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"GeraContrato\"][0].focus();' class='titulo2'> Gera Contrato </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        // if(is_null($PublicaValor) && (($TipoCompra == 3 || $TipoCompra == 4))){
        //     adicionarMensagem("<a href='javascript:formulario.elements[\"PublicaValor\"][0].focus();' class='titulo2'> Publicar na internet </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        // }
        
        if(is_null($DisputaValor) && ($TipoCompra == 3) && ($Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70))){
            adicionarMensagem("<a href='javascript:formulario.elements[\"PublicaValor\"][0].focus();' class='titulo2'> Haverá Disputa </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (! $ocultarCampoRegistroPreco and is_null($RegistroPreco)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"RegistroPreco\"][0].focus();' class='titulo2'> Registro de Preço </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (! $ocultarCampoLegislacao) {
            if (is_null($TipoLei) or $TipoLei == '') {
                adicionarMensagem("<a href='javascript:formulario.TipoLei.focus();' class='titulo2'> Tipo de Lei </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }

            if (is_null($Lei) or $Lei == '') {
                adicionarMensagem("<a href='javascript:formulario.Lei.focus();' class='titulo2'> Lei </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }

            if (is_null($Artigo) or $Artigo == '') {
                adicionarMensagem("<a href='javascript:formulario.Artigo.focus();' class='titulo2'> Artigo </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }

            if (is_null($Inciso) or $Inciso == '') {
                adicionarMensagem("<a href='javascript:formulario.Inciso.focus();' class='titulo2'> Inciso/ Parágrafo </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }

        if (! $ocultarCampoDataDOM && $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE && $Lei =="8666") {
            if (is_null($DataDom) or $DataDom == '') {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de publicação do DOM </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            } if (ValidaData($DataDom)) {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> '" . ValidaData($DataDom) . "' em Data de publicação do DOM</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            } elseif (DataInvertida($DataDom) > DataAtual()) {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de publicação do DOM menor ou igual à data atual </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            } else {
                $dataHoje = new DateTime();
                $dataDOM = new DateTime(DataInvertida($DataDom));
                $data_vigencia = new DateTime();
                $data_vigencia->setDate($dataDOM->format('Y'), $dataDOM->format('m'), $dataDOM->format('d') + prazoDOM($db));

                if (! ($data_vigencia->format('Y-m-d') >= $dataHoje->format('Y-m-d'))) {
                    adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'>A Dispensa/Inexigibilidade extrapola a data limite a partir da publicação no DOM</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
            }
        }
       
        if($TipoCompra == 3 && $Lei == "14133" && $Artigo == "75" && ($Inciso == "69" || $Inciso == "70") && $DisputaValor == "S" && $Botao == 'Incluir'){
           
            if($DataInicioProposta == '' || is_null($DataInicioProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> Data de início de recebimento das propostas </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if(ValidaData($DataInicioProposta) && !empty($DataInicioProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> '" . ValidaData($DataInicioProposta) . "' em Data de início de recebimento das propostas</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if(DataInvertida($DataInicioProposta) < Date("Y-m-d") && !empty($DataInicioProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de início de recebimento das propostas não pode ser menor que a data atual </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']); 
            }

            if($DataFimProposta == '' || is_null($DataFimProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> Data fim de recebimento das propostas </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if(ValidaData($DataFimProposta) && !empty($DataFimProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> '" . ValidaData($DataFimProposta) . "' em Data fim de recebimento das propostas</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if(DataInvertida($DataFimProposta) <= DataInvertida($DataInicioProposta) && !empty($DataFimProposta) ){
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data fim de recebimento das propostas não pode ser menor ou igual a data de início </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']); 
            }
        }
        if($TipoCompra == 3 && $Lei == "14133" && $Artigo == "75" && ($Inciso == "69" || $Inciso == "70") && $DisputaValor == "S" && $Botao == 'Manter'){
          
            if($DataInicioProposta == '' || is_null($DataInicioProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> Data de início de recebimento das propostas </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if(ValidaData($DataInicioProposta) && !empty($DataInicioProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> '" . ValidaData($DataInicioProposta) . "' em Data de início de recebimento das propostas</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
           
            if(DataInvertida($DataInicioProposta) < DataInvertida($DataSolicitacao) && !empty($DataInicioProposta)){ 
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de início de recebimento das propostas não pode ser menor que a data da Solicitação de Compra </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']); 
            }

            if($DataFimProposta == '' || is_null($DataFimProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> Data fim de recebimento das propostas </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if(ValidaData($DataFimProposta) && !empty($DataFimProposta)){
                adicionarMensagem("<a href='javascript:formulario.DataInicioProposta.focus();' class='titulo2'> '" . ValidaData($DataFimProposta) . "' em Data fim de recebimento das propostas</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if(DataInvertida($DataFimProposta) <= DataInvertida($DataInicioProposta) && !empty($DataFimProposta) ){
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data fim de recebimento das propostas não pode ser menor ou igual a data de início </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']); 
            }
        }

        if($CnpjFornecedor == ""){
            if(($TipoCompra == TIPO_COMPRA_DISPENSA && $Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)) && $DisputaValor == "S" || $TipoCompra == TIPO_COMPRA_LICITACAO){
                
            }else{
                adicionarMensagem("<a href='javascript:formulario.elements[\"CnpjFornecedor\"][0].focus();' class='titulo2'> Fornecedor </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }

        $item       = empty($MaterialCod) ? $ServicoCod : $MaterialCod;
        $isMaterial = empty($MaterialCod);
        $valido     = true;

        if (! $ocultarCampoProcessoLicitatorio and (is_null($NumProcessoSARP) or $NumProcessoSARP == '')) {
            adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (count($MaterialCod) == 0 and count($ServicoCod) == 0) { // Se não escolheu nenhum item
            adicionarMensagem("<a href='javascript:formulario.IncluirItem.focus();' class='titulo2'>Pelo menos um item de material ou serviço</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
       
        // if($Bloqueios==NULL && $TipoCompra == 2 && $RegistroPreco == 'N'){
        //     adicionarMensagem("<a href='javascript:formulario.BloqueioTitulo.focus();' class='titulo2'>Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        // }

        $fornecedorCompra       = null; // verificando se há mais de 1 fornecedor (tanto para materiais quanto servicos)
        $fornecedorCompraSetado = false;
        $elementoDespesaItem    = null;
        $posElementoDespesa     = - 1;
        
        if (! is_null($materiais)) {
            foreach ($materiais as $material) {
                if (! $GLOBALS['BloquearMensagens']) {
                    $pos = $material['posicao'];
                    $ord = $pos + 1;

                    if ($material['quantidade'] == '' or moeda2float($material['quantidade']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidade[" . $pos . "]').focus();\" class='titulo2'> Quantidade do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    if ($material['fornecedor'] != $CnpjFornecedor && $CnpjFornecedor!='') {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidade[" . $pos . "]').focus();\" class='titulo2'>Verifique se o CNPJ e razão social do fornecedor informado no(s) item(ns) corresponde ao informado no campo CPF/CNPJ do Fornecedor e clique no botão Confirmar " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    
                    if ($_POST['TipoCompra'] == 3 && $TipoLei == 1  && $Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){
                  
                    }else{
                        if ($material['valorEstimado'] == '' or moeda2float($material['valorEstimado']) == 0) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }
                    }

                    if (! $ocultarCampoFornecedor) {
                        if ($material['marca'] == '' && $TipoLei == 1  && $Lei == 14133 && $Artigo == 75 && $Inciso > 70) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialMarca[" . $pos . "]').focus();\" class='titulo2'> Marca do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }

                        if ($material['modelo'] == '' && $TipoLei == 1  && $Lei == 14133 && $Artigo == 75 && $Inciso > 70) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialModelo[" . $pos . "]').focus();\" class='titulo2'> Modelo do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }
                        
                        if ($material['fornecedor'] == ''&& ($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) && $TipoLei != 1  && $Lei != 14133 && $Artigo != 75 && ($Inciso != 69 || $Inciso != 70)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> Fornecedor do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } else {
                            if (! $fornecedorCompraSetado) { // pegando 1o fornecedor dos itens
                                $fornecedorCompra = $material['fornecedor'];
                                $fornecedorCompraSetado = true;
                            }
                            if($materiais[$pos]['fornecedor'] != ""){
                                $retorno = validaFormatoCNPJ_CPF($materiais[$pos]['fornecedor']);
                            }

                            if (! $retorno[0] && ($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) && $TipoLei != 1  && $Lei != 14133 && $Artigo != 75 && ($Inciso != 69 || $Inciso != 70)) {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> falha na validação do fornecedor do material ord " . ($ord) . ' com a seguinte mensagem:' . $retorno[1] . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                            } else {
                                $materiais[$pos]['fornecedor'] = $retorno[1];

                                if ($isFornecedorUnico and $material['fornecedor'] != $fornecedorCompra) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Para os tipos Dispensa e Inexigibilidade, só deve haver 1 fornecedor' em fornecedor do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                }
                                if (($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) && $TipoLei != 1  && $Lei != 14133 && $Artigo != 75 && ($Inciso != 69 || $Inciso != 70)){
                                    try { // checar sicref e debito mercantil
                                        validaFornecedorItemSCC($db, $material['fornecedor'], $TipoCompra, $material['codigo'], TIPO_ITEM_MATERIAL);
                                    } catch (ExcecaoPendenciasUsuario $e) {
                                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'>'" . $e->getMessage() . "' em fornecedor do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                    }
                                }
                            }
                        }
                    }

                    // if ($TipoCompra == TIPO_COMPRA_SARP) {
                    // if ($SarpTipo == 'C') {
                    // $valido = CR92::validarCondicaoSARPCarona($_SESSION ['UsuOrgLogado'], $_SESSION['ataCasoSARP'], $material['codigo'], true, $material['quantidade']);
                    // } else {
                    // $valido = CR92::validarCondicaoSARPParticpante($_SESSION ['UsuOrgLogado'], $_SESSION['ataCasoSARP'], $material['codigo'], true, $material['quantidade']);
                    // }
                    // if (!$valido) {
                    // adicionarMensagem("<a href='javascript:formulario.IncluirItem.focus();' class='titulo2'>Quantidade dos Itens da SCC to tipo Sarp Inválida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    // }
                    // }

                    $valorTotalItem = moeda2float($material['quantidade']) * moeda2float($material['valorEstimado']);

                    if ($ocultarCampoExercicio) {
                        $valorTotalExercicioItem = 0;
                    } else {
                        $varAux = trim($material['quantidadeExercicio']);

                        if ($material['quantidadeExercicio'] == '') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> Quantidade de exercício do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } else {
                            if ($material['totalExercicio'] == '') {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> Valor de exercício do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                            }
                        }
                        $valorTotalExercicioItem = moeda2float($material['totalExercicio']);

                        if (comparaFloat(moeda2float($material['quantidade']), '<', moeda2float($material['quantidadeExercicio']), 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Quantidade no Exercício maior que a Quantidade' no material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } elseif (comparaFloat($valorTotalItem, '<', $valorTotalExercicioItem, 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Valor total de exercício maior que valor total do item' no material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }
                    }
                }
            }
        }

        // if($CnpjFornecedor =='' && $DisputaValor != 'S' && ($TipoCompra == 3 || $TipoCompra == 4) && $Lei != 14133 && $Artigo != 75 && ($Inciso != 69 || $Inciso != 70) || $Lei != 8666  && $Artigo != 24 && ($Inciso != 1 || $Inciso != 2)){
        //     adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidade[" . $pos . "]').focus();\" class='titulo2'>  Fornecedor" . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
        // }
       

        if (! is_null($servicos)) {
            $posElementoDespesa = - 1;
            foreach ($servicos as $servico) {
                if (! $GLOBALS['BloquearMensagens']) {
                    $pos = $servico['posicao'];
                    $ord = $pos + 1;
                    if ($servico['quantidade'] == '' or moeda2float($servico['quantidade']) == 0 && ($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE)) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidade[" . $pos . "]').focus();\" class='titulo2'> Quantidade do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    if ($servico['fornecedor'] != $CnpjFornecedor && $CnpjFornecedor!='') {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidade[" . $pos . "]').focus();\" class='titulo2'>  Verifique se o CNPJ e razão social do fornecedor informado no(s) item(ns) corresponde ao informado no campo CPF/CNPJ do Fornecedor e clique no botão Confirmar " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                   

                    $DescDet = trim($servico['descricaoDetalhada']);
                    if (strlen($DescDet) > 1000 && strlen(trim($DescDet)) > 1000) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[" . $pos . "]').focus();\" class='titulo2'> Descrição detalhada do serviço ord " . ($ord) . ' acima do limite de 200 caracteres</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    if (! strlen($DescDet) > 0 && ! strlen(trim($DescDet)) > 0 && ($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE)) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[" . $pos . "]').focus();\" class='titulo2'> Descrição detalhada do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    // if ($servico['valorEstimado'] == '' or moeda2float($servico['valorEstimado']) == 0 && ($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE)) {
                    //     adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do servico ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    // }

                    if (! $ocultarCampoFornecedor) {
                        if($servico['fornecedor'] == '' && ($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) && $TipoLei != 1  && $Lei != 14133 && $Artigo != 75 && ($Inciso != 69 || $Inciso != 70)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'> Fornecedor do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } else {
                            if (! $fornecedorCompraSetado) { // pegando 1o fornecedor dos itens
                                $fornecedorCompra = $servico['fornecedor'];
                                $fornecedorCompraSetado = true;
                            }
                            if($servicos[$pos]['fornecedor'] != ""){
                                $retorno = validaFormatoCNPJ_CPF($servicos[$pos]['fornecedor']);
                            }

                            if (! $retorno[0] && ($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) && $TipoLei != 1  && $Lei != 14133 && $Artigo != 75 && ($Inciso != 69 || $Inciso != 70)) {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'>falha na validação do fornecedor do serviço ord " . ($ord) . ' com a seguinte mensagem:' . $retorno[1] . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                            } else {
                                $servicos[$pos]['fornecedor'] = $retorno[1];

                                if ($isFornecedorUnico and $servico['fornecedor'] != $fornecedorCompra) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Para os tipos Dispensa e Inexigibilidade, só deve haver 1 fornecedor' em fornecedor do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                }
                                if($TipoCompra == TIPO_COMPRA_DISPENSA && $Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || 70)){

                                }else{
                                    if(($TipoCompra != TIPO_COMPRA_DISPENSA or $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) && $TipoLei != 1  && $Lei != 14133 && $Artigo != 75 && ($Inciso != 69 || $Inciso != 70)){
                                        try { // checar sicref e debito mercantil
                                            validaFornecedorItemSCC($db, $servico['fornecedor'], $TipoCompra, $servico['codigo'], TIPO_ITEM_SERVICO);
                                        } catch (ExcecaoPendenciasUsuario $e) {
                                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'>'" . $e->getMessage() . "' em fornecedor do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                        }
                                    }
                                }
                                
                            }
                        }
                    }
                    $valorTotalItem = moeda2float($servico[$pos]['quantidade']) * moeda2float($servico[$pos]['valorEstimado']);

                    if ($ocultarCampoExercicio) {
                        $valorTotalExercicioItem = 0;
                    } else {
                        // if ($servico['quantidadeExercicio']=="" or moeda2float($servico['quantidadeExercicio'])==0) {
                        // adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidadeExercicio[".$pos."]').focus();\" class='titulo2'> Quantidade de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        // } elseif ($servico['totalExercicio']=="" or moeda2float($servico['totalExercicio'])==0) {
                        // adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoTotalExercicio[".$pos."]').focus();\" class='titulo2'> Valor de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        // }
                        $valorTotalExercicioItem = moeda2float($servico[$pos]['totalExercicio']);

                        if (comparaFloat(moeda2float($material['quantidade']), '<', moeda2float($material['quantidadeExercicio']), 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Quantidade no Exercício maior que a Quantidade' no serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } elseif (comparaFloat($valorTotalItem, '<', $valorTotalExercicioItem, 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Valor total de exercício maior que valor total do item' no serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }
                    }
                }
            }
        }
        $permitirSemBloqueios = false;
    } else { // Caso seja rascunho
        $permitirSemBloqueios = true;
    }

    if ($isDotacao) {
        $tipoReserva = TIPO_RESERVA_ORCAMENTARIA_DOTACAO;
    } else {
        $tipoReserva = TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO;
    }
    $itensSCC = array_merge((array) $materiais, (array) $servicos);
    $codigoUsuarioPerfil = $_SESSION['_cperficodi_'];
    if ((is_null($itensSCC) or count($itensSCC) == 0) and ! is_null($Bloqueios) and count($Bloqueios) > 0) {
        adicionarMensagem('Não é possível adicionar Bloqueios ou Dotações em SCCs que não tenham itens', $GLOBALS['TIPO_MENSAGEM_ERRO']);
    } else {
        if ($Botao == 'Incluir' or $Botao == 'Manter' or $Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
            $campoDotacaoNulo = campoDotacaoNulo();

            if ( ($TipoCompra == 2 && $RegistroPreco == 'S') || ($TipoCompra == 1 && $GeraContrato == 'N') || !($campoDotacaoNulo) ) {
                // echo '<div style="display:none">';
                //     var_dump("kim1753");
                // echo '</div>';die;
            } else {
                
                if(($TipoCompra == 3 && $Lei == 8666 && $Artigo == 24 && ($Inciso == 1 || $Inciso == 2)) || ($TipoCompra == 3 && $Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)) || ($Lei == 13303) || $TipoCompra == 5){
                    
                }else{
                    //var_dump($TipoCompra);die;
                    validarReservaOrcamentaria($db, $dbOracle, $tipoReserva, $Bloqueios, $itensSCC, 'BloqueioTodos', $TipoCompra, $codigoUsuarioPerfil, $CompromissoValor, $RegistroPreco);
                }
                
            }
        }
    }

    if ($Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
        if (! $ocultarCampoProcessoLicitatorio and (is_null($NumProcessoSARP) or $NumProcessoSARP == '')) {
            adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
    }
    // echo '<div style="display:none">';
    //     var_dump($GLOBALS['Mens']);
    // echo '</div>';die;
    if ($GLOBALS['Mens'] != 1) {
        if ((($Botao == 'Incluir' or $Botao == 'Rascunho') and $acaoPagina == ACAO_PAGINA_INCLUIR) or (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $acaoPagina == ACAO_PAGINA_MANTER)) {
            $ano = date('Y');
            //  echo '<div style="display:none">';
            //     var_dump($ano);
            //  echo '</div>';
            // Pegando dados de órgão e unidade pelo centro de custo
            $sql = "SELECT  CORGLICODI, CCENPOCORG, CCENPOUNID
                    FROM    SFPC.TBCENTROCUSTOPORTAL
                    WHERE   CCENPOSEQU = $CentroCusto ";

            $Linha = resultLinhaUnica(executarTransacao($db, $sql));
            $Orgao        = $Linha[0];
            $OrgaoSofin   = $Linha[1];
            $UnidadeSofin = $Linha[2];

            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                // Pegando ano, órgão e tipo para ver se sequencial da SCC deve mudar, e vendo se a situação da SCC
                $sql = "SELECT  CORGLICODI, ASOLCOANOS, CTPCOMCODI, CSOLCOTIPCOSEQU, CSOLCOCODI, CSITSOCODI, CINTRPSEQU, CINTRPSANO
                        FROM    SFPC.TBSOLICITACAOCOMPRA
                        WHERE   CSOLCOSEQU = $Solicitacao ";
                // echo '<div style="display:none">';
                // var_dump($sql);
                // echo '</div>';

                $linha = resultLinhaUnica(executarTransacao($db, $sql));
                $OrgaoAntes                     = $linha[0];
                $AnoAntes                       = $linha[1];
                $TipoCompraAntes                = $linha[2];
                $sequencialPorAnoOrgaoTipoAntes = $linha[3];
                $sequencialPorAnoOrgaoAntes     = $linha[4];
                $SituacaoCompraAntes            = $linha[5];
                $IntencaoSequ                   = $linha[6];
                $IntencaoAno                    = $linha[7];
                $ano = $AnoAntes; // em manter o ano nao deve mudar
                                  // aceitar Cadastramento apenas para rascunho!

                assercao(($SituacaoCompraAntes != TIPO_SITUACAO_SCC_EM_CADASTRAMENTO and $Botao != 'Rascunho' and $Botao != 'ManterRascunho') or $SituacaoCompraAntes == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO, "ERRO: Tentando alterar SCC já incluída para 'EM CADASTRAMENTO'. Abortando.");
            }

            if (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $OrgaoAntes == $Orgao and $AnoAntes == $ano and $TipoCompraAntes == $TipoCompra) { // Pegando sequencial da SCC pelo ano, orgao e tipo
                $sequencialPorAnoOrgaoTipo = $sequencialPorAnoOrgaoTipoAntes; // nao mudar o sequencial caso o ano e orgao e tipo nao mudaram
            } else {
                // para inclusão ou mudança de orgao ano ou tipo, mudar sequencial
                $sql = 'SELECT  MAX(CSOLCOTIPCOSEQU)
                        FROM    SFPC.TBSOLICITACAOCOMPRA
                        WHERE   CORGLICODI = ' . $Orgao . '
                                AND ASOLCOANOS = ' . date('Y') . '
                                AND CTPCOMCODI = ' . $TipoCompra . ' ';

                $sequencialPorAnoOrgaoTipo = resultValorUnico(executarTransacao($db, $sql));

                if (is_null($sequencialPorAnoOrgaoTipo) or $sequencialPorAnoOrgaoTipo == '') {
                    $sequencialPorAnoOrgaoTipo = 1;
                } else {
                    ++ $sequencialPorAnoOrgaoTipo;
                }
            }

            if (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $OrgaoAntes == $Orgao and $AnoAntes == $ano) { // Pegando sequencial da SCC pelo ano e orgao
                $sequencialPorAnoOrgao = $sequencialPorAnoOrgaoAntes; // nao mudar o sequencial caso o ano e orgao nao mudaram
            } else {
                // para inclusão ou mudança de orgao ou ano, mudar sequencial
                $sql = 'SELECT  MAX(CSOLCOCODI)
                        FROM    SFPC.TBSOLICITACAOCOMPRA
                        WHERE   CORGLICODI = ' . $Orgao . '
                                AND ASOLCOANOS = ' . date('Y') . ' ';

                $sequencialPorAnoOrgao = resultValorUnico(executarTransacao($db, $sql));

                if (is_null($sequencialPorAnoOrgao) or $sequencialPorAnoOrgao == '') {
                    $sequencialPorAnoOrgao = 1;
                } else {
                    ++ $sequencialPorAnoOrgao;
                }
            }
            $strCodigoSolicitacao = '';

            // tratando dados para SQL
            if ($ocultarCampoSARP /* or $Botao == "Rascunho" or $Botao == "ManterRascunho" */) { // rascunho também deve gravar licitação de SARP
                $strNumProcessoSARP          = 'null';
                $strGrupoEmpresaCodigoSARP   = 'null';
                $strAnoProcessoSARP          = 'null';
                $strComissaoCodigoSARP       = 'null';
                $strOrgaoLicitanteCodigoSARP = 'null';
            } else {
                $strNumProcessoSARP          = $NumProcessoSARP;
                $strGrupoEmpresaCodigoSARP   = $GrupoEmpresaCodigoSARP;
                $strAnoProcessoSARP          = $AnoProcessoSARP;
                $strComissaoCodigoSARP       = $ComissaoCodigoSARP;
                $strOrgaoLicitanteCodigoSARP = $OrgaoLicitanteCodigoSARP;
            }

            if ($ocultarCampoLegislacao or is_null($Inciso) or $Inciso == '') {
                $strTipoLei = 'null';
                $strLei     = 'null';
                $strArtigo  = 'null';
                $strInciso  = 'null';
            } else {
                $strTipoLei = "'" . $TipoLei . "'";
                $strLei     = "'" . $Lei . "'";
                $strArtigo  = "'" . $Artigo . "'";
                $strInciso  = "'" . $Inciso . "'";
            }

            if ($ocultarCampoGeraContrato or is_null($GeraContrato)) {
                $strGeraContrato = 'null';
            } else {
                if ($preencherCampoGeraContrato) {
                    $strGeraContrato = "'S'";
                } else {
                    $strGeraContrato = "'" . $GeraContrato . "'";
                }
            }

            if ($ocultarCampoSARP) {
                $strSarp = 'null';
            } else {
                $strSarp = "'" . $Sarp . "'";
            }

            if ($ocultarCampoRegistroPreco or is_null($RegistroPreco)) {
                $strRegistroPreco = 'null';
            } else {
                $strRegistroPreco = "'" . $RegistroPreco . "'";
            }

            if ($ocultarCampoDataDOM or is_null($DataDom) or $DataDom == '') {
                $strDataDom = 'null';
            } else {
                $strDataDom = "'" . DataInvertida($DataDom) . "'";
            }

            if ($ocultarCampoJustificativa or $Justificativa == '' or is_null($Justificativa)) {
                $strJustificativa = 'null';
            } else {
                $strJustificativa = "'" . $Justificativa . "'";
            }

            // Verificando a situação da solicitação
            $situacaoSolicitacao         = - 1;
            $fluxoVerificarGerarContrato = false;

            // Encontrando situação da solicitação
            if ($Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
                $situacaoSolicitacao = $TIPO_SITUACAO_SCC_EM_CADASTRAMENTO;
            } elseif ($TipoCompra == TIPO_COMPRA_DIRETA or $TipoCompra == TIPO_COMPRA_DISPENSA or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
                $fluxoVerificarGerarContrato = true;
            } elseif ($TipoCompra == TIPO_COMPRA_LICITACAO) {
                if ($Botao == 'Manter' && $_SESSION['_cperficodi_'] == 2 && $SituacaoCompraAntes == 9) {
                    $situacaoSolicitacao = 9;
                } else {
                        $sql = 'SELECT  FORGLITIPO
                                FROM    SFPC.TBORGAOLICITANTE
                                WHERE   CORGLICODI = ' . $Orgao . ' ';

                $administracao = resultValorUnico(executarTransacao($db, $sql));

                    if ($administracao == 'D') {
                        $situacaoSolicitacao = TIPO_SITUACAO_SCC_EM_ANALISE;
                    } elseif ($administracao == 'I') {
                        $situacaoSolicitacao = TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO;
                    } else {
                        assercao(false, 'Tipo de adiministração de órgão não reconhecido', $db);
                    }
                }
            } elseif ($TipoCompra == TIPO_COMPRA_SARP) {
                if (! isset($Solicitacao)) {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                } elseif ($Botao != 'Incluir' and isAutorizadoSarp($db, $Solicitacao)) {
                    $fluxoVerificarGerarContrato = true;
                } else {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                }
            } else {
                assercao(false, 'Tipo de compra não reconhecida', $db);
            }

            if ($fluxoVerificarGerarContrato) {
                if ($GeraContrato == 'S') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO;
                } else {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO;
                }
            }
            assercao(($situacaoSolicitacao != - 1), 'Caso da situação de solicitação de compra não está sendo tratado.', $db);
            assercao(! is_null($situacaoSolicitacao), 'Erro em variável de situação de solicitação de compra. Variável nula. Motivo provável é se foi usado uma constante nula.', $db);

            $sequencialIntencao = !empty($sequencialIntencao) ? (int) $sequencialIntencao : 'null';
            $anoIntencao = !empty($anoIntencao) ? $anoIntencao : 'null';
            $sequencialSolicitacao = $Solicitacao;

            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                $Observacao = str_replace("'","''",trim($Observacao));
                $Observacao = RetiraAcentos($Observacao);

                $Objeto = str_replace("'","''",trim($Objeto));
                $Objeto = RetiraAcentos($Objeto);

                $DataHoraInicioProposta = DataInvertida($DataInicioProposta)." "."00:00:01";
                $DataHoraFimProposta = DataInvertida($DataFimProposta)." "."23:59:59";
                $DataHoraInicioProposta = ($DataHoraInicioProposta != "--"." "."00:00:01")?"'$DataHoraInicioProposta'":'null';
                $DataHoraFimProposta = ($DataHoraFimProposta != "--"." "."23:59:59")?"'$DataHoraFimProposta'":'null';

                if($CnpjFornecedor == '' && (($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE))){
                    
                    $situacaoSolicitacao = 1;
                    
                }
               
                if($CnpjFornecedor == "" && $DisputaValor == 'S' && $TipoCompra == TIPO_COMPRA_DISPENSA && $Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){
    
                    if($Botao == 'ManterRascunho'){
                        $situacaoSolicitacao = 1;
                    }else{
                        $situacaoSolicitacao = 11;
                    }
                       
                }

                $Justificativa = str_replace("'","''",trim($Justificativa));
                $Justificativa = RetiraAcentos($Justificativa);
                $compromissoString = "'".$CompromissoValor."'";
                if($OrgaoAntes == $Orgao){
                    $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                    SET     CORGLICODI = " . $Orgao . ",
                            CSOLCOCODI = " . $sequencialPorAnoOrgao . ",
                            CTPCOMCODI = " . $TipoCompra . ",
                            CSOLCOTIPCOSEQU = " . $sequencialPorAnoOrgaoTipo . ",
                            CCENPOSEQU = " . $CentroCusto . ",
                            ESOLCOOBSE = '".$Observacao."' ,
                            ESOLCOOBJE = '".$Objeto."',
                            ESOLCOJUST = '".$Justificativa."',
                            CLICPOPROC = " . $strNumProcessoSARP . ",
                            ALICPOANOP = " . $strAnoProcessoSARP . ",
                            CGREMPCODI = " . $strGrupoEmpresaCodigoSARP . ",
                            CCOMLICODI = " . $strComissaoCodigoSARP . ",
                            CORGLICOD1 = " . $strOrgaoLicitanteCodigoSARP . ",
                            DSOLCODPDO = " . $strDataDom . ",
                            CTPLEITIPO = " . $strTipoLei . ",
                            CLEIPONUME = " . $strLei . ",
                            CARTPOARTI = " . $strArtigo . ",
                            CINCPAINCI = " . $strInciso . ",
                            FSOLCORGPR = " . $strRegistroPreco . ",
                            FSOLCORPCP = '" . $SarpTipo . "',
                            FSOLCOCONT = " . $strGeraContrato . ",
                            fsolcotipi = " . $compromissoString . ",
                            TSOLCOULAT = now(),
                            CSITSOCODI = " . $situacaoSolicitacao . ",
                            CINTRPSEQU = " . $sequencialIntencao . ",
                            fsolcodisp  = '". $DisputaValor."',
                            fsolcopubl  = '".$PublicaValor."',
                            CINTRPSANO = " . $anoIntencao.",
                            tsolcoabpo = ".$DataHoraInicioProposta.",
                            tsolcoenpo = ".$DataHoraFimProposta;
                    $sql .= " WHERE CSOLCOSEQU = $Solicitacao";
                    // echo '<div style="display:none">';
                    //     var_dump($sql);
                    // echo '</div>';
                    // die;
                    executarTransacao($db, $sql);

                    if ($_SESSION['_cperficodi_'] == 2 && $SituacaoCompraAntes == 9) {
                        $codUsuario = $_SESSION['_cusupocodi_'];

                        $sql = "UPDATE  SFPC.TBLICITACAOPORTAL
                                SET     XLICPOOBJE = '".$Objeto."',
                                        CUSUPOCODI = $codUsuario,
                                        TLICPOULAT = now()
                                WHERE	CLICPOPROC = (SELECT CLICPOPROC FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CSOLCOSEQU = $Solicitacao)
                                        AND ALICPOANOP = (SELECT ALICPOANOP FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CSOLCOSEQU = $Solicitacao)
                                        AND CCOMLICODI = (SELECT CCOMLICODI FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CSOLCOSEQU = $Solicitacao) ";

                        executarTransacao($db, $sql);
                    }
                }else{
                    // assercao(false, "Esta SCC não pode ser vinculada a orgãos diferentes.");
                }

            } else {
                $Observacao = str_replace("'","''",trim($Observacao));
                $Observacao = RetiraAcentos($Observacao);

                $Objeto = str_replace("'","''",trim($Objeto));
                $Objeto = RetiraAcentos($Objeto);

                $Justificativa = str_replace("'","''",trim($Justificativa));
                $Justificativa = RetiraAcentos($Justificativa);
                $compromissoString = "'".$CompromissoValor."'";
                $DisputaValor = "'".$DisputaValor."'";
                $PublicaValor = "'".$PublicaValor."'";
                
                $DataHoraInicioProposta = DataInvertida($DataInicioProposta)." "."00:00:01";
                $DataHoraFimProposta = DataInvertida($DataFimProposta)." "."23:59:59";
                $DataHoraInicioProposta = ($DataHoraInicioProposta != "--"." "."00:00:01")?"'$DataHoraInicioProposta'":'null';
                $DataHoraFimProposta = ($DataHoraFimProposta != "--"." "."23:59:59")?"'$DataHoraFimProposta'":'null';

                if($CnpjFornecedor == '' && (($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE))){
                    
                    $situacaoSolicitacao = 1;
                }
           
                if($CnpjFornecedor == "" && $DisputaValor == "'S'" && $TipoCompra == "3" && $Lei == "14133" && $Artigo == "75" && ($Inciso == "69" || $Inciso == "70")){
                   
                    if($Botao != 'Rascunho'){
                        
                        $situacaoSolicitacao = 11;
                    }else{
                        
                        $situacaoSolicitacao = 1;
                    }
                       
                }
                
                $sql = 'INSERT INTO SFPC.TBSOLICITACAOCOMPRA (CORGLICODI, ASOLCOANOS, CSOLCOCODI, CTPCOMCODI, CSOLCOTIPCOSEQU, TSOLCODATA, CCENPOSEQU,
                                    ESOLCOOBSE, ESOLCOOBJE, ESOLCOJUST, CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICOD1, DSOLCODPDO, CTPLEITIPO,
                                    CLEIPONUME, CARTPOARTI, CINCPAINCI, FSOLCORGPR, FSOLCORPCP, FSOLCOCONT, CUSUPOCODI, CUSUPOCOD1, TSOLCOULAT, CSITSOCODI, CINTRPSEQU, CINTRPSANO, fsolcotipi, fsolcodisp, fsolcopubl, tsolcoabpo, tsolcoenpo)
                        VALUES (' . $Orgao . ', ' . $anoAtual . ', ' . $sequencialPorAnoOrgao . ', ' . $TipoCompra . ', ' . $sequencialPorAnoOrgaoTipo . ',
                                now(), ' . $CentroCusto . ", '" . $Observacao . "', '" . $Objeto . "', '" . $Justificativa . "', " . $strNumProcessoSARP . ',
                                ' . $strAnoProcessoSARP . ', ' . $strGrupoEmpresaCodigoSARP . ', ' . $strComissaoCodigoSARP . ', ' . $strOrgaoLicitanteCodigoSARP . ', ' . $strDataDom . ', ' . $strTipoLei . ',
                                ' . $strLei . ', ' . $strArtigo . ', ' . $strInciso . ', ' . $strRegistroPreco . ", '" . $SarpTipo . "', " . $strGeraContrato . ',
                                ' . $_SESSION['_cusupocodi_'] . ', ' . $_SESSION['_cusupocodi_'] . ', now(), ' . $situacaoSolicitacao . ',' . $sequencialIntencao . ',' . $anoIntencao . ',' . $compromissoString .','.$DisputaValor.','.$PublicaValor.', '.$DataHoraInicioProposta.', '.$DataHoraFimProposta.')';

                executarTransacao($db, $sql);
            }
            
            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') { // Deletando itens e salvando no histórico
                $sequencialSolicitacao = $Solicitacao;
            } else {
                $sql = 'SELECT LAST_VALUE FROM SFPC.TBSOLICITACAOCOMPRA_CSOLCOSEQU_SEQ1 ';
                $sequencialSolicitacao = resultValorUnico(executarTransacao($db, $sql));
                
                $seqSCC = getNumeroSolicitacaoCompra($db, $sequencialSolicitacao);
                $anoSeqSCC = substr($seqSCC,-5);
                $seqSCCSemAno = str_replace($anoSeqSCC, "", $seqSCC);
                $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                        SET esolconume = '" .$seqSCCSemAno."'";
                $sql .= " WHERE CSOLCOSEQU = $sequencialSolicitacao";

                executarTransacao($db, $sql);
            }
           
            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') { // Deletando itens e salvando no histórico
                // Apagando PSEs para apagar os itens de SCC
                $sql = "SELECT  '( '||APRESOANOE||', '||CPRESOSEQU||')' AS CHAVE
                        FROM    SFPC.TBPRESOLICITACAOEMPENHO
                        WHERE   CSOLCOSEQU = " . $sequencialSolicitacao . ' ';

                $resPSE = executarSQL($db, $sql);

                if (hasPSEImportadaSofin($db, $sequencialSolicitacao)) {
                    assercao(false, 'ERRO: SCC possui PSE que já foi processado pelo SOFIN. Portanto, não é possível alterá-la!');
                }

                while ($pse = $resPSE->fetchRow(DB_FETCHMODE_OBJECT)) {
                    $sql = 'DELETE FROM SFPC.TBITEMPRESOLICITACAOEMPENHO WHERE (apresoanoe, cpresosequ) = ' . $pse->chave . '';
                    executarTransacao($db, $sql);
                }
                $sql = 'DELETE FROM SFPC.TBPRESOLICITACAOEMPENHO WHERE CSOLCOSEQU = ' . $sequencialSolicitacao . '';
                executarTransacao($db, $sql);

                // remover todos itens de compra para depois recriá-los
                $sql = "DELETE FROM sfpc.tbitemdotacaoorcament WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbitembloqueioorcament WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbtabelareferencialprecos WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbitemsolicitacaocompra WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);

                // salvar o histórico da situação da SCC
                if ($situacaoSolicitacaoAtual != $situacaoSolicitacao) {
                    $sql = "INSERT INTO sfpc.tbhistsituacaosolicitacao (csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                            VALUES ($sequencialSolicitacao, now(), " . $situacaoSolicitacao . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()); ';
                }
                executarTransacao($db, $sql);
            } else {
                // Incluir
                // salvar o histórico da situação da SCC
                $sql = "INSERT INTO sfpc.tbhistsituacaosolicitacao (csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                        VALUES ($sequencialSolicitacao, now(), " . $situacaoSolicitacao . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()); ';
                executarTransacao($db, $sql);
            }

            // Incluindo os itens
            $sequencialItem = 0;

            if (is_array($materiais)) {
                foreach ($materiais as $material) {
                    ++ $sequencialItem;
                    $ordem = $material['posicao'] + 1;

                    $totalExercicio      = 0.0000;
                    $quantidadeExercicio = 0.0000;

                    if (! $ocultarCampoExercicio) {
                        $totalExercicio      = $material['totalExercicio'];
                        $quantidadeExercicio = $material['quantidadeExercicio'];
                    }

                    if ($ocultarCampoFornecedor) {
                        $strFornecedorSeq = 'null';
                    } else {
                        $strFornecedor = "'" . removeSimbolos($material['fornecedor']) . "'";

                        $sql = 'SELECT  AFORCRSEQU
                                FROM    SFPC.TBFORNECEDORCREDENCIADO
                                WHERE   AFORCRCCGC = ' . $strFornecedor . ' OR AFORCRCCPF = ' . $strFornecedor . ' ';

                        $strFornecedorSeq = resultValorUnico(executarTransacao($db, $sql));

                        if (is_null($strFornecedorSeq)) {
                            $strFornecedorSeq = 'null';
                        } else {
                            $strFornecedorSeq = "'" . $strFornecedorSeq . "'";
                        }
                    }
                    $material['descricaoDetalhada'] = str_replace("'","''",$material['descricaoDetalhada']);

                    // $material['descricaoDetalhada'] =  str_replace('€', '', $material['descricaoDetalhada']); //removeCaracteresEspeciais($material['descricaoDetalhada']);
                    $material['descricaoDetalhada'] =  RetiraAcentos($material['descricaoDetalhada']);

                    $sql = 'INSERT INTO SFPC.TBITEMSOLICITACAOCOMPRA (CSOLCOSEQU, CITESCSEQU, CMATEPSEQU, CSERVPSEQU, EITESCDESCSE, AITESCORDE, AITESCQTSO, VITESCUNIT, VITESCVEXE, AITESCQTEX, AFORCRSEQU, EITESCMARC, EITESCMODE, CUSUPOCODI, TITESCULAT, EITESCDESCMAT)
                            VALUES (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', ' . $material['codigo'] . ', null, null, ' . $ordem . ", '" . moeda2float($material['quantidade']) . "', '" . moeda2float($material['valorEstimado']) . "', '" . moeda2float($totalExercicio) . "', '" . moeda2float($quantidadeExercicio) . "', " . $strFornecedorSeq . ", '" . $material['marca'] . "', '" . $material['modelo'] . "', " . $_SESSION['_cusupocodi_'] . ", now(), '" . trim($material['descricaoDetalhada']) . "'); ";

                    executarTransacao($db, $sql);

                    if (count($Bloqueios) > 0) {
                        foreach ($Bloqueios as $bloqueio) {
                            if (isset($bloqueio)) {
                                if ($isDotacao) {
                                    $dados = getDadosDotacaoOrcamentaria($dbOracle, $bloqueio);
                                    assercao(! is_null($dados), 'Dotação Inválido ou Inexistente');

                                    $sql = 'INSERT INTO SFPC.TBITEMDOTACAOORCAMENT (CITESCSEQU, CSOLCOSEQU, AITCDOUNIDOEXER, CITCDOUNIDOORGA, CITCDOUNIDOCODI, CITCDOTIPA, AITCDOORDT, CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT, TITCDOULAT)
                                            VALUES (' . $sequencialItem . ', ' . $sequencialSolicitacao . ', ' . $dados['ano'] . ', ' . $dados['orgao'] . ', ' . $dados['unidade'] . ', ' . $dados['tipoProjetoAtividade'] . ', ' . $dados['projetoAtividade'] . ', ' . $dados['elemento1'] . ', ' . $dados['elemento2'] . ', ' . $dados['elemento3'] . ', ' . $dados['elemento4'] . ', ' . $dados['fonte'] . ', now()); ';

                                    executarTransacao($db, $sql);
                                } else {
                                    $dados = getDadosBloqueio($dbOracle, $bloqueio);

                                    assercao(! is_null($dados), 'Bloqueio Inválido ou Inexistente');

                                    $sql = 'INSERT INTO SFPC.TBITEMBLOQUEIOORCAMENT (CSOLCOSEQU, CITESCSEQU, TITCBLULAT, AITCBLNBLOQ, AITCBLANOB)
                                            VALUES (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', now(), ' . $dados['sequencialChave'] . ', ' . $dados['anoChave'] . '); ';
                                    executarTransacao($db, $sql);
                                }
                            }
                        }
                    }
                }
            }

            if (! is_null($servicos)) {
                foreach ($servicos as $servico) {
                    ++ $sequencialItem;
                    $ordem               = $servico['posicao'] + 1;
                    $totalExercicio      = 0.0000;
                    $quantidadeExercicio = 0.0000;

                    if (! $ocultarCampoExercicio) {
                        $totalExercicio      = $servico['totalExercicio'];
                        $quantidadeExercicio = $servico['quantidadeExercicio'];
                    }

                    if ($ocultarCampoFornecedor) {
                        $strFornecedorSeq = 'null';
                    } else {
                        $strFornecedor = "'" . removeSimbolos($servico['fornecedor']) . "'";

                        $sql = 'SELECT  AFORCRSEQU
                                FROM    SFPC.TBFORNECEDORCREDENCIADO
                                WHERE   AFORCRCCGC = ' . $strFornecedor . ' OR AFORCRCCPF = ' . $strFornecedor . ' ';

                        $strFornecedorSeq = resultValorUnico(executarTransacao($db, $sql));

                        if (is_null($strFornecedorSeq)) {
                            $strFornecedorSeq = 'null';
                        } else {
                            $strFornecedorSeq = "'" . $strFornecedorSeq . "'";
                        }
                    }
                    $servico['descricaoDetalhada'] = str_replace("'","''",trim($servico['descricaoDetalhada']));

                    // $servico['descricaoDetalhada'] =  str_replace('€', '', $servico['descricaoDetalhada']); //removeCaracteresEspeciais($servico['descricaoDetalhada']);
                    $servico['descricaoDetalhada'] =  RetiraAcentos($servico['descricaoDetalhada']);

                    $sql = 'INSERT  INTO SFPC.TBITEMSOLICITACAOCOMPRA (CSOLCOSEQU, CITESCSEQU, CMATEPSEQU, CSERVPSEQU, EITESCDESCSE, AITESCORDE, AITESCQTSO, VITESCUNIT, VITESCVEXE, AITESCQTEX, AFORCRSEQU, EITESCMARC, EITESCMODE, CUSUPOCODI, TITESCULAT)
                            VALUES  (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', null, ' . $servico['codigo'] . ", '" . trim($servico['descricaoDetalhada']) . "', " . $ordem . ", '" . moeda2float($servico['quantidade']) . "', '" . moeda2float($servico['valorEstimado']) . "', '" . moeda2float($totalExercicio) . "', '" . moeda2float($quantidadeExercicio) . "', $strFornecedorSeq, null, null, " . $_SESSION['_cusupocodi_'] . ', now()); ';

                    executarTransacao($db, $sql);

                    if (count($Bloqueios) > 0) {
                        foreach ($Bloqueios as $bloqueio) {
                            if (isset($bloqueio)) {
                                echo $isDotacao;

                                if ($isDotacao) {
                                    $dados = getDadosDotacaoOrcamentaria($dbOracle, $bloqueio);
                                    assercao(! is_null($dados), 'Dotação Inválido ou Inexistente');

                                    $sql = 'INSERT  INTO SFPC.TBITEMDOTACAOORCAMENT (CITESCSEQU, CSOLCOSEQU, AITCDOUNIDOEXER, CITCDOUNIDOORGA, CITCDOUNIDOCODI, CITCDOTIPA, AITCDOORDT, CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT, TITCDOULAT)
                                            VALUES  (' . $sequencialItem . ', ' . $sequencialSolicitacao . ', ' . $dados['ano'] . ', ' . $dados['orgao'] . ', ' . $dados['unidade'] . ', ' . $dados['tipoProjetoAtividade'] . ', ' . $dados['projetoAtividade'] . ', ' . $dados['elemento1'] . ', ' . $dados['elemento2'] . ', ' . $dados['elemento3'] . ', ' . $dados['elemento4'] . ', ' . $dados['fonte'] . ', now()); ';

                                    executarTransacao($db, $sql);
                                } else {
                                    $dados = getDadosBloqueio($dbOracle, $bloqueio);
                                    assercao(! is_null($dados), 'Bloqueio Inválido ou Inexistente');

                                    $sql = 'INSERT INTO SFPC.TBITEMBLOQUEIOORCAMENT (CSOLCOSEQU, CITESCSEQU, TITCBLULAT, AITCBLNBLOQ, AITCBLANOB)
                                            VALUES (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', now(), ' . $dados['sequencialChave'] . ', ' . $dados['anoChave'] . '); ';

                                    executarTransacao($db, $sql);
                                }
                            }
                        }
                    }
                }
            }
            
            // inserir documentos
            $dirdestino = $GLOBALS['CAMINHO_UPLOADS'] . 'compras/';
            for ($i = 0; $i < count($_SESSION['Arquivos_Upload']['conteudo']); ++ $i) {
                $sql = "SELECT  MAX(CDOCSOCODI)
                        FROM    SFPC.TBDOCUMENTOSOLICITACAOCOMPRA
                        WHERE   CSOLCOSEQU = $sequencialSolicitacao ";

                $CodigoDocto = resultValorUnico(executarTransacao($db, $sql)) + 1;
                $extensãoArquivo = substr($_SESSION['Arquivos_Upload']['nomeOriginal'][$i],-4);
                $NomeDocto   = 'DOC_' . $sequencialSolicitacao . '_' . $CodigoDocto . $extensãoArquivo;
                $nomeOriginalDoc = $_SESSION['Arquivos_Upload']['nomeOriginal'][$i];

                if ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'novo') {
                    $arquivo_criado = file_put_contents($dirdestino . $NomeDocto, $_SESSION['Arquivos_Upload']['conteudo'][$i]);
                    assercao($arquivo_criado, 'Falha na inclusão do documento. Verifique se o diretório de gravação não está protegido contra escrita.');
                    
                    $sql = "INSERT INTO SFPC.TBDOCUMENTOSOLICITACAOCOMPRA (CSOLCOSEQU, CDOCSOCODI, EDOCSONOME, CUSUPOCODI, TDOCSOULAT, EDOCSOEXCL, EDOCSONOMO)
                            VALUES ($sequencialSolicitacao, $CodigoDocto, '" . $NomeDocto . "', " . $_SESSION['_cusupocodi_'] . ", now(), 'N' , '" . $nomeOriginalDoc . "') ";

                    executarTransacao($db, $sql);
                } elseif ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'excluido') {
                    $sql = "UPDATE  SFPC.TBDOCUMENTOSOLICITACAOCOMPRA
                            SET     EDOCSOEXCL = 'S'
                            WHERE   CSOLCOSEQU = $sequencialSolicitacao
                                    AND CDOCSOCODI = " . $_SESSION['Arquivos_Upload']['codigo'][$i] . ' ';

                    executarTransacao($db, $sql);
                }
            }

            // Transação foi bem sucedida. gerar pre solicitação
            if ($situacaoSolicitacao == $TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
                // neste ponto, o pendente de empenho são só de SCCs que não foram processadas ainda pelo sofin. Neste caso
                try {
                    gerarPreSolicitacaoEmpenho($db, $dbOracle, $sequencialSolicitacao);
                } catch (Excecao $e) {
                    cancelarTransacao($db);
                    $e->getMessage();
                    adicionarMensagem('Não foi possível gerar a solicitação de compra pois houve falha ao gerar a solicitação de empenho, com a seguinte mensagem: ' . $e->getMessage(), $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
            }

            // ***********************************
            // Gerar TRP
            // ***********************************
            if ($GLOBALS['Mens'] != 1 && !is_null($materiais)) {
                inserirItensSCCNaTrp($sequencialSolicitacao, $db);
            }

            if ($GLOBALS['Mens'] != 1) {
                finalizarTransacao($db);
                $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $sequencialSolicitacao);

                if ($acaoPagina == ACAO_PAGINA_MANTER) {
                    if($OrgaoAntes == $Orgao){
                        $Mensagem = 'Solicitação ' . $strSolicitacaoCodigo . ' Alterada com Sucesso';
                        header('Location: CadSolicitacaoCompraManterSelecionar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
                        exit();
                    }else{
                        $Mensagem = 'A SCC não pode ser alterada por uma SCC de órgão diferente. Favor informar uma SCC do mesmo órgão.';
                        header('Location: CadSolicitacaoCompraManter.php?SeqSolicitacao='.$sequencialSolicitacao.'&Mens=1&Tipo=1&Mensagem=' . $Mensagem);
                        exit();
                    }
                    // adicionarMensagem($Mensagem, $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);
                } else {
                    $Mensagem = 'Solicitação ' . $strSolicitacaoCodigo . ' Incluída com Sucesso';
                    // adicionarMensagem($Mensagem, $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);

                    // Limpar variáveis
                    $Botao                       = null;
                    $InicioPrograma              = null;
                    $CentroCusto                 = null;
                    $Observacao                  = null;
                    $Objeto                      = null;
                    $Justificativa               = null;
                    $NCaracteresObservacao       = null;
                    $NCaracteresObjeto           = null;
                    $NCaracteresJustificativa    = null;
                    $DataDom                     = null;
                    $Lei                         = null;
                    $Artigo                      = null;
                    $Inciso                      = null;
                    $Foco                        = null;
                    $TipoLei                     = null;
                    $RegistroPreco               = null;
                    $Sarp                        = null;
                    $BloqueioTodos               = null;
                    $TipoReservaOrcamentaria     = null; // se é bloqueio (1) ou dotação (2)
                    $DotacaoTodos                = null;
                    $CnpjFornecedor              = null;
                    $GeraContrato                = null;
                    $TipoCompra                  = null;
                    $NomeDocumento               = null;
                    $DDocumento                  = null;
                    $OrigemBancoPreços           = null;
                    $NumProcessoSARP             = null;
                    $AnoProcessoSARP             = null;
                    $ComissaoCodigoSARP          = null;
                    $OrgaoLicitanteCodigoSARP    = null;
                    $GrupoEmpresaCodigoSARP      = null;
                    $CarregaProcessoSARP         = null;
                    $MaterialCheck               = null;
                    $MaterialCod                 = null;
                    $MaterialQuantidade          = null;
                    $MaterialValorEstimado       = null;
                    $MaterialTotalExercicio      = null;
                    $MaterialQuantidadeExercicio = null;
                    $MaterialMarca               = null;
                    $MaterialModelo              = null;
                    $MaterialFornecedor          = null;
                    $ServicoCheck                = null;
                    $ServicoCod                  = null;
                    $ServicoQuantidade           = null;
                    $ServicoDescricaoDetalhada   = null;
                    $ServicoQuantidadeExercicio  = null;
                    $ServicoValorEstimado        = null;
                    $ServicoTotalExercicio       = null;
                    $ServicoFornecedor           = null;
                    $materiais                   = array();
                    $servicos                    = array();
                    $isDotacaoAnterior           = null; // informa se na pagina anterior era dotação ou bloqueio
                    $Bloqueios                   = null;
                    $BloqueiosCheck              = null;
                    $BloqueioAno                 = null;
                    $BloqueioOrgao               = null;
                    $BloqueioUnidade             = null;
                    $BloqueioDestinacao          = null;
                    $BloqueioSequencial          = null;
                    $DotacaoAno                  = null;
                    $DotacaoOrgao                = null;
                    $DotacaoUnidade              = null;
                    $DotacaoFuncao               = null;
                    $DotacaoSubfuncao            = null;
                    $DotacaoPrograma             = null;
                    $DotacaoTipoProjetoAtividade = null;
                    $DotacaoProjetoAtividade     = null;
                    $DotacaoElemento1            = null;
                    $DotacaoElemento2            = null;
                    $DotacaoElemento3            = null;
                    $DotacaoElemento4            = null;
                    $DotacaoFonte                = null;

                    unset($_SESSION['Arquivos_Upload']);
                    // header('Location: CadSolicitacaoCompraManterSelecionar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
                    header('Location: CadSolicitacaoCompraIncluir.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
                    exit();
                }
            }
        }
    }
} elseif ($Botao == 'Retirar') {
    $quantidade = count($materiais);

    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        if ($materiais[$itr]['check']) {
            $materiais = array_removerItem($materiais, $itr);
            // $MaterialBloqueioItem = array_removerItem($MaterialBloqueioItem, $itr);
            $quantidadeNova = count($materiais);

            if ($quantidadeNova != $quantidade) { // verificação de tamanho para confirmar exclusão, para evitar loop infinito causado pelo itr--
                $quantidade = $quantidadeNova;
                -- $itr; // compensando a posição do item removido
            }
        }
    }
    $quantidade = count($materiais);

    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $materiais[$itr]['posicao'] = $itr;
    }
    $quantidade = count($servicos);

    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        if ($servicos[$itr]['check']) {
            $servicos = array_removerItem($servicos, $itr);
            // $ServicoBloqueioItem = array_removerItem($ServicoBloqueioItem, $itr);
            $quantidadeNova = count($servicos);

            if ($quantidadeNova != $quantidade) {
                $quantidade = $quantidadeNova;
                -- $itr; // compensando a posição do item removido
            }
        }
    }
    $quantidade = count($servicos);

    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $servicos[$itr]['posicao'] = $itr;
    }
} elseif ($Botao == 'Incluir_Documento') {


if(strlen($_FILES['Documentacao']['name']) >= 100){
        $template->DISABLED = "disabled";
        $programaSelecao = 'CadSolicitacaoCompraIncluir.php';
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= 'O nome do arquivo é muito grande. Máximo de 100 caracteres';

        // header('Location: ' . $programaSelecao);
        //exit;
    }

    if ($_FILES['Documentacao']['tmp_name']) {
        $_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);

        $extensoesArquivo .= ', .zip, .xlsm';

        $extensoes = explode(',', strtolower2($extensoesArquivo));
        array_push($extensoes, '.zip', '.xlsm');

        $noExtensoes = count($extensoes);
        $isExtensaoValida = false;

        for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
            // echo strtolower2($_FILES['Documentacao']['name']);
            // echo "\n".strtolower2($_FILES['Documentacao']['name']);
            // exit;
            if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
                $isExtensaoValida = true;
            }
        }

        if (! $isExtensaoValida) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
        }

        if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
        }
        $Tamanho = $tamanhoArquivo * pow(10, 6);  // tamanho em MB

        if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Mbytes    = $tamanhoArquivo;
            $Mbytes    = (int) $Mbytes;
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Mbytes Mb";
        }

        if ($Mens == '') {
            if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Caminho da Documentação Inválido';
            } else {
                $_SESSION['Arquivos_Upload']['nomeOriginal'][] = $_FILES['Documentacao']['name'];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
            }
        }
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = 'Documentação Inválida';
    }
} elseif ($Botao == 'Retirar_Documento') {
    foreach ($DDocumento as $valor) {
        // $_SESSION['Arquivos_Upload']['conteudo'][$valor]="";
        // $_SESSION['Arquivos_Upload']['nome'][$valor]="";
        if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
        } elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
        }
    }
} elseif ($Botao == 'IncluirBloqueio') {
    $BloqueioTodos = '';

    if ($isDotacao) {
        if (is_null($DotacaoAno) or $DotacaoAno == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoAno').focus();\" class='titulo2'>Ano da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoOrgao) or $DotacaoOrgao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoOrgao').focus();\" class='titulo2'>Orgão da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoUnidade) or $DotacaoUnidade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoUnidade').focus();\" class='titulo2'>Unidade da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoFuncao) or $DotacaoFuncao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFuncao').focus();\" class='titulo2'>Função da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoSubfuncao) or $DotacaoSubfuncao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoSubfuncao').focus();\" class='titulo2'>Subfunção da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoPrograma) or $DotacaoPrograma == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoPrograma').focus();\" class='titulo2'>Programa da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoTipoProjetoAtividade) or $DotacaoTipoProjetoAtividade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoTipoProjetoAtividade').focus();\" class='titulo2'>Tipo do projeto/Atividade da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoProjetoAtividade) or $DotacaoProjetoAtividade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoProjetoAtividade').focus();\" class='titulo2'>Projeto/Atividade da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoElemento1) or $DotacaoElemento1 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento1').focus();\" class='titulo2'>Elemento 1 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoElemento2) or $DotacaoElemento2 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento2').focus();\" class='titulo2'>Elemento 2 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoElemento3) or $DotacaoElemento3 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento3').focus();\" class='titulo2'>Elemento 3 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoElemento4) or $DotacaoElemento4 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento4').focus();\" class='titulo2'>Elemento 4 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($DotacaoFonte) or $DotacaoFonte == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFonte').focus();\" class='titulo2'>Fonte da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if ($GLOBALS['Mens'] != 1) {
            $BloqueioTodos .= sprintf('%04s', $DotacaoAno);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoOrgao);
            $BloqueioTodos .= sprintf('%02s', $DotacaoUnidade);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoFuncao);
            $BloqueioTodos .= '.' . sprintf('%04s', $DotacaoSubfuncao);
            $BloqueioTodos .= '.' . sprintf('%04s', $DotacaoPrograma);
            $BloqueioTodos .= '.' . sprintf('%01s', $DotacaoTipoProjetoAtividade);
            $BloqueioTodos .= '.' . sprintf('%03s', $DotacaoProjetoAtividade);
            $BloqueioTodos .= '.' . sprintf('%01s', $DotacaoElemento1);
            $BloqueioTodos .= '.' . sprintf('%01s', $DotacaoElemento2);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoElemento3);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoElemento4);
            $BloqueioTodos .= '.' . sprintf('%04s', $DotacaoFonte);
            $BloqueioTodosData = getDadosDotacaoOrcamentaria($dbOracle, $BloqueioTodos);
        }
    } else {
        if (is_null($BloqueioAno) or $BloqueioAno == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioAno').focus();\" class='titulo2'>Ano do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($BloqueioOrgao) or $BloqueioOrgao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioOrgao').focus();\" class='titulo2'>Orgão do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($BloqueioUnidade) or $BloqueioUnidade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioUnidade').focus();\" class='titulo2'>Unidade do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($BloqueioDestinacao) or $BloqueioDestinacao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioDestinacao').focus();\" class='titulo2'>Destinação do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (is_null($BloqueioSequencial) or $BloqueioSequencial == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioSequencial').focus();\" class='titulo2'>Sequencial do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if ($GLOBALS['Mens'] != 1) {
            $BloqueioTodos .= sprintf('%04s', $BloqueioAno);
            $BloqueioTodos .= '.' . sprintf('%02s', $BloqueioOrgao);
            $BloqueioTodos .= '.' . sprintf('%02s', $BloqueioUnidade);
            $BloqueioTodos .= '.' . sprintf('%01s', $BloqueioDestinacao);
            $BloqueioTodos .= '.' . sprintf('%04s', $BloqueioSequencial);
            $BloqueioTodosData = getDadosBloqueio($dbOracle, $BloqueioTodos);

            $valorBloqueio[] = $BloqueioTodosData['valorTotal'];
        }
    }
    $Foco = 'BloqueioAno';

    if ($isDotacao) {
        $Foco = 'DotacaoAno';
    }

    if ($GLOBALS['Mens'] != 1) {
        if (is_null($BloqueioTodosData)) {
            adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>" . $reserva . ' não existe</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
    }

    if ($GLOBALS['Mens'] != 1) {
        if (is_null($Bloqueios)) {
            $Bloqueios = array();
        }
        $isRepetido = false;

        foreach ($Bloqueios as $bloqueio) {
            if ($bloqueio == $BloqueioTodos) {
                $isRepetido = true;
                adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>" . $reserva . ' repetido(a)</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }

        if (! $isRepetido) {
            array_push($Bloqueios, $BloqueioTodos);
        }
    }
} elseif ($Botao == 'RetirarBloqueio') {
    $quantidade = count($Bloqueios);

    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        if ($BloqueiosCheck[$itr]) {
            unset($Bloqueios[$itr]);
        }
    }
    unset($BloqueiosCheck);

    if ($is_dotacao) {
        $Foco = 'DotacaoAno';
    } else {
        $Foco = 'BloqueioAno';
    }
    ;
}

if (! $cargaInicial and $isDotacaoAnterior != $isDotacao) { // era bloqueio e agora é dotação
    unset($Bloqueios);
    unset($BloqueiosCheck);
}

// INÍCIO DA GERAÇÃO DA PÁGINA
$acesso = '';

if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $acesso = 'Incluir';
    $descricao = "Preencha os dados abaixo e clique no botão 'Incluir'. Os itens obrigatórios estão com *. O valor estimado refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
    $acesso = 'Manter';
    $descricao = "Preencha os dados abaixo e clique no botão 'Manter'. Os itens obrigatórios estão com *. O valor estimado refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
    $acesso = 'Acompanhar';
    $descricao = "Para visualizar nova solicitação clique no botão 'Voltar'.";
} elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $acesso = 'Cancelar';
    $descricao = 'Clique no botão Cancelar Solicitação.';
}


if($programa == 'window' || $programa == 'JanelaLicitacaoIncluir.php' ) {
    // KIm
    if($telaAppView){
        $template = new TemplateNovaJanela('../compras/templates/CadSolicitacaoCompraIncluirManterExcluir.template.html', 'Compras > ' . $acesso);
    }else{
        $template = new TemplateNovaJanela('templates/CadSolicitacaoCompraIncluirManterExcluir.template.html', 'Compras > ' . $acesso);
    }

} else {
   $template = new TemplatePaginaPadrao('templates/CadSolicitacaoCompraIncluirManterExcluir.template.html', 'Compras > ' . $acesso);
}
$tela = isset($_GET['tela']) ? '?tela='.$_GET['tela'] : '';

if ($programa == 'CadLicitacaoIncluir.php') {
    $template->NOME_PROGRAMA = 'CadSolicitacaoCompraIncluirManterExcluir.php'.$tela;
} else {

    if ($programa == 'JanelaLicitacaoIncluir.php') {
        $template->NOME_PROGRAMA = 'javascript:window.close()';
    }else{
        $template->NOME_PROGRAMA = $programa . $tela;
    }


}
$template->ACESSO_TITULO = strtoupper2($acesso);
$template->DESCRICAO     = $descricao;

// Kim ============ >valida se vem da tela do app para apenas visualização caso não seja aparece a lupa para pesquisa. <==================
$template->SEEXISTEIRP = "style='display:block'";
if($telaAppView || $acesso = 'Acompanhar'){
   if(empty($IRP) || !empty($anoIrp)){
        $template->SEEXISTEIRP = "style='display:none'"; 
    }
}
// Kim =================================================== > fim <=========================================================================
if (! $ocultarCampoNumeroSCC && ! empty($Solicitacao)) {
    $template->block('BLOCO_NUMERO_SCC');
    $template->NUMERO_SCC = getNumeroSolicitacaoCompra($db, $Solicitacao);
}

if (! $ocultarCampoNumero) {
    $template->SEQUENCIAL_SCC       = $Numero;
    $template->SEQUENCIAL_SCC_VALOR = $Numero;
    $template->block('BLOCO_SEQUENCIAL_SCC');
}

if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $DataSolicitacao = date('d/m/Y');
}
$template->DATA_SCC       = $DataSolicitacao;
$template->DATA_SCC_VALOR = $DataSolicitacao;

$_cusupocodi_ = isset($_SESSION['_cusupocodi_']) ? $_SESSION['_cusupocodi_'] : 0;
$_cgrempcodi_ = isset($_SESSION['_cgrempcodi_']) ? $_SESSION['_cgrempcodi_'] : $DataAtual ;

// ## Centro de custo
// Pegando dados do usuário
$sql = "SELECT  USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI
        FROM    SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS
        WHERE   USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU
                AND USUCEN.FUSUCCTIPO IN ('C')
                AND ((USUCEN.CUSUPOCODI = " . $_cusupocodi_ . ' AND USUCEN.CGREMPCODI = ' . $_cgrempcodi_ . ')
                    OR (USUCEN.CUSUPOCOD1 = ' . $_cusupocodi_ . ' AND USUCEN.CGREMPCOD1 = ' . $_cgrempcodi_ . " AND '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS))
                AND USUCEN.FUSUCCTIPO = 'C'
                AND CENCUS.FCENPOSITU <> 'I'
        GROUP BY USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI ";

$res = executarSQL($db, $sql);
$Rows = $res->numRows();

if ($Rows != 0) {
    $Linha = $res->fetchRow();
    $TipoUsuario = $Linha[0];
    $OrgaoUsuario = $Linha[1];

    if ($TipoUsuario == 'R') {
        $DescUsuario = 'Requisitante';
    } elseif ($TipoUsuario == 'A') {
        $DescUsuario = 'Aprovador';
    } else {
        $DescUsuario = 'Atendimento';
    }
}

if (($_SESSION['_cgrempcodi_'] != 0) and ($TipoUsuario == 'C')) {
    $sqlCC = '  SELECT  A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA,
                        B.CORGLICODI, B.EORGLIDESC, B.FORGLITIPO
                FROM    SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B
                WHERE   A.CORGLICODI IS NOT NULL
                        AND A.ACENPOANOE = ' . date('Y') . '';
    $sqlCC .= '   AND A.CORGLICODI = B.CORGLICODI  ';
    $sqlCC .= "   AND A.FCENPOSITU <> 'I' ";
    $sqlCC .= '   AND A.CCENPOSEQU IN  ';
    $sqlCC .= '        ( SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU ';
    $sqlCC .= '       WHERE USU.CUSUPOCODI = ' . $_SESSION['_cusupocodi_'] . " AND USU.FUSUCCTIPO IN ('C'))";
    $sqlCC .= '       ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.CCENPOCENT, A.CCENPODETA ';
} else {
    $sqlCC = 'SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA,';
    $sqlCC .= '       D.CORGLICODI, D.EORGLIDESC, D.FORGLITIPO';
    $sqlCC .= '  FROM SFPC.TBCENTROCUSTOPORTAL A,  SFPC.TBGRUPOORGAO B, ';
    $sqlCC .= '       SFPC.TBGRUPOEMPRESA C, SFPC.TBORGAOLICITANTE D ';
    $sqlCC .= ' WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ' . date('Y') . '';
    $sqlCC .= '   AND A.CORGLICODI = B.CORGLICODI AND C.CGREMPCODI = B.CGREMPCODI ';
    $sqlCC .= '   AND B.CORGLICODI = D.CORGLICODI ';
    $sqlCC .= "   AND A.FCENPOSITU <> 'I' ";
    if ($TipoUsuario == 'C') {
        $sqlCC .= ' AND C.CGREMPCODI = ' . $_SESSION['_cgrempcodi_'] . '';
    }
    $sqlCC .= ' ORDER BY D.EORGLIDESC,A.CCENPONRPA, A.CCENPOCENT, A.CCENPODETA';
}
$resCC = executarSQL($db, $sqlCC);
$RowsCC = $resCC->numRows();

if ($RowsCC == 0) {
    // Nenhum centro de custo foi encontrado
    $template->block('BLOCO_CENTRO_CUSTO_NENHUM');
    /*
     * // Sempre pegar o centro de custo da SCC no else abaixo.
     * } elseif ($RowsCC == 1) {
     * $Linha = $resCC->fetchRow();
     * $CentroCusto = $Linha[0];
     * $DescCentroCusto = $Linha[1];
     * $RPA = $Linha[2];
     * $Detalhamento = $Linha[3];
     * $Orgao = $Linha[4];
     * $DescOrgao = $Linha[5];
     * $administracao = $Linha[6];
     *
     * # Apenas 1 CC foi encontrado
     * $template->CC_ORGAO = $DescOrgao;
     * $template->CC_RPA = $RPA;
     * $template->CC_DESCRICAO = $DescCentroCusto;
     * $template->CC_DETALHAMENTO = $Detalhamento;
     * $template->block("BLOCO_CENTRO_CUSTO");
     */
} else {
    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    if (! $ocultarCamposEdicao and $acaoPagina != ACAO_PAGINA_MANTER) {
        // Vários CCs existem
        $template->CC_TIPO_USUARIO = $TipoUsuario;
        $template->block('BLOCO_CENTRO_CUSTO_SELECIONAR');
    }

    if ($CentroCusto != '') {
        // Carrega os dados do Centro de Custo selecionado #
        $sql = 'SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA, B.FORGLITIPO, A.FCENPOSITU';
        $sql .= '  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ';
        $sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
        // $sql .= " AND A.FCENPOSITU <> 'I' ";
        $res = executarSQL($db, $sql);

        while ($Linha = $res->fetchRow()) {
            $DescCentroCusto = $Linha[0];
            $DescOrgao       = $Linha[1];
            $Orgao           = $Linha[2];
            $RPA             = $Linha[3];
            $Detalhamento    = $Linha[4];
            $administracao   = $Linha[5];
            $ccSituacao      = $Linha[6];
            if ($ccSituacao == 'I') {
                $Detalhamento .= ' (Centro de custo inativo)';
            }
        }

        // Vários CCs existem mas um já foi selecionado
        $template->CC_ORGAO        = $DescOrgao;
        $template->CC_RPA          = $RPA;
        $template->CC_DESCRICAO    = $DescCentroCusto;
        $template->CC_DETALHAMENTO = $Detalhamento;
        $template->block('BLOCO_CENTRO_CUSTO');

        if($ccSituacao == 'I'){
            $_SESSION['ccSituacao_origem'] = $ccSituacao;
        }

        if($_SESSION['ccSituacao_origem'] == 'I'){
            $template->block('BLOCO_CENTRO_CUSTO_SELECIONAR_INATIVO');
            $template->Orgao_base               = $Orgao;
        }
    }
}
$template->CC               = $CentroCusto;
$template->CC_ADMINISTRACAO = $administracao;

/*
 * [CUSTOMIZAÇÃO] - [CR129149]: REDMINE 92 (Registro de Preço)
 * if ($sequencialIntencao != "" && $anoIntencao != "") {
 * $template->SEQUENCIAL_INTENCAO = substr($sequencialIntencao + 10000, 1);
 * $template->ANO_INTENCAO = $anoIntencao;
 * $template->NUMERO_INTENCAO_REGISTRO_PRECO = substr($sequencialIntencao + 10000, 1) . '/' . $anoIntencao;
 * $template->block("BLOCO_INTENCAO_REGISTRO_PRECO");
 * }
 *
 * if ($TipoCompra == TIPO_COMPRA_LICITACAO && $RegistroPreco == "S") {
 * if ($sequencialIntencao == "" && $anoIntencao == "") {
 * $template->block("BLOCO_INTENCAO_REGISTRO_PRECO_SELECIONAR");
 * }
 *
 * $template->block("BLOCO_SELECT_INTENCAO_REGISTRO_PRECO");
 * }
 *
 * $ocultarBotaoItem = false; // Botão Incluir e Retirar item
 * if ($TipoCompra == TIPO_COMPRA_LICITACAO && $RegistroPreco == "S" && $sequencialIntencao != "" && $anoIntencao != "") {
 * $ocultarBotaoItem = true;
 * }
 * if ($TipoCompra == TIPO_COMPRA_SARP) {
 * $ocultarBotaoItem = true;
 * }
 *
 * // [/CUSTOMIZAÇÃO]
 */
if ($_SESSION['_fperficorp_'] == 'S') {
    if ($sequencialIntencao != "" && $anoIntencao != "") {
        $template->SEQUENCIAL_INTENCAO = substr($sequencialIntencao + 10000, 1);
        $template->ANO_INTENCAO = $anoIntencao;
        $template->NUMERO_INTENCAO_REGISTRO_PRECO = substr($sequencialIntencao + 10000, 1) . '/' . $anoIntencao;
        $template->block("BLOCO_INTENCAO_REGISTRO_PRECO");
    }

    if ($TipoCompra == TIPO_COMPRA_LICITACAO && $RegistroPreco == "S") {
        $template->block("BLOCO_SELECT_INTENCAO_REGISTRO_PRECO");
    }

    $ocultarBotaoItem = false;
    /*if ($TipoCompra == TIPO_COMPRA_SARP) {
        $ocultarBotaoItem = true;
    }*/
 }

// ## Fim Centro de custo

$template->CAMPO_OBJETO     = gerarTextArea('formulario', 'Objeto', $Objeto, "400", $ocultarCamposEdicao);
$template->CAMPO_OBSERVACAO = gerarTextArea('formulario', 'Observacao', $Observacao, "200", $ocultarCamposEdicao);

// tipo de compra
$sql = 'SELECT CTPCOMCODI, ETPCOMNOME FROM SFPC.TBTIPOCOMPRA';

$res = executarSQL($db, $sql);

if (!$ocultarCamposEdicao) {
    while ($linha = $res->fetchRow()) {
        $codTipoCompra               = $linha[0];
        $nomeTipoCompra              = $linha[1];
        $template->TIPO_COMPRA       = $nomeTipoCompra;
        $template->TIPO_COMPRA_VALOR = $codTipoCompra;

        if($programa == 'CadSolicitacaoCompraIncluir.php'){
           if($codTipoCompra != 1){ 
                if ($TipoCompra == $codTipoCompra) {
                    $template->TIPO_COMPRA_SELECTED = 'selected';
                } else {
                    $template->TIPO_COMPRA_SELECTED = '';
                }
                $template->block('BLOCO_TIPO_COMPRA_ITEM');
            }
        }else{

            if ($TipoCompra == $codTipoCompra) {
                $template->TIPO_COMPRA_SELECTED = 'selected';
            } else {
                $template->TIPO_COMPRA_SELECTED = '';
            }
            $template->block('BLOCO_TIPO_COMPRA_ITEM');
        }
    }
    $template->block('BLOCO_TIPO_COMPRA');
} else {
    while ($linha = $res->fetchRow()) {
        $codTipoCompra  = $linha[0];
        $nomeTipoCompra = $linha[1];

        if ($TipoCompra == $codTipoCompra) {
            $template->TIPO_COMPRA = $nomeTipoCompra;
            $template->VALOR_TIPO_COMPRA = $codTipoCompra;
            $template->block('BLOCO_TIPO_COMPRA_VISUALIZAR');
        }
    }
}

if ($TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {


    if (!empty($DisputaValor) AND $acaoPagina == ACAO_PAGINA_ACOMPANHAR){
        if ($DisputaValor == 'S') {
            $texto = 'SIM';
        } else {
            $texto = 'NÃO';
        }
        if($Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){
            $template->CAMPO_DISPUTA = "<td class='textonormal'> $texto </td>" ;
            $template->block("BLOCO_DISPUTA_ACOMPANHAR"); 
        }
        $template->CAMPO_PUBLICAR = "<td class='textonormal'> $texto </td>" ;
        $template->block("BLOCO_PUBLICAR_ACOMPANHAR");
    } else if (empty($DisputaValor) AND $acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
        $texto = '-';
        // if($Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){

        //     $template->CAMPO_DISPUTA = "<td class='textonormal'> $texto </td>" ;
        //     $template->block("BLOCO_DISPUTA_ACOMPANHAR"); 
            
        // }
        
        $template->CAMPO_PUBLICAR = "<td class='textonormal'> $texto </td>" ;
        $template->block("BLOCO_PUBLICAR_ACOMPANHAR");

    } else {
        // if ($Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){
        //     $template->CAMPO_DISPUTA = gerarRadioButtons('campoDisputa', array('SIM', 'NAO'), array('S', 'N') ,$DisputaValor, false, false,);
        //     $template->block("BLOCO_DISPUTA");
        // }
    
        $template->CAMPO_PUBLICAR = gerarRadioButtons('campoPublicar', array('SIM', 'NÃO'), array('S', 'N') ,$PublicaValor, false, false,);
        $template->block("BLOCO_PUBLICAR");
    }

    $compromissoDefault  = 'N';

    if(!empty($campoCompromissoManter)){
        $compromissoDefault = $campoCompromissoManter;
    }

    if(!empty($CompromissoValor)){
        $compromissoDefault = $CompromissoValor;
    }
    $template->CAMPO_COMPROMISSO = gerarRadioButtons('campoCompromisso', array('SIM', 'NÃO'), array('S', 'N') ,$compromissoDefault, false, $ocultarCamposEdicao);
    $template->block("BLOCO_COMPROMISSO");
}

if($TipoCompra == TIPO_COMPRA_DISPENSA){

    if (!empty($DisputaValor) AND $acaoPagina == ACAO_PAGINA_ACOMPANHAR){
        if ($DisputaValor == 'S') {
            $texto = 'SIM';
            $template->ACOMPANHAR_DATA_INICIO_PROPOSTA = $DataInicioProposta;
            $template->ACOMPANHAR_DATA_FIM_PROPOSTA = $DataFimProposta;
            $template->block("BLOCO_ACOMPANHAR_DATA_INICIO_PROPOSTA");
            $template->block("BLOCO_ACOMPANHAR_DATA_FIM_PROPOSTA");
        } else {
            $texto = 'NÃO';
        }
        if($Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){

            $template->CAMPO_DISPUTA = "<td class='textonormal'> $texto </td>" ;
            $template->block("BLOCO_DISPUTA_ACOMPANHAR"); 
    
        }
        $template->CAMPO_PUBLICAR = "<td class='textonormal'> $texto </td>" ;
        $template->block("BLOCO_PUBLICAR_ACOMPANHAR"); 
        

    } else if (empty($DisputaValor) AND $acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
        $texto = '-';
        if($TipoCompra == TIPO_COMPRA_DISPENSA && $Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){

            $template->CAMPO_DISPUTA = "<td class='textonormal'> $texto </td>" ;
            $template->block("BLOCO_DISPUTA_ACOMPANHAR"); 
            
        }

        $template->CAMPO_PUBLICAR = "<td class='textonormal'> $texto </td>" ;
        $template->block("BLOCO_PUBLICAR_ACOMPANHAR");

    } else {

        if ($TipoCompra == TIPO_COMPRA_DISPENSA && $Lei == 14133 && $Artigo == 75 && ($Inciso == 69 || $Inciso == 70)){
            $template->CAMPO_DISPUTA = gerarRadioButtons('campoDisputa', array('SIM', 'NÃO'), array('S', 'N') ,$DisputaValor, false, false,'submit()');
            $template->block("BLOCO_DISPUTA");
        }
    
        $template->CAMPO_PUBLICAR = gerarRadioButtons('campoPublicar', array('SIM', 'NÃO'), array('S', 'N') ,$PublicaValor, false, false,);
        $template->block("BLOCO_PUBLICAR");

        if($DisputaValor=="S"){
            $template->DATA_INICIO_PROPOSTA = $DataInicioProposta;
            $template->DATA_FIM_PROPOSTA = $DataFimProposta;
            $template->block("BLOCO_DATA_INICIO_PROPOSTA");
            $template->block("BLOCO_DATA_FIM_PROPOSTA");
        }
          
    }     
}

if (!$ocultarCampoRegistroPreco) {
    $template->CAMPO_REGISTRO_PRECO = gerarRadioButtons('RegistroPreco', array('SIM', 'NÃO'), array('S','N'), $RegistroPreco, false, $ocultarCamposEdicao, 'submit()');
    $template->block('BLOCO_REGISTRO_PRECO');
}

if (! $ocultarCampoSARP) {
    // Se o tipo da ata for externa, automaticamente a SARP será do tipo carona, e não será possível edita-lá
    if ($tipoAta == 'E') {
        $SarpTipo = 'C';
        $habilitado = 'disabled';
    }

    //$template->TIPOATASARP = $tipoAta;
    //$template->TIPOSARP = $SarpTipo;

    $acaoOnChange = "$( 'form' ).submit();";

    // $template->CAMPO_TIPO_ATA = gerarRadioButtons('tipoAta', array('INTERNA', 'EXTERNA'), array('I', 'E'), $tipoAta, false, $ocultarCamposEdicao, $acaoOnChange);
    $template->CAMPO_SARP = gerarRadioButtons('Sarp', array('CARONA', 'PARTICIPANTE'), array('C', 'P'), $SarpTipo, false, $ocultarCamposEdicao, null, $habilitado);
    $template->block('BLOCO_SARP');
}

// inicio processo licitatorio SARP
if (! $ocultarCampoProcessoLicitatorio) {
    if (! $ocultarCamposEdicao) {
        $template->block('BLOCO_LICITACAO_SELECIONAR');
    }

    if ($CarregaProcessoSARP == 1) {
        $sql = "SELECT  DISTINCT A.CLICPOPROC, A.ALICPOANOP, D.ECOMLIDESC, B.EORGLIDESC
                FROM    SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBCOMISSAOLICITACAO D
                WHERE   A.CORGLICODI = B.CORGLICODI
                        AND A.FLICPOSTAT = 'A'
                        AND A.CCOMLICODI = D.CCOMLICODI ";

        if ($NumProcessoSARP != '') {
            $sql .= " AND A.CLICPOPROC = $NumProcessoSARP ";
        }

        if ($AnoProcessoSARP != '') {
            $sql .= " AND A.ALICPOANOP = $AnoProcessoSARP ";
        }

        if ($ComissaoCodigoSARP != '') {
            $sql .= " AND A.CCOMLICODI = $ComissaoCodigoSARP ";
        }

        if ($OrgaoLicitanteCodigoSARP != '') {
            $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigoSARP ";
        }

        if ($GrupoEmpresaCodigoSARP != '') {
            $sql .= " AND A.CGREMPCODI = $GrupoEmpresaCodigoSARP ";
        }
        
        $res                         = executarTransacao($db, $sql);
        $Rows                        = $res->numRows();
        $Linha                       = $res->fetchRow();
        $ProcessoAnoSARP             = $Linha[0] . '/' . $Linha[1];
        $ComissaoDescricaoSARP       = $Linha[2];
        $OrgaoLicitanteDescricaoSARP = $Linha[3];

        if ($Rows == 1) {
            $template->SARP_LICITACAO_ANO = $ProcessoAnoSARP;
            $template->SARP_LICITACAO_COMISSAO = $ComissaoDescricaoSARP;
            $template->SARP_LICITACAO_ORGAO = $OrgaoLicitanteDescricaoSARP;
            $template->block('BLOCO_LICITACAO_VISUALIZAR');
        }
    } else {}
    $template->SARP_LICITACAO_PROCESSO = $NumProcessoSARP;
    $template->SARP_LICITACAO_ANO_VALOR = $AnoProcessoSARP;
    $template->SARP_LICITACAO_COMISSAO_VALOR = $ComissaoCodigoSARP;
    $template->SARP_LICITACAO_ORGAO_VALOR = $OrgaoLicitanteCodigoSARP;
    $template->SARP_LICITACAO_EMPRESA = $GrupoEmpresaCodigoSARP;
    $template->SARP_LICITACAO_CARREGA = $CarregaProcessoSARP;
    $template->block('BLOCO_LICITACAO');
    
}
// fim processo licitatorio SARP
if (! $ocultarCampoGeraContrato or $preencherCampoGeraContrato) {
    $template->CAMPO_CONTRATO = gerarRadioButtons('GeraContrato', array('SIM', 'NÃO'), array('S', 'N'), $GeraContrato, false, $ocultarCamposEdicao or $preencherCampoGeraContrato);
    $template->block('BLOCO_CONTRATO');
}

if (! $ocultarCamposEdicao) {
    if (! $ocultarCampoFornecedor) {
        if($CnpjFornecedor == NULL){
            $CnpjStr = FormataCpfCnpj($materiais[$itr]['fornecedor']);
        }else{
            $CnpjStr = FormataCpfCnpj($CnpjFornecedor);
        }
        
        $template->FORNECEDOR_CNPJ = $CnpjStr;

        if (! is_null($CnpjFornecedor)) {
            $CPFCNPJ = removeSimbolos($CnpjFornecedor);
            $materialServicoFornecido = null;
            $TipoMaterialServico = null;
            $resposta = checaSituacaoFornecedor($db, $CPFCNPJ);

            if (! is_null($resposta) and ! is_null($resposta['razao']) and $resposta['razao'] != '') {
                if ($resposta['situacao'] == 1) {
                    $template->FORNECEDOR = $resposta['razao'];
                } else {
                    $template->FORNECEDOR = $resposta['razao'] . " - <font color='#ff0000'>FORNECEDOR COM SANÇÕES NO SICREF</font>";
                }
            }
        }

        $template->block('BLOCO_FORNECEDOR');
    }
}
$template->READ_ONLY = $ifVisualizacaoThenReadOnly;

ob_start(); // pegando o html ainda não tratado pelo template, para depois jogar no template

if (! $ocultarCampoLegislacao) {
?>
    <tr>
        <td class="textonormal" bgcolor="#DCEDF7">Legislação*</td>
        <td class="textonormal">
            <?php   $sql = 'SELECT CTPLEITIPO, ETPLEITIPO FROM SFPC.TBTIPOLEIPORTAL';

                    $res = executarTransacao($db, $sql);

                    if (! $ocultarCamposEdicao) {
            ?>
            Tipo de lei:
            <select name="TipoLei" size="1" <?php echo  $ifVisualizacaoThenReadOnly?> class="textonormal" onChange="atualizar('TipoLei')" >
                <option value="">Selecionar Tipo de Lei</option>
                <?php   while ($Linha = $res->fetchRow()) {
                            $tipoLeiItem = $Linha[0];
                            $tipoLeiDesc = $Linha[1];
                ?>
                <option value="<?php echo  $tipoLeiItem ?>" <?php if ($tipoLeiItem == $TipoLei) { echo 'selected'; } ?>><?php echo  $tipoLeiDesc ?></option>
                <?php   } ?>
            </select>
            <?php   } else {
                        while ($Linha = $res->fetchRow()) {
                            $tipoLeiItem = $Linha[0];
                            $tipoLeiDesc = $Linha[1];

                            if ($tipoLeiItem == $TipoLei) {
                                echo 'Tipo de lei: ' . $tipoLeiDesc . '  ';
                            }
                        }
                    }
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;

            <?php   if (! is_null($TipoLei) and $TipoLei != '') {
                        $sql = 'SELECT CLEIPONUME FROM SFPC.TBLEIPORTAL WHERE CTPLEITIPO = ' . $TipoLei;

                        $res = executarTransacao($db, $sql);
                    }

                    if (! $ocultarCamposEdicao) {
            ?>
            Lei:
            <select name="Lei" size="1" <?php echo  $ifVisualizacaoThenReadOnly?> class="textonormal" onChange="atualizar('Lei')">
                <option value="">Selecionar Lei</option>
                <?php   if (! is_null($TipoLei) and $TipoLei != '') {
                            while ($Linha = $res->fetchRow()) {
                                $leiItem = $Linha[0];
                ?>
                <option value="<?php echo  $leiItem ?>" <?php if ($leiItem == $Lei) { echo 'selected'; } ?>><?php echo  $leiItem ?></option>
                <?php       }
                        }
                ?>
            </select>
            <?php   } else {
                        while ($Linha = $res->fetchRow()) {
                            $leiItem = $Linha[0];

                            if ($leiItem == $Lei) {
                                echo 'Lei: ' . $leiItem . ' ';
                            }
                        }
                    }
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;

            <?php   if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '') {
                        $sql = 'SELECT CARTPOARTI FROM SFPC.TBARTIGOPORTAL WHERE CTPLEITIPO= ' . $TipoLei . ' AND CLEIPONUME = ' . $Lei . ' ';

                        $res = executarTransacao($db, $sql);
                    }

                    if (! $ocultarCamposEdicao) {
            ?>
            Artigo:
            <select name="Artigo" size="1" <?php echo  $ifVisualizacaoThenReadOnly?> class="textonormal" onChange="atualizar('Artigo')">
                <option value="">Selecionar Artigo</option>
                <?php   if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '') {
                            while ($Linha = $res->fetchRow()) {
                                $artigoItem = $Linha[0];
                                // $artigoNumero = $Linha[1];
                ?>
                <option value="<?php echo  $artigoItem ?>" <?php if ($artigoItem == $Artigo) { echo 'selected'; } ?>><?php echo  $artigoItem ?></option>
                <?php       }
                        }
                ?>
            </select>
            <?php   } else {
                        while ($Linha = $res->fetchRow()) {
                            $artigoItem = $Linha[0];
                            $artigoNumero = $Linha[1];

                            if ($artigoItem == $Artigo) {
                                echo 'Artigo ' . $artigoItem . '  ';
                            }
                        }
                    }
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;

            <?php   if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '' and ! is_null($Artigo) and $Artigo != '') {
                        $sql = 'SELECT  CINCPAINCI, NINCPANUME
                                FROM    SFPC.TBINCISOPARAGRAFOPORTAL
                                WHERE   CTPLEITIPO = ' . $TipoLei . '
                                        AND CLEIPONUME = ' . $Lei . '
                                        AND CARTPOARTI = ' . $Artigo . ' ';

                        $res = executarTransacao($db, $sql);
                    }

                    if (! $ocultarCamposEdicao) {
            ?>
            Inciso/Parágrafo:
            <select name="Inciso" size="1" onChange="atualizar('Inciso')" <?php echo  $ifVisualizacaoThenReadOnly?>  class="textonormal">
                <option value="">Selecionar Inciso ou Parágrafo</option>
                <?php   if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '' and ! is_null($Artigo) and $Artigo != '') {
                            while ($Linha = $res->fetchRow()) {
                                $incisoItem = $Linha[0];
                                $incisoNumero = $Linha[1];
                ?>
                <option value="<?php echo  $incisoItem ?>" <?php if ($incisoItem == $Inciso) { echo 'selected'; } ?>><?php echo  $incisoNumero ?></option>
                <?php       }
                        }
                ?>
            </select>
            <?php   } else {
                        while ($Linha = $res->fetchRow()) {
                            $incisoItem = $Linha[1];

                            if ($Inciso == $Linha[0]) {
                                echo 'Inciso/Parágrafo: ' .$Linha[1];
                            }
                        }
                    }

            ?>
        </td>
    </tr>
<?php
}
$Legislacao = ob_get_contents();
$template->LEGISLACAO = $Legislacao;
ob_clean();

ob_start();

if (! $ocultarCampoJustificativa) {
?>
    <tr>
        <td class="textonormal" bgcolor="#DCEDF7">Justificativa</td>
        <?php   if (! $ocultarCamposEdicao) { ?>
            <td class="textonormal">
                <font class="textonormal">máximo de 200 caracteres</font>
                <input type="text" <?php echo  $ifVisualizacaoThenReadOnly?> name="NCaracteresJustificativa" size="3" disabled <?php echo  $ifVisualizacaoThenReadOnly?> value="<?php echo $NCaracteresJustificativa ?>" class="textonormal">
                <br>
                <textarea name="Justificativa" cols="50" maxlength="200" <?php echo  $ifVisualizacaoThenReadOnly?> rows="4" OnKeyUp="javascript:CaracteresJustificativa(1)" OnBlur="javascript:CaracteresJustificativa(0)" OnSelect="javascript:CaracteresJustificativa(1)" class="textonormal" style="text-transform: uppercase;"><?php echo trim($Justificativa);?></textarea>
            </td>
        <?php   } else { ?>
            <td class="textonormal">
                <?php echo trim($Justificativa); ?>
            </td>
        <?php   } ?>
    </tr>
    <?php if (!empty($IntencaoAno) && !empty($IntencaoSequ)) { ?>
    <tr>
        <td class="textonormal" bgcolor="#DCEDF7">Intenção de Registro de Preço</td>
            <td class="textonormal">
                <a href="../registropreco/CadRegistroPrecoIntencaoVisualizarConsolidacao.php?Botao=ImprimirCompleto&numero=<?php echo str_pad($IntencaoSequ, 4, '0', STR_PAD_LEFT) . '/' . $IntencaoAno; ?>" target="_blank">
                        <?php echo str_pad($IntencaoSequ, 4, '0', STR_PAD_LEFT) . '/' . $IntencaoAno; ?>
                </a>
            </td>
    </tr>
    <?php   } ?>
<?php
}

if (! $ocultarCampoDataDOM) {
?>
    <tr>
        <td class="textonormal" bgcolor="#DCEDF7">Data da publicação no DOM</td>
        <td class="textonormal">
            <?php   if (! $ocultarCamposEdicao) { ?>
                <input name="DataDom" <?php echo  $ifVisualizacaoThenReadOnly?> id="DataDom" class="data" size="10" maxlength="10" value="<?php echo  $DataDom ?>" type="text">
                <a href="javascript:janela('../calendario.php?Formulario=CadSolicitacaoCompraIncluirManterExcluir&amp;Campo=DataDom','Calendario',220,170,1,0)">
                    <img src="../midia/calendario.gif" alt="" border="0">
                </a>
            <?php } else { ?>
                <?php echo  $DataDom?>
            <?php } ?>
        </td>
    </tr>
<?php
}

// Bloqueio - início
?>
<tr>
    <td class="textonormal" colspan="4">
        <input type="hidden" name="TipoReservaOrcamentaria" id="TipoReservaOrcamentaria" value="<?php echo  $TipoReservaOrcamentaria ?>"/>
        <table id="scc_bloqueios" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
            <tbody>
                <tr>
                    <td class="titulo3" align="center" bgcolor="#75ADE6" valign="middle">
                        <span id="BloqueioTitulo" colspan=2>BLOQUEIO OU DOTAÇÃO ORÇAMENTÁRIA</span>
                    </td>
                </tr>
                <?php
                $cntBloqueio = - 1;

                if (! is_null($Bloqueios)) {
                    foreach ($Bloqueios as $key => $bloqueioItem) {
                        if (isset($bloqueioItem)) {
                            ++ $cntBloqueio;
                ?>
                <tr>
                    <td class="textonormal">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                            <input name="BloqueiosCheck[<?php echo $cntBloqueio;?>]" type="checkbox" <?php if ($BloqueiosCheck[$cntBloqueio]) { echo 'checked'; } ?>/>
                        <?php   } ?>
                        <?php   echo $bloqueioItem; echo ' - R$ '; echo converte_valor_estoques($valorBloqueio[$key]); ?>
                        <input type="hidden" name="ValorBloqueio[]" id="ValorBloqueio" value="<?php echo  $valorBloqueio[$key]; ?>"/>
                        <input name="Bloqueios[<?php echo  $cntBloqueio ?>]" value="<?php echo  $bloqueioItem ?>" type="hidden"/>
                    </td>
                </tr>
                <?php           }
                            }
                        }
                ?>
                <?php if (! $ocultarCamposEdicao) { ?>
                <tr>
                    <td class="textonormal" colspan=2 bgcolor="#ffffff">
                        <table class="textonormal" border="0" align="left" width="100%" summary="">
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7" width="200px">Novo <span id="BloqueioLabel"><?php echo  $reserva ?></span>:</td>
                                <td class="textonormal">
                                    <?php   if ($isDotacao) { ?>
                                        Ano:
                                        <input name="DotacaoAno" id="DotacaoAno" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoAno ?>"/>
                                        Órgão:
                                        <input name="DotacaoOrgao" id="DotacaoOrgao" size="2" maxlength="2" value=""type="text"value="<?php echo  $DotacaoAno ?>"/>
                                        Unidade:
                                        <input name="DotacaoUnidade" id="DotacaoUnidade" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoAno ?>"/>
                                        Funcao:
                                        <input name="DotacaoFuncao" id="DotacaoFuncao" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoFuncao ?>"/>
                                        SubFunção:
                                        <input name="DotacaoSubfuncao" id="DotacaoSubfuncao" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoSubfuncao ?>"/>
                                        Programa:
                                        <input name="DotacaoPrograma" id="DotacaoPrograma" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoSubfuncao ?>"/>
                                        Tipo Projeto/Atividade:
                                        <input name="DotacaoTipoProjetoAtividade" id="DotacaoTipoProjetoAtividade" size="1" maxlength="1" value="" type="text" value="<?php echo  $DotacaoTipoProjetoAtividade ?>"/>
                                        Projeto/Atividade:
                                        <input name="DotacaoProjetoAtividade" id="DotacaoProjetoAtividade" size="3" maxlength="3" value="" type="text" value="<?php echo  $DotacaoProjetoAtividade ?>"/>
                                        Elemento1:
                                        <input name="DotacaoElemento1" id="DotacaoElemento1" size="1" maxlength="1" value="" type="text" value="<?php echo  $DotacaoElemento1 ?>"/>
                                        Elemento2:
                                        <input name="DotacaoElemento2" id="DotacaoElemento2" size="1" maxlength="1" value="" type="text" value="<?php echo  $DotacaoElemento2 ?>"/>
                                        Elemento3:
                                        <input name="DotacaoElemento3" id="DotacaoElemento3" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoElemento3 ?>"/>
                                        Elemento4:
                                        <input name="DotacaoElemento4" id="DotacaoElemento4" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoElemento4 ?>"/>
                                        Fonte:
                                        <input name="DotacaoFonte" id="DotacaoFonte" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoFonte ?>"/>
                                    <?php   } else { ?>
                                        Ano:
                                        <input name="BloqueioAno" id="BloqueioAno" size="4" maxlength="4" value="" type="text" value="<?php echo  $BloqueioAno ?>"/>
                                        Órgão:
                                        <input name="BloqueioOrgao" id="BloqueioOrgao" size="2" maxlength="2" value="" type="text" value="<?php echo  $BloqueioOrgao ?>"/>
                                        Unidade:
                                        <input name="BloqueioUnidade" id="BloqueioUnidade" size="2" maxlength="2" value="" type="text" value="<?php echo  $BloqueioUnidade ?>"/>
                                        Destinação:
                                        <input name="BloqueioDestinacao" id="BloqueioDestinacao" size="1" maxlength="1" value="" type="text" value="<?php echo  $BloqueioDestinacao ?>"/>
                                        Sequencial:
                                        <input name="BloqueioSequencial" id="BloqueioSequencial" size="4" maxlength="4" value="" type="text"value="<?php echo  $BloqueioSequencial ?>"/>
                                    <?php   } ?>
                                    <?php
                                    /*
                                     * <input name="BloqueioTodos" id="BloqueioTodos" class="bloqueioDotacao" size="40" maxlength="36" value="" type="text" value="<?php echo $BloqueioTodos?>"/>
                                     * <a href="javascript:AbreJanela('InfPreenchimentoBloqueios.php',700,370);" id='CentroCustoLink'><img src="../midia/icone_interrogacao.gif" border="0"></a>
                                     */
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="textonormal" align="center">
                        <input name="BotaoIncluirBloqueioTodos" value="Incluir <?php echo  $reserva ?>" class="botao" type="button" onClick="incluirBloqueio()"/>
                        <input name="BotaoRemoverBloqueioTodos" value="Remover <?php echo  $reserva ?>" class="botao" type="button" onClick="retirarBloqueio()"/>
                    </td>
                </tr>
                <?php   } ?>
            </tbody>
        </table>
        <!-- Itens - início -->
        <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
            <tbody>
                <tr>
                    <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DA SOLICITAÇÃO DE MATERIAL</td>
                </tr>
                <tr class="head_principal">
                    <?php   $descricaoWidth = '300px';

                            // redimensionando dependendo do número de campos
                            if ($TipoCompra == TIPO_COMPRA_LICITACAO and ($RegistroPreco == 'S' or is_null($RegistroPreco)) or is_null($TipoCompra)) {
                                $descricaoWidth = '700px';
                            }
                            $qtdeColunas    = 12;
                            $colunasOcultas = 0;

                            if ($ocultarCampoTRP) {
                                ++ $colunasOcultas;
                            }

                            if ($ocultarCampoExercicio) {
                                $colunasOcultas += 3;
                            }

                            if ($ocultarCampoFornecedor) {
                                $colunasOcultas += 3;
                            }

                            if ($TipoCompra == TIPO_COMPRA_LICITACAO) {
                                $colunasOcultas -= 3;
                            }
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="<?php echo  $descricaoWidth ?>"/>
                        <br />
                        DESCRIÇÃO DO MATERIAL
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        CÓD.RED. CADUM
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        UND
                    </td>
                    <?php
                            /**
                             * Exibir TD na layout?
                             *
                             * @var bool
                             */
                            $exibirTd = false;

                            // Se material tiver indicador cadum (genérico) ou tiver descrição detalhada preenchida (diferente de vazio ou null)
                            if (is_array($materiais)) {
                                foreach ($materiais as $key) {
                                    if ((hasIndicadorCADUM($db, (int) $key['codigo']) === true)) {
                                        $exibirTd = true;
                                        break;
                                    }
                                }
                            }

                            if ($exibirTd) {
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        DESCRIÇÃO DETALHADA
                    </td>
                    <?php   } else {
                                $colunasOcultas += 1;
                            }

                    ?>
                    <?php
                    // kim cr#227641 & cr#228067  CR#228616
                    if(empty($telaAppView)){
                        if (! $ocultarCampoTRP) {
                    ?>

                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="10px"/>
                        <br />
                        VALOR TRP
                    </td>
                    <?php   } ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        QUANTIDADE
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        VALOR ESTIMADO
                    </td>
                    <?php }?>
                    <?php   if (! $ocultarCampoFornecedor) { ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="300px"/>
                        <br />
                        CPF/CNPJ DO FORNECEDOR
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="50px"/>
                        <br />
                        MARCA
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="10px"/>
                        <br />
                        MODELO
                    </td>
                    <?php   } ?>
                    <?php
                    // kim cr#227641 & cr#228067
                    if(empty($telaAppView)){
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        VALOR TOTAL
                    </td>
                    <?php   } ?>
                </tr>
                <?php   // Materiais do POST
                        $QuantidadeMateriais = count($materiais);
                        $QuantidadeServicos  = count($servicos);
                        $ValorTotalItem      = 0;
                        $ValorTotal          = 0;

                        for ($itr = 0; $itr < $QuantidadeMateriais; ++ $itr) {
                            $ValorTotalItem = moeda2float($materiais[$itr]['quantidade']) * moeda2float($materiais[$itr]['valorEstimado']);
                            $ValorTotal += $ValorTotalItem;

                            if (! $ocultarCampoExercicio) {
                                $ValorTotalExercicio   = $materiais[$itr]['totalExercicio'];
                                $TotalDemaisExercicios = $ValorTotalItem - moeda2float($ValorTotalExercicio);

                                if ($TotalDemaisExercicios < 0) {
                                    $TotalDemaisExercicios = 0;
                                }
                            }
                            ?>
                            <!-- Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                            <tr>
                                <!--  Coluna 1 = Codido-->
                                <td class="textonormal" align="center" style="text-align: center">
                                    <?php echo ($itr + 1);?>
                                </td>
                                <!--  Coluna 2  = Descricao -->
                                <td class="textonormal">
                                    <?php if (! $ocultarCamposEdicao) { ?>
                                        <input name="MaterialCheck[<?php echo  $itr ?>]" <?php echo ($materiais[$itr]['check']) ? 'checked' : '';?> <?php echo  $ifVisualizacaoThenReadOnly?> type="checkbox"/>
                                    <?php } ?>
                                        <!-- Kim 227641 -->
                                        <?php if(isset($telaAppView) && $telaAppView == true){ ?>
                                            <font color="#000000"><?php echo $materiais[$itr]['descricao']; ?></font>
                                        <?php }else{ ?>
                                            <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo  $materiais[$itr]['codigo'] ?>&amp;TipoGrupo=M&amp;ProgramaOrigem=<?php echo  $programa ?>',700,370);">
                                                <font color="#000000"><?php echo  $materiais[$itr]['descricao'] ?></font>
                                            </a>
                                        <?php } ?>
                                </td>
                                <!--  Coluna 3 = Cod CADUM-->
                                <td class="textonormal" style="text-align: center !important;">
                                    <?php echo  $materiais[$itr]['codigo']?>
                                    <input value="<?php echo  $materiais[$itr]['codigo'] ?>" name="MaterialCod[<?php echo  $itr ?>]" type="hidden"/>
                                    <input value="<?php echo ($materiais[$itr]['isObras']) ? 'true' : 'false';?>" name="MaterialIsObras[<?php echo  $itr ?>]" id="MaterialIsObras_<?php echo  $itr ?>" type="hidden"/>
                                </td>
                                <!--  Coluna 4 = UND-->
                                <td class="textonormal" align="center">
                                    <?php echo  $materiais[$itr]['unidade']?>
                                </td>
                                <!--  Coluna 5 = DESCRIÇÃO DETALHADA-->
                                <?php   if ($exibirTd) { ?>
                                    <td class="textonormal" align="center">
                                        <?php   if (hasIndicadorCADUM($db, $materiais[$itr]['codigo'])) {
                                                    $disabled = '';

                                                    if (! $ocultarCamposEdicao) {
                                        ?>
                                        <textarea style="text-transform: uppercase;" <?php echo  $ifVisualizacaoThenReadOnly?> name="MaterialDescricaoDetalhada[<?php echo  $itr ?>]" id="MaterialDescricaoDetalhada_<?php echo  $itr ?>" cols="50" rows="4" class="textonormal"><?php echo  $materiais[$itr]['descricaoDetalhada'] ?></textarea>
                                        <?php       } else {
                                                        echo $materiais[$itr]['descricaoDetalhada'];
                                                    }
                                                } else {
                                                    echo '<nobr>---</nobr>';
                                                    echo "<input name='MaterialDescricaoDetalhada[" . $itr . "]' id='MaterialDescricaoDetalhada[" . $itr . "]' value='' type='hidden'   />";
                                                }
                                        ?>
                                    </td>
                                <?php   } ?>
                                <!--  Coluna 6 = VALOR TRP-->
                                <?php
                                    // kim cr#227641 & cr#228067
                                    if(empty($telaAppView)){
                                    if (! $ocultarCampoTRP) { ?>
                                    <td class="textonormal" align="center">
                                        <?php   if (! is_null($materiais[$itr]['trp'])) {
                                                    $material                        = $materiais[$itr]['codigo'];
                                                    $dataMinimaValidaTrp             = prazoValidadeTrp($db, $TipoCompra)->format('Y-m-d');
                                                    $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');
                                                    $exibirMediaTRP                  = $materiais[$itr]['trp'] != null;

                                                    $sqlNaoGravarNaTRP = "  SELECT  FMATEPNTRP
                                                                            FROM    SFPC.TBMATERIALPORTAL MAT
                                                                            WHERE   MAT.CMATEPSEQU = ".$materiais[$itr]['codigo']."
                                                                            LIMIT 1 ";

                                                    $naoGravarNaTRP = resultValorUnico(executarTransacao($db, $sqlNaoGravarNaTRP));

                                                    if ($exibirMediaTRP && $naoGravarNaTRP != 'S') {
                                                        if ($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
                                                            $Url = 'RelTRPConsultarDireta.php?Material=' . $materiais[$itr]['codigo'];
                                                        } else {
                                                            $Url = 'RelTRPConsultar.php?Material=' . $materiais[$itr]['codigo'];
                                                        }
                                                            echo "<a href='javascript:AbreJanela(\"" . $Url . "\",800,500);'>" . $materiais[$itr]['trp'] . '</a>';
                                                            echo "<input name='MaterialTrp[" . $itr . "]' id='MaterialTrp[" . $itr . "]' value='" . $materiais[$itr]['trp'] . "' type='hidden'   />";
                                                    } else {
                                                        echo '<nobr>---</nobr>';
                                                    }
                                                } else {
                                                    echo '<nobr>---</nobr>';
                                                    echo "<input name='MaterialTrp[" . $itr . "]' id='MaterialTrp[" . $itr . "]' value='' type='hidden'   />";
                                                }
                                        ?>
                                    </td>
                                <?php }
                                    }
                                ?>
                                <!--  Coluna 7 =  Quantidade -->
                                <?php
                                     //    kim cr#228067 CR#228616
                                    // if (! $ocultarCampoTRP) {
                                     if(empty($telaAppView)){
                                ?>
                                <td class="textonormal" align="center" width="10">
                                    <?php   if (!$ocultarCamposEdicao) { ?>
                                        <input name="MaterialQuantidade[<?php echo  $itr ?>]" class="dinheiro4casas" value="<?php echo  $materiais[$itr]['quantidade'] ?>" <?php echo  $ifVisualizacaoThenReadOnly?> maxlength="16" size="15" id="MaterialQuantidade[<?php echo  $itr ?>]" type="text" onKeyUp="onChangeItemQuantidade('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL); "/>
                                    <?php   } else {

                                                    echo $materiais[$itr]['quantidade'];

                                            }
                                    ?>
                                </td>
                                <?php  }
                                    // }
                                ?>
                                <!--  Coluna 8 =  Valor Estimado -->
                                <?php
                                    // kim cr#227641 & cr#228067 CR#228616
                                    // if (! $ocultarCampoTRP) {
                                    if(empty($telaAppView)){
                                ?>
                                <td class="textonormal" align="center" width="10">
                                    <?php   if (!$ocultarCamposEdicao) { ?>
                                        <input name="MaterialValorEstimado[<?php echo  $itr ?>]" id="MaterialValorEstimado[<?php echo  $itr ?>]" <?php echo  $ifVisualizacaoThenReadOnly?> size="16" maxlength="16" value="<?php echo  $materiais[$itr]['valorEstimado'] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemValor('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL); " onBlur=" onChangeValorEstimadoItem('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL)"/>
                                    <?php   } else {
                                                echo $materiais[$itr]['valorEstimado'];

                                            }
                                    ?>
                                </td>
                                <?PHP }
                                    // }
                                ?>
                                <?php   if (! $ocultarCampoExercicio) {
                                            // condicoes em que campos são desabilitados
                                            if ($ifVisualizacaoThenReadOnly) {
                                                $ifVisualizacaoQtdeExercicioThenReadOnly         = 'disabled';
                                                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                            } elseif (moeda2float($materiais[$itr]['quantidade']) == 1 and ($QuantidadeMateriais + $QuantidadeServicos) == 1) {
                                                $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
                                                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
                                            } else {
                                                $ifVisualizacaoQtdeExercicioThenReadOnly = '';
                                                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                            }
                                        }

                                        if (! $ocultarCampoFornecedor) { ?>
                                            <td class="textonormal" align="left">
                                                <?php   $CnpjStr = FormataCpfCnpj($materiais[$itr]['fornecedor']);

                                                        if (! $ocultarCamposEdicao) {
                                                ?>
                                                    <input name="MaterialFornecedor[<?php echo  $itr ?>]" id="MaterialFornecedor[<?php echo  $itr ?>]" <?php echo  $ifVisualizacaoThenReadOnly?> <?php echo  $ifVisualizacaoThenReadOnlyFornecedorItens?> size="18" maxlength="18" value="<?php echo  $CnpjStr ?>" type="text" onChange="validaFornecedor('MaterialFornecedor[<?php echo  $itr ?>]', 'MaterialFornecedorNome[<?php echo  $itr ?>]',<?php echo  $materiais[$itr]['codigo'] ?>, TIPO_ITEM_MATERIAL); AtualizarFornecedorValor('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL);"/>
                                                    <input name="MaterialFornecedorValor[<?php echo  $itr ?>]" id="MaterialFornecedorValor[<?php echo  $itr ?>]" value="<?php echo  $CnpjStr ?>" type="hidden"/>
                                                <?php   } else {
                                                            echo $CnpjStr;
                                                        }
                                                ?>
                                                <br>
                                                <div align="left" id="MaterialFornecedorNome[<?php echo  $itr ?>]">
                                                    <?php   if (! is_null($materiais[$itr]['fornecedor'])) {
                                                                $CPFCNPJ                  = removeSimbolos($materiais[$itr]['fornecedor']);
                                                                $materialServicoFornecido = $materiais[$itr]['codigo'];
                                                                $tipoMaterialServico      = TIPO_ITEM_MATERIAL;

                                                                require 'RotDadosFornecedor.php';
                                                            }
                                                            $db = Conexao();
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="textonormal" align="center" width="10">
                                                <?php   if (! $ocultarCamposEdicao) { ?>
                                                    <input name="MaterialMarca[<?php echo  $itr ?>]" id="MaterialMarca[<?php echo  $itr ?>]" size="18" <?php echo  $ifVisualizacaoThenReadOnly?> maxlength="18" value="<?php echo  $materiais[$itr]['marca'] ?>" class="textonormal" type="text">
                                                <?php   } else {
                                                            echo $materiais[$itr]['marca'] . '&nbsp;';
                                                        }
                                                ?>
                                            </td>
                                            <td class="textonormal" align="right" width="10">
                                                <?php   if (! $ocultarCamposEdicao) { ?>
                                                    <input name="MaterialModelo[<?php echo  $itr ?>]" id="MaterialModelo[<?php echo  $itr ?>]" size="18" <?php echo  $ifVisualizacaoThenReadOnly?> maxlength="18" value="<?php echo  $materiais[$itr]['modelo'] ?>" class="textonormal" type="text">
                                                <?php   } else {
                                                            echo $materiais[$itr]['modelo'] . '&nbsp;';
                                                        }
                                                ?>
                                            </td>
                                <?php   } ?>
                                <!--  Coluna 9 =  Valor Total -->
                                <?php
                                        // kim cr#227641 & cr#228067
                                    if(empty($telaAppView)){
                                ?>
                                <td class="textonormal" align="right" width="10">
                                    <div id="MaterialValorTotal[<?php echo  $itr ?>]"><?php
                                           echo converte_valor_estoques($ValorTotalItem); 
                                      ?></div>
                                </td>
                                    <?php } ?>
                            </tr>
                <?php   }

                        if ($QuantidadeMateriais <= 0) {
                            ?>
                            <tr>
                                <td class="textonormal itens_material" colspan="<?php echo  ($qtdeColunas - $colunasOcultas) ?>">Nenhum item de material informado</td>
                            </tr>
                            <!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                            <?php
                        }
                ?>
                <?php 
                    // kim cr#227641 & cr#228067
                    if(empty($telaAppView)){
                ?>
                <tr>
                    <td colspan="0" class="titulo3 itens_material menosum">VALOR TOTAL DA SOLICITAÇÃO DE MATERIAL</td>
                    <td class="textonormal" align="right">
                        <div id="MaterialTotal">
                            <?php 
                                //    kim cr#228067
                                if(isset($telaAppView) && $telaAppView == true){
                                    echo " - ";
                               }else{
                                echo converte_valor_estoques($ValorTotal);
                               }
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php // Servicos  ?>
        <table id="scc_servico" summary="" bgcolor="bfdaf2" border="1" bordercolor="#75ADE6"width="100%">
            <tbody>
                <tr>
                    <td colspan="17" class="titulo3" align="center" bgcolor="#75ADE6" valign="middle">ITENS DA SOLICITAÇÃO DE SERVIÇO</td>
                </tr>
                <?php   $qtdeColunas    = 7;
                        $colunasOcultas = 0;

                        if ($ocultarCampoExercicio) {
                            $colunasOcultas += 3;
                        }

                        if ($ocultarCampoFornecedor) {
                            $colunasOcultas += 1;
                        }
                ?>
                <!-- Headers ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                <tr class="head_principal_servico">
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="<?php echo  $descricaoWidth ?>"/>
                        DESCRIÇÃO DO SERVIÇO
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        CÓD.RED. CADUS
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7"/>
                        DESCRIÇÃO DETALHADA
                    </td>
                    <?php 
                    // kim cr#227641 & cr#228067  
                    if(empty($telaAppView)){
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="50px">
                        QUANTIDADE
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        VALOR ESTIMADO
                    </td>
                    <?php   if (! $ocultarCampoFornecedor) {
                                ++ $qtdeColunas;
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="300px">
                        <br>
                        CPF/CNPJ DO FORNECEDOR
                    </td>
                    <?php   } ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        VALOR TOTAL
                    </td>
                    <?php } ?>
                </tr>
                <!-- FIM Headers ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                <?php   // Serviços do POST-----------------------------------
                        $Quantidade     = count($servicos);
                        $ValorTotalItem = 0;
                        $ValorTotal     = 0;

                        for ($itr = 0; $itr < $Quantidade; ++ $itr) {
                            $ValorTotalItem = moeda2float($servicos[$itr]['quantidade']) * moeda2float($servicos[$itr]['valorEstimado']);
                            $ValorTotal += $ValorTotalItem;

                            if (! $ocultarCampoExercicio) {
                                $ValorTotalExercicio   = moeda2float($servicos[$itr]['quantidadeExercicio']) * moeda2float($servicos[$itr]['valorEstimado']);
                                $TotalDemaisExercicios = $ValorTotalItem - $ValorTotalExercicio;

                                if ($TotalDemaisExercicios < 0) {
                                    $TotalDemaisExercicios = 0;
                                }
                            }
                ?>
                <!-- Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                <tr>
                    <!--   Coluna 1 => Ordem   -->
                    <td class="textonormal" align="center">
                        <?php echo  ($itr + 1)?>
                    </td>
                    <!--  Coluna 2 => Descricao -->
                    <td class="textonormal">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                            <input name="ServicoCheck[<?php echo  $itr ?>]" <?php if ($servicos[$itr]['check']) { echo 'checked'; } ?> <?php echo  $ifVisualizacaoThenReadOnly?> type="checkbox">
                        <?php   } ?>
                        <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo  $servicos[$itr]['codigo'] ?>&amp;TipoGrupo=S&amp;ProgramaOrigem=<?php echo  $programa ?>',700,370);">
                            <font color="#000000"><?php echo  $servicos[$itr]['descricao'] ?></font>
                        </a>
                    </td>
                    <!--  Coluna 3 => Código Red -->
                    <td class="textonormal" align="center">
                        <?php echo  $servicos[$itr]['codigo']?>
                        <input value="<?php echo  $servicos[$itr]['codigo'] ?>" name="ServicoCod[<?php echo  $itr ?>]" type="hidden">
                        <input value="<?php echo ($servicos[$itr]['isObras']) ? 'true' : 'true'; ?>" name="ServicoIsObras[<?php echo  $itr ?>]" id="ServicoIsObras_<?php echo  $itr ?>" type="hidden"/>
                    </td>
                    <!--  Coluna 4 => Descrição Detalhada -->
                    <td class="textonormal" align="center" width="300px">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                        <textarea style="text-transform: uppercase;" id="ServicoDescricaoDetalhada[<?php echo  $itr ?>]" name="ServicoDescricaoDetalhada[<?php echo  $itr ?>]" <?php echo  $ifVisualizacaoThenReadOnly?> cols="50" rows="4" OnKeyUp="javascript:CaracteresObservacao(1)" OnBlur="javascript:CaracteresObservacao(0)" OnSelect="javascript:CaracteresObservacao(1)" class="textonormal"><?php echo  $servicos[$itr]['descricaoDetalhada'] ?></textarea>
                        <?php   } else {
                                    echo $servicos[$itr]['descricaoDetalhada'];
                                }
                        ?>
                    </td>
                    <!--  Coluna 5 => Quantidade -->
                    <?php
                    // kim cr#227641 & cr#228067
                    if(empty($telaAppView)){
                    ?>
                    <td class="textonormal" align="center">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                            <input class="dinheiro4casas" value="<?php echo  $servicos[$itr]['quantidade'] ?>" <?php echo  $ifVisualizacaoThenReadOnly?> maxlength="16" size="11" name="ServicoQuantidade[<?php echo  $itr ?>]" id="ServicoQuantidade[<?php echo  $itr ?>]" type="text" onKeyUp="onChangeItemQuantidade('<?php echo  $itr ?>', TIPO_ITEM_SERVICO); "/>
                        <?php   } else {
                                    echo $servicos[$itr]['quantidade'];
                                }
                        ?>
                    </td>
                    <!--  Coluna 6 => Valor Extimado -->
                    <td class="textonormal" align="center" width="10">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                            <input name="ServicoValorEstimado[<?php echo  $itr ?>]" id="ServicoValorEstimado[<?php echo  $itr ?>]" <?php echo  $ifVisualizacaoThenReadOnly?> size="16" maxlength="16" value="<?php echo  $servicos[$itr]['valorEstimado'] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemValor('<?php echo  $itr ?>', TIPO_ITEM_SERVICO); " onBlur="onChangeValorEstimadoItem('<?php echo  $itr ?>', TIPO_ITEM_SERVICO)"/>
                        <?php   } else {
                                    echo $servicos[$itr]['valorEstimado'];
                                }
                        ?>
                    </td>
                    <?php

                    if (! $ocultarCampoExercicio) {

                                // condicoes em que campos são desabilitados
                                if ($ifVisualizacaoThenReadOnly) {
                                    $ifVisualizacaoQtdeExercicioThenReadOnly         = 'disabled';
                                    $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                } elseif (moeda2float($servicos[$itr]['quantidade']) == 1 and ($QuantidadeMateriais + $QuantidadeServicos) == 1) {
                                    $ifVisualizacaoQtdeExercicioThenReadOnly         = 'disabled';
                                    $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
                                } else {
                                    $ifVisualizacaoQtdeExercicioThenReadOnly         = '';
                                    $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                }
                            }

                            if (! $ocultarCampoFornecedor) {
                                ?>
                                <td class="textonormal" align="left" width="10%">

                                    <?php   $CnpjStr = FormataCpfCnpj($servicos[$itr]['fornecedor']);

                                            if (! $ocultarCamposEdicao) {
                                    ?>
                                    <input name="ServicoFornecedor[<?php echo  $itr ?>]" id="ServicoFornecedor[<?php echo  $itr ?>]" <?php echo  $ifVisualizacaoThenReadOnly?> <?php echo  $ifVisualizacaoThenReadOnlyFornecedorItens?> size="18" maxlength="18" value="<?php echo  $CnpjStr ?>" type="text" onChange="validaFornecedor('ServicoFornecedor[<?php echo  $itr ?>]', 'ServicoFornecedorNome[<?php echo  $itr ?>]',<?php echo  $servicos[$itr]['codigo'] ?>, TIPO_ITEM_SERVICO); AtualizarFornecedorValor('<?php echo  $itr ?>', TIPO_ITEM_SERVICO);" />
                                    <input name="ServicoFornecedorValor[<?php echo  $itr ?>]" id="ServicoFornecedorValor[<?php echo  $itr ?>]" value="<?php echo  $CnpjStr ?>" type="hidden"/>
                                    <?php   } else {
                                                echo $CnpjStr;
                                            }
                                    ?>
                                    <br>
                                    <div align="left" id='ServicoFornecedorNome[<?php echo  $itr ?>]'/>
                                        <?php   if (! is_null($servicos[$itr]['fornecedor'])) {
                                                    $CPFCNPJ = removeSimbolos($servicos[$itr]['fornecedor']);
                                                    $origem = 'scc';
                                                    $materialServicoFornecido = $servicos[$itr]['codigo'];
                                                    $tipoMaterialServico = TIPO_ITEM_SERVICO;

                                                    require 'RotDadosFornecedor.php';
                                                }
                                                $db = Conexao();
                                        ?>
                                    </div>
                                </td>
                    <?php   } ?>
                    <!--  Coluna 7 => Valor Total -->
                    <td class="textonormal" align="right" width="10">
                        <div id="ServicoValorTotal[<?php echo  $itr ?>]"> 
                            <?php 
                                echo converte_valor_estoques($ValorTotalItem);
                            ?>
                        </div>
                    </td>
                <?php } ?>
                </tr>
                <?php   }

                        if ($Quantidade <= 0) {
                            ?>
                            <tr>
                                <td class="textonormal itens_servico" colspan="<?php echo  ($qtdeColunas - 1) ?>">Nenhum item de serviço informado</td>
                            </tr>
                <?php   } ?>
                <!-- FIM Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                <?php
                    // kim cr#227641 & cr#228067
                    if(empty($telaAppView)){
                 ?>
                <tr>
                    <td class="titulo3" colspan="<?php echo  ($qtdeColunas - 1) ?>">VALOR TOTAL DA SOLICITAÇÃO DE SERVIÇO</td>
                    <td class="textonormal" align="right">
                        <div id="ServicoTotal">
                            <?php
                               echo converte_valor_estoques($ValorTotal);
                            ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php   if (! $ocultarCamposEdicao) :
                            if (! $ocultarBotaoItem) :
                ?>
                <tr>
                    <td class="textonormal" colspan="<?php echo  ($qtdeColunas - $colunasOcultas) + 4 ?>" align="center">
                        <input name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('../estoques/CadIncluirItem.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 700, 350);" type="button">
                        <input name="RetirarItem" value="Retirar Item" class="botao" onclick="javascript:enviar('Retirar');" type="button">
                    </td>
                </tr>
                <?php       endif;
                        endif;
                ?>
            </tbody>
        </table>
    </td>
</tr>
<!-- kim CR#227641 -->
<tr <?php echo (isset($telaAppView) and $telaAppView == true)? 'style="display:none"': "";?> >
    <td class="textonormal" colspan="4">
        <table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
            <tr>
                <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3"colspan="7">ANEXAÇÃO DE DOCUMENTO(S)</td>
            </tr>
            <?php   if (! $ocultarCamposEdicao) { ?>
                <tr>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%" valign="top">Anexação de Documentos</td>
                    <td class="textonormal">
                        <table border="0" width="100%" summary="">
                            <tr>
                                <td>
                                    <input type="file" name="Documentacao" class="textonormal"/>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php   }

                    $DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);

                    if ($DTotal == 0) {
                        ?>
                        <tr>
                            <td class="textonormal"colspan='2'>Nenhum documento informado</td> 
                        </tr>
            <?php   }

                    for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
                        if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') {
                            echo '<tr>';

                            if (! $ocultarCamposEdicao) {
                                echo "<td align='right' ><input type='checkbox' name='DDocumento[$Dcont]' value='$Dcont' ></td>\n";
                            }
                            echo "<td class='textonormal' >";
                            $arquivoOriginal = $_SESSION['Arquivos_Upload']['nomeOriginal'][$Dcont];
                            if (! $ocultarCamposEdicao) {
                                echo (empty($arquivoOriginal)?$_SESSION['Arquivos_Upload']['nome'][$Dcont]:$_SESSION['Arquivos_Upload']['nomeOriginal'][$Dcont]);
                            } else {
                                $arquivoServidor = $_SESSION['Arquivos_Upload']['nome'][$Dcont];

                                if(empty($arquivoOriginal)){
                                    $arquivoOriginal = $arquivoServidor;
                                }
                               
                                $arquivo = 'compras/' . $arquivoServidor;

                                addArquivoAcesso($arquivo);
                                echo "<a href='../carregarArquivo.php?arq=" . urlencode($arquivo) . "'>" . $arquivoOriginal . '</a>';
                            }
                            echo '</td></tr>';
                        }
                    }

                    if (! $ocultarCamposEdicao) {
                        ?>
                        <tr>
                            <td class="textonormal" colspan="7" align="center">
                                <input type="button" name="IncluirDocumento" value="Incluir Documento" class="botao" onclick="javascript:enviar('Incluir_Documento');">
                                <input type="button" name="RetirarDocumento" value="Retirar Documento" class="botao" onClick="javascript:enviar('Retirar_Documento');">
                            </td>
                        </tr>
            <?php   } ?>
        </table>
    </td>
</tr>

<tbody>
    <?php if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) { ?>
    <tr>
        <td class="textonormal" colspan="4">
            <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                <tbody>
                    <tr>
                        <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle">HISTÓRICO DA SITUAÇÃO DA SCC</td>
                    </tr>
                    <tr>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">SITUAÇÃO</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">DATA</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">RESPONSÁVEL</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">TELEFONE</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">EMAIL</td>
                    </tr>
                    <?php   $sql = "SELECT  SS.ESITSONOME, HSS.THSITSDATA, U.EUSUPORESP, U.EUSUPOMAIL, U.AUSUPOFONE, HSS.CSITSOCODI
                                    FROM    SFPC.TBHISTSITUACAOSOLICITACAO HSS, SFPC.TBSITUACAOSOLICITACAO SS, SFPC.TBUSUARIOPORTAL U
                                    WHERE   HSS.CSITSOCODI = SS.CSITSOCODI
                                            AND HSS.CUSUPOCODI = U.CUSUPOCODI
                                            AND CSOLCOSEQU = $Solicitacao
                                    ORDER BY HSS.THSITSDATA DESC ";

                            $res = executarSQL($db, $sql);

                            while ($linha = $res->fetchRow()) {
                                $nomeSituacao = $linha[0];

                                if ($linha[5] == 9) { // Se situação = Licitação
                                    $vetor        = getChaveLicitacao($Solicitacao, $db);
                                    $descComissao = getDescComissao($vetor[3], $db);

                                    if ($vetor[1] == '999') {
                                        $nomeSituacao .= $descComissao;
                                    } else {
                                        $nomeSituacao .= ' - PL ' . $vetor[0] . '/' . $vetor[1] . ' - ' . $descComissao;
                                    }
                                }

                                if ($linha[5] == 8) { // Se situação = Encaminhada
                                    $row           = getDadosSolicitacao($Solicitacao, $db);
                                    $descComissao  = getDescComissao($row->comissao, $db);
                                    $nomeSituacao .= ' - ' . $descComissao;
                                }
                                $dataSituacao = DataBarra($linha[1]) . ' ' . hora($linha[1]);
                                $nomeUsuario  = $linha[2];
                                $emailUsuario = $linha[3];
                                $foneUsuario  = $linha[4];
                    ?>
                    <tr style="text-align: center">
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%"><?php echo  $nomeSituacao ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%"><?php echo  $dataSituacao ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%"><?php echo  $nomeUsuario ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%"><?php echo  $foneUsuario ?>&nbsp;</td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%"><?php echo  $emailUsuario ?>&nbsp;</td>
                    </tr>
                    <?php   } ?>
<!--            </table>-->
<!--            </table>-->
<?php if($programa == 'window'){?>
                    <tr>
                        <td class="textonormal" colspan="5">
                            <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                <tbody>
                                    <tr>
                                        <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle">DOCUMENTO</td>
                                    </tr>
                                    <tr>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">NOME</td>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">RESPONSÁVEL</td>
                                    
                                    </tr>
                                    </tr>
                                    <?php  $sql = "SELECT doc.edocsonome, doc.tdocsoulat, us.eusuporesp FROM SFPC.tbdocumentosolicitacaocompra doc
                                            INNER JOIN sfpc.tbusuarioportal us on doc.cusupocodi = us.cusupocodi
                                            where doc.csolcosequ =  $Solicitacao";
                                            print_r($sql);
                                            $result = executarTransacao($db, $sql);

                                            $contAux = 0;

                                            while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                                                $contAux = $contAux + 1;
                                    ?>
                                    <tr style="text-align: left">
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><a href="../uploads/compras/<?php echo "$row->edocsonome"?>" download><?php echo $row->edocsonome?></a> </td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $row->eusuporesp ?></td>    
                                    </tr> 
                                    <?php   }

                                            if ($contAux == 0) {
                                                ?>
                                                <tr style="text-align: left">
                                                    <td class="textonormal" bgcolor="#DCEDF7" colspan=7 height="20" valign="top" align="left">Nenhum Documento</td>
                                                </tr>
                                    <?php   } ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if($programa != 'window'){?>
                    <tr>
                        <td class="textonormal" colspan="5">
                            <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                <tbody>
                                    <tr>
                                        <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle">PRÉ-SOLICITAÇÃO DE EMPENHO (PSE)</td>
                                    </tr>
                                    <tr>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7">NÚMERO/ANO</td>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7">DATA/HORA GERAÇÃO</td>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7">NÚMERO BLOQUEIO</td>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR</td>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7">FORNECEDOR</td>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7">SITUAÇÃO</td>
                                        <td class="textoabason" align="center" bgcolor="#DCEDF7">DATA SITUAÇÃO</td>
                                    </tr>
                                    <?php   $sql = "SELECT  A.CPRESOSEQU AS NUMERO, A.APRESOANOE AS ANO, to_char(A.TPRESOGERA,'DD/MM/YYYY HH:MI') AS DATAHORA, A.APRESONBLOQ AS BLOQUEIO, A.APRESOANOB AS ANOBLOQUEIO,
                                                            C.AFORCRCCGC AS CGC, C.AFORCRCCPF AS CPF, C.NFORCRRAZS AS RAZAO, A.CMOTNICODI AS IDMOTIVO, D.EMOTNIDESC AS DESCRICAO, A.APRESONUES AS NUMEROEMP, A.APRESONUES AS ANOEMP, 
                                                            to_char(A.TPRESOULAT,'DD/MM/YYYY') AS DATAULT, to_char(A.TPRESOIMPO,'DD/MM/YYYY') AS DATAIMPORTACAO, to_char(A.DPRESOCSEM,'DD/MM/YYYY') AS DATACANCEL,
                                                            to_char(A.DPRESOGERE,'DD/MM/YYYY') AS DATAGERACAO, A.APRESONUES AS NUMEMP, A.APRESOANES AS ANOEMP, to_char(A.DPRESOANUE,'DD/MM/YYYY') AS DATAANULACAO,
                                                            A.VPRESOANUE AS VALORANULADO, SUM(B.VIPRESEMPN) AS SOMA
                                                    FROM    SFPC.TBPRESOLICITACAOEMPENHO A
                                                            LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO C ON C.AFORCRSEQU  = A.AFORCRSEQU
                                                            LEFT JOIN SFPC.TBITEMPRESOLICITACAOEMPENHO B ON (A.CPRESOSEQU  = B.CPRESOSEQU AND  A.APRESOANOE=B.APRESOANOE )
                                                            LEFT JOIN SFPC.TBMOTIVONAOIMPORTACAO D ON D.CMOTNICODI = A.CMOTNICODI
                                                    WHERE   A.CSOLCOSEQU = $Solicitacao
                                                            AND A.CPRESOSEQU = B.CPRESOSEQU 
                                                            AND A.APRESOANOE = B.APRESOANOE
                                                            AND A.AFORCRSEQU = C.AFORCRSEQU
                                                    GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20  ";

                                            $result = executarTransacao($db, $sql);

                                            $contAux = 0;

                                            while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                                                $contAux = $contAux + 1;

                                                if (! empty($row->anobloqueio) && ! empty($row->bloqueio)) { // Formatar bloqueio
                                                    $vetor = getDadosBloqueioFromChave($dbOracle, $row->anobloqueio, $row->bloqueio);
                                                }
                                                $blqFormato = $vetor['bloqueio'];

                                                if (! empty($row->cpf)) { // Formatar cpf/cgc de fornecedor
                                                    $cpfcgc = $row->cpf;
                                                } else {
                                                    $cpfcgc = $row->cgc;
                                                }
                                                $cpfcgcAux = FormataCpfCnpj($cpfcgc);
                                                $soma      = number_format($row->soma, 4, ',', '.'); // Formatar soma

                                                if (! empty($row->idmotivo)) { // Formatar mensagens da situacao e datas
                                                    $descSituacao = 'PSE RECUSADA POR MOTIVO DE ' . $row->descricao;
                                                    $dataMotivo   = $row->datault;
                                                }

                                                if (! empty($row->dataimportacao)) {
                                                    $descSituacao = 'SE GERADA';
                                                    $dataMotivo   = $row->datault;
                                                }

                                                if (! empty($row->datacancel)) {
                                                    $descSituacao = 'SE CANCELADA';
                                                    $dataMotivo   = $row->datacancel;
                                                }

                                                if (! empty($row->datageracao)) {
                                                    $descSituacao = 'EMPENHADO (NÚMERO=' . $row->numemp . '/' . $row->anoemp . ')';
                                                    $dataMotivo   = $row->datageracao;
                                                }

                                                if (! empty($row->dataanulacao)) {
                                                    $descSituacao = 'EMPENHO ANULADO (VALOR=' . number_format($row->valoranulado, 4, ',', '.') . ')';
                                                    $dataMotivo   = $row->dataanulacao;
                                                }
                                    ?>
                                    <tr style="text-align: left">
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $row->numero.'/'.$row->ano ?> </td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $row->datahora ?> </td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $blqFormato ?></td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $soma ?></td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $cpfcgcAux.' '.$row->razao ?></td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $descSituacao ?></td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $dataMotivo ?></td>
                                    </tr>
                                    <?php $sql = "SELECT doc.edocsonome, doc.tdocsoulat, us.eusuporesp FROM SFPC.tbdocumentosolicitacaocompra doc
                                INNER JOIN sfpc.tbusuarioportal us on doc.cusupocodi = us.cusupocodi
                                where doc.csolcosequ =  $Solicitacao";

                                 $result = executarTransacao($db, $sql);

                                                ?>     
                                 <tr>
                                    <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle">DOCUMENTO</td>
                                </tr>
                                <tr>
                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">NOME</td>
                                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">RESPONSÁVEL</td>
                                
                                </tr>
                            
                                <?php while($dados = $result->fechRow(DB_FETCHMODE_OBJECT)){?>    
                            
                                    <tr style="text-align: left">
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $dados->edocsonome?> </td>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center"><?php echo $dados->eusuporesp ?></td>    
                                    </tr>
                            <?php }?> 
                                    <?php   }

                                            if ($contAux == 0) {
                                                ?>
                                                <tr style="text-align: left">
                                                    <td class="textonormal" bgcolor="#DCEDF7" colspan=7 height="20" valign="top" align="left">Nenhum item de pré-solicitação informado</td>
                                                </tr>
                                    <?php   } ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
   
                    <tr>
                        <td align="right" colspan="5">
                            <?php   if($programa != 'window') { ?>
                                <input type="button" name="Imprimir" value="Imprimir" class="botao" onClick="javascript:enviar('Imprimir');" />
                            <?php   }

                                    if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR && $programa !='window') {
                            ?>
                                <input type="submit" name="Voltar" value="Voltar" class="botao" onClick="<?php echo ($programa == 'window') ? 'window.close()' : 'javascript:onButtonVoltar();'?>">
                            <?php   } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <!--  final heraldo botelho -->
    <?php   } ?>
    <!-- FIM DE HISTORICO DE SCC -->
</tbody>
</table>
<tr>
    <td class="textonormal" align="right">
        <input type="hidden" name="InicioPrograma" value="1">
        <input type="hidden" name="RetirarDocs" value="<?php echo $RetirarDocs ?>">
        <input type="hidden" name="Solicitacao" value="<?php echo isset($Solicitacao) ? $Solicitacao : $_SESSION['SeqSolicitacaok']; ?>">
        <input type="hidden" name="Botao" value="">
        <input type="hidden" name="Foco" value="">
        <input type="hidden" name="SeqSolicitacao" value="<?php echo isset($Solicitacao) ? $Solicitacao : $_SESSION['SeqSolicitacaok']; ?>">
        <input type="hidden" name="isDotacaoAnterior" value="<?php echo $isDotacao ?>">
        <?php   if ($acaoPagina == ACAO_PAGINA_INCLUIR) { ?>
            <input type="button" name="Rascunho" value="Salvar Rascunho" class="botao" onClick="javascript:enviar('Rascunho');">
            <input type="button" name="Incluir" value="Incluir Solicitação" class="botao" onClick="javascript:onButtonIncluir();">
        <?php   } elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
                    if ($situacaoSolicitacaoAtual == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO) {
        ?>
                        <input type="button" name="Rascunho" value="Manter Rascunho" class="botao" onClick="javascript:enviar('ManterRascunho');">
        <?php       } ?>
                    <input type="button" name="Manter" value="Manter Solicitação" class="botao" onClick="javascript:onButtonManter();">
        <?php   } elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) { ?>
                    <input type="button" name="Excluir" value="Cancelar Solicitação" class="botao" onClick="javascript:enviar('Excluir');">
        <?php   }

                if ($acaoPagina == ACAO_PAGINA_EXCLUIR or $acaoPagina == ACAO_PAGINA_MANTER) {
        ?>
                    <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:onButtonVoltar();">
        <?php   } ?>
    </td>
</tr>
</table>
</form>
<script language="javascript" type="">
    qtdeMateriais        = <?php echo  count($materiais) ?>;
    qtdeServicos         = <?php echo  count($servicos) ?>;
    campoExercicioExiste = <?php echo  ($ocultarCampoExercicio) ? 'false' : 'true'; ?>;

    // ITENS DA SOLICITAÇÃO DE MATERIAL colspan
    $('td.itens_material').attr('colspan', contador('head_principal'));
    $('td.menosum').attr('colspan', contador('head_principal')-1);
    // ITENS DA SOLICITAÇÃO DE SERVIÇO colspan
    $('td.itens_servico').attr('colspan', contador('head_principal_servico'));
    $('td.menosum_servico').attr('colspan', contador('head_principal_servico')-1);

    var formulario = document.CadSolicitacaoCompraIncluirManterExcluir;

    <?php
    if (! is_null($Foco) and $Foco != '') {
        ?>
        document.CadSolicitacaoCompraIncluirManterExcluir.<?php echo  $Foco ?>.focus();
        <?php
    }
    if ($isDotacao) {
        // echo "passou";
        // exit;
    ?>
    mudarBloqueioDotacao(TIPO_RESERVA_DOTACAO);
    <?php
    } else {
    ?>
    mudarBloqueioDotacao(TIPO_RESERVA_BLOQUEIO);
    <?php
    }
    $db->disconnect();
    $dbOracle->disconnect();

    echo $JSCriacaoLimiteCompra; // imprime JS que gera todos valores de limite
    ?>
</script>
<?php   // Pegando output gerado fora do template e incluindo na posição correta no template
        $outputNaoTratratado = ob_get_contents();

        ob_clean();

        $template->FINAL = $outputNaoTratratado;
        $template->show();
