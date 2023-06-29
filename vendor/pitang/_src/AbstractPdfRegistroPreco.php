<?php

/**
 */
abstract class AbstractPdfRegistroPreco
{
    private $pdf;

    /**
     * [__construct description].
     *
     * @param string $orientacao
     *            [description]
     * @param string $unidadeMedida
     *            [description]
     * @param string $formato
     *            [description]
     */
    public function __construct($orientacao = 'L', $unidadeMedida = 'mm', $formato = 'A4')
    {
        $this->configurarCabecalhoRodape();
        $this->pdf = new PDF($orientacao, $unidadeMedida, $formato);
        $this->configurarPdf();
    }

    /**
     * [getInstance description].
     *
     * @return [type] [description]
     */
    public function getInstance()
    {
        return $this->pdf;
    }

    /**
     * [configurarCabecalhoRodape description].
     *
     * @return [type] [description]
     */
    private function configurarCabecalhoRodape()
    {
        $GLOBALS['TituloRelatorio'] = $this->getTitulo();
        CabecalhoRodapePaisagem();
    }

    /**
     * [configurarPdf description].
     *
     * @return [type] [description]
     */
    private function configurarPdf()
    {
        $this->pdf->AliasNbPages();
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->AddPage();
        $this->pdf->SetFont('Arial', '', 7);
    }

    /**
     * Invoca os métodos da instância do objeto PDF presente na classe.
     *
     * @see http://php.net/manual/pt_BR/language.oop5.overloading.php#language.oop5.overloading.methods
     *
     * @param string $metodo
     * @param array $argumentos
     */
    public function __call($metodo, $argumentos = array())
    {
        if (empty($argumentos)) {
            return $this->pdf->$metodo();
        } else {
            $strValores = '';
            $valores = array_values($argumentos);

            foreach ($valores as $argumento) {
                $strValores .= "'" . $argumento . "' ,";
            }

            $strValores = substr($strValores, 0, - 2);

            return eval('return $this->pdf->' . $metodo . '(' . $strValores . ');');
        }
    }

    /**
     * Deve retornar o título do relatório.
     *
     * @return string Título do relatório
     */
    abstract public function getTitulo();

    /**
     * Deve implementar a visualização do relatório.
     */
    abstract public function gerarRelatorio();
}
