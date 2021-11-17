<?php
namespace Omegapinho\CoresAdianti;
/**
 * TFerramentas - Ferramentas (funções) diversas de uso geral
 * Copyright (c) 
 * @author  Fernando de Pinho Araújo 
 * @version 1.0, 2016-01-22
 */

class TFerramentas extends TListas
{
    //Armazena a instância
    protected static $instancia;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        set_time_limit(3600);
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
    }
/**
 * Busca Grupo adminstrador
 * @return true/false
 **/
    public static function i_adm ($param=null)
    {
        $perfis = TSession::getValue('usergroupids');
        if (!is_array($perfis) && strpos($perfis,',') > 0)
        {
            $perfis = explode(',',$perfis);
        }
        else if (!is_array($perfis))
        {
            return false;
        }
        $ret    = false;
        foreach ($perfis as $perfil)
        {
            if ($perfil =='1')
            {
                $ret = true;
            }
        }
        return $ret;
    }//Fim Módulo
/**
 * Busca Grupo adminstrador
 * @return true/false
 **/
    public static function getSistemaEmUso($local = null)
    {
        $ini  = AdiantiApplicationConfig::get();
        return $ini['general']['application'];
        
    }//Fim Módulo
/**
 *  Verifica Ambiente para ver se é desenvolvimento
 * @return true/false
 **/
    public static function is_dev()
    {
        return (LOCAL == 'localhost');
    }//Fim Módulo
/**
 * Busca perfil no SIGU no profile
 * @param $profile array() de dados do SSO
 * @return true/false
 **/
    public function perfil_Sigu ($profile=array())
    {
        $ret    = false;
        if (is_array($profile) && array_key_exists('perfis',$profile))
        {
            $perfis = $profile['perfis'];
        }
        else
        {
            $perfis = array('sistema'=>array('id'=>false));
        }
        
        foreach ($perfis as $perfil)
        {
            //Verifica se o Usuário tem perfil nos sistema para se criar um novo se for o caso
            if (array_key_exists('sistema',$perfil) && $perfil['sistema']['id']=='606')//código do sistema que vem no profile do SSO
            {
                try
                {
                    TTransaction::open('permission');
                    //Cria um novo usuário
                    $novo_user                 = new SystemUser();//Cria um novo usuário
                    $novo_user->name           = $profile['nome'];
                    $novo_user->login          = $profile['login'];
                    $novo_user->password       = md5($profile['login']);//Cria senha com base no CPF
                    $novo_user->email          = $profile['email'];
                    $novo_user->frontpage_id   = '10';
                    $novo_user->active         = 'Y';
                    $novo_user->corporacao     = $profile['corporacao'];
                    //Verifica se a unidade Existe
                    $unidade = OPM::find($profile['unidade']['id']);
                    if (empty($unidade))
                    {
                        $unidade = null;
                    }
                    else
                    {
                        $unidade = $unidade->id;
                    }
                    $novo_user->system_unit_id = $unidade;
                    $novo_user->store();
                    $user                    = $novo_user->id;//Armazenha o id do usuário
                    //Conforme a descrição do perfil, atribui um grupo 
                    if ($perfil['descricao']=='ADMINISTRADOR')
                    {
                        $padrao = SystemGroup::where ('name','=','Admin')->load();//Se Administrador
                    }
                    else
                    {
                        $padrao = SystemGroup::where ('name','=','Public')->load();//Os demais
                    }
                    foreach ($padrao as $p)
                    {
                        $grupo = $p->id;
                    }
                    $novo_grupo                  = new SystemUserGroup();//Cria novo Grupo para Usuário
                    $novo_grupo->system_user_id  = $user;
                    $novo_grupo->system_group_id = $grupo;
                    $novo_grupo->store();                                        
                    TTransaction::close();
                    $ret = true;
                }
                catch (Exception $e) // in case of exception
                {
                    new TMessage('error', $e->getMessage());
                    TTransaction::rollback();
                }
            }
        }
        return $ret;
    }//Fim Módulo
/**
 *        Valida CPF
 * @param $cpf a testar
 * @return true/false
 **/
   public static function isValidCPF($cpf) 
   {
           // Verifica se um número foi informado
        if(empty($cpf)) 
        {
            return false;
        }
        // Elimina possivel mascara
        $cpf = preg_replace('/[^0-9]/', '', (string) $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
        // Verifica se o numero de digitos informados é igual a 11 
        if (strlen($cpf) != 11) 
        {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' || 
            $cpf == '11111111111' || 
            $cpf == '22222222222' || 
            $cpf == '33333333333' || 
            $cpf == '44444444444' || 
            $cpf == '55555555555' || 
            $cpf == '66666666666' || 
            $cpf == '77777777777' || 
            $cpf == '88888888888' || 
            $cpf == '99999999999') 
        {
            return false;
         // Calcula os digitos verificadores para verificar se o
         // CPF é válido
         } 
         else 
         {   
            for ($t = 9; $t < 11; $t++) 
            {
                for ($d = 0, $c = 0; $c < $t; $c++) 
                {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) 
                {
                    return false;
                }
            }
            return true;
       }
   }//Fim Módulo
/**
 *        Valida CNPJ
 * @param $cnpj a testar
 * @return true/false
 **/
   public static function isValidCNPJ($cnpj) {
   
      if (strlen($cnpj) <> 14)
      {
         return false;
      } 
      $soma = 0;
      $soma += ($cnpj[0] * 5);
      $soma += ($cnpj[1] * 4);
      $soma += ($cnpj[2] * 3);
      $soma += ($cnpj[3] * 2);
      $soma += ($cnpj[4] * 9); 
      $soma += ($cnpj[5] * 8);
      $soma += ($cnpj[6] * 7);
      $soma += ($cnpj[7] * 6);
      $soma += ($cnpj[8] * 5);
      $soma += ($cnpj[9] * 4);
      $soma += ($cnpj[10] * 3);
      $soma += ($cnpj[11] * 2); 
      $d1 = $soma % 11; 
      $d1 = $d1 < 2 ? 0 : 11 - $d1; 

      $soma = 0;
      $soma += ($cnpj[0] * 6); 
      $soma += ($cnpj[1] * 5);
      $soma += ($cnpj[2] * 4);
      $soma += ($cnpj[3] * 3);
      $soma += ($cnpj[4] * 2);
      $soma += ($cnpj[5] * 9);
      $soma += ($cnpj[6] * 8);
      $soma += ($cnpj[7] * 7);
      $soma += ($cnpj[8] * 6);
      $soma += ($cnpj[9] * 5);
      $soma += ($cnpj[10] * 4);
      $soma += ($cnpj[11] * 3);
      $soma += ($cnpj[12] * 2); 
      
      $d2 = $soma % 11; 
      $d2 = $d2 < 2 ? 0 : 11 - $d2; 
      if ($cnpj[12] == $d1 && $cnpj[13] == $d2) 
      {
         return true;
      }
      else 
      {
         return false;
      }
   }//Fim Módulo
/**
 *   Verifica se uma data é válida
 * @param $dat = data a testar
 * @return true/false
 **/
    public static function isValidData($dat)
    {
    	if (strpos($dat,'/'))
    	{
            $data = explode("/","$dat"); // fatia a string $dat em pedados, usando / como referência 
        }
        else
        {
            $data = explode("-","$dat"); // fatia a string $dat em pedados, usando - como referência
        }
    	if (!is_array($data))
    	{
            return false; 
        }
        if (!array_key_exists(0,$data) || !array_key_exists(1,$data) || !array_key_exists(2,$data))
        {
            return false;
        }
    	$d = self::soNumeros($data[0]);
    	$m = self::soNumeros($data[1]);
    	$y = self::soNumeros($data[2]);
     
    	// verifica se a data é válida!
    	// 1 = true (válida)
    	// 0 = false (inválida)
    	$res = checkdate($m,$d,$y);
    	if ($res == 1)
    	{
    	   return true;
    	}
    	else 
    	{
    	   return false;
    	}
    }
/**
 *   Verifica se uma hora é válida
 * @param $hr = hora a testar
 * @return  true/false
 **/
    public static function isValidHora ($hr)
    {
        $hora = explode(":",$hr); // fatia a string $dat em pedados, usando / como referência 
        if (!is_array($hora))
        {
            return false;
        }
        if (!array_key_exists(0,$hora) || !array_key_exists(1,$hora))
        {
            return false;
        }
    	$h = (int) $hora[0];
    	$m = (int) $hora[1];
    	$s = (int) (array_key_exists(2,$hora)) ? $hora[2] : '00';
        $res = true;
        if ($h<0 || $h>23)
        {
            $res = false;
        }
        if ($m<0 || $m>59)
        {
            $res = false;
        }
        if ($s<0 || $s>59)
        {
            $res = false;
        }
        return $res;
    }//Fim Módulo
/**
 *   Funçao corrige String para URL e nomes que não podem estar acentuados
 * @param $string a ser limpada
 * @param $sp caracter space
 * @return string sem acentos 
 **/
    public static function removeAcentos($string,$sp=' ')
    {
        // matriz de entrada
        $what = array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','É','Í','Ó','Ú','ñ','Ñ','ç','Ç',
                       ' ','-','(',')',',',';',':','|','!','"','#','$','%','&','/','=','?','~','^','>','<','ª','º',"'" );
        // matriz de saída
        $by   = array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','E','I','O','U','n','N','c','C',
                       $sp,'-',' ',' ',' ',' ',' ',' ',' ',' ',' ','$',' ',' ',' ',' ',' ',' ',' ',' ',' ','a','o'," " );
        // devolver a string
        return str_replace($what, $by, $string);
    }//Fim do Modúlo
/**
 *   Funçao corrige String para URL e nomes que não podem estar acentuados
 * @param $string a ser limpada
 * @param $sp caracter space
 * @return string sem acentos 
 **/
    public static function removeAcentoSinais($string,$sp=' ')
    {
        // matriz de entrada
        $what = array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û',
                       'À','Á','Ã','Â','É','Í','Ó','Õ','Ô','Ú','ñ','Ñ','ç','Ç',
                       ' ','-','(',')',',',';',':','|','!','"','#','$','%','&','/','=','?','~','^','>',
                       '<','ª','º',"'" );
        // matriz de saída
        $by   = array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u',
                       'A','A','A','A','E','I','O','O','O','U','n','N','c','C',
                       $sp,'-',' ',' ',' ',' ',' ',' ',' ',' ',' ','$',' ',' ',' ',' ',' ',' ',' ',' ',
                       ' ',' ',' '," " );
        // devolver a string
        return str_replace($what, $by, $string);
    }//Fim do Modúlo
/**
 *
 **/
    public static function limpaString($string,$modo = 'no_simbolo')
    {
        switch (strtolower($modo) )
        {
            case 'no_simbolo':
                //Removendo símbolos de uma string (caracteres não alfa-numéricos)
                $nova_string = preg_replace("/[^a-zA-Z0-9\s]/", "", $string);
                break;
            case 'no_simbolo_numero':
                //Removendo símbolos e números (Deixa só letras)
                $nova_string = preg_replace("/[^a-zA-Z\s]/", "", $string);
                break;
            case 'no_simbolo_letra':
                //Removendo letras e símbolos (deixa só numeros)
                $nova_string = preg_replace("/[^0-9\s]/", "", $string);
                break;
            case 'no_simbolo_espaco':
                //Removendo símbolos de uma string (Deixa só alfa-numéricos sem espaço)
                $nova_string = preg_replace("/[^a-zA-Z0-9]/", "", $string);
                break;
            case 'no_espaco':
                //Removendo espaço uma string
                $nova_string = str_replace(' ', '', $string);
                break;
            default:
                $nova_string = $string;
                break;
        }
        return $nova_string;
    }//Fim Método
/**
 *  DESCRICAO: separa, ddd do numero do telefone
 * @param $dado = telefone
 * @return array ('ddd','fone')
 **/
    public function formata_fone ($dado=null) 
    {
        if (is_array($dado)) 
        {
            if (strlen($dado['telefone'])>4) 
            {
                $fone = $dado['telefone'];
            } 
            elseif (strlen($dado['celular'])>4) 
            {
                $fone = $dado['celular'];
            } 
            else 
            {
                $ret = array ('ddd'=>00,'fone'=>'00000000');
                return $ret;
            }
        } 
        else 
        {
            $fone = $dado;
        }
        $simbolos = array("(", ")", "-", ".", "[", "]", "_");
        $fone = str_replace($simbolos, "", $fone);
        if (strlen($fone)<9) 
        {
            $ret['ddd'] = "";
            $ret['fone'] = $fone;
        } 
        else 
        {
            $ret['ddd'] = substr($fone,0,2);
            $ret['fone'] = substr($fone,2);
        }
        return $ret;
    }//Fim Módulo
/**
 *  Pega o dados de um diretório. Retorna uma array com nome e url
 * @param $path a ser pesquisada
 * @param $file arquivo específico
 * @return array com dados do diretório  
 **/
    public function getDiretorio ($path = null, $file = null )
    {
        $path = (empty($path)) ? 'app/output/' : $path;
        $file = (empty($file)) ? '*.*' : $file;
        $arquivos_pattern = glob($path . $file);
        if(!empty($arquivos_pattern)) 
        {
            $result = array();
            foreach($arquivos_pattern as $arquivo) 
            {
                $result[] = array ('nome'=>$arquivo,'url'=>basename($arquivo));
            }
        }
        else
        {
            return false;
        }
        return $result;
    }//Fim Módulo
/**
 *  Retorna uma coluna de uma array (para versão php <5.5)
 * @param $input = array de trabalho
 * @param $columnKey = Coluna desejada
 * @param $indexKey
 * @return uma das colunas
 **/
    public function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }
        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }
            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
/**
 *   Funçao calculo de tempo hora minuto segundos
 * @param $param = horas total
 * @return string com Horas, Minutos e segundos
 **/
    public function tempo_descrito($param=null)
    {
        $hora = (int) ($param / 360);
        $min  = (int) (($param - ($hora * 360))/60);
        $seg  = (int) ($param - (($hora*360)+($min*60)));
        $texto = '';
        if ($hora)
        {
            $texto .= $hora;
            $texto .= ($hora > 1) ? " horas" : " horas";
        }
        if ($min)
        {
            $texto .= (strlen($texto)>1) ? ", " : " ";
            $texto .= $min;
            $texto .= ($min>1) ? " minutos" : " minuto"; 
        }
        if ($seg)
        {
            $texto .= (strlen($texto)>1) ? ", " : " ";
            $texto .= $seg;
            $texto .= ($seg>1) ? " segundos" : " segundo "; 
        }
        return $texto;
    }//Fim Módulo
/**
 *   Funçao retorna nível de acesso da classe 
 * @param $param = classe pesquisada
 * @return nível de acesso à classe pesquisada
 **/
    public static function getnivel($param = null)
    {
        //Carrega o que já existe na seção
        $lista_niveis = TSession::getValue('lista_niveis');
        if (empty($lista_niveis) )
        {
            $lista_niveis  = array();
            //Procura a classe entre as que o usuário tem acesso
            try
            {
                TTransaction::open('sisopm');
                
                //Carrega os grupos do usuário
                $items         = TSession::getValue('usergroupids');
                $criteria      = new TCriteria;
                $criteria->add(new TFilter('id','IN',$items) );
                $groups        = SystemGroup::getObjects($criteria);
                
                if ($groups)
                {
                    //Varre os grupos
                    foreach($groups as $group)
                    {
                        //$group = new SystemGroup;
                        $programs = $group->getSystemPrograms();
                        if ($programs)
                        {
                            //Varre os programas pegando o maior nível de acesso do grupo
                            foreach($programs as $program)
                            {
                                $nivel_atual = $lista_niveis[$program->controller] ?? 0;
                                if ($group->acess > $nivel_atual)
                                {
                                    $lista_niveis[$program->controller] = $group->acess;
                                }
                            }
                        }
                    }
                }
                TTransaction::close();
            }
            catch (Exception $e)
            {
                TTransaction::rollback();
            }
        }
        TSession::setValue('lista_niveis',$lista_niveis);
        return $lista_niveis[$param] ?? 0;
    }//Fim Módulo
/**
 *  Retorna os itens de configuração de funcionamento
 * @param $param = item de configuração pesquisado
 * @return definições do item pesquisado 
 **/
    public function getConfig ($param = null)
    {
        $lista_configs = TSession::getValue('lista_configs');
        if (empty($lista_configs) )
        {
            $lista_configs = array();
            try
            {
                TTransaction::open('sisopm');
                
                //Carregas os sistemas atendidos
                $criteria = TComp::defineCriteriaItem('configura',100);
                $sistemas = Item::getObjects($criteria);
                //Varre os sistemas criando todas definições
                if ($sistemas)
                {
                    //Carregas os itens de configuração
                    foreach($sistemas as $sistema)
                    {
                        $configs = configura::getConfiguracao($sistema->nome,['ativo'=>'S','visivel'=>'S']);
                        if ($configs)
                        {
                            $lista = array();
                            foreach($configs as $config)
                            {
                                $lista[$config->name] = $config->value;
                            }
                        }
                        else
                        {
                            $lista = '';
                        }
                        $lista_configs[$sistema->nome] = $lista;
                    }
                }
                else
                {
                    $lista_configs = false;
                }
            }
            catch (Exception $e) // in case of exception
            {
                new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        }
        TSession::getValue('lista_configs',$lista_configs);
        return $lista_configs[$param] ?? 0;
    }//Fim Módulo
/**
 *   Pega o arquivo de configuração do sisopm
 * @return dados da configuração
 **/
    public static function getConfig_sisopm($param = null)
    {
        //Define o local do arquivo de configuração
        $arq    = "app/config/sisopm_cfg.ini";//Trocar o nome do arquivo ini se for pertinente
        if (file_exists($arq))//Se existe carrega 
        {        
            $date   = parse_ini_file($arq, true);
        }
        else//Se não existe cria os dados
        {
            $date = array('config_geral'=>array('ambiente'=>'local',
                                                'site_sso'=>"https://ssows-h.ssp.go.gov.br/auth?response_type=token_only",
                                                'logoff_sso'=>"https://ssows-h.ssp.go.gov.br/logout/?response_type=token",
                                                'validade'=>"https://ssows-h.ssp.go.gov.br/validate?token=",
                                                'base_url'=>"http://sisopm-homo.ssp.go.gov.br"));//Trocar a URL
        }
        $server = $_SERVER;
        $local  = $server['SERVER_NAME'];
        //Indice de nomes do servidor e seu retorno
        $indice = array('localhost'=>'local',
                        'sisopm.pm.go.gov.br'=>'producao',
                        'sisopm-homo.ssp.go.gov.br'=>'homologa');//Trocar as URLs
        //Redefine o ambiente
        $date['config_geral']['ambiente'] = $indice[$local];
        if ($date['config_geral']['ambiente'] == 'producao')
        {
            $date['config_geral']['base_url'] = "http://sisopm.pm.go.gov.br";//Trocar a URL
        }
        return $date; 
    }//Fim Módulo

/**
 *        Monta uma lista de assinantes da OPM
 * @param $param = id da OPM
 * @return array () de nomes dos militares da OPM 
 **/
    public function getAssinantes ($param = null)
    {
        $lista = array('0'=>'--- Não Localizei Militares nesta OPM ---');
        $ativo = 'N';
        if ($param != null)
        {
            try
            {
                TTransaction::open('sisopm'); // open a transaction
                $sql = "SELECT DISTINCT servidor.rgmilitar AS rgmilitar, ". 
                                "servidor.postograd || ' ' || servidor.rgmilitar || ' ' || servidor.nome AS nome, ".
                                "item.ordem ".
                        "FROM efetivo.servidor JOIN opmv.item ON servidor.postograd = item.nome ".
                        "WHERE unidadeid IN ($param) ";
                if ($ativo =='N')
                {
                    $sql .= "AND status = 'ATIVO' "; 
                }
                $sql .="ORDER BY item.ordem, nome ASC;";
                $conn = TTransaction::get();
                $res = $conn->prepare($sql);
                $res->execute();
                $militares = $res->fetchAll(PDO::FETCH_NAMED);
                //var_dump($militares);
                $lista = array();
                foreach ($militares as $militar)
                {
                    $lista[$militar['rgmilitar']] = $militar['nome'];
                }
                TTransaction::close();   
            }
            catch (Exception $e) // in case of exception
            {
                //new TMessage('error', $e->getMessage()); // shows the exception error message
                TTransaction::rollback(); // undo all pending operations
            }
        }
        return $lista;
    }//Fim Módulo
/*-------------------------------------------------------------------------------
 *        Monta uma assinatura para relatórios
 *-------------------------------------------------------------------------------*/
    public function getAssinatura($param=null)
    {
        $assina   = "<br><br><center>";
        $profile  = TSession::getValue('profile');
        if (LOCAL != 'localhost' || $param != null)
        {
            if ($param == null) // Continua a pegar a assinatura do usuário atual
            {
                $assina .= $profile['nome']." - ";
                try
                {
                    TTransaction::open('sisopm');
                    $militar = servidor::where('cpf','=',$profile['cpf'])->load();//Busca dados do militar
                    TTransaction::close();
                    $posto   = (array_key_exists(0,$militar)) ? $militar[0]->postograd : "";
                    $opm     = false;
                    if (array_key_exists('unidade',$profile))
                    {
                        $opm = $profile['unidade']['nome'];
                    }
                }
                catch (Exception $e) // in case of exception
                {
                    //new TMessage('error', $e->getMessage()); // shows the exception error message
                    $posto = ''; // keep form data
                    TTransaction::rollback(); // undo all pending operations
                }
                $local   = '<br><br><p style="text-align: right;">';
                $local  .= ($opm!=false) ? $opm.',' : '';
                $local  .= $this->dataExtenso()."</p>";
                $assina .= $posto." RG ".$profile['rg'];
                $assina .= "<br>".$profile['funcao'];
            }
            else   //Usa a assinatura do usuário indicado por $param
            {
                try
                {
                    TTransaction::open('sisopm');
                    if (strlen($param)== 11 )
                    {
                        $militar = servidor::where('cpf','=',$param)->load();//Busca dados do militar
                    }
                    else
                    {
                        $militar = servidor::where('rgmilitar','=',$param)->load();//Busca dados do militar
                    }
                    if (!empty($militar))
                    {
                        $assina    .= (!empty($militar[0]->nome))      ? $militar[0]->nome              : '- NC -';
                        $posto      = (!empty($militar[0]->postograd)) ? $militar[0]->postograd : '';
                        $rgmilitar  = (!empty($militar[0]->rgmilitar)) ? $militar[0]->rgmilitar         : '';
                        $opm        = (!empty($militar[0]->postograd)) ? $militar[0]->unidade           : '';
                        $funcao     = (!empty($militar[0]->funcao))    ? $militar[0]->funcao            : 'RELATOR';
                    }
                    TTransaction::close();
                }
                catch (Exception $e) // in case of exception
                {
                    //new TMessage('error', $e->getMessage()); // shows the exception error message
                    $posto      = '';
                    $rgmilitar  = '';
                    $opm        = false;
                    $funcao     = '';
                    TTransaction::rollback(); // undo all pending operations
                }
                $local   = '<br><br><p style="text-align: right;">';
                $local  .= ($opm != false) ? $opm.',' : '';
                $local  .= $this->dataExtenso()."</p>";
                $assina .= ((!empty($posto)) ? " - " : '') . $posto . ((!empty($rgmilitar)) ? " RG " : '') . $rgmilitar;
                $assina .= "<br>".$funcao;
            }
        }
        else
        {
            $local   = '<br><br><p style="text-align: right;">';
            $local  .= $this->dataExtenso()."</p>";
            $assina .= TSession::getValue('username')."<br>Relator";

        }
        $assina = $local . $assina;        
        return $assina."</center>";
    }//Fim Módulo

/*----------------------------------------------------------------------------------------
 * Nota: csv_in_array
 *----------------------------------------------------------------------------------------
fuction call with 4 parameters:

(1) = the file with CSV data (url / string)
(2) = colum delimiter (e.g: ; or | or , ...)
(3) = values enclosed by (e.g: ' or " or ^ or ...)
(4) = with or without 1st row = head (true/false)

// ----- call ------
$csvdata = csv_in_array( $yourcsvfile, ";", "\"", true ); 
 *----------------------------------------------------------------------------------------*/
    public function csv_in_array($url,$delm=";",$encl="\"",$head=false) 
    {
       
        $csvxrow = file($url);   // ---- csv rows to array ----
       
        $csvxrow[0] = chop($csvxrow[0]);
        $csvxrow[0] = str_replace($encl,'',$csvxrow[0]);
        $keydata = explode($delm,$csvxrow[0]);
        $keynumb = count($keydata);
        $keydata = array_change_key_case($keydata,CASE_UPPER);
        
       
        if ($head === true) 
        {
            $anzdata = count($csvxrow);//Quantidade de linhas no arquivo
            $z=0;
            for($x=1; $x<$anzdata; $x++) 
            {
                $csvxrow[$x]  = chop($csvxrow[$x]);
                $csvxrow[$x]  = str_replace($encl,'',$csvxrow[$x]);
                $csv_data[$x] = explode($delm,$csvxrow[$x]);
                $i=0;
                foreach($keydata as $key) 
                {
                    if (isset($csv_data[$x][$i]))
                    {
                        //$out[$z][$key] = iconv(mb_detect_encoding($csv_data[$x][$i]), "UTF-8//IGNORE", $csv_data[$x][$i]);
                        $out[$z][$key] = strtoupper($this->ConvertToUTF8($csv_data[$x][$i]));
                    }
                    $i++;
                }   
                $z++;
            }
        }
        else 
        {
            $i=0;
            foreach($csvxrow as $item) 
            {
                $item = chop($item);
                $item = str_replace($encl,'',$item);
                $csv_data = explode($delm,$item);
                for ($y=0; $y<$keynumb; $y++) 
                {
                   $out[$i][$y] = strtoupper($this->ConvertToUTF8($csv_data[$y]));
                }
                $i++;
            }
        }
    return array_change_key_case($out,CASE_UPPER);
    }//Fim do Módulo
/**
 * Convert a comma separated file into an associated array.
 * The first row should contain the array keys.
 * 
 * Example:
 * 
 * @param string $filename Path to the CSV file
 * @param string $delimiter The separator used in the file
 * @return array
 * @link http://gist.github.com/385876
 * @author Jay Williams <http://myd3.com/>
 * @copyright Copyright (c) 2010, Jay Williams
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 **/
    public static function csv_to_array($filename, $delimiter = ';')
    {
    	if(!file_exists($filename) || !is_readable($filename))
    	{
    		//var_dump($filename);
    		return FALSE;
    	}
    	
    	$header = NULL;
    	$data   = array();
    	$fer    = self::instancia();
    	if (($handle = fopen($filename, 'r')) !== FALSE)
    	{
    		while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE)
    		{
    			if(!$header)
    			{
    				$header = array();
    				foreach($row as $key => $value)
    				{
                        $header[] = TFerramentas::trataNomeIdentificador($value);
                    }
    		    }
    			else
    			{
    				$dado = [];
    				foreach($row as $key => $string)
    				{
                        $dado[$key] = $fer->ConvertToUTF8($string);
                    }
    				//Combina os campos com os cabeçalhos
    				$data[] = array_combine($header, $dado );
    			}
    		}
    		fclose($handle);
    	}
    	return $data;
    }
/*----------------------------------------------------------------------------------------
 * Nota: Conversor de caracteres para UTF8
 *----------------------------------------------------------------------------------------*/
    public function ConvertToUTF8($text)
    {
        $encoding = mb_detect_encoding($text.'x', mb_detect_order(), false);
        if($encoding == "UTF-8")
        {
            //Converte letra a letra
            $i    = 0;
            $conv = '';
            do 
            {
                $letra = substr($text,$i,1);
                $conv .= iconv(mb_detect_encoding($letra, mb_detect_order(), true), "UTF-8//IGNORE", $letra);
                $i ++;
            } while ($i < strlen($text));
            $text = $conv;
        }
        else if ($encoding == 'ISO-8859-1')
        {
            $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        }
        else if ($encoding == 'ASCII')
        {
            $text = mb_convert_encoding($text, "UTF-8");
        }
        $out = iconv(mb_detect_encoding($text.'x', mb_detect_order(), false), "UTF-8//TRANSLIT//IGNORE", $text);

        return $out;
    }//Fim Módulo
/*----------------------------------------------------------------------------------------
 * Nota: Conversor de caracteres para ISO-8859-1
 *----------------------------------------------------------------------------------------*/
    public function ConvertToISO($text)
    {
        return iconv(mb_detect_encoding($text, mb_detect_order(), true), "ISO-8859-1//IGNORE", $text);
    }//Fim Módulo
/*----------------------------------------------------------------------------------------
 * Nota: Gerador de Senhas automática
 *----------------------------------------------------------------------------------------*/
    public function gerar_senha($tamanho = 8, $maiusculas = true, $minusculas = true, $numeros = true, $simbolos = true)
    {
        $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
        $mi = "abcdefghijklmnopqrstuvyxwz"; // $mi contem as letras minusculas
        $nu = "0123456789";                 // $nu contem os números
        $si = "!@#$%¨&*()_+=";              // $si contem os símbolos
        $senha = '';
        if ($maiusculas)
        {
            // se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($ma);
        }
        if ($minusculas)
        {
            // se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($mi);
        }
        if ($numeros)
        {
            // se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($nu);
        }
         
        if ($simbolos)
        {
            // se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $senha
            $senha .= str_shuffle($si);
        }
        // retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
        return substr(str_shuffle($senha),0,$tamanho);
    }//Fim Módulo
/**
 * Nota: Gera um profile falso para testes
 * @param   $param Login do usuário (cpf)
 * @return  array de profile(montado)
 **/
    public function get_Profile ($param = null)
    {
        $ci  = new TSicadDados();
        $base = '{"dtCadastro":"2014-03-24","perfis":['.
                 '{"id":41,"sistema":{"id":61,"descricao":"SIEI"},"descricao":"Consulta"},'.
                 '{"id":230,"sistema":{"id":43,"descricao":"sigu"},"descricao":"ADM_PM","restrito":true},'.
                 '{"id":252,"sistema":{"id":162,"descricao":"SRO"},"descricao":"CONSULTA"},'.
                 '{"id":1,"sistema":{"id":1,"descricao":"mportal"},"descricao":"Consulta"},'.
                 '{"id":248,"sistema":{"id":21,"descricao":"geocontrol"},"descricao":"ADM_SAC"},'.
                 '{"id":508,"sistema":{"id":142,"descricao":"gescop"},"descricao":"CONSULTA"},'.
                 '{"id":710,"sistema":{"id":426,"descricao":"geocontrol2"},"descricao":"DESPACHANTE"},'.
                 '{"id":708,"sistema":{"id":426,"descricao":"geocontrol2"},"descricao":"ADM","restrito":false},'.
                 '{"id":550,"sistema":{"id":362,"descricao":"atendimento"},"descricao":"ATENDENTE"},'.
                 '{"id":587,"sistema":{"id":364,"descricao":"legado"},"descricao":"BASICO"},'.
                 '{"id":552,"sistema":{"id":364,"descricao":"legado"},"descricao":"ADM","restrito":true},'.
                 '{"id":568,"sistema":{"id":322,"descricao":"despacho"},"descricao":"DESPACHANTE"},'.
                 '{"id":608,"sistema":{"id":386,"descricao":"SISFREAP"},"descricao":"ADM","restrito":false},'.
                 '{"id":1048,"sistema":{"id":606,"descricao":"sisopm"},"descricao":"ADMINISTRADOR","restrito":false},'.
                 '{"id":748,"sistema":{"id":446,"descricao":"SISVTR"},"descricao":"ADM","restrito":false},'.
                 '{"id":754,"sistema":{"id":448,"descricao":"SISPAT"},"descricao":"CARACTERIZADOR","restrito":false},'.
                 '{"id":750,"sistema":{"id":448,"descricao":"SISPAT"},"descricao":"ADM","restrito":false},'.
                 '{"id":554,"sistema":{"id":262,"descricao":"escala"},"descricao":"ESCALADOR"},'.
                 '{"id":571,"sistema":{"id":365,"descricao":"detran"},"descricao":"ESCRIVAO_DERFRVA"},'.
                 '{"id":569,"sistema":{"id":365,"descricao":"detran"},"descricao":"ATENDIMENTO"},'.
                 '{"id":894,"sistema":{"id":508,"descricao":"BI"},"descricao":"PENTAHO","restrito":false},'.
                 '{"id":892,"sistema":{"id":508,"descricao":"BI"},"descricao":"PAINEL_ESTRATEGICO","restrito":false},'.
                 '{"id":988,"sistema":{"id":546,"descricao":"SICAD"},"descricao":"CONSULTA","restrito":false}],'.
                 '"rg":"30089","corporacao":"PM","administrador":false,"id":847,"funcao":"AUXILIAR",'.
                 '"email":"o.megapinho@gmail.com","telefone":"(62)9244-7470","dtExtincao":"2017-04-30",'.
                 '"nome":"FULANO","cpf":"00000000011","login":"00000000011",'.
                 '"unidade":{"id":217,"corporacaoId":4,"sigla":"01º(01ºCRPM)-BASE","nome":"01º BATALHÃO DE POLÍCIA MILITAR (01ºCRPM)-BASE",'.
                 '"corporacao":"PM"}} ';        
        //Transforma o $base em array
        $profile          = $ci->object_to_array(json_decode ($base));
        //Carrega os dados do servidor
        $user             = new SystemUser(TSession::getValue('userid'));
        $servidor         = $user->servidor;
        //Atualiza o profile
        $profile['nome']   = TSession::getValue('username');
        $profile['email']  = (!empty($user->email)) ? $user->email : 'sinal_de_fumaca@apache.org.rs';
        //Se tem cadastro como servidor
        if ($servidor)
        {
            //$servidor = new servidor;
            $profile['cpf']       = $servidor->cpf;
            $profile['login']     = $servidor->cpf;
            $profile['rg']        = $servidor->rgmilitar;
            $profile['telefone']  = $servidor->telefonecelular;
            if (is_null($servidor->funcao) )
            {
                $profile['funcao'] = 'AUXILIAR';
            }
            else
            {
                $profile['funcao'] = $servidor->funcao;
            }
            //Unidade
            $profile['unidade']['id']    = $servidor->unidadeid;
            $profile['unidade']['sigla'] = $servidor->siglaunidade;
            $unidade                     = new OPM($servidor->unidadeid);
            //Completa dados da Unidade
            if (!empty($unidade->nome) )
            {
                $profile['unidade']['nome'] = $unidade->nome;
            }
            else
            {
                $profile['unidade']['nome'] = $profile['unidade']['sigla'];
            }
        }
        else
        {
            $profile['cpf']       = TSession::getValue('login');
            $profile['login']     = $profile['cpf'];
            $unidade              = new OPM($user->unit->id);
            //Completa dados da Unidade
            if (!empty($unidade->nome) )
            {
                $profile['unidade']['nome']  = $unidade->nome;
                $profile['unidade']['id']    = $unidade->id;
                $profile['unidade']['sigla'] = $unidade->sigla;
            }
            else
            {
                $profile['unidade']['nome']  = '01º BATALHÃO DE POLÍCIA MILITAR (01ºCRPM)-AUTO';
                $profile['unidade']['id']    = 217;
                $profile['unidade']['sigla'] = '01ºBPM(01ºCRPM)-AUTO';
            }
        }
        return $profile;
    }//Fim Módulo
 /**
  * Executor de querys
  * @param $sql deve ser uma query preparada para ser aplicada
  **/
     public function runQuery($sql)
     {
        try
        {
            TTransaction::open('sisopm');
            set_time_limit(360);
            $conn = TTransaction::get();
            $res = $conn->prepare($sql);
            $res->execute();
            $retorno = $res->fetchAll(PDO::FETCH_NAMED);
            TTransaction::close();
            return $retorno;
        }
        catch (Exception $e) 
        { 
            new TMessage('error', $e->getMessage().'<br>Erro ao buscar dados.<br>'.$sql); 
            TTransaction::rollback();
            return false;
        }
    }//Fim Módulo
 /*-------------------------------------------------------------------------------
  *        Verifica qual status do usuário em um sistema
  * 
  *-------------------------------------------------------------------------------*/
    public function Usuario_Acess_System($param)
    {
        $sql = "SELECT system_group.name FROM g_system.system_user_group, ".
               "g_system.system_group, g_system.system_user ".
               "WHERE system_user_group.system_user_id = system_user.id AND ".
               "system_group.id = system_user_group.system_group_id AND ".
               "system_user.id = ". TSession::getValue('userid'). " AND system_group.system_id = " . $param['sistema'] . " AND ".
               "system_group.acess > " . $param['acess'];
        $ret = $this->runQuery($sql);
        return (!empty($ret)) ? true : false; 
    }//Fim Módulo
 /*-------------------------------------------------------------------------------
  *        Mascarador
  * 
  *-------------------------------------------------------------------------------*/
    public function mascara($val, $mask)
    {
         $maskared = '';
    	 $k = 0;
    	 for($i = 0; $i<=strlen($mask)-1; $i++)
    	 {
        	 if($mask[$i] == '#')
        	 {
            	 if(isset($val[$k]))
            	 {
            	     $maskared .= $val[$k++];
            	 }
        	 }
        	 else
        	 {
        	     if(isset($mask[$i]))
        	     {
        	         $maskared .= $mask[$i];
        	     }
        	 }
         }
    	 return $maskared;
    }
 /**
  *        Removedor de Letras, deixa somente números
  * @param $param = string a limpar
  * @return   string limpa 
  **/
    public static function soNumeros($param)
    {
        return preg_replace("/[^0-9]/", "", $param);
    }//Fim Módulo 
 /*-------------------------------------------------------------------------------
  *        Gerador de tabela
  * Gera uma tabela HTML com os registros do resultado de uma consulta SQL
  * @author Rafael Wendel Pinheiro     - Criador da idéia
  * @author Fernanando de Pinho Araújo - Modificações
  * @param $rows    = array com resultados (0=>array(cel,cel,cel,...),1=>(cel,cel...))
  * @param $headers = array com os cabeçalhos da tabela
  * @param $width   = tamanho das celulas, pode ser tamanho unico ou um array de tamanhos
  * @return $s = Tabela
  *-------------------------------------------------------------------------------*/
    public function geraTabelaHTML($rows, $headers, $stylus = array() , $width = null)
    {
        //Define os complementos de cada tab html
        $tab  = (isset($stylus['tab']))  ? $stylus['tab']  : '';//Para Tabela
        $cab  = (isset($stylus['cab']))  ? $stylus['cab']  : '';//Para o cabecalho da tabela
        $lnh  = (isset($stylus['row']))  ? $stylus['row']  : '';//Para linhas da tabela
        $cel  = (isset($stylus['cell'])) ? $stylus['cell'] : '';//Para Celulas da tabela

        $s     = '';//Saída da tabela
        $s    .= '<table class="tabela" cellspacing="0" cellpadding="0" ' . $tab . '>';
        $s    .= '<tr class="tabela_titulo" ' . $cab . '>';
        $cel_c = 0;
        foreach ($headers as $header)//Inclui o cabeçalho da tabela
        {
            if ($width == null)
            {
                $s .=  '<td class="tabela_cabecalho" ' . $cel. '>' . $header . '</td>';
            }
            else
            {
                if (is_array($width))
                {
                    $s .=  '<td class="tabela_cabecalho" ' . $cel . ' width="' .$width[$cel_c] .'">' . $header . '</td>';
                    if (isset($width[$cel_c+1]))
                    {
                        $cel_c ++;
                    }
                }
                else
                {
                    $s .=  '<td class="tabela_cabecalho" ' . $cel . ' width="' .$width .'">' . $header . '</td>';
                }
            }
        }
        $s .= '</tr>';		  
        foreach ($rows as $row)//inclui as linhas e celulas da tabela
        {
            $s    .= '<tr  class="tabela_linha" ' . $lnh . '>';
            $cel_c = 0;
            foreach ($row as $cell)
            {
                //$s .=  '<td  class="tabela_celula" ' . $cel . '>' . $cell . '</td>';
                if (is_array($width))
                {
                    $s .=  '<td class="tabela_celula" ' . $cel . ' width="' .$width[$cel_c] .'">' . $cell . '</td>';
                    if (isset($width[$cel_c+1]))
                    {
                        $cel_c ++;
                    }
                }
                else
                {
                    $s .=  '<td class="tabela_celula" ' . $cel . ' width="' .$width .'">' . $cell . '</td>';
                }
            }		  
            $s .= '</tr>';		  		  
        }
        $s .= '</table>';	  
        return $s;
    }
 /*-------------------------------------------------------------------------------
  *        Gerador de Lista
  * Gera uma Lista HTML com os registros do resultado de uma consulta SQL
  * @author Fernanando de Pinho Araújo 
  * @param $rows    = array com resultados (0=>array(cel,cel,cel,...),1=>(cel,cel...))
  * @param $headers = array com os cabeçalhos da tabela
  * @return $s = Tabela
  *-------------------------------------------------------------------------------*/
    public function geraListaHTML($rows, $stylus = array())
    {
        //Define os complementos de cada tab html
        $lst  = (isset($stylus['lst']))  ? $stylus['lst']  : '';//Para Tabela
        $itm  = (isset($stylus['itm']))  ? $stylus['itm']  : '';//Para o cabecalho da tabela
        $s = '';
        $s .= "<ul class='lista' $lst>";
        foreach ($rows as $row)//Inclui itens
        {
            $s .=  "<li class='lista_item' $itm>$row</li>";
        }
        $s .= "</ul>";	  
        return $s;
    }//Fim Módulo
 /**
  *  Formata numero para monetário BR,EUA
  * @param  $param  = valor a formatar
  * @param  $format = formato da apresentação (BR ou EU)
  * @return string com apresentação monetária
  **/
    public function formataDinheiro($param, $format = "BR")
    {
        switch ($format)
        {
            case "BR":
                return 'R$' . number_format($param, 2, ',', '.');
                break;
            case "EU":
                return 'US$' . number_format($param, 2, '.', ',');
                break;
        }
        return 'R$ ' . number_format($param, 2, ',', '.');
    }//Fim Módulo
/*------------------------------------------------------------------------------
 *   Busca código de característica do SICAD
 *------------------------------------------------------------------------------*/
    public function codigoCaracteristica ($param,$codigo )
    {
        $sicad       = new TSicadDados();
        $param       = substr(strtoupper($param),0,strlen($param)-1);           //Deixa tudo em caixa alta
        $dados_sicad = $sicad->caracteristicas_SICAD($codigo);                  //Busca tabela de características
        $key         = array_search($param, $dados_sicad); // $key = 2;
        $key = ($key != false) ? $key : null; 
        return $key;        
    }//Fim Módulo
/*------------------------------------------------------------------------------
 *   Busca código de característica Tipo Sangue
 *------------------------------------------------------------------------------*/
    public function codigoSangue ($param, $codigo )
    {
        $param       = strtoupper($param);                  //Deixa tudo em caixa alta
        $key = false;
        if ($codigo = 'sangue')
        {
            if (strpos ($param,'AB') === 0)
            {
                $key = 'AB';
            }
            else if (strpos ($param,'O') === 0)
            {
                $key = 'O';
            }
            else if (strpos ($param,'A') === 0)
            {
                $key = 'A';
            }
        }
        else if ($codigo = 'fatorrh')
        {
            if (strpos ($param,'POSITIV') > 0 || strpos ($param,'+'))
            {
                $key = 'POSITIVO';
            }
            else if (strpos ($param,'NEGATIV') > 0 || strpos ($param,'-'))
            {
                $key = 'NEGATIVO';
            }
        }
        $key = ($key != false) ? $key : null; 
        return $key;        
    }//Fim Módulo
/*------------------------------------------------------------------------------
 *   Busca corrigir altura e peso
 *------------------------------------------------------------------------------*/
    public function alturaPeso ($param = null,$codigo )
    {
        if (empty($param)) 
        {
            return 0;
        }
        $param      = $this->soNumeros($param);
        if (strlen($param)<3 && $codigo == 'altura')
        {
            $param .= '0';
        }
        $param = ($codigo == 'altura')? $param/100 : $param;
        return $param;        
    }//Fim Módulo
/*------------------------------------------------------------------------------
 *   Retorna Array com ids dos usuários de uma dado Sistema
 *------------------------------------------------------------------------------*/
    public function getIdsDestino ($param)
    {
        $sql1 = "(SELECT id FROM opmv.item WHERE nome = '{$param['sistema']}' )";
        $sql2 = "(SELECT id FROM g_system.system_group WHERE system_id IN $sql1 )";
        $sql3 = "(SELECT system_user_id FROM g_system.system_user_group WHERE system_group_id IN $sql2 )";
        $sql4 = "SELECT id FROM g_system.system_user WHERE id IN $sql3";
        $rets = $this->runQuery($sql4);
        if ($rets)
        {
            $lista = array();
            foreach($rets as $ret)
            {
                $lista[] = ($ret) ? implode(',',$ret)  : '';
            } 
        }
        return $lista;        
    }//Fim Módulo
/**
 *   converte Número e sua versão por extenso
 * @param $number = numero a ser escrito por extenso
 * @return string com numero por extenso
 **/
     public function numeroExtenso($number) 
     {
        $hyphen      = '-';
        $conjunction = ' e ';
        $separator   = ', ';
        $negative    = 'menos ';
        $decimal     = ' ponto ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'um',
            2                   => 'dois',
            3                   => 'três',
            4                   => 'quatro',
            5                   => 'cinco',
            6                   => 'seis',
            7                   => 'sete',
            8                   => 'oito',
            9                   => 'nove',
            10                  => 'dez',
            11                  => 'onze',
            12                  => 'doze',
            13                  => 'treze',
            14                  => 'quatorze',
            15                  => 'quinze',
            16                  => 'dezesseis',
            17                  => 'dezessete',
            18                  => 'dezoito',
            19                  => 'dezenove',
            20                  => 'vinte',
            30                  => 'trinta',
            40                  => 'quarenta',
            50                  => 'cinquenta',
            60                  => 'sessenta',
            70                  => 'setenta',
            80                  => 'oitenta',
            90                  => 'noventa',
            100                 => 'cento',
            200                 => 'duzentos',
            300                 => 'trezentos',
            400                 => 'quatrocentos',
            500                 => 'quinhentos',
            600                 => 'seiscentos',
            700                 => 'setecentos',
            800                 => 'oitocentos',
            900                 => 'novecentos',
            1000                => 'mil',
            1000000             => array('milhão', 'milhões'),
            1000000000          => array('bilhão', 'bilhões'),
            1000000000000       => array('trilhão', 'trilhões'),
            1000000000000000    => array('quatrilhão', 'quatrilhões'),
            1000000000000000000 => array('quinquilhão', 'quinquilhões')
        );
    
        if (!is_numeric($number)) 
        {
            return false;
        }
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) 
        {
            // overflow
            trigger_error(
                'convert_number_to_words só aceita números entre ' . PHP_INT_MAX . ' à ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }
        if ($number < 0) 
        {
            return $negative . self::numeroExtenso(abs($number));
        }
        $string = $fraction = null;
        if (strpos($number, '.') !== false) 
        {
            list($number, $fraction) = explode('.', $number);
        }
    
        switch (true) 
        {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) 
                {
                    $string .= $conjunction . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = floor($number / 100)*100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds];
                if ($remainder) 
                {
                    $string .= $conjunction . self::numeroExtenso($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                if ($baseUnit == 1000) 
                {
                    $string = self::numeroExtenso($numBaseUnits) . ' ' . $dictionary[1000];
                } 
                elseif ($numBaseUnits == 1) 
                {
                    $string = self::numeroExtenso($numBaseUnits) . ' ' . $dictionary[$baseUnit][0];
                } 
                else 
                {
                    $string = self::numeroExtenso($numBaseUnits) . ' ' . $dictionary[$baseUnit][1];
                }
                if ($remainder) 
                {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::numeroExtenso($remainder);
                }
                break;
        }
        if (null !== $fraction && is_numeric($fraction)) 
        {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) 
            {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }
        return $string;
    }
/*------------------------------------------------------------------------------
 *   Arruma stylo de fonte
 * param['cor'] = cor, param['b'] = bold,param['i'] = italic,param['back'] = cor
 *------------------------------------------------------------------------------*/
    public function font_Estilo ($text, $param = null)
    {
        $ret = '<p style=" ';
        foreach ($param as $key => $p)
        {
            switch ($key)
            {
                case 'cor':
                    $ret .= 'color=:' . $p . '; ';
                    break;
                case 'b':
                    $ret .= 'font-weight: bold; '; 
                    break;
                case 'i':
                    $ret .= 'font-style: italic; '; 
                    break;
                case 'back':
                    $ret .= 'background:' . $p . '; '; 
                    break;
            }
        }
        $ret .= '">' . $text . '</p>';
        return $ret;
    }//Fim Módulo
/**
 * Função para calcular o próximo dia útil de uma data
 * @param $date  = data inicial (DD-MM-YYYY)
 * @param $saida = formato de saída da data
 * @param $prazo = quantidade de dias a contar
 * @return proxima data em dia útil
 **/
    public function proximoDiaUtil($date, $saida = 'd/m/Y', $prazo = 1) 
    {
    	$date       = TDate::date2us($date);
        $data_banco = new DateTime($date);
        $data_banco->modify("+{$prazo} weekdays");
        $dt = $data_banco->format($saida);
        return $dt;
    }//Fim Módulo
/**
 * Captura o CEP válido e retorna um objeto
 * @param $cep = CEP Válido
 **/
    public function get_CEP($cep)
    {
        $ret = false;
        try
        {
            $cep = self::soNumeros($cep);
            if (strlen($cep)<8)
            {
                throw new Exception ('CEP em formato inválido');
            }
            $url = "https://viacep.com.br/ws/";
            $url = $url . $cep . "/json/";
            $items = json_decode(file_get_contents($url));
            if (!$items)
            {
                throw new Exception ('CEP não localizado.');
            }
            $ret = $this->object_to_array($items);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
        return $ret;
    }// Fim Módulo
/**
 * Transforma objeto em array
 * @param $data = json a converter para array
 **/    
    public static function object_to_array($data) 
    {
        if (is_array($data) || is_object($data)) 
        {
            $result = array();
            foreach ($data as $key => $value) 
            {
                $result[$key] = self::object_to_array($value);
            }
            return $result;
        }
        return $data;
    }//Fim Módulo
/**
 * Executa a limpeza das pastas do sistema
 * @param $path = pasta do sistema a limpar
 * @param $delete_all => se true apaga a pasta $path também
 * @return array('resultado','mensagem')
 **/    
    public function clear_all_path($path, $delete_all = false ) 
    {
          $files = array_diff(scandir($path), array('.','..')); 
          foreach ($files as $file) 
          { 
            (is_dir("$path/$file")) ? self::clear_all_path("$path/$file",true) : unlink("$path/$file"); 
          }
          $resultado = ($delete_all == false) ? true : rmdir($path);
          return array('resultado'=> $resultado, 'mensagem'=>''); 
    }//Fim Módulo
/**
 * Serviço temporizado de Limpa as pastas temporárias do sistema
 * @return array('resultado','mensagem')
 **/
    public function atualiza_paths()
    {
        $CI        = new TSicadDados;
        $pastas    = array('tmp/','app/output/');
        $mensagens = '';
        $resultado = true;
        foreach ($pastas as $pasta)
        {
            $retorno = self::clear_all_path($pasta);
            if ($retorno['resultado'] == false)
            {
                $mensagens .= (strlen($mensagens) > 0) ? '<br>' : '';
                $mensagens .= $retorno['mensagen'] ;
                $resultado  = false;
            }
        }
        $CI->put_atualizou('paths', TSicadDados::getTempoAtualizar('paths') );//Armazena atualização        
        return array('resultado'=>$resultado, 'mensagem'=>$mensagens);
    }//Fim Módulo
/**
 * Serviço temporizado de atualização de OPM
 * @param $opm = id da OPM a atualizar
 * @return array('resultado','mensagem')
 **/
    public function atualiza_OPM($opm)
    {
        $ci    = new TSicadDados();
        $ret   = $ci->update_pm_opm($opm);
        
        $tranf = $ci->update_transferidos('ATIVO');
        
        $ci->put_atualizou('OPM=' . $opm, TSicadDados::getTempoAtualizar('opm') );//Armazena atualização
        if ($ret == false)
        {
            $msg = 'Erro ao atualizar a sua OPM.';
        }
        else
        {
            $msg = 'Sua OPM foi atualizada.';
        }
        return array('resultado'=>true, 'mensagem'=>$msg);
    }//Fim Módulo
/**
 * Serviço temporizado de atualização de VTR
 * @param $opm = id da OPM a atualizar
 * @return array('resultado','mensagem')
 **/
    public function atualiza_VTR($opm)
    {
        $ci    = new TSicadDados();
        $ret   = $ci->update_vtr_opm($opm);
        
        //$tranf = $ci->update_transferidos('ATIVO');
        
        $ci->put_atualizou('VTR=' . $opm, TSicadDados::getTempoAtualizar('vtr') );//Armazena atualização
        if ($ret == false)
        {
            $msg = 'Erro ao atualizar as VTRs da sua OPM.';
        }
        else
        {
            $msg = 'As VTRs da sua OPM foi atualizada.';
        }
        return array('resultado'=>true, 'mensagem'=>$msg);
    }//Fim Módulo
/**
 * converte maiúscula <=> Minuscula um texto ou os valores de um array
 * @param $texto = string ou array
 * @param $tp UP/DOWN ou UPPER/LOWER
 * @return string com nova caixa
 **/
    public static function str_case($texto, $tp = 'UP') 
    { 
        if (is_string($texto) )
        {
            //quando é string
            if ($tp == "UP" || $tp == 'UPPER') 
            {
                $retorno = strtr(strtoupper($texto),"àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþºª","ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞºª");
            } 
            else if ($tp == "DOWN" || $tp == 'LOWER') 
            {
                $retorno = strtr(strtolower($texto),"ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞºª","àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþºª");
            }
            $retorno = str_replace("\"", "`",$retorno);
            $retorno = str_replace("'", "`",$retorno);
        }
        else if (is_array($texto))
        {
            //quando é array
            $retorno = array();
            foreach($texto as $key => $palavra)
            {
                $retorno[$key] = self::str_case($palavra, $tp);
            }
        }
        else
        {
            $retorno = $texto;
        }
        return $retorno;
    }//Fim Método
/**
 * Monta um histórico
 * @param $object    = deve ser um objeto model qualquer
 * @param $classe    = __METHOD__
 * @param $existente = historico já existente no model
 * @param $registro_especial = Dados extra que o sistema precisa informar
 * @return string do histórico
 **/
    public static function MakeHistorico($object, $classe, $existente = '',$registro_especial = false) 
    { 
        $item      = 0;
        $dados     = '';
        if (!is_array($object))
        {
            $object    = $object->toArray();
        }
        if (array_key_exists('historico',$object) && ($existente == '' || $existente == null))
        {
            $existente = json_decode($object['historico']);
        }
        //Dados comparados
        $object = self::comparaMudancas($object,$existente);
        foreach($object as $key => $obj)
        {
            if ($key != 'historico')
            {
                $dados .= "[$key=>" . self::removeAcentos($obj) .']';
            }
        }
        //Dados comparados
        //$dados                       = self::comparaMudancas($dados,$existente);
        //Histórico com data e user
        $historico                   = '';
        $historico                  .= '{data_registro=>' . date('Y-m-d H:m:s') . '}';//Data da modificação
        $historico                  .= '{cpf_usuario=>'   . TSession::getValue('login') . '}';//Usuário Modificador
        $historico                  .= '{classe=>'        . $classe . '}';//Classe que operou a mudança
        //Verifica se há um registro especial
        if ($registro_especial != false)
        {
            $historico              .= '{registro_especial=>' . $registro_especial . '}';//Registro especial do sistema
        }
        $historico                  .= '{dados=>'         . $dados . '}';//Dados Atualizados
        //Monta o registro total
        $retorno                     = '[REGISTRO=>'      . $historico . ']';//Junta tudo
        //Acrescenta as mudanças anteriores
        if (!empty($existente))
        { 
            $retorno                .= $existente;
        }
        return $retorno;
    }//Fim Módulo
/**
 *    Compara duas arrays de dados gerando um Histórico somente com mudanças
 * @param  $novo   => array com os dados atuais
 * @param  $velho  => json com os dados armazenados
 * @return $result => array com os dados que foram alterados
 **/
    public static function comparaMudancas ($novos,$velho)
    {
        if (empty($velho))
        {
            return $novos;
        }
        //Converte a string historico para array
        $velho = self::stringHistoricoToArray($velho);
        $table = self::historicoConsolidado($velho);
        //var_dump($table);
        //Realiza a comparação
        $retorno = array();
        foreach($novos as $key => $novo)
        {
            //Verifica se houve mudanças
            if ($key != 'historico' && (!isset($table[$key]) || self::removeAcentos($table[$key]) != self::removeAcentos($novo) ) )
            {
                $retorno[$key] = $novo;
            }
        }
        //var_dump($retorno);
        return $retorno;
    }//Fim Módulo
/**
 *    Pegando do primeiro registro (mais atual) para o ultimo, remonta uma array com todas mudanças
 * @param  $historicos => array de dados
 * @return array com dados
 **/
    public static function historicoConsolidado($historicos)
    {
        $retorno = array();
        foreach($historicos as $historico)
        {
            foreach($historico['dados'] as $key => $dado)
            {
                if (!isset($retorno[$key]) )
                {
                    $retorno[$key] = $dado;
                }
            }
        }
        return $retorno;
    }
/**
 *    Converte a string historico para uma array
 * @param  $historico => string a formatar
 * @return array de dados
 **/
    public static function stringHistoricoToArray($historico)
    {
        $n_table   = array();
        $registros = explode('[REGISTRO=>',$historico);
        foreach ($registros as $registro)
        {
            if (!empty($registro))
            {
                $registro = substr($registro,0, (strlen($registro) - 1) );//Remove ']' no fim da string
                $dados    = explode('{',$registro);
                $s_table  = array();
                foreach ($dados as $dado)
                {
                    if (!empty($dado))
                    {
                        $dado = substr($dado,0,(strlen($dado) - 1) ); // Remove '}' no fim da string
                        $dt   = explode('=>',$dado,2);
                        if (!empty($dt[0]))
                        {
                            if ($dt[0] == 'dados')
                            {
                                $infos    = explode('[',$dt[1]);
                                $dt_table = array();
                                foreach($infos as $info)
                                {
                                    if (!empty($info))
                                    {
                                        $info = substr($info,0,(strlen($info) - 1) );//Remove ']' no fim da string
                                        $dt_t = explode('=>',$info,2);
                                        if (!empty($dt_t[0]))
                                        {
                                            $dt_table[$dt_t[0]] = ($dt_t[1] ?? '');
                                        } 
                                    }
                                }
                                $s_table[$dt[0]] = $dt_table;
                            }
                            else
                            {
                                $reg             = (array_key_exists(1,$dt)) ? $dt[1] : '';
                                $s_table[$dt[0]] = $reg;
                            }
                        }
                    }
                }
                if (count($s_table) > 0)
                {
                    $n_table [] = $s_table;
                }
            }
        }
        return $n_table;
    }// Fim Módulo
/**
 * verifica e deleta um arquivo
 * @param $file    = deve ser um objeto model qualquer
 * @return true/false
 **/
    public static function clearFile($file) 
    {
        if (file_exists($file)) 
        {
            unlink($file);
            if (file_exists($file)) 
            {
                return false;
            }
        }
        return true; 
    }//Fim Módulo
/**
 * trata o nome de um arquivo elimiando sinais gráficos 
 * @param $string    = Nome a ser tratado
 * @return string limpa para usar com nome de arquivo
 *---------------------------------------------------------------------------*/
    public static function trataNomeArquivo($string)
    {
        $filename = explode('.',$string);
        $name     = preg_replace("/[^a-zA-Z0-9_]/", "_", self::removeAcentos($filename[0]));
        return self::str_case($name . "." . $filename[1],'DOWN');        
    }
/**
 * Cria identificador 
 * @param $string    = Nome a ser tratado
 * @return string limpa para usar com nome de arquivo
 *---------------------------------------------------------------------------*/
    public static function trataNomeIdentificador($string)
    {
        $name     = preg_replace("/[^a-zA-Z0-9_]/", "_", self::removeAcentos($string));
        return self::str_case($name,'DOWN');        
    }//Fim Método
/**
 * Trata o valor monentário tornando compatível com o BD
 * $param $data => valor quem pode estar no formato BR
 * @return numero no formato XXXX.YY
 **/
    public static function trataValorMonetarioBD($data)
    {
        //Troca separador de milhar e casa decimal
        if (strpos($data,',') > 0)
        {
            $data = str_replace('.' , '', $data); //Retira o separador de milhar (padrão BR)
            $data = str_replace(',' , '.', $data);//Substitui a , por . (padrão BD)
        }
        
        //Separa as partes
        $num  = explode('.',$data);
        
        if(is_array($num) )
        {
            if (count($num) > 1)
            {
                //Possui valor decimal
                $data = self::limpaString($num[0],'no_simbolo_letra') . 
                        "." .
                        self::limpaString($num[1],'no_simbolo_letra');
            }
            else
            {
                //Inteiro
                $data = self::limpaString($num[0],'no_simbolo_letra');
            }
        }
        else
        {
            //Não houve o uso do ponto separador decimal logo é inteiro
            $data = self::limpaString($data,'no_simbolo_letra');
        }
        return (float) $data;
    }//Fim metodo
/**
 * Apresenta item para debug 
 * @param $label    = Nome a ser mostrado
 * @param $mostra   = item para var_dump
 * @param $debug    = ativa ou não a apresentação
 *---------------------------------------------------------------------------*/
    public static function showDebug($label, $mostra, $debug = false)
    {
        if ($debug)
        {
            echo "<pre>{$label}";
            var_dump($mostra);
            echo "<br></pre>";
        }
        return;
    }//Fim Módulo
/**
 *    Validação estática de Login via SSO
 *
 **/
    public static function ValidarSSO()
    {
        $config = self::getConfig_sisopm();
        $handle = $config['config_geral']['ambiente'];
        if ($handle == 'local')
        {
            return true;
        }
        $token   = TSession::getValue('token');
        $sicad   = new TSicadDados();
        //Valida o Login na SSP
        $items   = $sicad->validateLogin($token,$handle);
        if (is_array($items))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
/**
 *    Verifica Erros em cUrl
 *  @param $param = strig de retorno de uma cUrl
 *  @return true/false
 **/
    public static function validaRetornoWS($param)
    {
        if(!is_array($param))
        {
            $sicad = new TSicadDados;
            $param = $sicad->object_to_array(json_decode($param) ) ;
        }
        $teste = false;
        //var_dump($param);
        $erros = array('301'=>'Moved permanently',
                       '304'=>'Not Modified',
                       '307'=>'Temporary Redirect',
                       '400'=>'Bad Request',
                       '401'=>'Authorization Required',
                       '403'=>'Forbidden',
                       '404'=>'Not Found',
                       '408'=>'Request Timeout',
                       '411'=>'HTTP Length Required',
                       '413'=>'Entity Too Large',
                       '414'=>'URI Too Large',
                       '500'=>'Internal Server Error',
                       '501'=>'Not Implemented',
                       '502'=>'Bad Gateway',
                       '503'=>'Service Unavailable',
                       '503'=>'Service Temporarily Unavailable'
                       );
        
        if ( isset($param['status']) && array_key_exists($param['status'],$erros) == true)
        {
            $teste  = "Ocorreu um erro na comunicação:<br>";
            $teste  = "Erro código: {$param['status']} - " .  $erros[$param['status'] ] . "<br>";
            $teste .= "<b>Mensagem de Retorno:</b> " .$param['message']; 
        }
        return $teste;
    }//Fim Módulo
/**
 * Função de acrescimo de data_registro, cpf_usuario(login), historico e oculto
 * @param $object objeto a ser trabalhado
 * @param $classe Classe que realizou a requisição __METHOD__
 * @param $registro_especial dados especiais devem ser lançados aqui
 * @return $object
 **/
    public static function objectRegistro ($object, $classe = null,$registro_especial = false)
    {
        if (empty($object->data_registro) )
        {
            $object->data_registro = date('Y-m-d');
            $object->cpf_usuario   = TSession::getValue('login');
        }
        if (empty($classe))
        {
            $classe = __CLASS__;
        }
        $object->oculto           = (empty($object->oculto) ) ? 'N' : $object->oculto;
		$hi                       = TFerramentas::MakeHistorico((array) $object->toArray(), $classe, $object->historico,$registro_especial);
		$object->historico        = $hi;
		return $object;
    }//Fim Modulo
/**
 * Coluna em DataGrid para oculto. Usado em tela de listagem
 * @param $grid  => datagrid ou list que será incluido a coluna
 * @param $nivel => nivel no Sistema
 * @param $class => classe que chamou
 * @param $quick => indicar que usa o protocolo de grid rápida
 * @return $grid
 **/
    public static function gridColunaOculto($grid,$nivel,$class, $quick = false)
    {
        if ($nivel >= 90)
        {
            if ($quick == false)
            {
                //Grid comum
                $column_oculto = new TDataGridColumn('oculto', 'Em Desuso/Apagado?', 'center');
                $grid->addColumn($column_oculto);
                $column_oculto->setAction(new TAction([$class, 'onReload']), ['order' => 'oculto']);
            }
            else
            {
                //Grid Rápida
                $grid->addQuickColumn('Em Desuso/Apagado', 'oculto', 'center', '10%');
            }
        }
        return $grid;
    }//Fim Módulo
/**
 * Coluna em DataGrid informar publicação in BGR
 * @param $grid  => datagrid ou list que será incluido a coluna
 * @param $nivel => nivel no Sistema
 * @param $class => classe que chamou
 * @param $quick => indicar que usa o protocolo de grid rápida
 * @return $grid
 **/
    public static function gridColunaInBGR ($grid,$nivel,$class, $quick = false)
    {
        if ($quick == false)
        {
            //Grid comum
            $column_in_bgr = new TDataGridColumn('in_bgr', 'BGR', 'center');
            $grid->addColumn($column_in_bgr);
        }
        else
        {
            //Grid Rápida
            $grid->addQuickColumn('BGR', 'in_bgr', 'center', '10%');
        }
        return $grid;
    }//Fim Módulo
/**
 * Coluna em oculto. Para formulários em geral
 * @param  $form        => formulário em que está sendo inserido
 * @param  $nivel       => nivel no Sistema
 * @param  $class       => classe que chamou
 * @param  $componentes => array ('label'=>$componente). O label pode vir com marcação
 *                                   indicando cor diferente para o mesmo.
 * @param  $nome        => nome do componente, normalmente 'oculto' ou 'detail_oculto'
 * @return $form
 **/
    public static function colunaOculto($form,$nivel,$class,$componentes = array(), $nome = 'oculto' )
    {
        $label  = '';
        $oculto = new THidden($nome);
        //Se o nível é de Adm starta oculto como TCombo
        if ($nivel >= 90)
        {
            $oculto          = new TCombo($nome);
            $oculto->style   = self::getLista('style','combo');
            $label           = 'Em Desuso/Apagado?';
            $oculto->setSize('10%');
            $oculto->addItems(TListas::sim_nao());
            $oculto->setValue('N');
        }
        $oculto->setName(TFerramentas::str_case($nome,'LOWER') );
        //Insere os componente oculto em uma array;
        $array_o     = array($label=>$oculto);
        //Agrupa as duas arrays
        $componentes = array_merge($componentes,$array_o);
        $first       = false;
        $result      = array();
        //Roda para capturar os labels e os componentes
        foreach ($componentes as $key=>$componente)
        {
            $label  = self::achaCor($key);
            $texto  = new TLabel($label['label']);
            //Define destaque de cor na label
            if ($label['cor'] != false)
            {
                $texto->setFontColor($label['cor']);
            }
            //Separa o label inicial
            if ($first == false)
            {
                $first    = true;
                $inicio   = array($texto);
                $result[] = $componente;
            }
            else
            {
                $result[] = new TLabel($texto);
                $result[] = $componente;
            }
        }
        //Adiciona no Form
        $form->addFields( $inicio, $result );

        return $form;
    }//Fim Módulo
/**
 *    Verifica se há um tag de cor na label e separa
 **/
    public static function achaCor($param)
    {
        $retorno = array();
        if (strpos($param,'//') > 0)
        {
            $part             = explode('//',$param);
            $retorno['label'] = $part[0];
            $retorno['cor']   = $part[1];
        }
        else
        {
            $retorno['label'] = $param;
            $retorno['cor']   = false;
        }
        return $retorno;
    }//Fim Módulo
/**
 * Botão Busca
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @param $quick  => se o Form é do tipo TQuickForm
 * @return $form + botão
 **/
    public static function botaoBusca($form,$classe,$metodo,$cabeca = false, $style = null,$quick = false)
    {
        if ($quick == false)
        {
            if ($cabeca == true)
            {
                $btn = $form->addHeaderAction(_t('Find'),  new TAction(array($classe, $metodo) ), 'fa:search');
                $btn->setName('top_busca');
            }
            else
            {
                $btn = $form->addAction(_t('Find'),  new TAction(array($classe, $metodo)), 'fa:search');
                $btn->setName('busca');
            }
        }
        else
        {
            if ($cabeca == false)
            {
                $btn = $form->addQuickAction(_t('Find'),  new TAction(array($classe, $metodo)), 'fa:search');
                $btn->setName('busca');
            }
        }
        $btn->title = 'Realiza a busca com base nos filtros acima.';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botão retorna
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @param $quick  => se o Form é do tipo TQuickForm
 * @return $form + botão
 **/
    public static function botaoRetorna($form,$classe,$metodo,$cabeca = false, $style = null,$quick = false)
    {
        if ($quick == false)
        {
            if ($cabeca == true)
            {
                $btn = $form->addHeaderAction(_t('Back'),  new TAction(array($classe, $metodo) ), 'fa:backward');
                $btn->setName('top_retorna');
            }
            else
            {
                $btn = $form->addAction(_t('Back'),  new TAction(array($classe, $metodo)), 'fa:backward');
                $btn->setName('retorna');
            }
        }
        else
        {
            if ($cabeca == false)
            {
                $btn = $form->addQuickAction(_t('Back'),  new TAction(array($classe, $metodo)), 'fa:backward');
                $btn->setName('retorna');
            }
        }

        $btn->title = 'Retorna para Listagem';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botão Salva
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @param $quick  => se o Form é do tipo TQuickForm
 * @return $form + botão
 **/
    public static function botaoSalva($form,$classe,$metodo,$cabeca = false, $style = null,$quick = false)
    {
        if ($quick == false)
        {
            if ($cabeca == true)
            {
                $btn = $form->addHeaderAction(_t('Save'),  new TAction(array($classe, $metodo) ), 'fa:save');
                $btn->setName('top_salva');
            }
            else
            {
                $btn = $form->addAction(_t('Save'),  new TAction(array($classe, $metodo)), 'fa:save');
                $btn->setName('salva');
            }
        }
        else
        {
            if ($cabeca == false)
            {
                $btn = $form->addQuickAction(_t('Save'),  new TAction(array($classe, $metodo)), 'fa:save');
                $btn->setName('salva');
            }
        }
        $btn->title = 'Salva esta edição.';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botão Novo
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @param $quick  => se o Form é do tipo TQuickForm
 * @return $form + botão
 **/
    public static function botaoNovo($form,$classe,$metodo,$cabeca = false, $style = null,$quick = false)
    {
        if ($quick == false)
        {
            if ($cabeca == true)
            {
                $btn = $form->addHeaderAction(_t('New'),  new TAction(array($classe, $metodo) ), 'fa:plus-circle');
                $btn->setName('top_novo');
            }
            else
            {
                $btn = $form->addAction(_t('New'),  new TAction(array($classe, $metodo)), 'fa:plus-circle');
                $btn->setName('novo');
            }
        }
        else
        {
            if ($cabeca == false)
            {
                $btn = $form->addQuickAction(_t('New'),  new TAction(array($classe, $metodo)), 'fa:plus-circle');
                $btn->setName('novo');
            }
        }
        $btn->title = 'Cria um novo';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botão Assinar
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @return $form + botão
 **/
    public static function botaoAssina($form,$classe,$metodo,$cabeca = false, $style = null)
    {
        if ($cabeca == true)
        {
            $btn = $form->addHeaderAction('Assinar',  new TAction(array($classe, $metodo) ), 'fa:pencil #000000');
            $btn->setName('top_valida');
        }
        else
        {
            $btn = $form->addAction('Assinar',  new TAction(array($classe, $metodo)), 'fa:pencil #000000');
            $btn->setName('valida');
        }
        $btn->title = 'Define quem são os assinates e muda o Status.';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botão Cancela
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @return $form + botão
 **/
    public static function botaoCancela($form,$classe,$metodo,$cabeca = false, $style = null)
    {
        if ($cabeca == true)
        {
            $btn = $form->addHeaderAction('Cancela Assinar',  new TAction(array($classe, $metodo) ), 'fa:ban red');
            $btn->setName('top_cancela_valida');
        }
        else
        {
            $btn = $form->addAction('Cancela Assinar',  new TAction(array($classe, $metodo)), 'fa:ban red');
            $btn->setName('cancela_valida');
        }
        $btn->title = 'Cancela o processo de colher assinaturas.';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botão Imprime
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @return $form + botão
 **/
    public static function botaoImprime($form,$classe,$metodo,$cabeca = false, $style = null)
    {
        if ($cabeca == true)
        {
            $btn = $form->addHeaderAction('Imprime',  new TAction(array($classe, $metodo) ), 'fas:print #000000');
            $btn->setName('top_imprime');
        }
        else
        {
            $btn = $form->addAction('Imprime',  new TAction(array($classe, $metodo)), 'fas:print #000000');
            $btn->setName('imprime');
        }
        $btn->title = 'Visualiza/Imprime o presente Termo';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botão retorna para Estoque
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @return $form + botão
 **/
    public static function botaoRetornaEstoque($form,$cabeca = false, $style = null,$classe = 'almox_estoqueList' ,$metodo = 'onReload',$quick = false)
    {
        if ($cabeca == true)
        {
            $btn = $form->addHeaderAction('Estoque',  new TAction(array($classe, $metodo) ), 'fas:long-arrow-alt-up #000000');
            $btn->setName('top_home');
        }
        else
        {
            $btn = $form->addAction('Estoque',  new TAction(array($classe, $metodo)), 'fas:long-arrow-alt-up #000000');
            $btn->setName('home');
        }
        $btn->title = 'Retorna para Listagem de Estoque';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 * Botoneiras para Almoxarifado
 * @param $form    => formulário
 * @param $classe  => classe atual
 * @param $retorno => classe list que irá retornar
 * @return $form + botão
 **/
    public static function botoneirasAlmox($form,$class,$retorno)
    {
        //Actions Rodapé
        $form = self::botaoSalva($form,$class,'onSave',false,'btn btn-sm btn-primary');
        $form = self::botaoNovo($form,$class,'onClear');
        $form = self::botaoAssina($form,$class,'onValida');
        $form = self::botaoCancela($form,$class,'onCancelaValida');
        $form = self::botaoImprime($form,$class,'onImprime');
        $form = self::botaoRetorna($form,$retorno,'onReload');
        $form = self::botaoRetornaEstoque($form,false);
        //Actions Cabeçalho
        $form = self::botaoSalva($form,$class,'onSave',true);
        $form = self::botaoNovo($form,$class,'onClear',true);
        $form = self::botaoRetorna($form,$retorno,'onReload',true);
        $form = self::botaoRetornaEstoque($form,true);
        
        return $form;
    }//Fim Módulo
/**
 * Botão Ementa
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @param $quick  => se o Form é do tipo TQuickForm
 * @return $form + botão
 **/
    public static function botaoEmenta($form,$classe,$metodo,$cabeca = false, $style = null,$quick = false)
    {
        if ($quick == false)
        {
            if ($cabeca == true)
            {
                $btn = $form->addHeaderAction('Ementa',  new TAction(array($classe, $metodo) ), 'fa:book gray');
                $btn->setName('top_disciplina');
            }
            else
            {
                $btn = $form->addAction('Ementa',  new TAction(array($classe, $metodo)), 'fa:book gray');
                $btn->setName('disciplina');
            }
        }
        else
        {
            if ($cabeca == false)
            {
                $btn = $form->addQuickAction('Ementa',  new TAction(array($classe, $metodo)), 'fa:book gray');
                $btn->setName('disciplina');
            }
        }
        $btn->title = 'Abre as disciplinas da Ementa do Curso';
        if (!empty($style))
        {
            $btn->class = $style;
        }
        return $form;
    }//Fim Módulo
/**
 *    Centraliza texto
 * @param $texto   => texto para centralizar
 * @param $colunas => quantidade de colunas
 **/
    public static function centralizaTexto ($texto = null, $colunas = 90)
    {
        $colunas = (strlen($texto) > 90) ? strlen($texto) : $colunas;
        $colunas = floor( $colunas * 1.25);
        $espacos = floor( ($colunas - strlen($texto) ) / 2 );
        $ret = str_pad($texto,($espacos + strlen($texto) )," ",STR_PAD_LEFT);
        return $ret;
    }//Fim Módulo
/**
 *    Apaga os itens associados
 * @param $items     => lista de objetos associados atualmente
 * @param $old_items => lista de objetos associados antigos 
 **/
    public static function delete_item_associado ($items,$old_items)
    {
        $keep_items = array();
        //Carrega os itens atuais
        if( $items )
        {
            foreach( $items as $item )
            {
                //Salva o item para gerar o id
                $item->store();
                $keep_items[] = $item->id;
            }
        }
        //Varre os itens antigos e elimina os que não estão listados
        if ($old_items)
        {
            foreach ($old_items as $old_item)
            {
                if (!in_array( $old_item->id, $keep_items))
                {
                    $old_item->delete();
                }
            }
        }
        return;
    }//Fim Módulo
/**
 *    Acrescenta criteria para oculto
 **/
    public static function addCriteriaOculto($criteria, $param)
    {
        if ($param == 'S')
        {
            $criteria->add(new TFilter('oculto','!=','S'));
        }
        return $criteria;
    }//Fim Método
/**
 * Função que retorna as iniciais de um nome desconsiderando se a particula do nome tiver menos de duas letras
 * @param $string => texto a ser tirado as iniciais
 * @return string com as iniciais.
 **/
    public static function getIniciaisString($string)
    {
        $pos   = 0;
        $saida = '';
        
        $tags  = explode(' ',$string);
        if(is_array($tags) )
        {
            foreach($tags as $tag)
            {
                //Se o trecho tem mais de dois caracteres, adiciona a letra inicial.
                if (strlen($tag) > 2)
                {
                    $saida .= substr($tag,0,1);
                }
            }
        }
        return $saida;
    }//Fim Método
/**
 * Junta duas array e retorna os valores duplicados se houver
 * @param $array1 => valores em array simples
 * @param $array2 => valores em array simples
 * @return array com os valores achado duplicado
 **/
    public static function getArrayValorDuplicado($array1,$array2 = array() )
    {
        $combined = array_merge($array1,$array2);

        $counted  = array_count_values($combined);

        $dupes = [];
        $keys = array_keys($counted);

        foreach ($keys as $key)
        {   
            if ($counted[$key] > 1)
            {$dupes[] = $key;}
        }
        sort($dupes);

        return $dupes;
    }//Fim Método
/**
 * Grava uma string em um arquivo
 * @param $file   => nome do arquivo
 * @param $string => o que será gravado. A mesma pode ser um array
 **/
    public static function setLogText($file,$string)
    {
        $arquivo = fopen($file,"a+");
        
        $lncr    = "\r\n";
        
        fwrite($arquivo, "-------------------------- Gravado em " . date('Y-m-d') . 
                         " --------------------------{$lncr}");
        
        /*if (is_array($string))
        {
            foreach($string as $key => $txt)
            {
                fwrite($arquivo, json_encode($txt) . $lncr);
            }
        }
        else
        {
            fwrite($arquivo, json_encode($string));
        }*/
        
        fwrite($arquivo, json_encode($string ) . $lncr);
        
        fwrite($arquivo, "-------------------------- Final --------------------------{$lncr}");
        fclose($arquivo);
    }//fim método

/**
 * Adiciona uma celula com colspan
 * @param $table => tabela
 * @param $linha => array de celulas
 * @param $colunas => quantidade de celulas de uma linha 
 **/
    public static function addCellColSpan($table, $linha, $colunas = 20)
    {
        $qnt_cell = count($linha);
        $por_cell = round($colunas / $qnt_cell);
        $dif_cell = $colunas - ($por_cell * $qnt_cell);
        
        $row      = $table->addRow();
        $count    = 0;
        foreach($linha as $cell)
        {
            $cell_add          = $row->addCell( $cell );
            $cell_add->colspan = $por_cell + ( ($count + 1 >= $qnt_cell) ? $dif_cell : 0 );
            $cell_add->style   = "width:" . round( (100 / $qnt_cell),2 ) . '%';
            $count ++;
        }
        return $table;
    }//Fim Método
/**
 * Monta um capitula para linha em tabela
 * @param $capitular => string com o nome do campo
 * @param $info      => dados do campo
 **/
    public static function addCapitular($capitular,$info)
    {
        return "<b>{$capitular}</b>: " . ( ($info) ? $info : '--');
    }//Fim Método

}//Fim da classe

