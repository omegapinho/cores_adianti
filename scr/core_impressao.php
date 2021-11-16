<?php
namespace Omegapinho\CoresAdianti;
/**
 * core_impressao - Gerencia a impressão de termos
 * @author  Fernando de Pinho Araújo
 * @package Core
 * @version 1.0 2021-10-19
 */

class core_impressao extends TFerramentas
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
 * Acha Posto/Graduação e Quadro
 * @param $param => string de identificação do componente de assinatura
 * @return array('posto'=>'','quadro'=>'')
 **/
    public static function getPostoQuadro($param)
    {
        $retorno         = ['nome'=>'','posto'=>'','quadro'=>''];
        if (strlen($param) < 15 || strpos($param, ' - ') == false)
        {
            return ['nome'=>$param,'posto'=>'NC','quadro'=>'NC'];
        }
        $dados           = explode(' - ',$param);
        $retorno['nome'] = $dados[0];
        $dados           = explode(' ',$dados[1]);
        
        $qod             = end($dados);
        $sicad           = new TSicadDados;
        $qod_comp = $sicad->caracteristicas_SICAD('quadro');
        if (in_array($qod,$qod_comp) == true)
        {
            $retorno['quadro'] = $qod;
            $posto             = implode(' ',$dados);
            $posto             = str_replace(' ' . $qod,'',$posto);
            $retorno['posto']  = $posto;
        }
        else
        {
            $retorno['posto']  =  implode(' ',$dados);
        }
        return $retorno;
    }//Fim Módulo
/**
 * Formata o número do item
 * @param $param => numero
 * @return texto AGUARDANDO OU NÚMERO FORMATADO
 **/
    private static function setNumeroFormata($param)
    {
        $retorno = 'AGUARDANDO';
        if (!empty($param))
        {
            $retorno = str_pad($param, 5, '0', STR_PAD_LEFT);
        }
        return $retorno;
    }//Fim Módulo
/**
 * Formata o Ano do item
 * @param $param => Ano
 * @return texto AGUARDANDO OU NÚMERO FORMATADO
 **/
    private static function setAnoFormata($param)
    {
        $retorno = 'XXXX';
        if (!empty($param))
        {
            $retorno = str_pad($param, 4, '0', STR_PAD_LEFT);
        }
        return $retorno;
    }//Fim Módulo

/**
 * Cria a caixa de conferência flutuante que substituirá a tag {$selo}
 * @param $param => objeto que será incluido
 **/
    public static function setCaixaConferencia($param)
    {
        $div = new TElement('div');
        $div->style = 'position: absolute; top: 0px; right: 6px;float: right;width: 84px;height: 84px;
                       background: #ffff;border: 1px solid black;';
        $param = $termo = str_replace('POR SENHA','',$param);
        $div->add($param);
        return $div;
    }//Fim Módulo
/**
 *  Cria QRCode
 * @param $assinante => objeto assinatura
 * @param $termo     => Tipo de Documento
 * @param $master    => objeto primário
 **/
    private static function setQRCodeAssinante ($assinante = null, $termo = 'ACP',$master = null,$detalhe = null)
    {
        $retorno            = '';
        if ($assinante == null || !is_object($assinante))
        {
            return $retorno;
        }
        $qrcode             = new GenQRCode();
        $data               = date('Y_m_d_H_i_s');
        //$file               = "app/output/{$termo}_" . $data . "_{$assinante->id}.png";
        $file               = "tmp/{$termo}_" . $data . "_{$assinante->id}.png";
        $dados              = "SEM ASSINATURA";
        if (file_exists($file))
        {
            unlink($file);
        }
        if ($assinante->status == 'ASSINADO')
        {
            //var_dump($assinante->ordem_identificacao);
            if ( !is_null($assinante->ordem_identificacao) )
            {
                $militar = "O(A) {$assinante->ordem_identificacao}, assinou por Ordem em " ;
            }
            else
            {
                if ($assinante->tipo_assinatura == 'DISPENSADO ASSINAR')
                {
                    $militar = 'VALIDADO AUTOMÁTICAMENTE SEM ASSINATURA em ';
                }
                else
                {
                    $militar = "O(A) {$assinante->cargo} {$assinante->identificacao}, assinou em ";
                }
            }
            $dados              = $militar . TDate::date2br($assinante->data_validado);
            $dados             .= ". Para o(a) $termo Nº " . self::setNumeroFormata($master->numero) . '/' . self::setAnoFormata($master->ano);
        }
        else
        {
            if ($assinante->status == 'AGUARDANDO')
            {
                $dados = 'AGUARDANDO ASSINATURA DO(A) RESPONSÁVEL.';
            }
            else if ($assinante->status == 'CANCELADO')
            {
                $dados = 'ASSINATURA CANCELADA.';
            }            
        }
        //echo $dados;
        $params['data']     = $dados;
        $params['level']    = 'H';
        $params['size']     = 2;
        $params['savename'] = $file;
        $qrcode->generate($params); 
        if (file_exists($file))
        {
            $retorno        = new TImage($file);
            $retorno->setProperty('width','84px');
            $retorno->setProperty('height','84px');
            if ( !is_null($assinante->ordem_identificacao) )
            {
                $retorno->title = "{$detalhe} Por Delegação: {$assinante->ordem_identificacao}";
            }
            else
            {
                $retorno->title = "{$detalhe} Pelo(a) Assinante: {$assinante->identificacao}";
            }
        }
        
        return $retorno;
    }//Fim Módulo
/**
 * Formata o número do item incluindo o ID do termo se ainda não tiver sido validado
 * @param $param => numero
 * @param $termo => object do termo
 * @return texto AGUARDANDO OU NÚMERO FORMATADO
 **/
    public static function setNumeroIdTermo($param,$termo)
    {
        if ($param == 'AGUARDANDO')
        {
            $param .= "(Id do Termo = {$termo->id})";
        }
        return $param;
    }//Fim Módulo
/**
 * Formata o número do item
 * @param $param => numero
 * @return texto AGUARDANDO OU NÚMERO FORMATADO
 **/
    private static function formatNumero($param)
    {
        $retorno = 'AGUARDANDO';
        if (!empty($param))
        {
            $retorno = str_pad($param, 5, '0', STR_PAD_LEFT);
        }
        return $retorno;
    }//Fim Módulo
/**
 * Formata o Ano do item
 * @param $param => Ano
 * @return texto AGUARDANDO OU NÚMERO FORMATADO
 **/
    private static function formatAno($param)
    {
        $retorno = 'XXXX';
        if (!empty($param))
        {
            $retorno = str_pad($param, 4, '0', STR_PAD_LEFT);
        }
        return $retorno;
    }//Fim Módulo
/**
 * Formata o número do item incluindo o ID do termo se ainda não tiver sido validado
 * @param $termo  => object termo
 * @param $numero => numero do termo já formatado 
 * @return texto AGUARDANDO OU NÚMERO FORMATADO
 **/
    private static function formatNumeroComId($termo,$numero)
    {
        if (is_object($termo) && in_array($termo->status, ['AGUARDANDO','INICIADO','EDITANDO','CANCELADO']) )
        {
            $numero .= "(Id do Termo = {$termo->id})";
        }
        return $numero;
    }//Fim Módulo
/**
 *    Carrega Array com Numero Ano formatados
 * @param $termo  => objeto termo
 * @param $master => array de dados;
 * @return array ['numero'=>numero,'ano'=>ano,'data_impressao'=>date(),'usuario'=>logado()]
 **/
    public static function getNumeroAnoRodape($termo,$master)
    {
        if (is_object($termo))
        {
            $master['ano']            = self::formatAno($termo->ano);
            $master['numero']         = self::formatNumero($termo->numero);
            $master['data_impressao'] = TDate::date2br(date('d-m-Y hh:mm:ss') );
            $master['usuario']        = TSession::getValue('username') ;
            
            //Verifica se o termo já foi numerado, se não coloca o id do documento
            $master['numero']         = self::formatNumeroComId($termo,$master['numero']);
        }
        return $master;
    }//Fim Módulo
/**
 *    Coleta os assinates do termo
 * @param $termo      => objetc termo
 * @param $assinantes => array com objeto assinantes do termo
 * @param $documento  => string informando qual o Termo trabalhado (ACP, TREM etc)
 **/
    public static function setAssinantes($termo,$assinantes,$documento,$param = [])
    {
        $detail_assina = [];

        //Lista de Cargos Possíveis
        //Possibilidades de melhoria: criar uma relação cargos de assinantes e termos
        // cargo_assinatura -> sistema_assinatura
        
        
        $lista_cargo   = ['CAUTELANTE'       => 'emissor',
                          'RESPONSAVEL'      => 'recebedor',
                          'ENTREGADOR'       => 'emissor',
                          'RECEBEDOR'        => 'recebedor',
                          'COMANDANTE'       => 'comandante',
                          'PRESIDENTE'       => 'presidente',
                          '1º MEMBRO'        => 'pmembro',
                          '2º MEMBRO'        => 'smembro',
                          'CHEFE DE DIVISÃO' => 'dchefe',
                          'HOMOLOGADOR'      => 'homologador',
                          'EDITOR'           => 'editor'];

        //Cria defaults para o array de saída
        foreach ($lista_cargo as $key => $cargo)
        {
            $detail_assina[$cargo]               = 'Não informado ainda';
            $detail_assina["{$cargo}_ordem"]     = '';
            $detail_assina["tipo_{$cargo}"]      = '';
            $data_cargo                          = ($cargo == 'recebedor') ? 'recebido' : $cargo;
            $detail_assina["data_{$data_cargo}"] = 'DD/MM/YYYY';
            $detail_assina["{$cargo}_nome"]      = strtoupper($key);
        }

        //coleta as assinaturas
        foreach ($assinantes as $assinante)
        {
            if (in_array($assinante->status,['AGUARDANDO','ASSINADO'] ) == true)
            {
                if (array_key_exists($assinante->cargo, $lista_cargo) )
                {
                    $cargo  = $lista_cargo[$assinante->cargo];
                    if ( empty($detail_assina["tipo_{$cargo}"])  ||
                         
                         (!empty($detail_assina["tipo_{$cargo}"]) && $assinante->status == 'ASSINADO') )
                    {
                        $qrcode                            = self::setQRCodeAssinante($assinante,
                                                                                      $documento,
                                                                                      $termo,
                                                                                      self::setAssinaturaEspera($assinante) );
                        
                        $detail_assina[$cargo]             = $assinante->identificacao;
                        $detail_assina["{$cargo}_ordem"]   = self::setAssinaturaOrdem($assinante->ordem_identificacao);
                        $detail_assina["tipo_{$cargo}"]    = $qrcode;
                        $data_cargo                        = ($cargo == 'recebedor') ? 'recebido' : $cargo;
                        $detail_assina["data_{$data_cargo}"] = self::setDataEspera($assinante);
                        
                        //Monta o campo assinatura completo
                        $detail_assina["tag_{$cargo}"]     = "<center>" .
                                                             $detail_assina["data_{$data_cargo}"] . "<br>".
                                                             $detail_assina[$cargo]               . "<br>"  .
                                                             $detail_assina["{$cargo}_nome"]      . "<br>" .
                                                             $detail_assina["{$cargo}_ordem"]     . "<br>" .
                                                             $detail_assina["tipo_{$cargo}"]      . "<br></center>";
                                                             
                    }
                }
            }//Fim Testa status
        }//Fim foreach $assinante

        return $detail_assina;
    }//Fim Método
/**
 *    Monta texto para assinatura por Ordem
 * @param $param => Identificação do PM
 * @return texto formatado ou nulo
 **/
    private static function setAssinaturaOrdem($param)
    {
        if (strlen($param) > 0)
        {
            return "<br><br><strong>Por Delegação: $param</strong>";
        }
        return '';
    }//Fim Módulo
/**
 *    Monta texto para assinatura que ainda aguardam preenchimento
 * @param $assina => objeto assinatura
 * @param $master => objeto master
 * @return texto informando como foi assinado
 **/
    private static function setAssinaturaEspera($assina,$master = null)
    {
        $tipo      = 'DISPENSADO ASSINAR';
        
        $f_tipo_as = (isset($assina->tipo_assinatura) && !empty($assina->tipo_assinatura));
        if ($f_tipo_as == true )
        {
            $tipo = $assina->tipo_assinatura;
        }
        else if ($f_tipo_as == false && $assina->status == 'AGUARDANDO')
        {
            $tipo = 'AGUARDANDO';
        }
        else if ( $f_tipo_as == false  && 
                 ($assina->status == 'ASSINADO' || $assina->status == 'VALIDADO') )
        {
            $tipo = 'ASSINADO';
        }
        return $tipo;
    }//Fim Módulo
/**
 *    Monta texto para data da assinatura que ainda aguardam preenchimento
 * @param $assina => objeto assinatura
 * @param $master => objeto master
 * @return texto informando a data validada a assinatura ou mensagem AGUARDANDO
 **/
    private static function setDataEspera($assina,$master = null)
    {
        //var_dump($assina);
        $tipo = 'AGUARDANDO';
        if ($assina->status == 'ASSINADO' )
        {
            $tipo = 'ASSINADO EM ' . TDate::date2br($assina->data_validado);
        }
        return $tipo;
    }//Fim Módulo
/**
 *    Monta texto das Observações dos Itens
 * @param $id    => contador
 * @param $param => Texto a adicionar
 * @param $atual => Atual comentário
 * @return texto formatado
 **/
    public static function setObservacaoFormata($id, $param, $atual = null)
    {
        $texto = '';
        if (strlen($param) > 0)
        {
            $texto = (strlen($id) > 0) ? "<strong>- Id {$id}:</strong> {$param}" : "- {$param}";
        }
        $retorno = $atual . ( ( !empty($atual) && !empty($param)) ? '<br>' : '') . $texto; 
        return $retorno;
    }//Fim Módulo
}//Fim Classe
