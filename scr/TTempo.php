<?php
namespace Omegapinho\CoresAdianti;
/**
 * TTempo - Rotinas relativos a calculo de tempo, datas e feriados
 * Copyright (c) 
 * @author  Fernando de Pinho Araújo 
 * @version 1.0, 2020-11-15
 **/

//use omegapinho\coresadianti\scr\TTempo

class TTempo
{
    var $pascoa       = "";
    var $carnaval     = "";
    var $corpus       = "";
    var $sextasanta   = "";
    var $feriados     = false;
    var $OPM_feriado  = false;
    var $feriadosOPM  = false;
    
/*------------------------------------------------------------------------------
 *  DESCRICAO: Converte timestamp para data
 *------------------------------------------------------------------------------*/
    public function timestampToDate ($dt=null, $format = 'br') 
    {
        if ($dt != null)
        {
            if ($format == 'br') 
            {
                //$data = date_format($date, 'd-m-Y');
                $data = date("d/m/Y",$dt);
            }
            elseif ($format == 'en')
            {
                //$data = date_format($date, 'Y-m-d');
                $data = date('Y-m-d', (int) $dt);
            }
            //var_dump($data);
            
            return $data;
        }
        return false;
    }//Fim Módulo
/*------------------------------------------------------------------------------
 *  DESCRICAO: Converte date para timestamp
 *------------------------------------------------------------------------------*/
    public function dateTo_Timestamp ($dt=null, $format = 'br') 
    {
        if ($dt != null)
        {
            
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dt, new DateTimeZone('America/Sao_Paulo'));
            $timestamp = $dateTime->getTimestamp();
            
            return $timestamp;
        }
        return false;
    }//Fim Módulo
/*------------------------------------------------------------------------------
 *  DESCRICAO: Converte data para timestamp
 *------------------------------------------------------------------------------*/
    public function dateToTimestamp ($dt=null, $format = 'br', $time = 'N') 
    {
        if ($dt != null)
        {
            if ($this->isValidData($dt) == true)
            {
                //echo $dt;
                $date = date_create();
                date_timestamp_set($date, strtotime($dt));
                //$date = date_timestamp_get($dt);
                return date_timestamp_get($date);
            }
        }
        return false;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *                        Formata a data para padrão dd/mm/YYYY
 *------------------------------------------------------------------------------- */
    public function corrigeData($dat)
    {
        if ($dat==NULL || $dat=="" || strlen($dat)<8) 
        {
            return false;
        }
        $data = (strstr($dat,'/')) ? explode('/',$dat) : explode('-',$dat);
        $d = (int)$data[0];
        $m = (int)$data[1];
        $y = (int)$data[2];
        $dd = str_pad($d, 2, '0', STR_PAD_LEFT);
        $mm = str_pad($m, 2, '0', STR_PAD_LEFT);
        $yy = str_pad($y, 2, '0', STR_PAD_LEFT);            

        // checa posicionando os elementos no formato mês dia e ano.
        if (checkdate($m, $d, $y) == TRUE) 
        {
            return $dd."/".$mm."/".$y;
        } 
        elseif (checkdate($d, $m, $y)==TRUE) 
        {
            return $mm."/".$dd."/".$y;
        } 
        elseif (checkdate($m,$y,$d)==TRUE) 
        {
            return $yy."/".$mm."/".$d;
        }
        return false;
    }//Fim Módulo
/*---------------------------------------------------------------
 * Nota: Retorna o dia da Semana
 *---------------------------------------------------------------*/
    public function diaSemana($data) 
    {
    	$dia = date ( "w", strtotime ( $data ) );
    	//return $dia;
    	switch ($dia) {
    		case 0 :
    			return 0;
    			break; // Domingo
    		case 1 :
    			return 1;
    			break; // segunda
    		case 2 :
    			return 2;
    			break; // terça
    		case 3 :
    			return 3;
    			break; // quarta
    		case 4 :
    			return 4;
    			break; // quinta
    		case 5 :
    			return 5;
    			break; // sexta
    		case 6 :
    			return 6;
    			break; // sabado
    	}
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 * Calcula o intervalo entre duas datas (strings)
 * $dti = dd/mm/yyyy
 * $dtf = dd/mm/yyyy
 * $formato = br ou en
 * Retorno em dias
 * OBS: como o timesstam retorna uma diferença fracionária e será arredondado para cima.
 *------------------------------------------------------------------------------- */
    public function diffDatas ($dti,$dtf, $formato = 'br')
    {
        $data_i = $this->geraTimestamp($dti,$formato);
        $data_f = $this->geraTimestamp($dtf,$formato);

        if ($data_f==false || $data_i==false)
        {
            return false;
        }
        $diferenca = $data_f - $data_i;

        return round( ($diferenca)  /(60*60*24) );
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *                        Converte em DateTime
 *------------------------------------------------------------------------------- */
    public function geraTimestamp ($data, $formato = 'br')
    {
        if (!$data)
        {
            return false;
        }
        $d = (strpos($data,'-')!=0) ? explode('-',$data) : explode('/',$data);
        $ret = ($formato == 'br') ? mktime (0,0,0,$d[1],$d[0],$d[2]): mktime (0,0,0,$d[1],$d[2],$d[0]);
        return $ret;
    }//Fim Módulo
/*---------------------------------------------------------------
 * Nota: Verifica os Feriados
 * $data 'dd/mm/yyyy'
 * $opm = opm_id
 *---------------------------------------------------------------*/
    public function is_feriado($data,$opm = null) {
    	//$fer     = TFerramentas::instancia();
    	$Feriado = false;
        $ano     = substr ( $data, 6, 4 ) ;
        if (false !== strpos($data,"-"))//padroniza a data trocando o simbolo separador
        {
            $data = str_replace("-","/", $data);
            //if ($this->debug) echo "Corrigida".$data;
        }
        //Dá carga inicial dos feriados
        if (empty($this->feriados))
        {
            $feriados    = array();
            //Calcula os Feriados de data móvel
            $feriados [] = self::dataCarnaval($ano);
            $feriados [] = self::dataPascoa($ano);
            $feriados [] = self::dataSextaSanta($ano);
            $feriados [] = self::dataCorpusChristi($ano);
            /*try
            {
                TTransaction::open('sisopm');
                $results     = feriado::where ('tipo','=','NACIONAL')->load();
                
                TTransaction::close();
            }
            catch (Exception $e) // in case of exception 
            {
                TTransaction::rollback(); // undo all pending operations
                $results = false;
            }
            if ($results !== false)
            {
                //Add a lista os Feriados Nacionais
                foreach ($results as $result)//Cria lista de Feriados Nacionais
                {
                    if($result->dataferiado)
                    {
                        $feriados[] = $result->dataferiado.'/'.$ano;
                    }
                }
                $this->feriados = $feriados;
            }*/
        }
        else
        {
            //Carrega os Feriados
            $feriados = $this->feriados;
        }
        /*if ($opm != null)
            {
            //Verifica se os feriados municipais são os da OPM carregados 
            if($this->OPM_feriado != $opm && $opm != null)
            {
                $conn = TTransaction::get();
                $sql = "SELECT DISTINCT dataferiado FROM bdhoras.feriado, bdhoras.feriadoopm ".
                            "WHERE feriadoopm.feriado_id = feriado.id AND (feriado.tipo = 'MUNICIPAL' OR ".
                            " feriado.tipo = 'INSTITUCIONAL') AND feriadoopm.opm_id = ".(int) $opm.";";
                $feriados_opm = $conn->prepare($sql);
                $feriados_opm->execute();
                $results = $feriados_opm->fetchAll();
                $municipais = array();
                foreach ($results as $result)
                {
                     $municipais[] = $result['dataferiado'].'/'.$ano;   
                }
                $this->feriadosOPM = $municipais;
                $this->OPM_feriado = $opm;
            }
            else
            {
                //Carrega Feriados Municiapais da OPM
                $municipais = $this->feriadosOPM;
            }
        }*/
        //Verifica os feriados nacionas com a $data
        foreach ($feriados as $feriado)
        {
            if ($feriado == $data)
            {
                $Feriado = true;
            }
        }
        //Verifica somente se foi pedido OPM também
        /*if ($opm != null)
        {
            foreach ($municipais as $municipal)
            {
                if ($municipal == $data)
                {
                    $Feriado = true;
                }
            }
        }*/
		$datas = substr ( $data, 6, 4 ) . "/" . substr ( $data, 3, 2 ) . "/" . substr ( $data, 0, 2 );
		// para retornar o dia da semana e necessário passar a data no formato americano ano/mes/dia
		$diaSemana = date ( "w", strtotime ( $datas ) );
		if ($diaSemana == 0 || $diaSemana == 6) 
		{
			$Feriado = true;//Sábado ou Domingo
		}
    	return $Feriado;
    }//Fim Módulo
/*---------------------------------------------------------------
 * Nota: Cria lista de todos feriados
 *---------------------------------------------------------------*/
    public function getFeriado($ano,$opm = null) 
    {
        if (empty($this->feriados) || $this->feriados==false)
        {
            $feriados = array();
            //Feriados de data móvel
            $feriados [] = $this->dataCarnaval($ano);
            $feriados [] = $this->dataPascoa($ano);
            $feriados [] = $this->dataSextaSanta($ano);
            $feriados [] = $this->dataCorpusChristi($ano);
            /*try//Busca Feriados Nacionais
            { 
                TTransaction::open('sisopm');
                $results = feriado::where ('tipo','=','NACIONAL')->load();
                foreach ($results as $result)//Cria lista de Feriados Nacionais
                {
                    if($result->dataferiado)
                    {
                        $feriados[] = $result->dataferiado.'/'.$ano;
                    }
                }
                TTransaction::close();
            } 
            catch (Exception $e) 
            { 
                TTransaction::rollback();
            }*/
            //Busca Feriados exclusivos da OPM, se esta for definida
            /*if($opm!=null)
            {
                try
                {
                    TTransaction::open('sisopm');
                    $conn = TTransaction::get();
                    $sql = "SELECT DISTINCT dataferiado FROM bdhoras.feriado, bdhoras.feriadoopm ".
                                "WHERE feriadoopm.feriado_id = feriado.id AND (feriado.tipo = 'MUNICIPAL' OR ".
                                " feriado.tipo = 'INSTITUCIONAL') AND feriadoopm.opm_id = ".(int) $opm.";";
                    $feriados_opm = $conn->prepare($sql);
                    $feriados_opm->execute();
                    $results = $feriados_opm->fetchAll();
                    foreach ($results as $result)//Acrescenta os feriados municipais
                    {
                         $feriados[] = $result['dataferiado'].'/'.$ano;   
                    }
                    TTransaction::close();
                } 
                catch (Exception $e) 
                { 
                    TTransaction::rollback();
                }
            }*/
            $this->feriados = $feriados;
        }
        return $this->feriados;
    }//Fim Módulo
/*----------------------------------------------------------------------------------------
 * Nota: Rotinas de Calculo de Data Móvel
 *----------------------------------------------------------------------------------------
 dataPascoa(ano, formato);
 Autor: Yuri Vecchi

 Funcao para o calculo da Pascoa
 Retorna o dia da pascoa no formato desejado ou false.

 ######################ATENCAO###########################
 Esta funcao sofre das limitacoes de data de mktime()!!!
 ########################################################

 Possui dois parametros, ambos opcionais
 ano = ano com quatro digitos
	 Padrao: ano atual
 formato = formatacao da funcao date() http://br.php.net/date
	 Padrao: d/m/Y
 *----------------------------------------------------------------------------------------*/
/*----------------------------------------------------------------------------------------
 * Nota: Pascoa
 *----------------------------------------------------------------------------------------*/
    public function dataPascoa($ano=false, $form="d/m/Y") 
    {
    	if ($this->pascoa)
    	{
            return $this->pascoa; 
        }
    	$ano=($ano) ? $ano :date("Y");
    	if ($ano<1583) 
    	{ 
    		$A = ($ano % 4);
    		$B = ($ano % 7);
    		$C = ($ano % 19);
    		$D = ((19 * $C + 15) % 30);
    		$E = ((2 * $A + 4 * $B - $D + 34) % 7);
    		$F = (int)(($D + $E + 114) / 31);
    		$G = (($D + $E + 114) % 31) + 1;
    		$ret = date($form, mktime(0,0,0,$F,$G,$ano));
    	}
    	else 
    	{
    		$A = ($ano % 19);
    		$B = (int)($ano / 100);
    		$C = ($ano % 100);
    		$D = (int)($B / 4);
    		$E = ($B % 4);
    		$F = (int)(($B + 8) / 25);
    		$G = (int)(($B - $F + 1) / 3);
    		$H = ((19 * $A + $B - $D - $G + 15) % 30);
    		$I = (int)($C / 4);
    		$K = ($C % 4);
    		$L = ((32 + 2 * $E + 2 * $I - $H - $K) % 7);
    		$M = (int)(($A + 11 * $H + 22 * $L) / 451);
    		$P = (int)(($H + $L - 7 * $M + 114) / 31);
    		$Q = (($H + $L - 7 * $M + 114) % 31) + 1;
    		$ret = date($form, mktime(0,0,0,$P,$Q,$ano));
    	}
    	$this->pascoa = $ret;
    	return $ret;
    }//Fim Módulo
/*----------------------------------------------------------------------------------------
 * Nota: Carnaval
 *----------------------------------------------------------------------------------------
 dataCarnaval(ano, formato);
 Autor: Yuri Vecchi

 Funcao para o calculo do Carnaval
 Retorna o dia do Carnaval no formato desejado ou false.

 ######################ATENCAO###########################
 Esta funcao sofre das limitacoes de data de mktime()!!!
 ########################################################

 Possui dois parametros, ambos opcionais
 ano = ano com quatro digitos
	 Padrao: ano atual
 formato = formatacao da funcao date() http://br.php.net/date
	 Padrao: d/m/Y
 *----------------------------------------------------------------------------------------*/
    public function dataCarnaval($ano=false, $form="d/m/Y") 
    {
    	if ($this->carnaval)
    	{
            return $this->carnaval; 
        } 
    	$ano=($ano) ? $ano :date("Y");
    	$a=explode("/", self::dataPascoa($ano));
    	$this->carnaval = date($form, mktime(0,0,0,$a[1],$a[0]-47,$a[2]));
    	return $this->carnaval;
    }// Fim do Módulo
/*----------------------------------------------------------------------------------------
 * Nota: Corpus Christi
 *----------------------------------------------------------------------------------------
    // dataCorpusChristi(ano, formato);
    // Autor: Yuri Vecchi
    //
    // Funcao para o calculo do Corpus Christi
    // Retorna o dia do Corpus Christi no formato desejado ou false.
    //
    // ######################ATENCAO###########################
    // Esta funcao sofre das limitacoes de data de mktime()!!!
    // ########################################################
    //
    // Possui dois parametros, ambos opcionais
    // ano = ano com quatro digitos
    //	 Padrao: ano atual
    // formato = formatacao da funcao date() http://br.php.net/date
    //	 Padrao: d/m/Y
 *----------------------------------------------------------------------------------------*/
    public function dataCorpusChristi($ano=false, $form="d/m/Y") 
    {
    	if ($this->corpus)
    	{
         return $this->corpus;
        }
    	$ano=($ano) ? $ano :date("Y");
    	$a=explode("/", self::dataPascoa($ano));
    	$this->corpus = date($form, mktime(0,0,0,$a[1],$a[0]+60,$a[2]));
    	return $this->corpus;
    }//Fim Módulo
/*----------------------------------------------------------------------------------------
 * Nota: Sexta Feira Santa
 *----------------------------------------------------------------------------------------
    // dataSextaSanta(ano, formato);
    // Autor: Yuri Vecchi
    //
    // Funcao para o calculo da Sexta-feira santa ou da Paixao.
    // Retorna o dia da Sexta-feira santa ou da Paixao no formato desejado ou false.
    //
    // ######################ATENCAO###########################
    // Esta funcao sofre das limitacoes de data de mktime()!!!
    // ########################################################
    //
    // Possui dois parametros, ambos opcionais
    // ano = ano com quatro digitos
    // Padrao: ano atual
    // formato = formatacao da funcao date() http://br.php.net/date
    // Padrao: d/m/Y
 *----------------------------------------------------------------------------------------*/
    public function dataSextaSanta($ano=false, $form="d/m/Y") 
    {
    	if ($this->sextasanta)
    	{
            return $this->sextasanta;
        }
    	$ano=($ano) ? $ano :date("Y");
    	$a=explode("/", self::dataPascoa($ano));
    	$this->sextasanta = date($form, mktime(0,0,0,$a[1],$a[0]-2,$a[2]));
    	return $this->sextasanta;
    } //Fim Módulo
/*-------------------------------------------------------------------------------
 *        Calcula a quantidade de dias do mês
 *-------------------------------------------------------------------------------*/
     public function qntDiasMes ($mes,$ano)
     {
         return date("t", mktime(0, 0, 0, $mes, 01, $ano));
     }//Fim Módulo
/*-------------------------------------------------------------------------------
 *        Calcula a quantidade de dias úteis do mês
 *-------------------------------------------------------------------------------*/
    public function getDiasUteis($mes,$ano)
    {
      $uteis = 0;
      $dias_no_mes = $this->qntDiasMes($mes,$ano);//cal_days_in_month(CAL_GREGORIAN, $mes, $ano); 
      for($dia = 1; $dia <= $dias_no_mes; $dia++)
      {
        // Aqui você pode verifica se tem feriado
        // ----------------------------------------
        // Obtém o timestamp
        // (http://php.net/manual/pt_BR/function.mktime.php)
        $timestamp = mktime(0, 0, 0, $mes, $dia, $ano);
        $semana    = date("N", $timestamp);
        if($semana < 6) $uteis++;
      }
      return $uteis;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *        Retorna a data por extenso
 *-------------------------------------------------------------------------------*/
    public function dataExtenso ($data = null)
    {
        /*if (LOCAL != 'localhost')
        {
            setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
        }        
        $data = ($data == null) ? date("Y/m/d") : $data;
        date_default_timezone_set('America/Sao_Paulo');
        return strftime('%d de %B de %Y', strtotime((string)$data));*/
        $data = new DateTime($data ?? date("Y/m/d"));
        $dia  = $data->format('d');
        $mes  = TListas::meses($data->format('m'));
        $ano  = $data->format('Y');
        
        return "$dia de $mes de $ano";
    }//Fim Módulo
/*---------------------------------------------------------------------------
 * Primeiro dia do mês 
 *---------------------------------------------------------------------------*/
    public static function primeiroDia ($param = null)
    {
        $data = mktime(0, 0, 0, date('m') , 1 , date('Y'));
        return date ('Y-m-d',$data);
    }
/*---------------------------------------------------------------------------
 * Ultimo dia do mês 
 * @param   $param = array('mes'=>XX,'ano'=>YYYY)
 * @return  ultimo dia do mês 
 *---------------------------------------------------------------------------*/
    public static function ultimoDia ($param = null)
    {
        if (is_array($param) && array_key_exists('mes',$param) && array_key_exists('ano',$param))
        {
            $mes = $param['mes'];
            $ano = $param['ano'];
        }
        else
        {
            $mes = date('m');
            $ano = date('Y');
        }
        $ultimo = array('E',31,'C',31,30,31,30,31,31,30,31,30,31);
        $dia_f  = $ultimo[$mes];
        if ($dia_f == 'C')
        {
            $dia_f = (($ano % 4 ) == 0 ) ? 29 : 28;
        }
        else if ($dia_f == 'E')
        {
            $dia_f = 31;
        }
        $data = date("{$ano}-{$mes}-{$dia_f}");
        return $data;
    }
/*------------------------------------------------------------------------------
 *   Verifica o tipo de data e converte para gravar no BD
 *------------------------------------------------------------------------------*/
    public function confereData ($date)
    {
        $date = str_replace('/','-',$date);
        if (strpos($date,'-') == 4)
        {
            return $date;
        }
        return TDate::date2us($date);
    }//Fim Módulo
  /**
  * Função para obter essa semana
  * @return array
  *   Retorna um array com o primeiro dia da semana e o dia atual
  */
    public static function getInicioFimSemana() 
    {
        $primeiro = date("Y-m-d", strtotime("last sunday"));
        $ultimo   = date("Y-m-d", strtotime("next saturday"));
        return array('primeiro'=>$primeiro ,'ultimo'=>$ultimo );
    }
    /**
 * Altera uma data para outro formato
 *
 * @param string $date String contendo a data a ser formatada
 * @param string $outputFormat Formato de saida
 * @throws Exception Quando não puder converter a data
 * @return string Data formatada
 * @author Hugo Ferreira da Silva
 */
    public static function parseDate($date, $outputFormat = 'd/m/Y')
    {
        try
        {
            //Formatos comuns
            $formats = array(
                'd/m/Y',
                'd/m/Y H',
                'd/m/Y H:i',
                'd/m/Y H:i:s',
                'Y-m-d',
                'Y-m-d H',
                'Y-m-d H:i',
                'Y-m-d H:i:s',
            );
            
            foreach($formats as $format)
            {
                $dateObj = DateTime::createFromFormat($format, $date);
                if($dateObj !== false)
                {
                    break;
                }
            }
            
            if($dateObj === false)
            {
                return false;
            }
            //Retorna 
            return $dateObj->format($outputFormat);
        }
        catch (Exception $e)
        {
            return false;
        }
    }//Fim Método
}//Fim Classe
