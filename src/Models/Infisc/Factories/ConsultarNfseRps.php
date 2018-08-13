<?php

namespace NFePHP\NFSe\Models\Infisc\Factories;

use NFePHP\NFSe\Models\Infisc\Factories\Header;
use NFePHP\NFSe\Models\Infisc\Factories\Factory;

class ConsultarNfseRps extends Factory
{
    public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $inscricaoMunicipal,
        $numero,
        $serie,
        $tipo
    ) {
        $method = "ConsultarNfseRpsEnvio";
        $xsd = 'servico_consultar_nfse_rps_envio';
        $content = $this->requestFirstPart($method, $xsd);
        $content .= "<IdentificacaoRps>";
        $content .= "<tc:Numero>$numero</tc:Numero>";
        $content .= "<tc:Serie>$serie</tc:Serie>";
        $content .= "<tc:Tipo>$tipo</tc:Tipo>";
        $content .= "</IdentificacaoRps>";
        $content .= Header::render($remetenteTipoDoc, $remetenteCNPJCPF, $inscricaoMunicipal);
        $content .= "</$method>";
        
        $body = $this->clear($content);
        $this->validar($versao, $body, 'Infisc', $xsd, '');
        return $body;
    }
}