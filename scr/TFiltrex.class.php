<?php
/**
 * TFiltrex - Rotinas relativos a filtros especiais
 * Copyright (c) 
 * @author  Fernando de Pinho Araújo 
 * @version 1.0, 2020-11-15
 * @require Precisa criar rotina sem_acentos no BD Postgres(Ver no final)
 **/
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
/**
 * Script PGSQL para criação da função sem_acentos
 * Executar o script dentro do schemma public
 * url: https://bragil.wordpress.com/2008/02/26/postgresql-funcao-para-remover-acentuacao/
 **/
 
 /**
 
CREATE OR REPLACE FUNCTION sem_acentos(character varying)
RETURNS character varying AS
$BODY$
SELECT translate($1, 'áéíóúàèìòùãõâêîôôäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ', 'aeiouaeiouaoaeiooaeioucAEIOUAEIOUAOAEIOOAEIOUC')
$BODY$
LANGUAGE 'sql' VOLATILE;
 
 **/
 /**
 * Script PGSQL para criação da função sem_acentos incluindo consoantes
 * Executar o script dentro do schemma public
 * url: https://devtools.com.br/blog/retirando-acentuacao-no-postgresql/
 **/
 /**
 
  CREATE OR REPLACE FUNCTION retira_acentuacao(p_texto text)  
  RETURNS text AS  
 $BODY$  
 Select translate($1,  
 'áàâãäåaaaÁÂÃÄÅAAAÀéèêëeeeeeEEEÉEEÈìíîïìiiiÌÍÎÏÌIIIóôõöoooòÒÓÔÕÖOOOùúûüuuuuÙÚÛÜUUUUçÇñÑýÝ',  
 'aaaaaaaaaAAAAAAAAAeeeeeeeeeEEEEEEEiiiiiiiiIIIIIIIIooooooooOOOOOOOOuuuuuuuuUUUUUUUUcCnNyY'   
  );  
 $BODY$  
 LANGUAGE sql VOLATILE  
 COST 100; 
 
 **/
 
}//Fim Classe