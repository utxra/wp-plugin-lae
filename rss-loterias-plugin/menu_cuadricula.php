<form action="<?= URL ?>" method="post" enctype="multipart/form-data">
    <div>        
        <div class="">
            <div class="cuadricula-menu">
                <? foreach ($lae as $loteria) {
                    include 'cuadricula.php';
                }
                ?>
            </div>
        </div>
    </div>
</form>