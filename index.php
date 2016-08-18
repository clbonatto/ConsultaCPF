<html xmlns="http://www.w3.org/1999/xhtml">
    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="pt-br" />
        <meta name="robots" content="nofollow" />
        <title>Consulta CPF</title>
    </head>
    <body>
        <table border="0" id="titulo" cellspacing="0" cellpadding="0">
            <tr>
                <td valign="top" width="100%">
                    <table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
                        <tr>
                            <td valign="top" width="100%">
                                <table border="0" cellspacing="0" cellpadding="0" width="100%" height="20">
                                    <tr>
                                        <td valign="top" width="500">
                                            <?php 
                                            require( "ConsultaCPF.php" );

                                            if ( isset( $_REQUEST['cpf_consulta'] ) ){ $cpf = $_REQUEST['cpf_consulta']; } else { exit( "CPF não foi informado" ); }
                                            if ( isset( $_REQUEST['data_nascimento'] ) ){ $data_nascimento = $_REQUEST['data_nascimento']; } else { exit( "A data de nascimento não foi informada" ); }

                                            $reload = false;
                                            if ( isset( $_REQUEST['cpf'] ) ) {
                                                $retorno = ConsultaCPF::consulta(   $_REQUEST['cpf_consulta'], 
                                                                                    $_REQUEST['data_nascimento'], 
                                                                                    $_REQUEST['captcha'], 
                                                                                    $_REQUEST['cookie'] 
                                                                                );
                                                isset($_REQUEST['id']) ? $id = $_REQUEST['id'] : $id = "";
                                                
                                                if ( sizeof( $retorno ) > 0 ){ ?>
                                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                                        <tr>
                                                            <td align="center" colspan="8">
                                                                <b>Retorno da Receita Federal</b>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="right" colspan="1">
                                                                <b>Nome completo:</b>
                                                            </td>
                                                            <td align="left" colspan="7">
                                                                <?php echo $retorno['nome'];?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="right" colspan="1">
                                                                <b>Situação cadastral:</b>
                                                            </td>
                                                            <td align="left" colspan="7">
                                                                <?php echo $retorno['situacao_cadastral'];?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="right" colspan="1">
                                                                <b>Dígito verificador:</b> 
                                                            </td>
                                                            <td align="left" colspan="7">
                                                                <?php echo $retorno['digito_verificador'];?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="right" colspan="1">
                                                                <b>Hora/Data:</b> 
                                                            </td>
                                                            <td align="left" colspan="7">
                                                                <?php echo $retorno['hora_data'];?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="right" colspan="1">
                                                                <b>Código de controle:</b> 
                                                            </td>
                                                            <td align="left" colspan="7">
                                                                <?php echo $retorno['controle'];?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                <?php
                                                } else {
                                                    $reload = true;
                                                }
                                            } else {
                                                $reload = true;
                                            }
                                            if ( $reload ){
                                                $params = ConsultaCPF::getParams(); ?>

                                                <script language="JavaScript" type="text/JavaScript">
                                                    function valida(){
                                                        if ( document.formulario.captcha.value == "" ){
                                                            alert("Por favor, informe os caracteres da imagem");
                                                            document.formulario.captcha.focus();
                                                            return false;
                                                        } else {
                                                            return true;
                                                        }
                                                    }
                                                    
                                                    function reloadPage(){
                                                        document.reload.submit();
                                                    }
                                                </script>

                                                <form action="" name="reload" method="POST">
                                                    <input type="hidden" name="cpf_consulta" value="<?php echo $cpf; ?>" />
                                                    <input type="hidden" name="data_nascimento" value="<?php echo $data_nascimento; ?>" />
                                                </form>

                                                <form action="" name="formulario" method="POST">
                                                    <input type="hidden" name="cpf" value="<?php echo $cpf; ?>" />
                                                    <input type="hidden" name="cookie" value="<?php echo $params['cookie']; ?>" />
                                                    <input type="hidden" name="cpf_consulta" value="<?php echo $cpf; ?>" />
                                                    <input type="hidden" name="data_nascimento" value="<?php echo $data_nascimento; ?>" />

                                                    <tr>
                                                        <td align="center">
                                                            Por favor, informe os caracteres da imagem
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center">
                                                            <img src="<?php echo $params['captchaBase64']; ?>" />
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center">
                                                            <input type="button" onclick="reloadPage()" value="Gerar outra imagem"/>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center">
                                                            <input type="text" name="captcha" size="8" maxlength="6" autocomplete="off"/>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center">
                                                            <input type="submit" value="Consultar" onclick="return valida()"/>
                                                        </td>
                                                    </tr>
                                                    <script>document.formulario.captcha.focus();</script>
                                                </form>
                                            <?php
                                            }?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>