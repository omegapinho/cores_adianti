<?php
/*------------------------------------------------------------------------------
 *    Rotinas relativos a filtros especiais
 *------------------------------------------------------------------------------*/
namespace Omegapinho\CoresAdianti

class TFiltrex
{
/**
 *  DESCRICAO: retorna filtro especial de localização de texto.
 * o Texto é convertido em caixa baixa e remove os acentos na pesquisa
 * @param $campo nome da coluna da tabela a procurar
 * @param $operador lógica de comparação
 * @param $param o dado a procurar
 **/
    public static function texto_match ($campo, $operador = 'LIKE', $param) 
    {
        $retorno = new TFilter("LOWER ( sem_acentos($campo) )", 
                                   "$operador", 
                                   "NOESC:LOWER (sem_acentos('$param') )");
        return $retorno;
    }//Fim Módulo
/**
 *  DESCRICAO: retorna filtro especial de localização de usando um dos elementos
 * da tabela para buscar.
 * @param $indice o Id procurado por referência
 * @param $tabela nome da tabela
 * @param $condicao filtro de pesquisa 
 **/
    public static function match_indice ($indice, $tabela, $condicao = "id > 0") 
    {
        $sql     = "(SELECT $indice FROM $tabela WHERE $condicao )";
        $retorno = new TFilter("id", "IN", $sql);
        return $retorno;
    }//Fim Módulo
}//Fim Classe