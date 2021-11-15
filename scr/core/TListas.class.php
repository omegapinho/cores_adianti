<?php
/**
 * TLista - Listagens simples
 * Copyright (c) 
 * @author  Fernando de Pinho Araújo
 * @version 1.0, 2019-08-14
 */
namespace Omegapinho\CoresAdianti

class TListas extends TTempo
{
/*-------------------------------------------------------------------------------
 *   Funçao retorna array meses
 *------------------------------------------------------------------------------- */
    public static function meses($param=null)
    {
        $meses = array (1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',
                        8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro');
        $meses = (null == $param) ? $meses : $meses[(int)$param];
        return $meses;
    }//Fim do Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array anos iniciando em 2014 e indo até 5 anos após ano atual
 *------------------------------------------------------------------------------- */
    public static function anos($param=null)
    {
        $ano = 2014;
        $ret = array();
        for ($ano; $ano<=(date('Y')+5); $ano++)
        {
            $ret[$ano] = (string) $ano;
        }
        return $ret;
    }//Fim do Modúlo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array anos iniciando em 2014 e indo até 5 anos após ano atual
 *------------------------------------------------------------------------------- */
    public static function semana($param = null)
    {
        $semana = array(
            '0' => 'Domingo', 
            '1' => 'Segunda-Feira',
            '2' => 'Terca-Feira',
            '3' => 'Quarta-Feira',
            '4' => 'Quinta-Feira',
            '5' => 'Sexta-Feira',
            '6' => 'Sabado'
        );
        $semana = (null == $param) ? $semana : $semana[$param];
        return $semana;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com os níveis de acesso
 *------------------------------------------------------------------------------- */
    public static function nivel_acesso($param=null)
    {
        $ret = array(10 => 'VISITADOR',
                     50 => 'OPERADOR',
                     80 => 'GESTOR',
                     90 => 'ADMINISTRADOR',
                    100 => 'DESENVOLVEDOR');
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com os níveis de acesso a dados
 *------------------------------------------------------------------------------- */
    public static function nivel_acesso_dados($param=null)
    {
        $ret = array('PUBLICO'  => 'PÚBLICO',
                     'RESTRITO' => 'RESTRITO A OPM',
                     'SIGILOSO' => 'RESTRITO A QUEM INCLUIU'
                     );
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com os níveis de acesso
 *------------------------------------------------------------------------------- */
    public static function system_group_status($param=null)
    {
        $ret = array('C' => 'GERAL - USUÁRIO PODE REDISTRIBUIR',
                     'E' => 'EXCLUSIVO - USUÁRIO NÃO PODE RESISTRIBUIR',
                     'S' => 'SISTEMA - ATRIBUIDO AUTOMÁTICAMENTE PELA APLICAÇÃO');
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna sim e não com base em f => false e t => true
 *------------------------------------------------------------------------------- */
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
        return self::sim_nao($ret);
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna sim e não com base em f => false e t => true
 *------------------------------------------------------------------------------- */
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
        return self::sim_nao($ret);
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com sim e não
 *------------------------------------------------------------------------------- */
    public static function sim_nao($param=null)
    {
        $ret = array(
            'S' => 'SIM', 
            'N' => 'NÃO');
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com onde Usar no sis-cate
 *------------------------------------------------------------------------------- */
    public static function onde_usar($param=null)
    {
        $ret = array(
            'D' => 'Documento PDF', 
            'I' => 'Imagem JPG/PNG');
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com sim e não
 *------------------------------------------------------------------------------- */
    public static function tipo_feriado($param=null)
    {
        $ret = array(
            'NACIONAL'      => 'Feriado Nacional/Estadual', 
            'MUNICIPAL'     => 'Feriado Municipal',
            'INSTITUCIONAL' => 'Feriado ou comemoração da Instituição');
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com Tipos de Campos para configuraForm
 *------------------------------------------------------------------------------- */
    public static function tipo_campo_configura($param=null)
    {
        $ret = array(
                     'E' => 'Entrada de Texto',
                     'C' => 'Caixa de Combo',
                     'S' => 'Caixa de Seleção',
                     'D' => 'Calendário',
                     'T' => 'Caixa de Texto',
                     'H' => 'Edição de Texto');
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array tipo de item para entrada de pesquisa
 *------------------------------------------------------------------------------- */
    public static function tipo_item($param=null)
    {
        $ret = array('CB'=>'COMBO',
                     'EN'=>'ENTRADA DE TEXTO',
                     'DT'=>'DATA',
                     'NM'=>'CAMPO TIPO NÚMERICO',
                     );
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com os tipos de BOL existentes
 *------------------------------------------------------------------------------- */
    public static function tipo_boletim($param=null)
    {
        $ret = array(
            '7' => 'GERAL', 
            '8' => 'GERAL RESERVADO',
            '5' => 'INTERNO',
            '6' => 'INTERNO RESERVADO',
            '1' => 'REGIONAL',
            '2' => 'REGIONAL RESERVADO'
            );
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo

/*-------------------------------------------------------------------------------
 *   Funçao retorna array com estado de conservação
 *------------------------------------------------------------------------------- */
    public static function conservacao($param=null)
    {
        $ret = array(
            'N' => 'NOVO', 
            'O' => 'ÓTIMO',
            'MB'=> 'MUITO BOM',
            'B' => 'BOM',
            'R' => 'REGULAR',
            'P' => 'PESSÍMO', //OU RUIM/DANIFICADO
            'I' => 'INSERVÍVEL',
            'E' => 'EXTRAVIADO');
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com os sexos
 *------------------------------------------------------------------------------- */
    public static function sexo($param=null)
    {
        $ret = array('M'=>'MASCULINO',
                     'F'=>'FEMININO'
                     );
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array com estados civis
 *------------------------------------------------------------------------------- */
    public static function estadocivil($param=null)
    {
        $ret = array('S'=>'SOLTEIRO',
                     'C'=>'CASADO',
                     'D'=>'DIVORCIADO',
                     'V'=>'VIÚVO',
                     'A'=>'AMAZIADO',
                     'O'=>'OUTROS'
                     );
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array opções de Tipos de Sangue
 *------------------------------------------------------------------------------- */
    public static function tipos_sangue($param=null)
    {
        $ret = array(
            'A' => 'A', 
            'B' => 'B',
            'O' => 'O',
            'AB' => 'AB'
            );
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *   Funçao retorna array opções de social plano de saúde
 *------------------------------------------------------------------------------- */
    public static function tipos_rh($param=null)
    {
        $ret = array(
            'P' => 'POSITIVO', 
            'N' => 'NEGATIVO',
            'I' => 'INDEFINIDO'
            );
        $ret = (null == $param) ? $ret : $ret[$param];
        return $ret;
    }//Fim Módulo
}//Fim Classe
