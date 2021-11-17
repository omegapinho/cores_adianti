<?php
namespace Omegapinho\CoresAdianti;
/**
 * TLista - Listagens simples
 * Copyright (c) 
 * @author  Fernando de Pinho Araújo 
 * @version 1.0, 2020-11-15
 **/

class TListas extends TTempo
{
/**
 * @description  Listas pré-cadastradas
 * @param  $param => lista a pesquisar
 * @return array indicada ou um array vazio se não achar
 **/
    private static function listas($param)
    {
        $items = [ 'meses' => [ 1  => 'Janeiro',
								2  => 'Fevereiro',
								3  => 'Março',
								4  => 'Abril',
								5  => 'Maio',
								6  => 'Junho',
								7  => 'Julho',
								8  => 'Agosto',
								9  => 'Setembro',
								10 => 'Outubro',
								11 => 'Novembro',
								12 =>'Dezembro'],
					'sexo' => [ 'M' => 'MASCULINO',
								'F' => 'FEMININO'],
					'semana' => [ '0' => 'Domingo', 
								  '1' => 'Segunda-Feira',
								  '2' => 'Terça-Feira',
								  '3' => 'Quarta-Feira',
								  '4' => 'Quinta-Feira',
								  '5' => 'Sexta-Feira',
								  '6' => 'Sábado' ],
					'sim_nao' => [ 'S' => 'SIM', 
								   'N' => 'NÃO' ],
					'anos'    => self::anos(),
					'nivel_acesso' => [ 10  => 'VISITADOR',
										50  => 'OPERADOR',
										80  => 'GESTOR',
										90  => 'ADMINISTRADOR',
										100 => 'DESENVOLVEDOR'],
					'false_true'      => self::false_true(),
					'false_true_bool' => self::false_true_bool(),
					'tipo_campo' => ['E' => 'Entrada de Texto',
									 'C' => 'Caixa de Combo',
									 'S' => 'Caixa de Seleção',
									 'D' => 'Calendário',
									 'T' => 'Caixa de Texto Simples',
									 'H' => 'Edição de Texto (Tags de HTML)'],
					'conservacao' => ['N'  => 'NOVO', 
									  'O'  => 'ÓTIMO',
									  'MB' => 'MUITO BOM',
									  'B'  => 'BOM',
									  'R'  => 'REGULAR',
									  'P'  => 'PESSÍMO', //OU RUIM/DANIFICADO
									  'I'  => 'INSERVÍVEL',
									  'E'  => 'EXTRAVIADO'],
					'estado_civil' => ['S' => 'SOLTEIRO',
							 		   'C' => 'CASADO',
									   'D' => 'DIVORCIADO',
									   'V' => 'VIÚVO',
									   'A' => 'AMAZIADO',
									   'O' => 'OUTROS'],
					'tipo_sangue' => [  'A'  => 'A', 
										'B'  => 'B',
										'O'  => 'O',
										'AB' => 'AB'],
					'tipo_rh' => [  'P' => 'POSITIVO', 
									'N' => 'NEGATIVO',
									'I' => 'INDEFINIDO'],
					'style'   => [ 'combo'  => 'padding-top: 0px;margin-top: 1px;margin-bottom: 1px;text-align: left;',
                                   'numero' => 'padding-top: 0px;margin-top: 1px;margin-bottom: 1px;text-align: right;']
					
				 ];
		if (array_key_exists($param,$items))
		{
			return $items[$param];
		}
		else
		{
			return [];
		}
    }//Fim do Método
/**
 *   Funçao retorna array anos iniciando em 2014 e indo até 5 anos após ano atual
 **/
    private static function anos()
    {
        $ano = 2014;
        $ret = array();
        for ($ano; $ano<=(date('Y')+5); $ano++)
        {
            $ret[$ano] = (string) $ano;
        }
        return $ret;
    }//Fim do Método
/**
 * Funçao retorna sim e não com base em f => false e t => true (string)
 * @param $valor => item a pesquisar
 * @return uma array ou o elemento da lista
 **/
    public static function false_true($param = null)
    {
        $ret = array(
            't' => 'SIM', 
            'f' => 'NÃO');
        if (null == $param)
        {
            return $ret ;
        }
        $ret = ($param == 't' || $param == 'T') ? 'S' : 'N';
        return self::listas('sim_nao',$ret);
    }//Fim Método
/**
 * Funçao retorna sim e não com base em f => false e t => true (boolean)
 * @param $valor => item a pesquisar
 * @return uma array ou o elemento da lista
 **/
    public static function false_true_bool($param = null)
    {
        $ret = array(
            true  => 'SIM', 
            false => 'NÃO');
        if (null == $param)
        {
            return $ret ;
        }
        $ret = ($param) ? 'S' : 'N';
        return self::listas('sim_nao',$ret);
    }//Fim Método
/**
 * Função que retorna a lista ou o elemento desta
 * @param $lista => indice da lista
 * @param $valor => item a pesquisar
 * @return uma array ou o elemento da lista
 **/
	public static function getLista($lista,$valor = false)
	{
		$item = self::listas($lista);
		if ($valor && array_key_exists($valor,$item))
		{
			return $item[$valor];
		}
		else if ($valor == false)
		{
			return $item;
		}
		else
		{
			return '';
		}
	}//Fim Método
}//Fim Classe
