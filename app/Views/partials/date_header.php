<?php
date_default_timezone_set('America/Sao_Paulo');

$dia_semana_sigla = date('D');
$mes_sigla = date('M');
$dia_numero = date('d');
$ano_numero = date('Y');


$semana = array(
    'Sun' => 'DOMINGO',
    'Mon' => 'SEGUNDA-FEIRA',
    'Tue' => 'TERÇA-FEIRA',
    'Wed' => 'QUARTA-FEIRA',
    'Thu' => 'QUINTA-FEIRA',
    'Fri' => 'SEXTA-FEIRA',
    'Sat' => 'SÁBADO'
);
$mes_extenso = [
    'Jan' => 'JANEIRO',
    'Feb' => 'FEVEREIRO',
    'Mar' => 'MARÇO',
    'Apr' => 'ABRIL',
    'May' => 'MAIO',
    'Jun' => 'JUNHO',
    'Jul' => 'JULHO',
    'Aug' => 'AGOSTO',
    'Sep' => 'SETEMBRO',
    'Oct' => 'OUTUBRO',
    'Nov' => 'NOVEMBRO',
    'Dec' => 'DEZEMBRO'
];
?>
<div class="day text-center">
    <div class="data">
        <?= $semana[$dia_semana_sigla] ?? '' ?>
        <span class="data_all d-inline-block">
            <span class="data_dia"><?= $dia_numero ?></span>
            <span class="data_dia"><?= $mes_extenso[$mes_sigla] ?? '' ?></span>
            <span class="data_dia"><?= $ano_numero ?></span>
        </span>

        <?= strtoupper($data['campus']) ?>

    </div>
</div>