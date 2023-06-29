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
 * @version   Git: $Id:$
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
class RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaInternaIncluir extends Dados_Abstrata
{

    /**
     * Consulta os órgãos
     */
    public function consultarOrgaos()
    {
        $repositorio = new Negocio_Repositorio_OrgaoLicitante();

        return $repositorio->consultaOrgaos();
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
class RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaInternaIncluir extends Negocio_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaInternaIncluir());
        return parent::getDados();
    }

    public function salvarOrgaosSessao($orgaos)
    {
        $orgaosNovoArray = array();
        foreach ($orgaos as $key => $value) {
            $orgaoArray = explode('!#', $value);
            $orgaoId    = $orgaoArray[0];
            $orgaoNome  = $orgaoArray[1];
            $orgaosNovoArray[$orgaoId] = $orgaoNome;
        }
        $_SESSION['Carona']['orgaos'] = $orgaosNovoArray;

        echo '<script>opener.document.CadAtaRegistroPrecoCaronaAtaInternaIncluir.submit()</script>';
        echo '<script>self.close()</script>';
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
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaInternaIncluir extends Adaptacao_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaInternaIncluir());
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
class RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaInternaIncluir extends UI_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaInternaIncluir());
        return parent::getAdaptacao();
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $tipoTela = $_REQUEST['tela'];

        $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoCaronaAtaInternaIncluir.html", ''));

        if (! empty($tipoTela) && $tipoTela = 'popup') {
            $this->setTemplate(new TemplatePortal("templates/CadAtaRegistroPrecoCaronaAtaInternaIncluir.html", ''));
        }
    }

    public function plotarBlocoOrgaos(array $orgaos)
    {
        if ($orgaos != null) {
            foreach ($orgaos as $key => $value) {
                $this->getTemplate()->VALOR_ORGAO_CODIGO    = $value->corglicodi;
                $this->getTemplate()->VALOR_ORGAO_DESCRICAO = $value->eorglidesc;
                $this->getTemplate()->block("BLOCO_ORGAOS");
            }
        }
    }
}

class CadAtaRegistroPrecoCaronaAtaInternaIncluir extends ProgramaAbstrato
{
    /**
     */
    private function processSalvar()
    {
        $orgaos = $_POST['OrgaoLicitanteCodigo'];

        $feedback
            = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->salvarOrgaosSessao($orgaos);

        // $feedback = $this->getUI()
        //     ->getAdaptacao()
        //     ->getNegocio()
        //     ->salvarDadosCarona();
        //
        // if ($feedback !== true) {
        //     $this->getUI()->mensagemSistema($feedback, 0, 0);
        //     return;
        // }
        //
        // $this->getUI()->mensagemSistema("Adicionado com sucesso", 1, 1);
    }

    /**
     */
    private function proccessPrincipal()
    {
        $orgaos
            = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaos();

        $this->getUI()->plotarBlocoOrgaos($orgaos);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaInternaIncluir());
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        $acao = filter_var($_POST['Botao'], FILTER_SANITIZE_STRING);

        switch ($acao) {
            case 'Salvar':
                $this->processSalvar();
                $this->proccessPrincipal();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoCaronaAtaInternaIncluir());
