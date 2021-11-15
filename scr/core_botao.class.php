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
/**************************************************************************************************
 ** Componentes do Tipo Botão
 **************************************************************************************************/
/**
 * tag de inicio de componentes botoneiras
 **/
    private function a_tag_componentes_2botoes ()
    {
        return;
    }//Fim Método
/**
 * Botão retorna
 * @param $form   => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeca => local no formulario (false => no rodapé, true => no cabeçalho)
 * @param $validaCliente => se o formulário está validando do lado do cliente(usará ação do tipo link)
 * @param $style  => estilo do botão
 * @return $form + botão
 **/
    public static function btRetorna($form,$classe,$metodo,$cabeca = false,$style = '',$link = false)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd(_t('Back'),  new TAction([$classe, $metodo]), 'fas:backward #000000');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Retorna para Listagem/Mestre.';
        $bnt->name  = 'retorna';
        return $form;
    }//Fim Módulo
/**
 * Botão Salvar
 * @param $form   => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeca => local no formulario (false => no rodapé, true => no cabeçalho)
 * @param $style  => estilo do botão
 * @param $validaCliente => se o formulário está validando do lado do cliente(usará ação do tipo link)
 * @return $form + botão
 **/
    public static function btSalva($form,$classe,$metodo,$cabeca = false,$style = '',$link = false)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd(_t('Save'),  new TAction([$classe, $metodo]), 'fas:save #000000');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Salva a edição.';
        $bnt->name  = 'salva';
        return $form;
    }//Fim Módulo
/**
 * Botão Salvar Static
 * @param $form   => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeca => local no formulario (false => no rodapé, true => no cabeçalho)
 * @param $style  => estilo do botão
 * @param $validaCliente => se o formulário está validando do lado do cliente(usará ação do tipo link)
 * @return $form + botão
 **/
    public static function btSalvaStatic($form,$classe,$metodo,$cabeca = false,$style = '',$link = false)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd(_t('Save'),  new TAction([$classe, $metodo],['static'=>'1']), 'fas:save #000000');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Salva a edição.';
        $bnt->name  = 'salva';
        return $form;
    }//Fim Módulo
/**
 * Botão Novo
 * @param $form   => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeca => local no formulario (false => no rodapé, true => no cabeçalho)
 * @param $style  => estilo do botão
 * @param $link   => indica que a chamada é um link sem validação do form
 * @return $form + botão
 **/
    public static function btNovo($form,$classe,$metodo = 'onEdit',$cabeca = false,$style = '',$link = true)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd(_t('New'),  new TAction([$classe, $metodo]), 'fas:plus-circle green');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Cria um novo registro.';
        $bnt->name  = 'novo';
        return $form;
    }//Fim Módulo
/**
 * Botão Busca
 * @param $form   => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeca => local no formulario (false => no rodapé, true => no cabeçalho)
 * @param $style  => estilo do botão
 * @param $validaCliente => se o formulário está validando do lado do cliente(usará ação do tipo link)
 * @return $form + botão
 **/
    public static function btBusca($form,$classe,$metodo = 'onSearch',$cabeca = false,$style = 'btn btn-sm btn-primary',$link = false)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd(_t('Find'),  new TAction([$classe, $metodo]), 'fas:search #000000');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Filtra os registro com base no que foi selecionado.';
        return $form;
    }//Fim Módulo
/** 
 * Cria um botão para fechar janela modal via JS
 * @param $window => object TWindow
 * @return object TButton
 **/
    public static function btCloseWindow($window)
    {
        $btn = new TButton('close_window');
        $btn->setImage('fas:window-close fa-lg');
        $btn->setLabel('Fechar');
        $btn->class = 'btn btn-danger';
        $btn->addFunction('$( "#' . $window->getId() . '" ).remove();');
        
        return $btn;
    }//Fim Método
/** 
 * Cria um botão para fechar right panel via JS
 * @param $window => object TWindow
 * @return object TButton
 **/
    public static function btCloseRightPanel()
    {
        $btn = new TButton('close_panel');
        $btn->setImage('fas:window-close fa-lg');
        $btn->setLabel('Fechar');
        $btn->class = 'btn btn-danger';
        $btn->addFunction("Template.closeRightPanel()");
        return $btn;
    }//Fim Método
/**
 * Botão Assinar
 * @param $form => formulário
 * @param $classe => classe para ir
 * @param $metodo => metodo a executar
 * @param $cabeça => true/false (cabeçalho/rodapé)
 * @param $style  => stilo do botão
 * @return $form + botão
 **/
    public static function btAssina($form,$classe,$metodo,$cabeca = false, $style = null,$link = false)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd('Assinar',  new TAction([$classe, $metodo]), 'fas:pencil-alt #000000');
        $bnt->setName((($cabeca == true) ? 'top_' : '' ) . 'valida');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Define quem são os assinates e muda o Status para aguardando assinatura';
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
    public static function btCancela($form,$classe,$metodo,$cabeca = false, $style = null,$link = false)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd('Cancela Assinar',  new TAction([$classe, $metodo]), 'fas:ban red');
        $bnt->setName((($cabeca == true) ? 'top_' : '' ) . 'cancela_valida');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Cancela o Processo de Assinatura.';
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
    public static function btImprime($form,$classe,$metodo,$cabeca = false, $style = null,$link = false)
    {
        //$form = new BootstrapFormBuilder;
        //Formata o Comando conforme uso se é no cabeçalho, se é tipo link
        $cmd = 'add' . (($cabeca == true) ? 'Header' : '' ) . 'Action' . (($link == true && $cabeca != true) ? 'Link' : '');
        $bnt = $form->$cmd('Imprime',  new TAction([$classe, $metodo]), 'fas:print #000000');
        $bnt->setName((($cabeca == true) ? 'top_' : '' ) . 'imprime');
        //Style
        if (!empty($style))
        {
            $bnt->class = $style;
        }
        $bnt->title = 'Visualiza/Imprime o presente Termo.';
        return $form;
    }//Fim Módulo







	
}//Fim Classe
