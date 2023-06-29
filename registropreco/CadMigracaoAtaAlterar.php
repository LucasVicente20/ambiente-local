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
 * @version   Git: v1.8.0-101-g07e25e1
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class Dados
{
    public function sqlconsultarAta($processo, $ano, $orgao, $fornecedor)
    {
        $sql = "SELECT * FROM SFPC.tbataregistroprecointerna arpi";
        $sql .= " where arpi.clicpoproc =" . $processo;
        $sql .= " and arpi.alicpoanop =" . $ano;
        $sql .= " and arpi.corglicodi =" . $orgao;
        $sql .= " and arpi.aforcrsequ =" . $fornecedor;
        return $sql;
    }

    public function sqlConsultarDocumentosARPI($ata)
    {
        $sql = "SELECT * FROM SFPC.tbdocumentoatarp darp";
        $sql .= " where darp.carpnosequ =" . $ata;
        return $sql;
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author rlfo
 *
 */
class GUI extends BaseIntefaceGraficaUsuario
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadMigracaoAta.html", "Registro de Preço > Migração > Manter"));
        $this->setAdaptacao(new Adaptacao());
        $this->getAdaptacao()->setTemplate($this->getTemplate());
    }

    public function baixarArquivo()
    {
        // $_SESSION['documentosAta']
    }

    /*
     * Este método é a porta de entrada default da página
     * Sempre que executar algum comando é aconselhável chama-lo
     */
    public function processarPrincipal()
    {
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author rlfo
 *
 */
class Adaptacao extends AbstractAdaptacao
{
    public function __construct()
    {
        $this->setNegocio(new Negocio());
    }

    public function baixarArquivo($codigoDocumento)
    {
        $documentos = $_SESSION['documentosAta'];
        $arquivo = $documentos->edocatnome;
        foreach ($documentos as $documento) {
            if ($documento->cdocatsequ == $codigoDocumento) {
                HelperPitang::criarArquivoFromBytes($arquivo, $documento->idocatarqu);
                HelperPitang::baixarArquivo($arquivo);
                HelperPitang::deletarArquivo($arquivo);
                break;
            }
        }
    }

    public function gerarDadosAta()
    {
        $processo = $_REQUEST['processo'];
        $ano = $_REQUEST['ano'];
        $orgao = $_REQUEST['orgao'];
        $tipoAta = $_REQUEST['tipo'];
        $fornecedor = $_REQUEST['fornecedor'];
        
        $ataRPi = $this->getNegocio()->consultarAtaRegistroPreco($processo, $ano, $orgao, $fornecedor);
        $_SESSION['documentosAta'] = $this->getNegocio()->consultarDocumentosAta($ataRPi->carpnosequ);
    }

    public function plotarValoresAta($ataRPi)
    {
        $this->getTemplate()->NATA = $ataRPi->$this->getTemplate()->PROCESSO = $ataRPi->clicpoproc;
        $this->getTemplate()->ANO = $ataRPi->alicpoanop;
        $this->getTemplate()->DADOSFORNECEDOR = $ataRPi->earpinobje;
        $this->getTemplate()->OBJETO = $ataRPi->$this->getTemplate()->LISTA_DOCUMENTO = "";
        $this->getTemplate()->DATA = date("d/m/Y", strtotime($ataRPi->tarpindini));
        $this->getTemplate()->VIGENCIA = $ataRPi->aarpinpzvg;
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author rlfo
 *
 */
class Negocio extends BaseNegocio
{
    public function __construct()
    {
        $this->setDados(new Dados());
    }

    public function consultarDocumentosAta($ata)
    {
        $resultados = array();
        $sql = $this->getDados()->sqlConsultarDocumentosARPI($ata);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        
        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $documento;
        }
        return $resultados;
    }

    public function consultarAtaRegistroPreco($processo, $ano, $orgao, $fornecedor)
    {
        $sql = $this->getDados()->sqlconsultarAta($processo, $ano, $orgao, $fornecedor);
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        
        $resultado->fetchInto($ataRP, DB_FETCHMODE_OBJECT);
        return $ataRP;
    }
}

$app = new GUI();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

switch ($acao) {
    
    case 'Principal':
    default:
        $app->processarPrincipal();
        break;
}

echo $app->getTemplate()->show();
