<?php
// 220038--
/**
 * @author jfranciscos4
 *
 */
interface Negocio_Entidade_Especificao_Interface
{

    /**
     * Field é o campo do Formulário
     *
     * @return string
     */
    public function getField();

    public function getText();

    public function getHref();

    public function getRequired();

    public function getValidation();

    public function getSanitize();
}