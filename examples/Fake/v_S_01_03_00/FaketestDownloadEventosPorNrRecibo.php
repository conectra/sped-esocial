<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../../../bootstrap.php';

use NFePHP\Common\Certificate;
use NFePHP\eSocial\Common\Soap\SoapFake;
use NFePHP\eSocial\Common\FakePretty;
use NFePHP\eSocial\Tools;

$config = [
    'tpAmb' => 2,
    //tipo de ambiente 1 - Produção; 2 - Produção restrita - dados reais;3 - Produção restrita - dados fictícios.
    'verProc' => 'S_1.3.0',
    //Versão do processo de emissão do evento. Informar a versão do aplicativo emissor do evento.
    'eventoVersion' => 'S.1.3.0',
    //versão do layout do evento
    'serviceVersion' => '1.5.0',
    //versão do webservice
    'empregador' => [
        'tpInsc' => 1, //1-CNPJ, 2-CPF
        'nrInsc' => '99999999', //numero do documento
        'nmRazao' => 'Razao Social',
    ],
    'transmissor' => [
        'tpInsc' => 1, //1-CNPJ, 2-CPF
        'nrInsc' => '99999999999999' //numero do documento
    ],
];
$configJson = json_encode($config, JSON_PRETTY_PRINT);

try {
    //carrega a classe responsavel por lidar com os certificados
    $content = file_get_contents('expired_certificate.pfx');
    $password = 'associacao';
    $certificate = Certificate::readPfx($content, $password);

    //usar a classe Fake para não tentar enviar apenas ver o resultado da chamada
    $soap = new SoapFake();
    //desativa a validação da validade do certificado
    //estamos usando um certificado vencido nesse teste
    $soap->disableCertValidation(true);

    //instancia a classe responsável pela comunicação
    $tools = new Tools($configJson, $certificate);
    //carrega a classe responsável pelo envio SOAP
    //nesse caso um envio falso
    $tools->loadSoapClass($soap);

    //executa a consulta
    //A = Agente de processamento: Serpro=1
    //B = Ambiente de recepção:
    //     1=Produção;
    //     2=Pré-produção - dados reais;
    //     3=Pré-produção - dados fictícios;
    //     6=Homologação;
    //     7=Validação;
    //     8=Testes;
    //     9=Desenvolvimento;
    //N = Número sequencial (19 posições)
    //nrRecibo = A.B.N
    $nrRec = [
        '1.1.1234567890123456789',
        '1.1.1234567890123456788',
        '1.1.1234567890123456787'
    ];

    $response = $tools->downloadEventosPorNrRecibo($nrRec);

    //header('Content-Type: application/xml; charset=utf-8');
    //echo $response;
    //retorna os dados que serão usados na conexão para conferência
    echo FakePretty::prettyPrint($response, '');
} catch (\Exception $e) {
    echo $e->getMessage();
}
