var personSelected;
        $(document).ready(function(){
            $('<style>.select2-results__options { max-height: 350px !important; }</style>').appendTo('head');
            $('#select_people_id').select2({
                width: '100%',
                placeholder: '<i class="fa fa-search"></i> Buscar...',
                escapeMarkup : function(markup) {
                    return markup;
                },
                language: {
                    inputTooShort: function (data) {
                        return `Por favor ingrese ${data.minimum - data.input.length} o más caracteres`;
                    },
                    noResults: function () {
                        return `<i class="far fa-frown"></i> No hay resultados encontrados`;
                    }
                },
                quietMillis: 250,
                minimumInputLength: 2,
                ajax: {
                    // url: "{{ url('admin/ajax/personList') }}",        
                    url: window.personListUrl, // Usa la variable global
                    processResults: function (data) {
                        let results = [];
                        data.map(data =>{
                            results.push({
                                ...data,
                                disabled: false
                            });
                        });
                        return {
                            results
                        };
                    },
                    cache: true
                },
                templateResult: formatPersonResult,
                templateSelection: (opt) => {
                    window.personSelected = opt; // Guarda en variable global
                    return opt.first_name?opt.first_name+' '+ (opt.last_name1?opt.last_name1:'')+(opt.last_name2?' '+opt.last_name2:''):'<i class="fa fa-search"></i> Buscar... ';
                }
            }).on('select2:select', function(e){
                var data = e.params.data;
                window.personSelected = data;

                // Llenar CI
                $('#input-dni').val(data.ci ? data.ci : '');

                // Abrir modal para confirmar/actualizar celular si existe en la vista
                if($('#modal-update-phone').length){
                    $('#person-name').text(data.first_name + ' ' + (data.last_name1 || ''));
                    $('#input-phone').val(data.cell_phone || '');
                    $('#input-person-id').val(data.id);
                    $('#modal-update-phone').modal('show');
                    $('#btn_submit').attr('disabled', true); // Asegurarse que el botón principal esté deshabilitado
                }
            });
        });

        function formatPersonResult(option){
            // Si está cargando mostrar texto de carga
            if (option.loading) {
                return '<span class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>';
            }

            let image = window.defaultImage;
            
            if (option.image) {
                // Remove the extension and add the cropped suffix with webp extension
                const lastDotIndex = option.image.lastIndexOf('.');
                const baseName = lastDotIndex !== -1 ? option.image.substring(0, lastDotIndex) : option.image;
                image = `${window.storagePath}${baseName}-cropped.webp`;
            }

            return $(`  
                        <div style="display: flex">
                            <div style="margin: 0px 10px">
                                <img src="${image}" width="50px" />
                            </div>
                            <div>
                                <small>CI: </small><b style="font-size: 15px; color: black">${option.ci?option.ci:'No definido'}</b><br>
                                <b style="font-size: 15px; color: black">${option.first_name} ${option.last_name1??''} ${option.last_name2??''}</b>
                            </div>
                        </div>
                        `);
        }