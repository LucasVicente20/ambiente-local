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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: v1.8.0-46-g35cfc81
 */

# ------------------------------------------------------------------------------
# Autor:   Caio Coutinho - Pitang Agile TI
# Data :    17/08/2018
# Objetivo: CR 200290 - REGISTRO DE PREÇOS] Criar botão para excluir uma carona externa
# ------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 20/11/2018
# Objetivo: Tarefa Redmine 205798
#-----------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadCaronaOrgaoExternoManterSelecionar extends Dados_Abstrata
{
    public function sqlCaronaAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $SQL = "SELECT * FROM SFPC.tbcaronaorgaoexterno ca
            where ca.carpnosequ = " . $carpnosequ->getValor() . " order by ca.ccaroesequ";
        return $SQL;
    }    
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_CadCaronaOrgaoExternoManterSelecionar extends Negocio_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadCaronaOrgaoExternoManterSelecionar());
        return parent::getDados();
    }

    /**
     *
     * @param unknown $ata
     * @return NULL
     */
    public function selecionarCaronasDaAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = $this->getDados()->sqlCaronaAta($carpnosequ);
        return ClaDatabasePostgresql::executarSQL($sql);
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadCaronaOrgaoExternoManterSelecionar extends Adaptacao_Abstrata
{
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadCaronaOrgaoExternoManterSelecionar());
        return parent::getNegocio();
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_CadCaronaOrgaoExternoManterSelecionar extends UI_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadCaronaOrgaoExternoManterSelecionar());
        return parent::getAdaptacao();
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadCaronaOrgaoExternoManterSelecionar.html", "Registro de Preço > Ata Interna > Carona Órgão Externo > Manter"));
        
        $this->getTemplate()->TITULO_SUPERIOR = "MANTER - CARONA ÓRGÃO EXTERNO";
        $this->getTemplate()->DESCRICAO_COLUNA = "Nº Da Ata Interna";
        $this->getTemplate()->TITULO_INFERIOR = "CARONA DE ÓRGÃO EXTERNO CADASTRADAS";
        $this->getTemplate()->NOME_PROGRAMA = "CadCaronaOrgaoExternoManterSelecionar";
    }

    /**
     *
     * @param array $caronas
     */
    public function plotarTabelaCarona($caronas)
    {
        if ($caronas == null) {
            return;
        }
        foreach ($caronas as $carona) {
            $mes = substr($carona->tcaroeulat, 5, 2);
            $dia = substr($carona->tcaroeulat, 8, 2);
            $ano = substr($carona->tcaroeulat, 0, 4);
            
            $this->getTemplate()->SEQ_Carona = $carona->ccaroesequ;
            $this->getTemplate()->ORG_EXT = $carona->ecaroeorgg;
            $this->getTemplate()->DATA_CAD = $dia . "/" . $mes . "/" . $ano;
            
            $this->getTemplate()->block('bloco_carona_ata');
        }
    }

    /**
     */
    public function inicializaItem()
    {
    }
}

/**
 *
 * @author jfsi
 *
 */
class CadCaronaOrgaoExternoManterSelecionar extends ProgramaAbstrato
{

    /**
     */
    private function acaoVoltar()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        
        $uri = 'CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao;
        header('location: ' . $uri);
    }

    /**
     */
    private function acaoSelecionar()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        $ata = $_SESSION['ata'];
        $_SESSION['numAtaFormatado'] = $_REQUEST['numAtaFormatado'];
        
        if (! isset($_POST['seqCarona']) || $_POST['seqCarona'] <= 0) {
            $this->getUI()->mensagemSistema("Selecione uma ata", 0, 0);
            $this->exibePaginaInicial();
            return;
        }

        $seqCarona = $_REQUEST['seqCarona'];
        
        $uri = 'CadCaronaOrgaoExternoManter.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao . '&ata=' . $ata . '&carona=' . $seqCarona;
        header('Location: ' . $uri);
        exit();
    }

    private function acaoExcluir()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        $ata = $_SESSION['ata'];
        $_SESSION['numAtaFormatado'] = $_REQUEST['numAtaFormatado'];

        if (! isset($_POST['seqCarona']) || $_POST['seqCarona'] <= 0) {
            $this->getUI()->mensagemSistema("Selecione uma ata para excluir", 0, 0);
            $this->exibePaginaInicial();
            return;
        } else {
            $seqCarona = $_POST['seqCarona'];
            $db = Conexao();
            $db->autoCommit(false);
            $db->query("BEGIN TRANSACTION");
            
            $sqlItem = "DELETE FROM SFPC.tbcaronaorgaoexternoitem COEI WHERE COEI.carpnosequ = " . $ata . " AND COEI.ccaroesequ = " . $seqCarona . ";";
            executarTransacao($db, $sqlItem);
            $sqlDoc = "DELETE FROM SFPC.tbdocumentocaronaexternorp DCERP WHERE DCERP.carpnosequ = " . $ata . " AND DCERP.ccaroesequ = " . $seqCarona . ";";
            executarTransacao($db, $sqlDoc);
            $sql = "DELETE FROM SFPC.tbcaronaorgaoexterno COE WHERE COE.carpnosequ = " . $ata . " AND COE.ccaroesequ = " . $seqCarona . ";";
            executarTransacao($db, $sql);

            $db->query("COMMIT");
            $db->query("END TRANSACTION");

            $this->getUI()->mensagemSistema("Ata Externa excluida com sucesso", 1, 1);
        }

        $this->exibePaginaInicial();
    }

    /**
     */
    private function exibePaginaInicial()
    {
        if(isset($_SESSION['mensagemFeedback']) && !empty($_SESSION['mensagemFeedback'])){
            $this->getUI()->mensagemSistema($_SESSION['mensagemFeedback'], 1, 1);
            unset($_SESSION['mensagemFeedback']);
            $this->exibePaginaInicial();
            return;
        }

        $ata = $_SESSION['ata'];
        $numAtaFormatado = $_SESSION['nAtaFormatado'];

        $this->getUI()->getTemplate()->Nata = $ata;
        $this->getUI()->getTemplate()->VALOR_DESCRICAO_COLUNA = $numAtaFormatado;
        $caronas = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->selecionarCaronasDaAta(new Negocio_ValorObjeto_Carpnosequ($ata));
        $this->getUI()->plotarTabelaCarona($caronas);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadCaronaOrgaoExternoManterSelecionar());
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        $botao = isset($_POST['Botao']) ? filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING) : 'Principal';
        
        switch ($botao) {
            case 'selecionar':
                $this->acaoSelecionar();
                break;
            case 'voltar':
                $this->acaoVoltar();
                break;
            case 'excluir':
                $this->acaoExcluir();
                break;
            case 'Principal':
            default:
                $this->exibePaginaInicial();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadCaronaOrgaoExternoManterSelecionar());
unset($_SESSION['Arquivos_Upload']);