<?php
namespace Omegapinho\CoresAdianti;
/**
 * TCBasic - Componentes do tipo básico
 * @author  Fernando de Pinho Araújo
 * @package Core
 * @version 1.0 2021-10-19
 */

class TCBasic extends core_componente
{
    //Armazena a instância
    protected static $instancia;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        //set_time_limit(60);
    }
/**
 *    Instância Singleton
 **/
    public static function instancia()
    {
        if (empty(self::$instancia))
        {
            self::$instancia = new self();
        }
        return self::$instancia;
    }//Fim Método
/**
 * Componente Tipo: id
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho
 * @param  $default => valor padrão
 * @return Componente
 **/
    public static function cId($nome    = 'id',
							   $tam     = '100%',
							   $default = false)
    {
        $componente = new TEntry($nome);
        $componente = self::defineBasico($componente,$tam,$default,'numero');
        $componente->setMask('99999999999999999');
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: numero
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho do campo 
 * @param  $default => valor padrão
 * @param  $mask    => mascara e limite de entrada
 * @return Componente
 **/
    public static function cNumero($nome    = 'numero',
								   $tam     = '100%',
								   $default = false, 
								   $mask    = '99999999999999')
    {
        $componente = new TEntry($nome);
        $componente = self::defineBasico($componente,$tam,$default,'numero');
        //$componente->setMask($mask,true);
        $componente->onKeyUp = self::defineMascaraMonetaria('','');
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Data
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho do objeto
 * @param  $default => true/false (se true define o valor padrão a data de hoje)
 * @return Componente
 * @observation Se usado em campo de listagem, certificar refornatar no onSearch para date2us
 **/
    public static function cData($nome    = 'data',
							     $tam  	  = '100%',
								 $default = false)
    {
        $componente = new TDate($nome);
        $componente = self::defineBasico($componente,$tam,false,'combo');
        if ($default === true)
        {
            $componente->setValue(date('d/m/Y'));
        }
        else if ($default !== false)
        {
            $componente->setValue($default);
        }
        $componente->setMask('dd/mm/yyyy');
        $componente->setDatabaseMask('yyyy-mm-dd');
        
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Data Time
 * @param  $nome => objeto a ser marcado
 * @param  $tam  => tamanho do objeto
 * @param  $default true/false (se true define o valor padrão a data de hoje)
 * @return Componente
 * @observation Se usado em campo de listagem, certificar refornatar no onSearch para date2us
 **/
    public static function cDataTempo($nome    = 'data',
									  $tam 	   = '100%',
									  $default = false)
    {
        $componente = new TDateTime($nome);
        $componente = self::defineBasico($componente,$tam,false,'combo');
        if ($default === true)
        {
            $componente->setValue(date('d/m/Y hh:ii'));
        }
        else if ($default !== false)
        {
            $componente->setValue($default);
        }
        $componente->setMask('dd/mm/yyyy hh:ii');
        $componente->setDatabaseMask('yyyy-mm-dd hh:ii');
        
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Entrada de Text
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => Array com cumprimento(H) e altura(V)
 * @param  $default => Valor padrão
 * @return Componente
 **/
    public static function cText($nome    = 'texto',
								 $tam     = ['H'=>'100%','V'=>48], 
								 $default = false )
    {
        $componente = new TText($nome);
        $tam_h = $tam['H'] ?? '85%';
        $tam_v = $tam['V'] ?? 48;
        $componente = self::defineBasico($componente,0,$default,'combo');
        $componente->setSize($tam_h,$tam_v);
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Entrada de string
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho
 * @param  $default => valor padrão
 * @return Componente
 **/
    public static function cString($nome    = 'nome',
								   $tam     = '100%',
								   $default = false)
    {
        $componente = new TEntry($nome);
        $componente = self::defineBasico($componente,$tam,$default,'combo');
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Entrada de HTML
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho
 * @param  $default => valor padrão
 * @param  $tipo    => componentes presentes [default,no_toolbar,text_edit,text_picture_edit
 * @return Componente
 **/
    public static function cHTMLEditor($nome    = 'texto',
                                       $tam     = ['H'=>'100%','V'=>48], 
                                       $default = false, 
                                       $tipo    = 'text_edit' )
    {
        $componente = new THtmlEditor($nome);
        $tam_h = $tam['H'] ?? '85%';
        $tam_v = $tam['V'] ?? 48;
        $componente = self::defineBasico($componente,0,$default,'combo');
        $componente->setSize($tam_h,$tam_v);
        $componente->setOption('toolbar', TListas::html_editor($tipo) );
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Entrada de string oculto
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho(não usado)
 * @param  $default => valor padrão
 * @return Componente
 **/
    public static function cHidden($nome    = 'nome',
								   $tam     = false,
								   $default = false)
    {
        $componente = new THidden($nome);
        $componente->setValue($default);
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: numero decimal
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho
 * @param  $default => valor padrão
 * @return Componente
 **/
    public static function cNumeroDecimal($nome    = 'numero',
										  $tam     = '100%', 
										  $default = false)
    {
        $componente          = new TEntry($nome);
        $componente          = self::defineBasico($componente,$tam,$default,'numero');
        $componente->onKeyUp = self::defineMascaraMonetaria();
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Entrada de string para Password
 * @param  $nome    => objeto a ser marcado
 * @param  $tam     => tamanho
 * @param  $default => valor padrão
 * @return Componente
 **/
    public static function cPassword($nome    = 'password',
									 $tam     = '100%',
									 $default = false)
    {
        $componente = new TPassword($nome);
        $componente = self::defineBasico($componente,$tam,$default,'combo');
        return $componente;
    }//Fim Método
/**
 * Componente Tipo: Destaca Labels
 * @param  $label => texto a destacar
 * @param  $color => cor do texto
 * @param  $style => estilo 
 * @return Componente
 **/
    public static function cDestaque($label, 
									 $color = 'red', 
									 $style = null )
    {
        $componente = new TLabel($label);
        $componente->setFontColor($color);
        if (!empty($style))
        {
            $componente->setFontStyle($style);
        }
        return $componente;
    }//Fim Método
}//Fim Classe
