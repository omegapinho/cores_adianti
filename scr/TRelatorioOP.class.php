<?php
/**
 * TRelatorioOP - Cria relatório de operações para ser mostrado em mensagens
 * Copyright (c) 
 * @author  Fernando de Pinho Araújo 
 * @version 1.0, 2020-11-15
 * @require Precisa criar rotina sem_acentos no BD Postgres(Ver no final)
 **/
namespace Omegapinho\CoresAdianti

class TRelatorioOP 
{
/*------------------------------------------------------------------------------
 *    Iniciando Variáveis
 *------------------------------------------------------------------------------*/
 
     public $mSucesso;
     public $mFalha;
     
     public $corSucesso;
     public $corFalha;
     
     protected $mensagem ;
     protected $mCount;
/*------------------------------------------------------------------------------
 *    Construtor da Classe
 *------------------------------------------------------------------------------*/
     public function __construct()
     {
        $this->mSucesso = " Sucesso na Operação";
        $this->mFalha   = " Falha na Operação";
         
        $this->corSucesso = "green";
        $this->corFalha   = "red";
        $this->mensagem   = array();
        $this->mCount     = 0;  
     }//Fim Módulo
/*------------------------------------------------------------------------------
 *    Adiciona Mensagem
 *------------------------------------------------------------------------------*/
     public function addMensagem ($texto, $status = TRUE)
     {
         if ( $status != null || ($status === true || $status === false) )
         {
             $cor = ($status) ? $this->corSucesso : $this->corFalha;
             $txt = ($status) ? $this->mSucesso   : $this->mFalha;
             $ret = "$texto<strong><font color='$cor'>$txt</font></strong>";
         }
         else
         {
             $ret = $texto;
         }
         $this->mensagem[]=$ret;
         $this->mCount++;
         
     }//Fim Módulo
/**
 *    Monta e publica a Mensagem
 *    @param $mostra = false/info/error (retorna a variavel $show)
 **/
     public function publicaRelatorio ($mostra = false)
     {
         $rels = $this->mensagem;
         $show = '';
         foreach ($rels as $rel)
         {
             if (!empty($show))
             {
                 $show.="<br>";
             }
             $show.= $rel;
         }
         if ($mostra)
         {
             new TMessage ($mostra,$show);
         }
         else
         {
             return $show;
         }
     }//Fim Módulo
 /**
  *    Adiciona Mensagem sendo toda ela destacada
  * @param $texto  => mensagem que será destacada
  * @param $status => true/false em relação a ter tido sucesso ou não 
  **/
     public function addTextoDestaque ($texto, $status = TRUE)
     {
         $cor              = ($status) ? $this->corSucesso : $this->corFalha;
         $this->mensagem[] = "<strong><font color='$cor'>$texto</font></strong>";
         $this->mCount++;
         
     }//Fim Módulo
}//Fim Classe

