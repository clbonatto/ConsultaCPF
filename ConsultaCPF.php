<?php

class ConsultaCPF {

	/**
	 * Metodo para capturar o captcha e viewstate para enviar no metodo
	 * de consulta
	 *
	 * @param  string $cnpj CPF
	 * @throws Exception
	 * @return array Link para ver o Captcha, Viewstate e Cookie
	 */
	public static function getParams() {
		$ch = curl_init('https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/captcha/gerarCaptcha.asp');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_0);

		$response = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);

		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		$headers = array();

		$out = preg_split("|(?:\r?\n){1}|m", $header);

		foreach ($out as $line) {
			if ( $line != "" ){
				if ( stristr( $line, ": " ) ){
					@list($key, $val) = explode(": ", $line, 2);
					if ($val != null) {
						if (!array_key_exists($key, $headers)) {
							$headers[$key] = trim($val);
						}
					} else {
						$headers[] = $key;
					}
				}
			}
		}

		if (!method_exists('phpQuery', 'newDocumentHTML'))
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'phpQuery-onefile.php';
		
		\phpQuery::newDocumentHTML($body, $charset = 'utf-8');

		$captchaBase64 = 'data:image/png;base64,' . base64_encode($body);
		
		return array(
			'captchaBase64' => $captchaBase64,
			'cookie' => $headers['Set-Cookie']
		);
	}

	/**
	 * Metodo para realizar a consulta
	 *
	 * @param  string $cpf CPF
	 * @param  string $captcha CAPTCHA
	 * @param  string $viewstate VIEWSTATE
	 * @param  string $stringCookie COOKIE
	 * @throws Exception
	 * @return array  Dados da pessoa
	 */
	public static function consulta($cpf, $data_nascimento, $captcha, $stringCookie) {
		$arrayCookie = explode(';', $stringCookie);

		$ch = curl_init("https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaPublicaExibir.asp");

		$param = array(
			'tempTxtCPF' => $cpf,
			'temptxtToken_captcha_serpro_gov_br' => '',
			'txtTexto_captcha_serpro_gov_br' => $captcha,
			'temptxtTexto_captcha_serpro_gov_br' => $captcha,
			'tempTxtNascimento' => $data_nascimento,
			'Enviar' => 'Consultar'
		);

		$options = array(
			CURLOPT_COOKIEJAR => 'cookiejar',
			CURLOPT_HTTPHEADER => array(
				"Host: www.receita.fazenda.gov.br",
				"User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0",
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3",
				"Accept-Encoding: gzip, deflate",
				"Referer: http://www.receita.fazenda.gov.br/Aplicacoes/ATCTA/CPf/ConsultaPublicaExibir.asp",
				"Cookie: ' . $arrayCookie[0] . '",
				"Connection: keep-alive"
			),
			CURLOPT_POSTFIELDS => http_build_query($param),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => 1
		);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_0);
		curl_setopt_array($ch, $options);
		$html = curl_exec($ch);
		curl_close($ch);

		if (!method_exists('phpQuery', 'newDocumentHTML'))
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'phpQuery-onefile.php';

		\phpQuery::newDocumentHTML($html, $charset = 'utf-8');

		$class_dados = pq('#F_Consultar > div > div.caixaConteudo > div > div:nth-child(3) > p > span.clConteudoDados');

		$class_complemento = pq('#F_Consultar > div > div.caixaConteudo > div > div:nth-child(4) > p > span.clConteudoComp');

		$result = array();
		foreach ($class_dados as $clConteudoDados){
			$result[] = trim(pq($clConteudoDados)->html());
		}

		foreach ($class_complemento as $clConteudoComp){
			$result[] = trim(pq($clConteudoComp)->html());
		}

		if (isset($result[0])) {
			$result[0] = str_replace('N<sup>o</sup> do CPF: ', '', $result[0]);

			return( [	'cpf' => $result[0],
						'nome' => strip_tags( str_replace('Nome da Pessoa Física: ', '', $result[1]) ),
						'situacao_cadastral' => strip_tags( str_replace('Situação Cadastral: ', '', $result[3]) ),
						'digito_verificador' => strip_tags( str_replace('Digito Verificador: ', '', $result[5]) ),
						'hora_data' => strip_tags( str_replace('Comprovante emitido às: ', '', $result[6]) ),
						'controle' => strip_tags( str_replace('Código de controle do comprovante: ', '', $result[7]) )
					]);
		} else {
			return $result;
		}
	}

}
