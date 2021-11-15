<?php
/**
 * TBuscaFonetica - Ferramentas para implementar a busca fonï¿½tica
 * Copyright (c) 
 * @author  Fernando de Pinho Araújo 
 * @version 1.0, 2020-11-15
 *   ---------------------------------------------------------------------------
 *   Baseado no Algoritimo de FRED JORGE TAVARES DE LUCENA
 *   http://www.unibratec.com.br/jornadacientifica/diretorio/NOVOB.pdf
 *   E-mail: Fred.lucena@unibratec.com.br
 *   URL: https://pastebin.com/f1819c394 
 **/
namespace Omegapinho\CoresAdianti

class TBuscaFonetica
{
/*-------------------------------------------------------------------------------
 *    Realiza a pesquisa fonética de frases ou palavras
 *------------------------------------------------------------------------------- */
    public static function Fonetica( $nome, $delimitador = false)
    {
        //Verifica se é uma frase com várias palavras (separadas por espaço)
        if (strpos($nome," ") !== false)
        {
            //Cria um array de palavras
            $nome1 = explode(" ",$nome);
            //Passa o valor da array por referência de forma que o retorno do SoudexBR vai direito para a array
            //O Valor de referência é tratado como se fosse a própria varíavel, ou seja
            //Seu valor é alterado normalmente
            foreach($nome1 as &$val)
            {
                //Avalia palavra por palavra
                $val = self::portuguese_metaphone($val);
            }
            //Junta todas palavrar do array numa string são para retorno
            $nome1 = implode(" ",$nome1);
        }
        else
        {
            $nome1 = self::portuguese_metaphone($nome);
        }
        //$nome1 = self::portuguese_metaphone($nome,250);
        $nome1 = trim($nome1);
        if ($delimitador)
        {
            $nome1 = "/ $nome1 /";
        }
        else
        {
            $nome1 = " $nome1 ";
        }
        return $nome1;
    }//Fim Método
/*-------------------------------------------------------------------------------
 *    Conversão fonética
 *------------------------------------------------------------------------------- */
    public function SoundexBR ($string)
    {
        $string = trim($string);
        
        //2. Remove acentos
        $fer = TFerramentas::instancia();
        $string = $fer->removeAcentos($string);
        //1. Converte minuscula
        $string = utf8_decode($string);
        $string = strtolower($string);
        $string = utf8_encode($string);

        $arr = array(
        "bl"=>"b",//4
        "br"=>"b",//4
        "ca"=>"k",//8
        "ce"=>"s",
        "ci"=>"s",
        "co"=>"k",//8
        "cu"=>"k",//8
        "ck"=>"k",//8
        "ï¿½"=>"s",//13
        "ch"=>"s",//13
        "ct"=>"t",
        "ge"=>"j",
        "gi"=>"j",
        "gm"=>"m",
        "gl"=>"g",
        "gr"=>"g",
        "l"=>"r",
        "n"=>"m",
        "md"=>"m",
        "mg"=>"g",
        "mj"=>"j",
        "ph"=>"f",
        "pr"=>"p",
        "q"=>"k",
        "rg"=>"g",
        "rs"=>"s",
        "rt"=>"t",
        "rm"=>"sm",
        "rj"=>"j",
        "st"=>"t",
        "tr"=>"t",
        "tl"=>"t",
        "ts"=>"s",
        "w"=>"v",
        "x"=>"s",
        "st"=>"t",
        "y"=>"i",
        "z"=>"s"
        );
        $string = strtr($string,$arr);

        if (substr($string,-2) == "ao") $string = substr($string,0,-2)."m";//10
        //Terminados em  SzRMNAOL
        if (substr($string,-1) == "s")  $string = substr($string,0,-1);//16
        if (substr($string,-1) == "z")  $string = substr($string,0,-1);//16
        if (substr($string,-1) == "r")  $string = substr($string,0,-1);//16
        if (substr($string,-1) == "r")  $string = substr($string,0,-1);//16
        if (substr($string,-1) == "m")  $string = substr($string,0,-1);//16
        if (substr($string,-1) == "n")  $string = substr($string,0,-1);//16
        if (substr($string,-2) == "ao") $string = substr($string,0,-2);//16
        if (substr($string,-1) == "l")  $string = substr($string,0,-1);//16
        $arr = array(
        "r"=>"1",//17
        "h"=>"",//18
        "a"=>"",//18
        "e"=>"",//18
        "i"=>"",//18
        "o"=>"",//18
        "u"=>"",//18
        "aa"=>"a",//19
        "bb"=>"b",
        "cc"=>"c",
        "dd"=>"d",
        "ee"=>"e",
        "ff"=>"f",
        "gg"=>"g",
        "hh"=>"h",
        "ii"=>"i",
        "jj"=>"j",
        "kk"=>"k",
        "ll"=>"l",
        "mm"=>"m",
        "nn"=>"n",
        "oo"=>"o",
        "pp"=>"p",
        "qq"=>"q",
        "rr"=>"r",
        "ss"=>"s",
        "tt"=>"t",
        "uu"=>"y",
        "vv"=>"v",
        "ww"=>"w",
        "xx"=>"x",
        "yy"=>"y",
        "zz"=>"z"//19
        );
        $string = strtr($string,$arr);

        return $string;
    }//Fim Método
    
/*
 *	pt_metaphone() "Portuguese Metaphone"
 *	version 1.0
 *
 *	Essa função pega uma palavra em português do Brasil e a retorna
 *	em uma chave metafônica.
 *
 *	Copyright (C) 2008		Prefeitura Municipal de Várzea Paulista
 *							<metaphone@varzeapaulista.sp.gov.br>
 *
 *	Histórico:
 *	2008-05-20		Versão 1.0
 *					Initial Release
 *
 *	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 *
 *	RECONHECIMENTO :
 *
 *	Essa função foi adaptada de uma função  chamada spanish_metaphone
 *	do Israel J. Sustaita. O código fonte original pode ser obtido em
 *	http://www.geocities.com/isloera/spanish_methaphone.txt (baseada
 *	na versão original do DoubleMetaphone em inglês de Geoff Caplan).
 *
 *	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 *
 *	AGRADECIMENTOS :
 *	Sílvia e Thaiza pela ajuda com a língua portuguesa
 *
 *	EQUIPE DE DESENVOLVIMENTO :
 * 		Rodrigo Domingos Pinto Lotierzo
 *		Giovanni dos Reis Nunes
 *
 * 		Estagiários:
 * 		  Caio Varlei Righi Schleich
 *		  Diego Jorge de Souza
 *		  Sueli Silvestre da Silva
 *
 *	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 *
 *	Funcionamento:
 *
 *	1.	Esta função recebe a string contendo o nome, palavra ou frase
 *		a ser criada a chave e retorna essa chave.
 *
 *	2.	Receber a 'string', a primeira coisa a ser feita é substituir
 *		os dígrafos e encontros consonantais pelas letras que corres-
 *		pondem aos seus sons.
 *
 *	3.	Os dígrafos "LH", "NH" e o encontro consonantal "RR" são con-
 *		vertidos em números para facilitar a interpretação.
 *
 *	4.	Os dígrafos "CH" e "PH" (questões históricas), o encontro con-
 *		sonantal "SC" e o "Ç" são convertidos em seus fonemas corres-
 *		pondentes.
 *
 *	5.	Os acentos são removidos das vogais.
 *
 *	6.	Letras cujos fonemas não se alteram não são mexidas ("B", "V",
 *		"P",etc...).
 *
 * 	7.	Outras letras como "G" e "X" são tratadas de acordo com casos
 *		específicos.
 */

    public static function portuguese_metaphone($STRING, $LENGTH = 50)
    {
        
        //$STRING = utf8_decode($STRING);
        //$STRING = strtolower($STRING);
        //$STRING = utf8_encode($STRING);        
        $STRING = strtolower($STRING);
        /*
         *    inicializa a chave metafï¿½nica
         */
        $META_KEY = "";
    
        /*
         *    configura o tamanho mï¿½ximo da chave metafï¿½nica
         */
        $KEY_LENGTH = (int)$LENGTH;
    
        /*
         *    coloca a posiï¿½ï¿½o no comeï¿½o
         */
        $CURRENT_POS = (int)0;
    
        /*
         *    recupera o tamanho mï¿½ximo da string
         */
        $STRING_LENGTH = (int)strlen($STRING);
    
        /*
         *    configura o final da string
         */
        $END_OF_STRING_POS = $STRING_LENGTH-1;
        $ORIGINAL_STRING   = $STRING."    ";
        /*
         *    vamos repor alguns caracteres portugueses facilmente
         *     confundidos, substituíndo os números não confundir com
         *    os encontros consonantais (RR), dígrafos (LH, NH) e o
         *    C-cedilha:
         *
         *    'LH' to '1'
         *    'RR' to '2'
         *    'NH' to '3'
         *    'Ç'  to 'SS'
         *    'CH' to 'X'
         */
        
        $match           = array('0','1','2','3','4','5','6','7','8','9');
        $ORIGINAL_STRING = str_replace($match ,' ',$ORIGINAL_STRING);
        //Código Original removia os acentos nesta parte.
        $ORIGINAL_STRING = str_replace('ç','SS',$ORIGINAL_STRING);
        $ORIGINAL_STRING = str_replace('Ç','SS',$ORIGINAL_STRING);
        $ORIGINAL_STRING = self::removeAcentos($ORIGINAL_STRING);
        /*
         *    Converte a string para caixa alta
         */
        $ORIGINAL_STRING = strtoupper($ORIGINAL_STRING);
    
        /*
         *    faz substituições
         *    -> "olho", "ninho", "carro", "exceção", "cabaça"
         */
        $ORIGINAL_STRING = str_replace('LH','1',$ORIGINAL_STRING);
        $ORIGINAL_STRING = str_replace('NH','3',$ORIGINAL_STRING);
        $ORIGINAL_STRING = str_replace('RR','2',$ORIGINAL_STRING);
        $ORIGINAL_STRING = str_replace('XC','SS',$ORIGINAL_STRING);
        /*
         *    a correção do SCH e do TH por conta dos nomes próprios:
         *    -> "schiffer", "theodora", "ophelia", etc..
         */
        $ORIGINAL_STRING = str_replace('SCH','X',$ORIGINAL_STRING);
        $ORIGINAL_STRING = str_replace('TH','T',$ORIGINAL_STRING);
        $ORIGINAL_STRING = str_replace('PH','F',$ORIGINAL_STRING);
        /*
         *    remove espaços extras
         */
        $ORIGINAL_STRING = trim($ORIGINAL_STRING);
    
        /*
         *    loop principal
         */
        while ( strlen($META_KEY) < $KEY_LENGTH )
        {
            /*
             *    sai do loop se maior que o tamanho da string
             */
            if ($CURRENT_POS >= $STRING_LENGTH)
            {
                break;
            }
    
            /*
             *    pega um caracter da string
             */
            $CURRENT_CHAR = substr($ORIGINAL_STRING, $CURRENT_POS, 1);
            /*
             *    se é uma vogal e faz parte do começo da string,
             *    coloque-a como parte da metachave
             */
            if    ( (self::is_vowel($ORIGINAL_STRING, $CURRENT_POS)) &&
                    ( ($CURRENT_POS == 0) ||
                      (self::string_at($ORIGINAL_STRING, $CURRENT_POS-1, 1," "))
                    )
                  )
            {
                $CURRENT_POS += 1;
            }
            /*
             *    procurar por consoantes que tem um único som, ou que
             *    que já foram substituídas ou soam parecido, como
             *     '?' para 'SS' e 'NH' para '1'
             */
            else if   ( self::string_at($ORIGINAL_STRING, $CURRENT_POS, 1,
                      array('1','2','3','B','D','F','J','K','L','M','P','T','V')) )
            {
                $META_KEY .= $CURRENT_CHAR;
    
                /*
                 *    incrementar por 2 se uma letra repetida for encontrada
                 */
                if ( substr($ORIGINAL_STRING, $CURRENT_POS + 1,1) == $CURRENT_CHAR )
                {
                    $CURRENT_POS += 2;
                }
    
                /*
                 *    senão incrementa em 1
                 */
                $CURRENT_POS += 1;
            }
            else
            {
                /*
                 *    checar consoantes com som confuso e similar
                 */
                switch ( $CURRENT_CHAR )
                {
    
                    case 'G':
                        switch ( substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1) )
                        {
                            case 'E':
                            case 'I':
                                $META_KEY   .= 'J';
                                $CURRENT_POS += 2;
                            break;
    
                            case 'U':
                                $META_KEY   .= 'G';
                                $CURRENT_POS += 2;
    
                            break;
    
                            case 'R':
                                $META_KEY .='GR';
                                $CURRENT_POS += 2;
                            break;
    
                            default:
                                $META_KEY   .= 'G';
                                $CURRENT_POS += 2;
                            break;
                        }
                    break;
    
                    case 'U':
                        if ( self::is_vowel($ORIGINAL_STRING, $CURRENT_POS-1) )
                        {
                            $CURRENT_POS+=1;
                            $META_KEY   .= 'L';
                            break;
                        }
                        /*
                         *    senão...
                         */
                        $CURRENT_POS += 1;
                    break;
    
                    case 'R':
                        if (($CURRENT_POS==0)||(substr($ORIGINAL_STRING, ($CURRENT_POS-1), 1)==' '))
                        {
                            $CURRENT_POS+=1;
                            $META_KEY   .= '2';
                            break;
                        }
                        elseif (($CURRENT_POS==$END_OF_STRING_POS)||(substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)==' '))
                        {
                            $CURRENT_POS+=1;
                            $META_KEY   .= '2';
                            break;
                        }
                        elseif ( self::is_vowel($ORIGINAL_STRING, $CURRENT_POS-1) && self::is_vowel($ORIGINAL_STRING, $CURRENT_POS+1) )
                        {
                            $CURRENT_POS+=1;
                            $META_KEY   .= 'R';
                            break;
                        }
                        /*
                         *    senão...
                         */
                        $CURRENT_POS += 1;
                        $META_KEY   .= 'R';
                    break;
    
                    case 'Z':
                        if ($CURRENT_POS>=(strlen($ORIGINAL_STRING)-1))
                        {
                            $CURRENT_POS+=1;
                            $META_KEY   .= 'S';
                            break;
                        }
                        elseif (substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)=='Z')
                        {
                            $META_KEY   .= 'Z';
                            $CURRENT_POS += 2;
                            break;
                        }
                        /*
                         *    senão...
                         */
                        $CURRENT_POS += 1;
                        $META_KEY   .= 'Z';
                    break;
    
    
                    case 'N':
                        if (($CURRENT_POS>=(strlen($ORIGINAL_STRING)-1)))
                        {
                            $META_KEY   .= 'M';
                            $CURRENT_POS += 1;
                            break;
                        }
                        elseif (substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)=='N')
                        {
                            $META_KEY   .= 'N';
                            $CURRENT_POS += 2;
                            break;
                        }
                        /*
                         *    senão...
                         */
                        $META_KEY   .= 'N';
                        $CURRENT_POS += 1;
                        break;
    
                    case 'S':
                        /*
                         *    caso especial 'assado', 'posse', 'sapato', 'sorteio'
                         */
                        if ( (substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)=='S') ||
                             ($CURRENT_POS==$END_OF_STRING_POS)||
                             (substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)==' ')
                           )
                        {
                            $META_KEY .= 'S';
                            $CURRENT_POS += 2;
                        }
                        elseif (($CURRENT_POS==0)||(substr($ORIGINAL_STRING, ($CURRENT_POS-1), 1)==' '))
                        {
                            $META_KEY .= 'S';
                            $CURRENT_POS += 1;
                        }
                        elseif((self::is_vowel($ORIGINAL_STRING, $CURRENT_POS-1)) &&
                               (self::is_vowel($ORIGINAL_STRING, $CURRENT_POS+1)))
                        {
                            $META_KEY .= 'Z';
                            $CURRENT_POS += 1;
                        }
                        /*
                        *  Ex.: Ascender, Lascivia
                        */
                        elseif (
                                    (substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)=='C') &&
                                    (
                                        (substr($ORIGINAL_STRING, ($CURRENT_POS+2), 1)=='E') ||
                                        (substr($ORIGINAL_STRING, ($CURRENT_POS+2), 1)=='I')
                                    )
                               )
    
                        {
                            $META_KEY .= 'S';
                            $CURRENT_POS += 3;
                        }
                        /*
                        * Ex.: Asco, Auscutar, Mascavo
                        */
                        elseif (
                                    (substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)=='C') &&
                                    (
                                        (substr($ORIGINAL_STRING, ($CURRENT_POS+2), 1)=='A') ||
                                        (substr($ORIGINAL_STRING, ($CURRENT_POS+2), 1)=='O') ||
                                        (substr($ORIGINAL_STRING, ($CURRENT_POS+2), 1)=='U')
                                    )
                               )
    
                        {
                            $META_KEY .= 'SC';
                            $CURRENT_POS += 3;
                        }
                        else
                        {
                            $META_KEY   .= 'S';
                            $CURRENT_POS += 1;
                        }
                        break;
    
                    case 'X':
                        /*
                         *    caso especial 'táxi', 'axioma', 'axila', 'tóxico'
                         */
                        if ((substr($ORIGINAL_STRING, ($CURRENT_POS-1), 1)=='E')&&($CURRENT_POS==1))
                        {
                            $META_KEY .= 'Z';
                            $CURRENT_POS += 1;
                        }
                        elseif ((substr($ORIGINAL_STRING, ($CURRENT_POS-1), 1)=='I')&&($CURRENT_POS==1))
                        {
                            $META_KEY .= 'X';
                            $CURRENT_POS += 1;
                        }
                        elseif ((self::is_vowel($ORIGINAL_STRING, $CURRENT_POS - 1))&&($CURRENT_POS==1))
                        {
                            $META_KEY .= 'KS';
                            $CURRENT_POS += 1;
                        }
                        else
                        {
                            $META_KEY .= 'X';
                            $CURRENT_POS += 1;
                        }
                    break;
    
                    case 'C':
                        /*
                         *    caso especial 'cinema', 'cereja'
                         */
                        if ( self::string_at($ORIGINAL_STRING, $CURRENT_POS, 2,array('CE','CI')) )
                        {
                            $META_KEY   .= 'S';
                            $CURRENT_POS += 2;
                        }
                        elseif( (substr($ORIGINAL_STRING, ($CURRENT_POS+1), 1)=='H'))
                        {
                            $META_KEY   .= 'X';
                            $CURRENT_POS += 2;
                        }
                        else
                        {
                            $META_KEY   .= 'K';
                            $CURRENT_POS += 1;
                        }
                        break;
    
                    /*
                     *    como a letra 'h' é silenciosa no português, vamos colocar
                     *    a chave meta como a vogal logo após a letra 'h'
                     */
                    case 'H':
                        if ( self::is_vowel($ORIGINAL_STRING, $CURRENT_POS + 1) )
                        {
                            $META_KEY .= $ORIGINAL_STRING[$CURRENT_POS + 1];
                            $CURRENT_POS += 2;
                        }
                        else
                        {
                            $CURRENT_POS += 1;
                        }
                        break;
    
                    case 'Q':
                       if (substr($ORIGINAL_STRING, $CURRENT_POS + 1,1) == 'U')
                       {
                          $CURRENT_POS += 2;
                       }
                       else
                       {
                          $CURRENT_POS += 1;
                       }
    
                       $META_KEY   .= 'K';
                       break;
    
                    case 'W':
                        if (self::is_vowel($ORIGINAL_STRING, $CURRENT_POS + 1))
                        {
                            $META_KEY   .= 'V';
                            $CURRENT_POS += 2;
                        }
                        else
                        {
                            $META_KEY   .= 'U';
                            $CURRENT_POS += 2;
                        }
                        break;
    
                    default:
                        $CURRENT_POS += 1;
                }
            }
        }
    
        /*
         *    corta os caracteres em branco
         */
        $META_KEY = trim($META_KEY);
    
        /*
         *    retorna a chave matafï¿½nica
         */
        return $META_KEY;
    }//Fim Método
/*-------------------------------------------------------------------------------
 *   Procura no array se aparece o char 
 *------------------------------------------------------------------------------- */
    public static function string_at($STRING, $START, $STRING_LENGTH, $LIST)
    {
        if ( ($START <0) || ($START >= strlen($STRING)) )
        {
            return false;
        }
        for ( $I=0; $I<count($LIST); $I++)
        {
            if ( $LIST[$I] == substr($STRING, $START, $STRING_LENGTH))
            {
                return true;
            }
        }
        return false;
    }//Fim Mï¿½dulo
/*-------------------------------------------------------------------------------
 *   Verifica se o Char é vogal
 *------------------------------------------------------------------------------- */
    public static function is_vowel($string, $pos)
    {
        if ($string === '' || $pos >= strlen($string))
        {
            return false;
        }
        
        $pos = strpos("AEIOU", substr($string, $pos, 1));
        if ($pos === false)
        {
            return false;
        }
        else if ($pos > 0 )
        {
            return true;
        }
        return false;
        
        
    }//Fim Método
/*-------------------------------------------------------------------------------
 *   Função corrige String para URL e nomes que não podem estar acentuados
 *------------------------------------------------------------------------------- */
    public static function removeAcentos($string,$sp=' ')
    {
        return TFerramentas::removeAcentos($string,$sp);
    }//Fim do Método
}//Fim Classe