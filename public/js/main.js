const language = {
    sProcessing: "Procesando...",
    sLengthMenu: "Mostrar _MENU_ registros",
    sZeroRecords: "No se encontraron resultados",
    sEmptyTable: "Ningún dato disponible en esta tabla",
    sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
    sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
    sSearch: "Buscar:",
    sInfoThousands: ",",
    sLoadingRecords: "Cargando...",
    oPaginate: {
        sFirst: "Primero",
        sLast: "Último",
        sNext: "Siguiente",
        sPrevious: "Anterior"
    },
    oAria: {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    },
    buttons: {
        copy: "Copiar",
        colvis: "Visibilidad"
    }
}

function customDataTable(url, columns = [], order = 0, orderBy = 'desc'){
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        searchDelay : 1000,
        ajax: url,
        columns,
        order: [[ order, orderBy ]],
        language
    });
}

function customSelect(select, url, templateResult, templateSelection, dropdownParent, createNew){
    $(select).select2({
        dropdownParent: dropdownParent ? dropdownParent : null,
        language: {
            noResults: function() {
                return `Resultados no encontrados ${createNew ? '<button class="btn btn-link" onclick="'+createNew+'">Crear nuevo</a>' : ''}`;
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        ajax: { 
            allowClear: true,
            url,
            type: "get",
            dataType: 'json',
            delay: 500,
            processResults: function (response) {
                return {
                    results: response
                };
            }
        },
        minimumInputLength: 4,
        templateResult,
        templateSelection
    });
}

function formatResultPeople(data) {
    if (data.loading) {
        return 'Buscando...';
    }
    let image = "/images/default.jpg";
    if(data.image){
        image = "/storage/"+data.image.replace('.', '-cropped.');
    }
    var $container = $(
        `<div class="option-select2-custom">
            <div style="display:flex; flex-direction: row">
                <div>
                    <img src="${image}" style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px" />
                </div>
                <div>
                    <h5>
                        ${data.first_name ? data.first_name : ''} ${data.last_name1 ? data.last_name1 : ''} ${data.last_name2 ? data.last_name2 : ''} <br>
                        <p style="font-size: 13px; margin-top: 5px">
                            CI: ${data.ci ? data.ci : 'No definido'}
                        </p>
                    </h5>
                </div>
            </div>
            
        </div>`
    );

    return $container;
}

$(document).ready(function(){
    $('.btn-notification').click(async function(e){
        if (whatsappServer && imagesGeneratorServer && whatsappServerSession) {
            $('body').loading({message: 'Enviando...'});
            e.preventDefault();
            let route = $(this).attr('href');
            var phone = $(this).data('phone');
            if (phone) {
                let image = await fetch(`${imagesGeneratorServer}/generate?url=${route}`)
                                    .then(res => {
                                        if (!res.ok) {
                                            return null;
                                        }
                                        return res.json();
                                    })
                                    .then(res => res);
                if(image){
                    let body = {
                        phone: `591${phone}`,
                        text: '',
                        image_url: image.url
                    }

                    fetch(`${whatsappServer}/send?id=${whatsappServerSession}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(body)
                    })
                        .then(res => {
                            if (!res.ok) {
                                return null;
                            }
                            return res.json();
                        });
                    toastr.success('Recibo reenviado', 'Bien hecho');
                }else{
                    toastr.error('No se puedo generar el recibo', 'Error');
                }
                $('body').loading('toggle');
            } else {
                toastr.warning('Número de celular vacío', 'Advertencia');
            }
        } else {
            toastr.warning('Uno de los servidores no está configurado', 'Advertencia');
        }
    });
});