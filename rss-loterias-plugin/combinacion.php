
<div class="ultimas-combinaciones">
    <div class="d-flex justify-content-md-between flex-minimum-column-reverse lottery-logo">
        <img src="<?= PLUGIN_URL . 'img/' . $loteria['name'] . '.webp.' ?>" alt="<?= $loteria['name']?>" class="imagen-selector-loteria">
        <span class="fecha-combinaciones"><?= $loteria['fecha']?></span>
    </div>
    <div class="card-body">
        <p class="lottery-numbers"><?= $loteria['combinacion'] ?></p>
    </div>
</div>
