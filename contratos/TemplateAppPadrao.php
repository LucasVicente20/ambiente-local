<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.13.0-23-gde560ac
 *
 * HISTORICO DE ALTERAÇÔES
 * -----------------------------------------------------------------------
 *  Alterado: Pitang Agile TI
 *  Data:     13/03/2015
 *  Objetivo: Bug CR 109036 - Legislação Consultar - link com problema
 *  Versão:   v1.29.3-2-g6a5621a
 */
if (!@require_once dirname(__FILE__).'/TemplateApp.class.php') {
    throw new Exception('Error Processing Request - TemplateApp.class.php', 1);
}


/**
 * TemplateAppPadrao.
 */
class TemplateAppPadrao extends TemplateApp
{
    /**
     * Constroi o link da home.
     *
     * @return Element
     */
    private function buildLinkHome()
    {
        $linkHome = new Element('a');
        $linkHome->set('href', '../app/home.php');
        $linkHome->set('class', 'active');
        $linkHome->set('text', 'Início');

        return $linkHome;
    }

    /**
     * Remove caracteres especiais da Descricao.
     *
     * @param string $descricao
     *                          palavra que deve ser tratada
     *
     * @return string palavra informada tratada
     */
    private function trataDescricao($descricao)
    {
        return str_replace(' ', '', str_replace('.', '', str_replace('ç', 'c', str_replace('õ', 'o', $descricao))));
    }

    /**
     * Constroi o menu.
     *
     * @return Element [description]
     */
    private function buildMenu()
    {
        $menu = new Element('ul');
        $menu->set('class', 'nav');

        $linkHome = $this->buildLinkHome();

        $liHome = new Element('li');
        $liHome->inject($linkHome);
        $menu->inject($liHome);

        return $menu;
    }

    /**
     * Constroi o menu do rodape da pagina.
     *
     * @param int $sizeMenuList
     *                          [description]
     * @param
     *            integer &$cont [description]
     * @param array $value
     *                     $value['descricao']
     * @param
     *            string &$menuFooter [description]
     */
    private function buildMenuFooter($sizeMenuList, &$cont, $value, &$menuFooter)
    {
        $elAFooter = new Element('a');
        $elAFooter->set('text', $value['descricao']);
        $elAFooter->set('href', $this->trataDescricao($value['descricao']).'.php');
        $elLIFooter = new Element('li');
        $elLIFooter->inject($elAFooter);
        $menuFooter .= $elLIFooter->build();
        if ($cont < $sizeMenuList - 1) {
            $elLISeparator = new Element('li');
            $elLISeparator->set('class', 'separator');
            $elLISeparator->set('text', '<i class="circle"></i>');
            $menuFooter .= $elLISeparator->build();
        }
        ++$cont;
    }

    /**
     * [getElemLink description].
     *
     * @param [type] $descricao
     *                          [description]
     *
     * @return [type] [description]
     */
    private function getElemLink($descricao)
    {
        $span = new Element('span');
        $span->set('class', 'caret');

        $link = new Element('a');

        $link->set('text', $descricao);
        $link->inject($span);
        $link->set('class', 'dropdown-toggle dropdown-hover');
        $link->set('data-toggle', 'dropdown');

        return $link;
    }

    /**
     * [getArrayItemAlterar description].
     *
     * @return [type] [description]
     */
    private function getArrayItemAlterar()
    {
        // [CUSTOMIZAÇÃO] - Adicionado para atender issue #129
        return array(
            'Institucional' => 'Consultar',
            'Fornecedores' => 'Cadastro',
            'Licitações' => 'Consultar',
        );
        // [/CUSTOMIZAÇÃO]
    }

    /**
     * Build menu main.
     */
    private function buildMenuPrincipal()
    {
        $menuList = MenuFactory::getMenuList();
        $menu = $this->buildMenu();

        if (!is_array($menuList)) {
            throw new DomainException('List not found');
        }
        $acumHtml = '';
        $menuFooter = '';
        $sizeMenuList = count($menuList);
        $cont = 0;

        foreach ($menuList as $value) {
            $li = new Element('li');

            $elDiv = new Element('div');
            $elDiv->set('class', 'span2');

            $elH2 = new Element('h2');
            $elA = new Element('a');

            $elA->set('href', $this->trataDescricao($value['descricao']).'.php');
            $elA->set('text', $value['descricao']);
            $elH2->inject($elA);
            $elDiv->inject($elH2);
            $elUL = new Element('ul');
            $elUL->set('class', 'unstyled');

            if (isset($value['filho'])) {
                // ul
                $ul = new Element('ul');
                // a
                $a = $this->getElemLink($value['descricao']);
                $li->inject($a);
                foreach ($value['filho'] as $keyf => $filho) {
                    $ulf = new Element('ul');
                    $lif = new Element('li');
                    $af = new Element('a');
                    $af->set('text', '<i class="arrow-right"></i>'.$filho['descricao']);
                    if (isset($value['filho'][$keyf]['filho'])) {
                        $lif->inject($af);
                        $lif->set('class', 'dropdown-submenu');
                        $liff = new Element('li');
                        foreach ($value['filho'][$keyf]['filho'] as $keyf1 => $filho1) {
                            $aff = new Element('a');
                            $aff->set('href', $filho1['url']);
                            $aff->set('text', $filho1['descricao']);
                            $liff->inject($aff);
                            $elAChild = new Element('a');
                            $elAChild->set('href', $filho1['url']);
                        }
                        $ulf->set('class', 'dropdown-menu');
                        $ulf->inject($liff);
                        $lif->inject($ulf);
                        $ul->inject($lif);
                    } else {
                        $af->set('href', $value['filho'][$keyf]['url']);
                        $lif->inject($af);
                        $ul->inject($lif);
                    }
                    $lif->inject($ulf);
                    if (isset($value['filho'][$keyf]['filho'])) {
                        foreach ($value['filho'][$keyf]['filho'] as $keyf2 => $filho2) {
                            $elLI = new Element('li');
                            $elAChild = new Element('a');
                            $elAChild->set('href', $value['filho'][$keyf]['filho'][$keyf2]['url']);
                            // [CUSTOMIZAÇÃO] - Ajuste para atender issue #129
                            $chaveExisteNoArray = array_key_exists($value['descricao'], $this->getArrayItemAlterar());
                            $valorEstaNoArray = in_array($value['filho'][$keyf]['filho'][$keyf2]['descricao'], $this->getArrayItemAlterar());
                            $descricaoItemMenu = $value['filho'][$keyf]['filho'][$keyf2]['descricao'];
                            if ($chaveExisteNoArray && $valorEstaNoArray) {
                                $descricaoItemMenu = $value['filho'][$keyf]['descricao'];
                            }
                            $elAChild->set('text', $descricaoItemMenu);
                            // [/CUSTOMIZAÇÃO]
                            $elLI->inject($elAChild);
                            $elUL->inject($elLI);
                        }
                    } else {
                        $elLI = new Element('li');
                        $elAChild = new Element('a');
                        $elAChild->set('text', $value['filho'][$keyf]['descricao']);
                        $elAChild->set('href', $value['filho'][$keyf]['url']);
                        $elLI->inject($elAChild);
                        $elUL->inject($elLI);
                    }
                }
                $ul->set('class', 'dropdown-menu');
                $li->inject($ul);
                $li->set('class', 'dropdown');
                $menu->inject($li);
                $elDiv->inject($elUL);
                $acumHtml .= $elDiv->build();
            } else {
                if ($_SESSION['_cgrempcodi_'] == 0 && $value['descricao'] != 'Fechar Sessão') {
                    $a = new Element('a');
                    $a->set('href', $value['url']);
                    $a->set('text', $value['descricao']);
                    $li->inject($a);
                    $menu->inject($li);
                }
            }
            unset($li);
            $this->buildMenuFooter($sizeMenuList, $cont, $value, $menuFooter);
        }
        $this->MENU_DINAMICO = $menu->build();
        $this->MAPA_SITE = $acumHtml;
        $this->MENU_FOOTER = $menuFooter;
    }

    /**
     * [definirAmbienteAplicacao description].
     */
    protected function definirAmbienteAplicacao()
    {
        $descricaoAmbiente = str_replace('_', ' ', strtoupper2($GLOBALS['LOCAL_SISTEMA']));
        $imgDefinicaoAmbiente = '';

        if (strpos($GLOBALS['LOCAL_SISTEMA'], CONST_NOMELOCAL_DESENVOLVIMENTO) !== false) {
            $imgDefinicaoAmbiente = '../midia/desenvolver.JPG';
        }

        if ($GLOBALS['LOCAL_SISTEMA'] === CONST_NOMELOCAL_HOMOLOGACAO) {
            $imgDefinicaoAmbiente = '../midia/homologa.JPG';
        }

        if (!empty($imgDefinicaoAmbiente)) {
            $this->IMG_DEFINICAO_AMBIENTE = $imgDefinicaoAmbiente;
            $this->DESCRICAO_AMBIENTE = $descricaoAmbiente;
            $this->block('BLOCO_DEFINICAO_AMBIENTE');
        }
    }

    /**
     * Construct of class.
     *
     * @param string $file
     * @param string $form
     */
    public function __construct($file, $form = '')
    {
        parent::__construct('../app/templates/layout-default.html');
        $this->buildMenuPrincipal();
        $this->FORM = $form;
        $this->BLOCK('BLOCK_FORM');
        $this->addFile('CORPO', $file);
        $this->BASE_URL = "http://".$_SERVER['HTTP_HOST'].str_replace('contratos','app/',dirname($_SERVER['REQUEST_URI']));
        $this->definirAmbienteAplicacao();
    }
}
