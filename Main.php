<?php

class Main {

	private $info;
	private $link;
	private $tokens;

	public function __construct() {
		$this->info = json_decode(file_get_contents('./data/info.json'));
		$this->link = 'https://' . $this->info->subdomain . '.amocrm.ru';
		$this->tokens = json_decode(file_get_contents('./data/tokens.json'));
	}

	public function makeRequest($link, $headers, $data) {
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
		curl_setopt($curl,CURLOPT_URL, $link);
		curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl,CURLOPT_HEADER, false);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
		$out = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($code >= 400) {
			header('Content-Type: application/json');
			die($out);
		}

		return $out;
	}

	public function checkToken() {
		if (!isset($this->tokens->access_token)) {
			$link = $this->link . '/oauth2/access_token';
			$headers = ['Content-Type:application/json'];
			$data = [
				'client_id' => $this->info->client_id,
				'client_secret' => $this->info->client_secret,
				'grant_type' => 'authorization_code',
				'code' => $this->info->authorization_code,
				'redirect_uri' => $this->info->redirect_uri,
			];
			file_put_contents('./data/tokens.json', $this->makeRequest($link, $headers, $data));
			return $this;
		}
		return $this;
	}

	public function addLead($request) {
		$link = $this->link . '/api/v4/leads/complex';
		$headers = [
			'Authorization: Bearer ' . $this->tokens->access_token,
			'Content-Type: application/json'
		];
		$data = array([
			"name" => "Новая заявка",
			"_embedded" => 
			[
			   "contacts" => 
			   [ 
					[
						"first_name" => $request['name'],
						"custom_fields_values" => 
						[ 
							[
								"field_code" => "EMAIL",
								"values" => 
								[
									[
											"enum_code" => "WORK",
											"value" => $request['email']
									]
								]
							],
							[
								"field_code" => "PHONE",
								"values" => 
								[
									[
											"enum_code" => "WORK",
											"value" => $request['phone']
									]
								]
							]
						]
					]
			   ]
			]
		]);
		$this->makeRequest($link, $headers, $data);
		echo "Заявка отправлена";
	}

}