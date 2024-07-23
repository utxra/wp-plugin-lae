<script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('btn-dropdown');
        var dropdown = document.getElementById('dropdown-content');
        var detalles = document.querySelector('.detalles-lae');
        var contenedor = document.querySelector('.contenedor-2');

        dropdown.style.display = 'none';

        btn.addEventListener('click', function() {
            if (dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
                detalles.className = "detalles-lae-2 d-flex justify-content-between";
                detalles.innerHTML = '<div>Detalles de LAE</div><div><img src="<?= PLUGIN_URL. 'icons/dash.svg'?>"></div>';
            } else {
                dropdown.style.display = 'none';
                detalles.className = "detalles-lae d-flex rounded justify-content-between";
                detalles.innerHTML = '<div>Detalles de LAE</div><div><img src="<?= PLUGIN_URL. 'icons/plus.svg'?>"></div>';
            }
        });

        var selectorDias = document.getElementById('dia');
        var resultadoDetalle = document.getElementById('resultado-detalle');
        var fechaSelectorPrincipal = document.getElementById('fecha-principal');
        var descripcionLae = document.getElementById('descripcion-lae');
        var linkLae = document.getElementById('link-lae');

        function updateResultado(selectedDate) {
            var loteria = '<?php echo $loteria; ?>'; // Obtener el valor de la lotería del contexto PHP

            fetch(`<?php echo admin_url('admin-ajax.php'); ?>?action=get_resultado&loteria=${loteria}&date=${selectedDate}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                    } else {
                        resultadoDetalle.innerHTML = data.combination;
                        fechaSelectorPrincipal.innerHTML = data.date;
                        descripcionLae.innerHTML = data.description;
                        linkLae.href = data.link;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Seleccionar el último día al cargar la página
        var lastDate = selectorDias.value;
        updateResultado(lastDate);

        selectorDias.addEventListener('change', function() {
            var selectedDate = this.value;
            updateResultado(selectedDate);
        });
    });
</script>

<?php foreach ($resultados as $resultado) {
    $last_resultado = reset($resultados); // Obtener el primer elemento del array
?>
    <div class="container">
        <div class="selector-resultado rounded">
            <div class="d-flex">
                <div>
                    <img class="imagen-selector-loteria" src="<?= PLUGIN_URL . 'img/' . $loteria . '.webp' ?>" alt="<?= ucfirst(str_replace("-", " ", $loteria)) ?>">
                </div>
                <div class="fecha-selector-principal">
                    <p id="fecha-principal"><?= $last_resultado->get_date('j-m-Y | g:i a') ?></p>
                </div>
            </div>
            <div class="d-flex flex-xl-row flex-md-column-reverse contenedor-3">
                <div class="w-100 border rounded">
                    <div class="d-flex justify-content-center align-items-center contenedor-1 flex-minimum-column">
                        <div class="combinacion-ganadora">Combinación ganadora:</div>

                        <div class="d-flex align-items-center flex-md-row contenedor-4 text-center">
                            <div class="m-xl-3" id="resultado-detalle">
                                <?= extract_combination($last_resultado->get_description(), $loteria) ?>
                            </div>
                            <div>
                                <img src="<?= PLUGIN_URL . "/img/Logotipo_de_Loterías_y_Apuestas_del_Estado.svg" ?>" alt="LAE" style="height: 60px; margin: 15px;">
                            </div>
                        </div>
                    </div>
                    <div>
                        <button id="btn-dropdown" class="btn-lae">
                            <div class="detalles-lae d-flex rounded justify-content-between">
                                <div>Detalles de LAE</div>
                                <div><img src="<?= PLUGIN_URL. 'icons/plus.svg'?>"></div>
                            </div>
                        </button>
                        <div id="dropdown-content" class="dropdown-content">
                            <div class="p-3 descripcion-lae d-flex">
                                <div class="contenedor-2 responsive-table" id="descripcion-lae">
                                    <?= $last_resultado->get_description(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="botonera-loteria d-flex justify-content-center align-items-center flex-column p-3">
                    <select class="form-select" aria-label="Default select example" name="dia" id="dia">
                        <?php foreach ($resultados as $resultado) { ?>
                            <option value="<?= $resultado->get_date('j-m-Y') ?>" <?= $resultado === $last_resultado ? 'selected' : '' ?>>
                                <?= $resultado->get_date('j-m-Y') ?>
                            </option>
                        <?php } ?>
                    </select>

                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center">
                <a id="link-lae" class=" btn btn-primary m-3" href="<?= $last_resultado->get_permalink(); ?>" target="_blank">Ver más detalles</a>
            </div>
        </div>
    </div>
<?php break;
} ?>