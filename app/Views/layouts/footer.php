<script src="<?= BASE_URL ?>public/assets/js/jquery-3.5.1.js"></script>
<script src="<?= BASE_URL ?>public/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>public/assets/js/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>public/assets/js/sweetalert.js"></script>
<script src="https://kit.fontawesome.com/0de36d37a3.js" crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>public/assets/js/header_tv.js"></script>

<script src="<?= BASE_URL ?>public/assets/js/painel_updater.js"></script>

<!-- SORTABLEJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- TABELA -->
<script>
    // Bloco único para scripts da página
    $(document).ready(function () {

        // Inicialização do DataTable (só precisa de uma)
        $('#tabela').DataTable({
            paging: false,
            ordering: true, // Recomendo deixar true, mas pode mudar para false
            info: false,
            "language": {
                "sProcessing": "Procurando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "Nenhuma reserva encontrada para hoje.",
                "search": "",
                "info": "Mostrar _START_ até _END_ de _TOTAL_ registros",
                "infoEmpty": "Nenhum registro encontrado",
                "infoFiltered": "(filtrado de _MAX_ registros totais)",
                "searchPlaceholder": "Busca",
                "paginate": {
                    "first": "Primeiro",
                    "last": "Último",
                    "next": "Próximo",
                    "previous": "Anterior"
                },
            }
        });


        // Lógica para seleção de linhas da tabela
        $('#tabela tbody').on('click', 'tr', function () {
            $(this).toggleClass('selected');
        });

        // Lógica para o botão de exclusão com SweetAlert
        $(document).on('click', '.btn-excluir', function (e) {
            e.preventDefault();
            const deleteUrl = $(this).attr('href');
            Swal.fire({
                title: 'Você tem certeza?',
                text: "Esta ação não poderá ser revertida!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, pode excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        });


        // Lógica para reordenar com Drag and Drop
        const sortableTbody = document.getElementById('sortable-tbody');
        if (sortableTbody) {
            new Sortable(sortableTbody, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function (evt) {
                    const items = evt.to.children;
                    let orderedIds = [];
                    for (let i = 0; i < items.length; i++) {
                        orderedIds.push(items[i].getAttribute('data-id'));
                    }
                    $.ajax({
                        url: '<?= BASE_URL ?>admin/salvarOrdemAjax',
                        type: 'POST',
                        data: { ordem: orderedIds },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                                Toast.fire({ icon: 'success', title: 'Ordem salva!' });
                            }
                        }
                    });
                }
            });
        }
    });

    // Lógica do Preloader (usa o evento 'load' do window)
    $(window).on('load', function () {
        const preloader = $('#preloader');
        if (preloader.length) {
            preloader.fadeOut('slow', function () {
                $(this).remove();
                $('body').css('overflow', 'visible'); // Restaura o overflow do body
            });
        }
    });


</script>

<!-- TOASTIFY -->
<?php
if (isset($_SESSION['toast_message'])):
    $toast = $_SESSION['toast_message'];
    ?>
    <script>
        // Espera o documento carregar para executar
        document.addEventListener('DOMContentLoaded', function () {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: '<?= $toast['type'] ?>',
                title: '<?= addslashes($toast['message']) ?>'
            });
        });
    </script>
    <?php
    unset($_SESSION['toast_message']);
endif; ?>



</body>

</html>