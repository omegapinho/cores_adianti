<?php
/**
 * core_componente - Gerencia a impressão de termos
 * @author  Fernando de Pinho Araújo
 * @package Core
 * @version 1.0 2021-10-19
 */
namespace Omegapinho\CoresAdianti 

class core_componente extends TFerramentas
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
    }//Fim Módulo
/*******************************************************************************
 *  Primeira Parte: Componentes auxiliares
 *******************************************************************************/
/**
 * tag de inicio de componentes auxiliares
 **/
    private function a_tag_componentes_1auxiliares ()
    {
        return;
    }//Fim Método
/**
 * Desabilita um elemento em tempo de execução
 * @param $form_name => nome do formulário
 * @param $field     => nome do campo
 **/
    public static function disableComboSearch($form_name,$field)
    {
        TScript::create( " tcombo_disable_fields('{$form_name}', '{$field}'); " );
        /*TScript::create( " var campo = document.getElementById('{$form_name}').elements['{$field}']; 
                                   campo.disabled  = true; " );*/
    }//Fim Método
/**
 * Habilita um elemento em tempo de execução
 * @param $form_name => nome do formulário
 * @param $field     => nome do campo
 **/
    public static function enableComboSearch($form_name,$field)
    {
        TScript::create( " tcombo_enable_fields('{$form_name}', '{$field}'); " );
        /*TScript::create( " var campo = document.getElementById('{$form_name}').elements['{$field}']; 
                                   campo.disabled  = false; " );*/
    }//Fim Método

/**
 * Desabilita a tecla Enter para fins de entrada
 **/
    public static function disableEnterKey()
    {
        $script = TScript::create('$("input, select, text").keypress(function (e) {var code = null;
                    code = (e.keyCode ? e.keyCode : e.which);                
                    return (code == 13) ? false : true;
                   });');        
        return $script;
    }//Fim Método
/**
 * Habilita a tela após a carga do onReload
 * @param $loaded => atributo da página form que indica a carga
 * @return $loaded modificado ou não
 **/
    public static function enableScreen($loaded = false)
    {
        if ($loaded == false)
        {
            TScript::create('loading = false;__adianti_unblock_ui();');
            $loaded = true;
        }
        return $loaded;
    }//Fim Método
/**
 * Fecha a janela de Visualização
 * @param $param => dados do formulário. Deve constar no Array o vetor window_id
 **/
    public static function viewCloseWindow($param)
    {
        //Verifica se há uma janela aberta (onView) e a fecha
        if (isset($param['window_id']) )
        {
            TJQueryDialog::closeById($param['window_id']);
        }
    }//Fim Método
/** 
 * Cria um container tipo TWindow para apresentar um form de coleta
 * @param $form => elemento do tipo BootstrapFormBuilder
 * @param $param => array de parametros 'classe' e 'metodo' de chamada do form(obrigatório)
 *                  titulo, largura e altura da window.
 * @return elemento TWindow com Form embutido
 **/
    public static function createWindow($form, $param = [])
    {
        //Carrega variáveis
        $classe   = $param['classe']   ?? false;
        $metodo   = $param['metodo']   ?? false;
        $titulo   = $param['titulo']   ?? '';
        $largura  = $param['largura']  ?? 0.6;
        $altura   = $param['altura']   ?? 0.5;
        $confirma = $param['confirma'] ?? true;
        
        if (($classe == false || $metodo == false) && $confirma == true)
        {
            throw new Exception('Parâmetro classe ou método ausente');
        }
        //Cria o container
        $window        = TWindow::create('',$largura,$altura);
        $window->style = 'overflow: hidden;';
        $window->setStackOrder(2000);
        
        //Adiciona Campo no Form
        $window_id     = TComp::cHidden('window_id',false,$window->getId());  //Id da window
        $form->addFields([$window_id]);

        //Desabilita a tecla Enter
        $form->add(self::disableEnterKey());
        
        //Add botão de confirmação
        if ($confirma)
        {
            $btn = $form->addAction('Confirma', new TAction([$classe, $metodo ]), 'fas:check-circle green fa-2x');
            $btn->class = 'btn btn-sm btn-primary';
        }

        $panel = new TPanelGroup("<center>{$titulo}</center>",'blue');
        $panel->add($form);
        //Botão de Fechar a janela
        $panel->addHeaderWidget( self::btCloseWindow($window) );

        //Prepara window
        $window->removePadding();
        $window->removeTitleBar();
        $window->disableEscape();

        $window->add($panel);
        
        return $window;
    }//Fim Método
/**
 * Verifica o nome carregado pelo TFile
 * @param $nome => valor retornado pelo TFile
 * @return string com o nome do arquivo + extenção
 **/
    private static function corrigeNomeFile($nome)
    {
        //Verifica se o nome está codificado
        if (strpos("---{$nome}",'%') == true)
        {
            //Decodifica e transforma em objecto
            $data = json_decode(urldecode($nome));
            $nome = $data->fileName;//Carrega o valor de retorno
            //Verifica se veio a path junto
            if (strpos("---{$nome}",'/') == true)
            {
                $data = explode('/',$nome);
                $nome = $data[1];//Limpa a path
            }
        }
        return $nome;
    }//Fim Método
/**
 *  Salva no BD um arquivo. Evita apagar um arquivo.
 * @param $data      => object de dados do form
 * @param $file_id   => campo onde está o id de vinculo com o objeto documentos
 * @param $file_nome => campo onde consta o nome do arquivo
 * @param $object    => objeto onde será armazenado o documento
 * @param $param     => definições extras
 * @return $data com alterações
 **/
    public static function fileStore($data,$file_id,$file_nome,$object,$param = [])
    {
            //Definições padrão
            $file_type          = $param['file_type']          ?? 'application/pdf';
            $objeto_removido    = $param['objeto_removido']    ?? 'auditagem_doc';
            $objeto_removido_id = $param['objeto_removido_id'] ?? 'XX';
            
            $arquivo_nome       = self::corrigeNomeFile($data->$file_nome);
            
            $file               = false;
            
            //Cria o arquivo no BD se ele não existir
            if (empty($data->$file_id))
            {
                //busca o arquivo para a memória
                $file     = "tmp/" . $arquivo_nome;
                $filedata = file_get_contents($file);
                $escaped  = bin2hex($filedata);
                
                //Cria novo item
                $arquivo                = new $object;
                $arquivo->file_type     = $file_type;
                $arquivo->contend       = '';
                $arquivo->filename      = TFerramentas::trataNomeArquivo($arquivo_nome);
                $arquivo->cpf_usuario   = TSession::getValue('login');
                $arquivo->data_registro = date('Y-m-d');
                $arquivo->oculto        = 'N';
                $arquivo->store();
                
                $tabela = $arquivo->getEntity();//Carrega o nome do BD e tabela

                $sql    = "UPDATE {$tabela} SET contend = decode('{$escaped}' , 'hex')  WHERE id= {$arquivo->id}";
                $conn   = TTransaction::get();
                $res    = $conn->prepare($sql);
                $res->execute();

            }
            else//Ou troca o arquivo mantendo a referencia ao documento de turma antigo
            {
                //Carrega o objeto já armazenado anteriormente
                $arquivo = new $object($data->$file_id);
                //Verifica se os nomes são diferentes
                if ($arquivo->filename != TFerramentas::trataNomeArquivo($arquivo_nome))
                {
                    if (!empty($data->id))//Colocando a id do documento que foi mudado 
                    {
                        $arquivo->documento_antigo_id = (int) $objeto_removido_id;
                        $arquivo->historico           = "{$objeto_removido}";
                        $arquivo->cpf_usuario         = TSession::getValue('login');
                        $arquivo->data_registro       = date('Y-m-d');
                        $arquivo->oculto              = 'S';
                        $arquivo->store();
                    }
                    
                    //busca o arquivo para a memória
                    $file     = "tmp/" . $arquivo_nome;
                    $filedata = file_get_contents($file);
                    $escaped  = bin2hex($filedata);
                    
                    //Cria novo item
                    $arquivo                = new $object;
                    $arquivo->file_type     = $file_type;
                    $arquivo->contend       = '';
                    $arquivo->filename      = TFerramentas::trataNomeArquivo($arquivo_nome);
                    $arquivo->cpf_usuario   = TSession::getValue('login');
                    $arquivo->data_registro = date('Y-m-d');
                    $arquivo->oculto        = 'N';
                    $arquivo->store();
                    
                    $tabela = $arquivo->getEntity();//Carrega o nome do BD e tabela
    
                    $sql    = "UPDATE {$tabela} SET contend = decode('{$escaped}' , 'hex')  WHERE id= {$arquivo->id}";
                    $conn   = TTransaction::get();
                    $res    = $conn->prepare($sql);
                    $res->execute();
                }
            }
            if ($file != false && file_exists($file))
            {
                unlink($file);//Apagar arquivo carregado para memória
            }
            
            //Atualiza $data
            $data->$file_id   = $arquivo->id;
            $data->$file_nome = $arquivo->filename;
            
            return $data;
    }//Fim Método
/**
 * Carrega para memória um arquivo de Banco de Dados
 * @param $key         => id do termo de informação
 * @param $object      => termo de informação (ex: auditagem_doc)
 * @param $vinculo     => campo de ligação (ex: almox_documento)
 **/
    public static function getDocumentoFile($key,$object,$vinculo)
    {
        $termo = new $object($key);
        if (!empty($termo))
        {
            $file    = $termo->$vinculo;
            //Carrega o conteúdo
            $arquivo = $file->contend;
            $file    = 'tmp/'. $file->filename;
            file_put_contents($file,$arquivo);
            return $file;
        }
        else
        {
            throw new Exception('Documento Não Localizado.');
        }
    }//Fim Método
/**
 *    Define configurações dos componentes básico
 * @param $componente => objeto TElement
 * @param $tam        => tamanho
 * @param $default    => valor padrão
 * @return $componente
 **/
    public static function defineBasico($componente, $tam = '40%', $default = false, $style = 'combo')
    {
        $componente->setSize($tam);
        if ($default !== false)
        {
            $componente->setValue($default);
        }
        $componente->style = TListas::stylo($style);
        return $componente;
    }//Fim Método
/**
 * Função de mascara funcional. Deve ser associada a função onKeyUp para campos TEntry.
 * Ex: $object->onKeyUp = TComp::defineMascaraMonetaria();
 * @param $decimal => separador de numero decimal
 * @param $milhares => separador das milhares
 * @return string
 **/
    public static function defineMascaraMonetaria ($decimal = '.' ,$milhares = '')
    {
        return "maskIt(this,event,'###{$milhares}###${milhares}###{$milhares}####{$decimal}##',true);";
        
    }//Fim Módulo
/**
 * Carrega criteria para filtragem dentro da tabela Item
 * @param $tipo     => identificador do item
 * @param $nivel    => nivel do sistema
 * @param $criteria => objeto TCriteria
 **/
    public static function defineCriteriaItem($tipo = 'tipoconsumo', $nivel = 50, $criteria = false)
    {
        $criteria          = ($criteria) ? $criteria : new TCriteria;
        $criteria->add(new TFilter('dominio','=',$tipo));
		$criteria->add(new TFilter('oculto','!=','t'));

		return self::defineCriteriaCorporacao($criteria,$nivel);
    }//Fim Método
/**
 * Carrega criteria para filtragem de OPM
 * @param $param => Parametros ['nivel'=>nivel no sistema, ;
 *                              'opm_id'=>OPM do usuário (false para busca pelo componente);
 *                              'classe'=>__CLASS__;
 *                              'campo'=>campo de vinculo, normalmente é opm_id
 *                              'oculto'=> true/false para eliminar (true) os deletados
 * @return $criteria
 **/
    public static function defineCriteriaOpm($param = [])
    {
        $nivel             = $param['nivel']  ?? 50;
        $classe            = "/" . strtolower($param['classe']  ?? 'list');
        $criteria          = $param['criteria'] ?? false;
        //Se Administrador, o filtro é só pela classe
        if ($nivel >= 90)
        {
            if ($criteria instanceof TCriteria)
            {
                return $criteria;
            }
            return (strpos($classe,'list') > 0) ? 'LIST' : 'FORM';
        }
        //Define a unidade com base no nível
        $sicad             = new TSicadDados;
        $listas            = $sicad->get_OPMsUsuario();
        $opm_id            = $param['opm_id'] ?? false;
        if ($opm_id == false)
        {
            if ($nivel < 90 && $nivel >= 80)
            {
                $opm_id = $listas['lista'];
            }
            else
            {
                $opm_id = [$listas['opm'] ];
            }
        }
        $campo             = $param['campo']  ?? 'opm_id';
        $oculto            = $param['oculto'] ?? false;
        
        $criteria = ($criteria != false) ? $criteria : new TCriteria; 
        $criteria->add(new TFilter($campo,'IN',$opm_id));
        //Elimina os apagados
        if ($oculto == true)
        {
		    $criteria->add(new TFilter('oculto','!=','S'));
		}
        return $criteria;
    }//Fim Método
/**
 * Define Criterios para BD
 * @param $filtro => informações de filtragem
 * @return $criteria
 **/
    public static function defineCriteriaBD ($filtro = 'LIST',$param = [])
    {
        $campo      = $param['campo']      ?? 'oculto';
        $comparador = $param['comparador'] ?? '!=';
        
        if (isset($param['valor']) && is_bool($param['valor']) )
        {
            $valor = $param['valor'];
        }
        else
        {
            $valor = $param['valor'] ?? 'S';
        }
        
        $criteria = new TCriteria;
        if ($filtro == 'FORM')
        {
            
            if($valor === true || $valor === false )
            {
                if($valor == true)
                {
                    $criteria->add(new TFilter($campo,$comparador,'t'));
                }
                else
                {
                    $criteria->add(new TFilter($campo,$comparador,'f'));
                }
            }
            else
            {
                $criteria->add(new TFilter($campo,$comparador,$valor));
            }
        }
        else if ($filtro != 'LIST' && $filtro instanceof TCriteria)
        {
            $criteria = $filtro;
        }
        return $criteria;
    }//fim Método
/**
 *    Add Criterio corporação se o nível menor que Adm
 * @param $criteria => TCriteria
 * @param $nivel    => nivel no sistema
 * @return $criteria
 **/
    public static function defineCriteriaCorporacao($criteria,$nivel = 50)
    {
        if ($nivel <= 90)
        {
            //$criteria->add(new TFilter('corporacao','=',TSession::getValue('corporacao')));
            $criteria->add(new TFilter('corporacao','=','PM'));
        }
        return $criteria;
    }//Fim método
/**
 *    Define array de campos para elementos com base na tabela Item
 * @param $campo  => lista de campos definidos pelo usuário
 * @param $padrao => lista padrão de informações do componente
 * @return $c => array ()
 **/
    public static function defineCampo($campo,$padrao)
    {
        $c           = array();
        /*$c['indice'] = (is_array($campo) && isset($campo['indice']) ) ? $campo['indice'] : $padrao['indice'];
        $c['label']  = (is_array($campo) && isset($campo['label'])  ) ? $campo['label']  : $padrao['label'];
        $c['ordem']  = (is_array($campo) && isset($campo['ordem'])  ) ? $campo['ordem']  : $padrao['ordem'];*/
        if (!is_array($campo))
        {
            $campo = array();
        }
        $c['indice'] = $campo['indice'] ?? $padrao['indice'];
        $c['label']  = $campo['label']  ?? $padrao['label'];
        $c['ordem']  = $campo['ordem']  ?? $padrao['ordem'];        
        return $c;
    }//Fim Método
/**
 *    Define array de campos para elementos com base na tabela Item
 * @param $campo  => lista de campos definidos pelo usuário
 * @param $padrao => lista padrão de informações do componente
 * @return $c => array ()
 **/
    public static function defineCampoGroup($campo,$padrao)
    {
        $c                  = array();
        $c['setLayout']     = (is_array($campo) && isset($campo['setLayout']) )      ? $campo['setLayout']     : $padrao['setLayout'];
        $c['setUseButton']  = (is_array($campo) && isset($campo['setUseButton'])  )  ? $campo['setUseButton']  : $padrao['setUseButton'];
        $c['setBreakItems'] = (is_array($campo) && isset($campo['setBreakItems'])  ) ? $campo['setBreakItems'] : $padrao['setBreakItems'];
        $c['style']         = (is_array($campo) && isset($campo['style'])  )         ? $campo['style']         : $padrao['style'];
        $c['labelCase']     = (is_array($campo) && isset($campo['labelCase'])  )     ? $campo['labelCase']     : $padrao['labelCase'];
        return $c;
    }//Fim Método
/**
 * Carrega o tipo de componente de Combo
 * @param $nome      => nome do componete
 * @param $tabela    => tabela de origem
 * @param $c         => array com campos (indice(id), label(show), ordem(ordem) )
 * @param $tipo      => tipo de componente C=>TDBCombo ou U=>TDBUniqueSearch
 * @param $criteria  => TCriteria;
 * @param $conn      => coneção com o BD
 **/
    public static function defineTipoCombo($nome, $tabela, $c, $tipo = 'C' ,$criteria = null, $conn = 'sisopm')
    {
        if ($tipo == 'U')
        {
            if (strpos($c['label'],'}') > 0)
            {
                $componente = new TDBUniqueSearch($nome,$conn,$tabela,$c['indice'],$c['ordem'],$c['ordem'],$criteria);
                $componente->setMask($c['label']);
            }
            else
            {
                $componente = new TDBUniqueSearch($nome,$conn,$tabela,$c['indice'],$c['label'],$c['ordem'],$criteria);
            }
            $componente->setId($nome);
        }
        else if ($tipo == 'S')
        {
            $componente = new TDBSelect($nome,$conn,$tabela,$c['indice'],$c['label'],$c['ordem'],$criteria);
        }
        else if ($tipo == 'M')
        {
            $componente = new TDBMultiSearch($nome,$conn,$tabela,$c['indice'],$c['label'],$c['ordem'],$criteria);
        }
        else if ($tipo == 'E')
        {
            $componente = new TDBEntry($nome,$conn,$tabela,$c['label'],$c['ordem'],$criteria);
        }
        else
        {
            $componente = new TDBCombo($nome,$conn,$tabela,$c['indice'],$c['label'],$c['ordem'],$criteria);
        }
        return $componente;
    }//Fim método
/**
 * Cria um sistema de auto reload para formulários do tipo List(não usar no tipo form ou tipos mistos)
 * @param $class => Classe que será recarregada
 * @param $method => metodo que deve ser acionado
 * @param $time   => tempo de espera (60000 equivale a um minuto)
 * @return script para incluir na página no fim do método construtor parent:add(TComp::autoReload(__CLASS__,'onReload'));
 **/
    public static function autoReload($class,$method = 'onReload',$time = 300000)
    {
        $script = new TElement('script'); 
        $script->type = 'text/javascript'; 
        $script->add("$(document).ready(function(){
              window.setTimeout(function(){ 
                var results = new RegExp('[\\?&]class=([^&#]*)').exec(window.location.href);
                if('$class' == results[1] )
                __adianti_load_page('engine.php?class={$class}&method={$method}');
              }, $time);
           });
           ");
        return $script;
    }//Fim Método
/**
 * Verifica se o Formulário pela variárvel $data trás o Id e retorna adequadamente
 * @param $classe => objeto a ser carregado ou criado um novo
 * @param $data   => dados do TForm
 * @param $indice => Normalmente o id do item
 * @return objeto do tipo $classe
 **/
    public static function cNewOrUpdate($classe,$data,$indice = 'id')
    {
        if ($data->$indice)
        {
            $retorno =  $classe::find($data->$indice);
            if (!empty($retorno))
            {
                return $retorno;
            }
        }
        return new $classe;
    }//Fim Método
/**
 * Marcação de softdelete
 * @param  $object => objeto a ser marcado
 * @param  $opcoes => array('store'=>true/false,'classe'=>__METHOD__,'especial'=>false, 'logica' => 'S/N' ou 'f/t')
 * @return $object com redefiniçoes
 **/
    public static function cMarcaDeletar($object, $opcoes = array('store'    => false,
                                                                  'classe'   => __METHOD__,
                                                                  'especial' => false,
                                                                  'logico'   => 'S') )
    {
        //Opções
        $store     = $opcoes['store']     ?? false;
        $classe    = $opcoes['classe']    ?? __METHOD__;
        $especial  = $opcoes['especial']  ?? false;
        $logica    = $opcoes['logico']    ?? $opcoes['logico'] ?? 'S'; 

        //Re-define dados do objeto
        $object->oculto        = $logica;//Virtualmente deleta
        $object->cpf_usuario   = TSession::getValue('login');//Quem apagou
        $object->data_registro = date('Y-m-d');//Quando apagou

        //Se é para salvar, grava o histórico antes
        if ($store == true)
        {
            $object = self::cHistorico($object,$classe,$especial,true);
        }
        return $object;
    }//fim Módulo
/**
 * Marcação de softdelete
 * @param  $details => objeto a ser marcado
 * @return void - salva os itens
 **/
    public static function cMarcaDeletarGrupo($details,$classe,$especial = false)
    {
        if ($details)
        {
            foreach ($details as $detail)
            {
                $detail = self::cMarcaDeletar($detail,['store'=>true,'classe'=>$classe,'especial'=>$especial] );
            }
        }
    }//Fim Módulo
/**
 * Função de acrescimo de data_registro, cpf_usuario(login), historico e oculto
 * @param $object            => objeto a ser trabalhado(deve possuir os campos de trabalho)
 * @param $classe            => Classe que realizou a requisição __METHOD__
 * @param $registro_especial => dados especiais devem ser lançados aqui
 * @param $store             => retorna o texto (false) ou salva (true) o processo e retorna
 * @return set $retorno true => $object, Se não salva
 **/
    public static function cHistorico ($object, 
                                       $classe,
                                       $registro_especial = false,
                                       $store = true,
                                       $param = [])
    {
        //Modifica o o padrão sim/não 
        $padrao_oculto = $param['padrao_oculto'] ?? false;
        if ($padrao_oculto == 'true/false')
        {
            $padrao_sim = 't';
            $padrao_nao = 'f';
            
        }
        else
        {
            $padrao_sim = 'S';
            $padrao_nao = 'N';
        }
        if (empty($object->id) || empty($object->data_registro) )
        {
            $object->data_registro = date('Y-m-d');
            $object->cpf_usuario   = TSession::getValue('login');
            $object->oculto        = $padrao_nao;
        } 
        $object->data_registro = $object->data_registro ?? date('Y-m-d');
        $object->cpf_usuario   = $object->cpf_usuario   ?? TSession::getValue('login');
        $object->oculto        = ($object->oculto == $padrao_sim || $object->oculto == $padrao_nao) ? $object->oculto : $padrao_nao;
		$object->historico     = TFerramentas::MakeHistorico((array) $object->toArray(), $classe, $object->historico,$registro_especial);
		if ($store)
		{
            $object->store();
        }
        return $object;
    }//Fim Modulo
/**
 * Gera um relatório de Log especial para monitoramento de atualizações e comunicações com o SICAD
 * @param $relato => informações detalhadas do problema
 * @param $indice => uma chave de indexação para acompanhamento
 * @param $metodo => __METHOD__ que fez a requisição
 * @return objecto system_error_log
 **/
    public static function geraRelatorioLog($relato,$indice,$metodo = __METHOD__)
    {
        $object                  = new system_error_log;
        $object->data            = date('Y-m-d H:i:s');
        $object->relato          = $relato;
        $object->indice          = $indice;
        $object->classe          = $metodo;
        $object->solucionado     = 'N';
        $object->ignorado        = 'N';
        $object->system_user_id  = TSession::getValue('userid');
        return self::cHistorico($object,$metodo);
    }//Fim do Método
/**
 * Define se carrega ou não elementos ocultos
 * @param $nivel => nível do sistema para a classe
 * @return >= 90 retorna N (livre), ou S (sem apagados)
 **/
    public static function getOculto($nivel)
    {
        $autoriza = TSession::getValue('visualiza_apagados') ?? 'N'; 
        $retorno  = ($nivel >= 90 && $autoriza == 'S') ? 'N' : 'S';
        return $retorno;
    }//Fim Método
/**
 * Define o filtro de itens apagados
 * @param $criteria => objeto TCriteria
 * @param $nivel    => nivel de acesso ao sistema
 * @param $coluna   => nome da coluna a ser avaliada
 * @return $criteria
 */
    public static function defineCriteriaOculto($criteria,$nivel,$coluna = 'oculto',$param = [])
    {
        $operador = $param['operador'] ?? '!=';
        $valor    = $param['valor']    ?? 'S';
        
        $autoriza = TSession::getValue('visualiza_apagados') ?? 'N';
        if ($nivel < 90 || ($nivel >= 90 && $autoriza == 'N' ) )
        {
            $criteria->add(new TFilter($coluna,$operador,$valor));
        }
        
        return $criteria;
    }//Fim Método
/**
 * Colhe a assinatura digitas em tablet ou dispositivos móveis
 * @param $nome => nome do campo onde está assinado.
 **/
    public static function cCampoAssina($nome = 'canvas_div')
    {
        $script = TScript::create("
                    function download(dataURL, filename) 
                    {
                        alert('teste');
                        if (navigator.userAgent.indexOf(\"Safari\") > -1 && navigator.userAgent.indexOf(\"Chrome\") === -1) 
                        {
                            window.open(dataURL);
                        } 
                        else 
                        {
                            var blob = dataURLToBlob(dataURL);
                            var url = window.URL.createObjectURL(blob);
                
                            var a = document.createElement(\"a\");
                            a.style = \"display: none\";
                            a.href = url;
                            a.download = filename;
                
                            document.body.appendChild(a);
                            a.click();
                
                            window.URL.revokeObjectURL(url);
                        }
                    }
                
                    function dataURLToBlob(dataURL) 
                    {
                        // Code taken from https://github.com/ebidel/filer.js
                        var parts = dataURL.split(';base64,');
                        var contentType = parts[0].split(\":\")[1];
                        var raw = window.atob(parts[1]);
                        var rawLength = raw.length;
                        var uInt8Array = new Uint8Array(rawLength);
                
                        for (var i = 0; i < rawLength; ++i) 
                        {
                            uInt8Array[i] = raw.charCodeAt(i);
                        }
                
                        return new Blob([uInt8Array], { type: contentType });
                    }
                
                
                    var signaturePad = new SignaturePad(document.getElementById('signature-pad'), {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: 'rgb(0, 0, 0)'
                    });
                
                
                    var saveButton = document.getElementById('save');
                    var cancelButton = document.getElementById('clear');
                    alert('teste');
                
                
                
                    saveButton.addEventListener(\"click\", function(event) {
                        if (signaturePad.isEmpty()) {
                            alert(\"Faça sua assinatura.\");
                        } else {
                            var dataURL = signaturePad.toDataURL();
                            download(dataURL, \"signature.png\");
                            alert(dataURL);
                            $(\"#imageCheck\").val(dataURL);
                        }
                    });
                
                    cancelButton.addEventListener('click', function(event) {
                        signaturePad.clear();
                    });" );
        return $script;
    }//Fim Método
/**
 * Testa se dado programa (classe) está no rol de programas do usuário
 * @param $programa => string com o nome da classe
 * @return true/false 
 **/
    public static function ifExistProgram($programa)
    {
        $programs    = TSession::getValue('programs');
        return (array_key_exists($programa,$programs) && $programs[$programa] == true);
    }//Fim Método
}//Fim Classe
