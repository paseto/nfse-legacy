<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');
require_once '../../bootstrap.php';

use NFePHP\NFSe\NFSe;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
use NFePHP\NFSe\Models\Betha\Rps;

$arr = [
    "atualizacao" => "2016-08-03 18:01:21",
    "tpAmb" => 2,
    "versao" => 1,
    "razaosocial" => "SUA RAZAO SOCIAL LTDA",
    "cnpj" => "99999999999999",
    "cpf" => "",
    "im" => "99999999",
    "cmun" => "4118402", //PARANAVAI
    "siglaUF" => "PR",
    "pathNFSeFiles" => "/dados/nfse",
    "proxyConf" => [
        "proxyIp" => "",
        "proxyPort" => "",
        "proxyUser" => "",
        "proxyPass" => ""
    ]
];

$configJson = json_encode($arr);
$contentpfx = file_get_contents('/var/www/sped/sped-nfse/certs/certificado.pfx');

try {

    $nfse = new NFSe($configJson, Certificate::readPfx($contentpfx, 'senha'));
    //Por ora apenas o SoapCurl funciona com IssNet
    $nfse->tools->loadSoapClass(new SoapCurl());
    //caso o mode debug seja ativado serão salvos em arquivos
    //a requisicção SOAP e a resposta do webservice na pasta de
    //arquivos temporarios do SO em sub pasta denominada "soap"
    $nfse->tools->setDebugSoapMode(false);

    //Construção do RPS
    $rps = new Rps();
    $rps->prestador($rps::CNPJ, '30002237000172', '51490');
    $rps->tomador($rps::CPF, '03900722900', '', 'JONAS AVENTURA', '5530335740','jonas@email.com');
    $rps->tomadorEndereco(
        'Rua 12',
        '1234',
        'casa 2',
        'Centro',
        '5103403',
        'MT',
        '78088408'
    );
    //$rps->intermediario($rsp::CNPJ, '99999999999999', '222222', 'Teste');
    $rps->numero(1);
    $rps->serie('1');
    $rps->status($rps::STATUS_NORMAL);
    $rps->tipo($rps::TIPO_RPS);

    $timezone = new \DateTimeZone('America/Sao_Paulo');
    $rps->dataEmissao(new \DateTime("now", $timezone));
    $rps->municipioPrestacaoServico('4204608'); //999 em ambiente de produção
    $rps->naturezaOperacao($rps::NATUREZA_INTERNA);
    $rps->itemListaServico('0103');
//    $rps->codigoTributacaoMunicipio('631940001');
    $rps->discriminacao('TESTE ### Valor Aproximado dos Tributos: R$ 0,17');
//    $rps->rpsSubstituido('5555', 'A1', 1);
    $rps->regimeEspecialTributacao($rps::REGIME_MICROEMPRESA);
    $rps->optanteSimplesNacional($rps::SIM);
    $rps->incentivadorCultural($rps::NAO);
    $rps->issRetido($rps::NAO);
    $rps->aliquota(5.0000);
    $rps->valorServicos(1321.50);
    $rps->valorDeducoes(0.00);
    $rps->outrasRetencoes(0.00);
    $rps->descontoCondicionado(0.00);
    $rps->descontoIncondicionado(0.00);

    //(Valor dos serviços - Valor das deduções - descontos incondicionados)
    $rps->baseCalculo(1321.50);

    $rps->valorIss(66.075);
    $rps->valorPis(0.00);
    $rps->valorCofins(0.00);
    $rps->valorCsll(0.00);
    $rps->valorInss(0.00);
    $rps->valorIr(0.00);

    //(ValorServicos - ValorPIS - ValorCOFINS - ValorINSS - ValorIR - ValorCSLL - OutrasRetençoes - ValorISSRetido - DescontoIncondicionado - DescontoCondicionado)
    $rps->valorLiquidoNfse(1321.50);

    #$rps->construcaoCivil('1234', '234-4647-aa');

//    envio do RPS
//    echo '<pre>';
    $response = $nfse->tools->gerarNfse($rps);

    //recupera o xml da request enviada ao webservices
//    $response = $nfse->tools->getXmlRequest();
//    print_r(get_class_methods($nfse->response));
    $return = $nfse->response->readReturn('return', $response);
    print_r($return);
    //apresentação do retorno
//    header("Content-type: text/xml");
//    echo $response;

} catch (\NFePHP\Common\Exception\SoapException $e) {
    echo $e->getMessage();
} catch (NFePHP\Common\Exception\CertificateException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}
